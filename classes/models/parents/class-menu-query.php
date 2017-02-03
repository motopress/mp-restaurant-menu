<?php
namespace mp_restaurant_menu\classes\models\parents;

use mp_restaurant_menu\classes\Model;
use mp_restaurant_menu\classes\models\Order;

/**
 * Class Parent_query
 *
 * @package mp_restaurant_menu\classes\models\parents
 */
class Parent_query extends Model {
	
	protected static $instance;
	
	public $args = array();
	
	public $payments = array();
	
	public $start_date;
	
	public $end_date;
	
	public $timestamp;
	
	/**
	 * Parent_query constructor.
	 *
	 * @param array $args
	 */
	public function __construct($args = array()) {
		parent::__construct();
	}
	
	/**
	 * @return Parent_query
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * @param array $args
	 */
	public function setup_args($args = array()) {
		$defaults = array(
			'post_type' => $this->get_post_types('value'),
			'start_date' => false,
			'end_date' => false,
			'number' => 20,
			'page' => null,
			'orderby' => 'ID',
			'order' => 'DESC',
			'user' => null,
			'status' => $this->get('payments')->get_payment_status_keys(),
			'meta_key' => null,
			'year' => null,
			'month' => null,
			'day' => null,
			's' => null,
			'search_in_notes' => false,
			'children' => false,
			'fields' => null,
		);
		$this->args = wp_parse_args($args, $defaults);
	}
	
	/**
	 * @param array $params
	 *
	 * @return array
	 */
	public function get_posts($params = array()) {
		
		do_action('mprm_pre_get_payments', $this);
		
		$query = new \WP_Query($this->args);
		
		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();
				
				$payment_id = get_post()->ID;
				$payment = new Order($payment_id);
				
				if ($this->get('settings')->get_option('enable_sequential')) {
					// Backwards Compatibility, needs to set `payment_number` attribute
					$payment->payment_number = $payment->number;
				}
				
				$this->payments[] = apply_filters('mprm_payment', $payment, $payment_id, $this);
			}
			
