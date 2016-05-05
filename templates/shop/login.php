<?php
global $mprm_login_redirect;
if (!is_user_logged_in()) : ?>
	<p class="mprm-alert mprm-alert-warn"><?php _e('You must be logged in to view this payment receipt.', 'mp-restaurant-menu') ?></p>
	<?php
// Show any error messages after form submission
	mprm_print_errors(); ?>
	<form id="mprm_login_form" class="mprm_form" action="" method="post">
		<fieldset>
			<span><legend><?php _e('Log into Your Account', 'mp-restaurant-menu'); ?></legend></span>
			<?php do_action('mprm_login_fields_before'); ?>
			<p>
				<label for="mprm_user_login"><?php _e('Username or Email', 'mp-restaurant-menu'); ?></label>
				<input name="mprm_user_login" id="mprm_user_login" class="required mprm-input" type="text" title="<?php _e('Username or Email', 'mp-restaurant-menu'); ?>"/>
			</p>
			<p>
				<label for="mprm_user_pass"><?php _e('Password', 'mp-restaurant-menu'); ?></label>
				<input name="mprm_user_pass" id="mprm_user_pass" class="password required mprm-input" type="password"/>
			</p>
			<p>
				<input type="hidden" name="redirect" value="<?php echo esc_url($mprm_login_redirect); ?>"/>
				<input type="hidden" name="mprm_login_nonce" value="<?php echo wp_create_nonce('mprm-login-nonce'); ?>"/>
				<input type="hidden" name="mprm_action" value="user_login"/>
				<input id="mprm_login_submit" type="submit" class="mprm_submit" value="<?php _e('Log In', 'mp-restaurant-menu'); ?>"/>
			</p>
			<p class="mprm-lost-password">
				<a href="<?php echo wp_lostpassword_url(); ?>" title="<?php _e('Lost Password', 'mp-restaurant-menu'); ?>">
					<?php _e('Lost Password?', 'mp-restaurant-menu'); ?>
				</a>
			</p>
			<?php do_action('mprm_login_fields_after'); ?>
		</fieldset>
	</form>
<?php else : ?>
	<p class="mprm-logged-in"><?php _e('You are already logged in', 'mp-restaurant-menu'); ?></p>
<?php endif; ?>