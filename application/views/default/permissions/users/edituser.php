<h1 class="content-title"><?php echo translate('Edit user'); ?> "<?php echo $user['user_name'];?>"</h1>
<input type="hidden" id="enable-exit-page-message" value="true" />
<?php
$form_attributes = array('class' => 'permissions_edituser_form');

// Open form tag
echo form_open_multipart('permissions/saveuseraction', $form_attributes);

// Define and display form fields
$user_name = array(
	'name' => 'user_name',
	'id' => 'user_name',
	'class' => 'required',
	'readonly' => 'readonly',
	'value' => $user['user_name']
);
$firstname = array(
	'name' => 'firstname',
	'id' => 'firstname',
	'class' => 'required',
	'value' => $user['firstname']
);
$lastname = array(
	'name' => 'lastname',
	'id' => 'lastname',
	'class' => 'required',
	'value' => $user['lastname']
);
$password = array(
	'name' => 'password',
	'id' => 'password',
	'class' => 'required',
	'value' => ''
);
$user_phone = array(
	'name' => 'user_phone',
	'id' => 'user_phone',
	'class' => '',
	'value' => $user['user_phone']
);
$user_email = array(
	'name' => 'user_email',
	'id' => 'user_email',
	'class' => 'required',
	'value' => $user['user_email']
);
?>

<div class="form-fields">
	<?php echo form_hidden('user_id', $user['user_id']);?>
	<div class="form-field">
		<label for="user_name"><span class="label"><?php echo translate('Username');?></span></label>
		<span><?php echo form_input($user_name);?></span>
	</div>
	<div class="form-field">
		<label for="firstname"><span class="label"><?php echo translate('First name');?></span><span class="required">*</span></label>
		<span><?php echo form_input($firstname);?></span>
	</div>
	<div class="form-field">
		<label for="lastname"><span class="label"><?php echo translate('Last name');?></span><span class="required">*</span></label>
		<span><?php echo form_input($lastname);?></span>
	</div>
	<div class="form-field">
		<label for="password"><span class="label"><?php echo translate('Password');?></span><span class="required">*</span></label>
		<span><?php echo form_password($password);?><span class="input-description"><?php echo translate('Enter a new password if you want to change it'); ?></span></span>
	</div>
	<div class="form-field">
		<label for="user_email"><span class="label"><?php echo translate('E-mail address');?></span><span class="required">*</span></label>
		<span><?php echo form_input($user_email);?></span>
	</div>
	<div class="form-field">
		<label for="user_phone"><span class="label"><?php echo translate('Phone number');?></span></label>
		<span><?php echo form_input($user_phone);?></span>
	</div>
	<div class="form-field">
		<label for="permissions"><span class="label"><?php echo translate('User specific permissions');?></span></label>
		<div class="permission-control-box">
			<table id="permissions-table" class="data-table">
				<thead>
					<tr>
						<th><?php echo translate('Name'); ?></th>
						<th><?php echo translate('Type'); ?></th>
						<th><?php echo translate('View'); ?></th>
						<th><?php echo translate('Edit'); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
					</tr>
				</tbody>
			</table>
			<script type="text/javascript">
				var permissionData = [];
				
				var projects_array = [];
				<?php if(in_array("projects", $this->config->item('enabled_modules'))): ?>
				<?php $permission = $this->Permissions_model->check_permission_user('access_projects_section', 0, $user['user_id']); ?>
				projects_array.push({ title: '<?php echo cut(translate('Access projects section'), 80); ?>', key: 'projects-access_projects_section', id: '0', type: 'access_projects_section', typeTitle: '<?php echo translate("Projects"); ?>', permission: '<?php echo $permission; ?>'});
				<?php $permission = $this->Permissions_model->check_permission_user('create_project', 0, $user['user_id']); ?>
				projects_array.push({ title: '<?php echo cut(translate('Create project'), 80); ?>', key: 'projects-create_project', id: '0', type: 'create_project', typeTitle: '<?php echo translate("Projects"); ?>', permission: '<?php echo $permission; ?>'});
				<?php $permission = $this->Permissions_model->check_permission_user('edit_all_projects', 0, $user['user_id']); ?>
				projects_array.push({ title: '<?php echo cut(translate('Edit all projects'), 80); ?>', key: 'projects-edit_all_projects', id: '0', type: 'edit_all_projects', typeTitle: '<?php echo translate("Projects"); ?>', permission: '<?php echo $permission; ?>'});
				<?php $permission = $this->Permissions_model->check_permission_user('all_projects_actions', 0, $user['user_id']); ?>
				permissionData.push({ title: '<?php echo cut(translate('Projects'), 80); ?>', key: 'all_projects_actions-0', id: '0', type: 'all_projects_actions', typeTitle: '<?php echo translate("Projects"); ?>', permission: '<?php echo $permission; ?>', children: projects_array});
				<?php endif; ?>
				
				
				var permissions_array = [];
				<?php $permission = $this->Permissions_model->check_permission_user('access_permissions_section', 0, $user['user_id']); ?>
				permissions_array.push({ title: '<?php echo cut(translate('Access permissions section'), 80); ?>', key: 'permissions-access_permissions_section', id: '0', type: 'access_permissions_section', typeTitle: '<?php echo translate("Permissions"); ?>', permission: '<?php echo $permission; ?>'});
				<?php $permission = $this->Permissions_model->check_permission_user('list_users', 0, $user['user_id']); ?>
				permissions_array.push({ title: '<?php echo cut(translate('List users'), 80); ?>', key: 'permissions-list_users', id: '0', type: 'list_users', typeTitle: '<?php echo translate("Users"); ?>', permission: '<?php echo $permission; ?>'});
				<?php $permission = $this->Permissions_model->check_permission_user('create_user', 0, $user['user_id']); ?>
				permissions_array.push({ title: '<?php echo cut(translate('Create user'), 80); ?>', key: 'permissions-create_user', id: '0', type: 'create_user', typeTitle: '<?php echo translate("Users"); ?>', permission: '<?php echo $permission; ?>'});
				<?php $permission = $this->Permissions_model->check_permission_user('create_user_own_company', 0, $user['user_id']); ?>
				<?php $permission = $this->Permissions_model->check_permission_user('manage_all_permissions', 0, $user['user_id']); ?>
