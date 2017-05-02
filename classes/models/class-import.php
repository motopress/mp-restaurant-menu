<?php
namespace mp_restaurant_menu\classes;

use mp_restaurant_menu\classes\libs;
use mp_restaurant_menu\classes\models\Menu_category;

/**
 * Model Events
 */
class Import extends Core {
	protected static $instance;
	var $max_wxr_version = 1.2; // max. supported WXR version
	var $id; // WXR attachment ID
	var $file;
	var $import_data;
	// information to import from WXR file
	var $version;
	var $authors = array();
	var $posts = array();
	var $terms = array();
	var $categories = array();
	var $tags = array();
	var $base_url = '';
	var $processed_authors = array();
	var $author_mapping = array();
	var $processed_terms = array();
	var $processed_posts = array();
	var $post_orphans = array();
	var $processed_menu_items = array();
	var $menu_item_orphans = array();
	var $missing_menu_items = array();
	var $fetch_attachments = false;
	var $url_remap = array();
	var $featured_images = array();

	/**
	 * Import constructor.
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @return Import
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 *
	 * @param bool $return_me
	 * @param string $meta_key
	 *
	 * @return bool
	 */
	public function mprm_filter_postmeta($return_me, $meta_key) {
		if ('_edit_lock' == $meta_key)
			$return_me = true;
		return $return_me;
	}

	/**
	 * Import
	 */
	public function import() {
		$this->header();
		$step = empty($_GET['step']) ? 0 : (int)$_GET['step'];
		switch ($step) {
			case 0:
				$this->greet();
				break;
			case 1:
				check_admin_referer('import-upload');
				if ($this->handle_upload()) {
					$this->import_options();
				}
				break;
			case 2:
				if (!empty($_POST['import_id'])) {
					check_admin_referer('mprm-importer');
					$this->fetch_attachments = (!empty($_POST['fetch_attachments']) && $this->allow_fetch_attachments());
					$this->id = (int)$_POST['import_id'];
					$file = get_attached_file($this->id);
				} else {
					check_admin_referer('import-upload');
					$this->handle_upload();
					$this->fetch_attachments = $this->allow_fetch_attachments();
					$file = $this->file;
				}
				set_time_limit(0);
				add_filter('import_post_meta_key', array($this, 'is_valid_meta_key'));
				add_filter('http_request_timeout', array(&$this, 'bump_request_timeout'));
				$this->process_start($file);
				$this->process_end();
				break;
		}
		$this->footer();
	}

	public function header() {
		View::get_instance()->render_html('../admin/import/header');
	}

	public function greet() {
		View::get_instance()->render_html('../admin/import/greet');
	}

	/**
	 * @return bool
	 */
	public function handle_upload() {
		$file = wp_import_handle_upload();
		if (isset($file['error'])) {
			echo '<p><strong>' . __('Sorry, there has been an error.', 'mp-restaurant-menu') . '</strong><br />';
			echo esc_html($file['error']) . '</p>';
			return false;
		} else if (!file_exists($file['file'])) {
			echo '<p><strong>' . __('Sorry, there has been an error.', 'mp-restaurant-menu') . '</strong><br />';
			printf(__('The export file could not be found at <code>%s</code>. It is likely that this was caused by a permissions problem.', 'mp-restaurant-menu'), esc_html($file['file']));
			echo '</p>';
			return false;
		}
		$this->file = $file['file'];
		$this->id = (int)$file['id'];
		$this->import_data = $this->parse($file['file']);
		if (is_wp_error($this->import_data)) {
			echo '<p><strong>' . __('Sorry, there has been an error.', 'mp-restaurant-menu') . '</strong><br />';
			echo esc_html($this->import_data->get_error_message()) . '</p>';
			return false;
		}
		$this->version = $this->import_data['version'];
		if ($this->version > $this->max_wxr_version) {
			echo '<div class="error"><p><strong>';
			printf(__('This WXR file (version %s) may not be supported by this version of the importer. Please consider updating.', 'mp-restaurant-menu'), esc_html($this->import_data['version']));
			echo '</strong></p></div>';
		}
		$this->get_authors_from_import();
		return true;
	}

