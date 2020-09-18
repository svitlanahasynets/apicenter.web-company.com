<?php
class Projects_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }
    
    function addRowToArray($array){
        $defaultArray = array(
            'date' => '',
            'name' => '',
            'invoice_nr' => '',
            'description' => '',
            'qty' => '',
            'price' => '',
            'total' => '',
            'type' => ''
        );
        $array = array_merge($defaultArray, $array);
        return $array;
    }
    
    function getAvailableUserProjects(){
        $userName = $this->session->userdata('username');
        $instance = get_instance();
        $user = $instance->db->get_where('permissions_users', array(
            'user_name' => $userName
        ))->row_array();
        $user_id = $user['user_id'];
        
        $projectRules = array();
        $canViewAllProjects = false;
        
        // Check for user rules
        $rules = $this->db->get_where('permissions_user_rules', array(
            'user_id' => $user_id,
            'type' => 'project'
        ))->result_array();
        $allProjects = $this->db->get_where('permissions_user_rules', array(
            'user_id' => $user_id,
            'type' => 'edit_all_projects'
        ))->row_array();
        if($allProjects && $allProjects['view'] == '1'){
            $canViewAllProjects = true;
        }

        $user_role = $user['role'];

        if ($user_role == 'partner') {
            $partner_projects = $this->db->get_where('projects', array(
                'partner_id' => $user_id
            ))->result_array();

            if(!empty($partner_projects)){
                foreach($partner_projects as $partner_project){
                    $projectRules[] = $partner_project['id'];
                }
            }
        } else {
            if(!empty($rules)){
                foreach($rules as $rule){
                    if($rule['view'] == 1){
                        $projectRules[] = $rule['type_id'];
                    }
                }
            }
        }
        
        if($canViewAllProjects){
            return array(
                'type' => 'all'
            );
        }
        return array(
            'type' => 'some',
            'projects' => $projectRules
        );
        
    }
    
    function saveValue($field, $value, $projectId, $type = 'project_setting'){
        // Check whether field already exists
        $this->db->where('code', $field);
        $this->db->where('project_id', $projectId);
        $this->db->from('project_settings');
        if($this->db->count_all_results() > 0){
            $this->db->where('code', $field);
            $this->db->where('project_id', $projectId);
            $this->db->update('project_settings', array('value' => $value));
        } else {
            $this->db->set('project_id', $projectId);
            $this->db->set('code', $field);
            $this->db->set('type', $type);
            $this->db->set('value', $value);
            $this->db->insert('project_settings');
        }
        return true;
    }
    
    function getValue($fieldCode, $projectId){
        $this->db->where('code', $fieldCode);
        $this->db->where('project_id', $projectId);
        $value = $this->db->get('project_settings')->row_array();
        $value = $value['value'];
        return $value;
    }

    function getProjectId($fieldCode,$fieldValue){
        $this->db->where('code', $fieldCode);
        $this->db->where('value', $fieldValue);
        $this->db->order_by("project_id", "desc");
        $value = $this->db->get('project_settings')->row_array();
        $value = $value['project_id'];
        return $value;
    }

    function scriptStart($projectId) {
        $this->saveValue('is_running', '1', $projectId);
        $this->saveValue('start_running', time(), $projectId);
    }

    function scriptFinish($projectId, $cms = '', $isRunning = 2) {
        optiply_log($projectId, 'script_finish', $cms.":".$isRunning);
        if($cms == 'optiply') {
            $this->saveValue('is_running', '0', $projectId);
        }
    }

    /**
     * Get from the Date import project
     * for Exact - Optiply projects
     * @param $projectId
     * @return $date String
     */
    function getDate($projectId) {

        $newDate = $this->db
            ->where('project_id', $projectId)
            ->where('code', 'from_date_changed')
            ->get('project_settings')
            ->row_array();

        if(!empty($newDate) && $newDate['value'] == '1') {
            $this->db->where('code', 'exact_ord_update_date');
            $this->db->where('project_id', $projectId);
            $value = $this->db->get('project_settings')->row_array();
            $value = $value['value'];
            $date = str_replace(' ', 'T', $value);

            return $date;
        }

        $this->db->where('code', 'orders_from_date');
        $this->db->where('project_id', $projectId);
        $value = $this->db->get('project_settings')->row_array();
        $value = $value['value'];
        $date = str_replace(' ', 'T', $value);

        return $date;
    }

    public function getWebshopUrl($projectId) {

        $this->db->where('id', $projectId);
        $value = $this->db->get('projects')->row_array();
        $url = $value['store_url'];

        return $url;
    }

    public function processStatuses($mainData, $compareData, $updatedProducts = []) {

        $toUpdate = [];

        foreach ($mainData as $mainName => $mainItem) {

            if(isset($compareData[$mainName]) && !in_array($mainName, $updatedProducts)) {
                $mainDate = strtotime($mainItem['date']);
                $compareDate = strtotime($compareData[$mainName]['date']);

                if($mainDate > $compareDate && strtolower($mainItem['status']) != strtolower($compareData[$mainName]['status'])) {
                    $toUpdate[] = $mainItem;
                }
            }
        }

        return $toUpdate;
    }

    public function importMissedLines($projectId) {
        $this->load->model('Afas_model');
        $this->load->model('Optiply_model');

        $orders = $this->db->get('orders_to_import', ['project_id' => $projectId, 'status' => 0]);
        $token = $this->Optiply_model->getAccesToken($projectId);
        $accountId = $this->getValue('optiply_acc_id', $projectId);

        foreach ($orders as $order) {

            $orderData = json_decode($order['order'], true);

            foreach ($orderData['lines'] as $line) {
                $itemData = $this->Afas_model->getItemByName($projectId, $line['name']);

                $productId = $this->Optiply_model->createProduct($token, $accountId, $itemData);
                if(!$productId) {
                    optiply_log($productId, 'h_prod_not_created', '');
                    $this->db->where('id', $order['id']);
                    $this->db->update('orders_to_import', ['status' => 2]);
                    continue;
                }
                $this->Optiply_model->createSupplierProducts($token, $order['supplier_id'], $productId, $accountId, $itemData);

                $lineId = $this->Optiply_model->pushBuyOrderLine($token, $line, $order['order_id'], $accountId, $productId);
                if(!$lineId) {
                    optiply_log($productId, 'h_line_not_created', '');
                    $this->db->where('id', $order['id']);
                    $this->db->update('orders_to_import', ['status' => 2]);
                    continue;
                }
                $this->Optiply_model->pushReceipLine($token, $line, $lineId, $accountId);
            }

            $this->db->where('id', $order['id']);
            $this->db->update('orders_to_import', ['status' => 1]);
        }

        $this->saveValue('missed_lines', '', $projectId);
    }

    public function getGMTtime() {
        date_default_timezone_set("GMT");
        $date = date('Y-m-d H:i:s');
        date_default_timezone_set("Europe/Amsterdam");

        return $date;
    }

    public function getMultiStores($projectId)
    {
        $code = 'store_'; 

        $this->db->where('project_id', $projectId);
        $this->db->like('code', $code);

        $values = $this->db->get('project_settings')->result_array();
        $result = [];

        foreach ($values as $value) {
            $store_code = str_replace($code, '', $value['code']);
            $result[] = [
                'code'  => $store_code,
                'value'=> $value['value']
            ];
        }

        return $result;
    }

    public function getCustomers()
    {
        $this->db->select("*");
        $this->db->from("permissions_users");
        $users = $this->db->get()->result_array();

        return $users;
    }

    public function getUnreadMessagesCount($user_id)
    {
        $filter_condition = array(
            'recipient' => $user_id,
            'isRead' => 0
        );

        $this->db->select("*");
        $this->db->from("user_messages");
        $this->db->where($filter_condition);
        $unread_messages = $this->db->get()->result_array();
        $unread_messages_count = count($unread_messages);

        return $unread_messages_count;
    }

    public function updateLastRemindTimstamp($user_id)
    {

        $data['last_remind_time']  = date('Y-m-d H:i:s');

        $this->db->where('user_id', $user_id);
        $this->db->update('permissions_users', $data);

        return;
    }

    public function getProjectIds($user_id)
    {
        // Check for user rules
        $rules = $this->db->get_where('permissions_user_rules', array(
            'user_id' => $user_id,
            'type' => 'project'
        ))->result_array();

        $project_ids = array();

        foreach ($rules as $key => $rule) {
            $project_ids[] = $rule['type_id'];
        }

        return $project_ids;        
    }

    public function getErrorLogCount($project_ids)
    {
        $project_ids_str = '(';
        $project_ids_str .= implode(',', $project_ids);
        $project_ids_str .= ')';  

        $generated_error_count = 0;          

        if (count($project_ids) > 0) {
            
            $error_sql = "SELECT * FROM project_logs WHERE project_logs.project_id IN " . $project_ids_str . " AND LOWER(project_logs.message) LIKE '%error:%' ";

            $query = $this->db->query($error_sql);
            $generated_error_count = $query->num_rows();
        }

        return $generated_error_count;        
    }

    public function getRecentLogCount($project_ids)
    {
        $project_ids_str = '(';
        $project_ids_str .= implode(',', $project_ids);
        $project_ids_str .= ')';

        $last_interval_time = 2 * 3600;

        $current_timestamp = time();
        $limit_timestamp = $current_timestamp - $last_interval_time;

        $recent_log_count = 0;          

        if (count($project_ids) > 0) {
            
            $recent_log_sql = "SELECT * FROM project_logs WHERE project_logs.project_id IN " . $project_ids_str . " AND UNIX_TIMESTAMP(project_logs.timestamp) > " . $limit_timestamp;

            $query = $this->db->query($recent_log_sql);
            $recent_log_count = $query->num_rows();
        }

        return $recent_log_count;        
    }

    public function getProjectCustomers($projectId)
    {
        $this->db->select('*');
        $this->db->from('permissions_users');
        $this->db->where('permissions_user_rules.type_id', $projectId);
        $this->db->where('permissions_user_rules.type', 'project');
        $this->db->join('permissions_user_rules', 'permissions_user_rules.user_id = permissions_users.user_id');

        return $this->db->get()->result_array();
    }

    public function getContactPerson($projectId)
    {
        $this->db->select('*');
        $this->db->from('permissions_users');
        $this->db->where('projects.id', $projectId);
        $this->db->join('projects', 'projects.contact_person = permissions_users.user_id');

        return $this->db->get()->result_array();
    }

    function getProjectValue($fieldCode, $projectId){
        $this->db->where('id', $projectId);
        $value = $this->db->get('projects')->row_array();
        $result = isset($value[$fieldCode]) ? $value[$fieldCode] : false;

        return $result;
    }
    
    public function getProjectData($projectId, $code, $decode = false){
        $data = $this->db->get_where('project_data', array('project_id' => $projectId, 'code' => $code))->row_array();
        $data = isset($data['data']) ? $data['data'] : '';
        if($data != '' && $decode == true){
            $data = json_decode($data, true);
        } elseif($decode == true){
            $data = array();
        }
        return $data;
    }

    public function saveProjectData($projectId, $code, $data, $encode = false){
        if($data != '' && $encode == true){
            $data = json_encode($data);
        }
        $existingItem = $this->db->get_where('project_data', array('project_id' => $projectId, 'code' => $code))->row_array();
        if(!empty($existingItem)){
            $this->db->where('id', $existingItem['id']);
            $this->db->update('project_data', array('data' => $data));
        } else {
            $this->db->insert('project_data', array('project_id' => $projectId, 'code' => $code, 'data' => $data));
        }
        return true;
    }
}