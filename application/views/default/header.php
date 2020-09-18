<?php
$username = $this->session->userdata('username');
$success_messages = get_success_messages();
$error_messages = get_error_messages();
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

<div class="relogin dialog-container">
	<div class="dialog">
		<span class="error" style="display:none;"><?php echo translate('Login not successful. Please try again'); ?></span>
		<?php $this->load->view(TEMPLATE.'/user/login'); ?>
	</div>
</div>

<div class="wrapper"><!-- Start of wrapper -->
	<div class="header"><!-- Start of header -->
		<div class="quick-menu"><!-- Start of quick menu -->
			<span class="user-profile-button"></span>
			<span class="search-button"></span>
			<span class="help-button"></span>
		</div><!-- End of quick menu -->
		<div class="page-title"><!-- Start of page title bar -->
			<div class="page-title-left">
				<div class="mobile menu-toggle">
					<span class="fa fa-bars"></span>
				</div>
				<div class="logo"><!-- Start of logo -->
					<img src="<?php echo $this->pmurl->get_template_image('logo.png'); ?>" class="logo" />
				</div><!-- End of logo -->
			</div>
			<h1 class="page-title"><?php echo $page_title;?></h1>
			<?php if(isset($project_title)): ?>
			<h2 class="project-title"> | <?php echo $project_title; ?></h2>
			<?php endif; ?>
			<?php if(isset($go_back_url) && isset($go_back_title)): ?>
			<a href="<?php echo $go_back_url;?>" class="go-back-url"><?php echo $go_back_title; ?></a>
			<?php endif; ?>
		</div><!-- End of page title bar -->
	</div><!-- End of header -->
	<div class="menu-container">
		<div class="menu"><!-- Start of menu -->
			<?php if($username != ''): ?>
			<?php echo $menu_html; ?>
			<?php endif; ?>
		</div><!-- End of menu -->
		<div class="login-bar"><!-- Start of login bar -->
			<?php if($username != ''): ?>
			<p><?php echo translate('Welcome');?>, <?php echo $username;?> | <a href="<?php echo $login_url;?>"><?php echo translate('Log out');?></a></p>
			<?php endif; ?>
		</div><!-- End of login bar -->
	</div>
	<div class="container"><!-- Start of container -->
		<div class="messages"><!-- Start of messages -->
			<?php if(!empty($success_messages)): ?>
				<div class="success-messages">
					<span>
					<?php foreach($success_messages as $success_message): ?>
						<?php echo $success_message;?><br />
					<?php endforeach; ?>
					</span>
				</div>
			<?php endif; ?>
			<?php if(!empty($error_messages)): ?>
				<div class="success-messages">
					<span>
					<?php foreach($error_messages as $error_message): ?>
						<?php echo $error_message;?><br />
					<?php endforeach; ?>
					</span>
				</div>
			<?php endif; ?>
		</div><!-- End of messages -->
		<?php if(!isset($hide_sidebar)): ?>
		<div class="sidebar box-shadow folded"><!-- Start of sidebar -->
			<?php echo $sidebar_html; ?>
		</div><!-- End of sidebar -->
		<div class="sidebar-toggle box-shadow"><span>></span></div>
		<?php endif; ?>
		<?php
			$content_box_class = '';
			if(isset($hide_sidebar)){
				$content_box_class = 'fullwidth';
			}
		?>
		<div class="content box-shadow <?php echo $content_box_class; ?>"><!-- Start of content -->
			<div class="content-inner">