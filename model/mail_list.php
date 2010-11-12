<?php
class MailList extends Model
{
    public function getListForCollection($semesterID)
    {
        $sql =
            "SELECT DISTINCT p.mail
             FROM pedagog p
             LEFT JOIN vyucuje_predmet vp ON vp.id_pedagog=p.id
             LEFT JOIN skupina s ON s.id=vp.id_pedagog_typ
             LEFT JOIN predmet pr ON pr.id=vp.id_predmet
             WHERE pr.id_semester=$1 AND s.code=$2";
        $this->dbh->query($sql, array($semesterID, "Garant"));

        return $this->dbh->fetchall_assoc();
    }

    //TODO: poznamenat ze ma dat textovu rolu
    public function getTeacherListForPredmet($predmetID, $role)
    {
        $sql =
            "(SELECT DISTINCT p.mail
             FROM vyucuje_predmet vp
             LEFT JOIN pedagog p ON vp.id_pedagog=p.id
             WHERE vp.id_predmet=$1 AND vp.id_pedagog_typ<= 
             (SELECT s.id 
              FROM skupina s
              WHERE s.code=$2))
             UNION
             (SELECT DISTINCT p.mail
             FROM pedagog p
             LEFT JOIN clenstvo c ON c.id_pedagog=p.id
             LEFT JOIN skupina s ON c.id_skupina=s.id
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
            "(SELECT DISTINCT p.mail
             FROM vyucuje_predmet vp
             LEFT JOIN pedagog p ON vp.id_pedagog=p.id
             WHERE vp.id_predmet=$1 AND vp.id_pedagog_typ<=
             (SELECT s.id 
              FROM skupina s
              WHERE s.code=$2)) 
             UNION
             (SELECT DISTINCT p.mail
             FROM pedagog p
             LEFT JOIN clenstvo c ON c.id_pedagog=p.id
             LEFT JOIN skupina s ON c.id_skupina=s.id
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
        "SELECT DISTINCT p.mail FROM pedagog p
         LEFT JOIN clenstvo c ON p.id = c.id_pedagog
         LEFT JOIN skupina s ON s.id = c.id_skupina
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
        "SELECT p.mail FROM pedagog p
            LEFT JOIN pripomienka pr ON p.id = pr.id_pedagog
            WHERE pr.id = $1";

        $this->dbh->query($sql, $suggestionId);
        return $this->dbh->fetchall_assoc();
    }
}
?>
