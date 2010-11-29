<?php

/*
 predmety - priradenie garantov k predmetom
 */

if(!defined('IN_CMS')) {
    exit();
}

class Lessons extends Model {

    public $garant;
    // id aktivneho semestra
    public $id_semester;

    // vrati ID Garanta z tabulky skupina
    function getGarantID() {
        $query="SELECT id FROM group WHERE code='Garant'";
        $this->dbh->Query($query);
        $result=$this->dbh->fetch_assoc();
        return $result['id'];
    }

    // vrati vsetky predmety v aktivnom semestri
    function getSubjects($semesterID) {
        $query="SELECT p.id, p.name, p.code, p.study_programme, sp.name as sp_name
                FROM subject p, study_programme sp
                WHERE p.study_programme = sp.id AND p.id_semester=$1
                ORDER BY p.name";
        $this->dbh->query($query, $semesterID);
        return $this->dbh->fetchall_assoc();
    }

    // vrati vsetkych pedagogov co su garanti nejakemu predmetu
    function getGarants() {
        $query="SELECT person.id,person.name, person.last_name, person.titles_before, person.titles_after
                FROM person_subject
                JOIN person on id_person = person.id
                WHERE role_type = $1
                ORDER BY person.last_name";
        $this->dbh->query($query, $this->getGarantID());
        return $this->dbh->fetchall_assoc();
    }

    // vybera predmety priradene garantovi
    function getCurAssoc() {
        $query="SELECT * FROM person_subject WHERE role_type=$1";
        $this->dbh->query($query, $this->getGarantID());
        return $this->dbh->fetchall_assoc();
    }

    // priradi garanta k predmetu
    function save() {
        $this->dbh->TransactionBegin();
        $garantID = $this->getGarantID();

        //TODO: tu je delete na all mozno komplikovany,
        //je mozne mazat rovno zaznamy vo foreach podla predmetu a potom insertovat ak treba
        //toto potom vyhodi id_semester z modelu+pohladu

        //vymazeme vsetky priradenia pedagogov k predmetom v aktivnom semestri
        $query =
            "DELETE FROM person_subject WHERE role_type=$1
        	 AND (id_subject IN (SELECT id FROM subject WHERE semester = $2))";

        $this->dbh->query($query, array($garantID, $this->id_semester));

        foreach ($this->garant as $gar) {
            if (!$gar['id_garant']) continue;
            $query =
                "INSERT INTO person_subject(id_subject,id_person,role_type)
                 VALUES ($1, $2, $3)";
            $this->dbh->query($query, array(
                $gar['id_predmet'], $gar['id_garant'], $garantID
            ));
        }
        $this->dbh->TransactionEnd();
    }

}
?>