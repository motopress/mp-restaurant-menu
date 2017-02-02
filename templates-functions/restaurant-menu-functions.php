<?php
use mp_restaurant_menu\classes;use mp_restaurant_menu\classes\Core;use mp_restaurant_menu\classes\models;use mp_restaurant_menu\classes\modules\Breadcrumbs;use mp_restaurant_menu\classes\View;

/**
 * @return array|bool
 */
function mprm_get_attributes() {
	global $post;
	$attributes = models\Menu_item::get_instance()->get_attributes($post);
	$empty_attributes = models\Menu_item::get_instance()->is_arr_values_empty($attributes);
	if ($empty_attributes) {
		return false;
	} else {
		return $attributes;
	}
}

/**
 * @return array|bool
 */
function mprm_get_ingredients() {
	global $post;
	$ingredients = models\Ingredient::get_instance()->get_ingredients($post->ID);
	$empty_ingredients = models\Menu_item::get_instance()->is_arr_values_empty($ingredients);
	if ($empty_ingredients) {
		return false;
	} else {
		return $ingredients;
	}
}

/**
 * @return bool|mixed
 */
function mprm_get_nutritional() {
	global $post;
	$nutritional = get_post_meta($post->ID, 'nutritional', true);
	$empty_nutritional = models\Menu_item::get_instance()->is_arr_values_empty($nutritional);
	if ($empty_nutritional) {
		return false;
	} else {
	    return $nutritional;
	}
}

/**
 * @return bool|models\array
 */
function mprm_get_related_items() {
	global $post;
	$related_items = models\Menu_item::get_instance()->get_related_items($post->ID);
	$empty_related_items = models\Menu_item::get_instance()->is_arr_values_empty($related_items);
	if ($empty_related_items) {
		return false;
	} else {
		return $related_items;
	}
}

/**
 * @return array
 */
function mprm_get_gallery() {
	global $post;
	if (empty($post)) {
		return array();
	}
	return Core::get_instance()->get('menu_item')->get_gallery($post->ID);
}

/**
 * @return string
 */
function mprm_get_price() {
	global $post;
	return models\Menu_item::get_instance()->get_price($post->ID, false);
}

/**
 * @return array|object
 */
function mprm_get_tags() {
	global $post;
	return wp_get_post_terms($post->ID, 'mp_menu_tag');
}

/**
 * Get image thumbnail
 *
 * @param string $thumbnail
 *
 *@return string
 */

function mprm_get_item_image($thumbnail ='mprm-big') {
	global $post;
	return wp_get_attachment_image(get_post_thumbnail_id($post->ID), $thumbnail, false, array('class' => apply_filters('mprm-item-image', "mprm-image")));
}

/**
 * Get current category options
 *
 * @global array $mprm_category
 *
 * @param bool /int $id
 *
 * @return array
 */
function mprm_get_category_options($id = false) {
	if ($id) {
		return models\Menu_category::get_instance()->get_term_params($id);
	} else {
		$term = mprm_get_taxonomy();
		if (!empty($term)) {
			return models\Menu_category::get_instance()->get_term_params($term->term_id);
		} else {
			return array();
		}
	}
}

/**
 * Get Menu item options
 *
 * @global array $mprm_menu_item
 *
 * @param object $post
 *
 * @return array
 */
function mprm_get_menu_item_options($post = NULL) {
	global $mprm_menu_item, $mprm_menu_items,$mprm_view_args;

	$post = empty($post) ? $mprm_menu_item : $post;

	if (empty($post)) {
		return array();
	}

	if (empty($mprm_menu_items)) {
		$mprm_menu_items = array();
	}

	if (empty($mprm_menu_items[$mprm_view_args['view']][$post->ID])) {
		$mprm_menu_items[$mprm_view_args['view']][$post->ID] = classes\Core::get_instance()->get('menu_item')->get_menu_item_option($post);
	}

	return $mprm_menu_items[$mprm_view_args['view']][$post->ID];
}

/**
 * Get category
 *
 * @global string $taxonomy
 * @return object WP_Term
 */
function mprm_get_taxonomy() {
	global $term, $taxonomy, $mprm_term;
	if (empty($mprm_term) || $term != $mprm_term->slug) {
		$mprm_term = get_term_by('slug', $term, $taxonomy);
	}
	return $mprm_term;
}

/**
 * Get categories
 */
