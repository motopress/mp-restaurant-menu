<?php
namespace mp_restaurant_menu\classes\modules;

use mp_restaurant_menu\classes\Module;

/**
 * Class Breadcrumbs
 * @package mp_restaurant_menu\classes\modules
 */
class Breadcrumbs extends Module {
	protected static $instance;

	/**
	 * @return Breadcrumbs
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	// Breadcrumbs
	/**
	 * @param array $params
	 */
	function show_breadcrumbs(array $params = array()) {
		// Settings
		$current_page = isset($params['current_page']) ? $params['current_page'] : true;
		$separator = !empty($params['separator']) ? $params['separator'] : '&gt;';
		$breadcrums_id = !empty($params['breadcrums_id']) ? $params['breadcrums_id'] : 'mprm-breadcrumbs';
		$breadcrums_class = !empty($params['breadcrums_class']) ? $params['breadcrums_class'] : 'mprm-breadcrumbs';
		$home_title = !empty($params['home_title']) ? $params['home_title'] : 'Home';
		// If you have any custom post types with custom taxonomies, put the taxonomy name below (e.g. product_cat)
		$custom_taxonomy = !empty($params['custom_taxonomy']) ? $params['custom_taxonomy'] : 'product_cat';
		// Get the query & post information
		global $post;
		// Do not display on the homepage
		if (!is_front_page()) {
			// Build the breadcrums
			echo '<ul id="' . esc_attr( $breadcrums_id ) . '" class="' . esc_attr( $breadcrums_class ) . '">';
			// Home page
			echo '<li class="mprm-item-home"><a class="mprm-breadcrumbs-link mprm-breadcrumbs-home" href="' . esc_url( get_home_url() ) . '" title="' . esc_attr( $home_title ) . '">' . esc_html( $home_title ) . '</a></li>';
			echo '<li class="mprm-breadcrumbs-delimiter mprm-breadcrumbs-delimiter-home"> ' . esc_html( $separator ) . ' </li>';
			if (is_archive() && !is_tax() && !is_category() && !is_tag()) {
				echo '<li class="mprm-item-current mprm-item-archive"><strong class="mprm-breadcrumbs-current mprm-breadcrumbs-archive">' . post_type_archive_title($prefix, false) . '</strong></li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else if (is_archive() && is_tax() && !is_category() && !is_tag()) {
				// If post is a custom post type
				$post_type = get_post_type();
				// If it is a custom post type display name and link
				if ($post_type != 'post') {
					$post_type_object = get_post_type_object($post_type);
					$post_type_archive = get_post_type_archive_link($post_type);
					echo '<li class="mprm-item-cat mprm-item-custom-post-type-' . esc_attr( $post_type ) . '"><a class="mprm-breadcrumbs-category mprm-breadcrumbs-custom-post-type-' . esc_attr( $post_type ) . '" href="' . esc_url( $post_type_archive ) . '" title="' . esc_attr( $post_type_object->labels->name ) . '">' . esc_html( $post_type_object->labels->name ) . '</a></li>';
					echo '<li class="separator"> ' . esc_html( $separator ) . ' </li>';
				}
				$custom_tax_name = get_queried_object()->name;
				if (!$current_page) {
					echo '<li class="mprm-item-current mprm-item-' . esc_attr( $post->ID ) . '"><a class="mprm-breadcrumbs-current mprm-breadcrumbs-' . esc_attr( $post->ID ) . '" href="' . esc_url( get_permalink() ) . '" title="' . esc_attr( get_the_title() ) . '">' . esc_html( get_the_title() ) . '</a></li>';
				} else {
					echo '<li class="mprm-item-current mprm-item-archive"><strong class="mprm-breadcrumbs-current mprm-breadcrumbs-archive">' . esc_html( $custom_tax_name ) . '</strong></li>';
				}
			} else if (is_single()) {
				// If post is a custom post type
				$post_type = get_post_type();
				// Get post category info
				$category = get_the_category();
				if (!empty($category)) {
					// Get last category post is in
					$last_category = end(array_values($category));
					// Get parent any categories and create array
					$get_cat_parents = rtrim(get_category_parents($last_category->term_id, true, ','), ',');
					$cat_parents = explode(',', $get_cat_parents);
					// Loop through parent categories and store in variable $cat_display
					$cat_display = '';
					foreach ($cat_parents as $parents) {
						$cat_display .= '<li class="mprm-item-category">' . esc_html( $parents ) . '</li>';
						$cat_display .= '<li class="mprm-category-separator"> ' . esc_html( $separator ) . ' </li>';
					}
				}
				// If it's a custom post type within a custom taxonomy
				$taxonomy_exists = taxonomy_exists($custom_taxonomy);
				if (empty($last_category) && !empty($custom_taxonomy) && $taxonomy_exists) {
					$taxonomy_terms = get_the_terms($post->ID, $custom_taxonomy);
					if (!empty($taxonomy_terms)) {
						$cat_id = $taxonomy_terms[0]->term_id;
						$cat_nicename = $taxonomy_terms[0]->slug;
						$cat_link = get_term_link($taxonomy_terms[0]->term_id, $custom_taxonomy);
						$cat_name = $taxonomy_terms[0]->name;
					}
				}
				// Check if the post is in a category
				if (!empty($last_category)) {
					echo $cat_display; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo '<li class="item-current mprm-item-' . esc_attr( $post->ID ) . '"><strong class="mprm-breadcrumbs-current mprm-breadcrumbs-' . esc_attr( $post->ID ) . '" title="' . esc_attr( get_the_title() ) . '">' . esc_html( get_the_title() ) . '</strong></li>';
					// Else if post is in a custom taxonomy
				} else if (!empty($cat_id)) {
					echo '<li class="mprm-item-cat mprm-item-cat-' . esc_attr( $cat_id ) . ' mprm-item-cat-' . esc_attr( $cat_nicename ) . '"><a class="mprm-breadcrumbs-category mprm-breadcrumbs-category-' . esc_attr( $cat_id ) . ' mprm-breadcrumbs-category-' . esc_attr( $cat_nicename ) . '" href="' . esc_url( $cat_link ) . '" title="' . esc_attr( $cat_name ) . '">' . esc_html( $cat_name ) . '</a></li>';
					echo '<li class="mprm-breadcrumbs-delimiter"> ' . esc_html( $separator ) . ' </li>';
					if (!$current_page) {
						echo '<li class="mprm-item-current mprm-item-' . esc_attr( $post->ID ) . '"><a class="mprm-breadcrumbs-current mprm-breadcrumbs' . esc_attr( $post->ID ) . '" href="' . esc_url( get_permalink() ) . '" title="' . esc_attr( get_the_title() ) . '">' . esc_html( get_the_title() ) . '</a></li>';
					} else {
						echo '<li class="mprm-item-current mprm-item-' . esc_attr( $post->ID ) . '"><strong class="mprm-breadcrumbs-current mprm-breadcrumbs' . esc_attr( $post->ID ) . '" title="' . esc_attr( get_the_title() ) . '">' . esc_html( get_the_title() ) . '</strong></li>';
					}
				} else {
					if (!$current_page) {
						echo '<li class="mprm-item-current mprm-item-' . esc_attr( $post->ID ) . '"><a class="mprm-breadcrumbs-current mprm-breadcrumbs' . esc_attr( $post->ID ) . '" href="' . esc_url( get_permalink() ) . '" title="' . esc_attr( get_the_title() ) . '">' . esc_html( get_the_title() ) . '</a></li>';
					} else {
						echo '<li class="mprm-item-current mprm-item-' . esc_attr( $post->ID ) . '"><strong class="mprm-breadcrumbs-current mprm-breadcrumbs' . esc_attr( $post->ID ) . '" title="' . esc_attr( get_the_title() ) . '">' . esc_html( get_the_title() ) . '</strong></li>';
					}
				}
			} else if (is_category()) {
				// Category page
				echo '<li class="mprm-item-current mprm-item-cat"><strong class="mprm-breadcrumbs-current mprm-breadcrumbscat">' . esc_html( single_cat_title('', false) ) . '</strong></li>';
			} else if (is_page()) {
				// Standard page
				if ($post->post_parent) {
					// If child page, get parents
					$anc = get_post_ancestors($post->ID);
					// Get parents in the right order
					$anc = array_reverse($anc);
					// Parent page loop
					foreach ($anc as $ancestor) {
						$parents .= '<li class="item-parent item-parent-' . esc_attr( $ancestor ) . '"><a class="mprm-breadcrumbs-parent mprm-breadcrumbs-parent-' . esc_attr( $ancestor ) . '" href="' . esc_url( get_permalink($ancestor) ) . '" title="' . esc_attr( get_the_title($ancestor) ) . '">' . esc_html( get_the_title($ancestor) ) . '</a></li>';
						$parents .= '<li class="mprm-breadcrumbs-delimiter mprm-breadcrumbs-delimiter-' . esc_attr( $ancestor ) . '"> ' . esc_html( $separator ) . ' </li>';
					}
					// Display parent pages
					echo $parents; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					// Current page
					echo '<li class="mprm-item-current mprm-item-' . esc_attr( $post->ID ) . '"><strong title="' . esc_attr( get_the_title() ) . '"> ' . esc_html( get_the_title() ) . '</strong></li>';
				} else {
					if (!$current_page) {
						echo '<li class="mprm-item-current mprm-item-' . esc_attr( $post->ID ) . '"><a class="mprm-breadcrumbs-current mprm-breadcrumbs-' . esc_attr( $post->ID ) . '" href="' . esc_url( get_permalink() ) . '" title="' . esc_attr( get_the_title() ) . '">' . esc_html( get_the_title() ) . '</a></li>';
					} else {
						// Just display current page if not parents
						echo '<li class="mprm-item-current mprm-item-' . esc_attr( $post->ID ) . '"><strong class="mprm-breadcrumbs-current mprm-breadcrumbs-' . esc_attr( $post->ID ) . '"> ' . esc_html( get_the_title() ) . '</strong></li>';
					}
				}
			} else if (is_tag()) {
				// Tag page
				// Get tag information
				$term_id = get_query_var('tag_id');
				$taxonomy = 'post_tag';
				$args = 'include=' . $term_id;
				$terms = get_terms($taxonomy, $args);
				$get_term_id = $terms[0]->term_id;
				$get_term_slug = $terms[0]->slug;
				$get_term_name = $terms[0]->name;
				// Display the tag name
				echo '<li class="mprm-item-current item-tag-' . esc_attr( $get_term_id ) . ' item-tag-' . esc_attr( $get_term_slug ) . '"><strong class="mprm-breadcrumbs-current mprm-breadcrumbs-tag-' . esc_attr( $get_term_id ) . ' mprm-breadcrumbs-tag-' . esc_attr( $get_term_slug ) . '">' . esc_html( $get_term_name ) . '</strong></li>';
			} elseif (is_day()) {
				// Day archive
				// Year link
				echo '<li class="item-year item-year-' . get_the_time('Y') . '"><a class="mprm-breadcrumbs-year mprm-breadcrumbs-year-' . get_the_time('Y') . '" href="' . get_year_link(get_the_time('Y')) . '" title="' . get_the_time('Y') . '">' . get_the_time('Y') . ' Archives</a></li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<li class="mprm-breadcrumbs-delimiter mprm-breadcrumbs-delimiter-' . get_the_time('Y') . '"> ' . esc_attr( $separator ) . ' </li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				// Month link
				echo '<li class="item-month item-month-' . get_the_time('m') . '"><a class="mprm-breadcrumbs-month mprm-breadcrumbs-month-' . get_the_time('m') . '" href="' . get_month_link(get_the_time('Y'), get_the_time('m')) . '" title="' . get_the_time('M') . '">' . get_the_time('M') . ' Archives</a></li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<li class="mprm-breadcrumbs-delimiter mprm-breadcrumbs-delimiter-' . get_the_time('m') . '"> ' . esc_attr( $separator ) . ' </li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				// Day display
				echo '<li class="mprm-item-current mprm-item-' . get_the_time('j') . '"><strong class="mprm-breadcrumbs-current mprm-breadcrumbs' . get_the_time('j') . '"> ' . get_the_time('jS') . ' ' . get_the_time('M') . ' Archives</strong></li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else if (is_month()) {
				// Month Archive
				// Year link
				echo '<li class="item-year item-year-' . get_the_time('Y') . '"><a class="mprm-breadcrumbs-year mprm-breadcrumbs-year-' . get_the_time('Y') . '" href="' . get_year_link(get_the_time('Y')) . '" title="' . get_the_time('Y') . '">' . get_the_time('Y') . ' Archives</a></li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<li class="mprm-breadcrumbs-delimiter mprm-breadcrumbs-delimiter-' . get_the_time('Y') . '"> ' . esc_attr( $separator ) . ' </li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				// Month display
				echo '<li class="item-month item-month-' . get_the_time('m') . '"><strong class="mprm-breadcrumbs-month mprm-breadcrumbs-month-' . get_the_time('m') . '" title="' . get_the_time('M') . '">' . get_the_time('M') . ' Archives</strong></li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else if (is_year()) {
				// Display year archive
				echo '<li class="mprm-item-current mprm-item-current-' . get_the_time('Y') . '"><strong class="mprm-breadcrumbs-current mprm-breadcrumbs-current-' . get_the_time('Y') . '" title="' . get_the_time('Y') . '">' . get_the_time('Y') . ' Archives</strong></li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else if (is_author()) {
				// Auhor archive
				// Get the author information
				global $author;
				$userdata = get_userdata($author);
				// Display author name
				echo '<li class="mprm-item-current mprm-item-current-' . esc_attr( $userdata->user_nicename ) . '"><strong class="mprm-breadcrumbs-current mprm-breadcrumbs-current-' . esc_attr( $userdata->user_nicename ) . '" title="' . esc_attr( $userdata->display_name ) . '">' . 'Author: ' . esc_html( $userdata->display_name ) . '</strong></li>';
			} else if (get_query_var('paged')) {
				// Paginated archives
				echo '<li class="mprm-item-current mprm-item-current-' . esc_attr( get_query_var('paged') ) . '"><strong class="mprm-breadcrumbs-current mprm-breadcrumbs-current-' . esc_attr( get_query_var('paged') ) . '" title="Page ' . esc_attr( get_query_var('paged') ) . '">' . esc_html__('Page', 'mp-restaurant-menu') . ' ' . esc_html( get_query_var('paged') ) . '</strong></li>';
			} else if (is_search()) {
				// Search results page
				echo '<li class="mprm-item-current mprm-item-current-' . esc_attr( get_search_query() ) . '"><strong class="mprm-breadcrumbs-current mprm-breadcrumbs-current-' . esc_attr( get_search_query() ) . '" title="Search results for: ' . esc_attr( get_search_query() ) . '">Search results for: ' . esc_html( get_search_query() ) . '</strong></li>';
			} elseif (is_404()) {
				// 404 page
				echo '<li>' . 'Error 404' . '</li>';
			}
			echo '</ul>';
		}
	}
}
