<?php



function blippress_prefix() {

	global $blippress;

	return $blippress->prefix;

}



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



function update_blippress_meta( $meta, $data, $post_id = null ) {

	global $post;

	if ( ! $post_id ) {
		$post_id = $post->ID;
	}

	update_post_meta( $post_id, blippress_prefix() . $meta, $data );

}



function get_blippress_meta( $meta, $post_id = null ) {

	global $post;

	if ( ! $post_id ) {
		$post_id = $post->ID;
	}

	return get_post_meta( $post_id, blippress_prefix() . $meta, true );

}



function get_blippress_id( $post_id = null ) {

	global $blippress_post;

	return get_blippress_meta( $blippress_post->entry_post_meta, $post_id );

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

	$opts = blippress_options();

	if ( isset( $opts[$opt] ) )
		return $opts[$opt];

	return false;

}



function blippress_options() {

	global $blippress_settings;

	return $blippress_settings->get();

}



function blippress_post_types() {

	$opts = blippress_options();

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

	if ( $types = blippress_option( 'post-types' ) )
		return array_key_exists( $type, $types );

	return false;

}



function blippress_auth_option( $opt ) {

	$opts = blippress_auth_options();

	if ( isset( $opts[$opt] ) )
		return $opts[$opt];

	return false;

}



function blippress_auth_options() {

	global $blippress_authentication;

	return get_option( $blippress_authentication->option() );

}



function blippress_exif_fields( $keys_only = false ) {

	$fields = array(
		'camera'            => 'Camera',
		'aperture'          => 'Aperture',
		'shutter_speed'     => 'Exposure',
		'focal_length'      => 'Focal length',
		'iso'               => 'ISO',
		'created_timestamp' => 'Date taken'
		);

	if ( $keys_only )
		return array_keys( $fields );

	return $fields;

}



function blippress_website( $protocol = true ) {

	global $blippress;

	return $protocol ? 'http://' . $blippress->website : $blippress->website;

}



function blippress_plugin_page() {

	global $blippress;

	return $blippress->plugin_page;

}
