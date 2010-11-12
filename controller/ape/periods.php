<?php
class PeriodsController extends AppController
{
    protected $access = array('APE', 'Admin');
    private $periods;

    public function __construct()
    {
        parent::__construct();
        $this->periods = new Periods();
    }

    public function index()
    {
        $this->set("semestre", $this->periods->getAll());
    }

    public function add()
    {
    }

    public function edit($semesterID)
    {
        $this->set("semester", $this->periods->load($semesterID));
    }

    public function save()
    {
        try
        {
            $checked = $this->bind($this->periods);
            if ($this->periods->semesterExistuje())
            {
                $this->flash("Taký semester už existuje.", "error");
                throw new dataValidationException($checked);
            }
            $this->periods->save();

            $semesterInfo = $this->periods->getSemesterInfo(); 
            if (empty($this->periods->id))
            {
                $this->flash("Semester úspešne pridaný.", "info");
                $this->log("Pridanie semestra `{$semesterInfo}`");
            }else{
                $this->log("Zmena semestra `{$semesterInfo}`");                
                $this->flash("Semester úspešne upravený.", "info");
            }
            $this->redirect("ape/periods/index");
        }catch(dataValidationException $ex)
        {
            $this->set("semester", $ex->checked);
        }
    }
}
?>