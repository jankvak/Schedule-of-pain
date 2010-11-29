<?php
class SuggestionsController extends AppController
{
    protected $access = array('Admin');

    function __construct()
    {
        parent::__construct();
        $this->suggestions = new Suggestions();
    }

    /**
     * Pripravi data pre pohlad na zoznam pripomienok.
     */
    function index()
    {
        $all = $this->suggestions->getAll();
        $this->set('suggestions', $all);
    }

    /**
     * Pripravi data na pohlad pre editaciu pripomienky.
     * @param $id - id pripomienky
     */
    function edit($id)
    {
        $suggestion = $this->suggestions->get($id);
        $this->set('suggestion', $suggestion);
        $nazvy_stavov = $this->suggestions->getNazvyStavov();
        $this->set('nazvyStavov', $nazvy_stavov);
        $suggestion_types = $this->suggestions->getTypes();
        $this->set('suggestion_types', $suggestion_types);
    }

    /**
     * Upravi danu pripomienku - z pohladu admina, cize upravi spatnu vazbu
     * k pripomienke.
     * @param $id - id pripomienky
     */
    function saveEdited($id)
    {
        try
        {
            $checked = $this->bind($this->suggestions);
            $this->suggestions->editSpatnaVazba($id);

            //posleme notifikaciu
            $this->notificator->sendSuggestionChangedMsg($this->suggestions);
            //zalogujeme a oznamime pouzivatelovi
            $this->log("Úprava spätnej väzby k pripomienke");
            $this->flash('Pripomienka upravená', 'info');
            $this->redirect('administrator/suggestions/index');
        }
        catch(dataValidationException $ex)
        {
            $this->_invalid_data($ex->checked);
        }
    }

    /**
     * Pripravi data pre pohlad v pripade chybne zadanych dat vo formulari
     */
    private function _invalid_data(&$checked)
    {
        $this->set('pripomienka', $checked);
        $suggestion_types = $this->suggestions->getTypes();
        $this->set('suggestion_types', $suggestion_types);
    }

    /**
     * Vymaze danu pripomienku.
     * @param $id - id pripomienky
     */
    function delete($id)
    {
        $this->suggestions->delete($id);
        $this->log("Vymazanie pripomienky");
        $this->flash("Pripomienka bola vymazaná");
        $this->redirect('administrator/suggestions/index');
    }

}
?>
