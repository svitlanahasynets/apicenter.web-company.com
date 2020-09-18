<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Logs extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        return;
    }

    public function index($log_type = 'metrics')
    {
        $this->load->helper('tools');
        $this->load->helper('form');
        $this->load->helper('constants');
        $this->load->helper('translate');

        $url_data = $this->uri->uri_to_assoc(1);

        $data = [];
        $variables = [];

        if (!file_exists(APPPATH . 'views/' .TEMPLATE . '/logs/' . $log_type . '.php')) {
            show_404();
        }

        $variables['page_title']    = translate(ucfirst($log_type));
        $data['variables']          = $variables;

        $data['helpers'] = array('form');

        $data['css'] = array(
            'datepicker/daterangepicker.css',
            'form/multiple-select.css',
            'form/jquery.resizableColumns.css',
            'logs/common.css',
            'style.css'
        );

        $data['js'] = array(
            'datepicker/daterangepicker.js',
            'form/jquery.multiple.select.js',
            'form/store.min.js',            
            'form/jquery.resizableColumns.min.js',
            'form/jquery.stickytableheaders.min.js',
            'logs/logs.js'
        );

        $username = $this->session->userdata('username');
        $user     = $this->db->get_where('permissions_users', array('user_name' => $username))->row_array();
        $user_id  = $user['user_id'];

        $projects = $this->db->get_where('permissions_user_rules', array('user_id' => $user_id, 'type' => 'project'))->result_array();

        $permitted_project_ids = [];

        foreach ($projects as $key => $project) {
            $permitted_project_ids[$key] = $project['type_id'];
        }

        $selected_project_id = null;
        $permitted_project_ids = array_reverse($permitted_project_ids);

        $function = '';

        if (count($permitted_project_ids) > 0) {

            $log_type = $this->db->escape_str($log_type);
            $selected_project_id = $permitted_project_ids[0];

            if ($log_type == 'metrics') {   

                $function = 'projectcontrol';

            } else if ($log_type == 'invoices') {

                $function = 'importInvoices';

            } else if ($log_type == 'afas') {

                $function = 'afas_setup_error';

            } else if ($log_type == 'salesentries') {

                $function = 'importSalesEntry';

            } else if ($log_type == 'customers') {

                $function = 'importcustomers';

            } else if ($log_type == 'products') {

                $function = 'importarticles';

            } else if ($log_type == 'orders') {

                $function = 'exportorders';

            } else if ($log_type == 'shipments') {

                $function = 'tracktrace';

            } else if ($log_type == 'exact') {

                $function = 'exact_setup';

            } else if ($log_type == 'custom_module') {

                $function = 'custom_cronjob';
                
            } else if ($log_type == 'optiply') {

                $function = 'optiply_connection';
                
            } else if ($log_type == 'optiply_buyorder') {

                $function = 'exact_buy_orders';
                
            } else if ($log_type == 'optiply_sellorder') {

                $function = 'exact_sell_orders';
                
            } else if ($log_type == 'optiply_suppliers') {

                $function = 'optiply_suppliers';
                
            } else if ($log_type == 'optiply_return') {

                $function = 'reimport_orders';
                
            } else if ($log_type == 'admindebugging') {

                if ($user['role'] != 'admin') {
                    set_error_message('You cannot access to this page.');
                    redirect('/', 'refresh');
                }

                $function = 'admindebugging';
                
            }

            foreach ($permitted_project_ids as $key => $permitted_project_id) {
                $first_selectable_project_sql = "SELECT * FROM project_logs WHERE project_logs.project_id = " . $permitted_project_id . " AND project_logs.function = '" . $function . "'";

                $query = $this->db->query($first_selectable_project_sql);
                $first_selectable_project_count = $query->num_rows();

                if ($first_selectable_project_count) {
                    $selected_project_id = $permitted_project_id;
                    break;
                }
            }

            if (!empty($this->input->get_post('selected_project_id'))) {
                $selected_project_id = intval($this->input->get_post('selected_project_id'));
            }
        }

        $data['selected_project_id'] = $selected_project_id;  

        $this->load->model('Projects_model');
        $availableProjects = $this->Projects_model->getAvailableUserProjects();

        if($availableProjects['type'] == 'some'){
            if (!count($availableProjects['projects'])) {
                set_error_message('There is none permittable project.');
                redirect('/', 'refresh');
            }

            if ($selected_project_id != null) {
                if (!in_array($selected_project_id, $availableProjects['projects'])) {
                    set_error_message('Invalid Permission.');
                    redirect('/logs');
                }
            }
        }

        $page_response = [];
        $exported_order_count = 0;
        $generated_error_count = 0;

        $start_date = '';
        $end_date = '';

        $filter_condition = array(
            'project_id' => $selected_project_id, 
            'function' => $function
        );

        $current_day = date('d');
        $current_month = date('m');
        $current_year = date('Y');
        $d = cal_days_in_month(CAL_GREGORIAN, $current_month, $current_year);
        $daterange = $current_month.'/01/'.$current_year.' - '.$current_month.'/'.$d.'/'.$current_year;

        if ($this->input->get_post('daterange') != '') {
            $daterange = $this->input->get_post('daterange');            
        }

        $daterange_array = explode('-', $daterange);
        $start_date_timestamp = strtotime($daterange_array[0]);
        $end_date_timestamp = strtotime($daterange_array[1]) + 24*3600 - 1;
        $start_date = date('Y-m-d H:i:s', $start_date_timestamp);
        $end_date = date('Y-m-d H:i:s', $end_date_timestamp);
        $filter_condition['timestamp >'] = $start_date;
        $filter_condition['timestamp <'] = $end_date;
        $data['daterange'] = date('m/d/Y', $start_date_timestamp).' - '.date('m/d/Y', $end_date_timestamp);

        $total_sql = "SELECT * FROM project_logs WHERE project_logs.project_id = " . $selected_project_id . " AND project_logs.function = '" . $function . "' ";
        // $total_sql .= "AND project_logs.timestamp > '".$start_date."' AND project_logs.timestamp < '".$end_date."' ";

        $total_query = $this->db->query($total_sql);
        $totalCount = $total_query->num_rows();

        $error_sql = "SELECT * FROM project_logs WHERE project_logs.project_id = " . $selected_project_id . " AND project_logs.function = '" . $function . "' ";
        // $error_sql .= "AND project_logs.timestamp > '".$start_date."' AND project_logs.timestamp < '".$end_date."' ";
        $error_sql .= "AND project_logs.is_error = 1 ";

        if ($this->input->get_post('search_log') != '') {
            $search_log = $this->input->get_post('search_log');
            // $error_sql .= "AND LOWER(project_logs.message) LIKE '%".strtolower($search_log)."%' ";
        }

        $error_query = $this->db->query($error_sql);
        $generated_error_count = $error_query->num_rows();    

        $data['total_count'] = $totalCount;
        $data['generated_error_count'] = $generated_error_count;

        $only_error_filter = '';

        if ($this->input->get_post('only_error_filter') == 'only_error_filter') {
            $only_error_filter = $this->input->get_post('only_error_filter');            
            $filter_condition['is_error'] = 1;
        }

        $data['only_error_filter'] = $only_error_filter;

        $this->db->select("*");
        $this->db->from("project_logs");
        $this->db->where($filter_condition);

        $search_log = '';

        if ($this->input->get_post('search_log') != '') {
            $search_log = $this->input->get_post('search_log');
            $this->db->like('message', $search_log);
        }

        $data['search_log'] = $search_log;

        $total_count = $this->db->count_all_results();

        $this->db->select("*");
        $this->db->from("project_logs");
        $this->db->where($filter_condition);  

        if ($this->input->get_post('search_log') != '') {
            $search_log = $this->input->get_post('search_log');
            $this->db->like('message', $search_log);
        }

        // $log_contents_count = min(100, $total_count);
        $log_contents_count = $total_count;

        $all_logs = $this->input->get_post('all_logs', 0);

        if ($all_logs) {
            $log_contents_count = $total_count;
        }        

        $numberOfPages = ceil($log_contents_count / DISPLAY_TABLE_ROWS);
        $current_page = $this->input->get_post('per_page') ? $this->input->get_post('per_page')/DISPLAY_TABLE_ROWS + 1 : 1;
        $currentItem = ($current_page * DISPLAY_TABLE_ROWS) - DISPLAY_TABLE_ROWS;

        if ($currentItem < 0) {
            $currentItem = 0;
        }

        $data['selected_date_sort'] = '';
        $data['error_sort'] = '';
        $data['name_sort'] = '';

        if($this->input->get_post('date') != ''){
            $data['selected_date_sort'] = $this->input->get_post('date');

            if ($this->input->get_post('date') == 'old') {
                $this->db->order_by("timestamp", "asc");
            } else if ($this->input->get_post('date') == 'new') {
                $this->db->order_by("timestamp", "desc");
            }
        } else if($this->input->get_post('error_sort') != ''){
            $data['error_sort'] = $this->input->get_post('error_sort');

            if ($this->input->get_post('error_sort') == 'yes') {
                $this->db->order_by("is_error", "desc");
            }
        } else if($this->input->get_post('name_sort') != ''){
            $data['name_sort'] = $this->input->get_post('name_sort');

            if ($this->input->get_post('name_sort') == 'asc') {
                $this->db->order_by("message", "asc");
            } else if ($this->input->get_post('name_sort') == 'desc') {
                $this->db->order_by("message", "desc");
            }
        }

        $this->db->limit(DISPLAY_TABLE_ROWS, $currentItem);

        $page_response = $this->db->get()->result_array();

        $this->load->library('pagination');
        $config['base_url'] = site_url('/' . $log_type . '?selected_project_id=' . $selected_project_id.'&daterange='.$data['daterange'].'&only_error_filter='.$data['only_error_filter'].'&search_log='.$data['search_log'].'&date='.$data['selected_date_sort'].'&error_sort='.$data['error_sort'] .'&name_sort='.$data['name_sort']. '&all_logs=' . $all_logs);
        $config['total_rows'] = $log_contents_count;
        $config['per_page'] = DISPLAY_TABLE_ROWS;
        $config['page_query_string'] = TRUE;
        $config["uri_segment"] = 2;
        $this->pagination->initialize($config);

        $data['from'] = 0;
        $data['to'] = 0;
        $data['total'] = 0;

        if ($total_count) {
            $data['from'] = ($current_page - 1) * DISPLAY_TABLE_ROWS + 1;
            $data['to'] = min($current_page * DISPLAY_TABLE_ROWS, $log_contents_count);
            $data['total'] = $log_contents_count;
        }
        
        $data['log_contents'] = $page_response;
        $data["links"] = $this->pagination->create_links();

        $data['views'] = array('logs/'.$log_type);

        $this->output_data($data);
    }

}

/* End of file Logs.php */
/* Location: ./application/controllers/Logs.php */
