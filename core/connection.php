<?php
/*
	napojenie na DB
*/
class Connection {

    private static $dbh;

    protected final function __construct() {
    }

    public static function get() {
        if(!self::$dbh) {
            self::$dbh = new db(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            // objekt je vytvoreny, z bezpecnostneho hladiska zrusime heslo
            // nie je mozne ....
            //define("DB_PASS", "");
            // TODO: pri generovani konstant moze rovno spravit unset $config ?
            global $config;
            unset ($config["DB_PASS"]);
        }

        return self::$dbh;
    }

}

?>
