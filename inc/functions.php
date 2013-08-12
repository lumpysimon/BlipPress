<?php



function blippress_latest( $args = array() ) {

	global $blippress, $blippress_shortcodes;

	$defaults = array(
		'user' => blippress_auth_option( 'username' ),
		'num'  => blippress_option( 'num' ),
		'size' => blippress_option( 'size' )
		);

	$args = wp_parse_args( $args, $defaults );

	echo $blippress_shortcodes->multi_latest( $args );

}



function get_blippress_url( $id ) {

	if ( ! is_numeric( $id ) )
		return;

	return sprintf(
				'http://blipfoto.com/entry/%s',
				absint( $id )
				);

}



function blippress_url( $id ) {

	echo get_blippress_url( $id );

}



function get_blippress_user_url( $user, $protocol = 'http://' ) {

	return sprintf(
				'%sblipfoto.com/%s',
				$protocol,
				$user
				);

}



function blippress_user_url( $user, $protocol = 'http://' ) {

	echo get_blippress_user_url( $user, $protocol );

}



function get_blippress_id( $post_id = null ) {

	global $post, $blippress_post;

	if ( ! $post_id ) {
		$post_id = $post->ID;
	}

	return get_post_meta( $post_id, $blippress_post->postmeta, true );

}



function blippress_id( $post_id = null ) {

	echo get_blippress_id( $post_id );

}



function is_blipped( $post_id = null ) {

	return get_blippress_id( $post_id );

}



function blippress_check_permission() {

	if ( !blippress_auth_option( 'username' ) or !blippress_auth_option( 'token' ) or !blippress_auth_option( 'secret' ) )
		return false;

	return true;

}



function blippress_authenticate_message( $text = '' ) {

	global $blippress_authentication;

	return 'Please <a href="' . $blippress_authentication->page_url() . '">authenticate your Blipfoto account</a>' . $text;

}



function blippress_option( $opt ) {

	global $blippress_settings;

	$opts = $blippress_settings->get();

	if ( isset( $opts[$opt] ) )
		return $opts[$opt];

	return false;

}



function blippress_options() {

	global $blippress_settings;

	return $blippress_settings->get();

}



function blippress_post_types() {

	global $blippress_settings;

	$opts = $blippress_settings->get();

	if ( isset( $opts['post-types'] ) and is_array( $opts['post-types'] ) )
		return array_keys( $opts['post-types'] );

	return false;

}



function is_blippress_post_type( $type = null ) {

	global $post;

	if ( ! $type ) {
		if ( ! $type = $post->post_type ) {
			return false;
		}
	}

	if ( $types = blippress_option( 'post-types' ) ) {
		return array_key_exists( $type, $types );
	}

	return false;

}



function blippress_auth_option( $opt ) {

	global $blippress_authentication;

	$opts = $blippress_authentication->get_option();

	if ( isset( $opts[$opt] ) )
		return $opts[$opt];

	return false;

}



function blippress_exif_fields( $keys_only = false ) {

	$fields = array(
		'camera'        => 'Camera',
		'aperture'      => 'Aperture',
		'shutter_speed' => 'Exposure',
		'focal_length'  => 'Focal length',
		'iso'           => 'ISO'
		);

	if ( $keys_only )
		return array_keys( $fields );

	return $fields;

}