	/**
	 * Parse a WXR file
	 *
	 * @param string $file Path to WXR file for parsing
	 *
	 * @return array Information gathered from the WXR file
	 */
	function parse($file) {
		$parser = new libs\WXR_Parser();
		return $parser->parse($file);
	}

	/**
	 * Retrieve authors from parsed WXR data
	 *
	 * Uses the provided author information from WXR 1.1 files
	 * or extracts info from each post for WXR 1.0 files
	 *
	 * @internal param array $this ->import_data Data returned by a WXR parser
	 */
	function get_authors_from_import() {
		if (!empty($this->import_data['authors'])) {
			$this->authors = $this->import_data['authors'];
			// no author information, grab it from the posts
		} else {
			foreach ($this->import_data['posts'] as $post) {
				$login = sanitize_user($post['post_author'], true);
				if (empty($login)) {
					printf(__('Failed to import author %s. Their posts will be attributed to the current user.', 'mp-restaurant-menu'), esc_html($post['post_author']));
					echo '<br />';
					continue;
				}
				if (!isset($this->authors[$login]))
					$this->authors[$login] = array(
						'author_login' => $login,
						'author_display_name' => $post['post_author']
					);
			}
		}
	}

	public function import_options() {
		$j = 0;
		?>
		<form action="<?php echo admin_url('admin.php?import=mprm-importer&amp;step=2'); ?>" method="post">
			<?php wp_nonce_field('mprm-importer'); ?>
			<input type="hidden" name="import_id" value="<?php echo $this->id; ?>"/>
			<?php if (!empty($this->authors)) : ?>
				<h3><?php _e('Assign Authors', 'mp-restaurant-menu'); ?></h3>
				<p><?php _e('To make it easier for you to edit and save the imported content, you may want to reassign the author of the imported item to an existing user of this site. For example, you may want to import all the entries as <code>admin</code>s entries.', 'mp-restaurant-menu'); ?></p>
				<?php if ($this->allow_create_users()) : ?>
					<p><?php printf(__('If a new user is created by WordPress, a new password will be randomly generated and the new user&#8217;s role will be set as %s. Manually changing the new user&#8217;s details will be necessary.', 'mp-restaurant-menu'), esc_html(get_option('default_role'))); ?></p>
				<?php endif; ?>
				<ol id="authors">
					<?php foreach ($this->authors as $author) : ?>
						<li><?php $this->author_select($j++, $author); ?></li>
					<?php endforeach; ?>
				</ol>
			<?php endif; ?>
			<?php if ($this->allow_fetch_attachments()) : ?>
				<h3><?php _e('Import Attachments', 'mp-restaurant-menu'); ?></h3>
				<p>
					<input type="checkbox" value="1" name="fetch_attachments" id="import-attachments"/>
					<label for="import-attachments"><?php _e('Download and import file attachments', 'mp-restaurant-menu'); ?></label>
				</p>
			<?php endif; ?>
			<p class="submit"><input type="submit" class="button" value="<?php esc_attr_e('Submit', 'mp-restaurant-menu'); ?>"/></p>
		</form>
		<?php
	}

	/**
	 * Decide whether or not the importer is allowed to create users.
	 * Default is true, can be filtered via import_allow_create_users
	 *
	 * @return bool True if creating users is allowed
	 */
	public function allow_create_users() {
		return apply_filters('import_allow_create_users', true);
	}

