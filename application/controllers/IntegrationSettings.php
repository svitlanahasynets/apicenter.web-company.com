<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class IntegrationSettings extends MY_Controller {

	public function index(){
		$variables = array();
        $variables['page_title'] = translate('Settings')
        ;
		$variables['go_back_url'] = site_url('/settings');
		$variables['go_back_title'] = translate('Back to all projects');
		// $variables['active_menu_item'] = 'Settings';

		$data = array();
		$data['variables'] = $variables;
		$data['helpers'] = array('form');
		$data['models'] = array('Permissions_model');
		$data['libraries'] = array('Pmprojects');
		$data['views'] = array('integrations/settings/index');
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

	// Edit project form
	public function edit(){
		$variables 					= array();
		$variables['page_title'] 	= translate('Settings');
		$variables['go_back_url'] 	= site_url('/settings');
		$variables['go_back_title'] = translate('Back to all projects');
		$variables['active_menu_item'] = 'projects';
		$data 				= array();
		$data['variables'] 	= $variables;
		$data['models'] 	= array('Permissions_model', 'Projects_model');
		$data['helpers'] 	= array('form');
		$data['libraries'] 	= array('Pmprojects');
		$data['views'] 		= array('integrations/settings/edit');
		$data['sidebar'] 	= array('sidebar/projects/index');
		$data['js'] 		= array('datepicker/datepicker.js', 'media/imagePreview.js', 'form.js', 'projects/edit-project.js');
		$data['css']		= array('datepicker/default.css', 'projects/projects.css');
		$url_data = $this->uri->uri_to_assoc(1);
		$project_id = $url_data['id'];
		$this->load->model('Permissions_model');
		$this->load->model('Projectfromsettings_model');
		$permission = $this->Permissions_model->check_permission_user('project', $project_id);
		if($permission != 've'){
			set_error_message('Error while opening project');
			redirect('/settings/');
			return;
		}
		$username = $this->session->userdata('username');
		$user 		= $this->db->get_where('permissions_users', array('user_name' => $username))->row_array();
		$user_id 	= $user['user_id'];
		$projects = $this->db->get_where('projects', array('id' => $project_id))->result_array();
		if(!empty($projects)){
			$data['project'] = $projects[0];
			$data['project_settings'] = $this->getProjectFormSettings($user_id, $project_id);
			$data['permission'] = $this->Projectfromsettings_model->getStaticFieldAssignedPermission($user_id, $project_id);
			$this->output_data($data);
		} else {
			set_error_message('Error while opening project');
			redirect('/projects/');
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

	public function editaction(){
		$this->load->model('Permissions_model');
		$this->load->model('Projects_model');
		$this->load->model('Projectfromsettings_model');
		$permission = $this->Permissions_model->check_permission_user('project', $_POST['project_id']);
		if($permission != 've'){
			set_error_message('Error while saving project');
			redirect('/settings/');
			return;
		}
		if(isset($_POST['project_id']) && $_POST['project_id'] != ''){
			$project_id = $_POST['project_id'];
		} else {
			set_error_message('Error while saving project');
			redirect('/settings');
			return;
		}
		if(isset($_POST['project_title']) && $_POST['project_title'] != ''){
			$project_title = $_POST['project_title'];
		} else {
			$project_title = translate('Project title');
		}
		if(isset($_POST['project_desc']) && $_POST['project_desc'] != ''){
			$project_desc = $_POST['project_desc'];
		} else {
			$project_desc = translate('Project description');
		}
		$username = $this->session->userdata('username');
		$user 		= $this->db->get_where('permissions_users', array('user_name' => $username))->row_array();
		$user_id 	= $user['user_id'];
		$check_permission = $this->Projectfromsettings_model->getStaticFieldAssignedPermission($user_id, $project_id);
				// Save to database
		$data 								= array();
		$market_place = '';
		if($check_permission['project_title']=='ve')
			$data['title'] 				= $project_title;
		if($check_permission['project_desc']=='ve')
			$data['description'] 	= $project_desc;
		if($check_permission['erp_system']=='ve')
			$data['erp_system'] 	= isset($_POST['erp_system'])?$_POST['erp_system']:'';
		if($check_permission['store_url']=='ve')
			$data['store_url'] 		= isset($_POST['store_url'])?$_POST['store_url']:'';
		$data['api_key'] 			= isset($_POST['api_key'])?$_POST['api_key']:'';
		$data['plugin_key'] 	= isset($_POST['plugin_key'])?$_POST['plugin_key']:'';
		$data['store_key'] 		= isset($_POST['store_key'])?$_POST['store_key']:'';
		$connect_to_webshop   = '';
		if(isset($_POST['creation_date']) && $_POST['creation_date'] != ''){
			$creation_date = format_date($_POST['creation_date'], 'd-m-Y', 'Y_m_d');
			$data['creation_date'] = $creation_date;
		}
		if(isset($_POST['contact_person']) && $_POST['contact_person'] > 0){
			if($check_permission['contact_person']=='ve')
				$data['contact_person'] = $_POST['contact_person'];
		}
		$this->db->where('id', $project_id);
		$this->db->update('projects', $data);
		$data_settings = $_POST['settings'];
		$getProjectFormSettings = $this->getProjectFormSettings($user_id, $project_id);
		if(!isset($data_settings['exactonline_administration_id']) || $data_settings['exactonline_administration_id']==''){
			$this->db->where('project_id', $project_id)->where('code','exactonline_administration_id');
			$this->db->delete('project_settings'); 
		} 
		foreach( $getProjectFormSettings as $field){
			$fieldCode = $field['code'];
			if($field['permission']!='ve')
				continue;
			if($field['code']=='cms')
				$connect_to_webshop = $data_settings[$fieldCode];
			if($field['code']=='market_place')
					$market_place = $data_settings[$fieldCode];
			if(isset($data_settings[$fieldCode]) && $data_settings[$fieldCode] != ''){
				if($field['type'] == 'table'){
					$fields = $field['fields'];
					$finalArray = array();
					foreach($data_settings[$fieldCode] as $columnCode => $rows){
						foreach($rows as $index => $rowValue){
							if($rowValue != ''){
									$finalArray[$columnCode][$index] = $rowValue;
							}
						}
					}
					$this->Projects_model->saveValue($fieldCode, json_encode($finalArray), $project_id);
				} else {
					$this->Projects_model->saveValue($fieldCode, $data_settings[$fieldCode], $project_id);
				}
			}
		}
		if($connect_to_webshop=='WooCommerce'){
			$this->load->model('Woocommerce_model');
    		$this->Woocommerce_model->putPostWooCommerceWebhook($project_id);
		} else if($connect_to_webshop=='mailchimp'){
			$this->load->model('Mailchimp_model');
    		$this->Mailchimp_model->putMailchimpWebhook($project_id);
		} elseif($connect_to_webshop=='magento2' && $market_place=='bol'){
			$this->load->model('Magentobol_model');
			$this->Magentobol_model->magentoBolConfiguration($project_id);
		}else if($connect_to_webshop=='shopify'){
			$this->load->model('Shopify_model');
    		$this->Shopify_model->putPostShopifyWebhook($project_id);
		}
		// Set success message and redirect to projects overview
		set_success_message('Project '.$project_title.' saved');
		redirect('/settings');
		return;
	}

	public function deleteaction(){
		// Delete project
		$url_data = $this->uri->uri_to_assoc(1);
		$project_id = $url_data['id'];

		$this->load->model('Permissions_model');
		$permission = $this->Permissions_model->check_permission_user('project', $project_id);
		if($permission != 've'){
			set_error_message('Error while deleting project');
			redirect('/projects/');
			return;
		}

		$this->db->where('id', $project_id);
		$this->db->delete('projects');

		// Set success message and redirect to projects overview
		set_success_message('Project deleted');
		redirect('/projects');

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
				$html = $this->load->view(TEMPLATE.'/integrations/settings/results', $data, true);
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
            $this->db->like('code', 'amount'); $this->db->or_like('code', 'offset');
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