function mprm_get_categories() {
	global $mprm_view_args, $mprm_categories;

	$ids = !empty($mprm_view_args['categ']) ? $mprm_view_args['categ'] : 0;
	if (!empty($ids) || preg_match('/category/', $mprm_view_args['action_path'])) {
		$mprm_categories = Core::get_instance()->get('menu_category')->get_categories_by_ids($ids);
	} else {
		$mprm_categories = array();
	}
	return $mprm_categories;
}
/**
 * Get term children
 *
*@param $taxonomy
 * @param $term_id
 *
 * @return array
 */
function mprm_get_term_children( $term_id, $taxonomy){

	$terms = Core::get_instance()->get('menu_category')->get_term_children($term_id, $taxonomy);

	foreach ($terms  as $key => $term){
		$terms[$key] = get_term($term);
	}

	return $terms;
}

/**
 * Set current term
 *
 * @param array $term
 */
function mprm_set_current_term($term = array()) {
	global $mprm_term;
	if (!empty($term)) {
		$mprm_term = $term;
	}
}

/**
 * Set current menu item
 *
 * @param int $id
 */
function mprm_set_menu_item($id) {
	global $post, $mprm_menu_item;
	if (!empty($id)) {
		$post = $mprm_menu_item = get_post($id);
	}
}

/**
 * Get category view
 */
function get_mprm_taxonomy_view() {
	$settings = models\Settings::get_instance()->get_settings();
	if (!empty($settings['category_view'])) {
		return $settings['category_view'];
	} else {
		return 'grid';
	}
}

/**
 * Is category grid
 */
function is_mprm_taxonomy_grid() {
	if ('grid' == get_mprm_taxonomy_view()) {
		return true;
	} else {
		return false;
	}
}

/**
 * Get category menu items
 *
 * @global array $mprm_terms_items
 * @global array $mprm_view_args
 * @global object $mp_menu_tag
 * @global object $mp_menu_category
 * @return array
 */
function mprm_get_term_menu_items() {
	global $taxonomy, $mprm_view_args;

	$mprm_view_args['buy'] = (!isset($mprm_view_args['buy']) ? '1' : $mprm_view_args['buy']);

	if (empty($mprm_view_args)) {
		$mprm_view_args = array();
		if ($taxonomy === 'mp_menu_category') {
			$mprm_view_args['categ'] = mprm_get_taxonomy()->term_id;
		}
		if ($taxonomy === 'mp_menu_tag') {
			$mprm_view_args['tags_list'] = mprm_get_taxonomy()->term_id;
		}
	}

	$mprm_items = models\Menu_item::get_instance()->get_menu_items($mprm_view_args);

	return $mprm_items;
}

/**
 * @return array
 */
function mprm_get_menu_items_by_term() {
	global $taxonomy;
	$params = array();
	if ($taxonomy === 'mp_menu_category') {
		$params['categ'] = mprm_get_taxonomy()->term_id;
	}
	if ($taxonomy === 'mp_menu_tag') {
		$params['tags_list'] = mprm_get_taxonomy()->term_id;
	}
	$mprm_items = models\Menu_item::get_instance()->get_menu_items($params);
	return $mprm_items;
}

/**
 * Get template
 *
 * @param $template
 * @param null $data
 * @param bool $output
 */
function mprm_get_template($template, $data = null, $output = true) {
	if ($output) {
		echo classes\View::get_instance()->get_template_html($template, $data);
	} else {
		classes\View::get_instance()->get_template($template, $data);
	}
}

/**
 * Template part
 *
 * @param $name
 * @param string $slug
 */
function mprm_get_template_part($slug, $name = '') {
	classes\View::get_instance()->get_template_part($slug, $name);
}

/**
 * Before widget
 *
 * @global array $mprm_widget_args
 */
function before_mprm_widget() {
	global $mprm_widget_args;
	if (!empty($mprm_widget_args)) {
		echo $mprm_widget_args['before_widget'];
	}
}

/**
 * The widget title
 *
 * @global array $mprm_widget_args
 * @global array $mprm_view_args
 */
function the_mprm_widget_title() {
	global $mprm_widget_args, $mprm_view_args;
	if (!empty($mprm_widget_args) && !empty($mprm_view_args['title'])) {
		echo $mprm_widget_args['before_title'] . $mprm_view_args['title'] . $mprm_widget_args['after_title'];
	}
}

