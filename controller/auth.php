<?php

/*
	prihlasovanie pouzivatelov
*/

class AuthController extends AppController {
    var $auth = null;
    var $user = null;

    function __construct() 
    {
		parent::__construct();
    	$this->auth = new Auth();
        $this->user = new User();
    }

    function login() {
    	// kontrola ci uz je pouzivatel prihlaseny
		// ak je tak redirectnut na user/home
		$session_uid = $this->session->read('uid');
		 
		if(isset($session_uid)) {
			$this->redirect('user/home');
		}
		
		// defined je small hack lebo ak nastavim true FORCE_HTTPS_LOGIN ma hodnotu 1
    	if (BASE_ROOT == "http" && defined("FORCE_HTTPS_LOGIN") && FORCE_HTTPS_LOGIN)
    	{
    		$this->redirect("auth/login", true);
    	}
    }

    function logout() {
    	$user_id = $this->session->read("uid");
    	// ak je niekto lognuty odhlasi
    	if (!empty($user_id))
    	{    	
        	$this->log("Odhlásenie používateľa");
        	$this->auth->logout();
        	$this->flash('Boli ste úspešne odhlásený.', 'info');
    	}
    	
        $this->redirect('auth/login');
    }

    function dologin() {
        $rememberme = @$_POST['rememberme'];
        $username = stripslashes($_POST['name']);
        $password = stripslashes($_POST['passwd']);

        if($rememberme) {
            $this->auth->setCookie($username);
        } else {
            $this->auth->deleteCookie();
        }

        // najprv sa vykona autentifikacia voci LDAP (korektny login + heslo)
        try {
            $this->auth->login($username, $password);
        }catch(LoginException $ex) {
            $this->log("Neplatné meno `{$username}` alebo heslo");
            $this->flash("Bolo zadané neplatné heslo alebo meno.", "error");
            $this->redirect('auth/login');
            return;
        }
        $user = $this->user->findByLogin($username);

        if(empty($user)) {
            $this->log("Pokus o prihlásenie neevidovanej osoby `{$username}`");
            $this->flash("Ľutujeme, nemáte prístup do tejto aplikácie. Kontaktujte prosím <a href='mailto:galbavy@fiit.stuba.sk' title='Poslať mail administratorovi'>administrátora aplikácie</a>", "error");
            $this->redirect('auth/login');
            return;
        }
        
        // nastavi aktivny semester
        // TODO: uchovavat v DB pre usera jeho poslednu hodnotu
        $periods = new Periods;
        $lastSemID = $periods->getLastSemesterID();
        $user["semester"] = $lastSemID;  
        $this->session->writeUser($user);
        $this->log("Prihlásenie používateľa");
        
        $redirect = $this->session->read("redirect");
        if ($redirect)
        {
        	// zmaze presmerovanie a presmetuje
        	$this->session->delete("redirect");
        	$this->redirect($redirect);
        }else $this->redirect('user/home');
    }

    function restoreAdmin() {

        $admin = $this->session->read("admin");
        if (isset($admin)) {
            $this->session->writeUser($admin);
            $this->session->delete("admin");
            
            $this->log("Obnova svojich práv.");
            $this->flash("Práva úspešne obnovené.", "info");
            $this->redirect("user/home");
        }else
        {
            // presmeruj ho, nemame pohlad na restoreAdmin
            $this->redirect("user/home");
        }
    }
}

?>
