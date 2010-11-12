<?php

/*
 Typy pripomienky
 */

defined('IN_CMS') or exit();

class SuggestionType extends Model {

// vrati vsetky typy pripomienky K SYSTEMU
// TODO: podpora pripomienok k rozvrhu
    function getAll() {
        $query =
            'SELECT id, nazov
			 FROM pripomienka_typ
             WHERE rozvrh = FALSE';
        $this->dbh->Query($query);
        return $this->dbh->fetchall_assoc();
    }
}
?>