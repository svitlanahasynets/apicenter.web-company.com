<?php
// Add ERP system type to project
$field = array(
	'erp_system' => array(
		'type' => 'VARCHAR',
		'constraint' => 255
	)
);
add_table_column('projects', $field);