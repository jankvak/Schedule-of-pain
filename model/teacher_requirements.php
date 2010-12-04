<?php

/*
 poziadavky prednasajucueho
 */

defined('IN_CMS') or die('No access');

class TeacherRequirements extends Model
{
// toto je pole, mozno by bodlo uviest strukturu ako je z pohladu
    public $requirement;
    // ostali aj stare polia aby z HTML rovno vytiahol semester a predmet a nemusel
    // to zistovat aj pri save
    public $course_id;
    // id predchadzajucej meta_poziadavky (na "kopirovanie" diskusie k novo ulozenej poziadavke)
    public $previousMetaID;
    // urcuje ci sa jedna o prebratu poziadavku (hodnota 1), dolezite pri ukladani prebratej poziadavky
    // ak nastane chyba, tak aby sme stale vedeli, ze pracujeme s prebratou poziadavkou
    public $poziadavka_prebrata;
    // typ poziadavky z DB
    private $typ_poziadavky;
    private $typ_role;

    // referencia na model metapoziadavok
    private $metaRequests;

    //to assign a request to actual semester
    private $periods;

    // tak manulne to sem zadavat ako magor nebudem ...
    // konstruktor nageneruje poziadavky pre requirements dynamicky
    public $check = array(
        "requirement" => array(
            "array" => array(
            // doplni sa dynamicky
            )
        )
    );

    public function __construct() {
    //init DB connectu
        parent::__construct();
        $this->periods = new Periods();
        // naplnenie requirements, treba zobrat referenciu aby neskopirovalo
        $ch = &$this->check["requirement"]["array"];
        for ($rozl=1;$rozl<=MAX_ROZLOZENI;$rozl++) {
            $rozlID = chr(ord("a")+$rozl-1);
            for ($poz=1;$poz<=MAX_POZIADAVOK;$poz++) {
                $ch["[layouts][{$rozlID}][requirement][{$poz}][equipment][chair_count]"] = array(
                    "not_empty" => true,
                    "is_int"    => true,
                    "min_value" => 0,
                    "max_value" => 1000,
                    "popis"     => "počet stoličiek v rozložení {$rozl} pre prednášku {$poz}"
                );
                $ch["[layouts][{$rozlID}][requirement][{$poz}][lecture_hours]"] = array(
                    "not_empty" => true,
                    "is_int"    => true,
                    "min_value" => 1,
                    "max_value" => 15,
                    "popis"     => "rozsah prednášky v rozložení {$rozl} pre prednášku {$poz}"
                );
                $ch["[layouts][{$rozlID}][requirement][{$poz}][rooms][students_count]"] = array(
                    "not_empty" => true,
                    "is_int"    => true,
                    "min_value" => 1,
                    "max_value" => 10000,
                    "popis"     => "počet študentov v rozložení {$rozl} pre prednášku {$poz}"
                );
                $ch["[layouts][{$rozlID}][requirement][{$poz}][comment]"] = array(
                    "block_tags"=> true,
                );
            }
        }
        $this->metaRequests = new MetaRequests();
        // ziskame id typu poziadavky z DB pre korektnost
        $this->__nacitajTypy();
    }

    private function __nacitajTypy()
    {
        /*$sql = "SELECT 1";// id FROM poziadavka_typ WHERE nazov='prednaska'";
        $this->dbh->Query($sql);
        $res = $this->dbh->fetch_assoc();
        $this->typ_poziadavky = $res["id"];*/
        $this->typ_poziadavky = 1;

        /*$sql = "SELECT 3";//id FROM skupina WHERE code='Lecturer'";
        $this->dbh->Query($sql);
        $res = $this->dbh->fetch_assoc();
        $this->typ_role = $res["id"];*/
        $this->typ_role = 3;
    }

    //***************************************SAVE*****************************************************

