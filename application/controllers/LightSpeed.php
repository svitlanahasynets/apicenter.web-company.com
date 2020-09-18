<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class LightSpeed extends CI_Controller {
	
	public function __construct(){
		parent::__construct();
        $this->load->helper('tools');
        $this->load->helper('constants');
	}

	
	public function indextest(){
        echo "string";
		die();
	}

}

/* End of file LightSpeed.php */
/* Location: ./application/controllers/LightSpeed.php */