	/**
	 * Display import options for an individual author. That is, either create
	 * a new user based on import info or map to an existing user
	 *
	 * @param int $n Index for each author in the form
	 * @param array $author Author information, e.g. login, display name, email
	 */
	function author_select($n, $author) {
		_e('Import author:', 'mp-restaurant-menu');
		echo ' <strong>' . esc_html($author['author_display_name']);
		if ($this->version != '1.0') echo ' (' . esc_html($author['author_login']) . ')';
		echo '</strong><br />';
		if ($this->version != '1.0')
			echo '<div style="margin-left:18px">';
		$create_users = $this->allow_create_users();
		if ($create_users) {
			if ($this->version != '1.0') {
				_e('or create new user with login name:', 'mp-restaurant-menu');
				$value = '';
			} else {
				_e('as a new user:', 'mp-restaurant-menu');
				$value = esc_attr(sanitize_user($author['author_login'], true));
			}
			echo ' <input type="text" name="user_new[' . $n . ']" value="' . $value . '" /><br />';
		}
		if (!$create_users && $this->version == '1.0')
			_e('assign posts to an existing user:', 'mp-restaurant-menu');
		else
			_e('or assign posts to an existing user:', 'mp-restaurant-menu');
		wp_dropdown_users(array('name' => "user_map[$n]", 'multi' => true, 'show_option_all' => __('- Select -', 'mp-restaurant-menu')));
		echo '<input type="hidden" name="imported_authors[' . $n . ']" value="' . esc_attr($author['author_login']) . '" />';
		if ($this->version != '1.0')
			echo '</div>';
	}

	/**
	 * Decide whether or not the importer should attempt to menu_item attachment files.
	 * Default is true, can be filtered via import_allow_fetch_attachments. The choice
	 * made at the import options screen must also be true, false here hides that checkbox.
	 *
	 * @return bool True if menu_iteming attachments is allowed
	 */
	public function allow_fetch_attachments() {
		return apply_filters('import_allow_fetch_attachments', true);
	}

	/**
	 * Import start
	 *
	 * @param $file
	 */
	public function process_start($file) {
		$this->import_data = $this->parse($file);
		$this->get_authors_from_import();
		$this->version = $this->import_data['version'];
		$this->posts = $this->import_data['posts'];
		$this->terms = $this->import_data['terms'];
		wp_suspend_cache_invalidation(true);
		$this->process_terms();
		$this->process_posts();
		wp_suspend_cache_invalidation(false);
		$this->backfill_parents();
		$this->backfill_attachment_urls();
		$this->remap_featured_images();
		wp_defer_term_counting(true);
		wp_defer_comment_counting(true);
		do_action('import_start');
	}

	/**
	 * Create new terms based on import information
	 *
	 * Doesn't create a term its slug already exists
	 */
	function process_terms() {
		$this->terms = apply_filters('wp_import_terms', $this->terms);
		foreach ($this->terms as $key => $term) {
			if (!empty($term['term_meta'])) {
				$term['term_meta'] = @unserialize($term['term_meta']);
				$this->terms[$key] = $term;
			} else {
				continue;
			}
		}
		if (empty($this->terms))
			return;
		foreach ($this->terms as $term) {
			// if the term already exists in the correct taxonomy leave it alone
			$term_id = term_exists($term['slug'], $term['term_taxonomy']);
			if ($term_id) {
				if (is_array($term_id)) $term_id = $term_id['term_id'];
				if (isset($term['term_id']))
					$this->processed_terms[intval($term['term_id'])] = (int)$term_id;
				if (!empty($term['term_meta'])) {
					Menu_category::get_instance()->save_menu_category($term_id, $term['term_meta']);
				}
				continue;
			}
			if (empty($term['term_parent'])) {
				$parent = 0;
			} else {
				$parent = term_exists($term['term_parent'], $term['term_taxonomy']);
				if (is_array($parent)) $parent = $parent['term_id'];
			}
			$description = isset($term['term_description']) ? $term['term_description'] : '';
			$termarr = array('slug' => $term['slug'], 'description' => $description, 'parent' => intval($parent));
			$id = wp_insert_term($term['term_name'], $term['term_taxonomy'], $termarr);
			if (!is_wp_error($id)) {
				if (isset($term['term_id'])) {
					$this->processed_terms[intval($term['term_id'])] = $id['term_id'];
				}
				if (!empty($term['term_meta'])) {
					Menu_category::get_instance()->save_menu_category($id['term_id'], $term['term_meta']);
				}
			} else {
				printf(__('Failed to import %s %s', 'mp-restaurant-menu'), esc_html($term['term_taxonomy']), esc_html($term['term_name']));
				if (defined('IMPORT_DEBUG') && IMPORT_DEBUG)
					echo ': ' . $id->get_error_message();
				echo '<br />';
				continue;
			}
		}
		unset($this->terms);
	}

