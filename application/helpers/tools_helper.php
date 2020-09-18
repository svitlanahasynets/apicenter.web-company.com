<?php

if(!function_exists('cut')){
	function cut($string = '', $maxLength = 99999999, $dots = true){
		if($dots == true){
			return (strlen($string) > $maxLength) ? substr($string,0,$maxLength - 3).'...' : $string;
		} else {
			return (strlen($string) > $maxLength) ? substr($string,0,$maxLength) : $string;
		}
	}
}

if(!function_exists('format_date')){
	function format_date($date, $start_format = 'Y_m_d', $end_format = DATE_FORMAT){
		if(!$date){
			return '';
		}
		$dateTime = DateTime::createFromFormat($start_format, $date);
		if($dateTime){
			return $dateTime->format($end_format);
		}
		return $date;
	}
}

if(!function_exists('format_db_decimal')){
	function format_db_decimal($number, $decimal = PRICE_DECIMAL_SEPARATOR){
		if(!$number){
			return '';
		}
		$number = str_replace('.', $decimal, $number);
		return $number;
	}
}

if(!function_exists('format_price')){
	function format_price($price, $currencySymbol = CURRENCY_SYMBOL, $thousands = PRICE_THOUSAND_SEPARATOR, $decimal = PRICE_DECIMAL_SEPARATOR){
		if($price == 0){
			return '';
		}
		$price = str_replace($thousands, '', $price);
		$price = str_replace($decimal, '.', $price);
		if(DISPLAY_CURRENCY_SYMBOL){
			$price = $currencySymbol . number_format(floatval($price), 2, $decimal, $thousands);
		} else {
			$price = number_format(floatval($price), 2, $decimal, $thousands);
		}
		return $price;
	}
}

if(!function_exists('array_column')){
	function array_column($array, $column)
	{
	    $ret = array();
	    foreach ($array as $row) $ret[] = $row[$column];
	    return $ret;
	}
}

if(!function_exists('get_project_title')){
	function get_project_title($id)
	{
		$project = get_instance()->db->get_where('projects', array('id' => $id))->row_array();
		if(!empty($project)){
			return $project['title'];
		} else {
			return $id;
		}
	}
}

if(!function_exists('get_supplier_name')){
	function get_supplier_name($id)
	{
		$supplier = get_instance()->db->get_where('suppliers', array('supplier_id' => $id))->row_array();
		if(!empty($supplier)){
			return $supplier['supplier_name'];
		} else {
			return $id;
		}
	}
}

if(!function_exists('get_boolean_label')){
	function get_boolean_label($value)
	{
		if($value == '1'){
			return translate("Yes");
		} else {
			return translate("No");
		}
	}
}

if(!function_exists('set_filter_data')){
	function set_filter_data($fields, $module = null, $action = null)
	{
		$instance = get_instance();
		$currentData = $instance->session->userdata('filter_data');
		if(is_null($currentData)){
			$currentData = array();
		}
		
		if($module == null){
			$module = strtolower($instance->router->fetch_class());
		}
		if($action == null){
			$action = strtolower($instance->router->fetch_method());
		}
		
		$currentFields = array();
		if(isset($currentData[$module][$action])){
			$currentFields = $currentData[$module][$action];
		}
		$fields = array_merge($currentFields, $fields);
		
		$currentData[$module][$action] = $fields;
		
		$instance->session->set_userdata('filter_data', $currentData);
		return;
	}
}

if(!function_exists('reset_filter_data')){
	function reset_filter_data($module = null, $action = null)
	{
		$instance = get_instance();
		$currentData = $instance->session->userdata('filter_data');
		if(is_null($currentData)){
			$currentData = array();
		}
		
		if($module == null){
			$module = strtolower($instance->router->fetch_class());
		}
		if($action == null){
			$action = strtolower($instance->router->fetch_method());
		}
		
		unset($currentData[$module][$action]);
		
		$instance->session->set_userdata('filter_data', $currentData);
		return;
	}
}

if(!function_exists('get_user_preference')){
	function get_user_preference($preferenceCode)
	{
		$instance = get_instance();
		$username = $instance->session->userdata('username');
		$user = $instance->db->get_where('permissions_users', array('user_name' => $username))->row_array();
		$userId = $user['user_id'];
		$preference = $instance->db->get_where('permissions_user_preferences', array('user_id' => $userId, 'preference' => $preferenceCode))->row_array();
		if(!empty($preference)){
			return json_decode($preference['value'], true);
		} else {
			return '';
		}
	}
}

if(!function_exists('set_user_preference')){
	function set_user_preference($preferenceCode, $value)
	{
		$instance = get_instance();
		$username = $instance->session->userdata('username');
		$user = $instance->db->get_where('permissions_users', array('user_name' => $username))->row_array();
		$userId = $user['user_id'];
		$preference = $instance->db->get_where('permissions_user_preferences', array('user_id' => $userId, 'preference' => $preferenceCode))->row_array();
		$data = array(
			'user_id' => $userId,
			'preference' => $preferenceCode,
			'value' => json_encode($value)
		);
		if(!empty($preference)){
			$instance->db->where('user_id', $userId);
			$instance->db->where('preference', $preferenceCode);
			$instance->db->update('permissions_user_preferences', $data);
		} else {
			$instance->db->insert('permissions_user_preferences', $data);
		}
		return;
	}
}

