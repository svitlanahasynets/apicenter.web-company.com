<?php
// Add connection parameters to projects table
$field = array(
	'store_url' => array(
		'type' => 'VARCHAR',
		'constraint' => 255
	)
);
add_table_column('projects', $field);

$field = array(
	'api_key' => array(
		'type' => 'VARCHAR',
		'constraint' => 255
	)
);
add_table_column('projects', $field);

$field = array(
	'plugin_key' => array(
		'type' => 'VARCHAR',
		'constraint' => 255
	)
);
add_table_column('projects', $field);

$field = array(
	'store_key' => array(
		'type' => 'VARCHAR',
		'constraint' => 255
	)
);
add_table_column('projects', $field);