	/**
	 * Create new posts based on import information
	 *
	 * Posts marked as having a parent which doesn't exist will become top level items.
	 * Doesn't create a new post if: the post type doesn't exist, the given post ID
	 * is already noted as imported or a post with the same title and date already exists.
	 * Note that new/updated terms, comments and meta are imported for the last of the above.
	 */
	public function process_posts() {
		$this->posts = apply_filters('wp_import_posts', $this->posts);
		foreach ($this->posts as $post) {
			$post = apply_filters('wp_import_post_data_raw', $post);
			if (!post_type_exists($post['post_type'])) {
				printf(__('Failed to import &#8220;%s&#8221;: Invalid post type %s', 'mp-restaurant-menu'),
					esc_html($post['post_title']), esc_html($post['post_type']));
				echo '<br />';
				do_action('wp_import_post_exists', $post);
				continue;
			}
			if (isset($this->processed_posts[$post['post_id']]) && !empty($post['post_id']))
				continue;
			if ($post['status'] == 'auto-draft')
				continue;
			$post_type_object = get_post_type_object($post['post_type']);
			$post_exists = post_exists($post['post_title'], '', $post['post_date']);
			if ($post_exists && get_post_type($post_exists) == $post['post_type']) {
				printf(__('%s &#8220;%s&#8221; already exists.', 'mp-restaurant-menu'), $post_type_object->labels->singular_name, esc_html($post['post_title']));
				echo '<br />';
				$comment_post_ID = $post_id = $post_exists;
			} else {
				$post_parent = (int)$post['post_parent'];
				if ($post_parent) {
					// if we already know the parent, map it to the new local ID
					if (isset($this->processed_posts[$post_parent])) {
						$post_parent = $this->processed_posts[$post_parent];
						// otherwise record the parent for later
					} else {
						$this->post_orphans[intval($post['post_id'])] = $post_parent;
						$post_parent = 0;
					}
				}
				// map the post author
				$author = sanitize_user($post['post_author'], true);
				if (isset($this->author_mapping[$author]))
					$author = $this->author_mapping[$author];
				else
					$author = (int)get_current_user_id();
				$postdata = array(
					'import_id' => $post['post_id'], 'post_author' => $author, 'post_date' => $post['post_date'],
					'post_date_gmt' => $post['post_date_gmt'], 'post_content' => $post['post_content'],
					'post_excerpt' => $post['post_excerpt'], 'post_title' => $post['post_title'],
					'post_status' => $post['status'], 'post_name' => $post['post_name'],
					'comment_status' => $post['comment_status'], 'ping_status' => $post['ping_status'],
					'guid' => $post['guid'], 'post_parent' => $post_parent, 'menu_order' => $post['menu_order'],
					'post_type' => $post['post_type'], 'post_password' => $post['post_password']
				);
				$original_post_ID = $post['post_id'];
				$postdata = apply_filters('wp_import_post_data_processed', $postdata, $post);
				if ('attachment' == $postdata['post_type']) {
					$remote_url = !empty($post['attachment_url']) ? $post['attachment_url'] : $post['guid'];
					// try to use _wp_attached file for upload folder placement to ensure the same location as the export site
					// e.g. location is 2003/05/image.jpg but the attachment post_date is 2010/09, see media_handle_upload()
					$postdata['upload_date'] = $post['post_date'];
					if (isset($post['postmeta'])) {
						foreach ($post['postmeta'] as $meta) {
							if ($meta['key'] == '_wp_attached_file') {
								if (preg_match('%^[0-9]{4}/[0-9]{2}%', $meta['value'], $matches))
									$postdata['upload_date'] = $matches[0];
								break;
							}
						}
					}
					$comment_post_ID = $post_id = $this->process_attachment($postdata, $remote_url);
				} else {
					$comment_post_ID = $post_id = wp_insert_post($postdata, true);
					do_action('wp_import_insert_post', $post_id, $original_post_ID, $postdata, $post);
				}
				if (is_wp_error($post_id)) {
					printf(__('Failed to import %s &#8220;%s&#8221;', 'mp-restaurant-menu'),
						$post_type_object->labels->singular_name, esc_html($post['post_title']));
					if (defined('IMPORT_DEBUG') && IMPORT_DEBUG)
						echo ': ' . $post_id->get_error_message();
					echo '<br />';
					continue;
				}
				if ($post['is_sticky'] == 1)
					stick_post($post_id);
			}
			// map pre-import ID to local ID
			$this->processed_posts[intval($post['post_id'])] = (int)$post_id;
			if (!isset($post['terms']))
				$post['terms'] = array();
			$post['terms'] = apply_filters('wp_import_post_terms', $post['terms'], $post_id, $post);
			// add categories, tags and other terms
			if (!empty($post['terms'])) {
				$terms_to_set = array();
				foreach ($post['terms'] as $term) {
					// back compat with WXR 1.0 map 'tag' to 'post_tag'
					$taxonomy = ('tag' == $term['domain']) ? 'post_tag' : $term['domain'];
					$term_exists = term_exists($term['slug'], $taxonomy);
					$term_id = is_array($term_exists) ? $term_exists['term_id'] : $term_exists;
					if (!$term_id) {
						$t = wp_insert_term($term['name'], $taxonomy, array('slug' => $term['slug']));
						if (!is_wp_error($t)) {
							$term_id = $t['term_id'];
							do_action('wp_import_insert_term', $t, $term, $post_id, $post);
						} else {
							printf(__('Failed to import %s %s', 'mp-restaurant-menu'), esc_html($taxonomy), esc_html($term['name']));
							if (defined('IMPORT_DEBUG') && IMPORT_DEBUG)
								echo ': ' . $t->get_error_message();
							echo '<br />';
							do_action('wp_import_insert_term_failed', $t, $term, $post_id, $post);
							continue;
						}
					}
					$terms_to_set[$taxonomy][] = intval($term_id);
				}
				foreach ($terms_to_set as $tax => $ids) {
					$tt_ids = wp_set_post_terms($post_id, $ids, $tax);
					do_action('wp_import_set_post_terms', $tt_ids, $ids, $tax, $post_id, $post);
				}
				unset($post['terms'], $terms_to_set);
			}
			if (!isset($post['comments']))
				$post['comments'] = array();
			$post['comments'] = apply_filters('wp_import_post_comments', $post['comments'], $post_id, $post);
			// add/update comments
			if (!empty($post['comments'])) {
				$num_comments = 0;
				$inserted_comments = array();
				foreach ($post['comments'] as $comment) {
					$comment_id = $comment['comment_id'];
					$newcomments[$comment_id]['comment_post_ID'] = $comment_post_ID;
					$newcomments[$comment_id]['comment_author'] = $comment['comment_author'];
					$newcomments[$comment_id]['comment_author_email'] = $comment['comment_author_email'];
					$newcomments[$comment_id]['comment_author_IP'] = $comment['comment_author_IP'];
					$newcomments[$comment_id]['comment_author_url'] = $comment['comment_author_url'];
					$newcomments[$comment_id]['comment_date'] = $comment['comment_date'];
					$newcomments[$comment_id]['comment_date_gmt'] = $comment['comment_date_gmt'];
					$newcomments[$comment_id]['comment_content'] = $comment['comment_content'];
					$newcomments[$comment_id]['comment_approved'] = $comment['comment_approved'];
					$newcomments[$comment_id]['comment_type'] = $comment['comment_type'];
					$newcomments[$comment_id]['comment_parent'] = $comment['comment_parent'];
					$newcomments[$comment_id]['commentmeta'] = isset($comment['commentmeta']) ? $comment['commentmeta'] : array();
					if (isset($this->processed_authors[$comment['comment_user_id']]))
						$newcomments[$comment_id]['user_id'] = $this->processed_authors[$comment['comment_user_id']];
				}
				ksort($newcomments);
				foreach ($newcomments as $key => $comment) {
					// if this is a new post we can skip the comment_exists() check
					if (!$post_exists || !comment_exists($comment['comment_author'], $comment['comment_date'])) {
						if (isset($inserted_comments[$comment['comment_parent']]))
							$comment['comment_parent'] = $inserted_comments[$comment['comment_parent']];
						$comment = wp_filter_comment($comment);
						$inserted_comments[$key] = wp_insert_comment($comment);
						do_action('wp_import_insert_comment', $inserted_comments[$key], $comment, $comment_post_ID, $post);
						foreach ($comment['commentmeta'] as $meta) {
							$value = maybe_unserialize($meta['value']);
							add_comment_meta($inserted_comments[$key], $meta['key'], $value);
						}
						$num_comments++;
					}
				}
				unset($newcomments, $inserted_comments, $post['comments']);
			}
			// change time_slot data if post ID change
			if (!empty($original_post_ID)) {
				if ($post['post_type'] == 'attachment') {
					if ($original_post_ID != $post_id) {
						if (!empty($this->import_data['terms'])) {
							foreach ($this->import_data['terms'] as $term) {
								if (!empty($term['term_meta'])) {
									$term['term_meta'] = @unserialize($term['term_meta']);
									if ($term['term_meta']['thumbnail_id'] == $original_post_ID) {
										$term['term_meta']['thumbnail_id'] = $post_id;
										Menu_category::get_instance()->save_menu_category($term['term_id'], $term['term_meta']);
									}
								} else {
									continue;
								}
							}
						}
					}
				}
			}
			if (!isset($post['postmeta'])) {
				$post['postmeta'] = array();
			}
			$post['postmeta'] = apply_filters('wp_import_post_meta', $post['postmeta'], $post_id, $post);
			// add/update post meta
			if (!empty($post['postmeta'])) {
				foreach ($post['postmeta'] as $meta) {
					$key = apply_filters('import_post_meta_key', $meta['key'], $post_id, $post);
					$value = false;
					if ('_edit_last' == $key) {
						if (isset($this->processed_authors[intval($meta['value'])]))
							$value = $this->processed_authors[intval($meta['value'])];
						else
							$key = false;
					}
					if ($key) {
						// export gets meta straight from the DB so could have a serialized string
						if (!$value)
							$value = maybe_unserialize($meta['value']);
						update_post_meta($post_id, $key, $value);
						do_action('import_post_meta', $post_id, $key, $value);
						// if the post has a featured image, take note of this in case of remap
						if ('_thumbnail_id' == $key)
							$this->featured_images[$post_id] = (int)$value;
					}
				}
			}
		}
		unset($this->posts);
	}

