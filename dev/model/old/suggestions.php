<?php

if(!defined('IN_CMS'))
{
    exit();
}

class Suggestions extends Model
{

//definuje nazvy jednotlivych stavov pripomienok
    public static $nazvyStavov = array(
        1 => "nová",
        2 => "akceptovaná",
        3 => "neakceptovaná",
        4 => "v riešení",
        5 => "vyriešená"
    );

    // korekcia zadanych udajov
    public $check = array(
        "pripomienka_typ_id" => array(
            "popis"            => "Typ pripomienky",
            "not_equal"        => 0,
            "not_equal_hlaska" => "Nebol vybraný Typ pripomienky."
        ),
        "text" => array(
            "popis"      => "text",
            "not_empty"  => true,
            "block_tags" => true
        )
    );

    //databazove atributy
    public $id;
    public $text;
    public $casova_peciatka;
    public $id_pedagog;
    public $pripomienka_typ_id;
    public $stav;

    /**
     * Vrati vsetky pripomienky k systemu od pouzivatelov aj s atributmi
     * - id, text, cas pridania, meno autora, stav
     * @return array
     */
    function getAll()
    {
        $query =
            "SELECT p.id, p.text, p.casova_peciatka, p.id_pedagog, p.stav, pt.nazov as typ, pt.id as typ_id, ".
            Users::vyskladajMeno("pedagog", "pedagog_meno", false).
            "FROM pripomienka p, pripomienka_typ pt, pedagog
             WHERE pt.id = p.pripomienka_typ_id AND pedagog.id = p.id_pedagog AND pt.rozvrh = FALSE
             ORDER BY p.stav";
        $this->dbh->Query($query);
        $retArr = $this->dbh->fetchall_assoc();
        $this->__pridajNazvyStavov($retArr);
        return $retArr;
    }

    /**
     * Vrati atributy danej pripomienky z databazy.
     * @param $id - id pripomienky
     * @return array
     */
    function get($id)
    {
        $query =
            "SELECT p.id, p.text, p.casova_peciatka, p.id_pedagog, p.stav, pt.nazov as typ, pt.id as pripomienka_typ_id, ".
            Users::vyskladajMeno("pedagog", "pedagog_meno", false).
            "FROM pripomienka p, pripomienka_typ pt, pedagog
             WHERE p.id = $1 AND pt.id = p.pripomienka_typ_id AND pedagog.id = p.id_pedagog AND pt.rozvrh = FALSE";
        $this->dbh->query($query, array($id));
        return $this->dbh->fetch_assoc();
    }

    /**
     * Vrati vsetky pripomienky daneho pouzivatela
     * @param $pedagog_id - id pouzivatela
     * @return array
     */
    function getAllFromUser($pedagog_id)
    {
        $sql =
            "SELECT p.id, p.text, p.casova_peciatka, p.stav, pt.nazov as typ
            FROM pripomienka p
            JOIN pripomienka_typ pt ON pt.id=p.pripomienka_typ_id
            WHERE p.id_pedagog=$1
            ORDER BY p.casova_peciatka ASC";
        $this->dbh->query($sql, array($pedagog_id));
        $retArr = $this->dbh->fetchall_assoc();
        $this->__pridajNazvyStavov($retArr);
        return $retArr;
    }

    /**
     * Prida do pola, kde su jednotlive pripomienky a ich atributy
     * nazov stavu ku kazdej pripomienke, ktora ma v tomto poli atribut stav.
     * Meni vstupne pole!
     * @param $pripomienky - smernik na pole s pripomienkami
     */
    private function __pridajNazvyStavov(&$pripomienky)
    {
        foreach ($pripomienky as &$pripomienka)
        {
            if (array_key_exists( "stav" , $pripomienka ))
                if (array_key_exists($pripomienka["stav"], self::$nazvyStavov))
                    $pripomienka["nazovStavu"] = self::$nazvyStavov[$pripomienka["stav"]];
                else
                    $pripomienka["nazovStavu"] = "nedefinovaný";
        }
    }

    /**
     * Vrati id a nazov moznych typov pripomienkok.
     * @return array
     */
    function getTypes()
    {
        $query = "SELECT id, nazov FROM pripomienka_typ";
        $this->dbh->Query($query);
        return $this->dbh->fetchall_assoc();
    }

    /**
     * Vlozi do databazy novu pripomienku, s atributmi danej instancie, nad ktorou
     * sa metoda vola a s aktualnym casom vlozenia.
     * @param $uid - id pouzivatela, ktory bude ulozeny ako autor pripomienky
     */
    // TODO: preco sa ako parameter predava aj user ID ak toto je
    // aj ako premenna modelu ???? (public $id_pedagog;)
    function save($uid)
    {
        $this->casova_peciatka = time();
        //stav pripomienky ma v databaze svoju default hodnotu
        $query =
            "INSERT INTO pripomienka(text, casova_peciatka, id_pedagog, pripomienka_typ_id)
             VALUES ($1, $2, $3, $4)";
        $this->dbh->query($query, array(
            $this->text, $this->casova_peciatka, $uid,
            $this->pripomienka_typ_id
        ));

        // potrbne nastavit este id pripomienky pre notifikator
        $this->id = $this->dbh->GetLastInsertID();
    }

    /**
     * Upravi v databaze atributy reprezentujuce spatnu vazbu pre danu pripomienku
     * (stav pripomienky, typ pripomienky) na zaklade atributov instancie nad
     * ktorou je metoda volana.
     */
    //TODO: naco sa predava id, nie je lepsie to predat ako hidden input ???
    function editSpatnaVazba($id)
    {
        $query =
            "UPDATE pripomienka
             SET pripomienka_typ_id=$1, stav=$2, text=$3
             WHERE id=$4";
        $this->dbh->query($query, array($this->pripomienka_typ_id,
            $this->stav, $this->text, $id)
        );

        // poslem si casovu peciatku, aby sa dala pridat do notifikatora
        // url adresa ktora bude mat aj parameter s filtrom a to bude datum
        $query =
            "SELECT casova_peciatka FROM pripomienka WHERE id=$1";
        $this->dbh->query($query, $id);
        $row = $this->dbh->fetch_row();
        $this->casova_peciatka = date("d.m.Y H:i", $row[0]) ;
    }

    /**
     * Vymaze danu pripomienku z databazy.
     * @param $id - id pripomienky
     */
    function delete($id)
    {
        if (!empty($id))
        {
            $query = "DELETE FROM pripomienka WHERE id=$1";
            $this->dbh->query($query, array($id));
        }
    }

    /**
     * Vrati pole s nazvami moznych stavov pripomienok aj s prisluchajucimi
     * hodnotami reprezentujucimi dany stav v databaze
     * @return array
     */
    function getNazvyStavov()
    {
        return self::$nazvyStavov;
    }

}
?>
