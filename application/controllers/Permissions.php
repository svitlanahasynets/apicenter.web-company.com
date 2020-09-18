<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Permissions extends MY_Controller {


	/* USER PART */
	public function index(){
		$variables = array();
		$variables['page_title'] = translate('Permissions');
		$variables['active_menu_item'] = 'permissions';

		$data = array();
		$data['helpers'] = array('tools');
		$data['variables'] = $variables;
		$data['js'] = array(
			'form/jquery.stickytableheaders.min.js',
			'form.js'
		);

		// Check permissions
		if(strpos($this->Permissions_model->check_permission_user('list_users', '', $this->session->userdata('username')), 'v') > -1){
			$data['views'] = array('permissions/users/listusers');
		} else {
			set_error_message('You don\'t have permission to perform this action');
			redirect('/projects/');
			return;
		}
		$data['sidebar'] = array('sidebar/permissions/users/listusers');

		$users = $this->db->get('permissions_users')->result_array();
		$data['users'] = $users;

		$this->output_data($data);
	}

	public function createuser(){
		if($this->Permissions_model->check_permission_user('create_user', '', $this->session->userdata('username')) != 've'){
			set_error_message('You don\'t have permission to perform this action');
			redirect('/permissions');
			return;
		}

		$variables = array();
		$variables['page_title'] = translate('Permissions');
		$variables['go_back_url'] = site_url('/permissions');
		$variables['go_back_title'] = translate('Back to permissions');
		$variables['active_menu_item'] = 'permissions';

		$data = array();
		$data['variables'] = $variables;
		$data['helpers'] = array('form');
		$data['models'] = array('Permissions_model');
		$data['views'] = array('permissions/users/createuser');
		$data['sidebar'] = array('sidebar/permissions/users/createuser');
		$data['js'] = array('datepicker/datepicker.js', 'form.js', 'permissions/jquery.fancytree.js', 'permissions/jquery.fancytree.table.js', 'permissions/createuser.js');
		$data['css'] = array('datepicker/default.css', 'permissions/permissions.css', 'permissions/ui.fancytree.css');

		// Load all project data
		$projects = $this->db->get('projects')->result_array();
		// TODO: Only load projects accessible for current user
		$data['projects'] = $projects;

		$this->output_data($data);
	}

	public function createuseraction(){
		if($this->Permissions_model->check_permission_user('create_user', '', $this->session->userdata('username')) != 've'){
			set_error_message('You don\'t have permission to perform this action');
			redirect('/permissions');
			return;
		}

		// Get input data
		$username = $this->db->escape_str($this->input->post('user_name', true));
		$firstname = $this->db->escape_str($this->input->post('firstname', true));
		$lastname = $this->db->escape_str($this->input->post('lastname', true));
		$password = $this->db->escape_str($this->input->post('password', true));
		$user_email = $this->db->escape_str($this->input->post('user_email', true));
		$user_phone = $this->db->escape_str($this->input->post('user_phone', true));
		$user_role = $this->db->escape_str($this->input->post('user_role', true));

		// Create salt
		$this->load->helper('string');
		$salt = random_string('unique');

		// Encrypt the password
		$dbPassword = $this->encryption->encrypt($salt.$password);

		// Check if username exists in database
		$result = $this->db->get_where('permissions_users', array('user_name' => $username))->result_array();
		if(!empty($result)){
			set_error_message('User '.$username.' already exists');
			redirect('/permissions/createuser');
			return;
		}

		// Check if e-mail address exists in database
		$result = $this->db->get_where('permissions_users', array('user_email' => $user_email))->result_array();
		if(!empty($result)){
			set_error_message('A user with the same email address already exists. Please enter a different email address.');
			redirect('/permissions/createuser');
			return;
		}

		// Check if fields are not empty
		if($username == '' || $password == '' || $user_email == ''){
			set_error_message('Please fill in all fields');
			redirect('/permissions/createuser');
			return;
		}

		$data = array();

		// save logo picture to database

		if ($user_role == 'partner') {

			$logo_picture = $_FILES['logo_picture'];

			$datavalid_logo_picture = true;

			if(trim($logo_picture['name']) != '') {
				$fileExt = pathinfo($logo_picture['name'], PATHINFO_EXTENSION);
				$pathinfo = pathinfo($logo_picture['name']);

				if($fileExt != 'jpg' && $fileExt != 'jpeg' && $fileExt != 'png') {
					$datavalid_logo_picture = false;
					set_error_message('Please upload jpg/jpeg/png file for logo!');
					redirect('/myaccount');
					return;
				}

				if($datavalid_logo_picture) {
					list($width, $height, $type, $attr) = getimagesize($logo_picture['tmp_name']);

					if($width < 24 || $height < 24) {
						$datavalid_logo_picture = false;	
						set_error_message('Logo Image size is not valid.');
						redirect('/myaccount');
						return;				
					}
				}

				if ($datavalid_logo_picture) {
					$logo_picture_folder = SOURCE_DIRECTORY.'/assets/images/sidebar/';

					$filename = $logo_picture_folder.trim($logo_picture['name']);

					if (!file_exists($filename)) {

					    if(!move_uploaded_file($logo_picture['tmp_name'], $filename)) {
					    	set_error_message('Logo File upload is failed.');
							redirect('/myaccount');
							return;	
						}

						$data['partner_logo'] = trim($logo_picture['name']);
					
					} else {

						$filename = $logo_picture_folder.trim($pathinfo['filename'].'_1.'.$pathinfo['extension']);

						if(!move_uploaded_file($logo_picture['tmp_name'], $filename)) {
					    	set_error_message('Logo File upload is failed.');
							redirect('/myaccount');
							return;	
						}
						
						$data['partner_logo'] = trim($pathinfo['filename'].'_1.'.$pathinfo['extension']);
					}
				}			
			} else {
				$data['partner_logo'] = '';
			}
		}

		// Save profile picture to database

		$profile_picture = $_FILES['profile_picture'];

		$datavalid = true;

		if(trim($profile_picture['name']) != '') {
			$fileExt = pathinfo($profile_picture['name'], PATHINFO_EXTENSION);
			$pathinfo = pathinfo($profile_picture['name']);

			if($fileExt != 'jpg' && $fileExt != 'jpeg' && $fileExt != 'png') {
				$datavalid = false;
				set_error_message('Please upload jpg/jpeg/png file for profile!');
				redirect('/myaccount');
				return;
			}

			if($datavalid) {
				list($width, $height, $type, $attr) = getimagesize($profile_picture['tmp_name']);

				if($width < 64 || $height < 64) {
					$datavalid = false;	
					set_error_message('Profile Image size is not valid.');
					redirect('/myaccount');
					return;				
				}
			}

			if ($datavalid) {
				$profile_picture_folder = DATA_DIRECTORY.'/profile_pictures/';

				$filename = $profile_picture_folder.trim($profile_picture['name']);

				if (!file_exists($filename)) {

				    if(!move_uploaded_file($profile_picture['tmp_name'], $filename)) {
				    	set_error_message('Profile File upload is failed.');
						redirect('/myaccount');
						return;	
					}

					$data['profile_picture'] = trim($profile_picture['name']);
				
				} else {

					$filename = $profile_picture_folder.trim($pathinfo['filename'].'_1.'.$pathinfo['extension']);

					if(!move_uploaded_file($profile_picture['tmp_name'], $filename)) {
				    	set_error_message('Profile File upload is failed.');
						redirect('/myaccount');
						return;	
					}
					
					$data['profile_picture'] = trim($pathinfo['filename'].'_1.'.$pathinfo['extension']);
				}
			}			
		} else {
			$data['profile_picture'] = '';
		}

		$data['user_name'] = $username;
		$data['firstname'] = $firstname;
		$data['lastname'] = $lastname;
		$data['password'] = $dbPassword;
		$data['salt'] = $salt;
		$data['user_email'] = $user_email;
		$data['user_phone'] = $user_phone;
		$data['role'] = $user_role;

		$this->db->insert('permissions_users', $data);
		$user_id = $this->db->insert_id();

		// Commented out because don't need to save permissions for specific user when creating user, only when editing user
		//$this->savepermissions($this->input->post('permissions'), 'user', $user_id);

		// Set success message and redirect to permissions landing page
		set_success_message('User '.$username.' created');
		redirect('/permissions/');
		return;
	}

	// View user information page
	public function viewuser(){
		if(strpos($this->Permissions_model->check_permission_user('list_users', '', $this->session->userdata('username')), 'v') == -1){
			set_error_message('You don\'t have permission to perform this action');
			redirect('/permissions');
			return;
		}

		$variables = array();
		$variables['page_title'] = translate('Permissions');
		$variables['go_back_url'] = site_url('/permissions');
		$variables['go_back_title'] = translate('Back to permissions');
		$variables['active_menu_item'] = 'permissions';

		$data = array();
		$data['variables'] = $variables;
		$data['helpers'] = array('form', 'tools');
		$data['views'] = array('permissions/users/viewuser');
		$data['sidebar'] = array('sidebar/permissions/users/viewuser');

		$url_data = $this->uri->uri_to_assoc(1);
		$user_id = $url_data['id'];
		$users = $this->db->get_where('permissions_users', array('user_id' => $user_id))->result_array();
		if(!empty($users)){
			$data['user'] = $users[0];
			$this->output_data($data);
		} else {
			set_error_message('Error while finding user');
			redirect('/permissions');
		}
		return;
	}

	// Edit user information page
	public function edituser(){
		if($this->Permissions_model->check_permission_user('list_users', '', $this->session->userdata('username')) != 've'){
			set_error_message('You don\'t have permission to perform this action');
			redirect('/permissions');
			return;
		}

		$variables = array();
		$variables['page_title'] = translate('Permissions');
		$variables['go_back_url'] = site_url('/permissions');
		$variables['go_back_title'] = translate('Back to permissions');
		$variables['active_menu_item'] = 'permissions';

		$data = array();
		$data['variables'] = $variables;
		$data['helpers'] = array('form', 'tools');
		$data['models'] = array('Permissions_model');
		$data['views'] = array('permissions/users/edituser');
		$data['sidebar'] = array('sidebar/permissions/users/edituser');
		$data['css'] = array('permissions/permissions.css', 'permissions/ui.fancytree.css');
		$data['js'] = array('permissions/jquery.fancytree.js', 'permissions/jquery.fancytree.table.js', 'permissions/permissions.js');

		// Load all project data
		$projects = $this->db->get('projects')->result_array();
		// TODO: Only load projects accessible for current user

		$data['projects'] = $projects;


		$url_data = $this->uri->uri_to_assoc(1);
		$user_id = $url_data['id'];
		$users = $this->db->get_where('permissions_users', array('user_id' => $user_id))->result_array();
		if(!empty($users)){
			$data['user'] = $users[0];
			$this->output_data($data);
		} else {
			set_error_message('Error while finding user');
			redirect('/permissions');
		}
		return;
	}

	public function saveuseraction(){
		if($this->Permissions_model->check_permission_user('list_users', '', $this->session->userdata('username')) != 've'){
			set_error_message('You don\'t have permission to perform this action');
			redirect('/permissions');
			return;
		}

		$data = array();

		// Get input data
		$user_id = $this->db->escape_str($this->input->post('user_id', true));
		$user = $this->db->get_where('permissions_users', array('user_id' => $user_id))->result_array();
		$user = $user[0];
		$username = $this->db->escape_str($this->input->post('user_name', true));
		$firstname = $this->db->escape_str($this->input->post('firstname', true));
		$lastname = $this->db->escape_str($this->input->post('lastname', true));
		$password = $this->db->escape_str($this->input->post('password', true));
		$user_email = $this->db->escape_str($this->input->post('user_email', true));
		$user_phone = $this->db->escape_str($this->input->post('user_phone', true));
		$user_role = $this->db->escape_str($this->input->post('user_role', true));

		$partner_ids = $this->db->escape_str($this->input->post('partner_id', true));

		foreach ($partner_ids as $project_id => $partner_id) {

			$project_data['partner_id'] = $partner_id;

			$this->db->where('id', $project_id);
			$this->db->update('projects', $project_data);
		}

		if($password != ''){
			// Create salt
			$this->load->helper('string');
			$salt = random_string('unique');
			$data['salt'] = $salt;
			// Encrypt the password
			$dbPassword = $this->encryption->encrypt($salt.$password);
			$data['password'] = $dbPassword;
		}

		// Check if fields are not empty
		if($username == '' || $user_email == ''){
			set_error_message('Please fill in all fields');
			redirect('/permissions/edituser/id/'.$user_id);
			return;
		}

		// Check if e-mail address exists in database
		if($user['user_email'] != $user_email && $user_email != ''){
			$data['user_email'] = $user_email;
			$result = $this->db->get_where('permissions_users', array('user_email' => $user_email))->result_array();
			if(!empty($result)){
				set_error_message('A user with the same email address already exists. Please enter a different email address.');
				redirect('/permissions/edituser/id/'.$user_id);
				return;
			}
		}

		// Save to database
		$data['firstname'] = $firstname;
		$data['lastname'] = $lastname;
		$data['user_phone'] = $user_phone;
		$data['role'] = $user_role;

		$this->db->where('user_id', $user_id);
		$this->db->update('permissions_users', $data);
		$this->savepermissions($this->input->post('permissions'), 'user', $user_id);

		// Set success message and redirect to permissions landing page
		set_success_message('User '.$username.' was updated');
		redirect('/permissions/');
		return;
	}

	public function deleteuser(){
		if($this->Permissions_model->check_permission_user('list_users', '', $this->session->userdata('username')) != 've'){
			set_error_message('You don\'t have permission to perform this action');
			redirect('/permissions');
			return;
		}
		// Delete user
		$url_data = $this->uri->uri_to_assoc(1);
		$user_id = $url_data['id'];
		$this->db->where('user_id', $user_id);
		$this->db->delete('permissions_users');
		$this->load->model('Permissions_model');
		$this->Permissions_model->delete_permissions('user', $user_id);
		$this->Permissions_model->delete_connections('user', $user_id);

		// Set success message and redirect to projects overview
		set_success_message('User was deleted');
		redirect('/permissions');

		return;
	}
	/* END OF USER PART */

	/* PERMISSIONS PART */
	public function savepermissions($projects, $save_type, $save_id){

		if($save_type == 'user'){
			if($this->Permissions_model->check_permission_user('list_users', '', $this->session->userdata('username')) != 've'){
				set_error_message('You don\'t have permission to perform this action');
				redirect('/permissions');
				return;
			}
			$username 		= $this->session->userdata('username');
			$user 				= $this->db->get_where('permissions_users', array('user_name' => $username))->row_array();
			$assigned_by = $user['user_id'];

			$this->db->where('user_id', $save_id);
			$this->db->delete('permissions_user_rules');
			$projects = $projects['projects'];
			foreach($projects as $type => $subgroup){
				foreach($subgroup as $type_id => $item){
					if(!isset($item['edit'])){
						$item['edit'] = 0;
					}

					$filter_data = array(
						'type' => $type,
						'type_id' => $type_id
					);

					$this->db->select('*');
					$this->db->from('permissions_user_rules');
					$this->db->where($filter_data);
					$query = $this->db->get();
					$other_user_rules = $query->result();

					if (count($other_user_rules)) {
						$update_data = array();
						$update_data['user_id'] = $save_id;
				        $update_data['view'] = $item['view'] || null;
				        $update_data['edit'] = $item['edit'] || null;

						$this->db->where($filter_data);
				        $this->db->update('permissions_user_rules', $update_data);
					} else {
						$data = array(
							'user_id' => $save_id,
							'type' => $type,
							'type_id' => $type_id,
							'view' => $item['view'] || null,
							'edit' => $item['edit'] || null
						);
						$this->db->insert('permissions_user_rules', $data);
					}
					
					if($type=='project'){
						$this->Permissions_model->saveFormPermission($data, $assigned_by);
					}
				}
			}
		}
	}

	public function saveuseractionformauth($value=''){
			 if($_POST){
				 $user_id 	  = $_POST['user_id'];
				 $project_id  = $_POST['project_id'];
				 $permissions = $_POST['permission'];

				 $this->db->where('user_id', $user_id);
				 $this->db->where('project_id', $project_id);
				 $this->db->delete('permissions_user_forms');
				 $username 		= $this->session->userdata('username');
		 		 $user 				= $this->db->get_where('permissions_users', array('user_name' => $username))->row_array();
		 		 $assigned_by = $user['user_id'];

				 foreach ($permissions as $key => $value) {
						 $permission =  isset($value['cve'])?'cve':(isset($value['ve'])?'ve':(isset($value['v'])?'v':''));
						 if($permission=='')
								continue;
						 $insert_data = array(
							 'user_id'		=> $user_id,
							 'project_id'	=> $project_id,
							 'field_code'	=> $key,
							 'permission'	=> $permission,
							 'assigned_by'=> $assigned_by,
						 );
						 $this->db->insert('permissions_user_forms', $insert_data);
				 }
					set_success_message('User '.$username.'`s permission  was updated successfully. ');
					redirect('/permissions/edituser/id/'.$user_id);
					return;
			 } else{
					redirect('/permissions/');
					return;
			 }
	}


	function convert_to_recursive_array($array){
		$result = array();
		foreach($array as $item){
			$type = $item['type'];
			$type_id = $item['type_id'];
			$result[$type][$type_id] = array(
				'view' => $item['view'],
				'edit' => $item['edit']
			);
		}
		return $result;
	}

	public function AccrediterenField(){

		$url_data 		= $this->uri->uri_to_assoc(1);
		$user_id 		= $url_data['id'];
		$project_id 	= $url_data['pid'];
		$variables 		= array();
		$variables['page_title'] = translate('Permissions');
		$variables['go_back_url'] = site_url('/permissions/edituser/id/'.$user_id);
		$variables['go_back_title'] = translate('Back to Edit user');
		$variables['active_menu_item'] = 'permissions';
		$data = array();
		$data['variables'] = $variables;
		$data['helpers'] = array('form', 'tools');
		$data['models'] = array('Projects_model', 'Permissions_model');
		$data['libraries'] = array('Pmprojects');
		$data['views'] = array('permissions/users/accessformfield');
		$data['js'] = array('datepicker/datepicker.js', 'form.js', 'projects/create-project.js');
		$data['css'] = array('datepicker/default.css');
		// Load project form setting data
		$project_settings1 = $this->db->get('project_from_settings');
		$project_settings2 = array();
		foreach ($project_settings1->result_array() as $row){
			$row['values'] 			= json_decode($row['values'], true);
			$row['depends_on'] 		= json_decode($row['depends_on'], true);
			$row['fields'] 			= json_decode($row['fields'], true);
			$row['permission']  	= $this->db->get_where('permissions_user_forms',['user_id'=>$user_id, 'project_id'=>$project_id, 'field_code'=>$row['code']])->row_array()?$this->db->get_where('permissions_user_forms',['user_id'=>$user_id, 'project_id'=>$project_id, 'field_code'=>$row['code']])->row_array()['permission']:'';
			$project_settings2[] = $row;
		}
		$this->load->model('Projectfromsettings_model');
		$permission = $this->Projectfromsettings_model->getStaticFieldPermission($user_id, $project_id);
		// Load project data
		$project_details = $this->db->get_where('projects',['id'=>$project_id])->row_array();
		//print_r($project_settings2);
		$data['project'] = $project_details;
		$data['project_settings'] = $project_settings2;
		$data['permission'] = $permission;
		$users = $this->db->get_where('permissions_users', array('user_id' => $user_id))->result_array();
		if(!empty($users)){
			$data['user'] = $users[0];
			$this->output_data($data);
		} else {
			set_error_message('Error while finding user');
			redirect('/permissions');
		}
		return;

	}

	// save account information
	public function savemyaccount(){
		
		$data = array();

		// Get input data
		$user_id = $this->db->escape_str($this->input->post('user_id', true));
		$user = $this->db->get_where('permissions_users', array('user_id' => $user_id))->result_array();
		$user = $user[0];
		$user_name = $this->db->escape_str($this->input->post('user_name', true));
		$firstname = $this->db->escape_str($this->input->post('firstname', true));
		$lastname = $this->db->escape_str($this->input->post('lastname', true));
		$password = $this->db->escape_str($this->input->post('password', true));
		$user_email = $this->db->escape_str($this->input->post('user_email', true));
		$user_phone = $this->db->escape_str($this->input->post('user_phone', true));

		$profile_picture = $_FILES['profile_picture'];

		$datavalid = true;

		if(trim($profile_picture['name']) != '') {
			$fileExt = pathinfo($profile_picture['name'], PATHINFO_EXTENSION);
			$pathinfo = pathinfo($profile_picture['name']);

			if($fileExt != 'jpg' && $fileExt != 'jpeg' && $fileExt != 'png') {
				$datavalid = false;
				set_error_message('Please upload jpg/jpeg/png file!');
				redirect('/myaccount');
				return;
			}

			if($datavalid) {
				list($width, $height, $type, $attr) = getimagesize($profile_picture['tmp_name']);

				if($width < 64 || $height < 64) {
					$datavalid = false;	
					set_error_message('Image size is not valid.');
					redirect('/myaccount');
					return;				
				}
			}

			if ($datavalid) {
				$profile_picture_folder = DATA_DIRECTORY.'/profile_pictures/';

				$filename = $profile_picture_folder.trim($profile_picture['name']);

				if (!file_exists($filename)) {

				    if(!move_uploaded_file($profile_picture['tmp_name'], $filename)) {
				    	set_error_message('File upload is failed.');
						redirect('/myaccount');
						return;	
					}

					$data['profile_picture'] = trim($profile_picture['name']);
				
				} else {

					$filename = $profile_picture_folder.trim($pathinfo['filename'].'_1.'.$pathinfo['extension']);

					if(!move_uploaded_file($profile_picture['tmp_name'], $filename)) {
				    	set_error_message('File upload is failed.');
						redirect('/myaccount');
						return;	
					}
					
					$data['profile_picture'] = trim($pathinfo['filename'].'_1.'.$pathinfo['extension']);
				}
			}			
		} else {
			$data['profile_picture'] = '';
		}

		$data['user_name'] = $user_name;
		$data['firstname'] = $firstname;
		$data['lastname'] = $lastname;
		$data['user_email'] = $user_email;
		$data['user_phone'] = $user_phone;

		$result_arr = $this->db->get_where('permissions_users', array('user_name' => $user_name))->result_array();
		$result = $result_arr[0];
		$dbSalt = $result['salt'];
		$dbPassword = $this->encryption->decrypt($result['password']);

		if($dbPassword != $dbSalt.$password){
			if($password != ''){
				// Create salt
				$this->load->helper('string');
				$salt = random_string('unique');
				$data['salt'] = $salt;
				// Encrypt the password
				$dbPassword = $this->encryption->encrypt($salt.$password);
				$data['password'] = $dbPassword;
			}
		}

		// Check if fields are not empty
		if($user_name == '' || $user_email == ''){
			set_error_message('Please fill in all fields');
			redirect('/myaccount');
			return;
		}

		$this->db->where('user_id', $user_id);
		$this->db->update('permissions_users', $data);
		// $this->savepermissions($this->input->post('permissions'), 'user', $user_id);

		// Set success message and redirect to permissions landing page
		set_success_message('User '.$user_name.' was updated');
		redirect('/permissions');
		return;
	}
	/* END OF PERMISSIONS PART */

}

/* End of file permissions.php */
/* Location: ./application/controllers/permissions.php */
