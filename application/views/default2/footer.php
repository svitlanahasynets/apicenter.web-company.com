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
</body>
<!-- end::Body -->
</html>