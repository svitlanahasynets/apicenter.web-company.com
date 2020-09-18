	<link rel=stylesheet href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" crossorigin="anonymous">
	<script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js"></script>
	<link href="<?php echo $this->pmurl->get_vendor('base/vendors.bundle.css');?>" rel="stylesheet" type="text/css" />
	<link href="<?php echo $this->pmurl->get_jscss('base/style.bundle.css');?>" rel="stylesheet" type="text/css" />
	<?php
		$styles = array_merge(
			$this->pmurl->get_all_data('dist/css', 'css', 'app.'),
			$this->pmurl->get_all_data('dist/css', 'css', 'chunk-vendors.')
		);
	?>
	<?php if ($styles):?>
		<?php foreach ($styles as $style):?>
			<link href=<?php echo '/'.$style;?> rel="preload" as="style">
			<link href=<?php echo '/'.$style;?> rel=stylesheet>
		<?php endforeach;?>
	<?php endif;?>

	<?php
		$scripts = array_merge(
			$this->pmurl->get_all_data('dist/js', 'js', 'app.'),
			$this->pmurl->get_all_data('dist/js', 'js', 'chunk-vendors.')
		);
	?>
	<?php if ($scripts):?>
		<?php foreach ($scripts as $script):?>
			<link href=<?php echo '/'.$script;?> rel="modulepreload" as="script">
		<?php endforeach;?>
	<?php endif;?>
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
<body class="m-page--wide m-header--fixed m-header--fixed-mobile m-footer--push m-aside--offcanvas-default fixed-sidebar"  >
<noscript>
	<strong>We're sorry but ArchitectUI doesn't work properly without JavaScript enabled. Please enable it to continue.</strong>
</noscript>
<?php
$username = $this->session->userdata('username');
$user_email = $this->session->userdata('user_email');
$fullname   = $this->session->userdata('fullname');
$site_url = site_url();
?>
<!-- Constants for AJAX requests or JS functions -->
<input type="hidden" id="site-url" value="<?php echo $site_url;?>" />
<input type="hidden" id="current_module" value="<?php echo strtolower($this->router->fetch_class());?>" />
<input type="hidden" id="current_action" value="<?php echo strtolower($this->router->fetch_method());?>" />
<input type="hidden" id="currency-symbol" value="<?php echo CURRENCY_SYMBOL; ?>" />
<input type="hidden" id="price-thousand-separator" value="<?php echo PRICE_THOUSAND_SEPARATOR; ?>" />
<input type="hidden" id="price-decimal-separator" value="<?php echo PRICE_DECIMAL_SEPARATOR; ?>" />
<input type="hidden" id="exit-page-message" value="<?php echo translate('Please save your work before leaving this page.'); ?>" />
<div id="app">