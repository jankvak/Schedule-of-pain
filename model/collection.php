<?php 
class Collection extends Model
{
    public $id;
    public $zaciatok;
    public $koniec;
    public $id_semester;

    public $check = array(
        "zaciatok" => array(
            "not_empty"		=> true,
            "is_datetime"	=> true,
            "popis"			=> "Začiatok"
        ),
        "koniec" => array(
            "not_empty"		=> true,
            "is_datetime"	=> true,
            "popis"			=> "Koniec"
        )
    );

    public function getAll()
    {
        $sql =
            "SELECT ra.id, ".
            DateConvert::DBTimestampToSkDateTime("zaciatok")." AS zaciatok, ".
            DateConvert::DBTimestampToSkDateTime("koniec")." AS koniec, ".
            "s.rok, s.semester
			 FROM rozvrhova_akcia ra
			 JOIN semester s ON s.id=ra.id_semester 
			 ORDER BY zaciatok DESC";
        $this->dbh->Query($sql);

        $akcie = $this->dbh->fetchall_assoc();
        foreach ($akcie as &$akcia)
        {
            $rok1 = $akcia["rok"];
            $rok2 = $rok1+1;
            $semester = ($akcia["semester"] == 1) ? "ZS" : "LS";
            $akcia["semester"] = "{$rok1}/{$rok2} - {$semester}";
            unset($akcia["rok"]);
        }
        return $akcie;
    }

    public function load($akciaID)
    {
        $sql =
            "SELECT id, ".
            DateConvert::DBTimestampToSkDateTime("zaciatok")." AS zaciatok, ".
            DateConvert::DBTimestampToSkDateTime("koniec")." AS koniec, ".
            "id_semester
			 FROM rozvrhova_akcia
			 WHERE id=$1";
        $this->dbh->query($sql, $akciaID);

        return $this->dbh->fetch_assoc();
    }

    public function save()
    {
    // ak sa pridava nove id nie je nastavene => ulozi novu
        if (empty($this->id))
        {
            $sql =
                "INSERT INTO rozvrhova_akcia (zaciatok, koniec, id_semester)
				 VALUES (".
                DateConvert::SKDateTime2DBTimestamp("$1").", ".
                DateConvert::SKDateTime2DBTimestamp("$2").",
				$3)";
            $params = array($this->zaciatok, $this->koniec, $this->id_semester);
        }else
        {
        // save after edit
            $sql =
                "UPDATE rozvrhova_akcia SET
				 zaciatok=".DateConvert::SKDateTime2DBTimestamp("$1").",
				 koniec=".DateConvert::SKDateTime2DBTimestamp("$2").",
				 id_semester=$3
				 WHERE id=$4";
            $params = array($this->zaciatok, $this->koniec, $this->id_semester, $this->id);
        }
        $this->dbh->query($sql, $params);
    }

    public function delete($akciaID)
    {
        $sql = "DELETE FROM rozvrhova_akcia WHERE id=$1";
        $this->dbh->query($sql, $akciaID);
    }

    /**
     * Zisti ci existuje casova kolizia s existujucou poziadavkou
     * @return null ak neexistuje, inac string so zaciatkom a koncom koliznej akcie
     */
    public function existujeKolizna()
    {
    // hlada nasledovne kolizne pripady:
    // - existuje zaciatok nejakeho obdobia v nasom
    // - existuje koniec nejakeho obdobia v nasom
    // - nejake obdobie kompletne prekryva nase obbodie
    // pricom vzdy sa ignoruje nase obdobie (kvoli update ktory by bol kolizny so sebou)
    // a zaroven nie su kolizie ak bezi vaicero akcii na viacero inych semestrov
        $sql =
            "SELECT id, ".
            DateConvert::DBTimestampToSkDateTime("zaciatok"). " AS zaciatok, ".
            DateConvert::DBTimestampToSkDateTime("koniec"). " AS koniec
			FROM rozvrhova_akcia
			 WHERE 
			 	((zaciatok>".DateConvert::SKDateTime2DBTimestamp("$1")." AND
			 	 zaciatok<".DateConvert::SKDateTime2DBTimestamp("$2").") OR	
			 	(koniec>".DateConvert::SKDateTime2DBTimestamp("$1")." AND
			 	 koniec<".DateConvert::SKDateTime2DBTimestamp("$2").") OR
			 	(zaciatok<=".DateConvert::SKDateTime2DBTimestamp("$1")." AND
			 	 koniec>=".DateConvert::SKDateTime2DBTimestamp("$2").")) AND
			 	 id_semester=$3";
        $params = array($this->zaciatok, $this->koniec, $this->id_semester);
        // ak je edit TREBA aj vylucit koliziu so sebou
        if (!empty($this->id))
        {
            $sql .= " AND id<>$4";
            $params[] = $this->id;
        }
        $this->dbh->query($sql, $params);

        if ($this->dbh->RowCount()>0)
        {
            $kolizia = $this->dbh->fetch_assoc();
            return "{$kolizia["zaciatok"]} - {$kolizia["koniec"]}";
        } else return null;
    }

    public function isActiveRozvrhovaAkcia($semesterID)
    {
        $sql =
            "SELECT id FROM rozvrhova_akcia
			 WHERE zaciatok<=CURRENT_TIMESTAMP AND CURRENT_TIMESTAMP<=koniec
			 AND id_semester=$1";
        $this->dbh->query($sql, $semesterID);

        return $this->dbh->RowCount() > 0;
    }

//TODO: get na aktivne obdobia
//TODO: get na planovane obdobia
}
?>