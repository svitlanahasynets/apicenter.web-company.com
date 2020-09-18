<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class DoLogin extends CI_Controller {

        public function __construct(){
		parent::__construct();
		$this->load->library('session');
		// Load constants
		$this->load->helper('constants');
       }
  
	public function index($email,$timestamp,$hash)
	{
            
            
		
                if( !empty($email) && !empty($timestamp) && !empty($hash) ){
                    
                    $email = urldecode($email);
                    $currentTime = time();
                
                    if( $timestamp < $currentTime - 30 * 60 || $currentTime < $timestamp ) 
                    {
                        exit( "Link has been expired!" );
                    }
                    
                    $autoauthkey = $this->config->item('autoauthkey');
                    
                    $newHash= sha1($email . $timestamp . $autoauthkey);
                    
                    if( $hash == $newHash ){
                        
                        $result = $this->db->get_where('permissions_users', array('user_email' => $email))->result_array();
                        if(!empty($result) && $result = $result[0]){
                            
                            // Set session
                            $dbPassword = $this->encryption->decrypt($result['password']);
                            $sessionPassword = $this->encryption->encrypt($dbPassword);
                            $sessionLimit = LOGIN_TIME_LIMIT;
                            $loginTimestamp = strtotime('+'.$sessionLimit.' minutes', now());
                            $loginTimestamp = $this->encryption->encrypt($loginTimestamp);
                            $this->session->set_userdata('is_logged_in', true);
                            $this->session->set_userdata('username', $result['user_name']);
                            $this->session->set_userdata('password', $sessionPassword);
                            $this->session->set_userdata('timestamp', $loginTimestamp);
                            $this->session->set_userdata('user_email', $result['user_email']);
                            $this->session->set_userdata('fullname', $result['firstname'].' '. $result['lastname'] );
                            $this->session->set_userdata('default_lang', 'english');


                            // Redirect to dashboard if checked
                            redirect('/', 'refresh');
                            return;
                            
                        }
                        
                    }
              
                    
                }
                
                set_error_message('Invalid details!');
                redirect('/login');
		return;
	}

	

	
}

/* End of file DoLogin.php */
/* Location: ./application/controllers/DoLogin.php */

