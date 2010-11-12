<?php

class RoomsController extends AppController {

    protected $access = array('APE', 'Admin');

    function __construct() 
    {
		parent::__construct();
    	$this->rooms = new Rooms();
        $this->room_type = new RoomType();
    }

    function index() {
        $all = $this->rooms->getAll();
        $this->set('rooms', $all);
    }

    function edit($id) {
        $room = $this->rooms->get($id);
        $room_types = $this->room_type->getAll();
        $equip = $this->rooms->getRoomEquipment($id);
        $equips = $this->rooms->getEquipment();
        $this->set('equips', $equips);
        $this->set('equip', $equip);
        $this->set('room_types', $room_types);
        $this->set('room', $room);
    }

    function save() {
        try {
            $checked = $this->bind($this->rooms);
            //
            $this->__roomExists($checked);
            ////
            $this->rooms->save();
            $this->log("Miestnosť `{$this->rooms->nazov}` pridaná");
            $this->flash('Miestnost pridaná.');
            $this->redirect('ape/rooms/index');
        }
        catch(dataValidationException $ex) {
            $this->_invalid_data($ex->checked);
        }
    }
    function saveEdited() {
        try {
            $checked = $this->bind($this->rooms);
            //
            $this->__roomExists($checked);

            $this->rooms->saveEdited();
            $this->log("Úprava miestnosti `{$this->rooms->nazov}`");
            $this->flash('Miestnosť upravená.');
            $this->redirect('ape/rooms/index');
        }
        catch(dataValidationException $ex) {
            $this->_invalid_data($ex->checked);
        }
    }

    private function _invalid_data(&$checked) {
    // nastavi vsetky veci co zadal korektne
        $this->set('room', $checked);
        // nasledne dve nie su previazane na hodnoty v $this->rooms =>
        // ich pouzitie je korektne
        $room_types = $this->room_type->getAll();
        $this->set('room_types', $room_types);
        $equips = $this->rooms->getEquipment();
        $this->set('equips', $equips);
        $this->set('equip', $_POST['vybavenie']);
    }

    function delete($id) {
        $room = $this->rooms->get($id);
        $this->log("Odstránenie miestnosti `{$room["nazov"]}`");

        $this->rooms->delete($id);
        $this->flash('Miestnosť vymazaná.');
        $this->redirect('ape/rooms/index');
    }

    function add() {
        $room_types = $this->room_type->getAll();
        $this->set('room_types', $room_types);
        $equips = $this->rooms->getEquipment();
        $this->set('equips', $equips);
    }

    private function __roomExists(&$checked) {
        $name_temp = $this->rooms->getRoomName($this->rooms->nazov, $this->rooms->id);
        if ($name_temp['nazov'] != "") {
            $this->flash('Zadaný už existujúci názov miestnosti.', 'error');
            unset($checked['nazov']);
            throw new dataValidationException($checked);
        }
    }
}

?>
