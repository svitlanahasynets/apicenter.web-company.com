<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Errorreport extends CI_Controller {
	
	public function __construct(){
		parent::__construct();
	}
		
	public function index(){
		$file 	= 'projects_data/log_files/37/importcustomers.log';

		$fh 	= @file_exists($file) ? @fopen($file,'r'):false;
		$email_template = '<div style="background: #FBFCFF; margin: 0;color: blue;"><div  style="background:#fff; border-radius:15px 15px 0 0; box-shadow:0 0 20px rgba(0,0,0,0.1); font-family:lota grotesque; margin:50px auto 0; max-width:1040px; text-align: center;"><h1>Daily Status Report</h1><h4>2018-09-04</h4><h3>Current Status</h3><table  width="100%" style="background:#fff; border-radius:0px 0px 0 0; box-shadow:0 0 20px rgba(0,0,0,0.1); font-family:lota grotesque; margin:50px auto 0; max-width:1040px; text-align: left;"><tr><td width="20%">Client Name:<hr></td><td width="80%">Manish Singh<hr></td></tr><tr><td width="20%">Contact No:<hr></td><td width="80%">8013588789<hr></td></tr><tr><td width="20%">Project Name:<hr></td><td width="80%">Woocommerce-ExactOnline (#25) <hr></td></tr></table><h4 style="color: red; text-align: left;"> Some Text maybe?</h4><h3>Activity Per Store</h3><table border="1"  width="100%" style="background:#fff; border-radius:0px 0px 0 0; box-shadow:0 0 20px rgba(0,0,0,0.1); font-family:lota grotesque; margin:50px auto 0; max-width:1040px; text-align: center;"><tr><th width="20%">Store URL:<hr></th><th width="20%">Error Storage:<hr></th><th width="20%">Error:<hr></th><th width="20%">Success:<hr></th><th width="20%">Total:<hr></th></tr>';

		if($fh):
			$current_time = date('Y-m-d H:i');
			while ($line = fgets($fh)) {
			 	$lin_array = explode(' -->', $line);
			 	$log_time = date('Y-m-d H:i', strtotime($lin_array[0]));
			 	if((strtotime($current_time) - strtotime($log_time) ) <= 300){
			 		$success = explode('::', $lin_array[1]);
			 		 
			 		$succ = trim($success[0],' ');
			 		 
			 		if($succ== 'Success'){
			 			$email_template .='<tr><td width="20%"><hr></td><td width="20%"><hr></td><td width="20%"><hr></td><td width="15%">'.$success[1].'<hr></td><td width="20%"><hr></td></tr>';

			 		} else{
			 			$email_template .='<tr><td width="20%"><hr></td><td width="20%"><hr></td><td width="20%">'.$success[1].'<hr></td><td width="15%"><hr></td><td width="20%"><hr></td></tr>';
			 		}
			 	}
			}
			fclose($fh);
		endif;
		$email_template .='<tr><td width="20%">ssssssssssssssss<hr></td><td width="20%"><hr></td><td width="20%"><hr></td><td width="20%"><hr></td><td width="20%"><hr></td></tr></table></div></div>';
		echo $email_template;
		 die();
	}

}

/* End of file exactonline.php */
/* Location: ./application/controllers/exactonline.php */