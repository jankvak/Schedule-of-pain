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
            "SELECT s.id, s.name, s.code, s.semester, s.study_programme, s.termination_method,
             COUNT(id_person) AS students
             FROM subject s
             LEFT JOIN person_subject zp ON zp.id_subject=s.id
             WHERE (id_semester=$1 AND zp.role_type='s')
             GROUP BY s.id, s.name, s.code, s.semester, s.study_programme, s.termination_method
             ORDER BY name";
        $this->dbh->query($query, array($this->id_semester));
        return $this->dbh->fetchall_assoc();
    }

    // vrati informacie o danom predmete
    function getSubject($id) {
        $query = "SELECT * FROM subject WHERE id=$1";
        $this->dbh->query($query, array($id));
        return $this->dbh->fetch_assoc();
    }

    // vymaze dany predmet
    function delete($id) {
        $query = "DELETE FROM subject WHERE id=$1";
        $this->dbh->query($query, array($id));
    }

    // vrati vsetky studijne programy
    function getPrograms() {
        $query = "SELECT * FROM study_programme";
        $this->dbh->Query($query);
        return $this->dbh->fetchall_assoc();
    }

    // vrati vsetky sposoby ukoncenia
    function getExamTypes() {
        $query = "SELECT DISTINCT termination_method FROM subject";
        $this->dbh->Query($query);
        return $this->dbh->fetchall_assoc();
    }

    // uloz novy predmet TODO : semester???
    function save() {
        $query =
            "INSERT INTO predmet (id_semester, name, code, semester, study_programme, termination_method)
				 VALUES ($1, $2, $3, $4, $5, $6)";
        $this->dbh->query($query, array(
            $this->id_semester, $this->nazov, $this->kod, $this->semester,
            $this->studijny_program, $this->sposob_ukoncenia
        ));
    }

    // zmen informacie existujuceho predmetu
    function saveEdited($id) {
        $query =
            "UPDATE subject
				 SET name=$1, code=$2, semester=$3, 
				 study_programme=$4, termination_method=$5
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
            $query = "SELECT code FROM subject WHERE code=$1 AND id!=$2 AND semester=$3";
            $params = array($this->kod, $this->id, $semesterID);
        }else{
            $query = "SELECT code FROM subject WHERE code=$1 AND semester=$2";
            $params = array($this->kod, $semesterID);
        }
        $this->dbh->query($query, $params);
        return $this->dbh->RowCount() == 1;
    }

    public static function getSubjectInfo($id_predmet) {
        $dbh = Connection::get();

        $query = "SELECT name,code FROM subject WHERE id = $1";

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
        $query = "SELECT count(id) as count from person_subject where id_subject = $1 AND role_type='s'";
        $this->dbh->query($query, array($id_predmet));
        return $this->dbh->fetch_assoc();
    }

    public function getStudentCountInfo($id_predmet) {
    // nie je potrebne filtrovat aj podla semestra, lebo predmet ako taky
    // je specificky pre kazdy semester (kazdy semester ma ine id)
        $query =
            "SELECT person.grade, study_programme.name, COUNT(*) AS student_count
             FROM person
             LEFT JOIN study_programme ON person_study_programme.id=study_programme.id
             JOIN person_subject ON person_subject.id_person=person.id
             WHERE (person_subject.id_subject = $1 AND person_subject.role_type='s') 
             GROUP BY person_study_programme.id_study_programme, person.grade, study_programme.name
             ORDER BY person_subject.id_study_programme";
        $this->dbh->query($query, array($id_predmet));
        return $this->dbh->fetchall_assoc();
    }

    //prida vsetky predmety, ktore este nie su v databaze z minuleho semestra do aktualneho semestra,
    //predmet je odlisny, ak ma iny KOD
    public function saveLastPeriodSubjects($actualPeriodID,$prevPeriodID)
    {
        $garantID = $this->getGarantID();
//todo : predmet study programme
        $query =
            "SELECT subject.name, subject.semester, subject.code, subject.termination_method,  subject.semester, person_subject.id_person
             FROM subject, person_subject
             WHERE (subject.id = person_subject.id_subject AND semester = $1  AND person_subject.role_type='p')";

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

    // metoda meni flag blokovania preberania pre konkretny predmet //depricated
    public function changeSubjectsBlockStatus($subjectId, $block) {
        $sql = "UPDATE subject SET blokovat_preberanie=$1 WHERE id=$2";
        $this->dbh->query($sql, array($block, $subjectId));
    }

    // pri blokovani je mozne zadat aj komentar a ten sa tu uklada//depricated
    public function saveSubjectsBlockComment($subjectId, $comment) {
        $sql = "UPDATE predmet SET dovod_blokovania=$1 WHERE id=$2";
        $this->dbh->query($sql, array($comment, $subjectId));
    }

    // Vrati ci je alebo nie blokovane preberanie poziadaviek a komentar k tomu//depricated
    public function isBlockedCopying($subjectId) {
        $sql = "SELECT blokovat_preberanie, dovod_blokovania FROM predmet WHERE id=$1";
        $this->dbh->query($sql, array($subjectId));

        return $this->dbh->fetch_assoc();
    }

    private function existSubjectCode($code, $actualPeriodID){

        $query = "SELECT code FROM subject WHERE (code='$1' AND semester=$2)";
        $this->dbh->query($query , array($code, $actualPeriodID));

        return $this->dbh->RowCount() >= 1;
    }

    private function getGarantID() {
        $query="SELECT id FROM group WHERE code='g'";
        $this->dbh->Query($query);
        $result=$this->dbh->fetch_assoc();
        return $result['id'];
    }

    private function saveSubject($predmet,$actualPeriodID) {

        $query =
            "INSERT INTO subject (name,semester,code,termination_method,study_programme)
			 		 VALUES ($1, $2, $3, $4, $5)";
        $this->dbh->query($query, array(
            $predmet['nazov'], $predmet['semester'],
            $predmet['kod'], $predmet['sposob_ukoncenia'],
            $predmet['studijny_program']
        ));
    }

    private function saveGarant($predmetID,$pedagogID,$garantID){
        $query =
            "INSERT INTO person_subject (id_subject,id_person,role_type)
					 VALUES ($1, $2, $3)";
        $this->dbh->query($query, array(
            $predmetID, $pedagogID, $garantID
        ));
    }
}
?>