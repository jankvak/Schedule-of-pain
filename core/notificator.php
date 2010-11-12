<?php

class Notificator {

    private $maily;
    private $controller;

    //****************************INICIALIZACNE***********************************************
    public function __construct()
    {
        $this->maily = new MailList();
    }

    public function setController($controller)
    {
        $this->controller = $controller;
    }

    //*******************************FILTRE***************************************************
    private function __getMailList($list)
    {
        $res = array();
        foreach ($list as $mail)
        {
            $res[] = $mail["mail"];
        }

        return $res;
    }

    private function __filterList($list)
    {
        $myMail = $this->controller->session->read("mail");
        $notifyMyChanges = $this->controller->session->read("notifyMyActions");
        if (!$notifyMyChanges) $list = array_diff($list, array($myMail));

        return $list;
    }

    private function __unify($list)
    {
    // najprv vyextrahuje udaje len na pole mailov
        $list = $this->__getMailList($list);
        // naslene prefiltruje podla nastaveni
        $list = $this->__filterList($list);

        // vrati zoznam zle bez opakujucich sa mailov
        // TODO: potrebne ?
        fb($list, "Mail list");
        return array_unique($list);
    }

    //******************************NOTIFIKACIE***********************************************

    /*
     * Zostavi notifikacnu spravu pre start zberu poziadaviek a odosle ju
     * @param <collection> @collection- model collection, v ktorom sa nachadza
     * datum start, koniec a aj id semestra
     */
    public function sendStartCollectionNotifyToAllUsers($collection)
    {
        $semesterID = $this->controller->session->read("semester");

        $default = array(
            'DATE' => date("d.m.Y H:i", time()),
            'START_DATE' => $collection->zaciatok,
            'END_DATE' => $collection->koniec,
            'URL' =>  BASE_URL."/user/home"
        );
        $message = $this->__createTemplate("messages/startCollection.tpl", $default);
        $toList = $this->__unify($this->maily->getListForCollection($collection->id_semester));

        $ref = $this->__createRef("collection", $semesterID);
        $this->sendNotifyMessage($toList, $message, "MOP notifikácia - Začiatok zberu údajov", $ref);
    }

    /*
     * metoda dostane potrebne parametre, zlozi si
     * spravu a odosle email
     * @param <string> model garant_requirement odkial ziskam potrebne data
     */
    public function sendCourseAssignedMsg($requirements)
    {
        $user = new User();
        $garant = $this->controller->session->read("name");
        $pract = $user->findById($requirements->cviciaci);
        $teacher= $user->findById($requirements->prednasajuci);

        $list = array(
            1 => array(
            "mail" => $pract["mail"],
            "role" => "Cvičiaci",
            "url_part" => "/pract/requirements/edit/"
            ),
            2 => array(
            "mail" => $teacher["mail"],
            "role" => "Prednášajúci",
            "url_part" => "/teacher/requirements/edit/"
            )
        );

        for($i=1; $i<3;$i++)
        {
            $mail = $list[$i]["mail"];
            $toList = array($mail);
            $toList =  $this->__filterList($toList);

            if(empty($toList) )
                continue;

            $default = array(
                'DATE' => date("d.m.Y H:i", time()),
                'GARANT' => $garant,
                'COURSE' => Subjects::getSubjectInfo($requirements->id),
                'ROLE'  => $list[$i]["role"],
                'URL'    => BASE_URL. $list[$i]["url_part"]. $requirements->id
            );

            $message = $this->__createTemplate("messages/courseAssigned.tpl", $default);
            $subject = "[".Subjects::getSubjectInfo($requirements->id). "]"." - priradená zodpovednosť ". $list[$i]["role"];
            $ref = $this->__createRef("garant.course", $requirements->id);

            $this->sendNotifyMessage($toList, $message, $subject, $ref);
        }
    }

    /*
     * Zostavi notifikacnu spravu pre zmenu poziadavky a odosle ju
     * @param <string> $urlPart - posledna cast url adresy [rola/controller/metoda]
     * @param <int> $predmetID - id predmetu
     * @param <string> $rola - maily sa poslu len pre vyssiu rolu
     */
    public function sendRequirementChangedMsg($url_part, $predmetID, $rola)
    {
        $user = $this->controller->session->read("name");
        // pridam za url ktoru poziadavku zmenil
        $url = BASE_URL."/".$url_part.$predmetID;
        $subject = Subjects::getSubjectInfo($predmetID);
        $default = array(
            'DATE' => date("d.m.Y H:i", time()),
            'COURSE'  =>  $subject,
            'URL' => $url,
            'USER' => $user
        );

        $message = $this->__createTemplate("messages/requirementChanged.tpl", $default);
        $toList = $this->__unify($this->maily->getTeacherListForPredmet($predmetID, $rola));

        $subject_part = $rola == "Pract" ? "na cvičenia": "na prednášky";

        $ref = $this->__createRef("requirement.{$rola}", $predmetID);
        $this->sendNotifyMessage($toList, $message, "[". $subject."] - zmenená požiadavka ". $subject_part, $ref);
    }