	/**
	 * If fetching attachments is enabled then attempt to create a new attachment
	 *
	 * @param array $post Attachment post details from WXR
	 * @param string $url URL to fetch attachment from
	 *
	 * @return int|WP_Error Post ID on success, WP_Error otherwise
	 */
	public function process_attachment($post, $url) {
		if (!$this->fetch_attachments)
			return new \WP_Error('attachment_processing_error',
				__('Fetching attachments is not enabled', 'mp-restaurant-menu'));
		// if the URL is absolute, but does not contain address, then upload it assuming base_site_url
		if (preg_match('|^/[\w\W]+$|', $url))
			$url = rtrim($this->base_url, '/') . $url;
		$upload = $this->fetch_remote_file($url, $post);
		if (is_wp_error($upload))
			return $upload;
		if ($info = wp_check_filetype($upload['file']))
			$post['post_mime_type'] = $info['type'];
		else
			return new \WP_Error('attachment_processing_error', __('Invalid file type', 'mp-restaurant-menu'));
		$post['guid'] = $upload['url'];
		// as per wp-admin/includes/upload.php
		$post_id = wp_insert_attachment($post, $upload['file']);
		wp_update_attachment_metadata($post_id, wp_generate_attachment_metadata($post_id, $upload['file']));
		// remap resized image URLs, works by stripping the extension and remapping the URL stub.
		if (preg_match('!^image/!', $info['type'])) {
			$parts = pathinfo($url);
			$name = basename($parts['basename'], ".{$parts['extension']}"); // PATHINFO_FILENAME in PHP 5.2
			$parts_new = pathinfo($upload['url']);
			$name_new = basename($parts_new['basename'], ".{$parts_new['extension']}");
			$this->url_remap[$parts['dirname'] . '/' . $name] = $parts_new['dirname'] . '/' . $name_new;
		}
		return $post_id;
	}

