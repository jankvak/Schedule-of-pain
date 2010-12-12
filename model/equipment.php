<?php

/*
 Tu su definovane funkcie tykajuce sa vybavenia
 */

if(!defined('IN_CMS')) {
    exit();
}

class Equipment extends Model {
    public $typ;
    public $prenosne;
    public $poznamka;
    public $id;

    var $check = array(
        "typ" => array(
            "popis"     => "Typ",
            "not_empty" => true,
            "maxlength" => 20,
            "block_tags"=> true
        ),
        "poznamka" => array(
            "popis"     => "Poznamka",
            "maxlength" => 255,
            "block_tags"=> true
        )
    );

    // vrati vsetko vybavenie
    function getAll() {
        $query = "SELECT id, type AS typ, note AS poznamka, portable AS prenosne FROM equipment;";
        $this->dbh->Query($query);
        return $this->dbh->fetchall_assoc();
    }

    // update existujuceho vybavenia
    function update() {
        $this->prenosne = $this->prenosne ? 'true' : 'false';
        $query = "UPDATE equipment SET type=$1, note=$2, portable=$3 WHERE id=$4";
        $this->dbh->query($query, array(
            $this->typ, $this->poznamka, $this->prenosne, $this->id
        ));
    }

    // vymaz vybavenie
    function delete($id) {
        $query = "DELETE FROM equipment WHERE id=$1;";
        $this->dbh->query($query, array($id));
    }

    // vrati vlastnosti vybavenia specifikovane jeho id v databaze
    function get($id) {
        $query = "SELECT id, type AS typ, note AS poznamka, portable AS prenosne FROM equipment WHERE id=$1";
        $this->dbh->query($query, array($id));
        return $this->dbh->fetch_assoc();
    }
    function getAllTypes() {
        $query = "SELECT distinct type, id FROM equipment";
        $this->dbh->query($query);
        return $this->dbh->fetchall_assoc();
    }

    // uloz nove vybavenie
    function save() {
        $this->prenosne = $this->prenosne ? 'true' : 'false';
        $query= "INSERT INTO equipment(type, note, portable) VALUES ($1, $2, $3)";
        $this->dbh->query($query, array(
            $this->typ, $this->poznamka, $this->prenosne
        ));
    }

    // ziska ID podla typu
    function getID() {
    // ak edituje hlada ci iny neexistuje, nech nepadne editacia lebo typ ponechal
        if ($this->id)
        {
            $query = "SELECT id FROM equipment WHERE type=$1 AND id!=$2";
            $params = array($this->typ, $this->id);
        }else
        {
            $query = "SELECT id FROM equipment WHERE type=$1";
            $params = array($this->typ);
        }
        $this->dbh->query($query, $params);
        if ($this->dbh->RowCount() == 0) return 0;
        else {
            $eq = $this->dbh->fetch_assoc();
            return $eq["id"];
        }
    }
}
?>
