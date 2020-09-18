<h1 class="content-title"><?php echo translate('All users'); ?></h1>
<table class="data-table">
	<thead>
		<tr>
			<th><?php echo translate('User ID');?></th>
			<th><?php echo translate('Username');?></th>
			<th><?php echo translate('First name');?></th>
			<th><?php echo translate('Last name');?></th>
			<th><?php echo translate('Job description');?></th>
			<th><?php echo translate('E-mail address');?></th>
			<th><?php echo translate('Phone number');?></th>
			<th><?php echo translate('Edit user');?></th>
			<th><?php echo translate('User information');?></th>
		</tr>
	</thead>
	<tbody>
		<?php $tr_count = 0; ?>
		<?php foreach($users as $user): ?>
			<?php
				$tr_count++;
				if($tr_count % 2){
					$tr_class = 'odd';
				} else {
					$tr_class = 'even';
				}
			?>
			<tr class="<?php echo $tr_class;?>" onclick="App.navigateTo('<?php echo site_url('/permissions/viewuser/id/'.$user['user_id']);?>');">
				<td><?php echo $user['user_id'];?></td>
				<td><?php echo $user['user_name'];?></td>
				<td><?php echo $user['firstname'];?></td>
				<td><?php echo $user['lastname'];?></td>
				<td><?php echo $user['user_function'];?></td>
				<td><?php echo $user['user_email'];?></td>
				<td><?php echo $user['user_phone'];?></td>
				<td><b><a href="<?php echo site_url('/permissions/edituser/id/'.$user['user_id']);?>"><?php echo translate('Edit');?></a></b></td>
				<td><b><a href="<?php echo site_url('/permissions/viewuser/id/'.$user['user_id']);?>"><?php echo translate('View');?></a></b></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>