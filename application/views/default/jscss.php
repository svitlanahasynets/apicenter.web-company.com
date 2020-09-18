	<link rel="stylesheet" type="text/css" href="<?php echo $this->pmurl->get_css('includes/font-awesome-4.5.0/css/font-awesome.min.css');?>" />
	<link href='https://fonts.googleapis.com/css?family=Open+Sans:400,700,700italic,400italic&subset=latin,greek' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" type="text/css" href="<?php echo $this->pmurl->get_css('style.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo $this->pmurl->get_css('responsive.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo $this->pmurl->get_css('jquery-ui/jquery-ui.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo $this->pmurl->get_css('form/select2.min.css');?>" />
	<?php
	if(isset($css)){
		foreach($css as $css_file){
			echo '<link rel="stylesheet" type="text/css" href="'.$this->pmurl->get_css($css_file).'" />'."\n";
		}
	}
	?>
	<script type="text/javascript" src="<?php echo $this->pmurl->get_js('jquery.js');?>"></script>
	<script type="text/javascript" src="<?php echo $this->pmurl->get_js('jquery-ui.js');?>"></script>
	<script type="text/javascript" src="<?php echo $this->pmurl->get_js('form/select2.min.js');?>"></script>
	<script type="text/javascript" src="<?php echo $this->pmurl->get_js('files/jquery.form.js');?>"></script>
	<script type="text/javascript" src="<?php echo $this->pmurl->get_js('app.js');?>"></script>
	<?php
	if(isset($js)){
		foreach($js as $js_file){
			echo '<script type="text/javascript" src="'.$this->pmurl->get_js($js_file).'"></script>'."\n";
		}
	}
	?>
</head>
<body>