<?php

defined('IN_CMS') or die('No access');

class Periods extends Model
{
    public $id;
    public $rok;
    public $semester;
    public $zac_uc;
    public $kon_uc;
    public $zac_skus;
    public $zac_opr;
    public $kon_skus;

    public $check = array(
        "rok" 		=> array(
            "popis"		=> "akademický rok",
            "is_int"	=> true,
            "not_empty" => true,
            "min_value"	=> 1970,
            "max_value"	=> 2500
        ),
        "semester" 	=> array(
            // neda sa kontrolovat na dve pripustne hodnoty
            "semester"	=> "semester",
            "not_empty" => true,
        ),
        "zac_uc"	=> array(
            "popis"		=> "začiatok semestra",
            "not_empty" => true,
            "is_date"	=> true
        ),
        "kon_uc"	=> array(
            "popis"		=> "koniec semestra",
            "not_empty" => true,
            "is_date"	=> true
        ),
        "zac_skus"	=> array(
            "popis"		=> "začiatok skúškového",
            "not_empty" => true,
            "is_date"	=> true
        ),
        "zac_opr"	=> array(
            "popis"		=> "začiatok opravných skúšok",
            "not_empty" => true,
            "is_date"	=> true
        ),
        "kon_skus"	=> array(
            "popis"		=> "koniec skúškového",
            "not_empty" => true,
            "is_date"	=> true
        )
    );

    /**
     * Ziska zoznam vsetkych evidovanych semestrov utriedenych podla rokov a semestrov zostupne
     * @return array
     */
    public function getAll()
    {
        $sql =
            "SELECT id, rok, semester, ";
        $sql .= DateConvert::DBtoSK("zac_uc")." AS zac_uc,";
        $sql .= DateConvert::DBtoSK("kon_uc")." AS kon_uc,";
        $sql .= DateConvert::DBtoSK("zac_skus")." AS zac_skus,";
        $sql .= DateConvert::DBtoSK("zac_opr")." AS zac_opr,";
        $sql .= DateConvert::DBtoSK("kon_skus")." AS kon_skus ";
        $sql .=
            "FROM semester
			 ORDER BY rok DESC, semester DESC";
        $this->dbh->Query($sql);
        return $this->dbh->fetchall_assoc();
    }

    /**
     * Ziska skrateny prehlad semestrov, len id, rok a semester
     * @return array utriedene zostupne podla rokov a semestrov
     */
    public function getShortAll()
    {
        $sql =
            "SELECT id, rok, semester
			 FROM semester
			 ORDER BY rok DESC, semester DESC";
        $this->dbh->Query($sql);
        return $this->dbh->fetchall_assoc();
    }

    /**
     * Nacita data daneho semestra
     * @param $semesterID - id semestra
     * @return array
     */
    public function load($semesterID)
    {
        $sql =
            "SELECT id, rok, semester, ";
        $sql .= DateConvert::DBtoSK("zac_uc")." AS zac_uc,";
        $sql .= DateConvert::DBtoSK("kon_uc")." AS kon_uc,";
        $sql .= DateConvert::DBtoSK("zac_skus")." AS zac_skus,";
        $sql .= DateConvert::DBtoSK("zac_opr")." AS zac_opr,";
        $sql .= DateConvert::DBtoSK("kon_skus")." AS kon_skus ";
        $sql .=
            "FROM semester
             WHERE id=$1";
        $this->dbh->query($sql, $semesterID);

        return $this->dbh->fetch_assoc();
    }

