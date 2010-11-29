<?php

/*
 editacia informacii o miestnostiach
 */

defined('IN_CMS') or exit();

class Rooms extends Model {

// korekcia zadanych udajov
    public $check = array(
        "nazov" => array(
            "popis"     => "Názov",
            "not_empty" => true,
            "maxlength" => 10,
            "block_tags"=> true
        ),
        "poznamka" => array(
            "popis"     => "Poznámka",
            "maxlength" => 255,
            "block_tags"=> true
        ),
        "kapacita" => array(
            "popis"     => "Kapacita",
            "not_empty" => true,
            "is_int"    => true,
            "min_value" => 1,
            "max_value" => 1000
        )
    );

    public $id;
    public $nazov;
    public $poznamka;
    public $kapacita;
    public $id_miestnost_typ;
    public $vybavenie;

    /**
     * Vrati vsetky miestnosti aj s informaciami o kazdej z nich
     * @return <array> miestnosti
     */
    function getAll($orderByName = false) {
        $order = $orderByName? "ORDER BY r.name": "ORDER BY r.capacity, r.room_type";
        $query =
            "SELECT r.id, r.name, r.capacity, r.note, r.room_type
               FROM room r
               {$order}";
               
        $this->dbh->Query($query);
        return $this->dbh->fetchall_assoc();
    }

    // vrati informacie o danej miestnosti
    function get($id) {
        $query =
            "SELECT r.id, r.name, r.capacity, r.note, r.room_type
             FROM room r
             WHERE r.id = $1";
        $this->dbh->query($query, array($id));
        return $this->dbh->fetch_assoc();
    }

    // pridaj novu miestnost aj jej vybavenie, kapacitu, nazov...
    function save() {
        $this->dbh->TransactionBegin();
        $query =
            "INSERT INTO room (name, capacity, note, room_type)
			 VALUES ($1, $2, $3, $4)";
        $this->dbh->query($query, array(
            $this->nazov, $this->kapacita, $this->poznamka, $this->id_miestnost_typ
        ));
        $id = $this->dbh->GetLastInsertID();
        foreach ($this->vybavenie as $eq) {
            $query =
                "INSERT INTO room_equipment (id_room,id_equipment)
            	 VALUES ($1, $2)";
            $this->dbh->query($query, array($id, $eq));
        }
        $this->dbh->TransactionEnd();
    }

    // edituj miestnost aj jej vybavenie, kapacitu, nazov...
    function saveEdited() {
        $this->dbh->TransactionBegin();
        $query =
            "UPDATE room
             SET name=$1, capacity=$2, note=$3, room_type=$4
             WHERE id=$5";
        $this->dbh->query($query, array(
            $this->nazov, $this->kapacita, $this->poznamka, $this->id_miestnost_typ,
            $this->id
        ));
        $query = "DELETE FROM room_equipment WHERE id_room=$1";
        $this->dbh->query($query, array($this->id));
        foreach ($this->vybavenie as $eq)
        {
            $query =
                "INSERT INTO room_equipment (id_room,id_equipment)
            	 VALUES ($1, $2)";
            $this->dbh->query($query, array($this->id, $eq));
        }
        $this->dbh->TransactionEnd();
    }

    // vymaz miestnost
    function delete($id) {
        $query = "DELETE FROM room WHERE id=$1";
        $this->dbh->query($query, array($id));
    }

    /**
     * vrati mozne kapacity miestnosti
     * @param <string> $sort ako triedi podla kapacity (pripustne hodnoty ASC a DESC)
     * @return <array> kapacity
     */
    function getCapacities($sort = "ASC") {
        $query =
            "SELECT DISTINCT capacity FROM room ORDER BY capacity {$sort}";
        $this->dbh->Query($query);
        return $this->dbh->fetchall_assoc();
    }

    // vrati typy miestnosti, ku ktorym existuju nejake miestnosti
    function getTypes() {
        $query =
            "	SELECT DISTINCT room_type
				FROM 	room r
				ORDER BY room_type";
        $this->dbh->Query($query);
        return $this->dbh->fetchall_assoc();
    }

    // pre jodnotlive typy miestnosti vrati kapacity, ktore su k dispozicii pre dane typy
    function getCapacitiesForTypes() {
        $query = "	SELECT DISTINCT r.room_type, r.capacity
					FROM 	room r
					ORDER BY r.room_type, r.capacity";
        $this->dbh->Query($query);
        return $this->dbh->fetchall_assoc();
    }

    // vrati vybavenie danej miestnosti
    function getRoomEquipment($id) {
        $query =
            "SELECT *
			 FROM room_equipment
			 WHERE id_room=$1";
        $this->dbh->query($query, array($id));
        return $this->dbh->fetchall_assoc();
    }

    // vrati vsetky mozne typy vybavenia
    function getEquipment() {
        $query="SELECT * FROM equipment";
        $this->dbh->Query($query);
        return $this->dbh->fetchall_assoc();
    }

    function getRoomName($nazov, $id) {
        if ($id == "")
        {
            $query = "SELECT * FROM room WHERE name=$1";
            $params = array($nazov);
        }else
        {
            $query = "SELECT * FROM room WHERE name=$1 AND id!=$2";
            $params = array($nazov, $id);
        }
        $this->dbh->query($query, $params);
        return $this->dbh->fetch_assoc();
    }
}
?>
