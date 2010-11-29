<?php
/*
 editacia informacii o predmetoch co sa vyucuju
 */

if (!defined('IN_CMS')) {
    exit ();
}

class Subjects extends Model {

// korekcia zadanych udajov
    public $check = array (
        "nazov" => array (
            "popis" => "Názov",
            "not_empty" => true,
            "maxlength" => 100,
            "block_tags" => true
        ),
        "kod" => array (
            "popis" => "Kód",
            "not_empty" => true,
            "maxlength" => 30,
            "block_tags" => true
        ),
        "studijny_program" => array (
            "popis" => "Študijný program",
            "not_equal" => 0,
            "not_equal_hlaska" => "Nebol vybraný Študijný program."
        ),
        "sposob_ukoncenia" => array (
            "popis" => "Spôsob ukončenia",
            "not_equal" => 0,
            "not_equal_hlaska" => "Nebol vybraný Spôsob ukončenia."
        )
    );

    public $nazov;
    public $kod;
    // semester v ramci programu kedy je predmet planovany
    public $semester;
    // POZOR: aby korekcia spravne fungovala je dobre ak su nazvy poli v ramci modelu
    // totozne s tymi v DB
    public $studijny_program;
    public $sposob_ukoncenia;
    // id semestra do akeho predmet patri
    public $id_semester;

    // vrati vsetky predmety z aktivneho semestra
    function getSubjects() {
        $query =
            "SELECT p.id, p.nazov, p.kod, p.semester, p.studijny_program, p.sposob_ukoncenia,
             p.blokovat_preberanie, COUNT(id_student) AS studentov
             FROM predmet p
             LEFT JOIN zapisany_predmet zp ON zp.id_predmet=p.id
             WHERE id_semester=$1
             GROUP BY p.id, p.nazov, p.kod, p.semester, p.studijny_program, p.sposob_ukoncenia, p.blokovat_preberanie
             ORDER BY nazov";
        $this->dbh->query($query, array($this->id_semester));
        return $this->dbh->fetchall_assoc();
    }

    // vrati informacie o danom predmete
    function getSubject($id) {
        $query = "SELECT * FROM predmet WHERE id=$1";
        $this->dbh->query($query, array($id));
        return $this->dbh->fetch_assoc();
    }

    // vymaze dany predmet
    function delete($id) {
        $query = "DELETE FROM predmet WHERE id=$1";
        $this->dbh->query($query, array($id));
    }

    // vrati vsetky studijne programy
    function getPrograms() {
        $query = "SELECT * FROM studijny_program";
        $this->dbh->Query($query);
        return $this->dbh->fetchall_assoc();
    }

    // vrati vsetky sposoby ukoncenia
    function getExamTypes() {
        $query = "SELECT * FROM sposob_ukoncenia";
        $this->dbh->Query($query);
        return $this->dbh->fetchall_assoc();
    }

    // uloz novy predmet
    function save() {
        $query =
            "INSERT INTO predmet (id_semester, nazov, kod, semester, studijny_program, sposob_ukoncenia)
				 VALUES ($1, $2, $3, $4, $5, $6)";
        $this->dbh->query($query, array(
            $this->id_semester, $this->nazov, $this->kod, $this->semester,
            $this->studijny_program, $this->sposob_ukoncenia
        ));
    }

    // zmen informacie existujuceho predmetu
    function saveEdited($id) {
        $query =
            "UPDATE predmet
				 SET nazov=$1, kod=$2, semester=$3, 
				 studijny_program=$4, sposob_ukoncenia=$5
				 WHERE id=$6";
        $this->dbh->query($query, array(
            $this->nazov, $this->kod, $this->semester, $this->studijny_program,
            $this->sposob_ukoncenia, $id
        ));
    }

    // vrati true ak dany kod uz existuje
    function kodExists($semesterID) {
    // konfliktne kody v inom obdobi mozu existovat
        if ($this->id)
        {
            $query = "SELECT kod FROM predmet WHERE kod=$1 AND id!=$2 AND id_semester=$3";
            $params = array($this->kod, $this->id, $semesterID);
        }else{
            $query = "SELECT kod FROM predmet WHERE kod=$1 AND id_semester=$2";
            $params = array($this->kod, $semesterID);
        }
        $this->dbh->query($query, $params);
        return $this->dbh->RowCount() == 1;
    }

    public static function getSubjectInfo($id_predmet) {
        $dbh = Connection::get();

        $query = "SELECT nazov,kod FROM predmet WHERE id = $1";

        $dbh->query($query, array($id_predmet));
        if ($dbh->RowCount()>0)
        {
            $predmet = $dbh->fetch_assoc();
            $dbh->Release();

            return "{$predmet["nazov"]}({$predmet["kod"]})";
        }else return "";
    }

