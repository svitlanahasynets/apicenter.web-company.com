<?php
if(isset($project_title)){
	$page_title .= ' | ';
} else {
	$project_title = '';
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="en">
<head>
	<meta charset="utf-8">
	<title><?php echo (isset($page_title) ? $page_title : '') . $project_title; ?></title>
	<link rel="shortcut icon" type="image/x-icon" href="<?php echo $this->pmurl->get_template_image('favicon.ico');?>">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	<?php
		$username = $this->session->userdata('username');
		$user_email = $this->session->userdata('user_email');
		$fullname   = $this->session->userdata('fullname');
		$user = $this->db->get_where('permissions_users', array('user_name' => $this->session->userdata('username')))->row_array();
		$profile_picture   = $user['profile_picture'] ? DATA_URL.'/profile_pictures/'.$user['profile_picture'] : DEFAULT3_THEME_URL.'/src/assets/images/avatars/default.png';
        $default_lang = $this->session->userdata('default_lang');
		$site_url = site_url();
	?>
	<script>
		localStorage.setItem('username', '<?php echo $username ?>');  
		localStorage.setItem('user_email', '<?php echo $user_email?>');
		localStorage.setItem('fullname', '<?php echo $fullname?>');
		localStorage.setItem('profile_picture', '<?php echo $profile_picture?>');
        localStorage.setItem('default_lang', '<?php echo $default_lang?>');
		localStorage.setItem('site_url', '<?php echo $site_url?>');
	</script>