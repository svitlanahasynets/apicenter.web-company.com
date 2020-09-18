<?php
// CREATE SETTINGS TABLE
$field = array(
	'setting_id' => array(
		'type' => 'INT',
		'constraint' => 11,
		'auto_increment' => true,
	)
);
add_table_field_before_create($field);
add_table_key_before_create('setting_id');
create_table('settings');

$field = array(
	'section' => array(
		'type' => 'VARCHAR',
		'constraint' => 255,
		'auto_increment' => true,
	)
);
add_table_column('settings', $field);

$field = array(
	'field' => array(
		'type' => 'VARCHAR',
		'constraint' => 255,
		'auto_increment' => true,
	)
);
add_table_column('settings', $field);

$field = array(
	'value' => array(
		'type' => 'TEXT',
		'auto_increment' => true,
	)
);
add_table_column('settings', $field);