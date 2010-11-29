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
            "SELECT time_event.start AS start, time_event.end AS end, semester.year AS rok, semester.id AS semester
             FROM   event JOIN semester ON event.id_semester = semester.id
                          JOIN event_time_event e2t ON event.id = e2t.id_event
                          JOIN time_event ON e2t.id_time_event = time_event.id
             WHERE  semester.id = $1";
        
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
                DateConvert::DBTimestampToSkDateTime("time_event.start"). " AS start, ".
                DateConvert::DBTimestampToSkDateTime("time_event.end"). " AS start, ".
            "       semester.year,
                    semester.id
             FROM   event JOIN semester ON event.id_semester = semester.id
                          JOIN event_time_event e2t ON event.id = e2t.id_event
                          JOIN time_event ON e2t.id_time_event = time_event.id
             WHERE  LOCALTIME
                        BETWEEN time_event.start - date_trunc('DAY', time_event.start)
                        AND time_event.end - date_trunc('DAY', time_event.end)
               AND  CAST((EXTRACT('DAY' FROM age(date_trunc('DAY', NOW()), date_trunc('DAY', time_event.start)))) as integer) %recur_freq = 0
               AND  abs(EXTRACT('DAY' FROM age(date_trunc('DAY', NOW()), date_trunc('DAY', time_event.start))/recur_freq)) < recur_count
             ORDER BY time_event.start DESC";
                 
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
                DateConvert::DBTimestampToSkDateTime("time_event.start"). " AS start, ".
                DateConvert::DBTimestampToSkDateTime("time_event.end"). " AS start, ".
            "       semester.year,
                    semester.id
             FROM   event JOIN semester ON event.id_semester = semester.id
                          JOIN event_time_event e2t ON event.id = e2t.id_event
                          JOIN time_event ON e2t.id_time_event = time_event.id
             WHERE  LOCALTIME < time_event.end + time_event.recur_freq * (time_event.recur_count-1) * INTERVAL '1 day'
             ORDER BY time_event.start DESC";
            
        $this->dbh->query($sql);

        $retArr = $this->dbh->fetchall_assoc();

        $this->__doplnAtributyAkciam($retArr);

        return $retArr;
    }

}
?>