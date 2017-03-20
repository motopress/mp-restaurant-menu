<?php
namespace mp_restaurant_menu\classes\models\parents;

use mp_restaurant_menu\classes\Model;

/**
 * Class Term
 * @package mp_restaurant_menu\classes\models\parents
 */
class Term extends Model {
	
	protected static $instance;
	
	/**
	 * @return Term
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * Sort category by order
	 *
	 * @param $items
	 *
	 * @return mixed
	 */
	public function sort_taxonomy_order( $items ) {
		
		usort( $items, function ( $a, $b ) {
			if ( $a[ 'order' ] == $b[ 'order' ] ) {
				return 0;
			}
			
			return ( $a[ 'order' ] < $b[ 'order' ] ) ? - 1 : 1;
		} );
		
		return $items;
	}
	
	/**
	 * Get term order
	 *
	 * @param $mprm_term
	 *
	 * @return mixed|string
	 */
	public function get_term_order( $mprm_term ) {
		if ( ! empty( $mprm_term ) && is_object( $mprm_term ) ) {
			$order = $this->get_term_params( $mprm_term->term_id, 'order' );
		} elseif ( ! empty( $mprm_term ) && is_numeric( $mprm_term ) ) {
			$order = $this->get_term_params( $mprm_term, 'order' );
		}
		
		return ( empty( $order ) ? '0' : $order );
		
	}
	
	/**
	 * Get term params
	 *
	 * @param $term_id
	 * @param $field
	 *
	 * @return mixed
	 */
	public function get_term_params( $term_id, $field = '' ) {
		global $wp_version;
		if ( $wp_version < 4.4 ) {
			$term_meta = get_option( "mprm_taxonomy_{$term_id}" );
		} else {
			$term_meta = get_term_meta( $term_id, "mprm_taxonomy_$term_id", true );
		}
		// if update version wordpress  get old data
		if ( $wp_version >= 4.4 && empty( $term_meta ) ) {
			$term_meta = get_option( "mprm_taxonomy_{$term_id}" );
		}
		$defaults  = array(
			'iconname'     => '',
			'thumbnail_id' => '',
			'order'        => '0'
		);
		$term_meta = wp_parse_args( $term_meta, $defaults );
		// thumbnail value
		if ( ! empty( $term_meta[ 'thumbnail_id' ] ) ) {
			$term_meta[ 'thumb_url' ] = wp_get_attachment_thumb_url( $term_meta[ 'thumbnail_id' ] );
			$term_meta[ 'full_url' ]  = wp_get_attachment_url( $term_meta[ 'thumbnail_id' ] );
			$attachment_image_src     = wp_get_attachment_image_src( $term_meta[ 'thumbnail_id' ], 'mprm-big' );
			$term_meta[ 'image' ]     = $attachment_image_src[ 0 ];
		}
		if ( ! empty( $field ) ) {
			return empty( $term_meta ) ? false : ( isset( $term_meta[ $field ] ) ? $term_meta[ $field ] : $term_meta[ $field ] );
		} else {
			return $term_meta;
		}
	}
	
	
	/**
	 * Get terms
	 *
	 * @param string $taxonomy
	 * @param array /string $ids
	 *
	 * @return array
	 */
	public function get_terms( $taxonomy, $ids = array() ) {
		global $mprm_view_args, $wp_version;
		
		$terms = array();
		
		if ( ! empty( $ids ) ) {
			
			if ( ! is_array( $ids ) ) {
				$cat_ids = explode( ',', $ids );
			} else {
				$cat_ids = $ids;
			}
			
			foreach ( $cat_ids as $id ) {
				$terms[ $id ] = get_term_by( 'id', (int) ( $id ), $taxonomy );
			}
			
		} else if ( empty( $mprm_view_args[ 'categ' ] ) && empty( $mprm_view_args[ 'tags_list' ] ) ) {
			
			if ( $wp_version < 4.5 ) {
				$terms = get_terms( $taxonomy, array( 'parent' => 0 ) );
			} else {
				$terms = get_terms( array( 'taxonomy' => $taxonomy, 'parent' => 0 ) );
			}
		}
		
		return array_filter( $terms, array( $this, 'filter_array' ) );
	}
	
	/**
	 * Get term children
	 *
	 * @param $taxonomy
	 * @param $term_id
	 *
	 * @return array|int|\WP_Error
	 */
	public function get_term_children( $term_id, $taxonomy ) {
		$terms = get_term_children( $term_id, $taxonomy );
		
		return array_filter( $terms, array( $this, 'filter_array' ) );
	}
	
	/**
	 * Filter terms array (false/empty/null)
	 *
	 * @param $value
	 *
	 * @return bool
	 */
	public function filter_array( $value ) {
		return ( $value !== null && $value !== false && $value !== '' );
	}
}
