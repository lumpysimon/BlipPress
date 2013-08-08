<?php



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

	global $post;

	if ( ! $post_id ) {
		$post_id = $post->ID;
	}

	return get_post_meta( $post_id, 'blipfoto-id', true );

}



function blip_id( $post_id = null ) {

	echo get_blip_id( $post_id );

}



function is_blipped( $post_id = null ) {

	return get_blip_id( $post_id ) ? true : false;

}



function check_blip_permission() {

	$opts = get_option( 'blipfoto' );

	if ( !isset( $opts['token'] ) or !$opts['token'] or !isset( $opts['username'] ) or !$opts['username'] )
		return false;

	return true;

}



function check_blip_options() {

	$opts = get_option( 'blipfoto' );

	if ( !isset( $opts['post-types'] ) or !$opts['post-types'] )
		return false;

	return true;

}



function blip_option( $opt ) {

	$opts = get_option( 'blipfoto' );

	if ( isset( $opts[$opt] ) )
		return $opts[$opt];

	return false;

}



function is_blip_post_type( $type = null ) {

	global $post;

	if ( ! $type ) {
		$type = $post->post_type;
	}

	if ( $type and $types = blip_option( 'post-types' ) and in_array( $type, $types ) )
		return true;

	return false;

}
