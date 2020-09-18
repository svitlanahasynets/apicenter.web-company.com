<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Remind extends MY_Controller {
	
	public function __construct()
	{
		parent::__construct();
		return;
	}
	
	public function index(){
		$this->load->helper('mailsender/mailsender');
		
		echo "trigger";
		remind_unread_message();
	}
}
