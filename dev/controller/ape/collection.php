<?php
class CollectionController extends AppController
{
    protected $access = array("APE", "Admin");

    private $collection;
    private $periods;

    public function __construct()
    {
        parent::__construct();
        $this->collection 	= new Collection();
        $this->periods		= new Periods();
    }

    public function index()
    {
        $this->set("akcie", $this->collection->getAll());
    }

    public function add()
    {
        $this->set("semestre", $this->periods->getShortAll());
    }

    public function edit($akciaID)
    {
        $this->set("semestre", $this->periods->getShortAll());
        $this->set("akcia", $this->collection->load($akciaID));
    }

    public function delete($akciaID)
    {
        $this->collection->delete($akciaID);
        $this->log("Zmazanie rovrhovej akcie");
        $this->flash("Rozvrhová akcia bola úspešne zmazaná.", "info");
        $this->redirect("ape/collection/index");
    }

    public function save()
    {
        try
        {
            $checked = $this->bind($this->collection);
             
            $kolizia = $this->collection->existujeKolizna();
            if (!is_null($kolizia))
            {
                $this->flash("Zadaná rozvrhová akcia koliduje s inou akciou ({$kolizia}).", "error");
                throw new dataValidationException($checked);
            }
            if (DateConvert::compareSKTimestamp($this->collection->zaciatok, $this->collection->koniec) >= 0)
            {
                $this->flash("Rozvrhová akcia nemôže skončiť skorej ako začala.", "error");
                throw new dataValidationException($checked);
            }
             
            $this->collection->save();
            $this->notificator->sendStartCollectionNotifyToAllUsers($this->collection);

            //TODO: mozno zalogovat aj na aky semester (load podla id a get..Info)
            if (empty($this->collection->id))
            {
                $this->log("Pridanie rozvrhovej akcie");
                $this->flash("Rozvrhová akcia bola pridaná.", "info");
            }else{
                $this->log("Zmena rozvrhovej akcie");
                $this->flash("Rozvrhová akcia bola zmenená.", "info");                
            }
            $this->redirect("ape/collection/index");
        }catch(dataValidationException $ex)
        {
            $this->set("semestre", $this->periods->getShortAll());
            $this->set("akcia", $ex->checked);
        }
    }
}
?>