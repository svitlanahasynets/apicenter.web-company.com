<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Convertuserspasswords extends CI_Controller {

    public function __construct(){
        parent::__construct();
        /*$this->load->helpers('tools');
        $this->load->helpers('constants');*/
        $this->load->model('Convertuserspasswords_model');
    }

    public function index()
    {
        $this->Convertuserspasswords_model->convert();
    }

    public function resetuserspasswords()
    {
        $this->Convertuserspasswords_model->reset();
    }
}