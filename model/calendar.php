<?php

defined('IN_CMS') or die('No access');

/**
 *
 */
class Calendar extends Model
{
    public $check = array(
    );

    /**
     * Doplni kazdemu eventu v danych eventoch chybajuce atributy potrebne na
     * zobrazenie v kalendari.     
     */
    private function __doplnAtributyAkciam(&$events)
    {
        foreach ($events as &$event)
        {
            if (!array_key_exists('title',$event))
            {
                $sem = Periods::skratenyPopis($event["rok"], $event["semester"]);
                $event['title'] = "Zber požiadaviek pre {$sem}";
            }
            if (!array_key_exists('allDay',$event))
            {
                $event['allDay'] = false;
            }
        }    
    }

    /**
     * Ziska zoznam vsetkych udalosti. Zatial pozera len na rozvrhove akcie.
     * TODO: rozsirit, aby vratil vsetky dolezite udalosti (zaciatok, koniec semestra a pod.)
     * TODO: vyriesit, ktore udalosti su allDay a ktore nie (kratsie ako 1 den??)
     * @return array(
     *    'title' => nazov_udalosti
     *    'start' => cas_zaciatku
     *    'end'   => cas_konca
     *    'allDay'=> false
     *    'url'   => odkaz - momentalne nie je sucastou vystupu
     * )
     */
    public function getAllEvents($semesterId)
    {
        $sql =
            "SELECT zaciatok AS start, koniec AS end, s.rok, s.semester
             FROM rozvrhova_akcia ra
             JOIN semester s ON s.id=ra.id_semester
             WHERE id_semester=$1";
        
        $this->dbh->query($sql,array($semesterId));

        $retArr = $this->dbh->fetchall_assoc();

        $this->__doplnAtributyAkciam($retArr);

        return $retArr;
    }


    /**
     * Ziska zoznam prave prebiehajucich udalosti. Zatial pozera len na rozvrhove akcie.
     * @return array(
     *    'title' => nazov_udalosti
     *    'start' => cas_zaciatku
     *    'end'   => cas_konca
     *    'allDay'=> false
     *    'url'   => odkaz - momentalne nie je sucastou vystupu
     * )
     */
    public function getActualEvents()
    {
        $sql =
            "SELECT ".
            DateConvert::DBTimestampToSkDateTime("zaciatok"). " AS start, ".
            DateConvert::DBTimestampToSkDateTime("koniec"). " AS end,
             s.rok, s.semester
             FROM rozvrhova_akcia ra
             JOIN semester s ON s.id=ra.id_semester
             WHERE zaciatok <= LOCALTIMESTAMP 
             AND koniec > LOCALTIMESTAMP
             ORDER BY start DESC";
                 
        $this->dbh->query($sql);

        $retArr = $this->dbh->fetchall_assoc();

        $this->__doplnAtributyAkciam($retArr);
        
        return $retArr;
    }


    /**
     * Ziska zoznam buducich udalosti. Zatial pozera len na rozvrhove akcie.
     * @return array(
     *    'title' => nazov_udalosti
     *    'start' => cas_zaciatku
     *    'end'   => cas_konca
     *    'allDay'=> false
     *    'url'   => odkaz - momentalne nie je sucastou vystupu
     * )
     */
    public function getFutureEvents()
    {
        $sql =
            "SELECT ".
            DateConvert::DBTimestampToSkDateTime("zaciatok"). " AS start, ".
            DateConvert::DBTimestampToSkDateTime("koniec"). " AS end,
             s.rok, s.semester
             FROM rozvrhova_akcia ra
             JOIN semester s ON s.id=ra.id_semester
             WHERE zaciatok > LOCALTIMESTAMP
             ORDER BY start DESC";                 


        $this->dbh->query($sql);

        $retArr = $this->dbh->fetchall_assoc();

        $this->__doplnAtributyAkciam($retArr);

        return $retArr;
    }

}
?>