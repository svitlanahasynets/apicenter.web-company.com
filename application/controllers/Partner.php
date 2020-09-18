<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Partner extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
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
        $variables['page_title'] = translate('Partner overview');
        $variables['go_back_url'] = site_url('/partner-overview');
        $variables['go_back_title'] = translate('Back to Partner Overview');

        $data = array();
        $data['variables'] = $variables;
        $data['helpers'] = array('form');
        $data['models'] = array('Permissions_model');
        $data['libraries'] = array('Pmprojects');
        $data['views'] = array('partner/overview');
        $data['js'] = array(
            'form/jquery.multiple.select.js',
            'form/store.min.js',
            'form/jquery.resizableColumns.min.js',
            'form/jquery.stickytableheaders.min.js',
            'partner/overview.js'
        );
        $data['css'] = array('form/multiple-select.css', 
            'form/jquery.resizableColumns.css',
            'partner/overview.css'
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
        $config['base_url'] = site_url('/partner-overview');
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

}

/* End of file Logs.php */
/* Location: ./application/controllers/Logs.php */
