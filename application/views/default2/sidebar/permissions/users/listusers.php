<h4><?php echo translate('Permissions'); ?></h4>
<ul>
	<?php if($this->Permissions_model->check_permission_user('create_user', '', $this->session->userdata('username')) == 've'): ?>
		<li class="sidebar-group-header"><?php echo translate('Users'); ?></li>
		<li><a href="<?php echo site_url('permissions/createuser');?>"><span class="icon fa fa-user-plus"></span><?php echo translate('Create user');?></a></li>
	<?php endif; ?>
</ul>