if(!function_exists('api2cart_log')){
	function api2cart_log($projectId, $logType, $message)
	{
		$instance = get_instance();
		$logDirectory = DATA_DIRECTORY.'/log_files/'.$projectId.'/';
		if(!file_exists($logDirectory)){
			mkdir($logDirectory, 0777, true);
		}
		$fileLocation = $logDirectory.$logType.'.log';
		if(file_exists($fileLocation)){
			$lines = explode("\n", file_get_contents($fileLocation));
			$lines = array_slice($lines, 0, 30000);
			array_unshift($lines, date("Y-m-d H:i:s").' --> '.$message);
			$lines = implode("\n", $lines);
			file_put_contents($fileLocation, $lines);
		} else {
			file_put_contents($fileLocation, date("Y-m-d H:i:s").' --> '.$message);
		}
		return;
	}
}

if(!function_exists('apicenter_logs')){
    function apicenter_logs($projectId, $logFunction, $message, $isError = false)
    {
        $CI =& get_instance();
        $CI->load->database();

        $CI->db->insert('project_logs',
            [
                'project_id' => $projectId,
                'function' => $logFunction,
                'message' => $message,
				'is_error' => $isError
            ]);
        return;
    }
}



if(!function_exists('exact_log')){
    function exact_log($projectId, $logType, $message)
    {
        $CI =& get_instance();
        $CI->load->database();

        $CI->db->insert('exact_logs',
            [
                'project_id' => $projectId,
                'type' => $logType,
                'body' => $message
            ]);
        return;
    }
}

if(!function_exists('optiply_log')){
    function optiply_log($projectId, $logType, $message)
    {
        $CI =& get_instance();
        $CI->load->database();

        $CI->db->insert('optiply_logs',
            [
                'project_id' => $projectId,
                'type' => $logType,
                'message' => $message
            ]);
        return;
    }
}

if(!function_exists('afas_log')){
    function afas_log($projectId, $logType, $message)
    {
        $CI =& get_instance();
        $CI->load->database();

        $CI->db->insert('afas_logs',
            [
                'project_id' => $projectId,
                'type' => $logType,
                'message' => $message
            ]);
        return;
    }
}

if(!function_exists('magento_log')){
    function magento_log($projectId, $logType, $message)
    {
        $CI =& get_instance();
        $CI->load->database();

        $CI->db->insert('magento2_logs',
            [
                'project_id' => $projectId,
                'type' => $logType,
                'message' => $message
            ]);
        return;
    }
}

if(!function_exists('magento1_log')){
    function magento1_log($projectId, $logType, $message)
    {
        $CI =& get_instance();
        $CI->load->database();

        $CI->db->insert('magento1_logs',
            [
                'project_id' => $projectId,
                'type' => $logType,
                'message' => $message
            ]);
        return;
    }
}

if(!function_exists('project_error_log')){
	function project_error_log($projectId, $logType, $message, $delete = ''){
		$instance = get_instance();
		$logDirectory = DATA_DIRECTORY.'/log_files/'.$projectId.'/';
		if(!file_exists($logDirectory)){
			mkdir($logDirectory, 0777, true);
		} else{
			if($delete=='delete'){
				unlink( $logDirectory.$logType.'.log');
				return;
			}
		}
		$fileLocation = $logDirectory.$logType.'.log';
		if(file_exists($fileLocation)){
			$lines = explode("\n", file_get_contents($fileLocation));
			$lines = array_slice($lines, 0, 3000);
			array_unshift($lines, date("Y-m-d H:i:s").' --> '.$message);
			$lines = implode("\n", $lines);
			file_put_contents($fileLocation, $lines);
		} else {
			file_put_contents($fileLocation, date("Y-m-d H:i:s").' --> '.$message);
		}
		return;
	}
}

if(!function_exists('save_image_string')){
	function save_image_string($projectId, $imageName, $imageString, $decode = true)
	{
		$instance = get_instance();
		$dir = DATA_DIRECTORY.'/tmp_files/'.$projectId.'/';
		if(!file_exists($dir)){
			mkdir($dir, 0777, true);
		}
		$imageName = str_replace(' ', '', $imageName);
		$imageName = str_replace(',', '', $imageName);
		$imageName = str_replace('JPG', 'jpg', $imageName);
		$imageName = rand(0, 999).$imageName;
		$fileLocation = $dir.$imageName;
		if(file_exists($fileLocation)){
			unlink($fileLocation);
		}
		if($decode){
//			file_put_contents($fileLocation, base64_decode($imageString, true));
			file_put_contents($fileLocation, base64_decode($imageString));
		} else {
			file_put_contents($fileLocation, $imageString);
		}
		return array(
			'url' => DATA_URL.'/tmp_files/'.$projectId.'/'.$imageName,
			'path' => $fileLocation,
			'image_name' => $imageName,
		);
	}
}

if(!function_exists('get_erp_system_label')){
	function get_erp_system_label($erpSystemCode)
	{
		if($erpSystemCode == 'afas'){
			return 'AFAS';
		} elseif($erpSystemCode == 'exactonline'){
			return 'Exact Online';
		} elseif($erpSystemCode == 'visma'){
			return 'Visma';
		}
		return false;
	}
}