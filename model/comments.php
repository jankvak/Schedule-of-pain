<?php

/*
 poziadavky vseobecne
 */
defined('IN_CMS') or die('No access');


class Comments extends Model {

    public $course_id;
    public $commentText;
    public $metaID;

    public $check = array(
        "commentText" => array(
            "popis"		=> "Komentár k diskusii",
            "not_empty" => true,
            "block_tags"   => true
        )
    );

    /*
     * Ulozenie kometaru pre konkretnu poziadavku identifikovanu idckom meta_poziadavky
     * Stlpce zadal a cas_zadania su relevatne len pre typ 3, ale vyplnia sa aj pre typ 1,2
     * z id_osoby sa vysklada cele meno pedagoga, ktory vlozil komentar
     */
    public function saveComment($metaPoziadavkaID, $text, $typ, $id_osoba)
    {
        if (!isset($text) || $text=="") return;

        // vyskladanie celeho mena osoby identifikovanej cez id_osob
        $sql = "SELECT * FROM person WHERE id=$1";
        $this->dbh->query($sql, array($id_osoba));
        $osoba = $this->dbh->fetch_assoc();
        $meno = "{$osoba[titles_before]} {$osoba[name]} {$osoba[last_name]}, {$osoba[titles_after]}";

        $sql =
            "INSERT INTO komentar (id_meta_poziadavka, text, typ, zadal, cas_zadania)
			VALUES($1, $2, $3, $4, now())";

        $this->dbh->query($sql, array(
            $metaPoziadavkaID, $text, $typ, trim($meno, ' ,')
        ));
    }

    /*
     * update komentarov k diskusii, tak aby boli naviazane na najnovsiu poziadavku daneho predmetu
     */
    public function updateComments($newMetaID, $oldMetaID)
    {
        if (!isset($oldMetaID) || $oldMetaID == "") return;

        $sql =
            "UPDATE komentar SET id_meta_poziadavka=$1 WHERE id_meta_poziadavka=$2 AND typ=3";

        $this->dbh->query($sql, array(
            $newMetaID, $oldMetaID
        ));
    }

    public function saveCommentType3($id_osoba)
    {
    // vyskladanie celeho mena osoby identifikovanej cez id_osob
        $sql = "SELECT * FROM pedagog WHERE id=$1";
        $this->dbh->query($sql, array($id_osoba));
        $osoba = $this->dbh->fetch_assoc();
        $meno = "{$osoba[tituly_pred]} {$osoba[meno]} {$osoba[priezvisko]}, {$osoba[tituly_za]}";

        $sql =
            "INSERT INTO komentar (id_meta_poziadavka, text, typ, zadal, cas_zadania)
			VALUES($1, $2, $3, $4, now())";
        echo $this->metaID;
        $this->dbh->query($sql, array(
            $this->metaID, $this->commentText, 3, trim($meno, ' ,')
        ));
    }

    public function loadComments($metaPoziadavkaID)
    {
        $sql =
            "SELECT text, zadal, ".
            DateConvert::DBTimestampToSkDateTime("cas_zadania").
            " AS cas_zadania, typ FROM komentar WHERE id_meta_poziadavka=$1 ORDER BY cas_zadania desc";
        $this->dbh->query($sql, array($metaPoziadavkaID));
        $comments = $this->dbh->fetchall_assoc();

        $komentare = array();
        foreach ($comments as $comment) {
            if ($comment["typ"] == 1) $komentare["vseobecne"] = $comment["text"];
            elseif ($comment["typ"] == 2) $komentare["sw"] = $comment["text"];
            elseif ($comment["typ"] == 3) $komentare["other"][] = $comment;
        }

        return $komentare;
    }
}

?>
