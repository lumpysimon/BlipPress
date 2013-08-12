<?php



class blippress_shortcodes {



	public function __construct() {

		add_shortcode( 'blip',         array( $this, 'single_id'        ) );
		add_shortcode( 'blipdate',     array( $this, 'single_date'      ) );
		add_shortcode( 'blippostdate', array( $this, 'single_post_date' ) );
		add_shortcode( 'bliplatest',   array( $this, 'single_latest'    ) );
		add_shortcode( 'blips',        array( $this, 'multi_latest'     ) );

	}



	function single_id( $atts ) {

		global $blippress;

		if ( ! blippress_check_permission() )
			return;

		extract(
			shortcode_atts(
				array(
					'id' => null
					),
				$atts
				)
			);

		if ( !$id or !is_numeric( $id ) )
			return;

		$id        = absint( $id );
		$transient = $blippress->transient_prefix . 'single-' . $id;

		if ( false === $out = get_transient( $transient ) ) {

			$blip = new blipWP( $blippress->key, blippress_auth_option( 'secret' ) );

			if ( $data = $blip->get_entry_by_id( $id ) ) {
				$out = $this->build_single_blip( $data );
			}

			set_transient( $transient, $out, $blippress->transient_timeout );

		}

		return $out;

	}



	function single_date( $atts ) {

		global $blippress;

		if ( ! blippress_check_permission() )
			return;

		extract(
			shortcode_atts(
				array(
					'user' => blippress_auth_option( 'username' ),
					'date' => date( 'd-m-Y' )
					),
				$atts
				)
			);

		if ( 10 != strlen( $date ) )
			return;

		$day   = substr( $date, 0, 2 );
		$month = substr( $date, 3, 2 );
		$year  = substr( $date, -4 );

		if ( ! checkdate( $month, $day, $year ) )
			return;

		$date = sprintf( '%s-%s-%s', $day, $month, $year );

		$transient = $blippress->transient_prefix . 'date-' . $date;

		if ( false === $out = get_transient( $transient ) ) {

			$blip = new blipWP( $blippress->key, blippress_auth_option( 'secret' ) );

			if ( $data = $blip->get_entry_by_date( $user, $date ) ) {
				$out = $this->build_single_blip( $data );
			}

			set_transient( $transient, $out, $blippress->transient_timeout );

		}

		return $out;

	}



	function single_post_date() {

		global $post;

		$args = array(
			'date' => mysql2date( 'd-m-Y', $post->post_date )
			);

		return $this->single_date( $args );

	}



	function single_latest( $atts ) {

		global $blippress;

		if ( ! blippress_check_permission() )
			return;

		extract(
			shortcode_atts(
				array(
					'user' => blippress_auth_option( 'username' )
					),
				$atts
				)
			);

		$transient = $blippress->transient_prefix . 'latest-' . $user;

		if ( false === $out = get_transient( $transient ) ) {

			$blip = new blipWP( $blippress->key, blippress_auth_option( 'secret' ) );

			if ( $data = $blip->get_latest_entry_by_user( $user ) ) {
				$out = self::build_single_blip( $data );
			}

			set_transient( $transient, $out, $blippress->transient_timeout );

		}

		return $out;

	}



	function multi_latest( $atts ) {

		global $blippress;

		if ( ! blippress_check_permission() )
			return;

		extract(
			shortcode_atts(
				array(
					'user' => blippress_auth_option( 'username' ),
					'num'  => blippress_option( 'num' ),
					'size' => blippress_option( 'size' )
					),
				$atts
				)
			);

		if ( ! $num = absint( $num ) ) {
			$num = $blippress->default_num;
		}

		$size = strtolower( $size );
		if ( ! in_array( $size, array( 'big', 'small' ) ) ) {
			$size = 'big';
		}

		$transient = $blippress->transient_prefix . 'latest-' . $user . '-' . $num . '-' . $size;

		if ( false === $out = get_transient( $transient ) ) {

			$blip = new blipWP( $blippress->key, blippress_auth_option( 'secret' ) );

			if ( $data = $blip->get_latest_entries_by_user( $user, $num, $size ) ) {
				$out = self::build_multi_blips( $data, $size );
			}

			set_transient( $transient, $out, $blippress->transient_timeout );

		}

		return $out;

	}



	private function build_single_blip( $data ) {

		$out  = '<div class="blip">';
		$out .= '<h2>' . $data->title . '</h2>';
		$out .= '<img src="' . $data->image . '"';
		if ( isset( $data->dimensions ) ) {
			$out .= ' height="' . $data->dimensions->height . '" width="' . $data->dimensions->width . '"';
		}
		$out .= '>';
		$out .= '<p>Taken on ' . date( get_option( 'date_format' ), strtotime( $data->date ) ) . '</p>';

		if ( isset( $data->exif ) ) {
			$fields = array( 'Model', 'FNumber', 'ExposureTime', 'FocalLength', 'ISO' );
			$values = array();
			foreach ( $fields as $field ) {
				if ( $value = $data->exif->$field ) {
					switch ( $field ) {
						case 'FNumber' :
							$value = 'f/' . $value;
						break;
						case 'ExposureTime' :
							$value .= 's';
						break;
						case 'FocalLength' :
							$value .= 'mm';
						break;
					}
					$values[$field] = $value;
				}
			}
			if ( !empty( $values ) ) {
				$out .= '<ul>';
				foreach ( $values as $k => $v ) {
					$out .= '<li>' . $k . ': ' . $v . '</li>';
				}
				$out .= '</ul>';
			}
		}

		$out .= '</div>';

		return $out;

	}



	private function build_multi_blips( $data, $size ) {

		$out = '<div class="blippress-gallery blippress-gallery-' . $size . '">';

		foreach ( $data as $entry ) {

			$out .= '<div class="blippress-thumb blippress-thumb-' . $size . '" id="blippress-thumb-' . $entry->entry_id . '">';
			$out .= '<a href="' . $entry->url . '" title="View &quot;' . $entry->title . '&quot; (' . date( get_option( 'date_format' ), strtotime( $entry->date ) ) . ') on Blipfoto"><img src="' . $entry->thumbnail . '"></a>';
			$out .= '</div>';

		}

		$out .= '</div>';

		return $out;

	}



}



global $blippress_shortcodes;

$blippress_shortcodes = new blippress_shortcodes;
