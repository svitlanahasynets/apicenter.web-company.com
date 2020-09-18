<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Whmcs extends CI_Controller {
	
	public function is_secure(){
		$secure_connection = false;
		if(isset($_SERVER['HTTPS'])) {
		    if ($_SERVER['HTTPS'] == "on") {
		        $secure_connection = true;
		    }
		}
		return $secure_connection;
	}
        
        /*
         * Added new inputs - wms, project_title_prefix
         */
	public function create_project(){
		$this->load->model('Projects_model');
		if($this->is_secure() && $this->input->get_post('key') == 'R7lbhPaxZ!vwNy#^Bo@IYOL7BG'){
			$data = array(
				'connection_type' => '1',
				'enabled' => 1,
				'article_interval' => 5,
				'article_amount' => 10,
				'stock_interval' => 5,
				'stock_amount' => 10,
				'article_update_interval' => 5,
				'article_update_amount' => 10,
				'customers_interval' => 5,
				'customers_amount' => 10,
				'orders_interval' => 5,
				'orders_amount' => 10,
			);
			$cmsType = $this->input->get_post('cms');
			if($cmsType == 'magento2'){
				$data['cms'] = 'magento2';
				$data['store_url'] = $this->input->get_post('store_url');
				$data['user'] = $this->input->get_post('cms_user');
				$data['password'] = $this->input->get_post('cms_password');
			}
			$erpType = $this->input->get_post('erp_system');
			if($erpType == 'exactonline'){
				$data['erp_system'] = 'exactonline';
				$data['exactonline_base_url'] = $this->input->get_post('exactonline_base_url');
				$data['exact_article_last_update_date'] = '';
				$data['exactonline_client_id'] = $this->input->get_post('exactonline_client_id');
				$data['exactonline_secret_key'] = $this->input->get_post('exactonline_secret_key');
				$data['exactonline_webhook_secret_key'] = $this->input->get_post('exactonline_webhook_secret_key');
				$data['exactonline_administration_id'] = $this->input->get_post('exactonline_administration_id');
				$data['exactonline_delete_webhooks'] = 1;
				$data['exactonline_import_all_products'] = 1;
				$data['import_exact_description'] = 1;
				$data['import_exact_extra_description'] = 1;
				$data['exact_authorizationcode'] = $this->input->get_post('exact_authorizationcode');
				$data['exact_accesstoken'] = $this->input->get_post('exact_accesstoken');
				$data['exact_expires_in'] = $this->input->get_post('exact_token_expires');
				$data['exact_refreshtoken'] = $this->input->get_post('exact_refresh_token');
			}
			if($this->input->get_post('connection_type') != ''){
				$data['connection_type'] = $this->input->get_post('connection_type');
			}
                        
                        //Add warehouse system setting
                        if($this->input->get_post('wms')){
				$data['wms'] = $this->input->get_post('wms');
			}
			
                        // Project title prefix for project name 
			$mainData['title'] 			= $this->input->get_post('project_title_prefix').'-'.$cmsType.'-'.$erpType;
			$mainData['description']		= $this->input->get_post('store_url').' '.$cmsType.'-'.$erpType;
			$mainData['erp_system'] 		= $data['erp_system'];
			$mainData['store_url'] 			= $data['store_url'];
			$mainData['connection_type'] 	= $data['connection_type'];
			$mainData['creation_date']      = date('Y_m_d');
			
			// Create project
			$this->db->insert('projects', $mainData);
			$project_id = $this->db->insert_id();
			
			// Save project settings
			foreach($data as $code => $value){
				$this->Projects_model->saveValue($code, $value, $project_id);
			}
			
			// Add user & permissions
			echo json_encode(array(
				'success' => true,
				'project_id' => $project_id
			));
			return;
		}
		echo json_encode(array(
			'success' => false
		));
		return;
	}

	public function disable_project(){
		$this->load->model('Projects_model');
		if($this->is_secure() && $this->input->get_post('key') == 'R7lbhPaxZ!vwNy#^Bo@IYOL7BG' && $this->input->get_post('project_id') > 0){
			// Check if project exists
			$project = $this->db->get_where('projects', array('id' => $this->input->get_post('project_id')))->row_array();
			if($project){
				$this->Projects_model->saveValue('enabled', 0, $project['id']);
				
				echo json_encode(array(
					'success' => true
				));
				return;
			}
		}
		echo json_encode(array(
			'success' => false
		));
		return;
	}

	public function enable_project(){
		$this->load->model('Projects_model');
		if($this->is_secure() && $this->input->get_post('key') == 'R7lbhPaxZ!vwNy#^Bo@IYOL7BG' && $this->input->get_post('project_id') > 0){
			// Check if project exists
			$project = $this->db->get_where('projects', array('id' => $this->input->get_post('project_id')))->row_array();
			if($project){
				$this->Projects_model->saveValue('enabled', 1, $project['id']);
				
				echo json_encode(array(
					'success' => true
				));
				return;
			}
		}
		echo json_encode(array(
			'success' => false
		));
		return;
	}
        
        /*
         * Added new input - user_id
         */
	public function remove_project(){
		$this->load->model('Projects_model');
		if($this->is_secure() && $this->input->get_post('key') == 'R7lbhPaxZ!vwNy#^Bo@IYOL7BG' && $this->input->get_post('project_id') > 0 && $this->input->get_post('user_id') > 0){
			// Check if project exists
			$project = $this->db->get_where('projects', array('id' => $this->input->get_post('project_id')))->row_array();
			if($project){
                            
                                // Not removing project settings
				//$this->db->where('project_id', $project['id']);
				//$this->db->delete('project_settings');
				
                                // No need to remove the project
				//$this->db->where('id', $project['id']);
				//$this->db->delete('projects');
                            
                                //Just disable the project
				$this->Projects_model->saveValue('enabled', 0, $project['id']);
                                
				$this->db->where('user_id', $this->input->get_post('user_id'));
				$this->db->where('project_id', $project['id']);
				$this->db->delete('permissions_user_forms');
				
				echo json_encode(array(
					'success' => true
				));
				return;
			}
		}
		echo json_encode(array(
			'success' => false
		));
		return;
	}
	
	public function check_cms_login(){
		$this->load->model('Cms_model');
		if($this->is_secure() && $this->input->get_post('key') == 'R7lbhPaxZ!vwNy#^Bo@IYOL7BG'){
			$cms = $this->input->get_post('cms');
			$data = $this->input->get_post('data');
			if($this->Cms_model->checkLoginCredentials($cms, $data)){
				echo json_encode(array(
					'success' => true
				));
				return;
			}
		}
		echo json_encode(array(
			'success' => false
		));
	}
	
	public function create_user(){
		$this->load->model('Projects_model');
		if($this->is_secure() && $this->input->get_post('key') == 'R7lbhPaxZ!vwNy#^Bo@IYOL7BG'){
			$data = array(
				'user_name' => $this->input->post('user_name'),
				'firstname' => $this->input->post('firstname'),
				'lastname' => $this->input->post('lastname'),
				'password' => $this->input->post('password'),
				'user_email' => $this->input->post('user_email')
			);
			// Check if not empty
			foreach($data as $index => $value){
				if($value == ''){
					echo json_encode(array(
						'success' => false,
						'message' => 'Please enter a value for '.$index
					));
					return;
				}
			}
			$data['user_phone'] = $this->input->post('user_phone');
			
			// CREATE USER
			// Create salt
			$this->load->helper('string');
			$salt = random_string('unique');
	
			// Encrypt the password
			$dbPassword = $this->encryption->encrypt($salt.$data['password']);
			$data['password'] = $dbPassword;
	
			// Check if username exists in database
			$result = $this->db->get_where('permissions_users', array('user_name' => $data['user_name']))->result_array();
			if(!empty($result)){
				echo json_encode(array(
					'success' => false,
					'message' => 'User '.$data['user_name'].' already exists'
				));
				return;
			}
	
			// Check if e-mail address exists in database
			$result = $this->db->get_where('permissions_users', array('user_email' => $data['user_email']))->result_array();
			if(!empty($result)){
				echo json_encode(array(
					'success' => false,
					'message' => 'A user with the same email address already exists. Please enter a different email address.'
				));
				return;
			}
	
			// Save to database
			$data['salt'] = $salt;
			$this->db->insert('permissions_users', $data);
			$user_id = $this->db->insert_id();
			echo json_encode(array(
				'success' => true,
				'id' => $user_id
			));
			return;
		}
		echo json_encode(array(
			'success' => false,
			'message' => ''
		));
		return;
	}
	
	public function set_user_permissions(){
		$this->load->model('Projects_model');
		if($this->is_secure() && $this->input->get_post('key') == 'R7lbhPaxZ!vwNy#^Bo@IYOL7BG'){
			$rules = $this->input->post('rules');
			$user_id = $this->input->post('user_id');
			if(isset($rules['user_rules'])){
				foreach($rules['user_rules'] as $rule){
					$rule['user_id'] = $user_id;
					$checkExisting = $this->db->get_where('permissions_user_rules', array('user_id' => $user_id, 'type' => $rule['type'], 'type_id' => $rule['type_id']))->row_array();
					if($checkExisting){
						$this->db->where('id', $checkExisting['id']);
						$this->db->update('permissions_user_rules', $rule);
					} else {
						$this->db->insert('permissions_user_rules', $rule);
					}
				}
			}
			if(isset($rules['accredit'])){
				foreach($rules['accredit'] as $projectId => $fields){
					foreach($fields as $field => $permission){
						$checkExisting = $this->db->get_where('permissions_user_forms', array('user_id' => $user_id, 'project_id' => $projectId, 'field_code' => $field))->row_array();
						$saveData = array(
							'user_id' => $user_id,
							'project_id' => $projectId,
							'field_code' => $field,
							'permission' => $permission
						);
						if($checkExisting){
							$this->db->where('id', $checkExisting['id']);
							$this->db->update('permissions_user_forms', $saveData);
						} else {
							$this->db->insert('permissions_user_forms', $saveData);
						}
					}
				}
			}
			echo json_encode(array(
				'success' => true
			));
			return;
		}
		echo json_encode(array(
			'success' => false,
			'message' => ''
		));
		return;
	}

	public function add_cron_job(){
		$this->load->model('Projects_model');
		if($this->is_secure() && $this->input->get_post('key') == 'R7lbhPaxZ!vwNy#^Bo@IYOL7BG'){
			$projectId = $this->input->post('project_id');
			if($projectId > 0){
				$fileLocation = explode('/public_html', getcwd());
				$fileLocation = $fileLocation[0].'/cronjobs/';
				$fileContents = file_get_contents($fileLocation.'crons.txt');
				$fileContents .= "\n".'5 * * * * cronlock /usr/bin/curl -sk https://apicenter.web-company.com/index.php/cronjob/index/?project='.$projectId;
				file_put_contents($fileLocation.'crons.txt', $fileContents);
				shell_exec($fileLocation.'importcrons.bash');
				echo json_encode(array(
					'success' => true
				));
				return;
			}
		}
		echo json_encode(array(
			'success' => false,
			'message' => ''
		));
		return;
	}
	
}

/* End of file whmcs.php */
/* Location: ./application/controllers/whmcs.php */