/**
 * After widget title
 *
 * @global array $mprm_widget_args
 */
function after_mprm_widget() {
	global $mprm_widget_args;
	if (!empty($mprm_widget_args)) {
		echo $mprm_widget_args['after_widget'];
	}
}

/**
 * Render current action
 */
function render_current_html() {
	global $mprm_view_args;
	if (!empty($mprm_view_args['action_path'])) {
		View::get_instance()->get_template($mprm_view_args['action_path']);
	}
}

/**
 * Get categories
 *
 * @global array $mprm_view_args
 * @return array
 */
function get_mprm_tags() {
	global $mprm_view_args, $mprm_tags;
	if (!empty($mprm_view_args['tags_list'])) {
		if (is_array($mprm_view_args['tags_list'])) {
			$tag_ids = $mprm_view_args['tags_list'];
		} else {
			$tag_ids = explode(',', $mprm_view_args['tags_list']);
		}
	}
	if (!empty($tag_ids)) {
		$mprm_tags = Core::get_instance()->get('menu_tag')->get_tags_by_ids($tag_ids);
	} else {
		$mprm_tags = array();
	}
	return $mprm_tags;
}

/**
 * Get current ID category
 *
 * @global array $mprm_category
 * @return int
 */
function get_mprm_tag_ID() {
	global $mprm_tag;
	if (!empty($mprm_tag->term_id)) {
		return $mprm_tag->term_id;
	} else {
		return 0;
	}
}

/**
 * Get tag
 *
 * @global string $mp_menu_tag
 * @global string $taxonomy
 * @return object WP_Term
 */
function get_mprm_tag() {
	global $mp_menu_tag, $taxonomy;
	return get_term_by('slug', $mp_menu_tag, $taxonomy);
}

/**
 * Init current tag
 *
 * @global array $mprm_tag
 *
 * @param int $id
 */
function set_mprm_tag($id) {
	if (!empty($id)) {
		global $mprm_tag;
		$mprm_tag = get_term($id);
	}
}

/**
 * Get current ID category
 *
 * @global array $mprm_category
 * @return int
 */
function get_mprm_cat_ID() {
	global $mprm_category;
	if (!empty($mprm_category->term_id)) {
		return $mprm_category->term_id;
	} else {
		return 0;
	}
}

/**
 * Get menu item menu ID
 *
 * @global array $mprm_menu_item
 * @return int
 */
function get_mprm_menu_item_ID() {
	global $mprm_menu_item;
	if (!empty($mprm_menu_item->ID)) {
		return $mprm_menu_item->ID;
	} else {
		return 0;
	}
}

/**
 * @param $params
 *
 * @return mixed
 */
function mprm_get_purchase_link($params) {
	return models\Menu_item::get_instance()->get_purchase_link($params);
}

/**
 * Get purchase template
 *
 * @param string $template
 */
function mprm_get_purchase_template($template = "default") {
	$template = empty($template) ? "default" : $template;
	mprm_get_template("common/buy/{$template}");
}

/**
 * @global array $mprm_view_args
 * @global array $mprm_term
 */
function mprm_category_list_item() {
	global $mprm_view_args, $mprm_term;
	$term_options = mprm_get_category_options();
	mprm_get_template('shortcodes/category/list/item', array('term_options' => $term_options, 'mprm_view_args' => $mprm_view_args, 'mprm_term' => $mprm_term));
}

function mprm_taxonomy_grid_item() {
	global $mprm_view_args, $mprm_term;
	$term_options = mprm_get_category_options();
	mprm_get_template('shortcodes/category/grid/item', array('term_options' => $term_options, 'mprm_view_args' => $mprm_view_args, 'mprm_term' => $mprm_term));
}

function mprm_shortcode_grid_item() {
	global $mprm_view_args, $mprm_term;
	$term_options = mprm_get_category_options();
	mprm_get_template('shortcodes/category/grid/item', array('term_options' => $term_options, 'mprm_view_args' => $mprm_view_args, 'mprm_term' => $mprm_term));
}

/**
 * Before category list
 */
function mprm_single_category_list_header() {	?>
	<div class="mprm-category-item-list mprm-column mprm-column-3">
	<div class="mprm">
	<?php
}

function mprm_taxonomy_list_before_right() {	?>
	<div class="mprm-content mprm-columns mprm-seven">
<?php }

