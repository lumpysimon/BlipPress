<?php



/*
This class is very heavily - about 90%! - based on BlipPHP v1.2 by Graham Bradley
http://gbradley.co.uk/projects/BlipPHP
 */



class blip_php {



	// @@TODO@@
	// update version number for first release
	private static $version = '0.1';

	// @@TODO@@
	// sort this out
	private static $errors = array(
								'1' => 'callback_url must be on the same domain',
								'2' => 'The API returned the following error code:',
								'3' => 'API timestamp not found',
								'4' => 'Could not connect via cURL',
								'5' => 'Could not connect via fopen'
								);

	private static $data = array(
							'display_name',
							'journal_title',
							'date',
							'title',
							'permalink',
							'thumbnail',
							'image',
							'image_width',
							'image_height',
							'exif:model|focal|exposure|aperture|iso'
							);

	protected $api_key;

	// @@TODO@@
	// don't think this is needed?
	protected $secret;

	public $id_token;

	private $auth = array( 'justme', 'subscribed' );
	private $timediff = null;



	// @@TODO@@
	// sort this out
	function __construct( $a, $s = '', $conf = array() ) {

		$this->api_key  = $a;
		$this->secret   = $s;
		$this->test     = ( isset( $conf['test'] ) and $conf['test'] );
		$this->fatal    = ( isset( $conf['fatal'] ) and $conf['fatal'] );
		$this->id_token = ( isset( $conf['id_token'] ) ? $conf['id_token'] : '' );

	}



	// return the version
	// @@TODO@@
	// do we really need this?
	public function version() {

		return self::$version;

	}



	// @@TODO@@
	// figure why i can't do this: function get_entry_by_id( $ids, $data = self::$data )
	function get_entry_by_id( $ids, $data = null ) {

		if ( ! $data ) {
			$data = self::$data;
		}

		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		$params   = $this->get_params();
		$params[] = 'entry_id=' . implode( ',', $ids );
		$params[] = $this->build_entry_params( $data );

		return $this->request( 'get', 'entry/', $params );

	}



	function get_entry_by_date( $display_name, $date, $data = null ) {

		if ( ! $data ) {
			$data = self::$data;
		}

		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		$params   = $this->get_params();
		$params[] = 'display_name=' . $display_name;
		$params[] = 'date=' . $date;
		$params[] = $this->build_entry_params( $data );

		return $this->request( 'get', 'entry/', $params );

	}



	function get_latest_entry_by_user( $display_name, $data = null ) {

		if ( ! $data ) {
			$data = self::$data;
		}

		$params   = $this->get_params();
		$params[] = 'display_name=' . $display_name;
		$params[] = 'date=latest';
		$params[] = $this->build_entry_params( $data );

		return $this->request(
						'get',
						'entry/',
						$params
						);

	}



	function get_latest_entries_by_user( $display_name, $num = 90 ) {

		$params   = $this->get_params();
		$params[] = 'query=by+' . $display_name;
		$params[] = 'max=' . $num;

		return $this->request(
						'get',
						'search/',
						$params
						);

	}



	// @@TODO@@
	// nothing from here on down to 'internal functions' has been used yet...

	// check if user is allowed to post
	function get_date_validation( $date ) {

		$params   = $this->get_params( $this->id_token );
		$params[] = 'entry_date=' . $date;

		return $this->request(
						'get',
						'datevalidation/',
						$params
						);

	}



	// post an entry
	function post_entry( $postdata ) {

		$params = $this->get_params( $this->id_token );

		return $this->request(
						'post',
						'entry/',
						$params,
						$postdata
						);

	}



