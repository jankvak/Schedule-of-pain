<?php
/*
 opravnenia pouzivatelov, dynamicke menu
 */
class AppController extends Controller {

	//obsahuje odkaz pre help
	public $helpLink = "help/index.php";
	// Pozn.:
	// - kazda dedena trieda musi obsahovat "protected $access=array(....)"
	// s vymenovanim skupin, ktore maju pristup k danej funkcionalite
	// - defaultne sa predpoklada ze som v skupine All
	// - ak sa uvedenie access bude sa kontrolovat ci som prihlaseny a v pozadovanej skupine
	protected $accessValidator;
	protected $notificator;

	public function __construct()
	{
		parent::__construct();
		// Pozn.: nemoze predat session rovno nakolko ta sa pridava do Controlleru neskor
		// v kontruktori este nie je dostupna
		$this->accessValidator = new AccessValidator();
		$this->accessValidator->setController($this);
		$this->notificator = new Notificator();
		$this->notificator->setController($this);
	}

	public function beforeFilter() {
		if(property_exists($this, 'access')) {
			$groups = $this->session->read('groups');
			// kazdy je defaultne v skupine All
			FB::log($groups);
			$groups = array_merge($groups, array("All"));
			$role_match = array_intersect($groups, $this->access);

			// nenasla sa skupina tak ohlasit chybu alebo ak neni prihlaseny, tak dat prihlasovacie okno
			if(empty($role_match)) {
				// kontrola ci je prihlaseny
				$session_uid = $this->session->read('uid');
				if(!isset($session_uid)) {
					// neni prihlaseny takze login stranka + zapamata si kam redirectovat
					$this->session->write("redirect", $this->url);
					$this->redirect('auth/login');
				} else {
					// je prihlaseny tak oznamit ze nema prava
					$this->redirect('system/accessdenied');
				}
			}
		}

	}

	public function beforeRender() {
		 
		$userId = $this->session->read('uid');
		$loggedIn = !empty($userId);

		// ak je prihlaseny iba vtedy ma zmysel kreslit menu a vyber semestra
		if ($loggedIn){
			$menu = new Menu();
			$periods = new Periods();
			$menuHelper = new MenuHelper();
			 
			$selSemester = $this->session->read('semester');

			$semesterHtml = $menuHelper->renderSemester(
			$periods->getShortAll(), $selSemester, $this->url);

			$menuItems = $menu->getForUser($userId);
			$admin = $this->session->read("admin");
			$adminActing = isset($admin);
			$menuHtml = $menuHelper->render($menuItems, $adminActing);
		}else{
			$menuHtml = $semesterHtml = "";
		}

		$this->addToTemplate("SEMESTER", $semesterHtml);
		$this->addToTemplate("MENU", $menuHtml);

		$helpHtml = "<a target='_BLANK' href='$this->helpLink'>Help</a>";
		$this->addToTemplate('HELP', $helpHtml);
	}

	/**
	 * Vrati ID aktualne prihlaseneho pouzivatela
	 * @return int - user ID
	 */
	public function getUserID()
	{
		return $this->session->read("uid");
	}

	/**
	 * Vrati ID semestra s ktorym sa pracuje
	 * @return int - id semestra
	 */
	public function getSemesterID()
	{
		return $this->session->read("semester");
	}

	/**
	 * Zisti ci je aktualne prihlaseny pouzivatel admin
	 * @return boolean - true ak je prihlaseny user admin
	 */
	public function isAdmin()
	{
		$groups = $this->session->read("groups");
		FB::log($sgroups);	
		return array_search("Admin", $groups);
	}
	
	/**
	 * Zisti ci je aktivny semester read-only
	 * @return boolean
	 * @see AccessValidator#isReadOnlySemester()
	 */
	public function isActiveSemesterReadOnly()
	{
		//return $this->accessValidator->isReadOnlySemester($this->getSemesterID());
		return false;
	}

	/**
	 * Zisti ci aktualny pouzivatel moze prehliadat pozidavky daneho predmetu v danej roli.
	 * Ak nie bude presmerovany na $redirect stranku.
	 * @param int $predmetID - id predmetu
	 * @param String $rola - kod role
	 * @param String $redirect - stranka kam presmeruje ak nema prava
	 * @return boolean - true ak moze prezerat, inac false
	 * @see AccessValidator#canSee()
	 */
	public function canSee($predmetID, $rola, $redirect)
	{
		$res = $this->accessValidator->canSee($this->getUserID(), $predmetID, $rola);
		if ($res === false) $this->redirect($redirect);
		
		return $res;
	}

	/**
	 * Zisti ci aktualny pouzivatel v aktualnom semestri
	 * v danej roli moze zmenit poziadavky daneho predmetu.
	 * Ak nie bude presmerovany na stranku $redirect.
	 * @param int $predmetID - id predmetu
	 * @param String $rola - kod role
	 * @param String $redirect - kam rpesmeruje ak nema prava
	 * @return boolean - true ak moze upravit, inac false
	 * @see AccessValidator#canEdit()
	 */
	public function canEdit($predmetID, $rola, $redirect)
	{
		$res = $this->accessValidator->canEdit(
			$this->getUserID(), $predmetID, $this->getSemesterID(), $rola
		);
		if ($res === false) $this->redirect($redirect);
		
		return $res;
	}
}

?>
