<?php

defined('IN_CMS') or die('No access');

class UserController extends AppController {

    protected $access = array("All");

    function __construct()
    {
        parent::__construct();
    }

    function home()
    {
        $user = new User();
        
        $usr = $user->findById($this->session->read('uid'));
        fb($usr);
        $this->set('name',$this->session->read('name'));
        $this->set('mail', $usr["mail"]);
        
//        $calendar = new Calendar();
//        $actualEvents = $calendar->getActualEvents();
//        $futureEvents = $calendar->getFutureEvents();
//        $this->set('actualEvents', $actualEvents);
//        $this->set('futureEvents', $futureEvents);
    }

    public function change_sem()
    {
        $newSemester = $_POST["semester"];
        $this->session->write("semester", $newSemester);

        // navrat tam odkial prisiel
        $this->redirect($_POST["redirect"]);
    }
}

?>
