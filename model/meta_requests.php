<?php

defined('IN_CMS') or die('No access');

class RequestModified extends Exception
{

}

class MetaRequests extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Nacita danu metapoziadavku. Vystupom budu data metapoziadavky aj
     * - predmet_nazov - nazov predmetu
     * - pedagogog - kto zadal poziadavku (normalny format)
     * @param int $metaPoziadavkaID
     * @return array
     */
    public function loadMetaRequest($metaPoziadavkaID)
    {
        $sql =
            "SELECT course.id AS id_predmet,
                    person.id AS id_osoba,
                    event.event_type AS id_poziadavka_typ,
                    course.name AS predmet_nazov, ".
                    DateConvert::DBTimestampToSkDateTime("request.timestamp")." AS cas_pridania, ".
                    Users::vyskladajMeno("person", "pedagog", false). ", ".
             "      request.description AS komentar
             FROM  request JOIN event ON request.id_event = event.id
                            JOIN course ON event.id_course = course.id
                            JOIN person ON request.id_person = person.id
             WHERE  request.id = $1";
            /*"SELECT mp.id_predmet, mp.id_osoba, mp.id_poziadavka_typ,
			        pr.nazov as predmet_nazov, ".
            DateConvert::DBTimestampToSkDateTime("mp.cas_pridania")." AS cas_pridania, ".
            Users::vyskladajMeno("p", "pedagog", false).
            "FROM meta_poziadavka mp
        	 JOIN pedagog p ON p.id=mp.id_osoba
        	 JOIN predmet pr ON pr.id=mp.id_predmet
			 WHERE mp.id=$1";*/
        $this->dbh->query($sql, $metaPoziadavkaID);

        // urcite sa vrati iba jeden zaznam lebo sa hlada podla id
        return $this->dbh->fetch_assoc();
    }

    /**
     * Ziska poslednu metapoziadavku k predmetu.
     * Meno bude v standardnom formate (titulky meno priezvisko tituly).
     * Poznamka: Ziskava sa posledna meta poziadavka k predmetu, predmet je viazany
     * na semester, preto sa tu nevyskytuje.
     * @param $predmetID - id predmetu
     * @param $typPoziadavky - o aky typ poziadavky sa jedna (prednaska, cvika)
     * @return array
     */
    public function getLastMetaRequest($predmetID, $typPoziadavky) {
        $query =
            "SELECT request.id,
                    event.id_course,".
                    DateConvert::DBTimestampToSkDateTime("request.timestamp")." AS cas_pridania, ".
                    Users::vyskladajMeno("person", "pedagog", false).
            "FROM   request JOIN event ON request.id_event = event.id
                            JOIN person ON request.id_person = person.id
             WHERE  event.id_course = $1 AND event.event_type = $2
             ORDER BY request.timestamp DESC
             LIMIT 1";
        $this->dbh->query($query, array(
            $predmetID, $typPoziadavky
        ));

        return $this->dbh->fetch_assoc();
    }

    public function getAllRequests($predmetID, $typPoziadavky)
    {
        $query =
            "SELECT request.id,
                    event.id_course,".
                    DateConvert::DBTimestampToSkDateTime("request.timestamp")." AS cas_pridania, ".
                    Users::vyskladajMeno("person", "pedagog", false).
            "FROM   request JOIN event ON request.id_event = event.id
                            JOIN person ON request.id_person = person.id
             WHERE  event.id_course = $1 AND event.event_type = $2
             ORDER BY request.timestamp DESC";
        $this->dbh->query($query, array(
            $predmetID, $typPoziadavky
        ));

        return $this->dbh->fetchall_assoc();
    }

    public function existsNewMetaRequest($predmetID, $typPoziadavky, $lastMetaID)
    {

        $sql = "LOCK TABLE meta_poziadavka IN ACCESS EXCLUSIVE MODE";
        $this->dbh->Query($sql);
        $lastMetaPoz = $this->getLastMetaRequest($predmetID, $typPoziadavky);

        // ak pridava prvu mozu nasta dve situacie
        if (empty($lastMetaID))
        {
        // ak existuje nejaka metapoziadavka tak uz niekto pridal
            return !empty($lastMetaPoz);
        }

        // vrati true ak poziadavka bola zmenena
        return $lastMetaPoz["id"] != $lastMetaID;
    }

    /**
     * Najde metapoziadavku k zadanej metapoziadavke, ktora bola vytvorena pre nou
     * @param int $id_predmet - ID predmetu
     * @param int $id_poziadavka_typ - typ poziavky (2 cviko, 1 prednaska)
     * @param int $metaPoziadavkaID - ID metapoziadavky
     * @return int - v pripade, ze neexistuje predchadzajuca metapoziadavka vrati null
     */
    public function getPreviousMetaID($id_predmet, $id_poziadavka_typ, $metaPoziadavkaID)
    {
         $query =
            "SELECT max(request.id) AS id
            FROM   request JOIN event ON request.id_event = event.id
                            JOIN person ON request.id_person = person.id
             WHERE  event.id_course = $1 AND event.event_type = $2
";
   //     $sql = "SELECT max(id) AS id FROM request WHERE (id_predmet=$1 AND id_poziadavka_typ=$2 AND id<$3)";
        $this->dbh->query($query, array($id_predmet,$id_poziadavka_typ));
        $result = $this->dbh->fetchall_assoc();

        return $result[0]['id'];
    }

    /**
     * Najde metapoziadavku k zadanej metapoziadavke, ktora bola vytvorena po nej
     * @param int $id_predmet - ID predmetu
     * @param int $id_poziadavka_typ - typ poziavky (2 cviko, 1 prednaska)
     * @param int $metaPoziadavkaID - ID metapoziadavky
     * @return int - v pripade, ze neexistuje nasledujuca metapoziadavka vrati null
     */
    public function getNextMetaID($id_predmet, $id_poziadavka_typ, $metaPoziadavkaID)
    {
       // $sql = "SELECT min(id) AS id FROM meta_poziadavka WHERE (id_predmet=$1 AND id_poziadavka_typ=$2 AND id>$3)";
        //$this->dbh->query($sql, array($id_predmet,$id_poziadavka_typ,$metaPoziadavkaID));
        $query =
            "SELECT min(request.id) AS id
            FROM   request JOIN event ON request.id_event = event.id
                            JOIN person ON request.id_person = person.id
             WHERE  event.id_course = $1 AND event.event_type = $2
";
   //     $sql = "SELECT max(id) AS id FROM request WHERE (id_predmet=$1 AND id_poziadavka_typ=$2 AND id<$3)";
        $this->dbh->query($query, array($id_predmet,$id_poziadavka_typ));
        $result = $this->dbh->fetchall_assoc();

        return $result[0]['id'];
    }

}
?>
