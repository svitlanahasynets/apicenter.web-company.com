<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Manualsync extends MY_Controller {

	public function index(){
		$variables = array();
        $variables['page_title'] = translate('Manualsync')
        ;
		$variables['go_back_url'] = site_url('/manual-sync');
		$variables['go_back_title'] = translate('Back to all projects');
		// $variables['active_menu_item'] = 'Settings';

		$data = array();
		$data['variables'] = $variables;
		$data['helpers'] = array('form');
		$data['models'] = array('Permissions_model');
		$data['libraries'] = array('Pmprojects');
		$data['views'] = array('integrations/manual-sync/index');
		$data['js'] = array(
			'form/jquery.multiple.select.js',
			'form/store.min.js',
			'form/jquery.resizableColumns.min.js',
			'form/jquery.stickytableheaders.min.js',
			'form.js'
		);
		$data['css'] = array('form/multiple-select.css', 'form/jquery.resizableColumns.css');

		$projects = $this->db->get('projects')->result_array();
		$data['projects'] = $projects;

		$this->output_data($data);
	}


    // View project page
	public function view(){
		$variables = array();
		$variables['page_title'] = translate('Manualsync');
		$variables['go_back_url'] = site_url('/manual-sync');
		$variables['go_back_title'] = translate('Back to all projects');
		$variables['active_menu_item'] = 'projects';
    	$this->load->model('Projects_model');

		$url_data 		= $this->uri->uri_to_assoc(1);
		$project_id 	= $url_data['id'];
		$log_details 						= array();

		$log_details['article_success'] 	= $this->Projects_model->getValue('total_article_import_success', $project_id)?$this->Projects_model->getValue('total_article_import_success', $project_id):0;
		$log_details['article_error'] 		= $this->Projects_model->getValue('total_article_import_error', $project_id)?$this->Projects_model->getValue('total_article_import_error', $project_id):0;

		$log_details['customer_success'] 	= $this->Projects_model->getValue('total_customer_import_success', $project_id)?$this->Projects_model->getValue('total_customer_import_success', $project_id):0;
		$log_details['customer_error'] 		= $this->Projects_model->getValue('total_customer_import_error', $project_id)?$this->Projects_model->getValue('total_customer_import_error', $project_id):0;

		$log_details['orders_success'] 		= $this->Projects_model->getValue('total_orders_import_success', $project_id)?$this->Projects_model->getValue('total_orders_import_success', $project_id):0;
		$log_details['orders_error'] 		= $this->Projects_model->getValue('total_orders_import_error', $project_id)?$this->Projects_model->getValue('total_orders_import_error', $project_id):0;

		$log_details['exact_success'] 		= $this->Projects_model->getValue('total_exact_import_success', $project_id)?$this->Projects_model->getValue('total_exact_import_success', $project_id):0;
		$log_details['exact_error'] 		= $this->Projects_model->getValue('total_exact_import_error', $project_id)?$this->Projects_model->getValue('total_exact_import_error', $project_id):0;

		$log_details['invoice_success'] 		= $this->Projects_model->getValue('total_invoice_import_success', $project_id)?$this->Projects_model->getValue('total_invoice_import_success', $project_id):0;
		$log_details['invoice_error'] 		= $this->Projects_model->getValue('total_invoice_import_error', $project_id)?$this->Projects_model->getValue('total_invoice_import_error', $project_id):0;

		$log_details['sales_entry_success'] 		= $this->Projects_model->getValue('total_sales_entry_import_success', $project_id)?$this->Projects_model->getValue('total_sales_entry_import_success', $project_id):0;
		$log_details['sales_entry_error'] 		= $this->Projects_model->getValue('total_sales_entry_import_error', $project_id)?$this->Projects_model->getValue('total_sales_entry_import_error', $project_id):0;
		$log_details['afas_setup_error'] 		= $this->Projects_model->getValue('total_afas_setup_error', $project_id)?$this->Projects_model->getValue('total_afas_setup_error', $project_id):0;

		$data = array();
		$data['variables'] = $variables;
		$data['log_details'] = $log_details;
		$data['models'] = array('Permissions_model');
		$data['helpers'] = array('form');
		$data['libraries'] = array('Pmprojects');
		$data['views'] = array('integrations/manual-sync/view');
		$data['sidebar'] = array('sidebar/projects/index');
		$data['js'] = array(
			'form/jquery.multiple.select.js',
			'form/store.min.js',
			'form/jquery.resizableColumns.min.js',
			'projects/chartist.min.js',
			'form.js',
			'projects/edit-project.js'
		);
		$data['css'] = array('form/multiple-select.css', 'form/jquery.resizableColumns.css', 'projects/chartist.min.css', 'projects/projects.css');

		$this->load->model('Permissions_model');
		$permission = $this->Permissions_model->check_permission_user('project', $project_id);
		if($permission != 've' && $permission != 'v'){
			set_error_message('Error while opening project');
			redirect('/projects/');
			return;
		}

		$projects = $this->db->get_where('projects', array('id' => $project_id))->result_array();
		if(!empty($projects)){
			$data['project'] = $projects[0];
			$data['project_settings'] = $this->getProjectFormSettings();
			$this->output_data($data);
		} else {
			set_error_message('Error while opening project');
			redirect('/projects');
		}
		return;
    }

	public function createexactwebhook(){
		if (isset($_GET['project_id']) && $_GET['project_id']) {
			$this->load->model('Projects_model');
			$this->load->helper('ExactOnline/vendor/autoload');
	        $this->load->model('Exactonline_model');
	        $projectId = intval($_GET['project_id']);
			if($projectId){
				 //--------------- make exact connection ----------------------------------//
				 $this->Exactonline_model->setData(
					array(
						'projectId'     => $projectId,
						'redirectUrl'   => $this->Projects_model->getValue('exactonline_redirect_url', $projectId),
						'clientId'      => $this->Projects_model->getValue('exactonline_client_id', $projectId),
						'clientSecret'  => $this->Projects_model->getValue('exactonline_secret_key', $projectId),
					)
				);
				$connection = $this->Exactonline_model->makeConnection($projectId);
				$sendWebhook  = $this->Exactonline_model->sendWebhook($connection, $projectId);
				if($sendWebhook){
					$this->Projects_model->saveValue('exact_webhook_item', 1, $projectId);
					set_success_message('Exact webhook for item update created');
				}
				else
					set_error_message('Error while creating exact webhook');
				exit();
				redirect('/projects/view/id/'.$projectId);
				return;
			}
		}
		redirect('/projects/');
		return;        
	}

	public function search_projects($display = true, $isExport = false){
		$this->load->helper('tools');
		$this->load->helper('form');
		$this->load->helper('constants');
		$this->load->helper('translate');
		$this->load->model('Permissions_model');
		$this->load->library('Pmprojects');

		$this->load->model('Projects_model');
		$availableProjects = $this->Projects_model->getAvailableUserProjects();

		$collection = $this->db->select('projects.*, project_settings.value');
		$collection->from('projects');

		// Check variables from post and apply filter
		if(is_numeric($this->input->get_post('id'))){
			$collection->where('id', $this->input->get_post('id'));
		} else {
			$inputProjectIds = explode(',', $this->input->get_post('id'));
			$projectIds = array();
			foreach($inputProjectIds as $projectId){
				if(is_numeric($projectId)){
					$projectIds[] = $projectId;
				}
			}
			if(!empty($projectIds)){
				$collection->where_in('id', $projectIds);
			}
		}

		// Check available projects by permissions
		if($availableProjects['type'] == 'some'){
			if (count($availableProjects['projects']) > 0) {
				$collection->where_in('id', $availableProjects['projects']);
			} else {
				$collection->where(0);
			}
		}

		if($this->input->get_post('erp_system') != ''){
			$collection->like('erp_system', $this->input->get_post('erp_system'));
		} 

		if($this->input->get_post('web_shop') != ''){
			$this->db->join('project_settings', 'project_settings.project_id = projects.id')->where('value',$this->input->get_post('web_shop'));
		} else if($this->input->get_post('market') != ''){
			$this->db->join('project_settings', 'project_settings.project_id = projects.id')->where('value',$this->input->get_post('market'));
		} else if($this->input->get_post('active') != ''){
			$this->db->join('project_settings', 'project_settings.project_id = projects.id')->where('value',$this->input->get_post('active'));
		} else{
			$this->db->join('project_settings', 'project_settings.project_id = projects.id')->where_in('code',['cms','market_place']);
		}

		if($this->input->get_post('title') != ''){
			$collection->like('title', $this->input->get_post('title'));
		}

		if($isExport == false){
			$countCollection = clone $collection;
			$numberOfPages = ceil($countCollection->count_all_results() / DISPLAY_TABLE_ROWS);

			$currentItem = ($this->input->get_post('current_page') * DISPLAY_TABLE_ROWS) - DISPLAY_TABLE_ROWS;
			$collection->limit(DISPLAY_TABLE_ROWS, $currentItem);
		}

		$projects = $collection->order_by('id', 'desc')->get()->result_array();
		if(!empty($projects)){
			if($display){
				$data = array();
				$data['projects'] = $projects;
				$html = $this->load->view(TEMPLATE.'/projects/results', $data, true);
				$return = array(
					'html' => $html,
					'pages' => $numberOfPages
				);
				echo json_encode($return);
				return;
			} else {
				return $projects;
			}
		} else {
			if($display){
				$return = array(
					'html' => '',
					'pages' => 1
				);
				echo json_encode($return);
				return;
			}
			return array();
		}
		return;
	}

	public function getResponse($display = true, $isExport = false){
		$this->load->helper('tools');
		$this->load->helper('form');
		$this->load->helper('constants');
		$this->load->helper('translate');
		$this->load->model('Permissions_model');
		$this->load->library('Pmprojects');

		$this->load->model('Projects_model');
		$availableProjects = $this->Projects_model->getAvailableUserProjects();

		$collection = $this->db->select('projects.*');
		$collection->from('projects');

		$this->db->join('project_settings m1', 'projects.id = m1.project_id');
		$this->db->join('project_settings m2', 'projects.id = m2.project_id');
		//$this->db->join('project_settings m3', 'projects.id = m3.project_id');

		if($this->input->get_post('web_shop') != '' || $this->input->get_post('market') != '' || $this->input->get_post('active') != ''){
			if($this->input->get_post('web_shop') != ''){
				$this->db->group_start();
				$this->db->where('m1.code','cms')->where('m1.value',$this->input->get_post('web_shop'));
				$this->db->group_end();
			}
			if($this->input->get_post('market') != ''){
				$this->db->group_start();
				$this->db->where('m2.code','market_place')->where('m2.value',$this->input->get_post('market'));
				$this->db->group_end();
			}
			// if($this->input->get_post('active') != ''){
			// 	$this->db->group_start();
			// 	$this->db->where('m1.code','enabled')->where('m1.value',$this->input->get_post('active'));
			// 	$this->db->group_end();
			// }		

		} 
		// Check variables from post and apply filter
		if(is_numeric($this->input->get_post('id'))){
			$collection->where('projects.id', $this->input->get_post('id'));
		} else {
			$inputProjectIds = explode(',', $this->input->get_post('id'));
			$projectIds = array();
			foreach($inputProjectIds as $projectId){
				if(is_numeric($projectId)){
					$projectIds[] = $projectId;
				}
			}
			if(!empty($projectIds)){
				$collection->where_in('projects.id', $projectIds);
			}
		}

		// Check available projects by permissions
		if($availableProjects['type'] == 'some'){
			if (count($availableProjects['projects']) > 0) {
				$collection->where_in('projects.id', $availableProjects['projects']);
			} else {
				$collection->where(0);
			}
		}

		if($this->input->get_post('erp_system') != ''){
			$collection->like('projects.erp_system', $this->input->get_post('erp_system'));
		}
		
		if($this->input->get_post('title') != ''){
			$collection->like('projects.title', $this->input->get_post('title'));
		}
		$collection->group_by('projects.id');
		if($isExport == false){
			// $countCollection = clone $collection;
			$query = $this->db->query(" SELECT 'COUNT(*) as count' FROM projects;");
			$query = $query->num_rows();
			$numberOfPages = ceil($query / DISPLAY_TABLE_ROWS);
			$currentItem = ($this->input->get_post('current_page') * DISPLAY_TABLE_ROWS) - DISPLAY_TABLE_ROWS;
			$collection->limit(DISPLAY_TABLE_ROWS, $currentItem);
		}

		$projects = $collection->order_by('projects.id', 'desc')->get()->result_array();

		if(!empty($projects)){
			if($display){
				$data = array();
				$data['projects'] = $projects;
				$data['controller'] = $this; 
				$html = $this->load->view(TEMPLATE.'/integrations/manual-sync/results', $data, true);
				$return = array(
					'html' => $html,
					'pages' => $numberOfPages
				);
				echo json_encode($return);
				return;
			} else {
				return $projects;
			}
		} else {
			if($display){
				$return = array(
					'html' => '',
					'pages' => 1
				);
				echo json_encode($return);
				return;
			}
			return array();
		}
		return;
	}

	public function webshopaname($project_id='', $type = 'webs_shop'){
		if($project_id!=''){
			$this->load->model('Projects_model');
			return $type=='webs_shop' ? $this->Projects_model->getValue('cms', $project_id):($this->Projects_model->getValue('market_place', $project_id)?$this->Projects_model->getValue('market_place', $project_id):'');
		} else
			return '';
	}

	public function connectionStatus($project_id=''){
		if($project_id!=''){
			$this->load->model('Projects_model');
			return $this->Projects_model->getValue('enabled', $project_id)?'Ja':'Nee';
		} else
			return '';
	}

	public function createFromSettings(){
			$this->config->load('project_settings', true);
			$project_settings = $this->config->item('project_settings');
			foreach ($project_settings['project_settings'] as $key => $value) {
				$values = '';
				if($value['type']=='select'){
					$values  = json_encode($value['values']);
				} else{
					$values  			= isset($value['values'])?$value['values']:NULL;
				}
				$depends_on  	= '';
				if(isset($value['depends_on'])){
					$depends_on  = json_encode($value['depends_on']);
				}
				$fields  	= '';
				if(isset($value['fields'])){
					$fields  = json_encode($value['fields']);
				}
				$default = isset($value['default'])?$value['default']:'';
				$data = array(
						'code' 	=> $value['code'],
						'title' => $value['title'],
						'type' 	=> $value['type'],
						'default' => $default,
						'values' => $values,
						'fields' => $fields,
						'depends_on' => $depends_on
				);
				$this->db->insert('project_from_settings', $data); // let it close this untill you know what do are going to change.
			}
	}

	public function getProjectFormSettings($user_id='', $project_id=''){
			$this->db->like('code', 'stock_synchronization');$this->db->or_like('code', 'enabled');
			$project_settings1 = $this->db->order_by("order_no", "asc")->get('project_from_settings');
            $project_settings2 = array();
			foreach ($project_settings1->result_array() as $row){
					$row['values'] 			= json_decode($row['values'], true);
					$row['depends_on'] 	= json_decode($row['depends_on'], true);
                    $row['fields'] 			= json_decode($row['fields'], true);
                    $row['headers'] 		= '';
					if($user_id!='' && $project_id!='')
						$row['permission']  = $this->db->get_where('permissions_user_forms',['user_id'=>$user_id, 'project_id'=>$project_id, 'field_code'=>$row['code']])->row_array()?$this->db->get_where('permissions_user_forms',['user_id'=>$user_id, 'project_id'=>$project_id, 'field_code'=>$row['code']])->row_array()['permission']:'';
					$project_settings2[] = $row;
			}
			return $project_settings2;
	}

	public function creatConditionCode(){
		if($this->input->server('REQUEST_METHOD') == 'POST'){
			$this->load->model('Magentobol_model');
			$params = ['magento_pass'=>$_POST['magento_pass'], 'magento_user'=>$_POST['magento_user'],'store_url'=>$_POST['store_url']];
			$code = $this->Magentobol_model->magentoAjaxConfig($params,'conditionCode');
			if($code['status']==1){
				$return = ['status'=>1,'result'=>'bol_product_condition'];
				print_r(json_encode($return));
			} else{
				$return = ['status'=>1,'result'=> $code['message']];
				print_r(json_encode($return));
			}
		} 
		return true;
	}
	public function creatDeliveryCode(){
		if($this->input->server('REQUEST_METHOD') == 'POST'){
			$this->load->model('Magentobol_model');
			$params = ['magento_pass'=>$_POST['magento_pass'], 'magento_user'=>$_POST['magento_user'],'store_url'=>$_POST['store_url']];
			$code = $this->Magentobol_model->magentoAjaxConfig($params,'deliveryCode');
			if($code['status']==1){
				$return = ['status'=>1,'result'=>'bol_delivery_code'];
				print_r(json_encode($return));
			} else{
				$return = ['status'=>1,'result'=> $code['message']];
				print_r(json_encode($return));
			}
		} 
		return true;
	}
	
	public function manageFormFieldsOrders(){
		$variables = array();
		$variables['page_title'] = translate('Manage Fields Order');
		$variables['go_back_url'] = site_url('/projects');
		$variables['go_back_title'] = translate('Back to all projects');
		$variables['active_menu_item'] = 'projects';

		$data = array();
		$data['variables'] 	= $variables;
		$data['helpers'] 	= array('form');
		$data['models'] 	= array('Permissions_model');
		$data['libraries'] 	= array('Pmprojects');
		$data['views'] 		= array('projects/arrange_orders');
		$data['js'] = array(
			'form/jquery.multiple.select.js',
			'form/store.min.js',
			'form/jquery.resizableColumns.min.js',
			'form/jquery.stickytableheaders.min.js',
			'form.js'
		);
		$data['css'] = array('form/multiple-select.css', 'form/jquery.resizableColumns.css');
		$projects = $this->db->order_by("order_no", "asc")->get('project_from_settings')->result_array();
		$data['project_from_settings'] = $projects;

		$this->output_data($data);
	}

	public function saveArrangedOrders(){
		$new_orders = $_POST['fields_orders'];
		$header_before_id = $_POST['header_before_id'];
		$form_label_id = $_POST['form_label_id'];
		if($new_orders!=''){
			$new_orders_array = explode(',', $new_orders);
			foreach ($new_orders_array as $key => $value) {
				$this->db->where('id', $value);
				$order = $key +1;
				$help_option_key = 'help_option'.$value;
				$help_message_key = 'help_message'.$value;
				$help_url_key = 'help_url'.$value;
				$update_array = array();
				$update_array['order_no'] = $order;
				$update_array['headers'] = $header_before_id[$key];
				$update_array['title'] 	= $form_label_id[$key];
				$help_option =  $_POST[$help_option_key];
				$update_array['help_option'] = $help_option;
				if($help_option==0){
					$update_array['help_message'] = '';
					$update_array['help_url'] = '';
				} else if($help_option==1){
					$update_array['help_message'] = $_POST[$help_message_key];
					$update_array['help_url'] = '';
				} else if($help_option==2){
					$update_array['help_message'] = '';
					$update_array['help_url'] = $_POST[$help_url_key];
				} else {
					$update_array['help_message'] = $_POST[$help_message_key];
					$update_array['help_url'] = $_POST[$help_url_key];
				}
				$this->db->update('project_from_settings', $update_array);
			}
		}
		set_success_message('Form fields ordered arranged successfully');
		redirect('/projects/');
		return;
	}

}

/* End of file projects.php */
/* Location: ./application/controllers/projects.php */
