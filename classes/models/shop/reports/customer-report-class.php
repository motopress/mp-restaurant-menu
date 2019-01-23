<?php
namespace mp_restaurant_menu\classes\models;

// Load WP_List_Table if not loaded
if (!class_exists('WP_List_Table')) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}


/**
 * Class Customer_Reports
 * @package mp_restaurant_menu\classes\models
 */
class Customer_Reports extends \WP_List_Table {
	protected static $instance;
	/**
	 * Number of items per page
	 *
	 * @var int
	 * @since 1.5
	 */
	public $per_page = 30;
	/**
	 * Number of customers found
	 *
	 * @var int
	 * @since 1.7
	 */
	public $count = 0;
	/**
	 * Total customers
	 *
	 * @var int
	 * @since 1.95
	 */
	public $total = 0;

	/**
	 * Get things started
	 *
	 * @since 1.5
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		// Set parent defaults
		parent::__construct(array(
			'singular' => __('Customer', 'mp-restaurant-menu'),
			'plural' => __('Customers', 'mp-restaurant-menu'),
			'ajax' => false,
		));

	}

	/**
	 * @return Customer_Reports
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Show the search field
	 *
	 * @since 1.7
	 * @access public
	 *
	 * @param string $text Label for the search box
	 * @param string $input_id ID of the search box
	 *
	 * @return void
	 */
	public function search_box($text, $input_id) {
		$input_id = $input_id . '-search-input';

		if (!empty($_REQUEST['orderby']))
			echo '<input type="hidden" name="orderby" value="' . esc_attr($_REQUEST['orderby']) . '" />';
		if (!empty($_REQUEST['order']))
			echo '<input type="hidden" name="order" value="' . esc_attr($_REQUEST['order']) . '" />';
		?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>"/>
			<?php submit_button($text, 'button', false, false, array('ID' => 'search-submit')); ?>
		</p>
		<?php
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @access public
	 * @since 1.5
	 *
	 * @param array $item Contains all the data of the customers
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default($item, $column_name) {
		switch ($column_name) {
			case 'num_purchases' :
				$value = '<a href="' .
					admin_url('/edit.php?post_type=mprm_order&s=' . urlencode($item['email'])
					) . '">' . esc_html($item['num_purchases']) . '</a>';
				break;
			case 'amount_spent' :
				$value = mprm_currency_filter(mprm_format_amount($item[$column_name]));
				break;
			case 'telephone' :
				$value = $item[$column_name];
				break;
			case 'date_created' :
				$value = date_i18n(get_option('date_format'), strtotime($item['date_created']));
				break;
			case 'user_id' :
				$value = ((int)$item['user_id'] > 0) ? $item['user_id'] : '&ndash;';
				break;
			default:
				$value = isset($item[$column_name]) ? $item[$column_name] : null;
				break;
		}
		return apply_filters('mprm_customers_column_' . $column_name, $value, $item['id']);
	}

	/**
	 * @param $item
	 *
	 * @return string
	 */
	public function column_name($item) {
		$name = '#' . $item['id'] . ' ';
		$name .= !empty($item['name']) ? $item['name'] : '<em>' . __('Unnamed Customer', 'mp-restaurant-menu') . '</em>';
//		$user = !empty($item['user_id']) ? $item['user_id'] : $item['email'];
		$view_url = admin_url('edit.php?post_type=mp_menu_item&page=mprm-customers&view=overview&id=' . $item['id']);
		$actions = array(
			'edit' => '<a href="' . $view_url . '">' . __('Edit', 'mp-restaurant-menu') . '</a>',
//			'logs' => '<a href="' . admin_url('edit.php?post_type=mp_menu_item&page=mprm-reports&tab=logs&user=' . urlencode($user)) . '">' . __('Menu item log', 'mp-restaurant-menu') . '</a>',
			'delete' => '<a href="' . admin_url('edit.php?post_type=mp_menu_item&page=mprm-customers&view=delete&id=' . $item['id']) . '">' . __('Delete', 'mp-restaurant-menu') . '</a>'
		);

		$customer = new Customer(array('field' => 'id', 'value' => $item['id']));
		$pending = mprm_user_pending_verification($customer->user_id) ? ' <em>' . __('(Pending Verification)', 'mp-restaurant-menu') . '</em>' : '';

		return '<a href="' . esc_url($view_url) . '">' . $name . '</a>' . $pending . $this->row_actions($actions);
	}

	/**
	 * Outputs the reporting views
	 *
	 * @access public
	 * @since 1.5
	 *
	 * @param string $which
	 */
	public function bulk_actions($which = '') {
		// These aren't really bulk actions but this outputs the markup in the right place
	}

	/**
	 *
	 */
	public function prepare_items() {

		$columns = $this->get_columns();
		$hidden = array(); // No hidden columns
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array($columns, $hidden, $sortable);

		$this->items = $this->reports_data();

		$this->total = count(Customer::get_instance()->get_customers(array('fields' => array('id'), 'number' => NULL)));

		$this->set_pagination_args(array(
			'total_items' => $this->total,
			'per_page' => $this->per_page,
			'total_pages' => ceil($this->total / $this->per_page),
		));
	}

	/**
	 * Retrieve the table columns
	 *
	 * @access public
	 * @since 1.5
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'name' => __('Name', 'mp-restaurant-menu'),
			'email' => __('Email', 'mp-restaurant-menu'),
			'telephone' => __('Phone', 'mp-restaurant-menu'),
			'num_purchases' => __('Purchases', 'mp-restaurant-menu'),
			'amount_spent' => __('Total Spent', 'mp-restaurant-menu'),
			'date_created' => __('Date Created', 'mp-restaurant-menu'),
			'user_id' => __('User ID', 'mp-restaurant-menu'),
		);

		return apply_filters('mprm_report_customer_columns', $columns);

	}

	/**
	 * Get the sortable columns
	 *
	 * @access public
	 * @since 2.1
	 * @return array Array of all the sortable columns
	 */
	public function get_sortable_columns() {
		return array(
			'date_created' => array('date_created', true),
			'name' => array('name', true),
			'num_purchases' => array('purchase_count', false),
			'amount_spent' => array('purchase_value', false),
		);
	}

	/**
	 * Build all the reports data
	 *
	 * @access public
	 * @since 1.5
	 * @global object $wpdb Used to query the database using the WordPress
	 *   Database API
	 * @return array $reports_data All the data for customer reports
	 */
	public function reports_data() {
		$data = array();
		$paged = $this->get_paged();
		$offset = $this->per_page * ($paged - 1);
		$search = $this->get_search();
		$order = isset($_REQUEST['order']) ? sanitize_text_field($_REQUEST['order']) : 'DESC';
		$orderby = isset($_REQUEST['orderby']) ? sanitize_text_field($_REQUEST['orderby']) : 'id';

		$args = array(
			'number' => $this->per_page,
			'offset' => $offset,
			'order' => $order,
			'orderby' => $orderby
		);

		if (is_email($search)) {
			$args['search_by'] = 'email';
			$args['search_value'] = $search;
		} elseif (is_numeric($search)) {
			$args['search_by'] = 'id';
			$args['search_value'] = $search;
		} elseif (strpos($search, 'user:') !== false) {
			$args['search_value'] = trim(str_replace('user:', '', $search));
			$args['search_by'] = 'user_id';
		} else {
			$args['search_value'] = $search;
			$args['search_by'] = 'name';
		}

		$args['fields'] = array('*');

		$customers = Customer::get_instance()->get_customers($args);

		if ($customers) {

			foreach ($customers as $customer) {

				$user_id = !empty($customer->user_id) ? intval($customer->user_id) : 0;

				$data[] = array(
					'id' => $customer->id,
					'user_id' => $user_id,
					'name' => $customer->name,
					'email' => $customer->email,
					'telephone' => $customer->telephone,
					'num_purchases' => $customer->purchase_count,
					'amount_spent' => $customer->purchase_value,
					'date_created' => $customer->date_created,
				);
			}
		}

		return $data;
	}

	/**
	 * Retrieve the current page number
	 *
	 * @access public
	 * @since 1.5
	 * @return int Current page number
	 */
	public function get_paged() {
		return isset($_REQUEST['paged']) ? absint($_REQUEST['paged']) : 1;
	}

	/**
	 * Retrieves the search query string
	 *
	 * @access public
	 * @since 1.7
	 * @return mixed string If search is present, false otherwise
	 */
	public function get_search() {
		return !empty($_REQUEST['s']) ? urldecode(trim($_REQUEST['s'])) : false;
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @since 2.5
	 * @access protected
	 *
	 * @return string Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'name';
	}
}