    // uloz poziadavky
    public function save($id_person, $lock)
    {
        /*$this->dbh->TransactionBegin();

        if ($this->metaRequests->existsNewMetaRequest($this->course_id, $this->typ_poziadavky, $lock))
        {
        // je potrebne zrusit transakciu inac bude tabulka zablokovana stale
            $this->dbh->TransactionRollback();
            // vyhod exception aby user vedel ze sa stranka modifikovala
            throw new RequestModified();
        }*/

        // najprv uloz metapoziadavku
        $sql =
            "INSERT INTO event(id_semester, id_course, event_type, confirmed)
                    VALUES ($1,$2,$3,false)";
        //    "INSERT INTO meta_poziadavka (id_predmet, id_osoba, id_poziadavka_typ, cas_pridania)
        //	 VALUES($1, $2, $3, now())";
        $this->dbh->query($sql, array(

            $this->periods->getLastSemesterID(),
            $this->course_id,
            $this->typ_poziadavky
        ));

        $id_event = $this->dbh->GetLastInsertID();

        $sql =
            "INSERT INTO request(id_person, id_event, description)
                    VALUES ($1, $2, $3)";
        $this->dbh->query($sql, array(
            $id_person, $id_event, $this->requirement['komentare']['vseobecne']
        ));

        /*$this->dbh->query($sql, array(
            $this->course_id, $id_person, $this->typ_poziadavky
        ));
        $metaPoziadavkaID = $this->dbh->GetLastInsertID();
        */

        // uloz komentare (posledny parameter
        //Comments::saveComment($metaPoziadavkaID, $this->requirement['komentare']['vseobecne'],1,$id_person);
        //Comments::saveComment($metaPoziadavkaID, $this->requirement['komentare']['sw'],2,$id_person);

        // update komentarov k diskusii, tak aby boli naviazane na najnovsiu poziadavku
        // update vykonat, len ak sme prave nepreberali poziadavku z minuleho roka  ( vtedy by bolo $this->poziadavka_prebrata == 1)
        /*if (!$this->poziadavka_prebrata) {
            Comments::updateComments($metaPoziadavkaID, $this->previousMetaID);
        }*/

        // nasledne uloz rozlozenia
        /*foreach($this->requirement["layouts"] as $layout)
        {
            $this->__saveLayout($layout, $id_person, $metaPoziadavkaID);
        }*/
        //$this->dbh->TransactionEnd();
    }

    private function __saveLayout($layout, $id_person, $metaPoziadavkaID) {
        $query =
            "INSERT INTO rozlozenie(id_meta_poziadavka, pocet_v_tyzdni, \"1\", \"2\", \"3\", \"4\", \"5\", \"6\", \"7\", \"8\", \"9\", \"10\", \"11\", \"12\", \"13\")
             VALUES($1, $2";
        $params = array($metaPoziadavkaID, $layout["lecture_count"]);
        // precykluj poziadavky inteligentne, ciarky davaj pred, tam bude treba vzdy aby
        // sa nemusela kontrolovat na konci ci dat/nedat ciarku
        for ($i=0;$i<=12;$i++)
        {
            $query .= ", $".($i+3);
            $params[] = isset($layout['weeks'][$i]);
        }
        $query .= ")";

        $this->dbh->query($query, $params);
        $id_layout = $this->dbh->GetLastInsertID();

        foreach($layout['requirement'] as $requirement) {
            $this->__saveRequirement($requirement, $id_layout);
        }
    }

    private function __saveRequirement($requirement, $id_layout)
    {
        $query =
            "INSERT INTO poziadavka(id_rozlozenie, rozsah_hodin, sucastne,
             cvic_hned_po_prednaske, cvic_skor_ako_predn, ine) 
             VALUES($1, $2, 0, $3, $4, $5)";

        $this->dbh->query($query, array(
            $id_layout, $requirement['lecture_hours'], isset($requirement['after_lecture']),
            isset($requirement['before_lecture']), $requirement['comment']
        ));

        $requirement_id = $this->dbh->GetLastInsertID();

        $this->__saveEquipment($requirement['equipment'], $requirement_id);
        $this->__saveRooms($requirement['rooms'], $requirement_id);
    }

    // TODO: mozno dakedy daleko v buducnosti:
    // su tu natvrdo len tieto vybavenia ... co takto brat udaje z tabulky vybavenie ?
    // tym padom aj upravit pohlad aby tam daval vsetky + do DB by sa muselo kvoli stolickam dat
    // atribut ci countable, inac len true/false ci treba alebo nie
    private function __saveEquipment($equipment, $requirement_id) {
        if($equipment['notebook']) {
            $this->__insertEquipment($requirement_id, 1, 1);
        }

        if($equipment['beamer']) {
            $this->__insertEquipment($requirement_id, 2, 1);
        }

        if($equipment['chair_count'] > 0) {
            $this->__insertEquipment($requirement_id, 3, $equipment['chair_count']);
        }

    }

    private function __insertEquipment($requirement_id, $equipment_id, $count) {
        $query =
            "INSERT INTO poziadavka_vybavenie(id_poziadavka, id_vybavenie, pocet_kusov)
             VALUES($1, $2, $3)";

        $this->dbh->query($query, array(
            $requirement_id, $equipment_id, $count
        ));
    }

    private function __saveRooms($rooms, $requirement_id) {
        $query =
            "INSERT INTO poziadavka_miestnost(id_poziadavka, pocet_studentov, zelana_kapacita, zelany_typ)
             VALUES($1, $2, $3, 1)";
        $this->dbh->query($query, array(
            $requirement_id, $rooms['students_count'], $rooms['capacity']
        ));

        $rooms_id = $this->dbh->GetLastInsertID();

        foreach($rooms['selected'] as $room) {
            $this->__saveRoom($room, $rooms_id);
        }
    }

