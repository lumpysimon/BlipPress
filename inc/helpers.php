<?php


if ( ! function_exists( 'meta_handler_nonce_value' ) ) {

	function meta_handler_nonce_value( $post_id, $meta_name ) {
		return wp_create_nonce( meta_handler_nonce_name( $post_id, $meta_name ) );
	}

}



if ( ! function_exists( 'meta_handler_nonce_name' ) ) {

	function meta_handler_nonce_name( $post_id, $meta_name ) {
		$meta_name = sanitize_title( $meta_name );
		return "handle_meta_{$post_id}_{$meta_name}_nonce";
	}

}



if ( ! function_exists( 'meta_handler_nonce_field' ) ) {

	function meta_handler_nonce_field( $post_id, $meta_name ) {
		$name  = meta_handler_nonce_name( $post_id, $meta_name );
		$value = meta_handler_nonce_value( $post_id, $meta_name );
		return '<input type="hidden" name="' . $name . '" value="' . $value . '" />';
	}

}



if ( ! function_exists( 'verify_meta_handler_nonce' ) ) {

	function verify_meta_handler_nonce( $post_id, $meta_name ) {
		$name = meta_handler_nonce_name( $post_id, $meta_name );
		if ( isset( $_REQUEST[$name] ) )
			return wp_verify_nonce( $_REQUEST[$name], $name );
		return false;
	}

}



function blippress_alphanumeric( $str ) {
	return ereg_replace( '[^A-Za-z0-9]', '', $str );
}



function blippress_lowercase_alphanumeric( $str ) {
	return strtolower( blippress_alphanumeric( $str ) );
}
