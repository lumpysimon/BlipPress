<?php



function blip_latest( $args = array() ) {

	global $blipfoto, $blipfoto_shortcodes;

	$defaults = array(
		'user' => blip_auth_option( 'username' ),
		'num'  => $blipfoto->default_num,
		'size' => blip_option( 'size' )
		);

	$args = wp_parse_args( $args, $defaults );

	echo $blipfoto_shortcodes->multi_latest( $args );

}



function get_blip_url( $id ) {

	if ( ! is_numeric( $id ) )
		return;

	return sprintf(
				'http://blipfoto.com/entry/%s',
				absint( $id )
				);

}



function blip_url( $id ) {

	echo get_blip_url( $id );

}



function get_blip_id( $post_id = null ) {

	global $post, $blipfoto_post;

	if ( ! $post_id ) {
		$post_id = $post->ID;
	}

	return get_post_meta( $post_id, $blipfoto_post->postmeta, true );

}



function blip_id( $post_id = null ) {

	echo get_blip_id( $post_id );

}



function is_blipped( $post_id = null ) {

	return get_blip_id( $post_id );

}



function blip_check_permission() {

	if ( !blip_auth_option( 'username' ) or !blip_auth_option( 'token' ) or !blip_auth_option( 'secret' ) )
		return false;

	return true;

}



function blip_authenticate_message( $text = '' ) {

	global $blipfoto_authentication;

	return 'Please <a href="' . $blipfoto_authentication->page_url() . '">authenticate your Blipfoto account</a>' . $text;

}



function blip_option( $opt ) {

	global $blipfoto_settings;

	$opts = $blipfoto_settings->get();

	if ( isset( $opts[$opt] ) )
		return $opts[$opt];

	return false;

}



function blip_post_types() {

	global $blipfoto_settings;

	$opts = $blipfoto_settings->get();

	if ( isset( $opts['post-types'] ) and is_array( $opts['post-types'] ) )
		return array_keys( $opts['post-types'] );

	return false;

}



function is_blip_post_type( $type = null ) {

	global $post;

	if ( ! $type ) {
		if ( ! $type = $post->post_type ) {
			return false;
		}
	}

	if ( $types = blip_option( 'post-types' ) ) {
		return array_key_exists( $type, $types );
	}

	return false;

}



function blip_auth_option( $opt ) {

	global $blipfoto_authentication;

	$opts = $blipfoto_authentication->get_option();

	if ( isset( $opts[$opt] ) )
		return $opts[$opt];

	return false;

}



function blip_exif_fields( $keys_only = false ) {

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
