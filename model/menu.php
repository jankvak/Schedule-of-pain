<?php

/*
 Nacitanie menu z databazy pre pouzivatela s danymi pravami
 */

defined('IN_CMS') or die('No access');

class Menu extends Model {

    function getForUser($userid) {
        if($userid) {
            /*
             * Drobne tweaknute menu, popis dopytu
             * 1. vyber vsetkych poloziek menu
             * 2. join na nazov skupiny (existuje ku kazdemu)
             * 3. left join menu na clenstvo, t.j. tie polozky ktory clenmi nie sme budu mat c.id NULL
             * 4. finalne vyfiltruje len tie pre ktore mame pristup alebo pristup je all
             * */
            $query =
                "SELECT s.nazov, m.name, m.href, m.poradie " .
                "FROM menu m " .
                "JOIN skupina s ON s.id = m.group_id " .
                "LEFT JOIN clenstvo c ON (c.id_skupina = s.id AND c.id_pedagog = $1) " .
                "WHERE c.id IS NOT NULL OR m.group_id=0 " .
                "ORDER BY m.poradie";
            $this->dbh->query($query, array($userid));

            return $this->dbh->fetchall_assoc();
        } else {
            return null;
        }
    }
}
?>