    public function save()
    {
        if (empty($this->id))
        {
            $sql =
                "INSERT INTO semester(rok, semester, zac_uc, kon_uc, zac_skus, zac_opr, kon_skus)
			 VALUES ($1, $2, ";
            $sql .= DateConvert::SKtoDB("$3").",";
            $sql .= DateConvert::SKtoDB("$4").",";
            $sql .= DateConvert::SKtoDB("$5").",";
            $sql .= DateConvert::SKtoDB("$6").",";
            $sql .= DateConvert::SKtoDB("$7").")";

            $this->dbh->query($sql, array(
                $this->rok, $this->semester, $this->zac_uc,
                $this->kon_uc, $this->zac_skus, $this->zac_opr, $this->kon_skus
            ));
        }else{
            $sql =
                "UPDATE semester
                 SET rok=$1, 
                 semester=$2, 
                 zac_uc=".DateConvert::SKtoDB("$3").",
                 kon_uc=".DateConvert::SKtoDB("$4").",
                 zac_skus=".DateConvert::SKtoDB("$5").", 
                 zac_opr=".DateConvert::SKtoDB("$6").", 
                 kon_skus=".DateConvert::SKtoDB("$7")."
                 WHERE id=$8";

            $this->dbh->query($sql, array(
                $this->rok, $this->semester, $this->zac_uc,
                $this->kon_uc, $this->zac_skus, $this->zac_opr,
                $this->kon_skus, $this->id
            ));
        }
    }

    /**
     * Vrati true ak dany semester v systeme uz existuje (ten isty rok a semester)
     * @return boolean
     */
    public function semesterExistuje()
    {
        $sql =
            "SELECT rok, semester
			 FROM semester
			 WHERE semester=$1 AND rok=$2";
        $params = array($this->semester, $this->rok);
        if (!empty($this->id))
        {
            $sql .= " AND id<>$3";
            $params[] = $this->id;
        }

        $this->dbh->query($sql, $params);
        return $this->dbh->RowCount() > 0;
    }

    /**
     * Vrati id posledneho semestra
     * @return int
     */
    public function getLastSemesterID()
    {
        $sql = "SELECT id FROM semester ORDER BY rok DESC, semester DESC LIMIT 1";
        $this->dbh->Query($sql);
        $lastSem = $this->dbh->fetch_assoc();
        $this->dbh->Release();

        return $lastSem["id"];
    }

    /**
     * Vrati id semestra o rok predchadzajuceho danemu (o rok dozadu ten isty semester)
     * @param int $semesterID - id daneho semestra
     * @return int - id predosleho semestra, -1 ak taky neexistuje
     */
    public function getPrevSemester($semesterID)
    {
    // ziska udaje o dabin semestri aby vedel ziskat predosly
        $sql = "SELECT rok, semester FROM semester WHERE id=$1";
        $this->dbh->query($sql, array($semesterID));
        $prev_semester = $this->dbh->fetch_assoc();
        $prev_semester["rok"] -= 1;
        $sql =
            "SELECT id FROM semester
			 WHERE rok=$1 AND semester=$2";
        $this->dbh->query($sql, array($prev_semester["rok"], $prev_semester["semester"]));
        if ($this->dbh->RowCount() == 0) return -1;
        else{
            $prev_semester = $this->dbh->fetch_assoc();
            $this->dbh->Release();
            return $prev_semester["id"];
        }
    }

    /**
     * Vrati idecka semestrov z minuleho roku vzhladom na dany semester
     * @param int $semesterID - id daneho semestra
     * @return array('LS'=>id;'ZS'=>id) - id je id semestra alebo -1 ak taky neexistuje
     */
    public function getPrevYearSemesters($semesterID)
    {
    // ziska udaje o dabin semestri aby vedel ziskat predosly
        $sql = "SELECT rok FROM semester WHERE id=$1";
        $this->dbh->query($sql, array($semesterID));
        $prev_semester = $this->dbh->fetch_assoc();
        $prev_semester["rok"] -= 1;
        $sql =
            "SELECT id FROM semester
			 WHERE rok=$1 AND semester=$2";
        //najprv zimny
        $this->dbh->query($sql, array($prev_semester["rok"],1));
        if ($this->dbh->RowCount() == 0) $retArr['ZS'] = -1;
        else{
            $prev_semester = $this->dbh->fetch_assoc();
            $this->dbh->Release();
            $retArr['ZS'] = $prev_semester["id"];
        }
        //teraz letny
        $this->dbh->query($sql, array($prev_semester["rok"],2));
        if ($this->dbh->RowCount() == 0) $retArr['LS'] = -1;
        else{
            $prev_semester = $this->dbh->fetch_assoc();
            $this->dbh->Release();
            $retArr['LS'] = $prev_semester["id"];
        }
        return $retArr;
    }

    /**
     * Vrati skratenu informaciu o semestri - rok/rok - semester
     * @return unknown_type
     */
    public function getSemesterInfo()
    {
        return self::skratenyPopis($this->rok, $this->semester);
    }

    /**
     * Vrati skrateny popis semestra vo formate rok/rok - XS
     * @param <int> $rok
     * @param <int> $semester
     */
    public static function skratenyPopis($rok, $semester)
    {
        $rok1 = $rok;
        $rok2 = $rok1+1;
        $sem = $semester == 1 ? "ZS" : "LS";

        return "{$rok1}/{$rok2} - {$sem}";
    }
}
?>