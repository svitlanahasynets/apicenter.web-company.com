<h1 class="content-title"><?php echo translate('User'); ?> "<?php echo $user['user_name'];?>"</h1>

<div class="project-data">
	<table class="data-table property-value no-edit">
		<tr class="odd">
			<td><?php echo translate('User ID');?></td>
			<td><?php echo $user['user_id'];?></td>
		</tr>
		<tr class="even">
			<td><?php echo translate('Username');?></td>
			<td><?php echo $user['user_name'];?></td>
		</tr>
		<tr class="odd">
			<td><?php echo translate('First name');?></td>
			<td><?php echo $user['firstname'];?></td>
		</tr>
		<tr class="even">
			<td><?php echo translate('Last name');?></td>
			<td><?php echo $user['lastname'];?></td>
		</tr>
		<tr class="even">
			<td><?php echo translate('E-mail address');?></td>
			<td><?php echo $user['user_email'];?></td>
		</tr>
		<tr class="odd">
			<td><?php echo translate('Phone number');?></td>
			<td><?php echo $user['user_phone'];?></td>
		</tr>
	</table>

	<div class="actions">
		<a href="<?php echo site_url('/permissions/edituser/id/'.$user['user_id']);?>" class="action"><?php echo translate('Edit user');?></a>
	</div>
</div>