    private function __saveRoom($room, $rooms_id) {
        $query =
            "INSERT INTO poziadavka_miestnost_miestnosti(id_poziadavka_miestnost, id_miestnost, sucastne_index)
             VALUES($1, $2, 1)";
        $this->dbh->query($query, array($rooms_id, $room));
    }

    //*************************************LOAD*******************************************************

    /**
     * Nacita data poziadavky danej metapoziadavky.
     * Pre lepsie pouzitie vracia aj metapoziadavku aj poziadavky v takej
     * strukture ako boli nabindovane z pohladu (viacrozmerne pole)
     * @param int $metaPoziadavkaID - ID metapoziadavky
     * @return array(
     * 	"meta_poziadavka" 	=> array (...),
     *  "requirement" 		=> array (...))
     */
    public function load($metaPoziadavkaID)
    {
        // ziskanie metapoziadavky
        $res["meta_poziadavka"] = $this->metaRequests->loadMetaRequest($metaPoziadavkaID);
        // ziskanie komentarov
        $res["requirement"]["komentare"] = $res["meta_poziadavka"]["komentar"];//$this->__loadComments($metaPoziadavkaID);
        //TODO:rozlozenie:$res["requirement"]["layouts"] 	= $this->__loadLayouts($metaPoziadavkaID);

        return $res;
    }

    /**
     * Najde metapoziadavku k zadanej metapoziadavke, ktora bola vytvorena pre nou
     * @param int $id_predmet - ID predmetu
     * @param int $id_poziadavka_typ - typ poziavky (2 cviko, 1 prednaska)
     * @param int $metaPoziadavkaID - ID metapoziadavky
     * @return int - v pripade, ze neexistuje predchadzajuca metapoziadavka vrati null
     */
    public function getPreviousMetaID($id_predmet, $metaPoziadavkaID)
    {
        return $this->metaRequests->getPreviousMetaID($id_predmet, 1, $metaPoziadavkaID);
    }

    /**
     * Najde metapoziadavku k zadanej metapoziadavke, ktora bola vytvorena po nej
     * @param int $id_predmet - ID predmetu
     * @param int $id_poziadavka_typ - typ poziavky (2 cviko, 1 prednaska)
     * @param int $metaPoziadavkaID - ID metapoziadavky
     * @return int - v pripade, ze neexistuje nasledujuca metapoziadavka vrati null
     */
    public function getNextMetaID($id_predmet, $metaPoziadavkaID)
    {
        return $this->metaRequests->getNextMetaID($id_predmet, 1, $metaPoziadavkaID);
    }

    private function __loadComments($metaPoziadavkaID)
    {
        return Comments::loadComments($metaPoziadavkaID);
    }

    private function __loadLayouts($metaPoziadavkaID)
    {
        $sql = "SELECT * FROM rozlozenie WHERE id_meta_poziadavka=$1";
        $this->dbh->query($sql, array($metaPoziadavkaID));
        $layouts = $this->dbh->fetchall_assoc();

        $res = array();
        $layoutIndex = "a";
        foreach ($layouts as &$layout)
        {
            $tayoutOut = array();
            $layoutOut["lecture_count"] = $layout["pocet_v_tyzdni"];
            $layoutOut["weeks"] = $this->__loadWeeks($layout);
            $layoutOut["requirement"] = $this->__loadRequirements($layout["id"]);

            $res[$layoutIndex] = $layoutOut;
            $layoutIndex = chr(ord($layoutIndex)+1);
        }
        return $res;
    }

    private function __loadRequirements($rozlozenieID)
    {
        $sql =
            "SELECT p.* FROM poziadavka p
             WHERE id_rozlozenie=$1";
        $this->dbh->query($sql, array($rozlozenieID));
        $requirements = $this->dbh->fetchall_assoc();

        $res = array();
        $requirementIndex = 1;
        foreach($requirements as $requirement)
        {
            $res[$requirementIndex++] = $this->__loadRequirement($requirement);
        }
        return $res;
    }

    /**
     * Pretransformuje data rozlozenia na do pola weeks pouzite v poli requirement
     * @param <array> $layout - data rozlozenia
     */
    private function __loadWeeks($layout) {
        $weeks = array();
        for ($i=1; $i <= 13; $i++) $weeks[$i-1] = $layout[$i];

        return $weeks;
    }

