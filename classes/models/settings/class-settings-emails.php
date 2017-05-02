<?php
namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Model;
use mp_restaurant_menu\classes\View;

/**
 * Class Settings_emails
 * @package mp_restaurant_menu\classes\models
 */
class Settings_emails extends Model {
	protected static $instance;
	/**
	 * Holds the from address
	 *
	 * @since 2.1
	 */
	private $from_address;
	/**
	 * Holds the from name
	 *
	 * @since 2.1
	 */
	private $from_name;
	/**
	 * Holds the email content type
	 *
	 * @since 2.1
	 */
	private $content_type;
	/**
	 * Holds the email headers
	 *
	 * @since 2.1
	 */
	private $headers;
	/**
	 * Whether to send email in HTML
	 *
	 * @since 2.1
	 */
	private $html = true;
	/**
	 * The email template to use
	 *
	 * @since 2.1
	 */
	private $template;
	/**
	 * The header text for the email
	 *
	 * @since  2.1
	 */
	private $heading = '';

	/**
	 * Get things going
	 *
	 * @since 2.1
	 */
	public function __construct() {
		if ('none' === $this->get_template()) {
			$this->html = false;
		}
		add_action('mprm_email_send_before', array($this, 'send_before'));
		add_action('mprm_email_send_after', array($this, 'send_after'));
	}

	/**
	 * Get the enabled email template
	 *
	 * @since 2.1
	 *
	 * @return string|null
	 */
	public function get_template() {
		if (!$this->template) {
			$this->template = $this->get('settings')->get_option('email_template', 'default');
		}
		return apply_filters('mprm_email_template', $this->template);
	}

	/**
	 * @return Settings_emails
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Set value
	 *
	 * @param $key
	 * @param $value
	 */
	public function __set($key, $value) {
		$this->$key = $value;
	}

	/**
	 * @return mixed
	 */
	public function get_email_templates() {
		$templates = array(
			'default' => __('Default Template', 'mp-restaurant-menu'),
			'none' => __('No template, plain text only', 'mp-restaurant-menu')
		);
		return apply_filters('mprm_email_templates', $templates);
	}

	/**
	 * Generate email template preview
	 */
	public function email_template_preview() {
		if (!current_user_can('manage_restaurant_settings')) {
			return;
		}
		ob_start();
		?>
		<a href="<?php echo esc_url(add_query_arg(array('mprm_action' => 'preview_email', 'controller' => 'settings'), home_url())); ?>" class="button-secondary" target="_blank" title="<?php _e('Purchase Receipt Preview', 'mp-restaurant-menu'); ?> "><?php _e('Preview Purchase Receipt', 'mp-restaurant-menu'); ?></a>
		<a href="<?php echo wp_nonce_url(add_query_arg(array('mprm_action' => 'send_test_email', 'controller' => 'settings')), 'mprm-test-email'); ?>" title="<?php _e('This will send a demo purchase receipt to the emails listed below.', 'mp-restaurant-menu'); ?>" class="button-secondary"><?php _e('Send Test Email', 'mp-restaurant-menu'); ?></a>
		<?php
		echo ob_get_clean();
	}

	/**
	 * Display email template preview
	 */
	public function display_email_template_preview() {
		if (empty($_GET['mprm_action'])) {
			return;
		}
		if ('preview_email' !== $_GET['mprm_action']) {
			return;
		}
		if (!current_user_can('manage_restaurant_settings')) {
			return;
		}
		$this->heading = __('Purchase Receipt', 'mp-restaurant-menu');

		$content = $this->get('emails')->get_email_body_content(0, array());
		$preview_template_tags = $this->get('emails')->email_preview_template_tags($content);
		echo $this->build_email($preview_template_tags);
		exit;
	}

	/**
	 * Build the final email
	 *
	 * @since 2.1
	 *
	 * @param string $message
	 *
	 * @return string
	 */
	public function build_email($message) {
		$template = $this->get_template();
		$header_img = $this->get('settings')->get_option('email_logo', '');
		$heading = $this->get_heading();
		if (false === $this->html) {
			return apply_filters('mprm_email_message', wp_strip_all_tags($message), $this);
		}
		$message = $this->text_to_html($message);
		ob_start();
		View::get_instance()->get_template('emails/header-' . $template, array('header_img' => $header_img, 'heading' => $heading));
		/**
		 * Hooks into the email header
		 *
		 * @since 2.1
		 */
		do_action('mprm_email_header', $this);
		if (has_action('mprm_email_template_' . $template)) {
			/**
			 * Hooks into the template of the email
			 *
			 * @param string $this ->template Gets the enabled email template
			 *
			 * @since 2.1
			 */
			do_action('mprm_email_template_' . $template);
		} else {
			View::get_instance()->get_template('emails/body');
		}
		/**
		 * Hooks into the body of the email
		 *
		 * @since 2.1
		 */
		do_action('mprm_email_body', $this);
		View::get_instance()->get_template('emails/footer-' . $template);
		/**
		 * Hooks into the footer of the email
		 *
		 * @since 2.1
		 */
		do_action('mprm_email_footer', $this);
		$body = ob_get_clean();
		$message = str_replace('{email}', $message, $body);
		return apply_filters('mprm_email_message', $message, $this);
	}

