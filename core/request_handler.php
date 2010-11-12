<?php

class RequestHandler {

    var $session;

    var $controller;

    var $legacy = false;

    public function __construct() {
        $this->session = new Session();
    }

    //
    //	spracovanie requestu
    //
    public function handle() {
        $page = htmlspecialchars(@$_GET['page']);
        $page = $this->getPageIfEmpty($page);

        $view = $this->pageData($page);

        $this->render($view);
    }

    //
    //	v pripade prihlaseneho uzivatela nastavi hlavnu stranku
    //	v pripade odhlaseneho uzivatela nastavi login stranku
    //
    private function getPageIfEmpty($page) {
        if(empty($page)) {
            $userId = $this->session->read('uid');
            if(!empty($userId)) {
                return 'user/home';
            } else {
                return 'auth/login';
            }
        }

        return $page;

    }

    //
    //	spatna kompatibilita so starym systemom
    //
    private function pageData($page) {

        $components = explode("/", $page);
        $componentSize = count($components);

        if($componentSize == 1) {
            $this->legacy = true;
            return $this->legacyPageData($page);
        } else {
            return $this->invokeController($page);
        }
    }

    //
    //	spatna kompatibilita so starym systemom
    //
    private function legacyPageData($page) {
        ob_start();

        $user = new uzivatel();

        require("$page.php");

        return ob_get_clean();
    }

    //
    //	parsovanie "peknych" URLciek
    //
    private function parseUrl($url) {
        require('config/routes.php');

        $components = array();
        $rest = "";

        foreach($prefixes as $prefix) {
            $pos = strpos($url, $prefix);
            if($pos === 0) {
                $components["path"] = $prefix;
                $rest = substr($url, strlen($prefix));
                break;
            }
        }

        if(empty($components)) {
            $rest = $url;
            $components['path'] = '.';
        }

        $urlParts = explode('/', $rest);

        $components["controller"] = $urlParts[0];
        $components["method"] = $urlParts[1];
        $components["params"] = array_slice($urlParts, 2);

        return $components;

    }

    //
    //	volanie funkcii controllera na zaklade URL CamelCase konvenciou
    //
    private function invokeController($page) {

        $components = $this->parseUrl($page);

        $controllerPath = $components['path'];
        $controllerName = $components['controller'];
        $method = $components['method'];
        $arguments = $components['params'];

        $controllerFullPath = "controller/" . $controllerPath . "/" . $controllerName . ".php";
        $controllerObjName = ucfirst($controllerName) . "Controller";

        if(!file_exists($controllerFullPath)) {
            $data = array(
                'controllerPath' => $controllerFullPath,
                'controllerName' => $controllerObjName,
            );
            $this->renderError($data, 'missing_controller_path');
        }

        require($controllerFullPath);

        if(!class_exists($controllerObjName)) {
            $data = array(
                'controllerPath' => $controllerFullPath,
                'controllerName' => $controllerObjName,
            );
            $this->renderError($data, 'missing_controller_class');
        }

        $this->controller = new $controllerObjName();
        $this->controller->setURL($page);

        if(!method_exists($this->controller, $method)) {
            $data = array(
                'controllerPath' => $controllerFullPath,
                'controllerName' => $controllerObjName,
                'controllerMethod' => $method,
            );

            $this->renderError($data, 'missing_controller_method');
        }

        $this->controller->setPath($controllerPath . "/" . $controllerName);
        $this->controller->setSession($this->session);

        $this->controller->timeout();

        $this->controller->beforeFilter();

        try {
            call_user_func_array(array(&$this->controller, $method), $arguments);
        } catch(Exception $e) {
            $this->renderError(array('exception' => $e), '500');
        }

        $this->controller->afterFilter();

        $this->controller->beforeRender();

        if(!file_exists("view/$controllerPath/$controllerName/$method.php")) {
            $data = array(
                'viewPath' => "view/$controllerPath/$controllerName/$method.php"
            );

            $this->renderError($data, 'missing_view');
        }

        return $this->controller->render($method);
    }

    private function renderError($viewData, $view) {
        /*
         * Drobna uprava chovania:
         * - v rezime DEBUG
         *      - chybajuce casti ako missing controller_(path|method|class) budu 
         *        spracovane cez konkretny pohlad ktory povie kde je chyba
         *      - chyba v systeme poskytne komplet vypis
         * - v prevadzkovom rezime (DEBUG=false)
         *      - chybajuce casti budu hadzat custom 404 chybu
         *      - chyba v systeme poskytne light vypis (nastala chyba)
         */
        
        // spolocne pre vsetky chyby ...
        ob_start();
        extract($viewData, EXTR_SKIP);
        // posleme aj korektny header, radsej nech sa pozrie aky protok sa pouziva a podla toho reply
        header($_SERVER["SERVER_PROTOCOL"]." 500 Error");
        // chyba v systeme ma specialne chovanie
        if ($view == '500')
        {
            $this->logError($viewData["exception"]);
            if (DEBUG) require('view/system/500.php');
            else require('view/system/500_light.php');
        }else
        {
            // not found chyby
            header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
            //zvysne chyby
            if(DEBUG) require("view/system/$view.php");
            else require('view/system/404.php');
        }
        $this->render(ob_get_clean());
        // po skonceni renderu ukoncit, inac by sa vyrenderovala aj povodna stranka
        exit();
    }