	/**
	 * Attempt to menu_item a remote file attachment
	 *
	 * @param string $url URL of item to fetch
	 * @param array $post Attachment details
	 *
	 * @return array|\WP_Error Local file location details on success, WP_Error otherwise
	 */
	public function fetch_remote_file($url, $post) {
		// extract the file name and extension from the url
		$file_name = basename($url);
		// get placeholder file in the upload dir with a unique, sanitized filename
		$upload = wp_upload_bits($file_name, 0, '', $post['upload_date']);
		if ($upload['error'])
			return new \WP_Error('upload_dir_error', $upload['error']);
		// fetch the remote url and write it to the placeholder file
		$response_data = wp_safe_remote_head($url);
		$headers = wp_remote_retrieve_headers($response_data);
		$headers['response'] = wp_remote_retrieve_response_code($response_data);
		if (!$headers) {
			@unlink($upload['file']);
			return new \WP_Error('import_file_error', __('Remote server did not respond', 'mp-restaurant-menu'));
		}
		// make sure the fetch was successful
		if ($headers['response'] != '200') {
			@unlink($upload['file']);
			return new \WP_Error('import_file_error', sprintf(__('Remote server returned error response %1$d %2$s', 'mp-restaurant-menu'), esc_html($headers['response']), get_status_header_desc($headers['response'])));
		}
		$this->write_file($url, $upload);
		$filesize = filesize($upload['file']);
		if (isset($headers['content-length']) && $filesize != $headers['content-length']) {
			@unlink($upload['file']);
			return new \WP_Error('import_file_error', __('Remote file is incorrect size', 'mp-restaurant-menu'));
		}
		if (0 == $filesize) {
			@unlink($upload['file']);
			return new \WP_Error('import_file_error', __('Zero size file menu_itemed', 'mp-restaurant-menu'));
		}
		$max_size = (int)$this->max_attachment_size();
		if (!empty($max_size) && $filesize > $max_size) {
			@unlink($upload['file']);
			return new \WP_Error('import_file_error', sprintf(__('Remote file is too large, limit is %s', 'mp-restaurant-menu'), size_format($max_size)));
		}
		// keep track of the old and new urls so we can substitute them later
		$this->url_remap[$url] = $upload['url'];
		$this->url_remap[$post['guid']] = $upload['url']; // r13735, really needed?
		// keep track of the destination if the remote url is redirected somewhere else
		if (isset($headers['x-final-location']) && $headers['x-final-location'] != $url)
			$this->url_remap[$headers['x-final-location']] = $upload['url'];
		return $upload;
	}

