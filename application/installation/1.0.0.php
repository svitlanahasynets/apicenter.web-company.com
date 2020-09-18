<?php
// CREATE PROJECTS TABLE
$field = array(
	'id' => array(
		'type' => 'INT',
		'constraint' => 11,
		'auto_increment' => true,
	)
);
add_table_field_before_create($field);
add_table_key_before_create('id');
create_table('projects');

$field = array(
	'title' => array('type' => 'VARCHAR(255)')
);
add_table_column('projects', $field);

$field = array(
	'description' => array('type' => 'TEXT')
);
add_table_column('projects', $field);

$field = array(
	'creation_date' => array('type' => 'VARCHAR(255)')
);
add_table_column('projects', $field);

$field = array(
	'contact_person' => array('type' => 'INT(11)')
);
add_table_column('projects', $field);

$field = array(
	'created_by' => array('type' => 'INT(11)')
);
add_table_column('projects', $field);





// CREATE FILES_FILES TABLE
$field = array(
	'id' => array(
		'type' => 'INT',
		'constraint' => 11,
		'auto_increment' => true,
	)
);
add_table_field_before_create($field);
add_table_key_before_create('id');
create_table('files_files');

$field = array(
	'file_id' => array('type' => 'VARCHAR(255)')
);
add_table_column('files_files', $field);

$field = array(
	'file_name' => array('type' => 'VARCHAR(255)')
);
add_table_column('files_files', $field);

$field = array(
	'file_path' => array('type' => 'VARCHAR(255)')
);
add_table_column('files_files', $field);

$field = array(
	'original_file_name' => array('type' => 'VARCHAR(255)')
);
add_table_column('files_files', $field);

$field = array(
	'file_description' => array('type' => 'VARCHAR(255)')
);
add_table_column('files_files', $field);

$field = array(
	'creator' => array('type' => 'INT(11)')
);
add_table_column('files_files', $field);

$field = array(
	'timestamp' => array('type' => 'VARCHAR(255)')
);
add_table_column('files_files', $field);

$field = array(
	'version' => array('type' => 'INT(11)')
);
add_table_column('files_files', $field);





// CREATE FILES_GROUPS TABLE
$field = array(
	'id' => array(
		'type' => 'INT',
		'constraint' => 11,
		'auto_increment' => true,
	)
);
add_table_field_before_create($field);
add_table_key_before_create('id');
create_table('files_groups');

$field = array(
	'type' => array('type' => 'VARCHAR(255)')
);
add_table_column('files_groups', $field);

$field = array(
	'type_id' => array('type' => 'INT(11)')
);
add_table_column('files_groups', $field);

$field = array(
	'parent_type' => array('type' => 'VARCHAR(255)')
);
add_table_column('files_groups', $field);

$field = array(
	'parent' => array('type' => 'INT(11)')
);
add_table_column('files_groups', $field);

$field = array(
	'name' => array('type' => 'VARCHAR(255)')
);
add_table_column('files_groups', $field);





// CREATE FILES_GROUP_FILES TABLE
$field = array(
	'id' => array(
		'type' => 'INT',
		'constraint' => 11,
		'auto_increment' => true,
	)
);
add_table_field_before_create($field);
add_table_key_before_create('id');
create_table('files_group_files');

$field = array(
	'file_id' => array('type' => 'VARCHAR(255)')
);
add_table_column('files_group_files', $field);

$field = array(
	'group_id' => array('type' => 'INT(11)')
);
add_table_column('files_group_files', $field);





// CREATE PERMISSIONS_USERS TABLE
$field = array(
	'user_id' => array(
		'type' => 'INT',
		'constraint' => 11,
		'auto_increment' => true,
	)
);
add_table_field_before_create($field);
add_table_key_before_create('user_id');
create_table('permissions_users');

$field = array(
	'user_name' => array('type' => 'VARCHAR(255)')
);
add_table_column('permissions_users', $field);

$field = array(
	'password' => array('type' => 'VARCHAR(255)')
);
add_table_column('permissions_users', $field);

$field = array(
	'salt' => array('type' => 'VARCHAR(255)')
);
add_table_column('permissions_users', $field);

$field = array(
	'user_function' => array('type' => 'VARCHAR(255)')
);
add_table_column('permissions_users', $field);

$field = array(
	'user_email' => array('type' => 'VARCHAR(255)')
);
add_table_column('permissions_users', $field);

$field = array(
	'user_phone' => array('type' => 'VARCHAR(255)')
);
add_table_column('permissions_users', $field);

$field = array(
	'company_id' => array('type' => 'INT(11)')
);
add_table_column('permissions_users', $field);

// ADD FOREIGN KEY TO CONTACT PERSON
add_table_key('projects', 'contact_person', 'permissions_users', 'user_id', 'CASCADE', 'CASCADE');





// CREATE PERMISSIONS_USER_RULES TABLE
$field = array(
	'id' => array(
		'type' => 'INT',
		'constraint' => 11,
		'auto_increment' => true,
	)
);
add_table_field_before_create($field);
add_table_key_before_create('id');
create_table('permissions_user_rules');

$field = array(
	'user_id' => array('type' => 'INT(11)')
);
add_table_column('permissions_user_rules', $field);

$field = array(
	'type' => array('type' => 'VARCHAR(255)')
);
add_table_column('permissions_user_rules', $field);

$field = array(
	'type_id' => array('type' => 'INT(11)')
);
add_table_column('permissions_user_rules', $field);

$field = array(
	'view' => array('type' => 'INT(1)')
);
add_table_column('permissions_user_rules', $field);

$field = array(
	'edit' => array('type' => 'INT(1)')
);
add_table_column('permissions_user_rules', $field);





// CREATE PROJECT_SETTINGS TABLE
$field = array(
	'id' => array(
		'type' => 'INT',
		'constraint' => 11,
		'auto_increment' => true,
	)
);
add_table_field_before_create($field);
add_table_key_before_create('id');
create_table('project_settings');

$field = array(
	'project_id' => array('type' => 'INT(11)')
);
add_table_column('project_settings', $field);

$field = array(
	'type' => array('type' => 'VARCHAR(255)')
);
add_table_column('project_settings', $field);

$field = array(
	'code' => array('type' => 'VARCHAR(255)')
);
add_table_column('project_settings', $field);

$field = array(
	'label' => array('type' => 'VARCHAR(255)')
);
add_table_column('project_settings', $field);

$field = array(
	'value' => array('type' => 'TEXT')
);
add_table_column('project_settings', $field);