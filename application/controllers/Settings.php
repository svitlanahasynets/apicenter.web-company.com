<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Settings extends MY_Controller {
	
	public function __construct(){
		parent::__construct();
		
		// Disable any access if user has no permission or if module is inactive
		$permission = $this->Permissions_model->check_permission_user('access_settings_section', '', $this->session->userdata('username'));
		if(($permission != 've' && $permission != 'v') || !in_array("settings", $this->config->item('enabled_modules'))){
			set_error_message('Settings section can\'t be accessed');
			redirect('/');
		}
	}

	public function index(){
		$this->config->load('settings', true);
		$settings = $this->config->item('settings');
		$sections = $settings['sections'];
		$firstSection = $sections[0];
		redirect('/settings/section/code/'.$firstSection['code']);
		return;
	}
	
	public function section(){
		$variables = array();
		$variables['page_title'] = translate('Settings');
		$variables['active_menu_item'] = 'settings';
		
		$data = array();
		$data['variables'] = $variables;
		$data['models'] = array('Permissions_model', 'Settings_model');
		$data['helpers'] = array('form');
		$data['views'] = array('settings/section');
		$data['sidebar'] = array('sidebar/settings/index');
		$data['js'] = array('form.js');
		$data['css'] = array('settings/settings.css');
		
		$currentSection = $this->uri->segment(4);
		if($currentSection == ''){
			return;
		}
		
		$this->config->load('settings', true);
		$settings = $this->config->item('settings');
		$data['sections'] = $settings['sections'];
		foreach($settings['sections'] as $section){
			if($section['code'] == $currentSection){
				$data['section'] = $section;
			}
		}
		$data['fields'] = $settings['fields'][$currentSection];
		
		$this->output_data($data);
	}
	
	public function saveaction(){
		$this->load->model('Settings_model');
		$sectionCode = $this->input->post('section', true);
		$this->Settings_model->save($sectionCode);
		
		$returnUrl = $this->input->post('returnUrl', true);
		set_success_message('Settings saved');
		redirect($returnUrl);
		return;
	}
	
	public function viewimagefile(){
		$this->load->library('encrypt');
		$file = $this->input->get('file');

		$url_data = $this->uri->uri_to_assoc(1);
		$file = $url_data['file'];
		$file = urldecode($file);
		$file = base64_decode($file);
		$file = DATA_DIRECTORY.$file;
		$file = str_replace('..', '', $file);

		$path = $file;
		
		if (!is_file($path)) {
			return false;
		}
		
		$extension = strtolower( pathinfo( basename( $path ), PATHINFO_EXTENSION ) );
	    $mime_types = array(
	        // images
	        'png' => 'image/png',
	        'jpe' => 'image/jpeg',
	        'jpeg' => 'image/jpeg',
	        'jpg' => 'image/jpeg',
	        'gif' => 'image/gif',
	    );
	    
	    // Set a default mime if we can't find it
	    if( !isset( $mime_types[$extension] ) )
	    {
			return false;
	    }
	    else
	    {
	        $mime = ( is_array( $mime_types[$extension] ) ) ? $mime_types[$extension][0] : $mime_types[$extension];
	    }
	    
	    // Generate the server headers
	    if( strstr( $_SERVER['HTTP_USER_AGENT'], "MSIE" ) )
	    {
	        header( 'Content-Type: "'.$mime.'"' );
	        header( 'Expires: 0' );
	        header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
	        header( "Content-Transfer-Encoding: binary" );
	        header( 'Pragma: public' );
	        header( "Content-Length: ".filesize( $path ) );
	    }
	    else
	    {
	        header( "Pragma: public" );
	        header( "Expires: 0" );
	        header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
	        header( "Cache-Control: private", false );
	        header( "Content-Type: ".$mime, true, 200 );
	        header( 'Content-Length: '.filesize( $path ) );
	        header( "Content-Transfer-Encoding: binary" );
	    }
	    readfile( $path );
		exit();
	}
	
	public function downloadfile(){
		$this->load->library('encrypt');
		$file = $this->input->get('file');

		$url_data = $this->uri->uri_to_assoc(1);
		$file = $url_data['file'];
		$file = urldecode($file);
		$file = base64_decode($file);
		$file = DATA_DIRECTORY.$file;
		$file = str_replace('..', '', $file);

		$path = $file;
		$pathinfo = pathinfo($path);
		$fileName = $pathinfo['basename'];
		
		if (!is_file($path)) {
			return false;
		}
	    
	    // Generate the server headers
        header( "Pragma: public" );
        header( "Expires: 0" );
        header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
        header( "Cache-Control: private", false );
		header( "Content-Type: application/octet-stream" );
		header( "Content-Transfer-Encoding: Binary" ); 
        header( "Content-Length: ".filesize( $path ) );
		header( "Content-disposition: attachment; filename=\"$fileName\"" ); 
	    readfile( $path );
		exit();
	}
	
}

/* End of file settings.php */
/* Location: ./application/controllers/settings.php */