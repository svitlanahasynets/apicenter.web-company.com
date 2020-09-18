<?php
class Permissions_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }

    // Check whether user has access to item (function for create/edit user permissions)
    function check_permission($save_type, $item_type, $type_id, $user_id, $user_rules = array()){
    	$rules = array();
    	if($save_type == 'user'){
    		if(empty($user_rules)){
			    $rules = $this->db->get_where('permissions_user_rules', array(
			    	'user_id' => $user_id,
			    	'type' => $item_type,
			    	'type_id' => $type_id
			    ))->result_array();
			} else {
				$rules = $user_rules;
			}
    	}

    	$permission = false;
	    if(!empty($rules)){
	    	foreach($rules as $rule){
	    		if($rule['type_id'] == $type_id && $rule['type'] == $item_type){
			    	if($rule['view'] == 1 && $rule['edit'] == 1){
				    	$permission = 've';
			    	} elseif($rule['view'] == 1){
				    	$permission = 'v';
			    	}
	    		}
	    	}
		}

		// If type of check is project, check whether user or group can edit all projects
		if($item_type == 'project' && $permission != 'v' && $permission != 've'){
			$permissionAllProjects = $this->check_permission('user', 'edit_all_projects', 0, $user_id);
			if($permissionAllProjects == 've'){
				$permission = 've';
			}
			if($permission != 've' && $permissionAllProjects == 'v'){
				$permission = 'v';
			}
		}

	    return $permission;
    }

    // Check whether specific user has access to item
    function check_permission_user($type, $type_id, $username_or_userid = null, $user_rules = array()){
    	$permission = false;
    	if($username_or_userid == null){
	    	$username_or_userid = $this->session->userdata('username');
    	}

    	if(is_numeric($username_or_userid)){
	    	$user_id = $username_or_userid;
    	} else {
	    	$user = $this->db->get_where('permissions_users', array(
		    	'user_name' => $username_or_userid
		    ))->result_array();
		    $user_id = $user[0]['user_id'];
		}

		// If user is admin, grant full permissions
    	$user = $this->db->get_where('permissions_users', array(
	    	'user_id' => $user_id
	    ))->row_array();

	    // if($user['user_name'] == 'admin'){
		   //  return 've';
	    // }

		// Check whether user has access
		foreach($user_rules as $rule){
			if($rule['type_id'] == $type_id){
		    	if($rule['view'] == 1 && $rule['edit'] == 1){
			    	$permission = 've';
			    // If rule is set to view only and permission is not already view+edit from other user groups
		    	} elseif($rule['view'] == 1 && $permission != 've'){
			    	$permission = 'v';
		    	} elseif($rule['view'] == 0 && $rule['edit'] == 0){
			    	$permission = '';
		    	}
			}
		}
		if(empty($user_rules)){
		    $rules = $this->db->get_where('permissions_user_rules', array(
		    	'user_id' => $user_id,
		    	'type' => $type,
		    	'type_id' => $type_id
		    ))->result_array();
		    if(!empty($rules)){
			    $rule = $rules[0];
			    if($rule){
			    	if($rule['view'] == 1 && $rule['edit'] == 1){
				    	$permission = 've';
			    	} elseif($rule['view'] == 1){
				    	$permission = 'v';
			    	} elseif($rule['view'] == 0){
				    	$permission = false;
			    	}
			    }
			}
		}

		// If there is another project type id in this user, then edit_all_projects will be changed to 0.

		$edit_all_project_user_rule = $this->db->get_where('permissions_user_rules', array(
	    	'user_id' => $user_id,
	    	'type' => 'edit_all_projects',
	    	'view' => 1,
	    	'edit' => 1
	    ))->result_array();

	    if (count($edit_all_project_user_rule)) {
	    	$this->db->select('*');
			$this->db->from('permissions_user_rules');
			$this->db->where('user_id !=' , $user_id);
			$query = $this->db->get();
			$other_user_rules = $query->result();

			if (count($other_user_rules)) {
				$update_data = array();
		        $update_data['view'] = 0;
		        $update_data['edit'] = 0;

		        $where_array = array('user_id' => $user_id, 'type' => 'edit_all_projects');
				$this->db->where($where_array);
		        $this->db->update('permissions_user_rules', $update_data);
			}

	    }

		// If type of check is project, check whether user or group can edit all projects

		if($type == 'project' && $permission != 'v' && $permission != 've'){
			$permissionAllProjects = $this->check_permission_user('edit_all_projects', '', $username_or_userid);
			if($permissionAllProjects == 've'){
				$permission = 've';
			}
			if($permission != 've' && $permissionAllProjects == 'v'){
				$permission = 'v';
			}
		}

	    return $permission;
    }

    function delete_permissions($type, $id){
	    if($type == 'group'){
		    $this->db->where('group_id', $id);
		    $this->db->delete('permissions_group_rules');
	    }
	    if($type == 'user'){
		    $this->db->where('user_id', $id);
		    $this->db->delete('permissions_user_rules');
	    }
    }

    function delete_connections($type, $id){
	    if($type == 'user'){
		    $this->db->where('user_id', $id);
		    $this->db->delete('permissions_usergroups');
	    }
    }

    function saveFormPermission($data, $assigned_by){
		$this->load->model('Projects_model');
		$project = $this->db->get_where('projects',['id'=>$data['type_id']])->row_array();
		$form_per = $this->db->get_where('permissions_user_forms',['project_id'=>$data['type_id'],'user_id'=>$data['user_id']]);
		$permission = '';
		if($data['view']==1)
			$permission = 'v';
		if($data['edit']==1)
			$permission = 've';

		if($form_per->num_rows()==0){
			if($permission!=''){
				$field_codes = array('project_title', 'project_desc', 'erp_system', 'store_url', 'contact_person');
				foreach ($field_codes as $key1 => $value1) {
					$insert_data = array(
							'user_id'		=> $data['user_id'],
							'project_id'	=> $data['type_id'],
							'field_code'	=> $value1,
							'permission'	=> $permission,
							'assigned_by'=> $assigned_by,
						);
						$this->db->insert('permissions_user_forms', $insert_data);
				}

				$project_settings1 = $this->db->get('project_from_settings')->result_array();
				foreach ($project_settings1 as $field) {
					$continue = false;
					$field['depends_on'] 	= json_decode($field['depends_on'], true);
					$field['values'] 			= json_decode($field['values'], true);
					$field['fields'] 			= json_decode($field['fields'], true);
					if(isset($field['depends_on'])){
						foreach ($field['depends_on'] as $dep_key => $dep_value) {
							$value1 = $this->Projects_model->getValue($dep_key, $data['type_id']);
							if($dep_key=='erp_system'){
								$value1 = $project['erp_system'];
							}
							if($dep_key=='connection_type'){
								$value1 = $project['connection_type'];
							}
							$value1_arr = explode(',', $dep_value);
							foreach ($value1_arr as $arr_key => $arr_value) {
								if($arr_value!=$value1){
									$continue = true;
								} else{
									$continue = false;
									break;
								}
							}
						}
					}
					if($continue)
						continue;
					$insert_data = array(
							'user_id'		=> $data['user_id'],
							'project_id'	=> $data['type_id'],
							'field_code'	=> $field['code'],
							'permission'	=> $permission,
							'assigned_by'=> $assigned_by,
						);
						$this->db->insert('permissions_user_forms', $insert_data);
				}
			}
		} else{
				if($permission!=''){
					$field_codes = $form_per->result_array();
					foreach ($field_codes as $key2 => $value2) {
						$insert_data = array(
								'user_id'		  => $data['user_id'],
								'project_id'	=> $data['type_id'],
								'field_code'	=> $value2['field_code'],
								'permission'	=> $permission,
								'assigned_by' => $assigned_by,
							);
							$this->db->where('id', $value2['id']);
							$this->db->update('permissions_user_forms', $insert_data);
					}
				} else{
					$this->db->where('user_id',  $data['user_id']);
					$this->db->where('project_id', $data['type_id']);
					$this->db->delete('permissions_user_forms');
				}
		}
	}

	// Get all partner ids from permission_users table
    function getPartnerIds(){
    	
    	$partners = array();
    	$partner_ids_array = array();
    	$partner_ids_str = '';

    	$partners = $this->db->get_where('permissions_users', array(
	    	'role' => 'partner'
	    ))->result_array();

	    foreach ($partners as $key => $partner) {
	    	$partner_ids_array[] = $partner['user_id'] . '-' . $partner['user_name'];
	    }

    	$partner_ids_str = implode(",",$partner_ids_array);

	    return $partner_ids_str;
    }

}
