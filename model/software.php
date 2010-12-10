<?php

/*
 Typy miestnosti
 */

defined('IN_CMS') or exit();

class Software extends Model {

// vrati vsetky typy miestnosti
    function getAll() {
        $query =
            'SELECT * FROM software';
        $this->dbh->Query($query);
        return $this->dbh->fetchall_assoc();
    }
}
?>