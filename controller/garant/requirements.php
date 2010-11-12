<?php

define("HELP_COMMON", "help/garant/requirement/help.php");

class RequirementsController extends AppController {

    protected $access = array('Garant', 'Admin');
    private $redirect = "garant/requirements/index";

    function __construct() {
        parent::__construct();
		$this->subjects = new Subjects();
        $this->requirements = new GarantRequirements();
        $this->helpLink = HELP_COMMON;
    }

    function index() {
        $this->set('requirements',
        $this->requirements->getForUser($this->getUserID(), $this->getSemesterID()));
    }

    /**
     * Metoda predvyplni poziadavky na predmet podla zadaneho minulorocneho predmetu
     * @param int $predmetID - id aktualneho predmetu kam predvyplni
     */
    public function copy($predmetID)
    {
        if (isset($_POST["id_minuly"]))
        {
            $reqData = $this->requirements->getReqData($_POST["id_minuly"]);

            $this->commonEdit($predmetID, $reqData);
            // nastavi az potom lebo common edit dava na false
            $this->set("prebrata", true);
        }else
        {
            // ak neodoslal formular ale manualne zadal URL presmetuje ho na edit
            $this->redirect("garant/requirements/edit/{$predmetID}");
        }
    }


    /**
     * Metoda handluje aj edit aj add poziadavok.
     * Tak ci onak ich musi nacitat, aby vedel ci boli zadane alebo nie a naviac save pre oboje je unikatny
     * @param <int> $predmetID id predmetu
     */
    function edit($predmetID)
    {
        // ziska info o predmete, bude je zadane alebo prazdne
        $reqData = $this->requirements->getReqData($predmetID);
        $this->commonEdit($predmetID, $reqData);
    }

    /**
     * Zabezpeci ulozenie poziadavky. Handluje aj pridanie aj upravu existujucej.
     */
    function save()
    {
        try
        {
            $crses = new Courses();
            $minule = $crses->getMinulorocne($this->getSemesterID());
            $this->set('minule', $minule);

            $checked = $this->bind($this->requirements);

            if (!$this->canEdit($this->requirements->id, "Garant", $this->redirect)) return;
            $this->requirements->save();

            $courseInfo = Subjects::getSubjectInfo($this->requirements->id);

            //poslat notifikaciu
            $this->notificator->sendCourseAssignedMsg($this->requirements);

            $this->log("Vloženie/editácia garantovej požiadavky na predmet `{$courseInfo}`");
            $this->flash('Požiadavky boli uložené.');
            $this->redirect('garant/requirements/index');
        } catch(dataValidationException $ex)
        {
            $this->set('requirements', $ex->checked);
            $this->__commonData($this->requirements->id);
        }
    }

    /**
     * Vlozi do pohladu spolocne data:
     * - prednasajucich
     * - cviciacich
     * - nazov predmetu
     * @param <int> $predmet_id id daneho predmetu
     */
    private function __commonData($predmet_id)
    {
		// flag blokovania a comment k tomu
		$this->set('blokovanie_preberania', $this->subjects->isBlockedCopying($predmet_id));
        // nazov a id predmetu podla id predmetu
        $subject = $this->requirements->get($predmet_id);
        $this->set('predmet', $subject);

        // zoznam cviciach a prednasajuich
        $lecturers = $this->requirements->getLecturers();
        $this->set('lecturers', $lecturers);
        $teachers = $this->requirements->getTeachers();
        $this->set('teachers', $teachers);
        $this->set('course_id', $predmet_id);
    }

    /**
     * Unifikovana metoda pre edit a copy.
     * Vykona predbezne kontroly, ziska a anstaveni potrebne data
     * @param int $predmetID - id predmetu
     * @param array $reqData - nacitane pozaidavky
     */
    private function commonEdit($predmetID, $reqData)
    {
        // postacuje canSee, edit moze byt pouzite aj na prehliadanie
        if (!$this->canSee($predmetID, "Garant", $this->redirect)) return;

        $this->set('requirements', $reqData);

        //nastavime si array minulorocnych predmetov
        //TODO: presunut do __getCommonData ??
        //TODO: again iba ak nie su zadane udaje, nie ?
        $crses = new Courses();
        $minule = $crses->getMinulorocne($this->getSemesterID());
        $this->set('minule', $minule);

        // nastavi vseobecne data
        $this->__commonData($predmetID);
    }
}

?>
