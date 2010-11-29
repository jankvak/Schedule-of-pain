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
                "SELECT g.name AS nazov, m.name , m.href, m.order " .
                "FROM menu m " .
                "JOIN groups g ON g.id = m.id_group " .
                "LEFT JOIN person_group pg ON (pg.id_group = g.id AND pg.id_person = $1) " .
                 "WHERE pg.id IS NOT NULL OR m.id_group=0 ".
                "ORDER BY m.order";
            $this->dbh->query($query, array($userid));

            return $this->dbh->fetchall_assoc();
        } else {
            return null;
        }
    }
}
?>
