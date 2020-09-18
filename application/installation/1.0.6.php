<?php
// Add profile picture
$field = array(
	'profile_picture' => array(
		'type' => 'VARCHAR',
		'constraint' => 255
	)
);
add_table_column('permissions_users', $field);