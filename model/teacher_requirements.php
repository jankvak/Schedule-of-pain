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
            "SELECT event.id AS id_event, request.id AS id_request
               FROM request JOIN event ON request.id_event = event.id
              WHERE event.id_course = $1";
        $this->dbh->query($sql, array(
            $this->course_id
        ));
        if ($this->dbh->RowCount()>0) {
            $result = $this->dbh->fetch_assoc();
            $id_event = $result["id_event"];
            $metaPoziadavkaID = $result["id_request"];
        } else {
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

            $metaPoziadavkaID = $this->dbh->GetLastInsertID();
        }

        // uloz komentare (posledny parameter
        //Comments::saveComment($metaPoziadavkaID, $this->requirement['komentare']['vseobecne'],1,$id_person);
        //Comments::saveComment($metaPoziadavkaID, $this->requirement['komentare']['sw'],2,$id_person);

        // update komentarov k diskusii, tak aby boli naviazane na najnovsiu poziadavku
        // update vykonat, len ak sme prave nepreberali poziadavku z minuleho roka  ( vtedy by bolo $this->poziadavka_prebrata == 1)
        /*if (!$this->poziadavka_prebrata) {
            Comments::updateComments($metaPoziadavkaID, $this->previousMetaID);
        }*/

        // nasledne uloz rozlozenia
        foreach($this->requirement["layouts"] as $layout)
        {
            $this->__saveLayout($layout, $id_person, $metaPoziadavkaID);
        }
        $this->__saveRequirement($this->requirement["layouts"]["a"]["requirement"][1], $id_event);
        //$this->__saveEquipment($requirement['equipment'], $requirement_id);
        $this->__saveRooms($this->requirement["layouts"]["a"]["requirement"][1]["rooms"], $metaPoziadavkaID);
        //$this->dbh->TransactionEnd();
    }

    private function __saveLayout($layout, $id_person, $id_request) {
        for ($lecture=0;$lecture<=$layout["lecture_count"];$lecture++) {
            $query =
                "INSERT INTO time_event(id)
                        VALUES (DEFAULT)";
            $this->dbh->query($query);
            $id_time_event = $this->dbh->GetLastInsertID();
            $query =
                "INSERT INTO event_time_event(id_event, id_time_event)
                        SELECT id_event, $2 FROM request WHERE id = $1";
            $this->dbh->query($query, array(
                $id_request, $id_time_event
            ));

            //tu zapisem do db tyzdne, v ktorych sa prednaska nekona
            for ($i=0;$i<=12;$i++)
            {
                if (!isset($layout['weeks'][$i])) {
                    $this->dbh->query(
                        "INSERT INTO time_event_exclusion(id_time_event, \"order\")
                                VALUES($1, $2)",
                        array($id_time_event, $i)
                    );
                }
            }

        }
    }

    private function __saveRequirement($requirement, $id_event)
    {
        $query =
            "UPDATE course
                SET lecture_hours = $2
              WHERE EXISTS(SELECT 1 FROM event WHERE event.id_course = course.id AND event.id = $1)";
            //"INSERT INTO poziadavka(id_rozlozenie, rozsah_hodin, sucastne,
            // cvic_hned_po_prednaske, cvic_skor_ako_predn, ine)
            // VALUES($1, $2, 0, $3, $4, $5)";

        $this->dbh->query($query, array(
            $id_event, $requirement['lecture_hours']
            //$id_layout, $requirement['lecture_hours'], isset($requirement['after_lecture']),
            //isset($requirement['before_lecture']), $requirement['comment']
        ));

        $requirement_id = $this->dbh->GetLastInsertID();
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
            "UPDATE course
                SET student_count = $2
              WHERE EXISTS(
                        SELECT 1
                          FROM event JOIN request ON request.id_event = event.id
                         WHERE event.id_course = course.id
                           AND request.id = $1)";
        $this->dbh->query($query, array(
            $requirement_id, $rooms['students_count']
        ));
        

        foreach($rooms['selected'] as $room) {
            $this->__saveRoom($room, $requirement_id, $rooms);
        }
    }

    private function __saveRoom($room, $requirement_id,$rooms) {

        $query =
            "INSERT INTO request_room(id_request,requested_capacity, requested_type, id_room)
                    VALUES($1, $2,'1',$3)";
        $this->dbh->query($query, array($requirement_id,$rooms['capacity'], $room));
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
        $res["requirement"]["layouts"] = $this->__loadLayouts($metaPoziadavkaID);

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
        $sql = "SELECT course.lecture_count AS pocet_v_tyzdni,
                       NOT \"IsNthEventExcluded\"(event.id, 0) AS \"1\",
                       NOT \"IsNthEventExcluded\"(event.id, 1) AS \"2\",
                       NOT \"IsNthEventExcluded\"(event.id, 2) AS \"3\",
                       NOT \"IsNthEventExcluded\"(event.id, 3) AS \"4\",
                       NOT \"IsNthEventExcluded\"(event.id, 4) AS \"5\",
                       NOT \"IsNthEventExcluded\"(event.id, 5) AS \"6\",
                       NOT \"IsNthEventExcluded\"(event.id, 6) AS \"7\",
                       NOT \"IsNthEventExcluded\"(event.id, 7) AS \"8\",
                       NOT \"IsNthEventExcluded\"(event.id, 8) AS \"9\",
                       NOT \"IsNthEventExcluded\"(event.id, 9) AS \"10\",
                       NOT \"IsNthEventExcluded\"(event.id, 10) AS \"11\",
                       NOT \"IsNthEventExcluded\"(event.id, 11) AS \"12\",
                       NOT \"IsNthEventExcluded\"(event.id, 12) AS \"13\"
                  FROM request JOIN event ON request.id_event = event.id
                               JOIN course ON event.id_course = course.id
                 WHERE request.id=$1";
        $this->dbh->query($sql, array($metaPoziadavkaID));
        $layouts = $this->dbh->fetchall_assoc();

        $res = array();
        $layoutIndex = "a";
        foreach ($layouts as &$layout)
        {
            $tayoutOut = array();
            $layoutOut["lecture_count"] = $layout["pocet_v_tyzdni"];
            $layoutOut["weeks"] = $this->__loadWeeks($layout);
            //$layoutOut["requirement"] = $this->__loadRequirements($layout["id"]);

            $res[$layoutIndex] = $layoutOut;
            $layoutIndex = chr(ord($layoutIndex)+1);
        }
        return $res;
    }

    private function __loadRequirements($rozlozenieID)
    {
        $sql =
            "SELECT p.* FROM request p
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
            "equipment"         => $this->__loadEquipment($req["id_poziadavka"]),
            "software"          => $this->__loadSoftware($reqID["id_poziadavka"])
        );
    }

    /**
     * Nacita poziadavky na miestnosti pre danu pozidavku
     * @param <int> $reqID - id poziadavky
     * @return <array>  - pole vo formate requirement
     */
    private function __loadRooms($reqID) {
//        $sql = "SELECT id AS id_poziadavka_miestnost, id_request AS id_poziadavka, requested_type AS zelany_typ, requested_capacity AS pocet_studentov, requested_capacity AS zelana_kapacita, id_room FROM request_room WHERE id_request=$1";
//        $this->dbh->query($sql, array($reqID));
//        // prednasky => predpokladam iba jeden zaznam
//        $rooms = $this->dbh->fetch_assoc();
//
//        $res = array(
//            "students_count"    => $rooms["pocet_studentov"],
//            "capacity"          => $rooms["zelana_kapacita"],
//            "selected"          => $this->__loadSelectedRooms($rooms["id_poziadavka_miestnost"])
//        );
           $sql = "SELECT id AS id_poziadavka_miestnost, id_request AS id_poziadavka, requested_type AS zelany_typ, requested_capacity AS pocet_studentov, requested_capacity AS zelana_kapacita, id_room FROM request_room WHERE id_request=$1";
        $this->dbh->query($sql, array($reqID));
        // prednasky => predpokladam iba jeden zaznam
        $rooms = $this->dbh->fetchall_assoc();
        foreach ($rooms as $selectedRoom) {
            $roomSel[] = $selectedRoom["id_room"];
        }
        if(!is_null($rooms)){
        $res = array(
            "students_count"    => $rooms[0]["pocet_studentov"],
            "capacity"          => $rooms[0]["zelana_kapacita"],
            "selected"          => $roomSel
        );
        };
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
//        $sql = "SELECT pv.* FROM poziadavka_vybavenie pv WHERE pv.id_poziadavka=$1";
//        $this->dbh->query($sql, array($reqID));
//        $vybavenie = $this->dbh->fetchall_assoc();
//        // default hodnoty ak nezadane, uviest vsetky ...
//        $res = array(
//            "chair_count"   => 0,
//            "beamer"        => false,
//            "notebook"      => false
//        );
//        foreach ($vybavenie as $vyb) {
//            if ($vyb["id_vybavenie"] == 1) $res["notebook"] = true;
//            elseif ($vyb["id_vybavenie"] == 2) $res["beamer"] = true;
//            elseif ($vyb["id_vybavenie"] == 3) $res["chair_count"] = $vyb["pocet_kusov"];
//        }
//        return $res;
         $sql = "SELECT pv.* FROM request_equipment pv WHERE pv.id_request=$1";
        $this->dbh->query($sql, array($reqID));
        $vybavenie = $this->dbh->fetchall_assoc();
        // default hodnoty ak nezadane, uviest vsetky ...
        return $vybavenie;
    }
    private function __loadSoftware($reqID) {
         $sql = "SELECT * FROM request_software WHERE id_request=$1";
        $this->dbh->query($sql, array($reqID));
        $software = $this->dbh->fetchall_assoc();
        // default hodnoty ak nezadane, uviest vsetky ...
        return $software;
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
