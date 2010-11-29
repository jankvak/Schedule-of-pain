<?php

class SuggestionController extends AppController {

    protected $access = array("All");

    function __construct()
    {
        parent::__construct();
        $this->suggestions = new Suggestions();
    }

    /**
     * Pripravi data pre pohlad na zoznam pripomienok daneho pouzivatela.
     */
    function index()
    {
        $all = $this->suggestions->getAllFromUser($this->session->read('uid'));
        $this->set('suggestions', $all);
    }

    /**
     * Pripravi data pre pohlad na pridanie novej pripomienky.
     */
    function add()
    {
        $suggestion_types = $this->suggestions->getTypes();
        $this->set('suggestion_types', $suggestion_types);
    }

    /**
     * Ulozi novu pripomienku.
     */
    function save()
    {
        try {
            $checked = $this->bind($this->suggestions);

            $this->suggestions->save($this->getUserID());
            //notifikujeme admina o pridani pripomienky
            $this->notificator->sendSuggestionAddedMsg($this->suggestions);
            //zalogujeme a oznamime pouzivatelovi
            $this->log("Vloženie pripomienky");
            $this->flash('Pripomienka vložená', 'info');
            $this->redirect('all/suggestion/index');
        }
        catch(dataValidationException $ex) {
            $this->_invalid_data($ex->checked);
        }
    }

    /**
     * Pripravi data pre pohlad v pripade zle vyplnenych dat vo formulari.
     */
    private function _invalid_data(&$checked)
    {
        $this->set('suggestion', $checked);
        $suggestion_types = $this->suggestions->getTypes();
        $this->set('suggestion_types', $suggestion_types);
    }
}
?>
