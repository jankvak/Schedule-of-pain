<?php

/*
 Funkcie na zobrazenie, editovanie a zadavanie poziadaviek garanta
 */

if(!defined('IN_CMS')) {
    exit();
}

class GarantRequirements extends Model {

    var $check = array(
        "skratka" => array(
            "popis"       => "Skratka predmetu",
            "not_empty"   => true,
            "maxlength"   => 30,
            "block_tags"  => true
        ),
        "pred_hod" => array(
            "popis"       => "Rozsah prednášok",
            "not_empty"   => true,
            "is_int"      => true,
            "min_value"   => 0,
            "max_value"   => 15
        ),
        "cvic_hod" => array(
            "popis"       => "Rozsah cvičení",
            "not_empty"   => true,
            "is_int"      => true,
            "min_value"   => 0,
            "max_value"   => 15
        )
    );

    public $id;
    public $skratka;
    public $pred_hod;
    public $cvic_hod;
    public $prednasajuci;
    public $cviciaci;
    // id aktivneho semestra
    public $id_semester;
    private $metaRequests;

    public function __construct() {
    //init DB connectu
        parent::__construct();
        $this->metaRequests = new MetaRequests();
    }

    function load_data($metaPoz, $id) {
        $this->id = $id;
        $this->skratka = $metaPoz["skratka"];
    }

    // vrati poziadavky vzhladom na pouzivatela pre aktivny semester
    function getForUser($userid, $semesterID) {
        $courses = new Courses();
        return $courses->getForUser($userid, $semesterID, 3);
    }

    /**
     * Ulozi nove poziadavky, aj aktualizuje existujuce:
     * - update predmetu
     * - zmazanie stareho cviciaceho a prednasajuceho
     * - pridanie noveho cviciaceho a prednasajuceho
     */
    function save() {
        $this->dbh->transactionBegin();
        $this->__updateCourse();

        // zmaze starsich vyucujucich
        $queryy =
            "DELETE FROM vyucuje_predmet WHERE id_predmet = $1
			 AND id_pedagog_typ IN (SELECT s.id FROM skupina s 
			 WHERE s.code ='Lecturer' OR s.code='Pract')";
        $this->dbh->query($queryy, $this->id);
        ////////

        //TODO: nizsie sa pouzivaju natvrdo konstanty skupin, moze raz nacitat
        // a potom len vyberat ...
        // ak bol prednasajuci nastaveny na "--nie je--" tak to nebudeme ukladat do DB (vymazany je vyssie)
        if ($this->prednasajuci != 0) {
            $query2 =
                "INSERT INTO vyucuje_predmet(id_predmet,id_pedagog,id_pedagog_typ)
				 VALUES($1, $2, (SELECT s.id FROM skupina s WHERE s.code ='Lecturer'))";
            $this->dbh->query($query2, array(
                $this->id, $this->prednasajuci
            ));
        }

        // ak bol cviciaci nastaveny na "--nie je--" tak to nebudeme ukladat do DB (vymazany je vyssie)
        if ($this->cviciaci != 0) {
            $query3 =
                "INSERT INTO vyucuje_predmet(id_predmet,id_pedagog,id_pedagog_typ)
				 VALUES($1, $2, (SELECT s.id FROM skupina s WHERE s.code ='Pract'))";
            $this->dbh->query($query3, array(
                $this->id, $this->cviciaci
            ));
        }

        $this->dbh->transactionEnd();
    }

    // vrati poziadavky pre dany predmet
    function get($id) {
        $query =
            "SELECT id, nazov FROM predmet
			 WHERE id = $1";

        $this->dbh->query($query, $id);
        return $this->dbh->fetch_assoc();
    }

    // pre editovanie poziadaviek na predmet, vytiahne to co uz bolo zadane predtym, teda skratku cviciaceho a prednasajuceho...
    function getReqData($id_predmet) {
    // ziska info o predmete, bude vzdy
        $sql =
            "SELECT p.pred_hod, p.cvic_hod, p.skratka FROM predmet p
             WHERE p.id=$1";
        $this->dbh->query($sql, $id_predmet);
        $res = $this->dbh->fetch_assoc();
        // doplni vyucujucich
        $sql =
            "SELECT vp.id_pedagog, s.code FROM vyucuje_predmet vp
             JOIN skupina s ON s.id=vp.id_pedagog_typ
             WHERE id_predmet=$1 AND s.code <> 'Garant'";
        $this->dbh->query($sql, $id_predmet);
        $vyucuju = $this->dbh->fetchall_assoc();

        // ak nie su zadani vyucujuci, tak bude aj tak prazdne pole
        foreach ($vyucuju as $vyucuje) {
            switch ($vyucuje["code"]) {
                case "Pract": $res["cviciaci"] = $vyucuje["id_pedagog"]; break;
                case "Lecturer": $res["prednasajuci"] = $vyucuje["id_pedagog"]; break;
            }
        }
        return $res;
    }

    function getPedagog($id) {
        $query =
            "SELECT meno, id from pedagog where id=$1";

        $this->dbh->query($query, $id);
        return $this->dbh->fetch_assoc();
    }

    // uloz upravene poziadavky
    function saveEdit() {
        $this->dbh->transactionBegin();
        $this->__updateCourse();

        // TODO idcka natvrdo, existuje lepsie riesenie ?
        $query2 =
            "UPDATE vyucuje_predmet
             SET id_pedagog = $1
             WHERE id_predmet = $2 AND id_pedagog_typ = '4' ";
        $this->dbh->query($query2, array(
            $this->prednasajuci, $this->id
        ));

        $query3 =
            "UPDATE vyucuje_predmet
             SET id_pedagog = $1
             WHERE id_predmet = $2 AND id_pedagog_typ = '5' ";
        $this->dbh->query($query3, array(
            $this->cviciaci, $this->id
        ));

        $this->dbh->transactionEnd();
    }

    function getLecturers() { // vytiahne zoznam vsetkych prednasajucich
        $query =
            "SELECT p.id, p.name, p.last_name, p.titles_before, p.titles_after from person p, person_group c, groups s
             WHERE p.id = c.id_person AND s.id = c.id_group AND s.code = 'Lecturer'
             ORDER by p.last_name";
        $this->dbh->Query($query);
        return $this->dbh->fetchall_assoc();
    }

    function getTeachers() { // vytiahne zoznam vsetkych cviciacich
        $query =
            "SELECT p.id, p.name, p.last_name, p.titles_before, p.titles_after from person p, person_group c, groups s
             WHERE p.id = c.id_person AND s.id = c.id_group AND s.code = 'Pract'
             ORDER by p.last_name";
        $this->dbh->Query($query);
        return $this->dbh->fetchall_assoc();
    }

    /**
     * Aktualizuje informacie predmetu, pouzivane pri add a edit
     */
    private function __updateCourse() {
        $query =
            "UPDATE predmet
             SET skratka = $1,
		 	 pred_hod = $2,
		 	 cvic_hod = $3
             WHERE id = $4";
        $this->dbh->query($query, array(
            $this->skratka, $this->pred_hod, $this->cvic_hod, $this->id
        ));
    }
}
?>