// 				permissions_array.push({ title: '<?php echo cut(translate('Manage permissions (master account)'), 80); ?>', key: 'permissions-manage_all_permissions', id: '0', type: 'manage_all_permissions', typeTitle: '<?php echo translate("Permissions"); ?>', permission: '<?php echo $permission; ?>'});
				<?php $permission = $this->Permissions_model->check_permission_user('all_permission_actions', 0, $user['user_id']); ?>
				permissionData.push({ title: '<?php echo cut(translate('Permissions'), 80); ?>', key: 'all_permission_actions-0', id: '0', type: 'all_permission_actions', typeTitle: '<?php echo translate("Permissions"); ?>', permission: '<?php echo $permission; ?>', children: permissions_array});
				
				
				var settings_array = [];
				<?php if(in_array("settings", $this->config->item('enabled_modules'))): ?>
				<?php $permission = $this->Permissions_model->check_permission_user('access_settings_section', 0, $user['user_id']); ?>
				settings_array.push({ title: '<?php echo cut(translate('Access settings section'), 80); ?>', key: 'settings-access_settings_section', id: '0', type: 'access_settings_section', typeTitle: '<?php echo translate("Settings"); ?>', permission: '<?php echo $permission; ?>'});
				<?php $permission = $this->Permissions_model->check_permission_user('all_settings_actions', 0, $user['user_id']); ?>
				permissionData.push({ title: '<?php echo cut(translate('Settings'), 80); ?>', key: 'all_settings_actions-0', id: '0', type: 'all_settings_actions', typeTitle: '<?php echo translate("Settings"); ?>', permission: '<?php echo $permission; ?>', children: settings_array});
				<?php endif; ?>
				
				
				<?php foreach($projects as $project): ?>
					var project_children = [];
					<?php $permission = $this->Permissions_model->check_permission_user('project', $project['id'], $user['user_id']); ?>
					permissionData.push({ title: '<?php echo cut(str_replace("'", "\'", $project["title"]), 80); ?>', key: 'project-<?php echo $project['id']; ?>', id: '<?php echo $project['id']; ?>', type: 'project', typeTitle: '<?php echo translate("Project"); ?>', permission: '<?php echo $permission; ?>', children: project_children, folder: true });
				<?php endforeach; ?>
				
				
			</script>
		</div>
	</div>
	<div class="form-field">
		<span>
			<?php
			// Display submit button
			echo form_submit('submit', translate('Save'));
			?>
		</span>
	</div>
	<div class="form-field">
		<span class="delete"><a href="<?php echo site_url('/permissions/deleteuser/id/'.$user['user_id']);?>" onclick="return confirm('<?php echo translate('Are you sure you want to delete this user?');?>')"><?php echo translate('Delete user');?></a></span>
	</div>
</div>

<?php
// Close form tag
echo form_close();