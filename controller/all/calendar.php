<?php

class CalendarController extends AppController {

    protected $access = array("All");

    function __construct()
    {
        parent::__construct();
        $this->calendar = new Calendar();
    }

    /**
     * Pripravi data pre pohlad na udalosti (kalendar).
     */
    function index()
    {
        //TODO: preco je naviazanie na semester ???
        // nebolo by korektnejsie zobrazit vsetky udalosti ?
        // ked bude scrollovat dalej do historie aby videl aj minulorocne bez toho aby musel prepinat semester
        $events = $this->calendar->getAllEvents($this->getSemesterID());
        $this->set('events', $events);
    }
}
?>