function mprm_taxonomy_list_after_right() { ?>
	</div>
	<?php
}

function mprm_taxonomy_list_image() {
	mprm_get_template('common/item-image',array('thumbnail'=> 'mprm-big'));
}

function mprm_taxonomy_list_header_title() {
	mprm_get_template('common/item-title');
}

function mprm_taxonomy_list_ingredients() {
	mprm_get_template('common/ingredients', array('mprm_title_ingredients' => false));
}

function mprm_taxonomy_list_tags() {
	global $taxonomy;
	if ($taxonomy != 'mp_menu_tag') {
		mprm_get_template('common/item-tags');
	}
}

function mprm_taxonomy_list_price() {
	mprm_get_template('common/price');
}

/**
 * Single category list footer
 */
function mprm_single_category_list_footer() {	?>
	</div></div>
	<?php
}

/**
 * Category grid header
 */
function mprm_single_category_grid_header() {
	global $post;
	if($post->ID !== $GLOBALS['id']){
		$post = get_post($GLOBALS['id']);
		setup_postdata($post);
	}
	?>
	<div <?php post_class('mprm-remove-hentry mprm-four mprm-columns') ?>>
	<?php
}

/**
 * Category grid image
 */
function mprm_single_category_grid_image() {
	mprm_get_template('common/menu-item-image');
}

/**
 * Category grid description
 */
function mprm_single_category_grid_wrapper_start() {	?>
	<div class="mprm-description">
	<?php
}
function mprm_single_category_grid_wrapper_end() { ?>
	</div>
	<?php
}
/**
 * Category grid title
 */
function mprm_single_category_grid_title() {
	mprm_get_template('common/item-title');
}

/**
 * Category grid ingredients
 */
function mprm_single_category_grid_ingredients() {
	mprm_get_template('common/ingredients', array('mprm_title_ingredients' => false));
}

/**
 * Category grid tags
 */
function mprm_single_category_grid_tags() {
	global $taxonomy;
	if ($taxonomy != 'mp_menu_tag') {
		mprm_get_template('common/item-tags');
	}
}

/**
 * Category grid price
 */
function mprm_single_category_grid_price() {
	mprm_get_template('common/price');
}

function mprm_single_category_grid_footer() {	?>
	</div>
	<?php
}

/**
 * Category header
 */
function mprm_category_header() {
	mprm_get_template('common/taxonomy-header');
}

/**
 * Single tag list header
 */
function mprm_single_tag_list_header() {	?>
	<div class="mprm-element-list mprm-item mprm-column mprm-column-3 mprm-grid">
	<?php
}

/**
 * Single tag list content
 */
