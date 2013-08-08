<?php



class blipPressShortcodes {



	public function __construct() {

		add_shortcode( 'blip',       array( $this, 'single_id'     ) );
		add_shortcode( 'blipdate',   array( $this, 'single_date'   ) );
		add_shortcode( 'bliplatest', array( $this, 'single_latest' ) );
		add_shortcode( 'blips',      array( $this, 'multi_latest'  ) );

	}



	function single_id( $atts ) {

		global $blippress;

		if ( ! check_blip_options() )
			return;

		extract( shortcode_atts( array( 'id' => null ), $atts ) );

		if ( !$id or !is_numeric( $id ) )
			return;

		$id       = absint( $id );
		$out      = '';
		$blip     = new BlipPHP( $blippress->key );
		$response = $blip->get_entry_by_id( $id );

		if ( $response and $response['data'] and count( $response['data'] ) ) {

			$data = array_shift( $response['data'] );

			$out = self::build_single_blip( $data );

		}

		return $out;

	}



	function single_date( $atts ) {

		global $blippress;

		if ( ! check_blip_options() )
			return;

		extract(
			shortcode_atts(
				array(
					'user' => blip_option( 'username' ),
					'date' => date( 'Y-m-d' )
					),
				$atts
				)
			);

		if ( 10 != strlen( $date ) )
			return;

		$month = substr( $date, 5, 2 );
		$day   = substr( $date, -2 );
		$year  = substr( $date, 0, 4 );

		if ( ! checkdate( $month, $day, $year ) )
			return;

		$date = sprintf( '%s-%s-%s', $year, $month, $day );

		$out      = '';
		$blip     = new BlipPHP( $blippress->key );
		$response = $blip->get_entry_by_date( $user, $date );

		if ( $response and $response['data'] and count( $response['data'] ) ) {

			$data = array_shift( $response['data'] );

			$out = self::build_single_blip( $data );

		} else {
			$out = '<p>Whoops! Couldn\'t retrieve ' . $user . '\'s blip for ' . date( get_option( 'date_format' ), strtotime( $date ) ) . '</p>';
		}

		return $out;

	}



	function single_latest( $atts ) {

		global $blippress;

		if ( ! check_blip_options() )
			return;

		extract(
			shortcode_atts(
				array(
					'user' => blip_option( 'username' )
					),
				$atts
				)
			);

		$out      = '';
		$blip     = new BlipPHP( $blippress->key );
		$response = $blip->get_latest_entry_by_user( $user );

		if ( $response and $response['data'] and count( $response['data'] ) ) {

			$data = array_shift( $response['data'] );

			$out = self::build_single_blip( $data );

		}

		return $out;

	}



	function multi_latest( $atts ) {

		global $blippress;

		if ( ! check_blip_options() )
			return;

		extract(
			shortcode_atts(
				array(
					'user' => blip_option( 'username' ),
					'num'  => $opts['num']
					),
				$atts
				)
			);

		$num = absint( $num );

		if ( ! $num ) {
			$num = $blippress->default_num;
		}

		$out      = '';
		$blip     = new BlipPHP( $blippress->key );
		$response = $blip->get_latest_entries_by_user( $user, $num );

		if ( $response and $response['data'] and count( $response['data'] ) ) {

			$out = self::build_multi_blips( $response['data'] );

		}

		return $out;

	}



	private function build_single_blip( $data ) {

		global $blippress;

		$out .= '<div class="blip">';
		$out .= '<h2>' . $data['title'] . '</h2>';
		$out .= '<img src="' . $data['image'] . '" height="' . $data['image_height'] . '" width="' . $data['image_width'] . '">';
		$out .= '<p>Taken on:' . date( get_option( 'date_format' ), strtotime( $data['date'] ) ) . '</p>';

		if ( isset( $data['exif'] ) and count( $data['exif'] ) ) {
			$exif_data = array();
			foreach ( $data['exif'] as $key => $value ) {
				if ( $value and array_key_exists( $key, $blippress->exif ) ) {
					switch ( $key ) {
						case 'aperture' :
							$value = 'f/' . $value;
						break;
						case 'exposure' :
							$value .= '&quot;';
						break;
						case 'focal' :
							$value .= 'mm';
						break;
					}
					$exif_data[] = $blippress->exif[$key] . ': ' . $value;
				}
			}
			if ( count( $exif_data ) ) {
				$out .= '<p>' . implode( '<br>', $exif_data ) . '</p>';
			}
		}

		$out .= '</div>';

		return $out;

	}



	private function build_multi_blips( $data ) {

		global $blippress;

		$out .= '<div class="blipgallery">';

		foreach ( $data as $i => $entry ) {

			$out .= '<div class="blip-thumb" id="blip-thumb-' . $entry['entry_id'] . '" style="float:left;margin:0 20px 20px 0;">';
			$out .= '<h3>' . $entry['title'] . '</h3>';
			$out .= '<img src="' . $entry['thumbnail'] . '" height="124" width="124">';
			$out .= '<p>' . date( get_option( 'date_format' ), strtotime( $entry['date'] ) ) . '</p>';
			$out .= '</div>';

		}

		$out .= '</div>';

		return $out;

	}



}



$blippress_shortcodes = new blipPressShortcodes;



?>