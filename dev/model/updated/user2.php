<?php


/*
 informacie o danom pouzivatelovi
 */

defined('IN_CMS') or die('No access');

class User extends Model {

    public $mail;
    public $notifyMyActions;

    public $check = array(
        "mail" => array(
            "popis"     => "mail",
            "not_empty" => true,
            "maxlength" => 50,
            "block_tags"=> true,
            "is_mail"=> true
        )
    );

    // vrati informacie o danom pouzivatelovi
    public function findByLogin($login) {
        $query =
            "SELECT p.id, p.login, p.email, p.notification_on, p.ais_id, ".
            Users::vyskladajMeno("p", "name", false).
            "FROM person p
        	 WHERE login = $1";

        $this->dbh->query($query, array($login));
        $user = $this->dbh->fetch_assoc();

        if (!empty ($user['id'])) {
            $user['groups'] = $this->loadGroups($user['id']);
        }

        //$user['posielat_moje_zmeny'] = ($user['posielat_moje_zmeny'] = 't') ? TRUE : FALSE;
        $user['posielat_moje_zmeny'] = $this->convertBoolean($user['posielat_moje_zmeny']);
        return $user;
    }

    public function save($uid)
    {
    // konverzia dat. typu na boolean, ak bola nastavene je checked
        $this->notifyMyActions = isset($this->notifyMyActions);
        // update v DB
        $sql =
            "UPDATE person
             SET email=$1,
             notification_on=$2 
             WHERE id=$3";
        $this->dbh->query($sql, array(
            $this->mail,
            $this->notifyMyActions ? "true" : "false", /* small hack lebo engine false konvertuje na '' */
            $uid
        ));
    }

    private function loadGroups($uid) {
        $query =
            "SELECT s.code FROM person p
        	 JOIN person_group c ON c.id_person = p.id
        	 JOIN group s ON c.id_group = s.id 
			 WHERE p.id = $1";

        $this->dbh->query($query, array($uid));
        $rows = $this->dbh->fetchall_assoc();

        $groups = array ();

        foreach ($rows as $row) {
            $groups[] = $row["code"];
        }

        return $groups;
    }

    /**
     * Ziska udaje pouzivatela na zaklade ID
     * @param <int> $id id pouzivatela
     * @param <bool> $loadGroups ci bude sucastou aj zoznam skupin, ktorcyh je clenom
     * @return <array> riadok z tabulky pedagog so vsetkymi stlpcami,
     * ak bolo $loadGroups==true tak aj sucastou bude aj index 'groups'
     * s polom vsetkych skupin, ktorych je user clenom (stlpec code z tabulky skupina)
     */
    public function findById($id, $loadGroups = false) {
        $query =
            "SELECT p.id, p.login, p.email, p.ais_id, ".
            Users::vyskladajMeno("p", "name", false).
            "FROM person p WHERE id=$1";

        $this->dbh->query($query,array($id));
        $user = $this->dbh->fetch_assoc();

        if (!empty ($user['id']) && $loadGroups) {
            $user['groups'] = $this->loadGroups($user['id']);
        }

        return $user;
    }

    private function convertBoolean($sbool){
        if ($sbool=='t')
            return TRUE;
        else
            return FALSE;
    }
}
?>
