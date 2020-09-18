<?php
class Settings_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }
    
    public $file_name;

	function save($sectionCode = null){
		$this->config->load('settings', true);
		$settings = $this->config->item('settings');
		$sections = $settings['sections'];
		$fieldSections = $settings['fields'];
		if($sectionCode != null){
			$fieldSections = array($settings['fields'][$sectionCode]);
		}

		foreach($fieldSections as $fields){
			foreach($fields as $field){
				if($field['type'] == 'file'){
					$checkFileField = $field['section'].'-'.$field['code'];
					if(array_key_exists($checkFileField, $_FILES) && $_FILES[$checkFileField]['name'] != ''){
						$extensions = explode('|', $field['extensions']);
						$fileName = $this->saveFile($checkFileField, $extensions);
						if($fileName != ''){
							$this->saveValue($field['section'], $field['code'], $fileName);
						}
					}
				} else {
					$value = $this->input->post($field['section'].'-'.$field['code'], true);
					$this->saveValue($field['section'], $field['code'], $value);
				}
			}
		}
	}
	
	function saveValue($section, $field, $value){
		// Check whether field already exists
		$this->db->where('section', $section);
		$this->db->where('field', $field);
		$this->db->from('settings');
		if($this->db->count_all_results() > 0){
			$this->db->where('section', $section);
			$this->db->where('field', $field);
			$this->db->update('settings', array('value' => $value));
		} else {
			$this->db->set('section', $section);
			$this->db->set('field', $field);
			$this->db->set('value', $value);
			$this->db->insert('settings');
		}
		return true;
	}
	
	function getValue($section, $fieldCode){
		$this->db->where('section', $section);
		$this->db->where('field', $fieldCode);
		$value = $this->db->get('settings')->row_array();
		$value = $value['value'];
		return $value;
	}
	
	function saveFile($fieldName, $extensions){
		$this->path = '/settings_files/';
		$full_path = DATA_DIRECTORY.$this->path;
		
		$extension = strtolower( pathinfo( basename( $_FILES[$fieldName]['name'] ), PATHINFO_EXTENSION ) );
		if(!in_array($extension, $extensions)){
			return false;
		}
		
		$config['upload_path'] = $full_path;
		$config['allowed_types'] = $extensions;
		$config['encrypt_name'] = false;
		$config['overwrite'] = true;
		$config['remove_spaces'] = true;
		$config['xss_clean'] = true;
		$config['max_size']	= MAX_UPLOAD_FILE_SIZE;
		$config['file_name'] = $fieldName.'.'.$extension;

		$this->load->library('upload', $config);
		$this->upload->initialize($config);

		$this->upload->do_upload($fieldName);
		$data = $this->upload->data();
		$path = $this->path;
		
		return $data['file_name'];
	}
	
	function getFileType($fileName){
		$extension = strtolower( pathinfo( basename( $fileName ), PATHINFO_EXTENSION ) );
	    $mime_types = array(
	        // images
	        'png' => 'image/png',
	        'jpe' => 'image/jpeg',
	        'jpeg' => 'image/jpeg',
	        'jpg' => 'image/jpeg',
	        'gif' => 'image/gif',
	    );
	    if( !isset( $mime_types[$extension] ) )
	    {
			return 'file';
	    } else {
			return 'image';
	    }
	}
	
	function getImageUrl($fileName){
		$extension = strtolower( pathinfo( basename( $fileName ), PATHINFO_EXTENSION ) );
	    $mime_types = array(
	        // images
	        'png' => 'image/png',
	        'jpe' => 'image/jpeg',
	        'jpeg' => 'image/jpeg',
	        'jpg' => 'image/jpeg',
	        'gif' => 'image/gif',
	    );
	    if( !isset( $mime_types[$extension] ) )
	    {
			return false;
	    }
	    
		$fullImageLocation = '/settings_files/'.$fileName;
		$file_path = urlencode(base64_encode($fullImageLocation));
		$url = site_url('/settings/viewimagefile/file/'.$file_path);
		return $url;
	}
	
	function getDownloadLink($fileName){
		$fullImageLocation = '/settings_files/'.$fileName;
		$file_path = urlencode(base64_encode($fullImageLocation));
		$url = site_url('/settings/downloadfile/file/'.$file_path);
		return $url;
	}
	
	function getFilePath($fileName){
		$location = DATA_DIRECTORY.'/settings_files/'.$fileName;
		return $location;
	}
	
}