<?php

/**
 * Recursive sanitation for an array
 *
 * @param $array
 *
 * @return mixed
 */
function mprm_recursive_sanitize_array( $array ) {

	if ( is_array( $array ) ) {

		foreach ( $array as $key => &$value ) {
			if ( is_array( $value ) ) {
				$value = mprm_recursive_sanitize_array($value);
			}
			else {
				$value = sanitize_text_field( $value );
			}
		}
	}

	return $array;
}

/**
 * Gets plugin's URL.
 *
 * @param string $path
 *
 * @return string
 */
function mprm_get_plugin_url( $path = '' ) {

	return plugins_url( $path, MP_RM_PLUGIN_FILE );

}