	/**
	 * Get the header text for the email
	 *
	 * @since 2.1
	 */
	public function get_heading() {
		return apply_filters('mprm_email_heading', $this->heading);
	}

	/**
	 * Converts text to formatted HTML. This is primarily for turning line breaks into <p> and <br/> tags.
	 *
	 * @param $message
	 *
	 * @return string
	 */
	public function text_to_html($message) {
		if ('text/html' == $this->content_type || true === $this->html) {
			$message = wpautop($message);
		}
		return $message;
	}

	/**
	 * Retrieve email templates
	 *
	 * @since 2.1
	 */
	public function get_templates() {
		$templates = array(
			'default' => __('Default Template', 'mp-restaurant-menu'),
			'none' => __('No template, plain text only', 'mp-restaurant-menu')
		);
		return apply_filters('mprm_email_templates', $templates);
	}

	/**
	 * Send the email
	 *
	 * @param $to
	 * @param $subject
	 * @param $message
	 * @param string $attachments
	 *
	 * @return bool
	 */
	public function send($to, $subject, $message, $attachments = '') {
		if (!did_action('init') && !did_action('admin_init')) {
			_doing_it_wrong(__FUNCTION__, __('You cannot send email with Restaurant menu Emails until init/admin_init has been reached', 'mp-restaurant-menu'), null);
			return false;
		}
		/**
		 * Hooks before the email is sent
		 *
		 * @since 2.1
		 */
		do_action('mprm_email_send_before', $this);
		$subject = $this->parse_tags($subject);
		$message = $this->parse_tags($message);
		$message = $this->build_email($message);
		$attachments = apply_filters('mprm_email_attachments', $attachments, $this);

		$sent = wp_mail($to, $subject, $message, $this->get_headers(), $attachments);

		$log_errors = apply_filters('mprm_log_email_errors', true, $to, $subject, $message);

		if (!$sent && true === $log_errors) {
			if (is_array($to)) {
				$to = implode(',', $to);
			}
			$log_message = sprintf(
				__("Email from Restaurant menu Menu items failed to send.\nSend time: %s\nTo: %s\nSubject: %s\n", 'mp-restaurant-menu'),
				date_i18n('F j Y H:i:s', current_time('timestamp')),
				$to,
				$subject
			);
			error_log($log_message);
		}
		/**
		 * Hooks after the email is sent
		 *
		 * @since 2.1
		 */
		do_action('mprm_email_send_after', $this);

		return $sent;
	}

	/**
	 * @param $content
	 *
	 * @return mixed
	 */
	public function parse_tags($content) {
		return $content;
	}

	/**
	 * Get the email headers
	 *
	 * @since 2.1
	 */
	public function get_headers() {
		if (!$this->headers) {
			$this->headers = "From: {$this->get_from_name()} <{$this->get_from_address()}>\r\n";
			$this->headers .= "Reply-To: {$this->get_from_address()}\r\n";
			$this->headers .= "Content-Type: {$this->get_content_type()}; charset=utf-8\r\n";
		}
		return apply_filters('mprm_email_headers', $this->headers, $this);
	}

	/**
	 * Get the email from name
	 *
	 * @since 2.1
	 */
	public function get_from_name() {
		if (!$this->from_name) {
			$this->from_name = $this->get('settings')->get_option('from_name', get_bloginfo('name'));
		}
		return apply_filters('mprm_email_from_name', wp_specialchars_decode($this->from_name), $this);
	}

	/**
	 * Get the email from address
	 *
	 * @since 2.1
	 */
	public function get_from_address() {
		if (!$this->from_address) {
			$this->from_address = $this->get('settings')->get_option('from_email', get_option('admin_email'));
		}
		return apply_filters('mprm_email_from_address', $this->from_address, $this);
	}

	/**
	 * Get the email content type
	 *
	 * @since 2.1
	 */
	public function get_content_type() {
		if (!$this->content_type && $this->html) {
			$this->content_type = apply_filters('mprm_email_default_content_type', 'text/html', $this);
		} else if (!$this->html) {
			$this->content_type = 'text/plain';
		}
		return apply_filters('mprm_email_content_type', $this->content_type, $this);
	}

	/**
	 * Add filters / actions before the email is sent
	 *
	 * @since 2.1
	 */
	public function send_before() {
		add_filter('wp_mail_from', array($this, 'get_from_address'));
		add_filter('wp_mail_from_name', array($this, 'get_from_name'));
		add_filter('wp_mail_content_type', array($this, 'get_content_type'));
	}

	/**
	 * Remove filters / actions after the email is sent
	 *
	 * @since 2.1
	 */
	public function send_after() {
		remove_filter('wp_mail_from', array($this, 'get_from_address'));
		remove_filter('wp_mail_from_name', array($this, 'get_from_name'));
		remove_filter('wp_mail_content_type', array($this, 'get_content_type'));
		// Reset heading to an empty string
		$this->heading = '';
	}
}