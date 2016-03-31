<?php

namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Model;

class Order extends Model {

	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Init metaboxes
	 */
	public function init_metaboxes() {

	}

	/**
	 * Columns Order
	 *
	 * @param array $existing_columns
	 *
	 * @return array
	 */
	public function order_columns(array $existing_columns) {
		$columns = array();
		$columns['cb'] = $existing_columns['cb'];
		$columns['order_status'] = __('Status', 'mp-restaurant-menu');
		$columns['order_title'] = __('Order', 'mp-restaurant-menu');
		$columns['order_items'] = __('Purchased', 'mp-restaurant-menu');
		$columns['order_date'] = __('Date', 'mp-restaurant-menu');
		$columns['order_total'] = __('Total', 'mp-restaurant-menu');
		return $columns;
	}

	/**
	 * Render columns order
	 *
	 * @param $column
	 *
	 * @return mixed
	 */
	public function render_order_columns($column) {
		global $post;
		//$the_order = new Order();
		switch ($column) {
			case 'order_status':
				echo ucfirst($post->post_status);
				break;
			case  'order_title':
				$order_user = $this->get_user($post);
				if (!empty($order_user)) {
					$user_info = get_userdata($order_user);
				}

				if (!empty($user_info)) {

					$username = '<a href="user-edit.php?user_id=' . absint($user_info->ID) . '">';

					if ($user_info->first_name || $user_info->last_name) {
						$username .= esc_html(sprintf(_x('%1$s %2$s', 'full name', 'mp-restaurant-menu'), ucfirst($user_info->first_name), ucfirst($user_info->last_name)));
					} else {
						$username .= esc_html(ucfirst($user_info->display_name));
					}

					$username .= '</a>';

				} else {
					if ($post->billing_first_name || $post->billing_last_name) {
						$username = trim(sprintf(_x('%1$s %2$s', 'full name', 'mp-restaurant-menu'), $post->billing_first_name, $post->billing_last_name));
					} else {
						$username = __('Guest', 'mp-restaurant-menu');
					}
				}

				printf(_x('%s by %s', 'Order number by X', 'mp-restaurant-menu'), '<a href="' . admin_url('post.php?post=' . absint($post->ID) . '&action=edit') . '" class="row-title"><strong>#' . esc_attr($this->get_order_number($post)) . '</strong></a>', $username);

				if ($post->billing_email) {
					echo '<small class="meta email"><a href="' . esc_url('mailto:' . $post->billing_email) . '">' . esc_html($post->billing_email) . '</a></small>';
				}

				echo '<button type="button" class="toggle-row"><span class="screen-reader-text">' . __('Show more details', 'mp-restaurant-menu') . '</span></button>';

				break;
			case 'order_date' :
				if ('0000-00-00 00:00:00' == $post->post_date) {
					$t_time = $h_time = __('Unpublished', 'mp-restaurant-menu');
				} else {
					$t_time = get_the_time(__('Y/m/d g:i:s A', 'mp-restaurant-menu'), $post);
					$h_time = get_the_time(__('Y/m/d', 'mp-restaurant-menu'), $post);
				}

				echo '<abbr title="' . esc_attr($t_time) . '">' . esc_html(apply_filters('mprm_post_date_column_time', $h_time, $post)) . '</abbr>';
				break;
			case 'order_items' :
				echo '<a href="#" class="show_order_items">' . apply_filters('mprm_admin_order_item_count', sprintf(_n('%d item', '%d items', $this->get_item_count($post), 'mp-restaurant-menu'), $this->get_item_count($post)), $post) . '</a>';

				if (sizeof($this->get_order_items($post)) > 0) {

				}
				break;
			case 'order_total' :
				echo $this->get_order_total($post);
				break;
			default:
				break;
		}

		return $column;
	}

	/**
	 * Get order items
	 *
	 * @param \WP_Post $post
	 *
	 * @return array
	 */
	public function get_order_items(\WP_Post $post) {
		$items = array();

		return $items;

	}


	public function get_order_total(\WP_Post $post) {

		return 0;

	}

	public function get_item_count(\WP_Post $post) {
		return 0;
	}

	public function get_order_number(\WP_Post $post) {
		return $post->ID;

	}

	public function get_user(\WP_Post $post) {
		return get_current_user_id();
	}

	public function order_sortable_columns($columns) {
		$custom = array(
			'order_title' => 'ID',
			'order_total' => 'order_total',
			'order_date' => 'date'
		);
		unset($columns['comments']);

		return wp_parse_args($custom, $columns);
	}
}