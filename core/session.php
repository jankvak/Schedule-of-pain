<?php
class Session {
	public function __construct() {
		@session_start();
	}

	public function read($key) {
		return @$_SESSION[$key];
	}

	public function write($key, $val) {
		$_SESSION[$key] = $val;
	}

	public function clear($key) {
		$_SESSION[$key] = '';
	}

	/**
	 * Odstrani danu premennu zo session
	 * @param <string> $key kluc premennej
	 */
	public function delete($key){
		unset($_SESSION[$key]);
	}

	/**
	 * Vlozi informacie o userovi do session
	 * @param <array> $user informacie o userovi, vo formate:
	 * id - id usera
	 * meno - meno usera
	 * login - username
	 * groups - pole skupin, ktorych je user clenom
	 */
	public function writeUser($user) {
		// vlozi data noveho usera
		$this->write('uid', $user['id']);
		$this->write('name', $user['meno']); // to je uz vyskladane z DB
		$this->write('mail', $user["mail"]);
		$this->write('username', $user['login']);
		$this->write('groups', $user['groups']);
		$this->write('semester', $user['semester']);
                if ($user['posielat_moje_zmeny']=='t')
                    $this->write('notifyMyActions', TRUE);
                else
                    $this->write('notifyMyActions', FALSE);
	}
}
?>
