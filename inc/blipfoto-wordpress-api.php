<?php



/*

	-----------
	Description
	-----------

	Blipfoto WordPress API v1

	A class to simplify working with the Blipfoto API in a WordPress plugin or theme.



	-------
	Credits
	-------

	By Simon Blackbourn (https://twitter.com/lumpysimon)
	https://github.com/lumpysimon/blipfoto-wordpress-api

	This class is a rewrite of BlipPHP v1.2 written by Graham Bradley
	A newer version of that class now exists: https://github.com/Blipfoto/blipfoto-api-php

	This version uses the WordPress wp_remote_get and wp_remote_post functions instead of cURL
	and also provides various helper functions.



	-------
	License
	-------

	Released under the GPL license:
	http://www.opensource.org/licenses/gpl-license.php

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.



	---------
	Changelog
	---------

	1.0
	- We have take-off



	-----
	To Do
	-----

	- Error handling

*/



class blipWP {



	var $version     = '1';
	var $api_version = '3';

	// @@TODO@@
	// sort this out
	var $errors = array(
		'1' => 'callback_url must be on the same domain',
		'2' => 'The API returned the following error code:',
		'3' => 'API timestamp not found',
		'4' => 'Could not connect via cURL',
		'5' => 'Could not connect via fopen'
		);

	protected $api_key;
	protected $secret;



	function __construct( $a, $s = '', $conf = array() ) {

		$this->api_key = $a;
		$this->secret  = $s;
		$this->token   = ( isset( $conf['token'] ) ? $conf['token'] : '' );

	}



	function get_temp_token( $permissions_id, $callback_url ) {

		$url = 'http://www.blipfoto.com/getpermission/v' . $this->api_version . '/' . $permissions_id;
		$url = add_query_arg( 'callback_url', $callback_url, $url );
		header( 'Location: ' . $url );
		exit( 0 );

	}



	function get_user_token( $temp_token ) {

		$args = array(
			'params' => array( 'temp_token' => $temp_token ),
			'secure' => true
			);

		$url = $this->url( 'token', $args );

		$json = $this->request( $url );

		if ( $data = $json->data ) {
			return $data;
		}

	}



	function get_entry_by_id( $id ) {

		if ( ! $id = absint( $id ) )
			return;

		$args = array(
			'params' => array(
				'entry_id'          => $id,
				'return_location'   => 1,
				'return_exif'       => 1,
				'return_dimensions' => 1
				)
			);

		$url = $this->url( 'entry', $args );

		$json = $this->request( $url );

		if ( $data = $json->data ) {
			return $data;
		}

	}



	function get_entry_by_date( $user, $date ) {

		$args = array(
			'params' => array(
				'query' => $date . ' by ' . $user,
				'max'   => 1
				)
			);

		$url = $this->url( 'search', $args );

		$json = $this->request( $url );

		if ( $data = $json->data and is_array( $data ) ) {
			return $this->get_entry_by_id( $data[0]->entry_id );
		}

	}



	function get_latest_entry_by_user( $user ) {

		$args = array(
			'params' => array(
				'display_name'      => $user,
				'return_location'   => 1,
				'return_exif'       => 1,
				'return_dimensions' => 1
				)
			);

		$url = $this->url( 'entry', $args );

		$json = $this->request( $url );

		if ( $data = $json->data ) {
			return $data;
		}

	}



	function get_latest_entries_by_user( $user, $num, $size ) {

		$args = array(
			'params' => array(
				'query' => 'by ' . $user,
				'max'   => $num,
				'size'  => $size
				)
			);

		$url = $this->url( 'search', $args );

		$json = $this->request( $url );

		if ( $data = $json->data and is_array( $data ) ) {
			return $data;
		}

	}



	function validate_date( $date ) {

		$args = array(
			'params' => array(
				'date' => $date
				)
			);

		$url = $this->url( 'datevalidation', $args );

		$json = $this->request( $url );

		if ( isset( $json->message ) ) {
			return true;
		}

		return false;

	}



	function post_entry( $postdata ) {

		$url      = $this->url( 'entry', array( 'auth_sig' => false ) );
		$sig      = $this->signature( true );
		$postdata = array_merge( $postdata, $sig );
		$json     = $this->request( $url, 'post', $postdata );

		return $json;

	}



	// ------------------
	// internal functions
	// ------------------



	private function url( $resource, $args = array() ) {

		$defaults = array(
			'api_key'   => true,
			'format'    => 'json',
			'params'    => null,
			'user_auth' => false,
			'auth_sig'  => true,
			'secure'    => false
			);

		$args = wp_parse_args( $args, $defaults );

		extract( $args, EXTR_SKIP );

		$url  = $secure ? 'https' : 'http';
		$url .= '://api.blipfoto.com/v' . $this->api_version . '/';
		$url .= $resource . '.' . $format;

		if ( $api_key ) {
			$url = add_query_arg( array( 'api_key' => $this->api_key ), $url );
		}

		if ( is_array( $params ) and !empty( $params ) ) {
			$url = add_query_arg( $params, $url );
		}

		if ( $auth_sig ) {

			$sig = $this->signature( $user_auth );
			$url = add_query_arg(
				array(
					'timestamp' => $sig['timestamp'],
					'nonce'     => $sig['nonce'],
					'token'     => $sig['token'],
					'signature' => $sig['signature']
					),
				$url
				);

		}

		return $url;

	}



	private function signature( $user_auth ) {

		$sig = array();

		$sig['timestamp'] = $this->create_time_stamp();
		$sig['nonce']     = str_shuffle( md5( uniqid( rand(), true ) ) );
		$sig['token']     = '';

		if ( $user_auth ) {
			$sig['token'] = $this->token;
		}

		$sig['signature'] = md5( $sig['timestamp'] . $sig['nonce'] . $sig['token'] . $this->secret );

		return $sig;

	}



	private function create_time_stamp() {

		$transient = 'blipfoto-time';
		$timeout   = 600;
		$now       = time();

		if ( false === $diff = get_transient( $transient ) ) {

			$url = $this->url( 'time', array( 'auth_sig' => false ) );

			$json = $this->request( $url );

			if ( isset( $json->data->timestamp) ) {
				$diff = intval( $json->data->timestamp ) - $now;
				set_transient( $transient, $diff, $timeout );
			}

		}

		return $now + $diff;

	}



	private function request( $url, $method = 'get', $postdata = null ) {

		if ( 'post' == $method and ! is_array( $postdata ) )
			return false;

		switch ( $method ) {
			case 'get' :
				$response = wp_remote_get( $url, array( 'sslverify' => false ) );
			break;
			case 'post' :
				$data = array(
					'sslverify' => false,
					'timeout'   => 60,
					'body'      => $postdata
					);
				$response = wp_remote_post(
					$url,
					$data
					);
			break;
		}

		if ( !is_wp_error( $response ) and isset( $response['body'] ) and $response['body'] ) {
			return json_decode( $response['body'] );
		}

		return false;

	}



} // class
