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
            "SELECT DISTINCT report_type AS id, report_type AS nazov
			 FROM report
             WHERE NOT report_type = 'rozvrh'";
        $this->dbh->Query($query);
        return $this->dbh->fetchall_assoc();
    }
}
?>