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
        $query="SELECT id FROM groups WHERE code='Garant' AND group_type='1'";
        $this->dbh->Query($query);
        $result=$this->dbh->fetch_assoc();
        return $result['id'];
    }

    // vrati vsetky predmety v aktivnom semestri
    function getSubjects($semesterID) {
        $query="SELECT p.id, p.name AS nazov, p.code AS kod, csp.id_study_programme, sp.name AS sp_nazov
                FROM course p
                JOIN course_semester cs ON cs.id_course=p.id 
                JOIN course_study_programme csp ON csp.id_course=p.id
                JOIN study_programme sp ON csp.id_study_programme=sp.id
                WHERE cs.id_semester=$1
                ORDER BY p.name";
        $this->dbh->query($query, $semesterID);
        return $this->dbh->fetchall_assoc();
    }

    // vrati vsetkych pedagogov co su garanti nejakemu predmetu
    function getGarants() {
        $query="SELECT p.id, p.name AS meno, p.last_name AS priezvisko, p.titles_before AS tituly_pred, p.titles_after AS tituly_za
                FROM person p
                WHERE EXISTS(
                        SELECT 1
                        FROM   person_course pc JOIN groups g on pc.id_group=g.id
                        WHERE  code = 'Garant' AND pc.id_person = p.id)
                ORDER BY p.last_name";
        $this->dbh->query($query);
        return $this->dbh->fetchall_assoc();
    }

    // vybera predmety priradene garantovi
    function getCurAssoc() {
        $query="SELECT id, id_person AS id_pedagog, id_course AS id_predmet, id_group AS id_pedagog_typ FROM person_course WHERE id_group=$1";
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
            "DELETE FROM person_course WHERE id_group=$1
        	 AND (id_course IN (SELECT c.id FROM course c
        	 JOIN course_semester cs ON cs.id_semester=c.id WHERE cs.id_semester = $2))";

        $this->dbh->query($query, array($garantID, $this->id_semester));

        foreach ($this->garant as $gar) {
            if (!$gar['id_garant']) continue;
            $query =
                "INSERT INTO person_course(id_course,id_person,id_group)
                 VALUES ($1, $2, $3)";
            $this->dbh->query($query, array(
                $gar['id_course'], $gar['id_garant'], $garantID
            ));
        }
        $this->dbh->TransactionEnd();
    }

}
?>