function mprm_single_tag_list_content() {
	$price = Core::get_instance()->get('menu_item')->get_price(get_the_ID(), true);
	$ingredients = Core::get_instance()->get('ingredient')->get_ingredients(get_the_ID());
	?>
	<div class="mprm-element-description">
		<h2 class="mprm-element-title"><?php the_title() ?></h2>
		<span class="mprm-element-price"><?php echo $price; ?></span>
		<?php if (!empty($ingredients)): ?>
			<div class="mprm-breadcrumbs">
				<?php foreach ($ingredients as $val): ?>
					<span class="mprm-element-breadcrumbs"><?php echo $val->name ?></span>
					<?php if (end($ingredients) !== $val): ?><span class="mprm-element-separator">/</span><?php endif; ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Single after tag list
 */
function mprm_single_tag_list_footer() {?>
	</div>
	<?php
}

/**
 * Shortcode header
 */
function mprm_menu_items_header() {
	global $mprm_view_args, $mprm_term;
	$title = !empty($mprm_term) ? $mprm_term->name : '';
	if ('none' !== $mprm_view_args['categ_name']) {
		$icon = mprm_get_category_icon();
		if (mprm_has_category_image() && ('with_img' == $mprm_view_args['categ_name'])) {
			?>
			<div class="mprm-header with-image" style="background-image: url('<?php echo (mprm_has_category_image()) ? mprm_get_category_image('large') : 'none'; ?>')">
				<?php if (!empty($icon)): ?>
					<i class="<?php echo $icon ?> mprm-icon"></i>
				<?php endif; ?>
				<h2 class="mprm-title"><?php echo $title ?></h2>
			</div>
		<?php } else { ?>
			<div class="mprm-header only-text">
				<h2 class="mprm-title"><?php echo $title ?></h2>
			</div>
			<?php
		}
	}
}

/**
 * Menu item Grid header
 */
function mprm_menu_item_list_header() {
	global $mprm_view_args;
	if (!empty($mprm_view_args['col']) && !empty($mprm_view_args['view'])) {?>
		<div <?php post_class('mprm-remove-hentry ' . get_column_class($mprm_view_args['col'])); ?>>
		<?php
	}
}

/**
 * Shortcode Flat view header
 */
function mprm_menu_item_simple_list_header() {
	global $mprm_view_args;
	if (!empty($mprm_view_args['col']) && !empty($mprm_view_args['view'])) {
		if (!empty($mprm_view_args['categ']) || !empty($mprm_view_args['tags_list'])){
			?>
			<div <?php post_class('mprm-remove-hentry '); ?> >
			<?php
			}else{ ?>
			<div <?php post_class('mprm-remove-hentry ' . get_column_class($mprm_view_args['col'])); ?>>
		<?php }
	}
}

/**
 * Menu item Grid image
 *
 * @global array $mprm_view_args
 */
function mprm_menu_item_list_image() {
	$post_options = mprm_get_menu_item_options();
	if (mprm_get_feat_image() && !empty($post_options['image'])) {
		mprm_get_template('common/list-item-image', array('image' => $post_options['image']));
	}
}

/**
 * Menu item list right header
 *
 * @global array $mprm_view_args
 */
function mprm_menu_item_list_right_header() {
	$post_options = mprm_get_menu_item_options();
	$feat_img = mprm_get_feat_image();
	?>
	<div class="mprm-side<?php echo $feat_img ? ' mprm-right-side ' : ''; ?><?php echo (!$feat_img || empty($post_options['image'])) ? " mprm-full-with" : " mprm-columns mprm-seven"; ?>">
	<?php
}
/**
 * Is menu item image
 *
 * @return bool
 */
function mprm_is_menu_item_image() {
	global $post;
	
	if(is_tax()){

		mprm_set_menu_item($post->ID);
		$post_options = mprm_get_menu_item_options();

		if(!empty($post_options['image'])){
			$is_menu_item_image = true;
		}else{
		    $is_menu_item_image = false;
		}

	}else{
		$post_options = mprm_get_menu_item_options();
		$feat_img = mprm_get_feat_image();

		if ( !empty($post_options['image'])) {
			if(isset($feat_img) && $feat_img){
				$is_menu_item_image = true;
			}else{
			    $is_menu_item_image = false;
			}
		} else {
		    $is_menu_item_image = false;
		}
	}
		return $is_menu_item_image;
}

/**
 * Get featured image
 *
 * @return bool
 */
function mprm_get_feat_image() {
	global $mprm_view_args;
	$feat_img = empty($mprm_view_args['feat_img']) ? false : $mprm_view_args['feat_img'];
	return $feat_img;
}

/**
 * Menu item Grid tags
 *
 * @global array $mprm_view_args
 */
function mprm_menu_item_list_tags() {
	global $mprm_view_args;
	$post_options = mprm_get_menu_item_options();
	if (!empty($post_options['tags']) && !empty($mprm_view_args['tags'])) {
		mprm_get_template('common/item-tags', array('tags' => $post_options['tags']));
	}
}

/**
 * Menu item Grid title
 *
 * @global array $mprm_view_args
 * @global array $mprm_menu_item
 */
function mprm_menu_item_list_title() {
	global $mprm_view_args, $mprm_menu_item;
	mprm_get_template('common/item-shortcode-title', array('mprm_menu_item' => $mprm_menu_item, 'mprm_view_args' => $mprm_view_args));
}

function mprm_menu_item_list_title_simple() {
	global $mprm_view_args, $mprm_menu_item;
	mprm_get_template('common/item-shortcode-title-simple', array('mprm_menu_item' => $mprm_menu_item, 'mprm_view_args' => $mprm_view_args));
}

/**
 * Menu item Grid ingredients
 *
 *
 */
function mprm_menu_item_list_ingredients() {
	global $mprm_view_args;
	$post_options = mprm_get_menu_item_options();
	if (!empty($post_options['ingredients']) && !empty($mprm_view_args['ingredients'])) {
		mprm_get_template('common/ingredients', array('ingredients' => $post_options['ingredients'], 'mprm_title_ingredients' => true));
	}
}

/**
 * Menu item list attributes
 *
 * @global array $mprm_view_args
 */
function mprm_menu_item_list_attributes() {
	global $mprm_view_args;
	$post_options = mprm_get_menu_item_options();
	if (!empty($post_options['attributes']) && !empty($mprm_view_args['show_attributes'])) {
		mprm_get_template('common/attributes', array('attributes' => $post_options['attributes'], 'mprm_title_attributes' => true));
	}
}

/**
 * Menu item Grid excerpt
 */
function mprm_menu_item_list_excerpt() {
	global $mprm_view_args;
	$post_options = mprm_get_menu_item_options();
	if (!empty($post_options['excerpt']) && !empty($mprm_view_args['excerpt'])) {
		$desc_length = !empty($mprm_view_args['desc_length']) ? $mprm_view_args['desc_length'] : -1;
		$excerpt = mprm_cut_str($desc_length, $post_options['excerpt']);
		mprm_get_template('common/excerpt', array('excerpt' => $excerpt));
	}
}

/**
 * Menu item Grid price
 */
function mprm_menu_item_list_price() {
	global $mprm_view_args;
	$post_options = mprm_get_menu_item_options();
	if (!empty($post_options['product_price']) && !empty($mprm_view_args['price'])) {
		mprm_get_template('common/price', array('price' => $post_options['product_price']));
	}
}

/**
 * Menu item list right footer
 */
function mprm_menu_item_list_right_footer() {	?>
	</div>
	<?php
}

/**
 * Menu item list footer
 */
function mprm_menu_item_list_footer() {	?>
	</div>
	<?php
}

/**
 *Menu item simple list footer
 */
function mprm_menu_item_simple_list_footer() {	?>
	</div>
	<?php
}

/**
 * Menu item Grid header
 */
function mprm_menu_item_grid_header() {
	global $mprm_view_args;
	if (!empty($mprm_view_args['col']) && !empty($mprm_view_args['view'])) {
		?>
		<div <?php post_class('mprm-remove-hentry ' . get_column_class($mprm_view_args['col'])); ?>>
		<?php
	}
}

function mprm_menu_item_before_content(){}

function mprm_menu_item_after_content(){}

function mprm_category_menu_item_before_content(){	 }

function mprm_category_menu_item_after_content(){ }

/**
 * Menu item Grid image
 *
 * @global array $mprm_view_args
 */
function mprm_menu_item_grid_image() {
	global $mprm_view_args;
	$post_options = mprm_get_menu_item_options();

	if (mprm_get_feat_image() && !empty($post_options['image'])) {
			 if (!empty($mprm_view_args['link_item'])) { ?>
				<a href="<?php the_permalink() ?>">
			<?php }

		 	echo $post_options['image'];

		 	 if (!empty($mprm_view_args['link_item'])) { ?>
				</a>
			<?php }
	}
}

/**
 * Menu item Grid tags
 *
 * @global array $mprm_view_args
 */
function mprm_menu_item_grid_tags() {
	global $mprm_view_args;
	$post_options = mprm_get_menu_item_options();
	if (!empty($post_options['tags']) && !empty($mprm_view_args['tags'])) {
		mprm_get_template('common/item-tags', array('tags' => $post_options['tags']));
	}
}

/**
 * Menu item Grid title
 *
 * @global array $mprm_view_args
 * @global array $mprm_menu_item
 */
function mprm_menu_item_grid_title() {
	global $mprm_view_args,$mprm_menu_item;
	mprm_get_template('common/item-shortcode-title', array('mprm_menu_item' => $mprm_menu_item, 'mprm_view_args' => $mprm_view_args));
}

/**
 * Menu item Grid ingredients
 *
 */
function mprm_menu_item_grid_ingredients() {
	global $mprm_view_args;
	$post_options = mprm_get_menu_item_options();
	if (!empty($post_options['ingredients']) && !empty($mprm_view_args['ingredients'])) {
		mprm_get_template('common/ingredients', array('ingredients' => $post_options['ingredients'], 'mprm_title_ingredients' => true));
	}
}

/**
 * Menu item attributes
 *
 * @global array $mprm_view_args
 */
function mprm_menu_item_grid_attributes() {
	global $mprm_view_args;
	$post_options = mprm_get_menu_item_options();
	if (!empty($post_options['attributes']) && !empty($mprm_view_args['show_attributes'])) {
		mprm_get_template('common/attributes', array('attributes' => $post_options['attributes'], 'mprm_title_attributes' => true));
	}
}

/**
 * Menu item Grid excerpt
 */
function mprm_menu_item_grid_excerpt() {
	global $mprm_view_args;
	$post_options = mprm_get_menu_item_options();
	if (!empty($post_options['excerpt']) && !empty($mprm_view_args['excerpt'])) {
		$desc_length = !empty($mprm_view_args['desc_length']) ? $mprm_view_args['desc_length'] : -1;
		$excerpt = mprm_cut_str($desc_length, $post_options['excerpt']);
		mprm_get_template('common/excerpt', array('excerpt' => $excerpt));
	}
}

/**
 * Menu item Grid price
 */
function mprm_menu_item_grid_price() {
	global $mprm_view_args;
	$post_options = mprm_get_menu_item_options();
	if (!empty($post_options['product_price']) && !empty($mprm_view_args['price'])) {
		mprm_get_template('common/price', array('price' => $post_options['product_price']));
	}
}

/**
 * Menu item grid footer
 */
function mprm_menu_item_grid_footer() {	?>
	</div>
	<?php
}

/**
 * Before Menu item header
 */
function mprm_before_menu_item_header() {
	$featured_url = mprm_get_attachment_url('large');
	$header_class = 'mprm-header' . (!empty($featured_url) ? ' with-image' : ''); ?>
	<div class="<?php echo apply_filters('mprm-single-item-header-class', $header_class); ?>" <?php if (!empty($featured_url)) : ?>style="background-image: url(<?php echo $featured_url ?>);"<?php endif; ?>>
	<div class="<?php echo apply_filters('mprm-header-content-class', 'mprm-header-content') ?> ">
	<?php
}

/**
 * Shortcode header
 */
function mprm_menu_item_header() { ?>
	<h1 class="mprm-header-title"><?php the_title() ?></h1>
	<?php
	if (apply_filters('mprm-item-breadcrumbs', true)) {
		Breadcrumbs::get_instance()->show_breadcrumbs(array('separator' => '&nbsp;/&nbsp;', 'custom_taxonomy' => 'mp_menu_category', 'home_title' => __('Home', 'mp-restaurant-menu')));
	}
}

/**
 * After menu_item header
 */
function mprm_after_menu_item_header() { ?>
	</div></div>
	<?php
}

/**
 * Menu item gallery
 */
function mprm_menu_item_gallery() {
	$gallery = Core::get_instance()->get('menu_item')->get_gallery(get_the_ID());
	if (!empty($gallery)) {
		?>
		<div class="mprm-item-gallery">
			<?php foreach ($gallery as $img_id):
				$thunbnail_src = wp_get_attachment_image_src($img_id, apply_filters('mprm-item-gallery-size', 'large'));
				?>
				<a href="<?php echo $thunbnail_src[0] ?>" title="<?php echo get_the_title($img_id) ?>">
					<?php echo wp_get_attachment_image($img_id, 'thumbnail', false, array('class' => "mprm-gallery-image")); ?>
				</a>
			<?php endforeach; ?>
			<div class="mprm-clear"></div>
		</div>
		<?php
	}
}

function mprm_purchase_link() {
	global $post;
	if (!empty($post) && $post->post_type == 'mp_menu_item') {
		$data = models\Cart::get_instance()->get_append_purchase_link();
		if (!empty($data)) {
			mprm_get_template("common/buy/{$data['template']}");
		}
	}
}

/**
 * Menu item content price
 */
function mprm_menu_item_price() {
	mprm_get_template('common/price-box');
}

/**
 * Menu item content
 */
function mprm_menu_item_content() {
	?>
	<div class="mprm-post-content">
		<?php the_content(); ?>
	</div>
	<?php
}

/**
 * Menu item content Author
 */
function mprm_menu_item_content_author() {
	get_template_part('author-bio');
}

/**
 * Menu item content comments
 */
function mprm_menu_item_content_comments() {
	if (comments_open() || get_comments_number()) {
		comments_template();
	}
}

/**
 * Before menu item sidebar
 */
function mprm_before_menu_item_sidebar() {
	?>
	<div class="mprm-sidebar">
	<?php
}

/**
 * Menu items proportions
 */
function mprm_menu_item_slidebar_attributes() {
	mprm_get_template('common/attributes');
}

/**
 * Menu item sidebar ingredients
 */
function mprm_menu_item_slidebar_ingredients() {
	mprm_get_template('common/ingredients', array('mprm_title_ingredients' => false));
}

/**
 * Menu item sidebar nutritional
 */
function mprm_menu_item_slidebar_nutritional() {
	mprm_get_template('common/nutritional', array('mprm_title_nutritional' => false));
}

/**
 * Menu item sidebar around items
 */
function mprm_menu_item_slidebar_related_items() {
	mprm_get_template('common/related-items');
}

function mprm_after_menu_item_sidebar() {	?>
	</div>
	<?php
}

/**
 * @param $name
 *
 * @return mixed
 */
function mprm_get_nutrition_label($name) {
	$labels = array(
		'calories' => __('Calories', 'mp-restaurant-menu'),
		'cholesterol' => __('Cholesterol', 'mp-restaurant-menu'),
		'fiber' => __('Fiber', 'mp-restaurant-menu'),
		'sodium' => __('Sodium', 'mp-restaurant-menu'),
		'carbohydrates' => __('Carbohydrates', 'mp-restaurant-menu'),
		'fat' => __('Fat', 'mp-restaurant-menu'),
		'protein' => __('Protein', 'mp-restaurant-menu'),
	);
	if (array_key_exists($name, $labels)){
		return $labels[$name];
	}
	return $name;
}

/**
 * @param $name
 *
 * @return mixed
 */
function mprm_get_proportion_label($name) {
	$labels = array(
		'size' => __('Size', 'mp-restaurant-menu'),
		'bulk' => __('Volume', 'mp-restaurant-menu'),
		'weight' => __('Weight', 'mp-restaurant-menu'),
	);
	if (array_key_exists($name, $labels)){
		return $labels[$name];
	}
	return $name;
}

/**
 * @return bool
 */
function mprm_has_category_image() {
	global $mprm_term;
	return models\Menu_category::get_instance()->has_category_image($mprm_term);
}

/**
 * @param $size
 *
 * @return bool
 */
function mprm_get_category_image($size = 'mprm-big') {
	global $mprm_term;
	$size = apply_filters('mprm_category_image_size',$size);
	return models\Menu_category::get_instance()->get_term_image($mprm_term, $size);
}

/**
 * @return mixed|string
 */
function mprm_get_category_icon() {
	global $mprm_term;
	return models\Menu_category::get_instance()->get_term_icon($mprm_term);
}

/**
 * Get image url by size
 *
 * @param $size
 *
 * @return false|string
 */
function mprm_get_attachment_url($size) {
	global $post;
	$size = apply_filters('mprm_attachment_url_size',$size);
	return models\Menu_item::get_instance()->get_featured_image($post, $size);
}

/**
 * @param $error_id
 * @param $error_message
 */
function mprm_set_error($error_id, $error_message) {
	models\Errors::get_instance()->set_error($error_id, $error_message);
}

/**
 * @return bool|mixed
 */
function mprm_get_error_html() {
	return models\Errors::get_instance()->get_error_html();
}

/**
 * @param $data
 * @param $col
 *
 * @return void
 */
function create_grid_by_posts($data, $col) {
	global $post;
	$i = 1;
	$last_key = array_search(end($data['posts']), $data['posts']);

	foreach ($data['posts'] as $key => $post) :
	
		setup_postdata($post);
	
		if (($key % $col) === 0) {
			$i = 1; ?>
			<div class="<?php echo mprm_grid_row_class() ?>">
		<?php }
		
		mprm_set_menu_item($post->ID);
		
		render_current_html();
		
		if (($i % $col) === 0 || $last_key === $key) { ?>
			</div>
		<?php }
		
		$i++;
		wp_reset_postdata();
		
	endforeach;
}

/**
 * @param $data
 */
function render_term_header($data) {
	if (!empty($data['term'])) {
		mprm_set_current_term($data['term']);
		mprm_get_template('common/item-taxonomy-header');
	}
}

/**
 * Preloader
 *
 * @param string $class
 */
function mprm_get_preloader($class = '') {
	mprm_get_template('common/preloader', array('class' => $class));
}

function mprm_taxonomy_list_before_left(){ ?>
	<div class="mprm-side mprm-left-side mprm-columns mprm-five">
	<?php
}

function mprm_taxonomy_list_after_left() { ?>
	</div>
<?php }