	/**
	 * Get file by URL and write upload
	 *
	 * @param $url
	 * @param $upload
	 * @param int $redirection
	 *
	 * @return bool
	 */
	private function write_file($url, $upload, $redirection = 5) {
		$options = array();
		$options['redirection'] = $redirection;
		if (false == $upload['file'])
			$options['method'] = 'HEAD';
		else
			$options['method'] = 'GET';
		$response = wp_safe_remote_request($url, $options);
		if (false == $upload['file']) {
			return false;
		}
		// GET request - write it to the supplied filename
		$out_fp = fopen($upload['file'], 'w');
		if (!$out_fp) {
			return false;
		}
		fwrite($out_fp, wp_remote_retrieve_body($response));
		fclose($out_fp);
		clearstatcache();
		return true;
	}

	/**
	 * @return mixed
	 */
	function max_attachment_size() {
		return apply_filters('import_attachment_size_limit', 0);
	}

	/**
	 * Attempt to associate posts and menu items with previously missing parents
	 *
	 * An imported post's parent may not have been imported when it was first created
	 * so try again. Similarly for child menu items and menu items which were missing
	 * the object (e.g. post) they represent in the menu
	 */
	function backfill_parents() {
		global $wpdb;
		// find parents for post orphans
		foreach ($this->post_orphans as $child_id => $parent_id) {
			$local_child_id = $local_parent_id = false;
			if (isset($this->processed_posts[$child_id]))
				$local_child_id = $this->processed_posts[$child_id];
			if (isset($this->processed_posts[$parent_id]))
				$local_parent_id = $this->processed_posts[$parent_id];
			if ($local_child_id && $local_parent_id)
				$wpdb->update($wpdb->posts, array('post_parent' => $local_parent_id), array('ID' => $local_child_id), '%d', '%d');
		}
		// all other posts/terms are imported, retry menu items with missing associated object
		$missing_menu_items = $this->missing_menu_items;
		foreach ($missing_menu_items as $item)
			$this->process_menu_item($item);
		// find parents for menu item orphans
		foreach ($this->menu_item_orphans as $child_id => $parent_id) {
			$local_child_id = $local_parent_id = 0;
			if (isset($this->processed_menu_items[$child_id]))
				$local_child_id = $this->processed_menu_items[$child_id];
			if (isset($this->processed_menu_items[$parent_id]))
				$local_parent_id = $this->processed_menu_items[$parent_id];
			if ($local_child_id && $local_parent_id)
				update_post_meta($local_child_id, '_menu_item_menu_item_parent', (int)$local_parent_id);
		}
	}

