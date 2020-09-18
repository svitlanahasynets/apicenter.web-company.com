<?php
// CREATE PERMISSIONS_USER_PREFERENCES TABLE
$field = array(
	'id' => array(
		'type' => 'INT',
		'constraint' => 11,
		'auto_increment' => true,
	)
);
add_table_field_before_create($field);
add_table_key_before_create('id');
create_table('permissions_user_preferences');

$field = array(
	'user_id' => array('type' => 'INT(11)')
);
add_table_column('permissions_user_preferences', $field);

$field = array(
	'preference' => array('type' => 'VARCHAR(255)')
);
add_table_column('permissions_user_preferences', $field);

$field = array(
	'value' => array('type' => 'TEXT')
);
add_table_column('permissions_user_preferences', $field);


// ADD FOREIGN KEY TO USER ID
add_table_key('permissions_user_preferences', 'user_id', 'permissions_users', 'user_id', 'CASCADE', 'CASCADE');