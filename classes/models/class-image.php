<?php
namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Model;

/**
 * Class Image
 * @package mp_restaurant_menu\classes\models
 */
class Image extends Model {
	protected static $instance;
	private $sizes;

	/**
	 * Image constructor.
	 */

	public function __construct() {
		parent::__construct();
		$this->sizes = include(MP_RM_CONFIGS_PATH . 'img-sizes.php');
	}

	/**
	 * @return Image
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Get image size
	 *
	 * @param $size
	 * @param bool $key
	 *
	 * @return mixed
	 */
	public function get_size($size, $key = false) {
		$sizes = $this->get_sizes();
		$size = "mprm-$size";
		if ($key && !empty($sizes[$size][$key])) {
			return $sizes[$size][$key];
		} elseif (!empty($sizes[$size])) {
			return $sizes[$size];
		}
	}

	/**
	 * Get all image sizes
	 *
	 * @return array
	 */
	public function get_sizes() {
		$sizes = array();
		if (!empty($this->sizes)) {
			foreach ($this->sizes as $key => $size) {
				$sizes["mprm-$key"] = $size;
			}
		}
		return $sizes;
	}

	/**
	 * Add image sizes
	 */
	public function add_image_sizes() {
		foreach ($this->get_sizes() as $key => $size) {
			if (1 == $size['crop']) {
				add_image_size($key, $size['width'], $size['height'], true);
			} else {
				add_image_size($key, $size['width'], $size['height']);
			}
		}
	}

	/**
	 * Hook Get image thumbnail
	 *
	 * @param $downsize
	 * @param $id
	 * @param string $size
	 *
	 * @return array|bool
	 */
	public function image_downsize($downsize, $id, $size = 'medium') {
		$return = array();
		$metadata = wp_get_attachment_metadata($id);
		if (empty($metadata['file'])) {
			return false;
		}
		$img_url = wp_get_attachment_url($id);
		$img_url_basename = wp_basename($img_url);
		$intermediate = image_get_intermediate_size($id, $size);
		global $_wp_additional_image_sizes;
		if (!empty($intermediate['file'])) {
			$return[0] = str_replace($img_url_basename, $intermediate['file'], $img_url);
			$return[1] = $intermediate['width'];
			$return[2] = $intermediate['height'];
			$return[3] = true;
		} else {
			// if full thumbnail
			if ('full' == $size) {
				$return[0] = $img_url;
				$return[1] = $metadata['width'];
				$return[2] = $metadata['height'];
			} else {
				$filePath = get_attached_file($id);
				if (!is_string($size) || empty($_wp_additional_image_sizes[$size])) {
					return $return;
				}
				if ($this->crop_image($id, $size, $filePath)) {
					// check thumbnail was created
					return $this->image_downsize($downsize, $id, $size);
				} else {
					return $this->image_downsize($downsize, $id, 'full');
				}
			}
		}
		return $return;
	}

	/**
	 * Crop image
	 *
	 * @global array $_wp_additional_image_sizes
	 *
	 * @param int $id
	 * @param string $size
	 * @param string $filePath
	 *
	 * @return boolean
	 */
	public function crop_image($id, $size, $filePath) {
		global $_wp_additional_image_sizes;
		if (is_array($size))
			return false;
		$viewWidth = $_wp_additional_image_sizes[$size]['width'];
		$viewHeight = $_wp_additional_image_sizes[$size]['height'];
		$thumbPath = $this->get_thumbnail_path($id, $size);
		$image = wp_get_image_editor($filePath);
		if (is_wp_error($image)) {
			return false;
		}
		$metadata = wp_get_attachment_metadata($id);
		$image->resize($viewWidth, $viewHeight, true);
		$image->set_quality(80);
		// save image
		$save_data = $image->save($thumbPath);
		if (!is_wp_error($save_data)) {
			unset($save_data['path']);
			// update attachment meta data
			$metadata['sizes'][$size] = $save_data;
		}
		return wp_update_attachment_metadata($id, $metadata);
	}

	/**
	 * Get thumbnail path
	 *
	 * @param $id
	 * @param string $size
	 *
	 * @return string
	 */
	public function get_thumbnail_path($id, $size = 'medium') {
		$metadata = wp_get_attachment_metadata($id);
		$file = get_attached_file($id);
		if (!is_array($size) && !empty($metadata) && !empty($metadata['sizes']) && !empty($metadata['sizes'][$size]) && !empty($metadata['sizes'][$size]['file'])) {
			$file_name = $metadata['sizes'][$size]['file'];
		} else {
			// get global img sizes
			global $_wp_additional_image_sizes;
			$ext = pathinfo($file, PATHINFO_EXTENSION);
			$name = basename($file, "." . $ext);
			$width = $_wp_additional_image_sizes[$size]['width'];
			$height = $_wp_additional_image_sizes[$size]['height'];
			$file_name = "$name-{$width}x{$height}.$ext";
		}
		$dir = pathinfo($file, PATHINFO_DIRNAME);
		$path = "$dir/$file_name";
		return $path;
	}
}
