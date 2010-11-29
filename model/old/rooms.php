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
        $order = $orderByName? "ORDER BY m.nazov": "ORDER BY m.kapacita, mt.id, m.nazov";
        $query =
            "SELECT m.id, m.nazov, m.kapacita, m.poznamka, mt.nazov as typ, mt.id as typ_id
               FROM miestnost m, miestnost_typ mt
               WHERE mt.id = m.id_miestnost_typ {$order}";
               
        $this->dbh->Query($query);
        return $this->dbh->fetchall_assoc();
    }

    // vrati informacie o danej miestnosti
    function get($id) {
        $query =
            "SELECT m.id, m.nazov, m.kapacita, m.poznamka, mt.nazov as typ, mt.id as typ_id
             FROM miestnost m, miestnost_typ mt
             WHERE mt.id = m.id_miestnost_typ AND m.id = $1";
        $this->dbh->query($query, array($id));
        return $this->dbh->fetch_assoc();
    }

    // pridaj novu miestnost aj jej vybavenie, kapacitu, nazov...
    function save() {
        $this->dbh->TransactionBegin();
        $query =
            "INSERT INTO miestnost (nazov, kapacita, poznamka, id_miestnost_typ)
			 VALUES ($1, $2, $3, $4)";
        $this->dbh->query($query, array(
            $this->nazov, $this->kapacita, $this->poznamka, $this->id_miestnost_typ
        ));
        $id = $this->dbh->GetLastInsertID();
        foreach ($this->vybavenie as $eq) {
            $query =
                "INSERT INTO vybavenie_miestnost (id_miestnost,id_vybavenie)
            	 VALUES ($1, $2)";
            $this->dbh->query($query, array($id, $eq));
        }
        $this->dbh->TransactionEnd();
    }

    // edituj miestnost aj jej vybavenie, kapacitu, nazov...
    function saveEdited() {
        $this->dbh->TransactionBegin();
        $query =
            "UPDATE miestnost
             SET nazov=$1, kapacita=$2, poznamka=$3, id_miestnost_typ=$4
             WHERE id=$5";
        $this->dbh->query($query, array(
            $this->nazov, $this->kapacita, $this->poznamka, $this->id_miestnost_typ,
            $this->id
        ));
        $query = "DELETE FROM vybavenie_miestnost WHERE id_miestnost=$1";
        $this->dbh->query($query, array($this->id));
        foreach ($this->vybavenie as $eq)
        {
            $query =
                "INSERT INTO vybavenie_miestnost (id_miestnost,id_vybavenie)
            	 VALUES ($1, $2)";
            $this->dbh->query($query, array($this->id, $eq));
        }
        $this->dbh->TransactionEnd();
    }

    // vymaz miestnost
    function delete($id) {
        $query = "DELETE FROM miestnost WHERE id=$1";
        $this->dbh->query($query, array($id));
    }

    /**
     * vrati mozne kapacity miestnosti
     * @param <string> $sort ako triedi podla kapacity (pripustne hodnoty ASC a DESC)
     * @return <array> kapacity
     */
    function getCapacities($sort = "ASC") {
        $query =
            "SELECT DISTINCT kapacita FROM miestnost ORDER BY kapacita {$sort}";
        $this->dbh->Query($query);
        return $this->dbh->fetchall_assoc();
    }

    // vrati typy miestnosti, ku ktorym existuju nejake miestnosti
    function getTypes() {
        $query =
            "	SELECT DISTINCT mt.id, mt.nazov
				FROM 	miestnost_typ mt,
						miestnost m
				WHERE m.id_miestnost_typ = mt.id
				ORDER BY mt.id";
        $this->dbh->Query($query);
        return $this->dbh->fetchall_assoc();
    }

    // pre jodnotlive typy miestnosti vrati kapacity, ktore su k dispozicii pre dane typy
    function getCapacitiesForTypes() {
        $query = "	SELECT DISTINCT mt.id, m.kapacita
					FROM 	miestnost_typ mt,
							miestnost m
					WHERE mt.id = m.id_miestnost_typ
					ORDER BY mt.id, m.kapacita";
        $this->dbh->Query($query);
        return $this->dbh->fetchall_assoc();
    }

    // vrati vybavenie danej miestnosti
    function getRoomEquipment($id) {
        $query =
            "SELECT *
			 FROM vybavenie_miestnost
			 WHERE id_miestnost=$1";
        $this->dbh->query($query, array($id));
        return $this->dbh->fetchall_assoc();
    }

    // vrati vsetky mozne typy vybavenia
    function getEquipment() {
        $query="SELECT * FROM vybavenie";
        $this->dbh->Query($query);
        return $this->dbh->fetchall_assoc();
    }

    function getRoomName($nazov, $id) {
        if ($id == "")
        {
            $query = "SELECT * FROM miestnost WHERE nazov=$1";
            $params = array($nazov);
        }else
        {
            $query = "SELECT * FROM miestnost WHERE nazov=$1 AND id!=$2";
            $params = array($nazov, $id);
        }
        $this->dbh->query($query, $params);
        return $this->dbh->fetch_assoc();
    }
}
?>
