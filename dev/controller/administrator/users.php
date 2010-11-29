<?php
class UsersController extends AppController {

	protected $access = array('Admin');
	private $users;

	function __construct() {
		parent::__construct();
		$this->users = new Users();
	}

	function index() {
		$all = $this->users->getAllWithGroups();
		$this->set('users', $all);
		$this->set('current_user_id', $this->session->read("uid"));
		$this->set("disable_role_taking", $this->__adminTakenRole());
	}

	function add() {
		$groups = $this->users->getGroupNames();
		$this->set('groups', $groups);
	}

	function save() {
		$this->bind($this->users);
		// pre istotu ak nic nezada ...
		if (empty($this->users->username)) {
			$this->flash("Nebol zadaný žiaden používateľ !", "error");
			$this->redirect('administrator/users/add');
			return ;
		}
		// pokusi sa ziskat udaje z LDAPu
		if ($this->users->getUserInfoFromLdap() === false) {
			$this->flash("Používateľ s loginom '{$this->users->username}' nie je evidovaný v AISe!", "error");
			$this->redirect('administrator/users/add');
			return ;
		}
		// korekcia 2: nemame uz takeho v systeme ?
		if (($id=$this->users->getUserID()) > 0) {
			$this->flash("Používateľ s loginom '{$this->users->username}' v systéme už existuje.<br/>Presmerované na jeho editáciu.", "error");
			$this->redirect("administrator/users/edit/{$id}");
			return ;
		}
                $this->users->email='';
		$this->users->save();

		$this->log("Pridanie používateľa `{$this->users->fullname}`");
		$this->flash('Používateľ bol uložený', 'info');
		$this->redirect('administrator/users/index');
	}

	function delete($login) {
		$this->users->delete($login);

		$this->log("Používateľ `{$login}` bol odstránený");
		$this->flash("Používateľ $login bol vymazaný");
		$this->redirect('administrator/users/index');
	}

	function edit($id) {
		$this->set('id', $id);
		$login = $this->users->getUserLogin($id);
		$this->set('login', $login);
		$checked = $this->users->getUserGroups($id);
		$this->set('checked', $checked);
		$groups = $this->users->getGroupNames();
		$this->set('groups', $groups);
	}

	function submitEdited() {
		$this->bind($this->users);
		$this->users->edit();

		//TODO: mozno aj meno
		$this->log("Zmena dát používateľa (id={$this->users->id})");
		$this->flash('Používateľ bol zmenený');
		$this->redirect('administrator/users/index');
	}

	function actAs($user_id) {

		if ($user_id == $this->session->read("uid"))
		{
			$this->flash("Prebrať svoje práva nemá zmysel.", "error");
			$this->redirect("administrator/users/index");
			return;
		}

		if ($this->__adminTakenRole())
		{
			$this->flash("V súčastnosti máte prebraté práva. Musíte najprv obnoviť svoje práva, až potom môžete prebrať práva tohoto používateľa.", "error");
			$this->redirect("administrator/users/index");
			return;
		}

		$user = new User();
		$newUser = $user->findById($user_id, true);
		// preberie mu aj obdobie v akom vykonaval
		$newUser["semester"] = $this->session->read('semester');

		if ($newUser["id"]) {

			// ulozi ako vo forme ako je to v DB pre jednoduchost
			$admin = array(
                'id'        => $this->session->read('uid'),
                'meno'      => $this->session->read('name'),
                'login'     => $this->session->read('username'),
                'groups'    => $this->session->read('groups'),
               	'semester'	=> $this->session->read('semester')            
			);

			$this->log("Prebratie práv používateľa '{$newUser["meno"]}'");

			$this->session->writeUser($newUser);
			$this->session->write("admin", $admin);

			$this->flash("Práva úspešne prevedené.", "info");
			$this->redirect("user/home");
		}else {
			$this->flash("Taký používateľ neexistuje.", "error");
			$this->redirect("administrator/users/index");
		}
	}

	/**
	 * Vrati true ak dalsie preberanie prav nie je mozne (dakto je prihlaseny)
	 * @return <boolean> vrati ci v susasnosti ma admin prebrate nejake prava
	 */
	private function __adminTakenRole()
	{
		$admin = $this->session->read("admin");
		return isset($admin);
	}

	function usersearch() {
		$q = $_GET["q"];

		if(!$q) exit();

		$ldap = new Ldap();

		$userinfos = $ldap->find(array("cn" => "*$q*"), array("cn", "uid"));

		foreach($userinfos as $userinfo) {
			echo $userinfo['cn'] . "|" . $userinfo['uid'] . "\n";
		}
		exit();
	}
}


?>
