	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js"></script>
	<link href="<?php echo $this->pmurl->get_vendor('base/vendors.bundle.css');?>" rel="stylesheet" type="text/css" />
	<link href="<?php echo $this->pmurl->get_jscss('base/style.bundle.css');?>" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $this->pmurl->get_css('form/select2.min.css');?>" />
	<script>
        WebFont.load({
          google: {"families":["Poppins:300,400,500,600,700","Roboto:300,400,500,600,700"]},
          active: function() {
              sessionStorage.fonts = true;
          }
        });
    </script>
	<?php
		if(isset($css)){
			foreach($css as $css_file){
				echo '<link rel="stylesheet" type="text/css" href="'.$this->pmurl->get_css($css_file).'" />'."\n";
			}
		}
	?>
	<script src="<?php echo $this->pmurl->get_vendor('base/vendors.bundle.js');?>" type="text/javascript"></script>
	<script src="<?php echo $this->pmurl->get_jscss('base/scripts.bundle.js');?>" type="text/javascript"></script>
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
<body class="m-page--wide m-header--fixed m-header--fixed-mobile m-footer--push m-aside--offcanvas-default"  >