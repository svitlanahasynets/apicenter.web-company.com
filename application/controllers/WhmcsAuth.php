<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class WhmcsAuth extends MY_Controller {
	
	
	public function index($goto = false){
            
           
           // Define WHMCS URL & AutoAuth Key
           $whmcsurl = "https://account.web-company.com/dologin.php";
           $autoauthkey = "asdSJUFHKfh45674623bfhsjd67834df";

           $timestamp = time(); // Get current timestamp
           $email = $this->session->userdata('user_email'); // Clients Email Address to Login
           if( !empty($goto) &&  $goto == 'submitticket'){
               $goto = 'submitticket.php';
           }else{
               $goto = 'clientarea.php?action=products';
           }
           

           $hash = sha1($email . $timestamp . $autoauthkey); // Generate Hash

           // Generate AutoAuth URL & Redirect
           //$url = $whmcsurl . "?email=$email&amp;timestamp=$timestamp&amp;hash=$hash&amp;goto=" . urlencode($goto);
           $url = $whmcsurl . "?timestamp=$timestamp&email=$email&hash=$hash&goto=" . urlencode($goto);
           redirect($url, 'location');
           exit;

	}

	
	
}

/* End of file WhmcsAuth.php */
/* Location: ./application/controllers/WhmcsAuth.php */
