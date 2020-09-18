<?php

if(!function_exists('version_update_database')){
	function version_update_database($currentVersion, $newVersion){
		get_instance()->load->helper('directory');
		
		$files = directory_map('application/installation/', 1);
		$finalFiles = array();
		foreach($files as $file){
			$fileName = str_replace('.php', '', $file);
			$fileVersion = explode('.', $fileName);
			$finalFileVersion = array();
			foreach($fileVersion as $index => $subVersion){
				$finalFileVersion[$index] = str_pad($subVersion, 2, '0', STR_PAD_LEFT);
			}
			$finalFileVersion = implode('.', $finalFileVersion);
			$finalFiles[] = $finalFileVersion.'.php';
		}
		sort($finalFiles);
		foreach($finalFiles as $file){
			$fileVersion = basename($file, '.php');
			$fileVersion = explode('.', $fileVersion);
			
			if(
				(
					($currentVersion[0] < $fileVersion[0])
					|| ($currentVersion[0] <= $fileVersion[0] && $currentVersion[1] < $fileVersion[1])
					|| ($currentVersion[0] <= $fileVersion[0] && $currentVersion[1] <= $fileVersion[1] && $currentVersion[2] < $fileVersion[2])
				)
				&&
				(
					($newVersion[0] > $fileVersion[0])
					|| ($newVersion[0] >= $fileVersion[0] && $newVersion[1] > $fileVersion[1])
					|| ($newVersion[0] >= $fileVersion[0] && $newVersion[1] >= $fileVersion[1] && $newVersion[2] >= $fileVersion[2])
				)
			){
				$fileName = str_replace('.php', '', $file);
				$subFileVersion = explode('.', $fileName);
				$subFinalFileVersion = array();
				foreach($subFileVersion as $index => $subVersion){
					if($subVersion == '00'){
						$subFinalFileVersion[$index] = '0';
					} else {
						$subFinalFileVersion[$index] = ltrim($subVersion, '0');
					}
				}
				$subFinalFileVersion = implode('.', $subFinalFileVersion);
				$file = $subFinalFileVersion.'.php';
					
				// Create maintenance file
				get_instance()->load->helper('file');
				if(!write_file('data/maintenance.flag', 'flagged')){
					return false;
				}
				
				$fileLocation = FCPATH.'application/installation/'.$file;
				if(!@require_once($fileLocation)){
					return false;
				}
				
				// Remove maintenance file
				unlink('data/maintenance.flag');
			}
		}
		return true;
	}
}

if(!function_exists('add_table_field_before_create')){
	function add_table_field_before_create($fieldData){
		if(!isset(get_instance()->dbforge)){
			get_instance()->load->dbforge();
		}
		get_instance()->dbforge->add_field($fieldData);
	}
}

if(!function_exists('add_table_key_before_create')){
	function add_table_key_before_create($key, $isPrimary = true){
		if(!isset(get_instance()->dbforge)){
			get_instance()->load->dbforge();
		}
		get_instance()->dbforge->add_key($key, $isPrimary);
	}
}

if(!function_exists('create_table')){
	function create_table($dbName, $ifNotExists = true){
		if(!isset(get_instance()->dbforge)){
			get_instance()->load->dbforge();
		}
		if (!get_instance()->db->table_exists($dbName) && get_instance()->dbforge->create_table($dbName, $ifNotExists))
		{
			return true;
		}
		return false;
	}
}

if(!function_exists('add_table_column')){
	function add_table_column($table, $field){
		if(!isset(get_instance()->dbforge)){
			get_instance()->load->dbforge();
		}
		get_instance()->dbforge->add_column($table, $field);
	}
}

if(!function_exists('add_table_key')){
	function add_table_key($table, $field, $secondTable, $secondField, $onDeleteAction = 'SET NULL', $onUpdateAction = 'SET NULL'){
		$onDeleteAction = 'ON DELETE '.$onDeleteAction;
		$onUpdateAction = 'ON UPDATE '.$onUpdateAction;
		$query = "ALTER TABLE $table ADD FOREIGN KEY (`$field`) REFERENCES $secondTable(`$secondField`) $onDeleteAction $onUpdateAction";
		get_instance()->db->query($query);
	}
}

if(!function_exists('add_table_index')){
	function add_table_index($table, $field){
		$query = "ALTER TABLE $table ADD INDEX (`$field`)";
		get_instance()->db->query($query);
	}
}