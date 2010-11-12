<?php

class EquipmentController extends AppController {

    protected $access = array('APE', 'Admin');
    private $equipment;
    
    function __construct() {
		parent::__construct();    	
    	$this->equipment = new Equipment();
    }

    function index() {
        $all = $this->equipment->getAll();
        $this->set('equipment', $all);
    }

    function edit($id) {
        $equipment = $this->equipment->get($id);
        $this->set('equipment', $equipment);
    }

    function add() {
    }

    function update($id) {
        try {
            $this->bind($this->equipment);

            if ($this->_exists()) return;

            $this->equipment->update();
            $this->log("Úprava vybavenia `{$this->equipment->typ}`");
            $this->flash('Vybavenie bolo upravené.', 'info');
            $this->redirect('ape/equipment/index');
        }
        catch(dataValidationException $ex){
            $checked = $ex->checked;
            $checked["id"] = $id;
            $this->_invalid_data($checked);
        }
    }

    function delete($id) {
        $equipment = $this->equipment->get($id);
        $this->log("Zmazanie vybavenia `{$equipment["typ"]}`");

        $this->equipment->delete($id);

        $this->flash('Vybavenie bolo vymazané.');
        $this->redirect('ape/equipment/index');
    }

    function save() {
        try {
            $this->bind($this->equipment);

            // ak existuje treba iny typ ...
            if ($this->_exists()) return;

            $this->equipment->save();
            $this->log("Pridanie vybavenia `{$this->equipment->typ}`");
            $this->flash('Vybavenie bolo pridané.');
            $this->redirect('ape/equipment/index');
        }
        catch(dataValidationException $ex){
            $this->_invalid_data($ex->checked);
        }
    }

    // nakrmi dany view datami co boli OK + doplni zvysne potrebne
    private function _invalid_data(&$checked) {
    // nastavi vsetky veci co zadal korektne
        $this->set('equipment', $checked);
    }

    // skontroluje ci take vybavenie neexistuje ak ano presmeruje na jeho editaciu
    private function _exists() {
    // taky typ uz existuje ?
        $id = $this->equipment->getID();
        if ($id > 0) {
            $this->flash("Také vybavenie už existuje.<br/>Presmerované na jeho editáciu.", "error");
            $this->redirect("ape/equipment/edit/$id");
        }
    }
}

?>
