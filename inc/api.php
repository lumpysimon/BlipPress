<?php



/*
This class is very heavily - about 90%! - based on BlipPHP v1.2 by Graham Bradley
http://gbradley.co.uk/projects/BlipPHP
 */



class blip {



	var $version     = '0.1';
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

	private $auth = array( 'justme', 'subscribed' );



	// @@TODO@@
	// sort this out
	function __construct( $a, $s = '', $conf = array() ) {

		$this->api_key = $a;
		$this->secret  = $s;
		$this->test    = ( isset( $conf['test'] ) and $conf['test'] );
		$this->fatal   = ( isset( $conf['fatal'] ) and $conf['fatal'] );
		$this->token   = ( isset( $conf['token'] ) ? $conf['token'] : '' );

	}



	function get_temp_token( $permissions_id, $callback_url ) {

		$url = 'http://www.blipfoto.com/getpermission/v' . $this->api_version . '/' . $permissions_id;
		$url = add_query_arg( 'callback_url', $callback_url, $url );
		header( 'Location: ' . $url );

	}



	function get_user_token( $temp_token ) {

		$args = array(
			'params' => array( 'temp_token' => $temp_token ),
			'secure' => true
			);

		$url = $this->url( 'token', $args );

		return $this->request( $url );

	}



	// @@TODO@@
	// figure why i can't do this: function get_entry_by_id( $ids, $data = self::$data )
	function get_entry_by_id( $id ) {

		if ( ! $id = absint( $id ) )
			return;

		$args = array(
			'params' => array(
				'entry_id'        => $id,
				'return_location' => 1,
				'return_exif'     => 1
				)
			);

		$url = $this->url( 'entry', $args );

		return $this->request( $url );

	}



	function get_entry_by_date( $user, $date ) {

		$args = array(
			'params' => array(
				'query' => $date . ' by ' . $user,
				'max'   => 1
				)
			);

		$url = $this->url( 'search', $args );

		if ( $data = $this->request( $url ) and is_array( $data ) ) {
			return $this->get_entry_by_id( $data[0]->entry_id );
		}

	}



	function get_latest_entry_by_user( $user ) {

		$args = array(
			'params' => array(
				'display_name' => $user,
				'return_location' => 1,
				'return_exif'     => 1
				)
			);

		$url = $this->url( 'entry', $args );

		return $this->request( $url );

	}



	function get_latest_entries_by_user( $user, $num ) {

		$args = array(
			'params' => array(
				'query' => 'by ' . $user,
				'max'   => $num
				)
			);

		$url = $this->url( 'search', $args );

		if ( $data = $this->request( $url ) and is_array( $data ) ) {
			return $data;
		}

	}



	// @@TODO@@
	// nothing from here on down to 'internal functions' has been used yet...

	// check if user is allowed to post
	function get_date_validation( $date ) {

		$params   = $this->get_params( $this->token );
		$params[] = 'entry_date=' . $date;

		return $this->request(
						'get',
						'datevalidation/',
						$params
						);

	}



	// post an entry
	function post_entry( $postdata ) {

		$params = $this->get_params( $this->token );

		return $this->request(
						'post',
						'entry/',
						$params,
						$postdata
						);

	}



	// post a comment
	function post_comment( $entry_id, $comment ) {

		$params = $this->get_params( $this->token );

		return $this->request(
						'post',
						'comment/',
						$params,
						array(
							'entry_id' => $entry_id,
							'comment'  => $comment
							)
						);

	}



	// ------------------
	// internal functions
	// ------------------



	private function url( $resource, $args = array() ) {

		$defaults = array(
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

		$url = add_query_arg( array( 'api_key' => $this->api_key ), $url );

		if ( is_array( $params ) and !empty( $params ) ) {
			$url = add_query_arg( $params, $url );
		}

		if ( $auth_sig ) {

			$timestamp  = $this->create_time_stamp();
			$nonce = md5( uniqid( rand(), true ) );
			$token = '';

			if ( $user_auth ) {
				$token = $this->token;
			}

			$signature = md5( $timestamp . $nonce . $token . $this->secret );

			$url = add_query_arg(
				array(
					'timestamp' => $timestamp,
					'nonce'     => $nonce,
					'token'     => $token,
					'signature' => $signature
					),
				$url
				);

		}

		return $url;

	}



	private function create_time_stamp() {

		$transient = 'blipfoto-time';
		$timeout   = 600;
		$now       = time();

		if ( false === $diff = get_transient( $transient ) ) {

			$url = $this->url( 'time', array( 'auth_sig' => false ) );

			if ( $response = $this->request( $url ) ) {
				$diff = intval( $response->timestamp ) - $now;
				// if ( defined( 'WP_LOCAL_DEV' ) and WP_LOCAL_DEV ) {
				// 	$timeout = 10;
				// }
				set_transient( $transient, $diff, $timeout );
			}

		}

		return $now + $diff;

	}



	private function request( $url, $method = 'get', $postdata = null ) {

		switch ( $method ) {
			case 'get' :
				$response = wp_remote_get( $url, array( 'sslverify' => false ) );
			break;
			case 'post' :
				if ( in_array( 'image_upload', array_keys( $postdata ) ) ) {
					$postdata['image_upload'] = '@' . $postdata['image_upload'];
				}
				$response = wp_remote_post( $url, array( 'sslverify' => false ) );
			break;
		}

		if ( !is_wp_error( $response ) and isset( $response['body'] ) and $response['body'] ) {
			$json = json_decode( $response['body'] );
			if ( $data = $json->data ) {
				return $data;
			}
		}

		return false;

	}



	// @@TODO@@
	// sort this out
	private function raise_error( $e, $apie = null ) {

		if ( $this->fatal )
			throw new Exception( blip::$errors[$e] . ( $apie ? ' ' . $apie : '' ) );

		return false;

	}



} // class
