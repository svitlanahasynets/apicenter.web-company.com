<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Form extends MY_Controller {

	public function set_filter_data(){
		$currentData = $this->session->userdata('filter_data');
		if(is_null($currentData)){
			$currentData = array();
		}
		
		$fields = $this->input->post('fields');
		parse_str($fields, $fields);
		$module = $this->input->post('module');
		$action = $this->input->post('action');
		
		$currentData[$module][$action] = $fields;
		
		$this->session->set_userdata('filter_data', $currentData);
		return;
	}
	
	public function get_filter_data(){
		$currentData = $this->session->userdata('filter_data');
		if(is_null($currentData)){
			$currentData = array();
		}
		
		$module = $this->input->post('module');
		$action = $this->input->post('action');
		
		if(isset($currentData[$module][$action])){
			$data = $currentData[$module][$action];
		} else {
			$data = array();
		}
		echo json_encode($data);
		return;
	}
	
	
	
	/* PREFERENCES PART */
	public function update_user_preference(){
		$preferenceCode = $this->input->post('preference');
		$value = $this->input->post('value');
		set_user_preference($preferenceCode, $value);
	}
	/* END OF PREFERENCES PART */
	
}

/* End of file form.php */
/* Location: ./application/controllers/form.php */