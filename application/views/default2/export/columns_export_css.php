<?php
$columnPreferences = get_user_preference($type);
?>
<?php foreach($columnPreferences as $columnName => $enabled): ?>
	<?php if($enabled == 'false' || !$enabled): ?>
		.data-table th[column=<?php echo $columnName; ?>],
		.data-table td[column=<?php echo $columnName; ?>] { display: none; }
	<?php endif; ?>
<?php endforeach; ?>