	/**
	 * Use stored mapping information to update old attachment URLs
	 */
	function backfill_attachment_urls() {
		global $wpdb;
		// make sure we do the longest urls first, in case one is a substring of another
		uksort($this->url_remap, array(&$this, 'cmpr_strlen'));
		foreach ($this->url_remap as $from_url => $to_url) {
			// remap urls in post_content
			$wpdb->query($wpdb->prepare("UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, %s, %s)", $from_url, $to_url));
			// remap enclosure urls
			$result = $wpdb->query($wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_value = REPLACE(meta_value, %s, %s) WHERE meta_key='enclosure'", $from_url, $to_url));
		}
	}

	/**
	 * Update _thumbnail_id meta to new, imported attachment IDs
	 */
	function remap_featured_images() {
		// cycle through posts that have a featured image
		foreach ($this->featured_images as $post_id => $value) {
			if (isset($this->processed_posts[$value])) {
				$new_id = $this->processed_posts[$value];
				// only update if there's a difference
				if ($new_id != $value)
					update_post_meta($post_id, '_thumbnail_id', $new_id);
			}
		}
	}

	public function process_end() {
		wp_import_cleanup($this->id);
		wp_cache_flush();
		foreach (get_taxonomies() as $tax) {
			delete_option("{$tax}_children");
			_get_term_hierarchy($tax);
		}
		wp_defer_term_counting(false);
		wp_defer_comment_counting(false);
		echo '<p>' . __('All done.', 'mp-restaurant-menu') . ' <a href="' . admin_url() . '">' . __('Have fun!', 'mp-restaurant-menu') . '</a>' . '</p>';
		echo '<p>' . __('Remember to update the passwords and roles of imported users.', 'mp-restaurant-menu') . '</p>';
		do_action('import_end');
	}

	public function footer() {
		View::get_instance()->render_html('../admin/import/footer');
	}

	// return the difference in length between two strings

	/**
	 * Decide if the given meta key maps to information we will want to import
	 *
	 * @param string $key The meta key to check
	 *
	 * @return string|bool The key if we do want to import, false if not
	 */
	function is_valid_meta_key($key) {
		// skip attachment metadata since we'll regenerate it from scratch
		// skip _edit_lock as not relevant for import
		if (in_array($key, array('_wp_attached_file', '_wp_attachment_metadata', '_edit_lock')))
			return false;
		return $key;
	}

	/**
	 * Added to http_request_timeout filter to force timeout at 60 seconds during import
	 * @return int 60
	 */
	function bump_request_timeout() {
		return 60;
	}

	/**
	 * @param $a
	 * @param $b
	 *
	 * @return int
	 */
	public function cmpr_strlen($a, $b) {
		return strlen($b) - strlen($a);
	}
}