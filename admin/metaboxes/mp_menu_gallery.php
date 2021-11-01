<div id="mprm-menu-item-gallery">
	<ul class="mp_menu_images ui-sortable">
		<?php if (!empty($value)): ?>
			<?php foreach (explode(',', $value) as $id): ?>
				<?php if (get_post($id)): ?>
					<?php $url = image_downsize($id, 'thumbnail') ?>
					<li data-attachment_id="<?php echo esc_attr( $id ); ?>" class="mprm-image">
						<img src="<?php echo esc_url( $url[0] );?>">
						<ul class="mprm-actions">
							<li>
								<a title="<?php esc_html_e('Delete image', 'mp-restaurant-menu') ?>" class="mprm-delete" href="#"><?php esc_html_e('Delete', 'mp-restaurant-menu') ?></a>
							</li>
						</ul>
					</li>
				<?php endif; ?>
			<?php endforeach; ?>
		<?php endif; ?>
	</ul>
	<input type="hidden" value="<?php echo esc_attr($value) ?>" name="<?php echo esc_attr($name) ?>">
</div>
<p class="description">
	<?php esc_html_e('Use CTRL/Command key to select multiple images', 'mp-restaurant-menu'); ?>
</p>
<p class="hide-if-no-js">
	<a class="mp_menu_gallery" href="#"><?php esc_html_e('Add images', 'mp-restaurant-menu'); ?></a>
</p>