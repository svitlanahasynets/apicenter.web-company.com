<?php
class MY_Controller extends CI_Controller{

	public function __construct(){
		parent::__construct();

		// Disable any access when updating system
		if(file_exists(FCPATH.'data/maintenance.flag')){
			show_error(translate('System is upgrading. Please try again.'));
			return;
		}
		$this->load->library('session');

		// Load constants
		$this->load->helper('constants');

		// Load some basic functions
		$this->load->helper('tools');
		$this->load->helper('string');

		// Set defaults
		date_default_timezone_set('Europe/Berlin');

		// Initial db install
		$version = $this->config->item('version');
		$version = explode('.', $version);
		$versionFile = FCPATH.'application/version.php';
		if(file_exists($versionFile)){
			$versionInfo = file_get_contents($versionFile);
			$versionInfo = json_decode($versionInfo, true);
			$currentVersion = $versionInfo['version'];
			if($currentVersion == '0.0.0'){ //echo "123"; exit();
				$currentVersion = explode('.', $currentVersion);
				if(($currentVersion[0] < $version[0])
					|| ($currentVersion[0] <= $version[0] && $currentVersion[1] < $version[1])
					|| ($currentVersion[0] <= $version[0] && $currentVersion[1] <= $version[1] && $currentVersion[2] < $version[2])){
					// Execute database update scripts
					$this->load->helper('database');
					if(version_update_database($currentVersion, $version)){
						$currentVersion = implode('.', $currentVersion);
						$version = implode('.', $version);
						$versionInfo['version'] = $version;
						$versionInfo = json_encode($versionInfo);
						file_put_contents($versionFile, $versionInfo);
					} else {
						show_error(translate('Update is mislukt, neem contact op met de beheerder.'));
					}
				}

				// Create admin user
				$salt = random_string('unique');
				$password = random_string('alnum', 8);
				$dbPassword = $this->encryption->encrypt($salt.$password);
				$username = 'admin';
				$user = $this->db->get_where('permissions_users', array('user_name' => $username))->row_array();

				$userData = array(
					'user_name' => $username,
					'password' => $dbPassword,
					'salt' => $salt
				);
				if(empty($user)){
					$this->db->insert('permissions_users', $userData);
					file_put_contents(DATA_DIRECTORY.'/password.php', $password);
				}
			}
		}

		// Login check
		if( $this->session->userdata('username') ){
			$is_logged_in = $this->session->userdata('is_logged_in');
			$username = $this->session->userdata('username');
			$password = $this->session->userdata('password');
			$timestamp = $this->session->userdata('timestamp');
			$logOut = false;

			// Check if username exists in database
			$result = $this->db->get_where('permissions_users', array('user_name' => $username))->result_array();
			if(!empty($result) && $result = $result[0])
			{

				// Check if passwords matches
				if($this->encryption->decrypt($result['password']) == $this->encryption->decrypt($password)){

					// Check if was active within session limit
					$timestamp = $this->encryption->decrypt($timestamp);
					if(now() > $timestamp){
						$logOut = true;
					}
				} else {
					$logOut = true;
				}
			}
			else
			{
				$logOut = true;
			}
		}
		else
		{
			$logOut = true;
		}

		// Do not force login for cronjob
		if($this->uri->segment(1) == 'cronjob' || $this->uri->segment(1) == 'exactonline'){
			$logOut = false;
		}

		// Log out and redirect to login page, if not on login page already
		if($logOut == true){
			$this->session->set_userdata('is_logged_in', false);
			$this->session->set_userdata('username', false);
			$this->session->set_userdata('password', false);
			$this->session->set_userdata('timestamp', false);
			if($this->uri->segment(1) != 'login'
				|| ($this->uri->segment(1) == 'login' &&
						($this->uri->segment(2) != '' && $this->uri->segment(2) != 'loginaction'))){
				/* AJAX check  */
				if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
					if($this->uri->segment(1) == 'usersession'){
						header("HTTP/1.0 404 Not Found");
					} else {
						header("HTTP/1.0 404 login");
					}
					exit();
				} else {
					redirect('/login');
				}
			}
			return;
		}

