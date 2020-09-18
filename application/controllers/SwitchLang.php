<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SwitchLang extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        return;
    }
    public function switch_language(){
        $lang = $this->input->post('lang', FALSE);
        $this->session->set_userdata('default_lang', $lang);
    }
    /* End switch language actions */
}

/* End of file SwitchLang.php */
/* Location: ./application/controllers/SwitchLang.php */
