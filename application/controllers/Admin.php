<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends MY_Controller {

    public function __construct()
    {
        parent::__construct();

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

        if ($user[0]['role'] != 'admin') {
            redirect('/', 'refresh');
        }

        return;
    }

    public function overview()
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

        $variables = array();
        $variables['page_title'] = translate('Admin overview');
        $variables['go_back_url'] = site_url('/admin-overview');
        $variables['go_back_title'] = translate('Back to Admin Overview');

        $data = array();
        $data['variables'] = $variables;
        $data['helpers'] = array('form');
        $data['models'] = array('Permissions_model');
        $data['libraries'] = array('Pmprojects');
        $data['views'] = array('admin/overview');
        $data['js'] = array(
            'form/jquery.multiple.select.js',
            'form/store.min.js',
            'form/jquery.resizableColumns.min.js',
            'form/jquery.stickytableheaders.min.js',
            'admin/overview.js'
        );
        $data['css'] = array('form/multiple-select.css', 
            'form/jquery.resizableColumns.css',
            'admin/overview.css'
        );

        $filter_condition = array(
            'recipient' => $user_id
        );

        $this->db->select("*");
        $this->db->from("user_messages");

        $this->db->where($filter_condition);
        
        $total_count = $this->db->count_all_results();

        $this->db->select("user_messages.*, permissions_users.user_name as message_sender");
        $this->db->from("user_messages");
        $this->db->join('permissions_users', 'permissions_users.user_id = user_messages.sender','left');
        
        $this->db->where($filter_condition);

        $numberOfPages = ceil($total_count / DISPLAY_TABLE_ROWS);
        $current_page = $this->input->get_post('per_page') ? $this->input->get_post('per_page')/DISPLAY_TABLE_ROWS + 1 : 1;
        $currentItem = ($current_page * DISPLAY_TABLE_ROWS) - DISPLAY_TABLE_ROWS;

        if ($currentItem < 0) {
            $currentItem = 0;
        }

        $this->db->limit(DISPLAY_TABLE_ROWS, $currentItem);

        $messages = $this->db->get()->result_array();

        $this->load->library('pagination');
        $config['base_url'] = site_url('/admin-overview');
        $config['total_rows'] = $total_count;
        $config['per_page'] = DISPLAY_TABLE_ROWS;
        $config['page_query_string'] = TRUE;
        $config["uri_segment"] = 2;
        $this->pagination->initialize($config);

        $data['from'] = 0;
        $data['to'] = 0;
        $data['total'] = 0;

        if ($total_count) {
            $data['from'] = ($current_page - 1) * DISPLAY_TABLE_ROWS + 1;
            $data['to'] = min($current_page * DISPLAY_TABLE_ROWS, $total_count);
            $data['total'] = $total_count;
        }
        
        $data['messages'] = $messages;
        $data["links"] = $this->pagination->create_links();

        $this->output_data($data);
    }

    public function settings()
    {
        $variables = array();
        $variables['page_title'] = translate('Admin settings');
        $variables['go_back_url'] = site_url('/admin-settings');
        $variables['go_back_title'] = translate('Back to Admin Settings');

        $data = array();
        $data['variables'] = $variables;
        $data['helpers'] = array('form');
        $data['models'] = array('Permissions_model');
        $data['libraries'] = array('Pmprojects');
        $data['views'] = array('admin/settings');
        $data['js'] = array(
            'form/jquery.multiple.select.js',
            'form/store.min.js',
            'form/jquery.resizableColumns.min.js',
            'form/jquery.stickytableheaders.min.js',
            'admin/settings.js'
        );
        $data['css'] = array('form/multiple-select.css', 
            'form/jquery.resizableColumns.css',
            'admin/settings.css'
        );

        $projects = $this->db->get('projects')->result_array();
        $data['projects'] = $projects;

        $this->output_data($data);
    }

    public function testApiConnection()
    {
        $variables = array();
        $variables['page_title'] = translate('Test Api Connection');
        $variables['go_back_url'] = site_url('/admin-test-api-connection');
        $variables['go_back_title'] = translate('Back to Test Api Connection');

        $response = null;

        if ($this->input->get_post('api_request_method') && $this->input->get_post('api_request_url')) {
            
            // request method
            $api_request_method = $this->input->get_post('api_request_method');

            // request url
            $api_request_url = $this->input->get_post('api_request_url');

            // list request body
            $bparam = $this->input->get_post('bparam') ? $this->input->get_post('bparam') : '';
            $bvalue = $this->input->get_post('bvalue') ? $this->input->get_post('bvalue') : '';

            // raw request body
            $raw_request_body_str = $this->input->get_post('raw_request_body') ? $this->input->get_post('raw_request_body') : '';

            // request Auth
            $api_request_auth = $this->input->get_post('api_request_auth') ? $this->input->get_post('api_request_auth') : '';

            // Authorization params
            $api_key_param_key = $this->input->get_post('api_key_param_key') ? $this->input->get_post('api_key_param_key') : '';
            $api_key_param_value = $this->input->get_post('api_key_param_value') ? $this->input->get_post('api_key_param_value') : '';
            $basic_auth_username = $this->input->get_post('basic_auth_username') ? $this->input->get_post('basic_auth_username') : '';
            $basic_auth_password = $this->input->get_post('basic_auth_password') ? $this->input->get_post('basic_auth_password') : '';

            $bearer_token_key = $this->input->get_post('bearer_token_key') ? $this->input->get_post('bearer_token_key') : '';
            $oauth_2_key = $this->input->get_post('oauth_2_key') ? $this->input->get_post('oauth_2_key') : '';

            // Header params
            $content_type = $this->input->get_post('content_type') ? $this->input->get_post('content_type') : '';
            $accept = $this->input->get_post('accept') ? $this->input->get_post('accept') : '';
            $x_api_key = $this->input->get_post('x_api_key') ? $this->input->get_post('x_api_key') : '';

            if ($x_api_key == '' && $api_key_param_value) {
                if ($api_key_param_key == 'x-api-key') {
                    $x_api_key = $api_key_param_value;
                }
            }

            // Query Params
            $param_key_1 = $this->input->get_post('param_key_1') ? $this->input->get_post('param_key_1') : '';
            $param_value_1 = $this->input->get_post('param_value_1') ? $this->input->get_post('param_value_1') : '';
            $param_key_2 = $this->input->get_post('param_key_2') ? $this->input->get_post('param_key_2') : '';
            $param_value_2 = $this->input->get_post('param_value_2') ? $this->input->get_post('param_value_2') : '';
            $param_key_3 = $this->input->get_post('param_key_3') ? $this->input->get_post('param_key_3') : '';
            $param_value_3 = $this->input->get_post('param_value_3') ? $this->input->get_post('param_value_3') : '';

            $host_url_array = explode("/", explode("//", $api_request_url)[1]);
            $host_url = $host_url_array[0];

            // if we select "API Key" on Authorization Tab

            $httpheader = array();

            if ($api_request_auth == 'api_key' && $x_api_key) {
                $httpheader = array(
                    "Accept: */*",
                    "Accept-Encoding: gzip, deflate",
                    "Cache-Control: no-cache",
                    "Connection: keep-alive",
                    "Host: " . $host_url,
                    "User-Agent: PostmanRuntime/7.20.1",
                    "cache-control: no-cache",
                );
            }

            if ($content_type) {
                $httpheader[] = "Content-Type: " . $content_type;
            }

            if ($api_request_auth == 'api_key' && $x_api_key) {
                $httpheader[] = "x-api-key: " . $x_api_key;
            } elseif ($api_request_auth == 'basic_auth' && $basic_auth_username != '' && $basic_auth_password != '') {
                $auth_str = $basic_auth_username . ":" . $basic_auth_password;
                $hash_str = base64_encode($auth_str);
                $httpheader[] = "Authorization: Basic " . $hash_str;
            }

            $raw_request_body = json_decode($raw_request_body_str, true);

            $postfields = '';
            if ($raw_request_body) {
                $postfields = json_encode($raw_request_body);
            }

            if (strlen($postfields)) {
                $httpheader[] = "Content-Length: " . strlen($postfields);
            }

            $request_info = array(
                CURLOPT_URL => $api_request_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => $api_request_method,
                CURLOPT_POSTFIELDS => $postfields,
                CURLOPT_HTTPHEADER => $httpheader,
            );

            if ($api_request_auth == 'basic_auth') {
                $request_info[CURLOPT_FOLLOWLOCATION] = true;
            }

            // curl command section
            $curl = curl_init();
            curl_setopt_array($curl, $request_info);
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            $response = json_encode(json_decode($response, true), JSON_PRETTY_PRINT);
            $response_rows_number = count(explode("\n", $response)) + 5;

        }

        $data = array();
        $data['variables'] = $variables;
        $data['helpers'] = array('form');
        $data['models'] = array('Permissions_model');
        $data['libraries'] = array('Pmprojects');
        $data['views'] = array('admin/testApiConnection');
        $data['js'] = array(
            'form/jquery.multiple.select.js',
            'form/store.min.js',
            'form/jquery.resizableColumns.min.js',
            'form/jquery.stickytableheaders.min.js',
            'admin/testApiConnection.js'
        );
        $data['css'] = array('form/multiple-select.css', 
            'form/jquery.resizableColumns.css',
            'admin/testApiConnection.css'
        );
        
        $data['api_request_method'] = $api_request_method;
        $data['api_request_url'] = $api_request_url;
        $data['api_request_auth'] = '';
        $data['bparam'] = $bparam;
        $data['bvalue'] = $bvalue;
        $data['raw_request_body'] = $raw_request_body;
        $data['api_key_param_key'] = $api_key_param_key;
        $data['api_key_param_value'] = $api_key_param_value;
        $data['basic_auth_username'] = $basic_auth_username;
        $data['basic_auth_password'] = $basic_auth_password;
        $data['bearer_token_key'] = $bearer_token_key;
        $data['oauth_2_key'] = $oauth_2_key;
        $data['content_type'] = $content_type;
        $data['accept'] = $accept;
        $data['x_api_key'] = $x_api_key;

        $data['response'] = $response;
        $data['response_rows_number'] = $response_rows_number ? $response_rows_number : 5;

        $this->output_data($data);
    }

    public function maintenance()
    {
        $variables = array();
        $variables['page_title'] = translate('Maintenance');
        $variables['go_back_url'] = site_url('/admin-maintenance');
        $variables['go_back_title'] = translate('Back to Maintenance');

        $data = array();

        $data['variables'] = $variables;
        $data['helpers'] = array('form');
        $data['models'] = array('Permissions_model');
        $data['libraries'] = array('Pmprojects');
        $data['views'] = array('admin/maintenance');
        $data['js'] = array(
            'form/jquery.multiple.select.js',
            'form/store.min.js',
            'form/jquery.resizableColumns.min.js',
            'form/jquery.stickytableheaders.min.js',
            'admin/maintenance.js'
        );
        $data['css'] = array('form/multiple-select.css', 
            'form/jquery.resizableColumns.css',
            'admin/maintenance.css'
        );

        $this->load->model('Projects_model');
        $availableProjects = $this->Projects_model->getAvailableUserProjects();

        $collection = $this->db->select('projects.*');
        $collection->from('projects');

        // Check available projects by permissions
        if($availableProjects['type'] == 'some'){
            if (count($availableProjects['projects']) > 0) {
                $collection->where_in('projects.id', $availableProjects['projects']);
            } else {
                $collection->where(0);
            }           
        }

        $collection->order_by('projects.id', 'desc');        
        $projects = $collection->get()->result_array();
        $data['projects'] = $projects;

        $this->output_data($data);
    }

    public function remove_tmp_files()
    {
        $project_id = $this->input->post('project_id');
        $project_name = $this->input->post('project_name');

        //The name of the folder.
        $folder = DATA_DIRECTORY . '/tmp_files/' . $project_id;
         
        //Get a list of all of the file names in the folder.
        $files = glob($folder . '/*');
         
        //Loop through the file list.
        foreach($files as $file){
            //Make sure that this is a file and not a directory.
            if(is_file($file)){
                //Use the unlink function to delete the file.
                unlink($file);
            }
        }

        $data = array();
        $data['success'] = true;
        $data['project_id'] = $project_id;
        $data['project_name'] = $project_name;

        echo json_encode($data);
        return;
    }


    public function sendMessage()
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

        if ($user[0]['role'] != 'admin') {
            redirect('/', 'refresh');
        }

        $ignore = array($user_id);

        $this->db->select("*");
        $this->db->from("permissions_users");
        $this->db->where_not_in('permissions_users.user_id', $ignore);
        $customers = $this->db->get()->result_array();

        $selectable_customers = array();

        foreach ($customers as $key => $customer) {
            $selectable_customers[$customer['user_id']] = $customer['user_name'];
        }

        $current_project_id = $this->input->get_post('selected_project_id');

        if ($current_project_id == '') {
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
            $current_project_id = $projectRules[0];
        }

        if ($this->input->get_post('selected_customers')) {

            $selected_customers = $this->input->get_post('selected_customers');
            $subject = $this->input->get_post('subject') ? $this->input->get_post('subject') : '';
            $message_body = $this->input->get_post('message_body') ? $this->input->get_post('message_body') : '';

            $file = $_FILES['file'];
            
            // $file_type = '';

            // if (count($_FILES) > 0) {

            //     $file_type = $file['type'];

            //     if (is_uploaded_file($file['tmp_name'])) {
            //         $message_body = file_get_contents($file['tmp_name']);
            //     }
            // }

            foreach ($selected_customers as $key => $selected_customer) {
                $new_message = array();
                $new_message['sender'] = $user_id;
                $new_message['recipient'] = $selected_customer;
                $new_message['type'] = "E";
                $new_message['subject'] = $subject;
                $new_message['message_body'] = $message_body;

                // if ($file_type != '') {
                //     $new_message['file_type'] = $file_type;
                // }
                
                $new_message['url'] = '';
                $new_message['date'] = date("Y-m-d H:i:s");
                $new_message['isRead'] = 0;
                $new_message['project_id'] = $current_project_id;
                $new_message['visibility'] = 1;
                $new_message['generated_by'] = '';

                $this->db->insert('user_messages', $new_message);
            }

            set_success_message('Message sent.');
            redirect('/message-center');
        }

        $variables = array();
        $variables['page_title'] = translate('Send Message');
        $variables['go_back_url'] = site_url('/admin-sendmessage');
        $variables['go_back_title'] = translate('Back to Admin Overview');

        $data = array();
        $data['variables'] = $variables;
        $data['helpers'] = array('form');
        $data['models'] = array('Permissions_model');
        $data['libraries'] = array('Pmprojects');
        $data['views'] = array('admin/sendMessage');
        $data['js'] = array(
            'form/jquery.multiple.select.js',
            'form/store.min.js',
            'form/jquery.resizableColumns.min.js',
            'form/jquery.stickytableheaders.min.js',
            'admin/sendMessage.js'
        );
        $data['css'] = array('form/multiple-select.css', 
            'form/jquery.resizableColumns.css',
            'admin/sendMessage.css'
        );

        $data['selectable_customers'] = $selectable_customers;
        $data['viewable_customers_count'] = count($selectable_customers) > 10 ? 10 : count($selectable_customers);
        $data['current_project_id'] = $current_project_id;

        $this->output_data($data);
    }

}

/* End of file Logs.php */
/* Location: ./application/controllers/Logs.php */
