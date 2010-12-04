<?php

/*
 informacie o pouzivateloch, skupinach
 */

if(!defined('IN_CMS')) {
    exit();
}

class Users extends Model {

// data z formularov
    public $id;
    public $username;
    public $skupina;
    public $meno;
    public $priezvisko;
    public $tituly_pred;
    public $tituly_za;
    public $ais_id;
    // data ziskane z LDAPu
    public $fullname;
    public $email;

    /**
     * Vrati len zoznam pouzivatelov s ich udajmi
     * @return array
     */
    public function getAll() {

        $query =
            "SELECT p.id, p.login,
            {$this->vyskladajMeno("p")},
             p.mail, p.pocet_hodin FROM pedagog p ORDER BY meno";

        $this->dbh->Query($query);

        return $this->dbh->fetchall_assoc();
    }

    /**
     * Ziska zoznam pouzivatelov aj s ich prinaleziacimi skupinami
     * (spojeny nazov vsetkych skupin, ktorych je pouzivate clenom)
     * @return array
     */
    public function getAllWithGroups()
    {
        $users = $this->getAll();

        foreach ($users as &$user)
        {
            $user['skupina'] = $this->getGroups($user['id']);
        }

        return $users;
    }

    /**
     * Vrati spojeny nazov vsetkych skupin, ktorych pouzivatel je clenom
     * @param $usr - id pouzivatela
     * @return String
     */
    function getGroups($usr) {

        $query =
            "SELECT s.nazov FROM clenstvo c
			 JOIN skupina s ON c.id_skupina = s.id 
			 WHERE c.id_pedagog = $1";
        $this->dbh->query($query, array($usr));
        $skupiny = $this->dbh->fetchall_assoc();

        // zmaha pouzit implode ale vyledky nie su v tomto poli ale az vnorene
        $res = "";
        foreach($skupiny as $skupina)
        {
            if (!empty($res)) $res .= ", ";
            $res .= $skupina["nazov"];
        }

        return $res;
    }

    // vrati nazvy skupin
    function getGroupNames() {
        $query = "SELECT nazov,id FROM skupina";
        $this->dbh->Query($query);
        return $this->dbh->fetchall_assoc();
    }

    // uloz noveho pouzivatela - pouzi info z LDAPu
    function save() {
        $this->dbh->TransactionBegin();
        $query =
            "INSERT into pedagog (login, mail, priezvisko, meno, tituly_pred, tituly_za, ais_id)
			 VALUES ($1, $2, $3, $4, $5, $6, $7)";
        $this->dbh->query($query, array(
            $this->username, $this->email, $this->priezvisko, $this->meno,
            $this->tituly_pred, $this->tituly_za, $this->ais_id
        ));
        $query = "SELECT id from pedagog WHERE login=$1";
        $this->dbh->query($query,array($this->username));
        $id=$this->dbh->fetch_assoc();
        foreach ($this->skupina as $grp) {
            $query =
                "INSERT into clenstvo (id_pedagog,id_skupina) VALUES ($1, $2)";
            $this->dbh->query($query, array($id['id'], $grp));
        }
        $this->dbh->TransactionEnd();
    }

    // vymaz daneho pouzivatela
    function delete($login) {
        $query = "DELETE FROM pedagog WHERE login=$1";
        $this->dbh->query($query, array($login));
    }

    // vrati id skupin, v ktorych je dany pouzivatel
    function getUserGroups($id) {
        $query = "SELECT id_skupina FROM clenstvo WHERE id_pedagog=$1";
        $this->dbh->query($query, array($id));
        return $this->dbh->fetchall_assoc();
    }


    function getUserLogin($id) {
        $query = "SELECT login FROM pedagog WHERE id=$1";
        $this->dbh->query($query, array($id));
        $user = $this->dbh->fetch_assoc();

        if(empty($user))
            return "";
        else
            return $user['login'];
    }

    // edituj pouzivatela
    function edit() {
        $this->dbh->TransactionBegin();

        $query = "DELETE from clenstvo WHERE id_pedagog=$1";
        $this->dbh->query($query, array($this->id));

        foreach ($this->skupina as $grp) {
            $query = "INSERT into clenstvo (id_pedagog,id_skupina) VALUES ($1, $2)";
            $this->dbh->query($query, array($this->id, $grp));
        }
        $this->dbh->TransactionEnd();
    }

    function getUserInfoFromLdap($fields = array("sn", "cn", "givenname", "uisid")) {
        $ldap = new Ldap();
        $userdata = $ldap->find(array("uid" => $this->username), $fields);

        // ak zadany user nie je korektny v ramci LDAPu
        if (empty($userdata)) return false;

        $this->fullname = addslashes($userdata[0]["cn"]);
        $this->meno = addslashes($userdata[0]["givenname"]);
        $this->priezvisko = addslashes($userdata[0]["sn"]);
        $this->ais_id = addslashes($userdata[0]["uisid"]);

        // tituly stuff
        if (preg_match("/^(.*) {$this->meno}/", $this->fullname, $matches))
        {
            $this->tituly_pred = $matches[1];
        }else $this->tituly_pred = "";
        if (preg_match("/{$this->priezvisko}, (.*)$/", $this->fullname, $matches))
        {
            $this->tituly_za = $matches[1];
        }else $this->tituly_za = "";

        return true;
    }

    /**
     * Vrati ID usera alebo 0 ak taky neexistuje
     */
    function getUserID() {
        $query = "SELECT id FROM pedagog WHERE login=$1";
        $this->dbh->query($query, array($this->username));
        if ($this->dbh->RowCount()) {
            $id = $this->dbh->fetch_assoc();
            return $id["id"];
        } else return 0;
    }

    /**
     * Vygeneruje SQL ktore naformatuje meno do specifickeho formatu
     * $specPoradie==true: priezvisko meno, tituly
     * $specPoradie==false: tituly_pred meno priezvisko tituly_za
     * @param String $skratka - skratka pod akou vystupuje tabulka pedagog v SQL alebo jej meno
     * @param String $vystup - pod nazvom akeho stlpca bude ulozene vyskladane meno
     * @param bool $specPoradie - specifikuje poradie vygenerovaneho mena
     * @return String
     */
    public static function vyskladajMeno($skratka, $vystup = "meno", $specPoradie = true)
    {
        if ($specPoradie)
        {
            $res = "{$skratka}.last_name || ' ' || {$skratka}.name || ', ' ||
                {$skratka}.titles_before || ' ' || {$skratka}.titles_after";
        }else
        {
            $res = "{$skratka}.titles_before || ' ' || {$skratka}.name || ' ' ||
                {$skratka}.last_name || ', ' || {$skratka}.titles_after";
        }
        // maly trik, ak nema obidva tituly aby neboli medzery na koncoch tak otrimujeme
        // (aj ciarku ked nema tituly)
        return "trim(both ' ,' from {$res}) AS {$vystup} ";
    }
}

?>
