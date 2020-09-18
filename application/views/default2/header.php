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
	<!-- begin:: Page -->
	<div class="m-grid m-grid--hor m-grid--root m-page">
		<!-- begin::Header -->
		<?php if($username != ''): ?>
			<header class="m-grid__item m-header "  data-minimize="minimize" data-minimize-offset="200" data-minimize-mobile-offset="200" >
				<div class="m-header__top">
					<div class="m-container m-container--responsive m-container--xxl m-container--full-height m-page__container">
						<div class="m-stack m-stack--ver m-stack--desktop">
							<!-- begin::Brand -->
							<div class="m-stack__item m-brand">
								<div class="m-stack m-stack--ver m-stack--general m-stack--inline">
									<div class="m-stack__item m-stack__item--middle m-brand__logo">
										<a href="javascript::void(0)" class="m-brand__logo-wrapper">
											<img src="<?php echo $this->pmurl->get_template_image('logo.png'); ?>" class="logo" />
										</a>
									</div>
									<div class="m-stack__item m-stack__item--middle m-brand__tools">
										<div class="m-dropdown m-dropdown--inline m-dropdown--arrow m-dropdown--align-left m-dropdown--align-push" data-dropdown-toggle="click" aria-expanded="true">
											<a href="#" class="dropdown-toggle m-dropdown__toggle btn btn-outline-metal m-btn  m-btn--icon m-btn--pill">
												<span>
													<?php echo $page_title;?>
												</span>
											</a>
											<div class="m-dropdown__wrapper">
												<span class="m-dropdown__arrow m-dropdown__arrow--left m-dropdown__arrow--adjust"></span>
												<div class="m-dropdown__inner">
													<div class="m-dropdown__body">
														<div class="m-dropdown__content">
															<ul class="m-nav">
																<li class="m-nav__section m-nav__section--first m--hide">
																	<span class="m-nav__section-text">
																		Quick Menu
																	</span>
																</li>
	<?php
	$seperate = '';
	foreach($menu_items as $item){
		if(isset($item['children']) && !empty($item['children'])){
			foreach($item['children'] as $child){
				$permission = $this->Permissions_model->check_permission_user('access_'.$child['code'].'_section', 0);
			}
		}else{
			$permission = $this->Permissions_model->check_permission_user('access_'.$item['code'].'_section', 0);
		}
			if($permission == 've' || $permission == 'v' || $item['code'] == 'dashboard'){
				// Get children
				$childrenHtml = '';
				$isActive = false;
				if(isset($item['children']) && !empty($item['children'])){
					foreach($item['children'] as $child){
						if(isset($active_menu_item) && $child['code'] == $active_menu_item){
							$isActive = true;
							$childrenHtml .= $seperate.'<li class="m-menu__item  m-menu__item--active  m-menu__item--submenu m-menu__item--rel"  data-menu-submenu-toggle="click" aria-haspopup="true" style="padding-bottom: 6px;"><div class="m-menu__submenu m-menu__submenu--classic m-menu__submenu--left"><span class="m-menu__arrow m-menu__arrow--adjust"></span><ul class="m-menu__subnav"><li class="m-menu__item "  data-redirect="true" aria-haspopup="true"><a  href="'.site_url('/'.$child['code']).'" class="m-nav__link "><span class="m-menu__link-text"><font color="#6f727d">'.$child['text'].'</font></span></a></li></ul></div></li>';
						} else {
							$childrenHtml .= $seperate.'<li class="m-menu__item m-menu__item--submenu m-menu__item--rel"  data-menu-submenu-toggle="click" aria-haspopup="true" style="padding-bottom: 6px;"><div class="m-menu__submenu m-menu__submenu--classic m-menu__submenu--left"><span class="m-menu__arrow m-menu__arrow--adjust"></span><ul class="m-menu__subnav"><li class="m-menu__item "><a  href="'.site_url('/'.$child['code']).'" class="m-nav__link "><span class="m-menu__link-text"><font color="#6f727d">'.$child['text'].'</font></span></a></li></ul></div></li>';
						}
					}
				}

				if($childrenHtml != ''){
					$itemUrl = '#';
				} else {
					$itemUrl = site_url('/'.$item['code']);
				}
				if((isset($active_menu_item) && $item['code'] == $active_menu_item) || $isActive){
					echo $seperate.'<li class="m-nav__item"><a href="'.$itemUrl.'" class="m-nav__link"> <i class="m-nav__link-icon la la-database"></i><span class="m-nav__link-text">'.$item['text'].'</span></a>'.$childrenHtml.'</li>';
				} else {
					echo $seperate.'</li> <li class="m-nav__item"><a href="'.$itemUrl.'" class="m-nav__link"><i class="m-nav__link-icon flaticon-cogwheel-2"></i><span class="m-nav__link-text">'.$item['text'].'</span></a>'.$childrenHtml.'</li>';
				}
				$seperate = '<li class="m-nav__separator"></li>';
			}
	}
	?>
															</ul>
														</div>
													</div>
												</div>
											</div>
										</div>
										<a id="m_aside_header_menu_mobile_toggle" href="javascript:;" class="m-brand__icon m-brand__toggler m--visible-tablet-and-mobile-inline-block">
											<span></span>
										</a>
										<a id="m_aside_header_topbar_mobile_toggle" href="javascript:;" class="m-brand__icon m--visible-tablet-and-mobile-inline-block">
											<i class="flaticon-more"></i>
										</a>

									</div>
									<!-- <span style="display: table-cell;vertical-align: middle;height: 100%;padding-left: 10px;">
										<font color="red"> BETA</font>
									</span> -->
								</div>
							</div>



							<div class="m-stack__item m-stack__item--fluid m-header-head" id="m_header_nav">
								<div id="m_header_topbar" class="m-topbar  m-stack m-stack--ver m-stack--general">
									<div class="m-stack__item m-topbar__nav-wrapper">
										<ul class="m-topbar__nav m-nav m-nav--inline">
											<li class="m-nav__item m-topbar__user-profile m-topbar__user-profile--img  m-dropdown m-dropdown--medium m-dropdown--arrow m-dropdown--header-bg-fill m-dropdown--align-right m-dropdown--mobile-full-width m-dropdown--skin-light" data-dropdown-toggle="click">
												<a href="#" class="m-nav__link m-dropdown__toggle">
													<span class="m-topbar__userpic m--hide">
														<img src="<?php echo $this->pmurl->get_jscss('media/img/users/user.jpg');?>" class="m--img-rounded m--marginless m--img-centered" alt=""/>
													</span>
													<span class="m-topbar__welcome">
														<?php echo translate('Welcome');?>,&nbsp;
													</span>
													<span class="m-topbar__username">
														<?php if($fullname != ''): echo $fullname; endif; ?>
													</span>
												</a>
												<div class="m-dropdown__wrapper">
													<span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust"></span>
													<div class="m-dropdown__inner">
														<div class="m-dropdown__header m--align-center" style="background: url(<?php echo $this->pmurl->get_jscss('media/img/misc/user_profile_bg.jpg');?>); background-size: cover;">
															<div class="m-card-user m-card-user--skin-dark">
																<div class="m-card-user__pic">
																	<img src="<?php echo $this->pmurl->get_jscss('media/img/users/user.jpg');?>" class="m--img-rounded m--marginless" alt=""/>
																</div>
																<div class="m-card-user__details">
																	<span class="m-card-user__name m--font-weight-500">
																		<?php if($username != ''): echo $username; endif; ?>
																	</span>
																	<a href="javascript:void(0);" class="m-card-user__email m--font-weight-300 m-link">
																		 <?php if($fullname != ''): echo $fullname; endif; ?>
																	</a>
																	<?php if($user_email != ''): ?>
																	<a href="mailto:<?php echo $user_email; ?>" class="m-card-user__email m--font-weight-300 m-link">
																		<?php echo $user_email; ?>
																	</a>
																	<?php endif; ?>
																</div>
															</div>
														</div>
														<div class="m-dropdown__body">
															<div class="m-dropdown__content">
																<ul class="m-nav m-nav--skin-light">
																	<li class="m-nav__section m--hide">
																		<span class="m-nav__section-text">
																			Section
																		</span>
																	</li>
																	<li class="m-nav__separator m-nav__separator--fit"></li>
																	<li class="m-nav__separator m-nav__separator--fit"></li>
																	<li class="m-nav__item">
																		<a href="<?php echo $login_url;?>" class="btn m-btn--pill btn-secondary m-btn m-btn--custom m-btn--label-brand m-btn--bolder">
																			<?php echo translate('Log out');?>
																		</a>
																	</li>
																</ul>
															</div>
														</div>
													</div>
												</div>
											</li>
										</ul>
									</div>
								</div>
							</div>
							<!-- end::Topbar -->
						</div>
					</div>
				</div>
				<div class="m-header__bottom">
					<div class="m-container m-container--responsive m-container--xxl m-container--full-height m-page__container">
						<div class="m-stack m-stack--ver m-stack--desktop">
							<!-- begin::Horizontal Menu -->
							<div class="m-stack__item m-stack__item--middle m-stack__item--fluid">
								<button class="m-aside-header-menu-mobile-close  m-aside-header-menu-mobile-close--skin-light " id="m_aside_header_menu_mobile_close_btn">
									<i class="la la-close"></i>
								</button>
								<div id="m_header_menu" class="m-header-menu m-aside-header-menu-mobile m-aside-header-menu-mobile--offcanvas  m-header-menu--skin-dark m-header-menu--submenu-skin-light m-aside-header-menu-mobile--skin-light m-aside-header-menu-mobile--submenu-skin-light "  >
									<ul class="m-menu__nav  m-menu__nav--submenu-arrow ">
										<?php
										foreach($menu_items as $item){
											if(isset($item['children']) && !empty($item['children'])){
												foreach($item['children'] as $child){
													$permission = $this->Permissions_model->check_permission_user('access_'.$child['code'].'_section', 0);
												}
											}else{
												$permission = $this->Permissions_model->check_permission_user('access_'.$item['code'].'_section', 0);
											}
										if($permission == 've' || $permission=='v' || $item['code'] == 'dashboard'){
												// Get children
												$childrenHtml = '';
												$isActive = false;

												if(isset($item['children']) && !empty($item['children'])){
													$childrenHtml .= '<ul class="m-menu__subnav">';
													foreach($item['children'] as $child){
														if(isset($active_menu_item) && $child['code'] == $active_menu_item){
															$isActive = true;
															$childrenHtml .= '<li class="m-menu__item " aria-haspopup="true"><a href="'.site_url('/'.$child['code']).'" class="m-menu__link "><span class="m-menu__link-title"><span class="m-menu__link-wrap"><span class="m-menu__link-text">'.$child['text'].'</span></a></li>';
														} else {
															$childrenHtml .= '<li class="m-menu__item " aria-haspopup="true"><a href="'.site_url('/'.$child['code']).'" class="m-menu__link "><span class="m-menu__link-title"><span class="m-menu__link-wrap"><span class="m-menu__link-text">'.$child['text'].'</span></a></li>';
														}
													}
													$childrenHtml .= '</ul>';
												}

												if((isset($active_menu_item) && $item['code'] == $active_menu_item) || $isActive){
													$active = 'm-menu__item--active';
												} else {
													$active = '';
												}

												if($childrenHtml != ''){
													$itemUrl = '#';
													echo '<li class="m-menu__item  m-menu__item--submenu m-menu__item--rel '.$active.' "  data-menu-submenu-toggle="click" aria-haspopup="true"><a  href="#" class="m-menu__link m-menu__toggle"><span class="m-menu__item-here"></span><span class="m-menu__link-text">'.$item['text'].'</span><i class="m-menu__hor-arrow la la-angle-down"></i><i class="m-menu__ver-arrow la la-angle-right"></i></a><div class="m-menu__submenu m-menu__submenu--classic m-menu__submenu--left"><span class="m-menu__arrow m-menu__arrow--adjust"></span>'.$childrenHtml.'</div></li>';
												} else {
													$itemUrl = site_url('/'.$item['code']);
													echo '<li class="m-menu__item '.$active.' "  aria-haspopup="true"><a href="'.$itemUrl.'" class="m-menu__link "><span class="m-menu__item-here"></span><span class="m-menu__link-text">'.$item['text'].'</span></a>'.$childrenHtml.'</li>';
												}
											}
										}
										?>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</div>
			</header>
		<?php endif; ?>
