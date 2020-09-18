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
	<title><?php echo $page_title . $project_title; ?></title>
	<link rel="shortcut icon" type="image/x-icon" href="<?php echo $this->pmurl->get_template_image('favicon.ico');?>">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">