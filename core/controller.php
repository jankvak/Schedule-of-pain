<?php

/**
 * Trieda zodpovedna za oznamenia ze vstupne data neboli korektne
 */
class dataValidationException extends Exception {
    public $checked;

    function __construct($checked) {
        $this->checked = $checked;
    }
}

class Controller extends AutoLoadable {

    var $path = "";

    var $viewData = array();

    var $rendered = false; //prevent double render

    var $session;

    var $templateParts = array();

    // aktualna URL, protected for all childs to see
    protected $url;

    private static $logger = null;

    protected static $DEFAULT_URL = "user/home";

    function __construct() {
    }

    function setSession($session) {
        $this->session = $session;
    }

    function setPath($path) {
        $this->path = $path;
    }

    public function setURL($url)
    {
        if (empty($url) || is_null($url)) $this->url = self::$DEFAULT_URL;
        else $this->url = $url;
    }

    public function set($name, $value) {
        $this->viewData[$name] = $value;
    }

    public function render($view) {
        if(!$this->rendered)
        {
            $user = $this->session->read("uid");
            if (!empty($user))
            {
                $this->set("read_only_semester", $this->isActiveSemesterReadOnly());
            } else {
                // vlozi fiktivnu hodnotu true ak nie je prihlaseny (avoid notices)
                $this->set("read_only_semester", true);
            }
            	
            extract($this->viewData, EXTR_SKIP);

            ob_start();

            // include custom helperov
            require_once "core/view_helpers.php";
            // samotny rendering
            require("view/{$this->path}/$view.php");

            $this->rendered = true;

            return ob_get_clean();
        }
    }

    public function flash($message, $class = 'info') {
        $this->session->write('flash', $message);
        $this->session->write('flash_class', $class);
    }

    private function __httpsURL($location)
    {
        if (!preg_match("/^https/", $location))
        {
            $location = preg_replace("/^http/", "https", $location);
        }
        return $location;
    }

    /**
     * Presmeruje na danu lokaciu, lokacia moze byt:
     * <ul>
     * <li>relativna: 'auth/login', na absolutnu sa doplni pomocou BASE_URL</li>
     * <li>absolutna> 'http://localhost/auth/login'</li>
     * </ul>
     * @param string $location lokacia kam presmerovat
     * @param bool $secured ak je true pouzije sa https
     */
    public function redirect($location, $secured = false) {
        	
        $baseURL = BASE_URL;
        // konverzia na HTTPS URL
        if ($secured === true)
        {
            $location 	= $this->__httpsURL($location);
            $baseURL 	= $this->__httpsURL($baseURL);
        }
        //redirect
        if(preg_match('/^http/', $location)) {
            header("Location: $location");
        } else {
            header("Location: {$baseURL}/{$location}");
        }
        exit();
    }

    public function addToTemplate($key, $html) {
        $this->templateParts[$key] = $html;
    }

    public function getTemplateParts() {
        return $this->templateParts;
    }

    public function timeout() {
        $session_start  = $this->session->read('start');
        $session_uid    = $this->session->read('uid');

        // timeout sa vykonava iba ak je user PRIHLASENY,
        // inac by napr. pro logine mu rovno hodilo ze timeout vyprsal
        if(!empty($session_start) && isset($session_uid)) {
            $session_life = time() - $this->session->read('start');
            if($session_life > AUTH_TIMEOUT) {
                $this->log("Odhlásenie z dôvodu dlhej doby neaktívnosti");
                // namiesto redirectu na logout vykona logout sam
                $auth = new Auth();
                $auth->logout();

                $this->session->write("redirect", $this->url);
                $this->flash('Boli ste odhlásený, pretože ste boli dlho neaktívny.', 'info');
                $this->redirect('auth/login');
            }
        }

        $this->session->write('start', time());
    }

    /**
     * Nabinduje premenne z $_POST do zadaneho modelu
     * Vracia:
     * - pole vsetkych korektnych premennych vzdy
     * Vynimky:
     * vyhodi vynimku dataValidationException, ak co i len jedna premenna nesplna podmienky
     */
    public function bind($model) {
        // vsetky chybove hlasky
        $errors = "";
        // toto pole obsahuje vsetky premenne splnajuce zadane kriteria
        foreach(array_keys($_POST) as $variable) {
            // berie referenciu nech moze hodnotu vynulovat
            $value = &$_POST[$variable];

            if(property_exists($model, $variable)) {
                try {
                    Validator::validProperty($variable, $value, $model);
                }catch(InvalidValue $ex) {
                    $errors .= $ex->getMessage()."<br/>\n";
                }
                // premennu nastavi, ale ak bola chyba $value je empty (resp. chybne veci v indexe)
                $checked[$variable] = $value;
                // modelu nastavi vzdy, ak je chyba aj tak ho nepouzije ...
                $model->$variable   = $value;
            }
        }
        if (empty($errors)) {
            return $checked;
        }else {
            $this->flash($errors, 'error');
            throw new dataValidationException($checked);
        }
    }

    /**
     *
     * Vrati instanciu objektu Log (model)
     * @return <Log> singleton objektu Log
     */
    private static function getLogger() {
        if (is_null(self::$logger))
        self::$logger = new Log();
        return self::$logger;
    }

    /**
     * Metoda zaloguje dany event (automaticky ako prihlasena osoba).
     * V pripade preberatych prav zaloguje obidve mena (kto + v koho mene)
     * <b>POZOR:</b> meno usera sa ziskava zo session, t.j. pri ruseni
     * premennych v session treba najprv logovat a potom rusit
     * @param <string> $event text udalosti
     */
    public function log($event) {
        $username   = $this->session->read("name");
        $admin      = $this->session->read("admin");
        $substitute = isset($admin) ? $admin["meno"] : "";
        $this->getLogger()->addLog($username, $substitute, $event);
    }

    // callbacks

    public function beforeRender() {}
    public function beforeFilter() {}
    public function afterFilter() {}
}

?>
