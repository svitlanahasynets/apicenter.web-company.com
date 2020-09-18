<ul class="menu">
	<?php
	$menu_items = array();
	if(in_array("projects", $this->config->item('enabled_modules'))
		&& strpos($this->Permissions_model->check_permission_user('access_projects_section', '', $this->session->userdata('username')), 'v') > -1){
		$menu_items[] = array("code" => "projects", "text" => translate('Projects'));
	}
	
	
	// Management
	$managerItems = array();
	if(in_array("permissions", $this->config->item('enabled_modules'))
		&& strpos($this->Permissions_model->check_permission_user('access_permissions_section', '', $this->session->userdata('username')), 'v') > -1){
		$managerItems[] = array("code" => "permissions", "text" => translate('Permissions'));
	}
	if(in_array("settings", $this->config->item('enabled_modules'))
		&& strpos($this->Permissions_model->check_permission_user('access_settings_section', '', $this->session->userdata('username')), 'v') > -1){
		$managerItems[] = array("code" => "settings", "text" => translate('Settings'));
	}
	if(!empty($managerItems)){
		$menu_items[] = array(
			"code" => "manager",
			"text" => translate('Management'),
			"children" => $managerItems
		);
	}
		
	foreach($menu_items as $item){
		$permission = $this->Permissions_model->check_permission_user('access_'.$item['code'].'_section', 0);
		$permission = 've';
		if($permission == 've' || $item['code'] == 'dashboard'){
			// Get children
			$childrenHtml = '';
			$isActive = false;
			if(isset($item['children']) && !empty($item['children'])){
				$childrenHtml .= '<ul class="menu-children">';
				foreach($item['children'] as $child){
					if(isset($active_menu_item) && $child['code'] == $active_menu_item){
						$isActive = true;
						$childrenHtml .= '<li class="active"><a href="'.site_url('/'.$child['code']).'">'.$child['text'].'</a></li>';
					} else {
						$childrenHtml .= '<li><a href="'.site_url('/'.$child['code']).'">'.$child['text'].'</a></li>';
					}
				}
				$childrenHtml .= '</ul>';
			}
			
			if($childrenHtml != ''){
				$itemUrl = '#';
			} else {
				$itemUrl = site_url('/'.$item['code']);
			}
			if((isset($active_menu_item) && $item['code'] == $active_menu_item) || $isActive){
				echo '<li class="active"><a href="'.$itemUrl.'">'.$item['text'].'</a>'.$childrenHtml.'</li>';
			} else {
				echo '<li><a href="'.$itemUrl.'">'.$item['text'].'</a>'.$childrenHtml.'</li>';
			}
		}
	}
	?>
</ul>