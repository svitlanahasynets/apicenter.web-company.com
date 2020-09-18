<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Pages extends MY_Controller
{

    public function index($page = '/')
    {
        $data      = [];
        $variables = [];

        if ($page == '/') { 
            $this->showDefautl();
            return;
        }

        if (!file_exists(APPPATH . 'views/' .TEMPLATE . '/pages/' . $page . '.php')) {
            show_404();
        }

        switch ($page) {
            case '':
                
            break;
            default:
                
                $variables['page_title'] = translate(ucfirst($page));
                $data['views']           = array('pages/'.$page);

            break;
        }

        $this->output_data($data);
    }

    protected function showDefautl()
    {
        $variables['page_title']  = translate('Dashboards');
        $variables['go_back_url'] = site_url('/dashboards');
        $data = array();
        $data['views'] = array('pages/dashboard');

        $this->output_data($data);
    }

    public function getDashboardParameters()
    {
        $data      = [];
        $variables = [];

        $username_or_userid = $this->session->userdata('username');
        $user_id = 0;
        $user = array();

        if(is_numeric($username_or_userid)){
            $user_id = $username_or_userid;
            $user = $this->db->get_where('permissions_users', array(
                'user_id' => $username_or_userid
            ))->result_array();
        } else {
            $user = $this->db->get_where('permissions_users', array(
                'user_name' => $username_or_userid
            ))->result_array();
            $user_id = $user[0]['user_id'];
        }

        $current_project_id = $this->input->post('current_project_id', 0);

        $this->load->model('Projects_model');
        $availableProjects = $this->Projects_model->getAvailableUserProjects();

        $api_send_collection = $this->db->select('project_settings.*');
        $api_send_collection->from('project_settings');
        $api_send_collection->where('project_settings.project_id', $current_project_id);
        $total_api_calls_send = array();
        $api_send_collection->where('project_settings.code','api_calls_send');
        $total_api_calls_send = $api_send_collection->get()->result_array();

        $total_api_calls_send_count = 0;

        foreach ($total_api_calls_send as $key => $api_call_send) {
            $total_api_calls_send_count += intval($api_call_send['value']);
        }

        $api_received_collection = $this->db->select('project_settings.*');
        $api_received_collection->from('project_settings');
        $api_received_collection->where('project_settings.project_id', $current_project_id);
        $total_api_calls_received = array();
        $api_received_collection->where('project_settings.code','api_calls_received');
        $total_api_calls_received = $api_received_collection->get()->result_array();

        $total_api_calls_received_count = 0;

        foreach ($total_api_calls_received as $key => $api_call_received) {
            $total_api_calls_received_count += intval($api_call_received['value']);
        }

        $total_api_calls_count = $total_api_calls_send_count + $total_api_calls_received_count;

        $products_synced_collection = $this->db->select('project_settings.*');
        $products_synced_collection->from('project_settings');
        $products_synced_collection->where('project_settings.project_id', $current_project_id);
        $total_products_synced = array();
        $products_synced_collection->where('project_settings.code','products_synced');
        $total_products_synced = $products_synced_collection->get()->result_array();

        $total_products_synced_count = 0;

        foreach ($total_products_synced as $key => $product_synced) {
            $total_products_synced_count += intval($product_synced['value']);
        }

        $orders_synced_collection = $this->db->select('project_settings.*');
        $orders_synced_collection->from('project_settings');
        $orders_synced_collection->where('project_settings.project_id', $current_project_id);
        $total_orders_synced = array();
        $orders_synced_collection->where('project_settings.code','orders_synced');
        $total_orders_synced = $orders_synced_collection->get()->result_array();

        $total_orders_synced_count = 0;

        foreach ($total_orders_synced as $key => $order_synced) {
            $total_orders_synced_count += intval($order_synced['value']);
        }

        $data['total_api_calls_send_count'] = $total_api_calls_send_count;
        $data['total_api_calls_received_count'] = $total_api_calls_received_count;
        $data['total_api_calls_count'] = $total_api_calls_count;
        $data['total_products_synced_count'] = $total_products_synced_count;
        $data['total_orders_synced_count'] = $total_orders_synced_count;

        $filter_condition = array(
            'recipient' => $user_id
        );

        $this->db->select("user_messages.*, permissions_users.user_name as message_sender");
        $this->db->from("user_messages");
        $this->db->join('permissions_users', 'permissions_users.user_id = user_messages.sender','left');                
        $this->db->where($filter_condition);
        $results = $this->db->get()->result_array();

        $messages = array();

        foreach ($results as $key => $result) {
            $binary_str = strval(decbin($result['visibility']));
            $fifth_index = intval(substr($binary_str, -5, 1));
            if ($fifth_index && strlen($binary_str) > 4) {
                $messages[] = $result;
            }
        }
        
        $data['messages'] = $messages;

        if(!empty($data)){

            echo json_encode($data);
        } 

        return;
    }

    public function sidevarMenuUpdate()
    {
        $username_or_userid = $this->session->userdata('username');
        $user_id = 0;
        $user = array();

        if(is_numeric($username_or_userid)){
            $user_id = $username_or_userid;
            $user = $this->db->get_where('permissions_users', array(
                'user_id' => $username_or_userid
            ))->result_array();
        } else {
            $user = $this->db->get_where('permissions_users', array(
                'user_name' => $username_or_userid
            ))->result_array();
            $user_id = $user[0]['user_id'];
        }

        $projectRules = array();

        $rules = $this->db->get_where('permissions_user_rules', array(
            'user_id' => $user_id,
            'type' => 'project'
        ))->result_array();

        foreach($rules as $rule){
            if($rule['view'] == 1){
                $projectRules[] = $rule['type_id'];
            }
        }

        $data['availableProjects'] = $projectRules;

        $possible_log_types = array();

        if (count($projectRules) > 0) {

            $current_project_id = $this->input->post('current_project_id') ? (int)$this->input->post('current_project_id') : 0;

            if (in_array($current_project_id, $projectRules)) {
                $collection = $this->db->select('project_logs.function');
                $collection->from('project_logs');
                $collection->where('project_logs.project_id', $current_project_id);
                $collection->group_by('project_logs.function');
                $possible_log_types = $collection->get()->result_array();

            }
        }

        $possible_log_types_functions = array();

        foreach ($possible_log_types as $key => $possible_log_type) {
            if ($possible_log_type['function'] == 'projectcontrol') {
                $possible_log_types_functions[0] = $possible_log_type['function'];
            } else if ($possible_log_type['function'] == 'importInvoices') {
                $possible_log_types_functions[1] = $possible_log_type['function'];
            } else if ($possible_log_type['function'] == 'afas_setup_error') {
                $possible_log_types_functions[2] = $possible_log_type['function'];
            } else if ($possible_log_type['function'] == 'importSalesEntry') {
                $possible_log_types_functions[3] = $possible_log_type['function'];
            } else if ($possible_log_type['function'] == 'importcustomers') {
                $possible_log_types_functions[4] = $possible_log_type['function'];
            } else if ($possible_log_type['function'] == 'importarticles') {
                $possible_log_types_functions[5] = $possible_log_type['function'];
            } else if ($possible_log_type['function'] == 'exportorders') {
                $possible_log_types_functions[6] = $possible_log_type['function'];
            } else if ($possible_log_type['function'] == 'tracktrace') {
                $possible_log_types_functions[7] = $possible_log_type['function'];
            } else if ($possible_log_type['function'] == 'exact_setup') {
                $possible_log_types_functions[8] = $possible_log_type['function'];
            } else if ($possible_log_type['function'] == 'custom_cronjob') {
                $possible_log_types_functions[9] = $possible_log_type['function'];
            } else if ($possible_log_type['function'] == 'optiply_connection') {
                $possible_log_types_functions[10] = $possible_log_type['function'];
            } else if ($possible_log_type['function'] == 'exact_buy_orders') {
                $possible_log_types_functions[11] = $possible_log_type['function'];
            } else if ($possible_log_type['function'] == 'exact_sell_orders') {
                $possible_log_types_functions[12] = $possible_log_type['function'];
            } else if ($possible_log_type['function'] == 'optiply_suppliers') {
                $possible_log_types_functions[13] = $possible_log_type['function'];
            } else if ($possible_log_type['function'] == 'reimport_orders') {
                $possible_log_types_functions[14] = $possible_log_type['function'];
            }
            $possible_log_types_functions[15] = 'admindebugging';          
        }

        $data['user_id'] = $user_id;
        $data['role'] = $user[0]['role'];

        if ($user[0]['role'] == 'partner') {
            $logo_picture_folder = DEFAULT3_THEME_URL.'/src/assets/images/sidebar/';
            $data['partner_logo'] = $logo_picture_folder.trim($user[0]['partner_logo']);
        }
        
        $data['possible_log_types_functions'] = $possible_log_types_functions;

        if($user_id){

            echo json_encode($data);
        } 

        return;
    }

    public function notificationUpdate()
    {
        $username_or_userid = $this->session->userdata('username');
        $user_id = 0;
        $user = array();

        if(is_numeric($username_or_userid)){
            $user_id = $username_or_userid;
            $user = $this->db->get_where('permissions_users', array(
                'user_id' => $username_or_userid
            ))->result_array();
        } else {
            $user = $this->db->get_where('permissions_users', array(
                'user_name' => $username_or_userid
            ))->result_array();
            $user_id = $user[0]['user_id'];
        }

        $filter_condition = array(
            'recipient' => $user_id
        );

        $this->db->select("user_messages.*, permissions_users.user_name as message_sender");
        $this->db->from("user_messages");
        $this->db->join('permissions_users', 'permissions_users.user_id = user_messages.sender','left');        
        $this->db->where($filter_condition);

        $messages = $this->db->get()->result_array();

        $data = array();
        $unread_message_count = 0;

        foreach ($messages as $key => $message) {

            $binary_str = strval(decbin($message['visibility']));
            $second_index = intval(substr($binary_str, -2, 1));

            if ($second_index && strlen($binary_str) > 1) {
                $message_info = array();
                $message_info['message_id'] = $message['message_id'];
                $message_info['subject'] = $message['subject'];
                $message_info['isRead'] = $message['isRead'];

                if (!$message['isRead']) {
                    $unread_message_count ++;
                }

                $message_info['from'] = $message['message_sender'];
                $data['messages'][] = $message_info;
            }
        }

        $data['unread_message_count'] = $unread_message_count;

        if($user_id){

            echo json_encode($data);
        } 

        return;
    }

}