		// Stay logged in
		$sessionLimit = LOGIN_TIME_LIMIT;
		$timestamp = strtotime('+'.$sessionLimit.' minutes', now());
		$timestamp = $this->encryption->encrypt($timestamp);
		$this->session->set_userdata('timestamp', $timestamp);

		// Update database if necessary
		$version = $this->config->item('version');
		$version = explode('.', $version);
		$versionFile = FCPATH.'application/version.php';
		if(file_exists($versionFile)){
			$versionInfo = file_get_contents($versionFile);
			$versionInfo = json_decode($versionInfo, true);
			$currentVersion = $versionInfo['version'];
			$currentVersion = explode('.', $currentVersion);
			if(($currentVersion[0] < $version[0])
				|| ($currentVersion[0] <= $version[0] && $currentVersion[1] < $version[1])
				|| ($currentVersion[0] <= $version[0] && $currentVersion[1] <= $version[1] && $currentVersion[2] < $version[2])){
				// Execute database update scripts
				$this->load->helper('database');
				if(version_update_database($currentVersion, $version)){
					$currentVersion = implode('.', $currentVersion);
					$version = implode('.', $version);
					$versionInfo['version'] = $version;
					$versionInfo = json_encode($versionInfo);
					file_put_contents($versionFile, $versionInfo);
				} else {
					show_error(translate('Update is mislukt, neem contact op met de beheerder.'));
				}
			}
		}
		return;
	}

	public function output_data($data = array()){

		// Load helpers if defined
		if(isset($data['helpers'])){
			$helpers = $data['helpers'];
			foreach($helpers as $helper){
				$this->load->helper($helper);
			}
		}

		// Load models if defined
		if(isset($data['models'])){
			$models = $data['models'];
			foreach($models as $model){
				$this->load->model($model);
			}
		}

		// Load models if defined
		if(isset($data['libraries'])){
			$libraries = $data['libraries'];
			foreach($libraries as $library){
				$this->load->library($library);
			}
		}

		$menu_items = array();
		if(in_array("projects", $this->config->item('enabled_modules'))
			&& strpos($this->Permissions_model->check_permission_user('access_projects_section', '', $this->session->userdata('username')), 'v') > -1){
			$menu_items[] = array("code" => "projects", "text" => translate('Projects'));
		}
		// Management
		$managerItems = array();
		if(in_array("permissions", $this->config->item('enabled_modules'))
			&& strpos($this->Permissions_model->check_permission_user('access_permissions_section', '', $this->session->userdata('username')), 'v') > -1){
			$managerItems[] = array("code" => "permissions", "text" => translate('Permissions'));
		}
		if(in_array("settings", $this->config->item('enabled_modules'))
			&& strpos($this->Permissions_model->check_permission_user('access_settings_section', '', $this->session->userdata('username')), 'v') > -1){
			$managerItems[] = array("code" => "settings", "text" => translate('Settings'));
		}
		if(!empty($managerItems)){
			$menu_items[] = array(
				"code" => "manager",
				"text" => translate('Management'),
				"children" => $managerItems
			);
		}
		// Load template
		$template = TEMPLATE;

		// Load variables
		
		$variables = isset($data['variables']) ? $data['variables'] : array();
		$this->load->library('pmurl');
		$variables['login_url'] = $this->pmurl->get_login_url();

		//Load menu
		$variables['menu_items'] = $menu_items;
		$variables['menu_html'] = $this->load->view($template.'/'.'menu', $variables, true);

		// Load sidebar
		$sidebar_html = '';
		if(isset($data['sidebar']) && !empty($data['sidebar'])){
			foreach($data['sidebar'] as $sidebar){
				$sidebar_html .= $this->load->view($template.'/'.$sidebar, $data, true);
			}
		} else {
			$variables['hide_sidebar'] = true;
		}
		$variables['sidebar_html'] = $sidebar_html;

		// Load default views
		$this->load->view($template.'/'.'head', $variables);
		$this->load->view($template.'/'.'jscss', $data);
		//Load menu
		if (TEMPLATE != 'default3') {
			$this->load->view($template.'/'.'header', $variables);
		}

		// Display content views if neccessary
		if(isset($data['views']) && !empty($data['views'])){
			foreach($data['views'] as $view){
				$this->load->view($template.'/'.$view, $variables);
			}
		}

		// Load footer
		$this->load->view($template.'/'.'footer', $variables);
	}

}