	// post a comment
	function post_comment( $entry_id, $comment ) {

		$params = $this->get_params( $this->id_token );

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



	// send user off to permissions. This uses the old permission URL but it does save the extra configuration step.
	function permission_request( $callback ) {

		if ( ! preg_match( '/^https?:\/\/' . $_SERVER['SERVER_NAME'] . '/', $callback ) )
			return $this->raise_error( '1' );

		header( 'Location: http://www.blipfoto.com/extras/getpermission?api_key=' . $this->api_key . '&callback_url=' . rawurlencode( $callback ) );

	}



	// get token after callback
	function permission_response() {

		if ( !isset( $_GET['error'] ) or '0' != $_GET['error'] )
			return $this->raise_error( '2', $_GET['error'] );

		return array(
					'token'        => $_GET['token'],
					'display_name' => $_GET['display_name']
					);

	}



	function get_app_stats( $size = 'big', $color = 'color' ) {

		$params   = $this->get_app_params();
		$params[] = 'size=' . $size . '&color=' . $color;

		return $this->request(
						'get',
						'appstatistics/',
						$params
						);

	}



	// ------------------
	// internal functions
	// ------------------



	// build an array to hold parameters
	// using token authentication if specified
	// @@TODO@@
	// change the secret stuff
	private function get_params( $auth_token = '' ) {

		$params = 'api_key=' . $this->api_key . '&format=PHP&version=2';

		if ( $auth_token ) {

			if ( ! $this->secret )
				return false;

			$timestamp = $this->create_time_stamp();
			$nonce     = md5( uniqid( rand(), true ) );
			$sig       = md5( $timestamp . $nonce . $auth_token . $this->secret );
			$params    = $params . '&timestamp=' . $timestamp . '&nonce=' . $nonce . '&token=' . $auth_token . '&signature=' . $sig;

		}

		return array( $params );

	}



	// build an array to hold parameters
	// using app-only authentication
	// @@TODO@@
	// change the secret stuff
	private function get_app_params() {

		if ( ! $this->secret )
			return false;

		$params    = 'api_key=' . $this->api_key . '&format=PHP&version=2';
		$timestamp = $this->create_time_stamp();
		$nonce     = md5( uniqid( rand(), true ) );
		$sig       = md5( $nonce . $timestamp . $auth_token . $this->secret );

		return array( $params . '&timestamp=' . $timestamp . '&nonce=' . $nonce . '&signature=' . $sig );

	}



	// build the data parameter for entry data
	private function build_entry_params( $data ) {

		if ( isset( $data['exif'] ) ) {
			$data['exif'] = 'exif:' . implode( '|', $data['exif'] );
		}

		if ( isset( $data['comments'] ) ) {
			$data['comments'] = 'comments:' . implode( '|', $data['comments'] );
		}

		return 'data=' . implode( ',', $data );

	}



	// return a synched timestamp
	// @@TODO@@
	// test it
	private function create_time_stamp() {

		$now = time();

		if ( null === $this->timediff ) {

			$params = $this->get_params();
			$response = $this->request( 'get', 'time/', $params );

			if ( ! $response['data'] )
				return $this->raise_error( '3', $_GET['error'] );

			$this->timediff = intval( $response['data']['timestamp'] ) - $now;

		}

		return $now + $this->timediff;

	}



	// issue the request and return response using the WordPress HTTP API
	// @@TODO@@
	// test posting stuff
	// test failures
	private function request( $method, $resource, $params, $postdata = null ) {

		$url = sprintf(
					'http://api.blipfoto.com/%s/%s?%s',
					$method,
					$resource,
					urlencode( implode( '&', $params ) )
					);

		switch ( $method ) {
			case 'get' :
				$response = wp_remote_get( $url );
			break;
			case 'post' :
				if ( in_array( 'image_upload', array_keys( $postdata ) ) ) {
					$postdata['image_upload'] = '@' . $postdata['image_upload'];
				}
				$response = wp_remote_post( $url );
			break;
		}

		if ( !is_wp_error( $response ) and isset( $response['body'] ) and $response['body'] ) {
			return unserialize( $response['body'] );
		}

		return false;

	}



	// @@TODO@@
	// sort this out
	private function raise_error( $e, $apie = null ) {

		if ( $this->fatal )
			throw new Exception( blip_php::$errors[$e] . ( $apie ? ' ' . $apie : '' ) );

		return false;

	}



} // class
