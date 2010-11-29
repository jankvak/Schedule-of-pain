<?php

class LogController extends AppController
{
	protected $access = array("Admin");
	
	public function __construct()
	{
		parent::__construct();
		$this->log = new Log();
	}

	public function view()
	{
		$this->set("events", $this->log->getEvents());
	}
	
	public function view_all()
	{
		$this->set("events", $this->log->getAllEvents());		
	}
}

?>