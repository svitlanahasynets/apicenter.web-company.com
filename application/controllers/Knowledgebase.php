<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');



class Knowledgebase extends MY_Controller {
    
        public function __construct(){
		parent::__construct();
		$this->config->load('whmcs');
                $this->load->helper('whmcs/knowledgebase');     
	}

	public function cats( $id = false ){
            
		$variables = array();
                $variables['page_title'] = translate('Knowledgebase Categories');
		$variables['go_back_url'] = site_url('/knowledgebase/cats');
		$variables['go_back_title'] = translate('Back to all Categories');
		// $variables['active_menu_item'] = 'Settings';

		$data = array();
		$data['variables'] = $variables;
		$data['views'] = !empty($id)  ? array('whmcs/kbcatarticles') : array('whmcs/knowledgebase');
                
                $url = $lang = $this->config->item('apiurl');
                $identifier = $lang = $this->config->item('identifier');
                $secret = $lang = $this->config->item('secret');
                $whmcsKbObj = new WhmcsKnowledgebase($url,$identifier,$secret);
                if( !empty( $id ) ){
                    //get cat detail
                    $catdetails = $whmcsKbObj->get_cats($id);
                    if( $catdetails['status'] == 200 ){
                        $data['catdetail'] = isset($catdetails['data']['categories']['category'][0]) ? $catdetails['data']['categories']['category'][0] : '';
                    }
                    
                    //get the knowledge base categorie's articles
                    $res = $whmcsKbObj->get_cat_articles($id);
                    $data['catarticles'] = [];
                    if( $res['status'] == 200 ){
                        $data['catarticles'] = $res['data']['articles']['article'];
                    }
                }else{
                    
                    //get the knowledge base categories
                    $res = $whmcsKbObj->get_cats();
                    $data['cats'] = [];
                    if( $res['status'] == 200 ){
                        $data['cats'] = $res['data']['categories']['category'];
                    }
                }
                
                
		$this->output_data($data);
	}
        
        public function article( $id ){
            
            $variables = array();
                $variables['page_title'] = translate('Knowledgebase Articles');
		$variables['go_back_url'] = site_url('/knowledgebase/cats');
		$variables['go_back_title'] = translate('Back to all Categories');
		// $variables['active_menu_item'] = 'Settings';

		$data = array();
		$data['variables'] = $variables;
                $data['views'] = array('whmcs/kbarticle');
                
                
                $url = $lang = $this->config->item('apiurl');
                $identifier = $lang = $this->config->item('identifier');
                $secret = $lang = $this->config->item('secret');
                $whmcsKbObj = new WhmcsKnowledgebase($url,$identifier,$secret);
                $data['article'] = '';
                if( !empty( $id ) ){
                    //get the knowledge base categorie's articles
                    $res = $whmcsKbObj->get_article($id);
                    if( $res['status'] == 200 ){
                        $data['article'] = $res['data']['articles']['article'];
                    }
                }
                
                
		$this->output_data($data);
                
            
        }
        
          public function search( ){
              
                $text = $this->input->post('keyword');
                $variables = array();
                $variables['page_title'] = translate('Knowledgebase Search');
		$variables['go_back_url'] = site_url('/knowledgebase/cats');
		$variables['go_back_title'] = translate('Back to all Categories');
		// $variables['active_menu_item'] = 'Settings';

		$data = array();
		$data['variables'] = $variables;
                $data['views'] = array('whmcs/kbcatarticles');
                $url = $lang = $this->config->item('apiurl');
                $identifier = $lang = $this->config->item('identifier');
                $secret = $lang = $this->config->item('secret');
                $whmcsKbObj = new WhmcsKnowledgebase($url,$identifier,$secret);
                $data['catarticles'] = [];
                if( !empty( $text ) ){
                    //get the knowledge base categorie's articles
                    $res = $whmcsKbObj->get_articles_by_keyword($text);
                    if( $res['status'] == 200 ){
                        $data['catarticles'] = $res['data']['articles']['article'];
                    }
                }
                
                
		$this->output_data($data);
                
            
        }

}

/* End of file Knowledgebase.php */
/* Location: ./application/controllers/Knowledgebase.php */
