<h4><?php echo translate('Permissions'); ?></h4>
<ul>
	<?php if(strpos($this->Permissions_model->check_permission_user('list_users', '', $this->session->userdata('username')), 'v') > -1
		|| $this->Permissions_model->check_permission_user('create_user', '', $this->session->userdata('username')) == 've'): ?>
		<li class="sidebar-group-header"><?php echo translate('Users'); ?></li>
	<?php endif; ?>
	<?php if(strpos($this->Permissions_model->check_permission_user('list_users', '', $this->session->userdata('username')), 'v') > -1): ?>
		<li><a href="<?php echo site_url('permissions/index');?>"><span class="icon fa fa-user"></span><?php echo translate('All users');?></a></li>
	<?php endif; ?>
	<?php if($this->Permissions_model->check_permission_user('create_user', '', $this->session->userdata('username')) == 've'): ?>
		<li><a href="<?php echo site_url('permissions/createuser');?>"><span class="icon fa fa-user-plus"></span><?php echo translate('Create user');?></a></li>
	<?php endif; ?>
</ul>