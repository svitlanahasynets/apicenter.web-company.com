<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Myaccount extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        return;
    }

    /* Display forms */

	public function index()
	{
		$variables = array();
		$variables['page_title'] = translate('APIcenter');

		$data = array();
		$data['variables'] = $variables;
		$data['helpers'] = array('form');
		$data['views'] = array('user/myaccount');
		$data['hide_sidebar'] = true;

		$data['css'] = array('form/multiple-select.css', 
            'form/jquery.resizableColumns.css',
            'user/myaccount.css'
        );

        $data['js'] = array(
            'form/jquery.multiple.select.js',
            'form/store.min.js',
            'form/jquery.resizableColumns.min.js',
            'form/jquery.stickytableheaders.min.js',
            'user/myaccount.js'
        );

		$user = $this->db->get_where('permissions_users', array('user_name' => $this->session->userdata('username')))->row_array();
		if(!empty($user)){
			$data['user'] = $user;
			$this->output_data($data);
		}
		return;
	}

}

/* End of file Myaccount.php */
/* Location: ./application/controllers/Myaccount.php */
