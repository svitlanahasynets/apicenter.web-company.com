<?php $tr_count = 0; ?>
<?php foreach($projects as $project): ?>
	<?php
		$permission = $this->Permissions_model->check_permission_user('project', $project['id']);
		if($permission != 've' && $permission != 'v'){
			continue;
		}
		
		$tr_count++;
		if($tr_count % 2){
			$tr_class = 'odd';
		} else {
			$tr_class = 'even';
		}
	?>
		<tr style="cursor: pointer;" class="<?php echo $tr_class;?>" onclick="App.navigateTo('<?php echo site_url('/manualsync/view/id/'.$project['id']);?>');">
			<td data-column="project_id"><?php echo $project['id'];?></td>
			<td title="<?php echo get_erp_system_label($project['erp_system']); ?>" data-column="erp_system"><?php echo get_erp_system_label($project['erp_system']);?></td>
			<td title="<?php echo $controller->webshopaname($project['id'],'webs_shop'); ?>" data-column="title"><?php echo $controller->webshopaname($project['id'],'webs_shop'); ?></td>
			<td title="<?php echo $controller->webshopaname($project['id'],'market'); ?>" data-column="title"><?php echo $controller->webshopaname($project['id'],'market'); ?></td>
			<td title="<?php echo $project['title']; ?>" data-column="title"><?php echo $project['title'];?></td>
			<td title="<?php echo $project['store_url']; ?>" data-column="store_url"><?php echo $project['store_url'];?></td>
			<td title="<?php echo $controller->connectionStatus($project['id']); ?>" data-column="title"><?php echo $controller->connectionStatus($project['id']); ?></td>
		</tr>
<?php endforeach; ?>