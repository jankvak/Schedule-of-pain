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

            "SELECT course.id,
                    course.name AS name,
                    course.code AS code,
                    c2s.id_semester AS semester,
                    c2sp.id_study_programme AS studijny_program,
                    course.termination_method AS sposob_ukoncenia,
                    false AS blokovat_preberanie,
                    (SELECT COUNT(1)
                     FROM   person_course
                     WHERE  person_course.id_course = course.id
                        AND person_course.id_group = '8') AS studentov,
'' AS dovod_blokovania
             FROM   course JOIN course_semester c2s ON course.id = c2s.id_course
                           JOIN course_study_programme c2sp ON course.id = c2sp.id_course
             WHERE  c2s.id_semester = $1
             ORDER BY course.name";
        $this->dbh->query($query, array($this->id_semester));
        return $this->dbh->fetchall_assoc();
    }

    // vrati informacie o danom predmete
    function getSubject($id) {
        $query = "SELECT c.id, s.semester_order AS semester, csp.id_study_programme AS studijny_program, c.termination_method AS sposob_ukoncenia,
		c.name AS nazov, c.abbreviation AS skratka, cs.id_semester AS semester, c.lecture_hours AS pred_hod, c.exercise_hours AS cvic_hod,
		c.lecture_count AS prednas_pocet, c.lecture_count AS cvic_pocet, c.student_count AS student_poc, c.exercise_capacity AS kapacita_cvic,
		c.code AS kode, true AS blokovat_preberanie,'' AS dovod_blokovania
        FROM course c 
        JOIN course_study_programme csp ON csp.id_course=c.id
        JOIN course_semester cs ON cs.id_course = c.id
        JOIN semester s ON cs.id_semester = s.id
         WHERE c.id=$1";
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
        $query = "SELECT id, name AS nazov FROM study_programme";
        $this->dbh->Query($query);
        return $this->dbh->fetchall_assoc();
    }

    // vrati vsetky sposoby ukoncenia
    function getExamTypes() {
        $query = "SELECT DISTINCT termination_method AS nazov, termination_method AS id FROM course GROUP BY nazov";
        $this->dbh->Query($query);
        return $this->dbh->fetchall_assoc();
    }

    // uloz novy predmet
    function save() {
        $query =
            "INSERT INTO course (id, name, code, termination_method)
                    VALUES (DEFAULT, $1, $2, $3) RETURNING id";
        $this->dbh->query($query, array(
            $this->nazov, $this->kod, $this->sposob_ukoncenia
        ));
        
        //$id_course = $this->dbh->fetch();
        $id_course=$this->dbh->GetLastInsertID();
        $query =
            "INSERT INTO course_semester (id_course, id_semester)
                   VALUES ($1, $2)";
        $this->dbh->query($query, array(
            $id_course, $this->id_semester
        ));
        $query =
            "INSERT INTO course_study_programme (id_course, id_study_programme)
                    VALUES ($1, $2)";
        $this->dbh->query($query, array(
            $id_course, $this->studijny_program
        ));
    }

    // zmen informacie existujuceho predmetu
    function saveEdited($id) {
        $query =
            "UPDATE course
                SET name = $1,
                    code = $2,
                    termination_method = $3
              WHERE id = $4";
        $this->dbh->query($query, array(
            $this->nazov, $this->kod, $this->sposob_ukoncenia, $id
        ));
        /*
        $query =
            "UPDATE predmet
				 SET nazov=$1, kod=$2, semester=$3, 
				 studijny_program=$4, sposob_ukoncenia=$5
				 WHERE id=$6";
        $this->dbh->query($query, array(
            $this->nazov, $this->kod, $this->semester, $this->studijny_program,
            $this->sposob_ukoncenia, $id
        ));*/
    }

    // vrati true ak dany kod uz existuje
    function kodExists($semesterID) {
    // konfliktne kody v inom obdobi mozu existovat
        if ($this->id)
        {
            $query = "SELECT code
                      FROM   course JOIN course_semester c2s ON course.id = c2s.id_course
                      WHERE  course.code = $1
                         AND course.id != $2
                         AND c2s.id_semester = $3";
            $params = array($this->kod, $this->id, $semesterID);
        }else{
            $query = "SELECT code
                      FROM   course JOIN course_semester c2s ON course.id = c2s.id_course
                      WHERE  course.code = $1
                         AND c2s.id_semester = $2";
            $params = array($this->kod, $semesterID);
        }
        $this->dbh->query($query, $params);
        return $this->dbh->RowCount() == 1;
    }

    public static function getSubjectInfo($id_predmet) {
        $dbh = Connection::get();

        $query = "SELECT name AS nazov, code AS kod FROM subject WHERE id = $1";

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
        $query = "SELECT count(id) as count from person_course where id_predmet = $1 AND id_group = '8'";
        $this->dbh->query($query, array($id_predmet));
        return $this->dbh->fetch_assoc();
    }

    public function getStudentCountInfo($id_predmet) {
    // nie je potrebne filtrovat aj podla semestra, lebo predmet ako taky
    // je specificky pre kazdy semester (kazdy semester ma ine id)
        $query =
            "SELECT person.grade AS rocnik, study_programme.name AS nazov, COUNT(1) AS student_count
             FROM   person JOIN person_study_programme p2sp ON person.id = p2sp.id_person
                           JOIN study_programme sp ON p2sp.id_study_programme = sp.id
                           JOIN person_course p2c ON person.id = p2c.id_person
             WHERE  p2c.id_course = $1
             GROUP BY study_programme.id, person.grade, study_programme.name
             ORDER BY study_programme.id";
        $this->dbh->query($query, array($id_predmet));
        return $this->dbh->fetchall_assoc();
    }

    //prida vsetky predmety, ktore este nie su v databaze z minuleho semestra do aktualneho semestra,
    //predmet je odlisny, ak ma iny KOD
    public function saveLastPeriodSubjects($actualPeriodID,$prevPeriodID)
    {
        $garantID = $this->getGarantID();

        $query =
            "SELECT course.name AS nazov,
                    c2s.id_semester AS semester,
                    course.code AS kod,
                    course.termination_method AS sposob_ukoncenia,
                    c2sp.id_study_programme AS studijny_program,
                    c2s.id_semester AS id_semester,
                    p2c.id_person AS id_pedagog
             FROM   course JOIN course_semester c2s ON course.id = c2s.id_course
                           JOIN course_study_programme c2sp ON course.id = c2sp.id_course
                           JOIN person_course p2c ON course.id = p2c.id_course
                                                  AND p2c.role_type = CASE $2
                                                                        WHEN 3 THEN 'E'
                                                                        WHEN 4 THEN 'L'
                                                                        WHEN 5 THEN 'G'
                                                                      END
             WHERE  c2s.id_semester = $1";
        
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
        //TODO:tu zrejme nemenit nic
        //$sql = "UPDATE predmet SET blokovat_preberanie=$1 WHERE id=$2";
        //$this->dbh->query($sql, array($block, $subjectId));
    }

    // pri blokovani je mozne zadat aj komentar a ten sa tu uklada
    public function saveSubjectsBlockComment($subjectId, $comment) {
        //TODO:tu zrejme nemenit nic
        //$sql = "UPDATE predmet SET dovod_blokovania=$1 WHERE id=$2";
        //$this->dbh->query($sql, array($comment, $subjectId));
    }

    // Vrati ci je alebo nie blokovane preberanie poziadaviek a komentar k tomu
    public function isBlockedCopying($subjectId) {
        //TODO:tu zrejme nemenit nic
        //$sql = "SELECT blokovat_preberanie, dovod_blokovania FROM predmet WHERE id=$1";
        //$this->dbh->query($sql, array($subjectId));

        //return $this->dbh->fetch_assoc();
    }

    private function existSubjectCode($code, $actualPeriodID){

        $query =
            "SELECT 1
             FROM   course JOIN course_semester c2s ON course.id = c2s.id_course
             WHERE  (course.code='$1' AND c2s.id_semester=$2)";
        $this->dbh->query($query , array($code, $actualPeriodID));

        return $this->dbh->RowCount() >= 1;
    }

    private function getGarantID() {
        //TODO: upravit podla Petovho modelu
        $query="SELECT id FROM skupina WHERE code='Garant'";
        $this->dbh->Query($query);
        $result=$this->dbh->fetch_assoc();
        return $result['id'];
    }

    private function saveSubject($predmet,$actualPeriodID) {

        $query =
            "INSERT INTO course (id, name, code, termination_method)
                    VALUES (DEFAULT, $1, $2, $3)";
        $this->dbh->query($query, array(
            $predmet['nazov'], $predmet['kod'], $predmet['sposob_ukoncenia']
        ));
        $id_course = $this->dbh->fetch();
        $query =
            "INSERT INTO course_semester(id_course, id_semester)
                    VALUES ($1, $2)";
        $this->dbh->query($query, array(
            $id_course, $actualPeriodID
        ));
        $query =
            "INSERT INTO course_study_programme(id_course, id_study_programme)
                    VALUES ($1, $2)";
        $this->dbh->query($query, array(
            $id_course, $predmet['studijny_program']
        ));
    }

    private function saveGarant($predmetID,$pedagogID,$garantID){
        $query =
            "INSERT INTO person_course (id_course,id_person,id_group)
					 VALUES ($1, $2, 2)";
        $this->dbh->query($query, array(
            $predmetID, $pedagogID
        ));
    }
}
?>