<?php
class MailList extends Model
{
    public function getListForCollection($semesterID)
    {
        $sql =
            "SELECT DISTINCT p.email
             FROM person p
             LEFT JOIN person_course pc ON pc.id_person=p.id
             LEFT JOIN groups g ON g.id=pc.id_group
             LEFT JOIN course c ON c.id=pc.id_course
             LEFT JOIN course_semester cs ON cs.id_course=c.id
             WHERE g.code=$2 AND cs.id_semester=$1";
        $this->dbh->query($sql, array($semesterID, "Garant"));

        return $this->dbh->fetchall_assoc();
    }

    //TODO: poznamenat ze ma dat textovu rolu
    public function getTeacherListForPredmet($predmetID, $role)
    {
        $sql =
            "(SELECT DISTINCT p.email
             FROM person_course vp
             LEFT JOIN person p ON vp.id_person=p.id
             WHERE vp.id_course=$1 AND vp.id_group<= 
             (SELECT s.id 
              FROM groups s
              WHERE s.code=$2))
             UNION
             (SELECT DISTINCT p.email
             FROM person p
             LEFT JOIN person_group c ON c.id_person=p.id
             LEFT JOIN groups s ON c.id_group=s.id
             WHERE s.code=$3)";
        $this->dbh->query($sql, array($predmetID, $role, "Scheduler"));

        return $this->dbh->fetchall_assoc();
    }

    public function getListForComments($predmetID, $role)
    {
    // vyberie aj vsetky nadradene role
    // t.j. ak napise dakto comments cviciaceho notifikuje
    // garanta, teachera aj cviciaceho
        $sql =
            "(SELECT DISTINCT p.email
             FROM person_course vp
             LEFT JOIN person p ON vp.id_person=p.id
             WHERE vp.id_course=$1 AND vp.id_group<=
             (SELECT s.id 
              FROM groups s
              WHERE s.code=$2)) 
             UNION
             (SELECT DISTINCT p.email
             FROM person p
             LEFT JOIN person_group c ON c.id_person=p.id
             LEFT JOIN groups s ON c.id_group=s.id
             WHERE s.code=$3)";
        $this->dbh->query($sql, array($predmetID, $role, "Scheduler"));

        return $this->dbh->fetchall_assoc();
    }

    /*
     * Vrati zoznam mailov vsetkych adminov
     */
    public function getAdminList()
    {
        //vyberie vsetkych adminov systemu
        $sql =
        "SELECT DISTINCT p.email FROM person p
         LEFT JOIN person_group c ON p.id = c.id_person
         LEFT JOIN groups s ON s.id = c.id_group
         WHERE s.code = $1";
        $this->dbh->query($sql, 'Admin');

        return $this->dbh->fetchall_assoc();
    }

    /*
     * V podstate vrati mail jedneho pouzivatela podla pripomienky
     */
    public function getUserMailBySuggestionsId($suggestionId)
    {
        //vyberie uzivatela podla jeho pripomienky
        $sql =
        "SELECT p.email FROM person p
            LEFT JOIN report r ON p.id = r.id_person
            WHERE r.id = $1";

        $this->dbh->query($sql, $suggestionId);
        return $this->dbh->fetchall_assoc();
    }
}
?>
