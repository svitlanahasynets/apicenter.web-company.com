<?php
	$project_id = $project['id'];
	$project = $this->db->get_where('projects', array('id' => $project_id))->row_array();
	$erpSystem = $project['erp_system'];
?>

<div class="project-actions">
	
	<?php
	$permission = $this->Permissions_model->check_permission_user('project', $project['id']);
	if($permission == 've'):
	?>
		<a href="<?php echo site_url('projects/edit/id/'.$project_id);?>" class="action-icon">
			<span class="icon fa fa-wrench"></span>
			<span class="icon-text"><?php echo translate('Project settings'); ?></span>
		</a>
	<?php endif; ?>
	<?php if($erpSystem == 'visma'): ?>
		<a href="<?php echo site_url('visma/index/project/'.$project_id);?>?reauthorize=1" class="action-icon">
			<span class="icon fa fa-sign-in"></span>
			<span class="icon-text"><?php echo translate('Visma authorization'); ?></span>
		</a>
	<?php endif; ?>
</div>