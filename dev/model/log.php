<?php

defined('IN_CMS') or die('No access');

class Log extends Model
{

    /**
     * Zaloguje zadany zaznam (spolu s casom)
     * @param <string> $username meno pouzivatela
     * @param <String> $substitute kto ho zastupuje
     * @param <string> $event udalost
     */
    public function addLog($username, $substitute, $event)
    {
        $username = $this->dbh->SQLFix($username);
        $event = $this->dbh->SQLFix($event);
        $ip = $_SERVER["REMOTE_ADDR"];
        $sql =
            "INSERT INTO log (username, represent, event, timestamp, ip)
             VALUES ($1, $2, $3, now(), $4)";
        $this->dbh->query($sql, array(
            $username, $substitute, $event, $ip
        ));
    }

    /**
     * Ziska poslednych $limit udalosti
     * @param $limit kolko udalosti ziska
     * @return array udalosti
     */
    public function getEvents($limit = 50)
    {
        return $this->__getEvents($limit);
    }

    /**
     * Ziska vsetky udalosti
     * @return array udalosti
     */
    public function getAllEvents()
    {
        return $this->__getEvents();
    }

    /**
     * Ziska vsetky alebo $limit poseldnych udalosti
     * @param int $limit kolko udalosti ziska (0 ak vsetky)
     * @return array udalosti
     */
    private function __getEvents($limit = 0)
    {
        if (!is_int($limit)) throw new Exception("Limit is not a number");
         
        $sql =
    		"SELECT id, ip, username, represent, event, ".
        DateConvert::DBTimestampToSkDateTime("timestamp")." AS cas
    		 FROM log 
    		 ORDER BY id DESC";
        if ($limit > 0) $sql.= " LIMIT {$limit}";
         
        $this->dbh->Query($sql);
        return $this->dbh->fetchall_assoc();
    }
}
?>
