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
            "SELECT s.id, s.name, s.code, cs.id_semester, sp.id_study_programme, s.termination_method,
             COUNT(id_person) AS students
             FROM course s
             LEFT JOIN person_course zp ON zp.id_course=s.id
             LEFT JOIN course_study_programme sp ON sp.id_course=s.id
             LEFT JOIN course_semester cs ON cs.id_course=s.id
             WHERE id_semester=$1
             GROUP BY s.id, s.name, s.code, cs.id_semester, sp.id_study_programme, s.termination_method
             ORDER BY name";
        $this->dbh->query($query, array($this->id_semester));
        return $this->dbh->fetchall_assoc();
    }

    // vrati informacie o danom predmete
    function getSubject($id) {
        $query = "SELECT * FROM course WHERE id=$1";
        $this->dbh->query($query, array($id));
        return $this->dbh->fetch_assoc();
    }

    // vymaze dany predmet
    function delete($id) {
        $query = "DELETE FROM course WHERE id=$1";
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
        $query = "SELECT DISTINCT termination_method FROM course";
        $this->dbh->Query($query);
        return $this->dbh->fetchall_assoc();
    }

    // uloz novy predmet TODO : semester???
    function save() {
        $query =
            "INSERT INTO course (id_semester, name, code, study_programme, termination_method)
				 VALUES ($1, $2, $3, $4, $5, $6)";
        $this->dbh->query($query, array(
            $this->id_semester, $this->nazov, $this->kod,
            $this->studijny_program, $this->sposob_ukoncenia
        ));
    }

    // zmen informacie existujuceho predmetu
    function saveEdited($id) {
        $query =
            "UPDATE subject
				 SET name=$1, code=$2, 
				 study_programme=$3, termination_method=$4
				 WHERE id=$5";
        $this->dbh->query($query, array(
            $this->nazov, $this->kod, $this->studijny_program,
            $this->sposob_ukoncenia, $id
        ));
    }

    // vrati true ak dany kod uz existuje
    function kodExists($semesterID) {
    // konfliktne kody v inom obdobi mozu existovat
        if ($this->id)
        {
            $query = "SELECT code FROM course 
            JOIN course_semester ON course.id=course_semester.id_course
            WHERE code=$1 AND id!=$2 AND id_semester=$3";
            $params = array($this->kod, $this->id, $semesterID);
        }else{
            $query = "SELECT code FROM subject 
            JOIN course_semester ON course.id=course_semester.id_course
            WHERE code=$1 AND id_semester=$2";
            $params = array($this->kod, $semesterID);
        }
        $this->dbh->query($query, $params);
        return $this->dbh->RowCount() == 1;
    }

    public static function getSubjectInfo($id_predmet) {
        $dbh = Connection::get();

        $query = "SELECT name,code FROM course WHERE id = $1";

        $dbh->query($query, array($id_predmet));
        if ($dbh->RowCount()>0)
        {
            $predmet = $dbh->fetch_assoc();
            $dbh->Release();

            return "{$predmet["name"]}({$predmet["code"]})";
        }else return "";
    }

    // vrati pocet studentov zapisanych na dany predmet
    public function getStudentCount($id_predmet) {
        $query = "SELECT count(id) as count 
        FROM person_course 
        where id_course = $1 AND id_group='8'";
        $this->dbh->query($query, array($id_predmet));
        return $this->dbh->fetch_assoc();
    }

    public function getStudentCountInfo($id_predmet) {
    // nie je potrebne filtrovat aj podla semestra, lebo predmet ako taky
    // je specificky pre kazdy semester (kazdy semester ma ine id)
        $query =
            "SELECT person.grade, study_programme.name, COUNT(*) AS student_count
             FROM person
             LEFT JOIN study_programme ON person_study_programme.id_person=person.id_person
             JOIN person_course ON person_course.id_person=person.id
             JOIN person_group ON person.id=person_group.id_person
             WHERE (person_course.id_course = $1 AND person_group.id_group='8') 
             GROUP BY person_study_programme.id_study_programme, person.grade, study_programme.name
             ORDER BY person_course.id_study_programme";
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
            "SELECT c.name, c.semester, c.code, c.termination_method,  csp.id_study_programme, cs.id_semester, person_course.id_person
             FROM course c, person_course
             JOIN course_study_programme csp on csp.id_course=c.id 
             JOIN course_semester cs on csp.id_course=c.id
             WHERE (c.id = person_course.id_course AND semester = $1  AND person_course.id_group=$2)";

        $this->dbh->query($query, array($prevPeriodID, $garantID));

        $predmety = $this->dbh->fetchall_assoc();

        $pocetPredmetov = 0;
        $pocetGarantov = 0;

        foreach($predmety as $predmet){
        //echo($predmet['nazov']);
            if (!$this->existSubjectCode($predmet['code'], $actualPeriodID))
            {
                $this->saveSubject($predmet, $actualPeriodID);
                $predmetID = $this->dbh->GetLastInsertID();
                if (isset($predmet['id_person']))
                {
                    $this->saveGarant($predmetID, $predmet['id_person'], $garantID);
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
        //$sql = "UPDATE course SET blokovat_preberanie=$1 WHERE id=$2";
       // $this->dbh->query($sql, array($block, $subjectId));
    }

    // pri blokovani je mozne zadat aj komentar a ten sa tu uklada//depricated
    public function saveSubjectsBlockComment($subjectId, $comment) {
       // $sql = "UPDATE predmet SET dovod_blokovania=$1 WHERE id=$2";
       // $this->dbh->query($sql, array($comment, $subjectId));
    }

    // Vrati ci je alebo nie blokovane preberanie poziadaviek a komentar k tomu//depricated
    public function isBlockedCopying($subjectId) {
        //$sql = "SELECT blokovat_preberanie, dovod_blokovania FROM predmet WHERE id=$1";
        //$this->dbh->query($sql, array($subjectId));

        //return $this->dbh->fetch_assoc();
    }

    private function existSubjectCode($code, $actualPeriodID){

        $query = "SELECT code FROM course WHERE (code='$1' AND semester=$2)";
        $this->dbh->query($query , array($code, $actualPeriodID));

        return $this->dbh->RowCount() >= 1;
    }

    private function getGarantID() {
        $query="SELECT id FROM groups WHERE code='Garant'";
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
            "INSERT INTO person_subject (id_subject,id_person,id_group)
					 VALUES ($1, $2, $3)";
        $this->dbh->query($query, array(
            $predmetID, $pedagogID, $garantID
        ));
    }
}
?>