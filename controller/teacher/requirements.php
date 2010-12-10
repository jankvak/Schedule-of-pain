<?php
define("HELP_COMMON", "help/teacher/requirement/help.php");

class RequirementsController extends AppController {

    protected $access 	= array('Lecturer');
    private $redirect	= "teacher/requirements/index";
    // modely s datami
    private $requirements;
    private $comments;

    function __construct()
    {
        parent::__construct();
		$this->subjects = new Subjects();
        $this->requirements = new TeacherRequirements();
        $this->comments = new Comments();
        $this->helpLink = HELP_COMMON;
    }

    public function index() {
        $osoba_id = $this->getUserID();
        $semesterID = $this->getSemesterID();
        $this->set('courses', $this->requirements->getCourses($osoba_id, $semesterID));
    }

    /**
     * Metoda predvyplni poziadavky na predmet podla zadaneho minulorocneho predmetu
     * @param int $predmetID - id aktualneho predmetu kam predvyplni
     */
    public function copy($predmetID)
    {
        if (isset($_POST["id_minuly"]))
        {
            $metaPoz = $this->requirements->getLastRequest($_POST["id_minuly"]);

            $this->commonEdit($predmetID, $metaPoz);
            // nastavi az potom lebo common edit dava na false
            $this->set("poziadavka_prebrata", 1);
        }else
        {
            // ak neodoslal formular ale manualne zadal URL presmetuje ho na edit
            $this->redirect("teacher/requirements/edit/{$predmetID}");
        }
    }

    /**
     * Unifikovana metoda:
     * - ak nebola zadana poziadavka umozni zadat (doplni predvolene udaje),
     * - ak bola zadana nacita a predvyplni
     * @param $predmetID - id predmetu
     */
    public function edit($predmetID)
    {
        // staci vediet aky predmet, chce poslednu metapoziadavku nech ju zadal ktokolvek
        $metaPoz = $this->requirements->getLastRequest($predmetID);

        $this->commonEdit($predmetID, $metaPoz);
        // nastavi lock (bude empty ak nebola zadana)
        $this->session->write($this->getLockName($predmetID), $metaPoz["id"]);
    }

    public function save()
    {
        try
        {
            $this->bind($this->requirements);
            $courseID = $this->requirements->course_id;
            // ak ho nevyucuje nepovoli mu zadat
            if (!$this->canEdit($courseID, "Lecturer", $this->redirect)) return;

            $lock = $this->session->read($this->getLockName($courseID));
            $this->requirements->save($this->getUserID(), $lock);

            $courseInfo = Subjects::getSubjectInfo($courseID);

            //odoslat notifikaciu
            $this->notificator->sendRequirementChangedMsg("teacher/requirements/edit/", $courseID, "Lecturer");

            $this->log("Vloženie novej požiadavky prednášajúceho na predmet `{$courseInfo}`");
            $this->flash('Požiadavky uložené.', 'info');
            $this->redirect('teacher/requirements/index');
        }catch (dataValidationException $ex) {
            // tak treba nacitat data ako pri add
            // BACHA: ak sa zrube validacia do $courseID = sa NEVYKONA !
            $this->__getCommonData($this->requirements->course_id);
            // nastavi iba validne data !
            $this->set("requirement", $ex->checked["requirement"]);
            // nastavi ci bola poziadavka preberana alebo nie
            $this->set("poziadavka_prebrata", $this->requirements->poziadavka_prebrata);
            // nezabudnut nastavit aj ID aby vedel robit kontroly kolizii
            $this->set("actualMetaID", $this->requirements->previousMetaID);
        }catch (RequestModified $ex)
        {
            // toto sa zrube uz pri save takze $courseID mame
            $hlaska  = "Požiadavka bola zmenená iným používateľom.<br/>";
            $hlaska .= "Kliknutim <a href=\"teacher/requirements/edit/{$courseID}\" target=\"_BLANK\">sem</a> si otvorte poslednú verziu požiadavky a upravte tú.";
            $this->flash($hlaska, "error");
            $this->__getCommonData($courseID);
            $this->set("requirement", $this->requirements->requirement);
            $this->set("poziadavka_prebrata", $this->requirements->poziadavka_prebrata);
            // nezabudnut nastavit aj ID aby vedel robit kontroly kolizii
            $this->set("actualMetaID", $this->requirements->previousMetaID);
        }
    }

    public function saveComment()
    {
        try
        {
            $this->bind($this->comments);
            $this->comments->saveCommentType3($this->getUserID());
            	
            // poslat notifikaciu
            $this->notificator->sendChatChangedMsg($this->comments, "teacher/requirements/edit", "Lecturer");

            $this->flash("Komentár do diskusie bol pridaný.");
            $this->redirect("teacher/requirements/edit/{$this->comments->course_id}");
        }catch(dataValidationException $ex)
        {
            // ak zlyhalo doplnime data pre poziadavku
            $this->edit($this->comments->course_id);
        }
    }

    /**
     * Ziska data poziadavky, nastavi aj predmet aj semester
     * @param unknown_type $courseID
     * @return unknown_type
     */
    private function __getCommonData($courseID) {
        $rooms = new Rooms();
        $soft = new Software();
        $equip = new Equipment();
        $subjects = new Subjects();
        $student_count = $subjects->getStudentCount($courseID);
        $student_count_info = $subjects->getStudentCountInfo($courseID);
        // od max kapacity
        $this->set('capacities', $rooms->getCapacities("DESC"));
        $this->set('equips', $equip->getAllTypes());
        $this->set('rooms', $rooms->getAll());
        $this->set('software', $soft->getAll());
        // zoradene, len podla nazvu
        $this->set('roomsByName', $rooms->getAll(true));
        $this->set('subject', $subjects->getSubject($courseID));
        $this->set('student_count', $student_count['count']);
        $this->set('student_count_info', $student_count_info);
        $this->set('course_id', $courseID);
		// flag blokovania a comment k tomu
		$this->set('blokovanie_preberania', $this->subjects->isBlockedCopying($courseID));
    }

    /**
     * Unifikovana metoda pre edit a copy.
     * Vykona predbezne kontroly, ziska a anstaveni potrebne data
     * @param int $predmetID - id predmetu
     * @param array $metaPoz - nacitana metapoziadavka
     */
    private function commonEdit($predmetID, $metaPoz)
    {
        // postacuje canSee, edit moze byt pouzite aj na prehliadanie
        if (!$this->canSee($predmetID, "Lecturer", $this->redirect)) return;

        //nastavime si array minulorocnych predmetov
        //TODO: presunut do __getCommonData ??
        //TODO: nebolo by vhodnejsie to dat iba vtedy ked nie je zadana ziadna poziadavka
        // t.j. ked predmet v roku nema metapoziadavku ...
        $crses = new Courses();
        $minule = $crses->getMinulorocne($this->getSemesterID());
        $this->set('minule', $minule);

        // nastavi vseobecne data
        $this->__getCommonData($predmetID);

        // v session si uchova lock na predmet
        $this->session->write($this->getLockName($predmetID), "");
        // defaultne neprebera => prepisat v copy treba
        $this->set('poziadavka_prebrata', 0);

        if (!empty($metaPoz))
        {
            $req = $this->requirements->load($metaPoz["id"]);
            $this->set("requirement", $req["requirement"]);
            $this->set("actualMetaID", $metaPoz["id"]);
        }
    }

    private function getLockName($predmetID)
    {
        return "lock_predmet_{$predmetID}_prednaska";
    }
}

?>
