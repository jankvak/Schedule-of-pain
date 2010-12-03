<?php

defined('IN_CMS') or die('No access');

class Courses extends Model {

    public $check = array(

    );

    /**
     * Vrati id predmetu v minulom roku
     * (hlada podla v minulom roku podla semesterID a nazvu predmetu)
     * @param String $predmetNazov - nazov predmetu
     * @param int $semesterID - id daneho semestra (aktualny nie minulorocny)
     * @return id minulorocneho predmetu, -1 ak neexistuje (alebo neexistuje obdobie)
     */
    private function __getPrevPredmetID($predmetNazov, $semesterID)
    {
    //TODO: tu treba v buducnosti ostrit ze ak je zakazane predavanie vrati -1 (bude potrebna extra query na zistenie co je mozne poziadavky prebrat)
        $periods = new Periods();
        $prevPeriodID = $periods->getPrevSemester($semesterID);
        // nemame predosle obdobie, nemame ani id predmetu
        if ($prevPeriodID == -1) return -1;
        $sql =
            "SELECT id FROM course p
 			 WHERE p.id_semester=$1 AND p.nazov=$2";
        $this->dbh->query($sql, array($prevPeriodID, $predmetNazov));
        if ($this->dbh->RowCount() == 0) return -1;
        else{
            $prev_predmet = $this->dbh->fetch_assoc();
            $this->dbh->Release();
            return $prev_predmet["id"];
        }
    }

    /*
     * Zmeni code roli na jej id
     */
    //TODO: lepsie riesenie je pouzit staticky ciselnik ako takto natvrdo
    private function __setRoleID($roleCode){
        if($roleCode == 'Pract') return 5;
        else if($roleCode == 'Lecturer') return 4;
            else if($roleCode == 'Garant') return 3;
        return $roleCode;
    }

    /**
     * Vrati true ak ma pedagog vyssiu rolu ako je pozadovana
     * @param int $requestedRole - pozadovana rola
     * @param array $realRoles - skutocna rola pedagoga
     * @return true ak ma pedagog vyssiu rolu ako je pozadovana
     */
    private function __compareRoles($requestedRole, $realRole){
    // ak som si poslal textovu hodnotu roli tak ju zmenim na id
        $requestedRole = $this->__setRoleID($requestedRole);

        if($requestedRole == $realRole)
            return true;
        //  echo 'skutocna rola:'.  $realRole ;
        switch($requestedRole)
        {
            case 3: if($realRole == 3) return true;
            case 4: if($realRole == 3 || $realRole == 4) return true;
            case 5: if($realRole == 3 || $realRole == 4|| $realRole == 5) return true;
        }
        // ak sa dostanem az sem vratim false
        return false;
    }

    /**
     * Vrati zoznam predmetov ktora dana osoba vyucuje v danej roli
     * @param $userid - id pouzivatela
     * @param $semester - id semestra v akom hlada
     * @param $role - id skupiny (role)
     * @return array([predmet.id, predmet.nazov])
     */
    public function getForUser($userID, $semester, $role) {
    // extra stlpce berie kvoli gerantovi aby mal vsetky potrebne udaje pre rozhodnutie ci boli zadane poziadavky
        $query =
            "SELECT course.id,
                    course.name AS nazov,
                    course.abbreviation AS skratka,
                    course.lecture_hours AS pred_hod,
                    course.exercise_hours cvic_hod,
                    p2c.id_group AS id_pedagog_typ
             FROM   person_course p2c JOIN course ON p2c.id_course = course.id
             WHERE  p2c.id_person=$1
                AND EXISTS (
                        SELECT 1
                        FROM   course_semester c2s
                        WHERE  c2s.id_course = course.id
                           AND c2s.id_semester=$2)
                AND p2c.id_group < 8
             ORDER BY course.name";
        //vp.id_pedagog_typ=$1 AND
        $this->dbh->query($query, array($userID, $semester));
        $predmety = $this->dbh->fetchall_assoc();
        $this->dbh->Release();
        $resultPredmety = array();
        foreach ($predmety as &$predmet)
        {
            $result = $this->__compareRoles($role,$predmet["id_pedagog_typ"]);
            //  echo $result;s
            if($result)
            {
                $predmet["prev_id"] = $this->__getPrevPredmetID($predmet["nazov"], $semester);
                if(array_key_exists($predmet['id'], $resultPredmety) == false)
                    $resultPredmety[$predmet['id']] = $predmet;
            }
            else
            {
            // ak mu nepatri tento predmet tak sa nepriradi ..
            }
        }
        return $resultPredmety;
    }

    public function getAll($semesterID)
    {
        $sql =
            "SELECT course.*
            FROM course JOIN course_semester c2s ON course.id = c2s.id_course
            WHERE c2s.id_semester=$1
	    ORDER BY course.name";
        $this->dbh->query($sql, array($semesterID));

        return $this->dbh->fetchall_assoc();
    }

    /*
     * Vrati nazov predmetu podla zadaneho id
     * @param <int> $courseID - id predmetu
     * @return array([predmet.nazov])
     */
    public function getCourseNameByID($courseID)
    {
        $sql = "SELECT course.nazov FROM course WHERE course.id = $1";
        $this->dbh->query($sql, $courseID);
        return $this->dbh->fetchall_assoc();
    }

