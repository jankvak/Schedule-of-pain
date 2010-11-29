<?php
/**
 * Description of profile
 *
 * @author eldrex
 */
class ProfileController extends AppController {

	function __construct() {
		parent::__construct();
		$this->user = new User();
	}

	function index() {
		$this->set('usr', $this->user->findByLogin($this->session->read('username')));
	}

	function save() {
		try
		{
			$checked = $this->bind($this->user);

			if (preg_match("/@is.stuba.sk$/", $this->user->mail))
			{
				$this->flash("Zadajte iný mail, preposielanie mailov na is.stuba.sk nie je možné.", "error");
				throw new dataValidationException($checked);
			}

			$this->user->save($this->getUserID());
            // update info v session			
            // TODO: mozno nejak sikovnejsie pojde
			$this->session->write("notifyMyActions", $this->user->notifyMyActions);
			$this->session->write("mail", $this->user->mail);
			 
			//zalogujeme a oznamime pouzivatelovi
			$this->log("Zmena profilu.");
			$this->flash('Profil zmenený.', 'info');
			$this->redirect('all/profile/index');
		}
		catch(dataValidationException $ex)
		{
			$this->set("usr", $ex->checked);
		}
	}
}
?>