			wp_reset_postdata();
		}
		
		do_action('mprm_post_get_payments', $this);
		
		return $this->payments;
	}
	
	/**
	 * Date filter
	 */
	public function date_filter_pre() {
		if (!($this->args[ 'start_date' ] || $this->args[ 'end_date' ])) {
			return;
		}
		$this->setup_dates($this->args[ 'start_date' ], $this->args[ 'end_date' ]);
		
		add_filter('posts_where', array($this, 'payments_where'));
	}
	
	/**
	 * @param string $_start_date
	 * @param bool $_end_date
	 */
	public function setup_dates($_start_date = 'this_month', $_end_date = false) {
		
		if (empty($_start_date)) {
			$_start_date = 'this_month';
		}
		
		if (empty($_end_date)) {
			$_end_date = $_start_date;
		}
		
		$this->start_date = $this->convert_date($_start_date);
		$this->end_date = $this->convert_date($_end_date, true);
		
	}
	
	/**
	 * @param $date
	 * @param bool $end_date
	 *
	 * @return mixed|\WP_Error
	 */
	public function convert_date($date, $end_date = false) {
		
		$this->timestamp = false;
		$second = $end_date ? 59 : 0;
		$minute = $end_date ? 59 : 0;
		$hour = $end_date ? 23 : 0;
		$day = 1;
		$month = date('n', current_time('timestamp'));
		$year = date('Y', current_time('timestamp'));
		
		if (array_key_exists($date, $this->get_predefined_dates())) {
			
			// This is a predefined date rate, such as last_week
			switch ($date) {
				
				case 'this_month' :
					
					if ($end_date) {
						
						$day = cal_days_in_month(CAL_GREGORIAN, $month, $year);
						$hour = 23;
						$minute = 59;
						$second = 59;
						
					}
					
					break;
				
				case 'last_month' :
					
					if ($month == 1) {
						
						$month = 12;
						$year--;
						
					} else {
						
						$month--;
						
					}
					
					if ($end_date) {
						$day = cal_days_in_month(CAL_GREGORIAN, $month, $year);
					}
					
					break;
				
				case 'today' :
					
					$day = date('d', current_time('timestamp'));
					
					if ($end_date) {
						$hour = 23;
						$minute = 59;
						$second = 59;
					}
					
					break;
				
				case 'yesterday' :
					
					$day = date('d', current_time('timestamp')) - 1;
					
					// Check if Today is the first day of the month (meaning subtracting one will get us 0)
					if ($day < 1) {
						
						// If current month is 1
						if (1 == $month) {
							
							$year -= 1; // Today is January 1, so skip back to last day of December
							$month = 12;
							$day = cal_days_in_month(CAL_GREGORIAN, $month, $year);
							
						} else {
							
							// Go back one month and get the last day of the month
							$month -= 1;
							$day = cal_days_in_month(CAL_GREGORIAN, $month, $year);
							
						}
					}
					
					break;
				
				case 'this_week' :
					
					$days_to_week_start = (date('w', current_time('timestamp')) - 1) * 60 * 60 * 24;
					$today = date('d', current_time('timestamp')) * 60 * 60 * 24;
					
					if ($today < $days_to_week_start) {
						
						if ($month > 1) {
							$month -= 1;
						} else {
							$month = 12;
						}
						
					}
					
					if (!$end_date) {
						
						// Getting the start day
						
						$day = date('d', current_time('timestamp') - $days_to_week_start) - 1;
						$day += get_option('start_of_week');
						
					} else {
						
						// Getting the end day
						
						$day = date('d', current_time('timestamp') - $days_to_week_start) - 1;
						$day += get_option('start_of_week') + 6;
						
					}
					
					break;
				
				case 'last_week' :
					
					$days_to_week_start = (date('w', current_time('timestamp')) - 1) * 60 * 60 * 24;
					$today = date('d', current_time('timestamp')) * 60 * 60 * 24;
					
					if ($today < $days_to_week_start) {
						
						if ($month > 1) {
							$month -= 1;
						} else {
							$month = 12;
						}
						
					}
					
					if (!$end_date) {
						
						// Getting the start day
						
						$day = date('d', current_time('timestamp') - $days_to_week_start) - 8;
						$day += get_option('start_of_week');
						
					} else {
						
						// Getting the end day
						
						$day = date('d', current_time('timestamp') - $days_to_week_start) - 8;
						$day += get_option('start_of_week') + 6;
						
					}
					
					break;
				
				case 'this_quarter' :
					
					$month_now = date('n', current_time('timestamp'));
					
					if ($month_now <= 3) {
						
						if (!$end_date) {
							$month = 1;
						} else {
							$month = 3;
							$day = cal_days_in_month(CAL_GREGORIAN, $month, $year);
							$hour = 23;
							$minute = 59;
							$second = 59;
						}
						
					} else if ($month_now <= 6) {
						
						if (!$end_date) {
							$month = 4;
						} else {
							$month = 6;
							$day = cal_days_in_month(CAL_GREGORIAN, $month, $year);
							$hour = 23;
							$minute = 59;
							$second = 59;
						}
						
					} else if ($month_now <= 9) {
						
						if (!$end_date) {
							$month = 7;
						} else {
							$month = 9;
							$day = cal_days_in_month(CAL_GREGORIAN, $month, $year);
							$hour = 23;
							$minute = 59;
							$second = 59;
						}
						
					} else {
						
						if (!$end_date) {
							$month = 10;
						} else {
							$month = 12;
							$day = cal_days_in_month(CAL_GREGORIAN, $month, $year);
							$hour = 23;
							$minute = 59;
							$second = 59;
						}
						
					}
					
					break;
				
				case 'last_quarter' :
					
					$month_now = date('n', current_time('timestamp'));
					
					if ($month_now <= 3) {
						
						if (!$end_date) {
							$month = 10;
						} else {
							$year -= 1;
							$month = 12;
							$day = cal_days_in_month(CAL_GREGORIAN, $month, $year);
							$hour = 23;
							$minute = 59;
							$second = 59;
						}
						
					} else if ($month_now <= 6) {
						
						if (!$end_date) {
							$month = 1;
						} else {
							$month = 3;
							$day = cal_days_in_month(CAL_GREGORIAN, $month, $year);
							$hour = 23;
							$minute = 59;
							$second = 59;
						}
						
					} else if ($month_now <= 9) {
						
						if (!$end_date) {
							$month = 4;
						} else {
							$month = 6;
							$day = cal_days_in_month(CAL_GREGORIAN, $month, $year);
							$hour = 23;
							$minute = 59;
							$second = 59;
						}
						
					} else {
						
						if (!$end_date) {
							$month = 7;
						} else {
							$month = 9;
							$day = cal_days_in_month(CAL_GREGORIAN, $month, $year);
							$hour = 23;
							$minute = 59;
							$second = 59;
						}
						
					}
					
					break;
				
				case 'this_year' :
					
					if (!$end_date) {
						$month = 1;
					} else {
						$month = 12;
						$day = cal_days_in_month(CAL_GREGORIAN, $month, $year);
						$hour = 23;
						$minute = 59;
						$second = 59;
					}
					
					break;
				
				case 'last_year' :
					
					$year -= 1;
					if (!$end_date) {
						$month = 1;
					} else {
						$month = 12;
						$day = cal_days_in_month(CAL_GREGORIAN, $month, $year);
						$hour = 23;
						$minute = 59;
						$second = 59;
					}
					
					break;
				
			}
			
			
		} else if (is_numeric($date)) {
			
			// return $date unchanged since it is a timestamp
			$this->timestamp = true;
			
		} else if (false !== strtotime($date)) {
			
			$date = strtotime($date, current_time('timestamp'));
			$year = date('Y', $date);
			$month = date('m', $date);
			$day = date('d', $date);
			
		} else {
			
			return new \WP_Error('invalid_date', __('Improper date provided.', 'mp-restaurant-menu'));
			
		}
		
		if (false === $this->timestamp) {
			// Create an exact timestamp
			$date = mktime($hour, $minute, $second, $month, $day, $year);
			
		}
		
		return apply_filters('mprm_stats_date', $date, $end_date, $this);
		
	}
	
	/**
	 * @return mixed
	 */
	public function get_predefined_dates() {
		$predefined = array(
			'today' => __('Today', 'mp-restaurant-menu'),
			'yesterday' => __('Yesterday', 'mp-restaurant-menu'),
			'this_week' => __('This Week', 'mp-restaurant-menu'),
			'last_week' => __('Last Week', 'mp-restaurant-menu'),
			'this_month' => __('This Month', 'mp-restaurant-menu'),
			'last_month' => __('Last Month', 'mp-restaurant-menu'),
			'this_quarter' => __('This Quarter', 'mp-restaurant-menu'),
			'last_quarter' => __('Last Quarter', 'mp-restaurant-menu'),
			'this_year' => __('This Year', 'mp-restaurant-menu'),
			'last_year' => __('Last Year', 'mp-restaurant-menu')
		);
		
		return apply_filters('mprm_stats_predefined_dates', $predefined);
	}
	
	public function date_filter_post() {
		if (!($this->args[ 'start_date' ] || $this->args[ 'end_date' ])) {
			return;
		}
		remove_filter('posts_where', array($this, 'payments_where'));
	}
	
	public function status() {
		if (!isset ($this->args[ 'status' ])) {
			return;
		}
		
		$this->__set('post_status', $this->args[ 'status' ]);
		$this->__unset('status');
	}
	
	/**
	 * @param $query_var
	 * @param $value
	 */
	public function __set($query_var, $value) {
		if (in_array($query_var, array('meta_query', 'tax_query')))
			$this->args[ $query_var ][] = $value;
		else
			$this->args[ $query_var ] = $value;
	}
	
	/**
	 * @param $query_var
	 */
	public function __unset($query_var) {
		unset($this->args[ $query_var ]);
	}
	
	public function page() {
		if (!isset ($this->args[ 'page' ])) {
			return;
		}
		
		$this->__set('paged', $this->args[ 'page' ]);
		$this->__unset('page');
	}
	
	public function per_page() {
		
		if (!isset($this->args[ 'number' ])) {
			return;
		}
		
		if ($this->args[ 'number' ] == -1) {
			$this->__set('nopaging', true);
		} else {
			$this->__set('posts_per_page', $this->args[ 'number' ]);
		}
		
		$this->__unset('number');
	}
	
	public function month() {
		if (!isset ($this->args[ 'month' ])) {
			return;
		}
		
		$this->__set('monthnum', $this->args[ 'month' ]);
		$this->__unset('month');
	}
	
	public function orderby() {
		switch ($this->args[ 'orderby' ]) {
			case 'amount' :
				$this->__set('orderby', 'meta_value_num');
				$this->__set('meta_key', '_mprm_order_total');
				break;
			default :
				$this->__set('orderby', $this->args[ 'orderby' ]);
				break;
		}
	}
	
	public function user() {
		if (is_null($this->args[ 'user' ])) {
			return;
		}
		
		if (is_numeric($this->args[ 'user' ])) {
			$user_key = '_mprm_order_user_id';
		} else {
			$user_key = '_mprm_order_user_email';
		}
		
		$this->__set('meta_query', array(
			'key' => $user_key,
			'value' => $this->args[ 'user' ]
		));
	}
	
	/**
	 * Search by args
	 */
	public function search() {
		
		if (!isset($this->args[ 's' ])) {
			return;
		}
		
		$search = trim($this->args[ 's' ]);
		
		if (empty($search)) {
			return;
		}
		
		$is_email = is_email($search) || strpos($search, '@') !== false;
		$is_user = strpos($search, strtolower('user:')) !== false;
		
		if (!empty($this->args[ 'search_in_notes' ])) {
			
			$notes = $this->get('payments')->get_payment_notes(0, $search);
			
			if (!empty($notes)) {
				
				$payment_ids = wp_list_pluck((array)$notes, 'comment_post_ID');
				
				$this->__set('post__in', $payment_ids);
			}
			
			$this->__unset('s');
			
		} elseif ($is_email || strlen($search) == 32) {
			
			$key = $is_email ? '_mprm_order_user_email' : '_mprm_order_purchase_key';
			$search_meta = array(
				'key' => $key,
				'value' => $search,
				'compare' => 'LIKE'
			);
			
			$this->__set('meta_query', $search_meta);
			$this->__unset('s');
			
		} elseif ($is_user) {
			
			$search_meta = array(
				'key' => '_mprm_order_user_id',
				'value' => trim(str_replace('user:', '', strtolower($search)))
			);
			
			$this->__set('meta_query', $search_meta);
			
			if ($this->get('settings')->get_option('enable_sequential')) {
				
				$search_meta = array(
					'key' => '_mprm_order_number',
					'value' => $search,
					'compare' => 'LIKE'
				);
				
				$this->__set('meta_query', $search_meta);
				
				$this->args[ 'meta_query' ][ 'relation' ] = 'OR';
				
			}
			
			$this->__unset('s');
			
		} elseif (
			$this->get('settings')->get_option('enable_sequential') &&
			(
				false !== strpos($search, $this->get('settings')->get_option('sequential_prefix')) ||
				false !== strpos($search, $this->get('settings')->get_option('sequential_postfix'))
			)
		) {
			
			$search_meta = array(
				'key' => '_mprm_order_number',
				'value' => $search,
				'compare' => 'LIKE'
			);
			
			$this->__set('meta_query', $search_meta);
			$this->__unset('s');
			
		} elseif (is_numeric($search)) {
			
			$post = get_post($search);
			
			if (is_object($post) && in_array($post->post_type, $this->post_types)) {
				
				$arr = array();
				$arr[] = $search;
				$this->__set('post__in', $arr);
				$this->__unset('s');
			}
			
		} elseif ('#' == substr($search, 0, 1)) {
			
			$search = str_replace('#:', '', $search);
			$search = str_replace('#', '', $search);
			$this->__set('menu_item', $search);
			$this->__unset('s');
			
		} elseif (0 === strpos($search, 'discount:')) {
			
			$search = trim(str_replace('discount:', '', $search));
			$search = 'discount.*' . $search;
			
			$search_meta = array(
				'key' => '_mprm_order_meta',
				'value' => $search,
				'compare' => 'REGEXP',
			);
			
			$this->__set('meta_query', $search_meta);
			$this->__unset('s');
			
		} else {
			$this->__set('s', $search);
		}
		
	}
	
	/**
	 * Payment Mode
	 *
	 * @access public
	 * @since 1.8
	 * @return void
	 */
	public function mode() {
		if (empty($this->args[ 'mode' ]) || $this->args[ 'mode' ] == 'all') {
			$this->__unset('mode');
			
			return;
		}
		
		$this->__set('meta_query', array(
			'key' => '_mprm_order_mode',
			'value' => $this->args[ 'mode' ]
		));
	}
	
	/**
	 * Children
	 *
	 * @access public
	 * @since 1.8
	 * @return void
	 */
	public function children() {
		if (empty($this->args[ 'children' ])) {
			$this->__set('post_parent', 0);
		}
		$this->__unset('children');
	}
	
	/**
	 * @param string $where
	 *
	 * @return string
	 */
	public function count_where($where = '') {
		// Only get payments in our date range
		
		$start_where = '';
		$end_where = '';
		
		if ($this->start_date) {
			
			if ($this->timestamp) {
				$format = 'Y-m-d H:i:s';
			} else {
				$format = 'Y-m-d 00:00:00';
			}
			
			$start_date = date($format, $this->start_date);
			$start_where = " AND p.post_date >= '{$start_date}'";
		}
		
		if ($this->end_date) {
			
			if ($this->timestamp) {
				$format = 'Y-m-d H:i:s';
			} else {
				$format = 'Y-m-d 23:59:59';
			}
			
			$end_date = date($format, $this->end_date);
			
			$end_where = " AND p.post_date <= '{$end_date}'";
		}
		
		$where .= "{$start_where}{$end_where}";
		
		return $where;
	}
	
	/**
	 * @param string $where
	 *
	 * @return string
	 */
	public function payments_where($where = '') {
		
		global $wpdb;
		
		$start_where = '';
		$end_where = '';
		
		if (!is_wp_error($this->start_date)) {
			
			if ($this->timestamp) {
				$format = 'Y-m-d H:i:s';
			} else {
				$format = 'Y-m-d 00:00:00';
			}
			
			$start_date = date($format, $this->start_date);
			$start_where = " AND $wpdb->posts.post_date >= '{$start_date}'";
		}
		
		if (!is_wp_error($this->end_date)) {
			
			if ($this->timestamp) {
				$format = 'Y-m-d 00:00:00';
			} else {
				$format = 'Y-m-d 23:59:59';
			}
			
			$end_date = date($format, $this->end_date);
			
			$end_where = " AND $wpdb->posts.post_date <= '{$end_date}'";
		}
		
		$where .= "{$start_where}{$end_where}";
		
		return $where;
	}
}