    /**
     * Vrati id a nazov vsetkych predmetov z zadaneho semestra
     * @param <int> $semesterID - id semestra
     * @return array([predmet.id, predmet.nazov]) pole vsetkych predmetov z daneho semestra
     */
    public function getAllShort($semesterID)
    {
        $sql =
            "SELECT course.id, course.name
			 FROM course JOIN course_semester c2s ON course.id = c2s.id_course
			 WHERE c2s.id_semester=$1
			 ORDER BY course.name";
        $this->dbh->query($sql, array($semesterID));

        return $this->dbh->fetchall_assoc();
    }

    /**
     * Vrati array so vsetkymi (LS aj ZS) predmetmi z predchadzajuceho roku
     * od zadaneho semestra
     * @param <int> $semesterID - id semestra
     * @return array('LS'=>array([predmet.id,predmet.nazov]), 'ZS'=>array([predmet.id,predmet.nazov]))
     *    pole vsetkych predmetov z predchadzajuceho roku
     *    - ak nejaky z predchadzajucich semestrov nie je evidovany, vrati prazdne pole
     *    pre tento semester
     */
    public function getMinulorocne($semesterID)
    {
        $retArr = array(); //array s minulorocnymi predmetmi

        //zsitime si id minulorocnych semestrov
        $periods = new Periods();
        $semIds = $periods->getPrevYearSemesters($semesterID);

        //ziskame predmety v ZS
        $retArr['ZS'] = array();
        if ($semIds['ZS'] != -1)
        {
            $retArr['ZS'] = $this->getAllShort($semIds['ZS']);
        }

        //ziskame predmety v LS
        $retArr['LS'] = array();
        if ($semIds['LS'] != -1)
        {
            $retArr['LS'] = $this->getAllShort($semIds['LS']);
        }

        return $retArr;
    }

    /**
     * Zisti ci dany pedagog vyucuje dany predmet v danej roli
     * @param <int> $pedagog_id - id pedagoga
     * @param <int> $predmet_id - id predmetu
     * @param <string> $rola - stlpec code v tabulke skupina (Garant, Pract, Lecturer)
     * @return <boolean> true ak dany pedagog vyucuje dany predmet v danej roli
     */
    public function vyucujePredmet($pedagog_id, $predmet_id, $rola) {

    // ak je rola vo forme "code" zmenim na jej id
        $roleID = $this->__setRoleID($rola);

        $sql =
            "SELECT 1
             FROM   person_course
             WHERE  person_course.id_course = $1
                AND person_course.id_person = $2
                AND person_course.id_group <= $3
                AND person_course.id_group IN (2,3,4)";
        $this->dbh->query($sql, array($predmet_id, $pedagog_id, $roleID));
        return $this->dbh->RowCount() > 0;
    }

    /**
     * Vrati zoznam vsetkych predmetov so zadanymi poziadavkami v danom semestri.
     * Sucastou vystupu je aj polozka "meta_poziadavka" s datami poslednej zadanej metapoziadavke
     * k danemu predmetu.
     * @param int $semesterID - id semestra
     * @param int $typPoziadavky - id typu poziadavky (prednasky/cvicenia)
     * @return array
     */
    public function getAllWithLastRequests($semesterID, $typPoziadavky)
    {
        $metaPoziadavky = new MetaRequests();
        $predmety = $this->getAll($semesterID);
        $res = array();
        foreach ($predmety as $predmet)
        {
            $predmet["meta_poziadavka"] =
                $metaPoziadavky->getLastMetaRequest($predmet["id"], $typPoziadavky);
            // zobrazi iba zadane predmety, aby sa nemuselo robit komplikovane SQL a filtrovat
            // iba poslednu poziadavku
            if (!empty($predmet["meta_poziadavka"])) $res[] = $predmet;
        }

        return $res;
    }

    /**
     * Vrati zoznam vsetkych predmetov so vsetkymi zadanymi metapoziadavkami.
     * Ak bolo v predmete zadanych viac metapoziadaviek, vystupom bude
     * viacnasobny vyskyt predmetu, kde rozlicne budu len polozky "meta_poziadavka" reprezentujuce
     * jednotlive metapoziadavky.
     * @param int $semesterID - id semestra
     * @param int $typPoziadavky - id typu poziadavky (prednasky/cvicenia)
     * @return array
     */
    public function getAllWithAllRequests($semesterID, $typPoziadavky)
    {
        $metaRequests = new MetaRequests();
        $predmety = $this->getAll($semesterID);
        $res = array(); //sem vlozi vysledne poziadavky
        foreach ($predmety as $predmet)
        {
            $metaPoziadavky = $metaRequests->getAllRequests($predmet["id"], $typPoziadavky);
            foreach ($metaPoziadavky as $metaPoziadavka)
            {
                $predmet["meta_poziadavka"] = $metaPoziadavka;
                $res[] = $predmet;
            }
        }
        return $res;
    }
}

?>
