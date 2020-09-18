<?php
// ADD FIRST AND LAST NAME FIELD TO USERS TABLE
$field = array(
	'firstname' => array(
		'type' => 'VARCHAR',
		'constraint' => 255,
	)
);
add_table_column('permissions_users', $field);

$field = array(
	'lastname' => array(
		'type' => 'VARCHAR',
		'constraint' => 255
	)
);
add_table_column('permissions_users', $field);