    /*
     * Zostavi notifikacnu spravu pre zmeneny chat a odosle ju
     * @param <Comments> $comments - model s komentarom
     * @param <string> $userChangedChat - pouzivatel, ktory prispel do chatu
     * @param <string> $urlPart - posledna cast url adresy [rola/controller/metoda]
     */
    public function sendChatChangedMsg($comments, $urlPart, $role)
    {
        $userChangedChat = $this->controller->session->read("name");
        $courseID = $comments->course_id;
        $courseInfo = Subjects::getSubjectInfo($courseID);
        $default = array(
            'DATE' => date("d.m.Y H:i", time()),
            'COURSE' => $courseInfo,
            'USER_CHANGED_CHAT' => $userChangedChat,
            'REQUIREMENT' => $comments->metaID,
            'LAST_COMMENT'   => nl2br($comments->commentText),
            'URL' => BASE_URL."/{$urlPart}/{$courseID}#komentare"
        );
        $message = $this->__createTemplate("messages/chatChanged.tpl", $default);
        $toList = $this->__unify($this->maily->getListForComments($courseID, $role));

        $subject_part = $role == "Pract" ? "k cvičeniu" : "k prednáške";

        $ref = $this->__createRef("requirement.{$role}.chat", $courseID);
        $this->sendNotifyMessage($toList, $message,"[{$courseInfo}] zmenený komentár {$subject_part}", $ref);
    }

    /*
     * Zostavi notifikacnu spravu pre pridanu pripomienku.
     * Tuto spravu posle vsetkym adminom.
     * @param <suggestions> $suggestions - model pripomienok
     */
    public function sendSuggestionAddedMsg($suggestions)
    {
        $default = array(
            'DATE' => date("d.m.Y H:i", time()),
            'USER' => $this->controller->session->read("name"),
            'TEXT'=> nl2br($suggestions->text),
            'URL' => BASE_URL.'/administrator/suggestions/edit/'.$suggestions->id
        );

        $message = $this->__createTemplate("messages/suggestionAdded.tpl", $default);
        $toList = $this->__unify($this->maily->getAdminList());

        $ref = $this->__createRef("suggestions", $suggestions->id);
        $this->sendNotifyMessage($toList, $message, "MOP Notifikácia - Pridaná pripomienka", $ref);
    }

    /*
     * Zostavi notifikacnu spravu pre zmenu stavu pripomienky.
     * Odosle ju pouzivatelovy, ktory spravu zadal.
     * @param <suggestions> $suggestions - model pripomienok 
     */
    public function sendSuggestionChangedMsg($suggestions)
    {
        $url = BASE_URL.'/all/suggestion/index?filter='.urlencode($suggestions->casova_peciatka);

        $default = array(
            'DATE' => date("d.m.Y H:i", time()),
            'TEXT' => nl2br($suggestions->text),
            'STATUS' => Suggestions::$nazvyStavov[$suggestions->stav] ,
            'URL' => $url
        );

        $message = $this->__createTemplate("messages/suggestionChanged.tpl", $default);
        $list = $this->maily->getUserMailBySuggestionsId($suggestions->id);
        $toList = $this->__unify($list);

        $ref = $this->__createRef("suggestions", $suggestions->id);
        $this->sendNotifyMessage($toList, $message, "MOP Notifikácia - Zmenená pripomienka", $ref);
    }

    //*****************************GENERATORY MAILOV******************************************

    /*
     * posle upozornenie pouzivatelovi ($to)
     * s telom spravy ($message)
     * a subjektom ($subject)
     */
    private function sendNotifyMessage($toList, $message, $subject, $references)
    {
    // vytvorim hlavicku mailu
        $headers = "From:" . MAIL_FROM . "\r\n";
        $headers .= "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "Bcc:". $toList = implode(", ", $toList). "\r\n";
        $headers .= "References: <{$references}>\r\n";
	$headers .= "Message-Id: <{$references}>\r\n";

        // zapiseme log do databazy
        $log = "Mail poslany na: " . $toList." \r\n Predmet správy: " . $subject. "\r\n Telo správy: " . $message;
        $log = htmlentities($log, ENT_QUOTES, "UTF-8");
        $this->controller->log($log);
        //echo ($headers);
        //echo ($message);

        if(mail("", $subject, $message, $headers))
        {

        }
        else
        {
        // todo nieco spravit ak sa nepodari odoslat
        // TODO: trapi nas ze neodosla notifikacia ? aj tak s tym nic moc nespravi
        }
    }

    //*****************************GENERATORY SPRAV******************************************

    private function __createTemplate($templatePath, $defaultValues)
    {
        $template = new XTemplate($templatePath);
        $template->assign($defaultValues);
        $template->parse('PAGE');
        return $template->text('PAGE');
    }

    private function __createRef($action, $id)
    {
        return "mop.{$action}-{$id}@labss2.fiit.stuba.sk";
    }
}
?>
