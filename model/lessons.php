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
        $query="SELECT id FROM skupina WHERE code='Garant'";
        $this->dbh->Query($query);
        $result=$this->dbh->fetch_assoc();
        return $result['id'];
    }

    // vrati vsetky predmety v aktivnom semestri
    function getSubjects($semesterID) {
        $query="SELECT p.id, p.nazov, p.kod, p.studijny_program, sp.nazov as sp_nazov
                FROM predmet p, studijny_program sp
                WHERE p.studijny_program = sp.id AND p.id_semester=$1
                ORDER BY nazov";
        $this->dbh->query($query, $semesterID);
        return $this->dbh->fetchall_assoc();
    }

    // vrati vsetkych pedagogov co su garanti nejakemu predmetu
    function getGarants() {
        $query="SELECT pedagog.id,pedagog.meno, pedagog.priezvisko, pedagog.tituly_pred, pedagog.tituly_za
                FROM clenstvo
                JOIN pedagog on id_pedagog = pedagog.id
                WHERE id_skupina = $1
                ORDER BY pedagog.priezvisko";
        $this->dbh->query($query, $this->getGarantID());
        return $this->dbh->fetchall_assoc();
    }

    // vybera predmety priradene garantovi
    function getCurAssoc() {
        $query="SELECT * FROM vyucuje_predmet WHERE id_pedagog_typ=$1";
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
            "DELETE FROM vyucuje_predmet WHERE id_pedagog_typ=$1
        	 AND (id_predmet IN (SELECT id FROM predmet WHERE id_semester = $2))";

        $this->dbh->query($query, array($garantID, $this->id_semester));

        foreach ($this->garant as $gar) {
            if (!$gar['id_garant']) continue;
            $query =
                "INSERT INTO vyucuje_predmet(id_predmet,id_pedagog,id_pedagog_typ)
                 VALUES ($1, $2, $3)";
            $this->dbh->query($query, array(
                $gar['id_predmet'], $gar['id_garant'], $garantID
            ));
        }
        $this->dbh->TransactionEnd();
    }

}
?>