    /**
     * Pretransformuje data z DB na format pouzity v requirement
     * @param <array> $req - data poziadavky v DB
     */
    private function __loadRequirement($req)
    {
        return array(
            "lecture_hours"     => $req["rozsah_hodin"],
            "after_lecture"     => $req["cvic_hned_po_prednaske"],
            "before_lecture"    => $req["cvic_skor_ako_predn"],
            "comment"           => $req["ine"],
            "rooms"             => $this->__loadRooms($req["id_poziadavka"]),
            "equipment"         => $this->__loadEquipment($req["id_poziadavka"])
        );
    }

    /**
     * Nacita poziadavky na miestnosti pre danu pozidavku
     * @param <int> $reqID - id poziadavky
     * @return <array>  - pole vo formate requirement
     */
    private function __loadRooms($reqID) {
        $sql = "SELECT * FROM poziadavka_miestnost WHERE id_poziadavka=$1";
        $this->dbh->query($sql, array($reqID));
        // prednasky => predpokladam iba jeden zaznam
        $rooms = $this->dbh->fetch_assoc();

        $res = array(
            "students_count"    => $rooms["pocet_studentov"],
            "capacity"          => $rooms["zelana_kapacita"],
            "selected"          => $this->__loadSelectedRooms($rooms["id_poziadavka_miestnost"])
        );

        return $res;
    }

    /**
     * Nacita vybrane miestnosti pre danu poziadavku
     * @param <int> $id_poziadavka_miestnost - id poziadavky
     * @return <array> - pole s vybranymi miestnostami
     */
    private function __loadSelectedRooms($id_poziadavka_miestnost) {
        $sql =
            "SELECT id_miestnost FROM poziadavka_miestnost_miestnosti
             WHERE id_poziadavka_miestnost=$1";
        $this->dbh->query($sql, array($id_poziadavka_miestnost));
        $res = array();
        foreach ($this->dbh->fetchall_assoc() as $selectedRoom) {
            $res[] = $selectedRoom["id_miestnost"];
        }
        return $res;
    }

    /**
     * Nacita udaje o pozadovanom vybaveni pre danu poziadavku
     * @param <int> $reqID - id poziadavky
     * @return <array>  - data vo formate pola requirement
     */
    private function __loadEquipment($reqID) {
    // mohol by spravit aj join ale nakolko vklada podla IDcok to iste spravi aj tu
        $sql = "SELECT pv.* FROM poziadavka_vybavenie pv WHERE pv.id_poziadavka=$1";
        $this->dbh->query($sql, array($reqID));
        $vybavenie = $this->dbh->fetchall_assoc();
        // default hodnoty ak nezadane, uviest vsetky ...
        $res = array(
            "chair_count"   => 0,
            "beamer"        => false,
            "notebook"      => false
        );
        foreach ($vybavenie as $vyb) {
            if ($vyb["id_vybavenie"] == 1) $res["notebook"] = true;
            elseif ($vyb["id_vybavenie"] == 2) $res["beamer"] = true;
            elseif ($vyb["id_vybavenie"] == 3) $res["chair_count"] = $vyb["pocet_kusov"];
        }
        return $res;
    }

    //*********************************OTHER**********************************************************

    //TODO: toto by slo presunut do Courses a tuna nechat len wrapper co preda
    // typ_poziadavky-roli
    public function getCourses($pedagogID, $semester)
    {
    // ziska predmety len ku ktorym moze zadavat a pripoji metapoziadavky
        $courses = new Courses();
        $predmety = $courses->getForUser($pedagogID, $semester, $this->typ_role);
        foreach ($predmety as &$predmet)
        {
            $predmet["meta_poziadavka"] =
                $this->metaRequests->getLastMetaRequest($predmet["id"], $this->typ_poziadavky);
        }
        return $predmety;
    }

    /**
     * Vrati poslednu metapoziadavku k predmetu
     * @param int $predmetID - id predmetu
     * @return array
     */
    public function getLastRequest($predmetID)
    {
        return $this->metaRequests->getLastMetaRequest($predmetID, $this->typ_poziadavky);
    }

    /**
     * Vrati zoznam vsetkych predmetov s ich poslednymi metapoziadavkami v danom semestri
     * @param int $semesterID - id semestra
     * @return array
     */
    public function getAllCoursesLastRequests($semesterID)
    {
        $courses = new Courses();
        return $courses->getAllWithLastRequests($semesterID, $this->typ_poziadavky);
    }

    /**
     * Vrati vsetky zadane metapoziadavky v danom semestri vratane informacii o predmete
     * @param int $semesterID - id semestra
     * @return array
     * @see Courses#getAllWithAllRequests
     */
    public function getAllRequests($semesterID)
    {
        $courses = new Courses();
        return $courses->getAllWithAllRequests($semesterID, $this->typ_poziadavky);
    }

}

?>
