<?php

/*
 Typy miestnosti
 */

defined('IN_CMS') or exit();

class RoomType extends Model {

// vrati vsetky typy miestnosti
    function getAll() {
        $query =
            'SELECT Distinct room_type
			 FROM room';
        $this->dbh->Query($query);
        return $this->dbh->fetchall_assoc();
    }
}
?>