    private function logError($ex)
    {
        if (is_a($ex, dbException))
        {
                $dbh = Connection::get();
                // transakcia padla takze treba rollback
                // inac by spadlo aj toto pridanie dokonca bez hlasky
                $dbh->transactionRollback();
        }
        // maly hack, nasimulujem pridanie pripomienky
        $chyba = new Suggestions();
        // nastavime pozadovane parametre
        $chyba->id_pedagog = $this->controller->getUserID();
        $chyba->pripomienka_typ_id = 1; // chyba v systéme  
        $chyba->stav = 1; // nova
        $chyba->text =
            "Exception: {$ex->backtrace[0]['class']}\n".
            "Message: {$ex->getMessage()}\n";
        if (isset($ex->backtrace))
        {
            foreach($ex->backtrace as $backtrace) 
            {
                $chyba->text .= "{$backtrace['file']} ({$backtrace['line']}) : ";
                $chyba->text .= "{$backtrace['class']} -> {$backtrace['function']}\n";
            }
        }    
       
        $chyba->save($this->controller->getUserID());

        $notifikator = new Notificator();
        $notifikator->setController($this->controller);
        $notifikator->sendSuggestionAddedMsg($chyba);
    }
    
    //
    //	vykreslenie stranky
    //
    private function render($viewData) {
    //if this is an AJAX request, don't use the template
        if(@$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
            echo $viewData;
        } else {
            $t = new XTemplate("index.tpl");
            
            $flash_class  = $this->session->read('flash_class');
            switch ($flash_class)
            {
                case "error":
                    $flash_class    = "ui-state-error";
                    $icon_class     = "ui-icon-alert";
                    break;
                default:
                    $flash_class = "ui-state-highlight";
                    $icon_class  = "ui-icon-info";
                    // warning
            }
            $flash_class .= " ui-corner-all";
            $icon_class  .= " ui-icon";
            $flash        = $this->session->read('flash');
            $flashHTML    = 
            "<div class='ui-widget' style='padding-bottom: 5px;'>".
            "   <div class='{$flash_class}' style='padding: 5px 5px 5px 5px;'>".
            "   <p><span class='{$icon_class}' style='float: left; margin-right: .3em;'></span>".
            "   {$flash}".
            "   </p>".
            "   </div>".
            "</div>";
             
            $default = array(
                'TOP_MENU'    => '',
                'CONTENT'     => $viewData,
                'FLASH'       => $flash ? $flashHTML : '',
                'SVN_VERSION' => SVN_VERSION
            );

            if($this->legacy) {
                $menu = new Menu();
                $menuHelper = new MenuHelper();

                $userId = $this->session->read('uid');

                if(!empty($userId)) {
                    $menuItems = $menu->getForUser($userId);
                    $menuHtml = $menuHelper->render($menuItems, true);

                    $default["MENU"] = $menuHtml;
                }
            }

            if(method_exists($this->controller, 'getTemplateParts')) {
                $t->assign(array_merge($default, $this->controller->getTemplateParts()));
            } else {
                $t->assign($default);
            }

            $t->parse('PAGE');
            echo $t->text('PAGE');

            $this->session->clear('flash');
            $this->session->clear('flash_class');

            if(DEBUG) {
                $this->dumpDebugInfo();
            }
        }
    }

    //
    //	FirePHP debug
    //
    private function dumpDebugInfo() {
        $dbh = Connection::get();

        $time = 0;

        $table = array(array('SQL Statement', 'Time', 'Result'));
        foreach($dbh->sql_history as $sql) {
            $table[] = $sql;
            $time += $sql[1];
        }

        $query_count = count($dbh->sql_history);

        fb(array("$query_count queries took $time seconds", $table), FirePHP::TABLE);

        // dump session

        $table = array(array('Key', 'Value'));
        foreach(array_keys($_SESSION) as $key) {
            $table[] = array($key, $_SESSION[$key]);
        }

        fb(array('Session (' . count($_SESSION) . ' items)', $table), FirePHP::TABLE);
    }

}

?>
