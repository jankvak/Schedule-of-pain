<?php

/*
 prihlasenie pouzivatela
 pouzivatel sa prihlasuje pomocou prihlasovacieho mena a hesla.
 ak je nastavene pouzivanie LDAP, tak sa autentifikuje cez LDAP server.
 do databazy sa vlozia informacie o prihlasenom pouzivatelovi.
 */

defined('IN_CMS') or die('No access');

class Auth extends Model {

    public $ldap;

    public function __construct() {
        parent::__construct();
        $this->ldap = new Ldap();
    }

    public function setCookie($name) {
        setcookie("username", addslashes($name));
    }

    public function deleteCookie() {
        setcookie("username", "", time()-3600);
    }

    public function getCookie() {
        return @$_COOKIE['username'];
    }

    public function login($username, $password) {
    	FB::log($username);
        if(USE_LDAP) {
            try {
                $this->ldap->autentificate($username, $password);
            } catch(LdapException $e) {
                throw new LoginException($e);
            }
        }
    }

    public function logout() {
        // session_destroy zrusilo session a tak ak bol nejaky flash ten sa stratil
        // namiesto povodneho postacuje session_unset, ktore zrusi vsetky registrovane premenne
        session_unset();
        // zaznaci poslednu operaciu (logout)
        $_SESSION["start"] = time();
    }
}
?>
