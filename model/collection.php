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
            "SELECT event.id, ".
                DateConvert::DBTimestampToSkDateTime("time_event.start")." AS zaciatok, ".
                DateConvert::DBTimestampToSkDateTime("time_event.end")." AS koniec, ".
            "   semester.year AS rok,
                semester.semester_order AS semester
            FROM event JOIN semester ON semester.id = event.id_semester
                       JOIN event_time_event e2t ON event.id = e2t.id_event
                       JOIN time_event ON e2t.id_time_event = time_event.id
            ORDER BY time_event.start DESC";
        $this->dbh->Query($sql);

        $akcie = $this->dbh->fetchall_assoc();
        foreach ($akcie as &$akcia)
        {
            $rok1 = $akcia["rok"];
            $rok2 = $rok1+1;
            $semester = ($akcia["semester"] == 'Z') ? "ZS" : "LS";
            $akcia["semester"] = "{$rok1}/{$rok2} - {$semester}";
            unset($akcia["rok"]);
        }
        return $akcie;
    }

    public function load($akciaID)
    {
        $sql =
            "SELECT event.id, ".
                    DateConvert::DBTimestampToSkDateTime("time_event.start")." AS zaciatok, ".
                    DateConvert::DBTimestampToSkDateTime("time_event.end")." AS koniec, ".
            "       event.id_semester
            FROM    event JOIN event_time_event e2t ON event.id = e2t.id_event
                          JOIN time_event ON e2t.id_time_event = time_event.id
            WHERE   event.id=$1";
        $this->dbh->query($sql, $akciaID);

        return $this->dbh->fetch_assoc();
    }

    public function save()
    {
    // ak sa pridava nove id nie je nastavene => ulozi novu
        if (empty($this->id))
        {
            $sql =
                "INSERT INTO time_event(id, start, \"end\")
                        VALUES (DEFAULT," .
                                DateConvert::SKDateTime2DBTimestamp("$1").", ".
                                DateConvert::SKDateTime2DBTimestamp("$2").")";
            $this->dbh->query($sql, array(
                $this->zaciatok, $this->koniec
            ));
            $id_time_event = $this->dbh->GetLastInsertID();

            $sql =
                "INSERT INTO event(id, id_semester)
                        VALUES (DEFAULT, $1)";
            $this->dbh->query($sql, array($this->id_semester));
            $id_event = $this->dbh->GetLastInsertID();

            $sql =
                "INSERT INTO event_time_event(id_event, id_time_event)
                        VALUES ($1, $2)";
            $this->dbh->query($sql, array($id_event, $id_time_event));
        }else
        {
        // save after edit
            $sql =
                "UPDATE event SET
                        id_semester=$1
		 WHERE id=$2";
            $this->dbh->query($sql, array($this->id, $this->id_semester));
            $sql =
                "UPDATE time_event SET
                    start = $1,
                    \"end\" = $2
                 WHERE EXISTS(
                        SELECT 1
                        FROM   event JOIN event_time_event e2t ON event.id = e2t.id_event
                        WHERE  e2t.id_time_event = time_event.id
                           AND event.id = $3)";
            $this->dbh->query($sql, array($this->zaciatok, $this->koniec, $this->id));
        }
    }

    public function delete($akciaID)
    {
        $sql = "DELETE FROM event WHERE id=$1";
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
        return null;
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
            "SELECT event.id
             FROM   event JOIN event_time_event e2t ON event.id = e2t.id_event
                          JOIN time_event ON e2t.id_time_event = time_event.id
             WHERE  event.id_semester = $1
                AND time_event.start < CURRENT_TIMESTAMP
                AND (CURRENT_TIMESTAMP - time_event.end)/time_event.recur_count < time_event.recur_freq * INTERVAL '1 DAY'";
        $this->dbh->query($sql, $semesterID);
        //TODO:: my zatial nepouzivame rozvrhove akcie, treba doplnit.
        return true;
        return $this->dbh->RowCount() > 0;
    }

//TODO: get na aktivne obdobia
//TODO: get na planovane obdobia
}
?>