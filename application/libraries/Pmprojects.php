<?php
class pmprojects {
	
	private $CI;
	
	public function __construct(){
		$this->CI = get_instance();
	}
	
	public function get_contact_person_name($user_id){
		$CI = $this->CI;
		$contact_person = $CI->db->get_where('permissions_users', array('user_id' => $user_id))->row_array();
		if(!empty($contact_person)){
			return $contact_person['firstname'].' '.$contact_person['lastname'];
		} else {
			return '';
		}
	}
	
}