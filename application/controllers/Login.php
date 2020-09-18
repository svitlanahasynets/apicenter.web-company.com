<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        return;
    }

    /* Display forms */

	public function index()
	{
		// Display login form if action name is not set
		if($this->uri->segment(2) == ''){
			$this->login();
		}
		return;
	}

	public function login(){
		// First log out of all login sessions if there are any
		$this->logoutaction(false);

		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			header("HTTP/1.0 404 login");
			exit();
		}

		$variables = array();
		$variables['page_title'] = translate('APIcenter');

		$data = array();
		$data['variables'] = $variables;
		$data['helpers'] = array('form');
		$data['views'] = array('user/login');
		$data['hide_sidebar'] = true;

		$this->output_data($data);
		return;
	}

	/* End display forms */

	/* Login actions */

	public function loginaction(){

		// Get input data
		$username = $this->db->escape_str($this->input->post('username'));
		$password = $this->db->escape_str($this->input->post('password'));

		// Check if username exists in database
		$result = $this->db->get_where('permissions_users', array('user_name' => $username))->result_array();
		if(!empty($result) && $result = $result[0]){

			// Check if passwords matches
			$dbSalt = $result['salt'];

			$dbPassword = $this->encryption->decrypt($result['password']);
			if($dbPassword == $dbSalt.$password){

				// Set session
				$sessionPassword = $this->encryption->encrypt($dbSalt.$password);
				$sessionLimit = LOGIN_TIME_LIMIT;
				$timestamp = strtotime('+'.$sessionLimit.' minutes', now());
				$timestamp = $this->encryption->encrypt($timestamp);
				$this->session->set_userdata('is_logged_in', true);
				$this->session->set_userdata('username', $username);
				$this->session->set_userdata('password', $sessionPassword);
				$this->session->set_userdata('timestamp', $timestamp);
		        $this->session->set_userdata('user_email', $result['user_email']);
		        $this->session->set_userdata('fullname', $result['firstname'].' '. $result['lastname'] );
                $this->session->set_userdata('default_lang', 'dutch');
				// Redirect to dashboard
				redirect('/', 'refresh');

			} else {
				set_error_message('Username or password incorrect');
				redirect('/login');
				return;
			}

		} else {
			set_error_message('Username or password incorrect');
			redirect('/login');
			return;
		}

	}

	public function logoutaction($redirect = true){
		$this->session->sess_destroy();
		$this->session->set_userdata('is_logged_in', false);
		$this->session->set_userdata('username', false);
		$this->session->set_userdata('password', false);
		$this->session->set_userdata('user_email', false);
		$this->session->set_userdata('timestamp', false);
        $this->session->set_userdata('default_lang', false);
    $this->session->set_userdata('fullname',false);
		if($redirect != false){
			redirect('/login');
		}
		return;
	}

	/* End login actions */
}

/* End of file login.php */
/* Location: ./application/controllers/login.php */
