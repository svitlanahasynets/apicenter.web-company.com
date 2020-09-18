		<?php $username = $this->session->userdata('username'); ?>
		<?php if($username != ''): ?>
		<footer class="m-grid__item m-footer ">
			<div class="m-container m-container--responsive m-container--xxl m-container--full-height m-page__container">
				<div class="m-footer__wrapper">
					<div class="m-stack m-stack--flex-tablet-and-mobile m-stack--ver m-stack--desktop">
						<div class="m-stack__item m-stack__item--left m-stack__item--middle m-stack__item--last">
							<span class="m-footer__copyright">
								<?php echo date('Y');?> &copy; Webcompany
							</span>
						</div>
					</div>
				</div>
			</div>
		</footer>
		<?php endif; ?>
	</div>
	</div>
	<?php 
		$scripts = array_merge(
			$this->pmurl->get_all_data('dist/js', 'js', 'app.'), 
			$this->pmurl->get_all_data('dist/js', 'js', 'chunk-vendors.')
		);
	?>
	<?php if ($scripts):?>
		<?php foreach ($scripts as $script):?>
			<script type=module src=<?php echo '/'.$script; ?>> </script>
		<?php endforeach;?>
	<?php endif;?>
	<script>
	!function () { var e = document, t = e.createElement("script"); if (!("noModule" in t) && "onbeforeload" in t) { 
		var n = !1; e.addEventListener("beforeload", function (e) { if (e.target === t) n = !0; else if 
		(!e.target.hasAttribute("nomodule") || !n) return; e.preventDefault() }, !0), 
		t.type = "module", t.src = ".", e.head.appendChild(t), t.remove() } }();
	</script>	
	<?php 
		$scripts = array_merge(
			$this->pmurl->get_all_data('dist/js', 'js', 'chunk-vendors-legacy.'), 
			$this->pmurl->get_all_data('dist/js', 'js', 'app-legacy.')
		);
	?>
	<?php if ($scripts):?>
		<?php foreach ($scripts as $script):?>
		<script src="<?php echo '/'.$script ?>" nomodule></script>
		<?php endforeach;?>
	<?php endif;?>

	<script type="text/javascript">window.$crisp=[];window.CRISP_WEBSITE_ID="c1ae157a-6a12-4e32-8f32-33c5d52c988d";(function(){d=document;s=d.createElement("script");s.src="https://client.crisp.chat/l.js";s.async=1;d.getElementsByTagName("head")[0].appendChild(s);})();</script>

</body>
<!-- end::Body -->
</html>