    // vrati pocet studentov zapisanych na dany predmet
    public function getStudentCount($id_predmet) {
        $query = "SELECT count(id) as count from zapisany_predmet where id_predmet = $1";
        $this->dbh->query($query, array($id_predmet));
        return $this->dbh->fetch_assoc();
    }

    public function getStudentCountInfo($id_predmet) {
    // nie je potrebne filtrovat aj podla semestra, lebo predmet ako taky
    // je specificky pre kazdy semester (kazdy semester ma ine id)
        $query =
            "SELECT student.rocnik, studijny_program.nazov, COUNT(*) AS student_count
             FROM student
             LEFT JOIN studijny_program ON student.id_studijny_program=studijny_program.id
             JOIN zapisany_predmet ON zapisany_predmet.id_student=student.id
             WHERE zapisany_predmet.id_predmet = $1 
             GROUP BY student.id_studijny_program, student.rocnik, studijny_program.nazov
             ORDER BY student.id_studijny_program";
        $this->dbh->query($query, array($id_predmet));
        return $this->dbh->fetchall_assoc();
    }

    //prida vsetky predmety, ktore este nie su v databaze z minuleho semestra do aktualneho semestra,
    //predmet je odlisny, ak ma iny KOD
    public function saveLastPeriodSubjects($actualPeriodID,$prevPeriodID)
    {
        $garantID = $this->getGarantID();

        $query =
            "SELECT predmet.nazov, predmet.semester, predmet.kod, predmet.sposob_ukoncenia, predmet.studijny_program, predmet.id_semester, vyucuje_predmet.id_pedagog
             FROM predmet, vyucuje_predmet
             WHERE (predmet.id = vyucuje_predmet.id_predmet AND id_semester = $1  AND id_pedagog_typ=$2)";

        $this->dbh->query($query, array($prevPeriodID, $garantID));

        $predmety = $this->dbh->fetchall_assoc();

        $pocetPredmetov = 0;
        $pocetGarantov = 0;

        foreach($predmety as $predmet){
        //echo($predmet['nazov']);
            if (!$this->existSubjectCode($predmet['kod'], $actualPeriodID))
            {
                $this->saveSubject($predmet, $actualPeriodID);
                $predmetID = $this->dbh->GetLastInsertID();
                if (isset($predmet['id_pedagog']))
                {
                    $this->saveGarant($predmetID, $predmet['id_pedagog'], $garantID);
                    $pocetGarantov++;
                }
                $pocetPredmetov++;
            }
        }

        $result = array();
        $result['pocetPredmetov'] = $pocetPredmetov;
        $result['pocetGarantov'] = $pocetGarantov;

        return $result;
    }

    // metoda meni flag blokovania preberania pre konkretny predmet
    public function changeSubjectsBlockStatus($subjectId, $block) {
        $sql = "UPDATE predmet SET blokovat_preberanie=$1 WHERE id=$2";
        $this->dbh->query($sql, array($block, $subjectId));
    }

    // pri blokovani je mozne zadat aj komentar a ten sa tu uklada
    public function saveSubjectsBlockComment($subjectId, $comment) {
        $sql = "UPDATE predmet SET dovod_blokovania=$1 WHERE id=$2";
        $this->dbh->query($sql, array($comment, $subjectId));
    }

    // Vrati ci je alebo nie blokovane preberanie poziadaviek a komentar k tomu
    public function isBlockedCopying($subjectId) {
        $sql = "SELECT blokovat_preberanie, dovod_blokovania FROM predmet WHERE id=$1";
        $this->dbh->query($sql, array($subjectId));

        return $this->dbh->fetch_assoc();
    }

    private function existSubjectCode($code, $actualPeriodID){

        $query = "SELECT kod FROM predmet WHERE (kod='$1' AND id_semester=$2)";
        $this->dbh->query($query , array($code, $actualPeriodID));

        return $this->dbh->RowCount() >= 1;
    }

    private function getGarantID() {
        $query="SELECT id FROM skupina WHERE code='Garant'";
        $this->dbh->Query($query);
        $result=$this->dbh->fetch_assoc();
        return $result['id'];
    }

    private function saveSubject($predmet,$actualPeriodID) {

        $query =
            "INSERT INTO predmet (nazov,semester,kod,sposob_ukoncenia,studijny_program,id_semester)
			 		 VALUES ($1, $2, $3, $4, $5, $6)";
        $this->dbh->query($query, array(
            $predmet['nazov'], $predmet['semester'],
            $predmet['kod'], $predmet['sposob_ukoncenia'],
            $predmet['studijny_program'], $actualPeriodID
        ));
    }

    private function saveGarant($predmetID,$pedagogID,$garantID){
        $query =
            "INSERT INTO vyucuje_predmet (id_predmet,id_pedagog,id_pedagog_typ)
					 VALUES ($1, $2, $3)";
        $this->dbh->query($query, array(
            $predmetID, $pedagogID, $garantID
        ));
    }
}
?>