<?php

/*
 priority vyucby, ktore zadavaju pedagogovia (kedy im vyhovuje mat prednasku a pod.)
 */

defined('IN_CMS') or die('No access');

class Priorities extends Model {

    public $priorities;
    public $comment;
    public $semester_id;
    public $check = array(
        "comment" => array(
            "block_tags" => true
        )
    );

    /**
     * Vrati zoznam pouzivatelov, ktori zadali casove priority v danom semestri
     * @param int $semesterID - id semestra
     * @return array
     */
    public function getAllUsersWithPriorities($semesterID) {
        $sql1 =
            "SELECT DISTINCT pe.id,".
            Users::vyskladajMeno("pe").
            "FROM pedagog pe
             JOIN priorita_vyucby pv ON pv.id_pedagog=pe.id
             WHERE pv.id_semester=$1";
        $sql2 =
            "SELECT DISTINCT pe.id,".
            Users::vyskladajMeno("pe").
            "FROM pedagog pe
             JOIN priorita_komentar pk ON pk.id_pedagog=pe.id
             WHERE pk.id_semester=$1";
        $sql = "({$sql1}) UNION ({$sql2}) ORDER BY meno";
        $this->dbh->query($sql, array($semesterID));
        return $this->dbh->fetchall_assoc();
    }

    // vrati priority daneho pouzivatela
    public function load($user_id, $semesterID) {
        $query =
            "SELECT *
        	 FROM priorita_vyucby 
        	 WHERE id_pedagog=$1 AND id_semester=$2 
        	 ORDER BY day,start DESC";
        $this->dbh->query($query, array($user_id, $semesterID));
        return $this->dbh->fetchall_assoc();
    }

    // vrati typy priorit
    public function loadTypes() {
        $query = "SELECT * FROM priorita_typ ORDER BY id";
        $this->dbh->Query($query);
        return $this->dbh->fetchall_assoc();
    }

    // vymaz priority vyucby daneho pouzivatela
    private function __drop($user_id, $semesterID) {
        $query =
            "DELETE FROM priorita_vyucby
        	 WHERE id_pedagog=$1  AND id_semester=$2";
        $this->dbh->query($query, array($user_id, $semesterID));
        $query =
            "DELETE FROM priorita_komentar
        	 WHERE id_pedagog=$1 AND id_semester=$2";
        $this->dbh->query($query, array($user_id, $semesterID));
    }

    // uloz priority vyucby pouzivatela
    public function save($user_id, $semesterID) {
    // fixed: ma to byt cele transakcia vratane delete ...
        $this->dbh->TransactionBegin();
        $this->__drop($user_id, $semesterID);

        // drobna korekcia: ak nic nezada alebo vsetko odskrta funkcie zlyhaju
        if (is_array($this->priorities) && !empty($this->priorities)) {
        // usporiada podla klucov formatu riadok_stlpec
        // => bude to sekvecne podla casu a dni
            ksort($this->priorities);
            $keys = array_keys($this->priorities);

            // nainicializuje prvy den
            $sp = explode('_', $keys[0]);
            $lastRow = $sp[0];
            $lastCol = $sp[1] - 1;
            $startCol = $sp[1];
            $lastPriority = $this->priorities[$keys[0]];

            foreach(array_keys($this->priorities) as $priority) {
                $sp = explode('_', $priority);

                if($lastRow == $sp[0] && $lastCol + 1 == $sp[1] && $lastPriority == $this->priorities[$priority]) {
                // ostali sme v tom istom dni a mame nasledujuci s tou istou prioritou
                // => predlzime interval
                // => nic neukladat mozno dalsi tiez predlzi
                } else {
                // nepokracuje => ulozime posledny interval
                    $this->__insert($startCol, $lastCol, $lastRow, $lastPriority, $user_id, $semesterID);
                    $startCol = $sp[1];
                }

                $lastRow = $sp[0];
                $lastCol = $sp[1];
                $lastPriority = $this->priorities[$priority];
            }

            // nezabudnut ulozit posledny interval
            $this->__insert($startCol, $lastCol, $lastRow, $lastPriority, $user_id, $semesterID);
        }

        $this->__insertComment($user_id, $semesterID, $this->comment);
        $this->dbh->TransactionEnd();
    }

    private function __insert($start, $end, $day, $prio, $user, $semesterID) {
        $query =
            "INSERT INTO priorita_vyucby(start, \"end\", day, type_id, id_pedagog, id_semester)
             VALUES($1, $2, $3, $4, $5, $6)";

        $this->dbh->query($query, array(
            $start, $end, $day, $prio, $user, $semesterID));
    }

    private function __insertComment($user, $semesterID, $comment) {
        $query =
            "INSERT INTO priorita_komentar(id_pedagog, id_semester, comment)
        	 VALUES($1, $2, $3)";
        $this->dbh->query($query, array($user, $semesterID, $comment));
    }

    // vrati komentare k prioritam vyucby daneho pouzivatela
    public function getComment($user_id, $semesterID) {
        $query =
            "SELECT comment
        	 FROM priorita_komentar 
        	 WHERE id_pedagog=$1 AND id_semester=$2";
        $this->dbh->query($query, array($user_id, $semesterID));
        $comment = $this->dbh->fetch_assoc();
        return $comment['comment'];
    }

    public function saveLastPriorities($user_id, $semesterID, $lastSemesterID) {
    // fixed: ma to byt cele transakcia vratane delete ...
        $this->dbh->TransactionBegin();
        $this->__drop($user_id, $semesterID);

        //preberanie komentaru z predchadzajuceho roku, ak nie je zadany tak vytvory prazdny
        $comment = $this->getComment($user_id, $lastSemesterID);
        if(isset($comment))
            $this->__insertComment($user_id, $semesterID, $comment);
        else
            $this->__insertComment($user_id, $semesterID, "");

        //preberanie časových priorít po jednotlivých dnoch v týzdni
        $days = $this->load($user_id, $lastSemesterID);
        foreach($days as $day) {
            $this->__insert($day['start'], $day['end'], $day['day'], $day['type_id'], $user_id, $semesterID);
        }
        $this->dbh->TransactionEnd();
    }
}

?>
