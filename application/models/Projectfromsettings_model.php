<?php
class  Projectfromsettings_model extends CI_Model {

    function __construct(){
        parent::__construct();
    }

    public function getStaticFieldAssignedPermission($user_id, $project_id){
        // $permission  = $this->db->get_where('permissions_user_forms',['user_id'=>$user_id, 'project_id'=>$project_id, 'field_code'=>$fieldCode])->row_array() ? $this->db->get_where('permissions_user_forms',['user_id'=>$user_id, 'project_id'=>$project_id, 'field_code'=>$fieldCode])->row_array()['permission']:'';
        $field_codes = array('project_title', 'project_desc', 'erp_system', 'store_url', 'contact_person');
        $permission  = $this->db->select('permission')->where(['user_id'=>$user_id, 'project_id'=>$project_id])->where_in('field_code', $field_codes)->get('permissions_user_forms')->result_array() ? $this->db->select('*')->where(['user_id'=>$user_id, 'project_id'=>$project_id])->where_in('field_code', $field_codes)->get('permissions_user_forms')->result_array():'';
        $return_permission = array();
        $return_permission['project_title']   = '';
        $return_permission['project_desc']    = '';
        $return_permission['erp_system']      = '';
        $return_permission['store_url']       = '';
        $return_permission['contact_person']  = '';
        if($permission!=''){
          foreach ($permission as $key => $value) {
            $return_permission[$value['field_code']] = $value['permission'];
          }
        }
        return $return_permission;
    }

    public function getStaticFieldPermission($user_id, $project_id){
        $permission  = $this->db->get_where('permissions_user_forms',['user_id'=>$user_id, 'project_id'=>$project_id, 'field_code'=>'project_title'])->row_array() ? $this->db->get_where('permissions_user_forms',['user_id'=>$user_id, 'project_id'=>$project_id, 'field_code'=>'project_title'])->row_array()['permission']:'';
    		$project_title_v 	= $project_title_ve = $project_title_cve = FALSE;
    		if($permission=='v'){
    			$project_title_v 	= TRUE;
    		} else if($permission=='ve'){
    			$project_title_v 	= $project_title_ve = TRUE;
    		} else if($permission=='cve'){
    			$project_title_v 	= $project_title_ve = $project_title_cve = TRUE;
    		}

    		$permission  = $this->db->get_where('permissions_user_forms',['user_id'=>$user_id, 'project_id'=>$project_id, 'field_code'=>'project_desc'])->row_array() ? $this->db->get_where('permissions_user_forms',['user_id'=>$user_id, 'project_id'=>$project_id, 'field_code'=>'project_desc'])->row_array()['permission']:'';
    		$project_desc_v 	= $project_desc_ve = $project_desc_cve = FALSE;
    		if($permission=='v'){
    			$project_desc_v 	= TRUE;
    		} else if($permission=='ve'){
    			$project_desc_v 	= $project_desc_ve = TRUE;
    		} else if($permission=='cve'){
    			$project_desc_v 	= $project_desc_ve = $project_desc_cve = TRUE;
    		}

    		$permission  = $this->db->get_where('permissions_user_forms',['user_id'=>$user_id, 'project_id'=>$project_id, 'field_code'=>'erp_system'])->row_array() ? $this->db->get_where('permissions_user_forms',['user_id'=>$user_id, 'project_id'=>$project_id, 'field_code'=>'erp_system'])->row_array()['permission']:'';
    		$erp_system_v 	= $erp_system_ve = $erp_system_cve = FALSE;
    		if($permission=='v'){
    			$erp_system_v 	= TRUE;
    		} else if($permission=='ve'){
    			$erp_system_v 	= $erp_system_ve = TRUE;
    		} else if($permission=='cve'){
    			$erp_system_v 	= $erp_system_ve = $erp_system_cve = TRUE;
    		}
    		$permission  = $this->db->get_where('permissions_user_forms',['user_id'=>$user_id, 'project_id'=>$project_id, 'field_code'=>'store_url'])->row_array() ? $this->db->get_where('permissions_user_forms',['user_id'=>$user_id, 'project_id'=>$project_id, 'field_code'=>'store_url'])->row_array()['permission']:'';
    		$store_url_v 	= $store_url_ve = $store_url_cve = FALSE;
    		if($permission=='v'){
    			$store_url_v 	= TRUE;
    		} else if($permission=='ve'){
    			$store_url_v 	= $store_url_ve = TRUE;
    		} else if($permission=='cve'){
    			$store_url_v 	= $store_url_ve = $store_url_cve = TRUE;
    		}
    		$permission  = $this->db->get_where('permissions_user_forms',['user_id'=>$user_id, 'project_id'=>$project_id, 'field_code'=>'contact_person'])->row_array() ? $this->db->get_where('permissions_user_forms',['user_id'=>$user_id, 'project_id'=>$project_id, 'field_code'=>'contact_person'])->row_array()['permission']:'';
    		$contact_person_v 	= $contact_person_ve = $contact_person_cve = FALSE;
    		if($permission=='v'){
    			$contact_person_v 	= TRUE;
    		} else if($permission=='ve'){
    			$contact_person_v 	= $contact_person_ve = TRUE;
    		} else if($permission=='cve'){
    			$contact_person_v 	= $contact_person_ve = $contact_person_cve = TRUE;
    		}
    		$permission = array();
    		$permission['project_title_v'] 		= $project_title_v;
    		$permission['project_title_ve'] 	= $project_title_ve;
    		$permission['project_title_cve'] 	= $project_title_cve;
    		$permission['project_desc_v'] 		= $project_desc_v;
    		$permission['project_desc_ve'] 		= $project_desc_ve;
    		$permission['project_desc_cve'] 	= $project_desc_cve;
    		$permission['erp_system_v'] 			= $erp_system_v;
    		$permission['erp_system_ve'] 			= $erp_system_ve;
    		$permission['erp_system_cve'] 		= $erp_system_cve;
    		$permission['contact_person_v'] 	= $contact_person_v;
    		$permission['contact_person_ve'] 	= $contact_person_ve;
    		$permission['contact_person_cve'] = $contact_person_cve;
    		$permission['store_url_v'] 				= $store_url_v;
    		$permission['store_url_ve'] 			= $store_url_ve;
    		$permission['store_url_cve'] 			= $store_url_cve;
        return $permission;
    }

}
