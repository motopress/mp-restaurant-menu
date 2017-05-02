<?php
namespace mp_restaurant_menu\classes;

use mp_restaurant_menu\classes\models\Menu_category;

/**
 * Export class
 */
class Export extends Core {
	protected static $instance;

	/**
	 * Export constructor.
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @return Export
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
	public function export_wp($args = array()) {
		if (in_array($args['content'], $this->post_types)) {
			$this->export($args);
		}
	}

	/**
	 * Export file
	 *
	 * @param $args
	 */
	public function export($args = array()) {
		global $wpdb, $post;
		if (!defined('ABSPATH')) exit;
		if (!function_exists('export_wp')) {
			include_once(ABSPATH . 'wp-admin/includes/export.php');
		}
		$defaults = array('content' => $this->post_types, 'author' => false, 'category' => false,
			'start_date' => false, 'end_date' => false, 'status' => false,
		);
		$args = wp_parse_args($args, $defaults);
		/**
		 * Fires at the beginning of an export, before any headers are sent.
		 *
		 *
		 * @param array $args An array of export arguments.
		 */
		//do_action('export_wp', $args);
		$filename = $this->file_name();
		header('Content-Description: File Transfer');
		header('Content-Disposition: attachment; filename=' . $filename);
		header('Content-Type: text/xml; charset=' . get_option('blog_charset'), true);
		if ('all' != $args['content'] && post_type_exists($args['content'])) {
			$ptype = get_post_type_object($args['content']);
			if (!$ptype->can_export)
				$args['content'] = 'post';
			$where = $wpdb->prepare("{$wpdb->posts}.post_type = %s", $args['content']);
		} else {
			$post_types = get_post_types(array('can_export' => true));
			$post_types = array_intersect($post_types, $args['content']);
			$esses = array_fill(0, count($post_types), '%s');
			$where = $wpdb->prepare("{$wpdb->posts}.post_type IN (" . implode(',', $esses) . ')', $post_types);
		}
		if ($args['status'] && ('post' == $args['content'] || 'page' == $args['content']))
			$where .= $wpdb->prepare(" AND {$wpdb->posts}.post_status = %s", $args['status']);
		else
			$where .= " AND {$wpdb->posts}.post_status != 'auto-draft'";
		$join = '';
		if ($args['category'] && 'post' == $args['content']) {
			if ($term = term_exists($args['category'], 'category')) {
				$join = "INNER JOIN {$wpdb->term_relationships} ON ({$wpdb->posts}.ID = {$wpdb->term_relationships}.object_id)";
				$where .= $wpdb->prepare(" AND {$wpdb->term_relationships}.term_taxonomy_id = %d", $term['term_taxonomy_id']);
			}
		}
		if ('post' == $args['content'] || 'page' == $args['content'] || 'attachment' == $args['content']) {
			if ($args['author'])
				$where .= $wpdb->prepare(" AND {$wpdb->posts}.post_author = %d", $args['author']);
			if ($args['start_date'])
				$where .= $wpdb->prepare(" AND {$wpdb->posts}.post_date >= %s", date('Y-m-d', strtotime($args['start_date'])));
			if ($args['end_date'])
				$where .= $wpdb->prepare(" AND {$wpdb->posts}.post_date < %s", date('Y-m-d', strtotime('+1 month', strtotime($args['end_date']))));
		}
		// Grab a snapshot of post IDs, just in case it changes during the export.
		$post_ids = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} $join WHERE $where");
		/*
		 * Get the requested terms ready, empty unless posts filtered by category
		 * or all content.
		 */
		$thumbnail_ids = array();
		foreach ($post_ids as $ID) {
			$thumbnail_id = get_post_thumbnail_id($ID);
			if (empty($thumbnail_id)) {
				continue;
			}
			$thumbnail_ids[] = $thumbnail_id;
		}
		if (!empty($thumbnail_ids)) {
			$post_ids = array_merge($post_ids, $thumbnail_ids);
		}
		$term_meta = $tags = $terms = array();
		if (!empty($this->taxonomy_names)) {
			$custom_taxonomies = get_taxonomies(array('_builtin' => false));
			$custom_terms = (array)get_terms($this->taxonomy_names, array('get' => 'all'));
			// Put terms in order with no child going before its parent.
			while ($t = array_shift($custom_terms)) {
				if ($t->parent == 0 || isset($terms[$t->parent]))
					$terms[$t->term_id] = $t;
				else
					$custom_terms[] = $t;
			}
			// get term_meta
			foreach ($terms as $key => $term) {
				$data = Menu_category::get_instance()->get_term_params($term->term_id);
				if (!empty($data)) {
					if (!empty($data['thumbnail_id'])) {
						$term_thumbnail_ids[] = $data['thumbnail_id'];
					}
					$term_meta[$term->term_id] = $data;
				}
			}
			if (!empty($term_thumbnail_ids)) {
				$post_ids = array_unique(array_merge($post_ids, $term_thumbnail_ids));
			}
			unset($categories, $custom_taxonomies, $custom_terms);
		}
		add_filter('mptt_export_skip_postmeta', array($this, 'mptt_filter_postmeta'), 10, 2);
		echo '<?xml version="1.0" encoding="' . get_bloginfo('charset') . "\" ?>\n"; ?>
		<?php the_generator('export'); ?>
		<rss version="2.0"
		     xmlns:excerpt="http://wordpress.org/export/<?php echo WXR_VERSION; ?>/excerpt/"
		     xmlns:content="http://purl.org/rss/1.0/modules/content/"
		     xmlns:dc="http://purl.org/dc/elements/1.1/"
		     xmlns:wp="http://wordpress.org/export/<?php echo WXR_VERSION; ?>/">
			<channel>
				<title><?php bloginfo_rss('name'); ?></title>
				<link><?php bloginfo_rss('url'); ?></link>
				<description><?php bloginfo_rss('description'); ?></description>
				<pubDate><?php echo date('D, d M Y H:i:s +0000'); ?></pubDate>
				<language><?php bloginfo_rss('language'); ?></language>
				<wp:wxr_version><?php echo WXR_VERSION; ?></wp:wxr_version>
				<wp:base_site_url><?php echo $this->mptt_site_url(); ?></wp:base_site_url>
				<wp:base_blog_url><?php bloginfo_rss('url'); ?></wp:base_blog_url>
				<?php $this->mptt_authors_list($post_ids); ?>
				<?php foreach ($terms as $t) : ?>
					<wp:term><?php if (!empty($term_meta[$t->term_id])) { ?>
							<wp:term_meta><?php echo $this->mptt_cdata(serialize($term_meta[$t->term_id])); ?></wp:term_meta><?php } ?>
						<wp:term_id><?php echo $this->mptt_cdata($t->term_id); ?></wp:term_id>
						<wp:term_taxonomy><?php echo $this->mptt_cdata($t->taxonomy); ?></wp:term_taxonomy>
						<wp:term_slug><?php echo $this->mptt_cdata($t->slug); ?></wp:term_slug>
						<wp:term_parent><?php echo $this->mptt_cdata($t->parent ? $terms[$t->parent]->slug : ''); ?></wp:term_parent><?php $this->mptt_term_name($t); ?><?php $this->mptt_term_description($t); ?> </wp:term>
				<?php endforeach;
				/** This action is documented in wp-includes/feed-rss2.php */
				do_action('rss2_head');
				?>
				<?php if ($post_ids) {
					/**
					 * @global WP_Query $wp_query
					 */
					global $wp_query;
					// Fake being in the loop.
					$wp_query->in_the_loop = true;
					// Fetch 20 posts at a time rather than loading the entire table into memory.
					while ($next_posts = array_splice($post_ids, 0, 20)) {
						$where = 'WHERE ID IN (' . join(',', $next_posts) . ')';
						$posts = $wpdb->get_results("SELECT * FROM {$wpdb->posts} $where");
						// Begin Loop.
						foreach ($posts as $post) {
							setup_postdata($post);
							$is_sticky = is_sticky($post->ID) ? 1 : 0; ?>
							<item>
								<title><?php
									/** This filter is documented in wp-includes/feed.php */
									echo apply_filters('the_title_rss', $post->post_title); ?></title>
								<link><?php the_permalink_rss() ?></link>
								<pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_post_time('Y-m-d H:i:s', true), false); ?></pubDate>
								<dc:creator><?php echo $this->mptt_cdata(get_the_author_meta('login')); ?></dc:creator>
								<guid isPermaLink="false"><?php the_guid(); ?></guid>
								<description></description>
								<content:encoded><?php
									/**
									 * Filter the post content used for WXR exports.
									 *
									 *
									 * @param string $post_content Content of the current post.
									 */
									echo $this->mptt_cdata(apply_filters('the_content_export', $post->post_content));
									?></content:encoded>
								<excerpt:encoded><?php
									/**
									 * Filter the post excerpt used for WXR exports.
									 *
									 *
									 * @param string $post_excerpt Excerpt for the current post.
									 */
									echo $this->mptt_cdata(apply_filters('the_excerpt_export', $post->post_excerpt));
									?></excerpt:encoded>
								<wp:post_id><?php echo intval($post->ID); ?></wp:post_id>
								<wp:post_date><?php echo $this->mptt_cdata($post->post_date); ?></wp:post_date>
								<wp:post_date_gmt><?php echo $this->mptt_cdata($post->post_date_gmt); ?></wp:post_date_gmt>
								<wp:comment_status><?php echo $this->mptt_cdata($post->comment_status); ?></wp:comment_status>
								<wp:ping_status><?php echo $this->mptt_cdata($post->ping_status); ?></wp:ping_status>
								<wp:post_name><?php echo $this->mptt_cdata($post->post_name); ?></wp:post_name>
								<wp:status><?php echo $this->mptt_cdata($post->post_status); ?></wp:status>
								<wp:post_parent><?php echo intval($post->post_parent); ?></wp:post_parent>
								<wp:menu_order><?php echo intval($post->menu_order); ?></wp:menu_order>
								<wp:post_type><?php echo $this->mptt_cdata($post->post_type); ?></wp:post_type>
								<wp:post_password><?php echo $this->mptt_cdata($post->post_password); ?></wp:post_password>
								<wp:is_sticky><?php echo intval($is_sticky); ?></wp:is_sticky>
								<?php if ($post->post_type == 'attachment') : ?>
									<wp:attachment_url><?php echo $this->mptt_cdata(wp_get_attachment_url($post->ID)); ?></wp:attachment_url>
								<?php endif; ?>
								<?php $this->mptt_post_taxonomy(); ?>
								<?php $postmeta = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->postmeta WHERE post_id = %d", $post->ID));
								foreach ($postmeta as $meta) :
									/**
									 * Filter whether to selectively skip post meta used for WXR exports.
									 *
									 * Returning a truthy value to the filter will skip the current meta
									 * object from being exported.
									 *
									 * @since 3.3.0
									 *
									 * @param bool $skip Whether to skip the current post meta. Default false.
									 * @param string $meta_key Current meta key.
									 * @param object $meta Current meta object.
									 */
									if (apply_filters('mptt_export_skip_postmeta', false, $meta->meta_key, $meta))
										continue;
									?>
									<wp:postmeta>
										<wp:meta_key><?php echo $this->mptt_cdata($meta->meta_key); ?></wp:meta_key>
										<wp:meta_value><?php echo $this->mptt_cdata($meta->meta_value); ?></wp:meta_value>
									</wp:postmeta>
								<?php endforeach; ?>
								<?php
								$_comments = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_approved <> 'spam'", $post->ID));
								$comments = array_map('get_comment', $_comments);
								foreach ($comments as $c) : ?>
									<wp:comment>
										<wp:comment_id><?php echo intval($c->comment_ID); ?></wp:comment_id>
										<wp:comment_author><?php echo $this->mptt_cdata($c->comment_author); ?></wp:comment_author>
										<wp:comment_author_email><?php echo $this->mptt_cdata($c->comment_author_email); ?></wp:comment_author_email>
										<wp:comment_author_url><?php echo esc_url_raw($c->comment_author_url); ?></wp:comment_author_url>
										<wp:comment_author_IP><?php echo $this->mptt_cdata($c->comment_author_IP); ?></wp:comment_author_IP>
										<wp:comment_date><?php echo $this->mptt_cdata($c->comment_date); ?></wp:comment_date>
										<wp:comment_date_gmt><?php echo $this->mptt_cdata($c->comment_date_gmt); ?></wp:comment_date_gmt>
										<wp:comment_content><?php echo $this->mptt_cdata($c->comment_content) ?></wp:comment_content>
										<wp:comment_approved><?php echo $this->mptt_cdata($c->comment_approved); ?></wp:comment_approved>
										<wp:comment_type><?php echo $this->mptt_cdata($c->comment_type); ?></wp:comment_type>
										<wp:comment_parent><?php echo intval($c->comment_parent); ?></wp:comment_parent>
										<wp:comment_user_id><?php echo intval($c->user_id); ?></wp:comment_user_id>
										<?php $c_meta = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->commentmeta WHERE comment_id = %d", $c->comment_ID));
										foreach ($c_meta as $meta) :
											/**
											 * Filter whether to selectively skip comment meta used for WXR exports.
											 *
											 * Returning a truthy value to the filter will skip the current meta
											 * object from being exported.
											 *
											 * @since 4.0.0
											 *
											 * @param bool $skip Whether to skip the current comment meta. Default false.
											 * @param string $meta_key Current meta key.
											 * @param object $meta Current meta object.
											 */
											if (apply_filters('mptt_export_skip_commentmeta', false, $meta->meta_key, $meta)) {
												continue;
											}
											?>
											<wp:commentmeta>
												<wp:meta_key><?php echo $this->mptt_cdata($meta->meta_key); ?></wp:meta_key>
												<wp:meta_value><?php echo $this->mptt_cdata($meta->meta_value); ?></wp:meta_value>
											</wp:commentmeta>
										<?php endforeach; ?>
									</wp:comment>
								<?php endforeach; ?>
							</item>
							<?php
						}
					}
				}
				?>
			</channel>
		</rss>
		<?php
	}

	/**
	 * Generate file name
	 * @return mixed
	 */
	public function file_name() {
		$sitename = sanitize_key(get_bloginfo('name'));
		if (!empty($sitename)) {
			$sitename .= '_';
		}
		$date = date('d.m.Y_H.i', time());
		$wp_filename = $sitename . 'restaurant_menu_' . $date . '.xml';
		/**
		 * Filter the export filename.
		 *
		 * @since 4.4.0
		 *
		 * @param string $wp_filename The name of the file for menu_item.
		 * @param string $sitename The site name.
		 * @param string $date Today's date, formatted.
		 */
		$filename = apply_filters('export_wp_filename', $wp_filename, $sitename, $date);
		return $filename;
	}

	/**
	 * Return the URL of the site
	 *
	 * @since 2.5.0
	 *
	 * @return string Site URL.
	 */
	public function mptt_site_url() {
		// Multisite: the base URL.
		if (is_multisite())
			return network_home_url();
		// WordPress (single site): the blog URL.
		else
			return get_bloginfo_rss('url');
	}

	/**
	 * Output list of authors with posts
	 *
	 * @since 3.1.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param array $post_ids Array of post IDs to filter the query by. Optional.
	 */
	public function mptt_authors_list(array $post_ids = null) {
		global $wpdb;
		if (!empty($post_ids)) {
			$post_ids = array_map('absint', $post_ids);
			$and = 'AND ID IN ( ' . implode(', ', $post_ids) . ')';
		} else {
			$and = '';
		}
		$authors = array();
		$results = $wpdb->get_results("SELECT DISTINCT post_author FROM $wpdb->posts WHERE post_status != 'auto-draft' $and");
		foreach ((array)$results as $result)
			$authors[] = get_userdata($result->post_author);
		$authors = array_filter($authors);
		foreach ($authors as $author) {
			echo "\t<wp:author>";
			echo '<wp:author_id>' . intval($author->ID) . '</wp:author_id>';
			echo '<wp:author_login>' . $this->mptt_cdata($author->user_login) . '</wp:author_login>';
			echo '<wp:author_email>' . $this->mptt_cdata($author->user_email) . '</wp:author_email>';
			echo '<wp:author_display_name>' . $this->mptt_cdata($author->display_name) . '</wp:author_display_name>';
			echo '<wp:author_first_name>' . $this->mptt_cdata($author->first_name) . '</wp:author_first_name>';
			echo '<wp:author_last_name>' . $this->mptt_cdata($author->last_name) . '</wp:author_last_name>';
			echo "</wp:author>\n";
		}
	}

	/**
	 * Wrap given string in XML CDATA tag.
	 *
	 * @since 2.1.0
	 *
	 * @param string $str String to wrap in XML CDATA tag.
	 *
	 * @return string
	 */
	public function mptt_cdata($str) {
		if (!seems_utf8($str)) {
			$str = utf8_encode($str);
		}
		// $str = ent2ncr(esc_html($str));
		$str = '<![CDATA[' . str_replace(']]>', ']]]]><![CDATA[>', $str) . ']]>';
		return $str;
	}

	/**
	 * Output a term_name XML tag from a given term object
	 *
	 * @since 2.9.0
	 *
	 * @param object $term Term Object
	 */
	public function mptt_term_name($term) {
		if (empty($term->name))
			return;
		echo '<wp:term_name>' . $this->mptt_cdata($term->name) . '</wp:term_name>';
	}

	/**
	 * Output a term_description XML tag from a given term object
	 *
	 * @since 2.9.0
	 *
	 * @param object $term Term Object
	 */
	public function mptt_term_description($term) {
		if (empty($term->description))
			return;
		echo '<wp:term_description>' . $this->mptt_cdata($term->description) . '</wp:term_description>';
	}

	/**
	 * Output list of taxonomy terms, in XML tag format, associated with a post
	 *
	 */
	public function mptt_post_taxonomy() {
		$post = get_post();
		$taxonomies = get_object_taxonomies($post->post_type);
		if (empty($taxonomies)) {
			return;
		}
		$terms = wp_get_object_terms($post->ID, $taxonomies);
		foreach ((array)$terms as $term) {
			echo "\t\t<category domain=\"{$term->taxonomy}\" nicename=\"{$term->slug}\">" . $this->mptt_cdata($term->name) . "</category>\n";
		}
	}

	/**
	 *
	 * @param bool $return_me
	 * @param string $meta_key
	 *
	 * @return bool
	 */
	public function mptt_filter_postmeta($return_me, $meta_key) {
		if ('_edit_lock' == $meta_key)
			$return_me = true;
		return $return_me;
	}
}
