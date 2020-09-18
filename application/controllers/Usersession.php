<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Usersession extends MY_Controller {
	
	// Set session variable
	public function setSessionVariable(){
		if(!(isset($_POST['name']) && $_POST['name'] != '' && isset($_POST['value']) && $_POST['value'] != '')){
			return;
		}
		$data = array( $_POST['name'] => $_POST['value'] );
		$this->session->set_userdata($data);
		return;
	}
	
	// Get session variable
	public function getSessionVariable(){
		if(!(isset($_POST['name']) && $_POST['name'] != '')){
			return;
		}
		echo $this->session->userdata($_POST['name']);
		return $this->session->userdata($_POST['name']);
	}
		
}

/* End of file session.php */
/* Location: ./application/controllers/session.php */