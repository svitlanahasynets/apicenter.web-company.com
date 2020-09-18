<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Messages extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        return;
    }

    public function index()
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
        
        /* Remove when going LIVE */
        if ($user[0]['role'] != 'admin') {
            redirect('/', 'refresh');
        }
        
        $variables = array();
        $variables['page_title'] = translate('Message center');
        $variables['go_back_url'] = site_url('/message-center');
        $variables['go_back_title'] = translate('Back to all messages');
        $variables['active_menu_item'] = 'message-center';

        $data = array();
        $data['variables'] = $variables;
        $data['helpers'] = array('form');
        $data['models'] = array('Permissions_model');
        $data['libraries'] = array('Pmprojects');
        $data['views'] = array('messages/index');
        $data['js'] = array(
            'form/jquery.multiple.select.js',
            'form/store.min.js',
            'form/jquery.resizableColumns.min.js',
            'form/jquery.stickytableheaders.min.js',
            'messages/index.js'
        );
        $data['css'] = array('form/multiple-select.css', 
            'form/jquery.resizableColumns.css',
            'messages/index.css'
        );

        $selected_project_id = null;

        if (!empty($this->input->get_post('selected_project_id'))) {
            $selected_project_id = intval($this->input->get_post('selected_project_id'));
        }

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
        $config['base_url'] = site_url('/message-center?selected_project_id=' . $selected_project_id);
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

    public function view(){

        $url_data = $this->uri->uri_to_assoc(1);
        $message_id = $url_data['id'];

        $variables                  = array();
        $variables['page_title']    = translate('Messages View');
        $variables['go_back_url']   = site_url('/messages');
        $variables['go_back_title'] = translate('Back to all projects');
        $variables['active_menu_item'] = 'messages';

        $filter_condition = array(
            'message_id' => $message_id
        );

        $this->db->select("user_messages.*, pu1.user_name as message_sender, pu2.user_name as message_receiver");
        $this->db->from("user_messages");
        $this->db->join('permissions_users pu1', 'pu1.user_id = user_messages.sender','left');
        $this->db->join('permissions_users pu2', 'pu2.user_id = user_messages.recipient','left');
        $this->db->where($filter_condition);

        $messages = $this->db->get()->result_array();

        $update_data = array();
        $update_data['isRead'] = 1;

        $this->db->where('message_id', $message_id);
        $this->db->update('user_messages', $update_data);

        $data               = array();
        $data['variables']  = $variables;
        $data['models']     = array('Permissions_model', 'Projects_model');
        $data['helpers']    = array('form');
        $data['libraries']  = array('Pmprojects');
        $data['views']      = array('messages/view');

        $data['js'] = array(
            'form/jquery.multiple.select.js',
            'form/store.min.js',
            'form/jquery.resizableColumns.min.js',
            'form/jquery.stickytableheaders.min.js',
            'messages/view.js'
        );
        $data['css'] = array('form/multiple-select.css', 
            'form/jquery.resizableColumns.css',
            'messages/view.css'
        );

        $data['message']    = $messages[0];

        $this->output_data($data);

    }

    public function imgView(){

        if(isset($_GET['message_id'])) {

            $filter_condition = array(
                'message_id' => $_GET['message_id']
            );

            $this->db->select("*");
            $this->db->from("user_messages");
            $this->db->where($filter_condition);
            $messages = $this->db->get()->result_array();

            $message = $messages[0];

            header("Content-type: " . $message["file_type"]);
            echo $message["message_body"];
        }

    }

    public function delete(){

        $url_data = $this->uri->uri_to_assoc(1);
        $message_id = $url_data['id'];

        $this->db->where('message_id', $message_id);
        $this->db->delete('user_messages');

        // Set success message and redirect to projects overview
        set_success_message('Message deleted');
        redirect('/message-center');

        return;
    }

}

/* End of file Logs.php */
/* Location: ./application/controllers/Logs.php */
