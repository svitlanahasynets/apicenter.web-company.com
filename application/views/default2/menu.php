
<?php if(!empty($menu_items)):?>
<div id="m_aside_left" class="m-grid__item m-aside-left ">
	<div  id="m_ver_menu" class="m-aside-menu  m-aside-menu--skin-light m-aside-menu--submenu-skin-light " data-menu-vertical="true" data-menu-scrollable="false" data-menu-dropdown-timeout="500"  >
		<ul class="m-menu__nav  m-menu__nav--dropdown-submenu-arrow create_project_sidebar">

			<?php
			foreach($menu_items as $item){
				if(isset($item['children']) && !empty($item['children'])){
					foreach($item['children'] as $child){
						$permission = $this->Permissions_model->check_permission_user('access_'.$child['code'].'_section', 0);
					}
				}else{
					$permission = $this->Permissions_model->check_permission_user('access_'.$item['code'].'_section', 0);
				}
				if($permission == 've' || $permission == 'v' || $item['code'] == 'dashboard'){
					$controller = $this->router->fetch_class();
					if($controller == 'permissions' || $controller == 'permissions')
						$controller = ' m-menu_active';
					else
						$controller = '';

					if(isset($item['children']) && !empty($item['children'])){

						echo '<li class="m-menu__item  m-menu__item--submenu create_project_sidebar_list '.$controller.'" aria-haspopup="true"  data-menu-submenu-toggle="hover">
							<a  href="#" class="m-menu__link m-menu__toggle">
								<i class="m-menu__link-icon flaticon-cogwheel-2"><span></span></i>
								<span class="m-menu__link-text">
									'.$item['text'].'
								</span>
								<i class="m-menu__ver-arrow la la-angle-right"></i>
							</a>
							<div class="m-menu__submenu ">
								<span class="m-menu__arrow"></span>
								<ul class="m-menu__subnav">
									<li class="m-menu__item  m-menu__item--parent" aria-haspopup="true" >
										<span class="m-menu__link">
											<span class="m-menu__link-text">
												'.$item['text'].'
											</span>
										</span>
									</li>';
								foreach($item['children'] as $child){
										echo '<li class="m-menu__item " aria-haspopup="true"  data-redirect="true">
										<a href="'.site_url('/'.$child['code']).'" class="m-menu__link ">
											<i class="m-menu__link-bullet m-menu__link-bullet--dot">
												<span></span>
											</i>
											<span class="m-menu__link-text">
												'.$child['text'].'
											</span>
										</a>
									</li>';
								}

							echo	'</ul>
							</div>
						</li>';
					} else{
						echo '<li class="m-menu__item create_project_sidebar_list " aria-haspopup="true"  data-redirect="true">
									<a href="'.site_url('/'.$item['code']).'" class="m-menu__link ">
									<i class=" m-menu__link-icon la la-database"> <span> </span> </i>
										<span class="m-menu__link-text">
											'.$item['text'].'
										</span>
									</a>
								</li>';
					}
				}
			}
			?>
	</ul>
	</div>
</div>
<?php endif;?>
