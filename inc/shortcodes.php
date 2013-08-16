<?php



class blippress_shortcodes {



	public function __construct() {

		add_shortcode( 'blip',         array( $this, 'single_id'        ) );
		add_shortcode( 'blipdate',     array( $this, 'single_date'      ) );
		add_shortcode( 'blippostdate', array( $this, 'single_post_date' ) );
		add_shortcode( 'bliplatest',   array( $this, 'single_latest'    ) );
		add_shortcode( 'blips',        array( $this, 'multi_latest'     ) );
		add_shortcode( 'postblip',     array( $this, 'single_post'      ) );

	}



	function single_post() {

		if ( $entry_id = is_blipped() ) {
			echo $this->single_id( array( 'id' => $entry_id ) );
		}

	}



	function single_id( $atts ) {

		global $blippress, $blippress_cache;

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
		$transient = $blippress->prefix . 'single-' . $id;

		if ( false === $out = get_transient( $transient ) ) {

			$blip = new blipWP( $blippress->key, blippress_auth_option( 'secret' ) );

			if ( $data = $blip->get_entry_by_id( $id ) ) {
				$out = $this->render_single_blip( $data );
				set_transient( $transient, $out, $blippress_cache->transient_timeout );
				$blippress_cache->add( $transient );
			}

		}

		return $out;

	}



	function single_date( $atts ) {

		global $blippress, $blippress_cache;

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

		$transient = $blippress->prefix . 'date-' . $date;

		if ( false === $out = get_transient( $transient ) ) {

			$blip = new blipWP( $blippress->key, blippress_auth_option( 'secret' ) );

			if ( $data = $blip->get_entry_by_date( $user, $date ) ) {
				$out = $this->render_single_blip( $data );
				set_transient( $transient, $out, $blippress_cache->transient_timeout );
				$blippress_cache->add( $transient );
			}

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

		global $blippress, $blippress_cache;

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

		$transient = $blippress->prefix . 'latest-' . $user;

		if ( false === $out = get_transient( $transient ) ) {

			$blip = new blipWP( $blippress->key, blippress_auth_option( 'secret' ) );

			if ( $data = $blip->get_latest_entry_by_user( $user ) ) {
				$out = self::render_single_blip( $data );
				set_transient( $transient, $out, $blippress_cache->transient_timeout );
				$blippress_cache->add( $transient );
			}

		}

		return $out;

	}



	function multi_latest( $atts ) {

		global $blippress, $blippress_cache;

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

		$transient = $blippress->prefix . 'latest-' . $user . '-' . $num . '-' . $size;

		if ( false === $out = get_transient( $transient ) ) {

			$blip = new blipWP( $blippress->key, blippress_auth_option( 'secret' ) );

			if ( $data = $blip->get_latest_entries_by_user( $user, $num, $size ) ) {
				$out = self::render_multi_blips( $data, $size );
				set_transient( $transient, $out, $blippress_cache->transient_timeout );
				$blippress_cache->add( $transient );
			}

		}

		return $out;

	}



	private function meta( $data ) {

		if ( ! blippress_option( 'meta' ) )
			return;

		if ( ! isset( $data->exif ) )
			return;

		$fields = array( 'Model', 'FNumber', 'ExposureTime', 'FocalLength', 'ISO' );
		$values = array();

		foreach ( $fields as $field ) {

			if ( $value = trim( $data->exif->$field ) ) {

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

					case 'ISO' :
						$value = 'ISO ' . $value;
					break;

				}

				$values[$field] = $value;

			}

		}

		if ( ! empty( $values ) )
			return implode( ' : ', $values );

	}



	private function render_single_blip( $data ) {

		$out  = '<div class="blippress blippress-single">';

		$out .= '<img class="blippress-image blippress-image-single" id="blippress-image-single-' . $data->entry_id . '" src="' . $data->image . '"';
		if ( isset( $data->dimensions ) ) {
			$out .= ' height="' . $data->dimensions->height . '" width="' . $data->dimensions->width . '"';
		}
		$out .= '>';

		$out .= '<div class="blippress-info">';
		$out .= '<p class="blippress-title"><a title="View &quot;' . esc_attr( $data->title ) . '&quot;on Blipfoto" href="' . esc_attr( $data->url ) . '">&quot;' . esc_html( $data->title ) . '&quot; by ' . $data->display_name . '</a></p>';
		$out .= '<p class="blippress-details">' . date( get_option( 'date_format' ), strtotime( $data->date ) );
		if ( $meta = $this->meta( $data ) ) {
			$out .=  ' : ' . $meta;
		}
		$out .= '</p>';
		$out .= '</div>';

		$out .= '</div>';

		return $out;

	}



	private function render_multi_blips( $data, $size ) {

		$out = '<div class="blippress blippress-multi blippress-multi-' . $size . '">';

		foreach ( $data as $entry ) {

			$out .= '<div class="blippress-image blippress-image-multi blippress-image-multi-' . $size . '" id="blippress-image-multi-' . $entry->entry_id . '">';
			$out .= '<a href="' . esc_attr( $entry->url ) . '" title="View &quot;' . esc_attr( $entry->title ) . '&quot; (' . esc_attr( date( get_option( 'date_format' ), strtotime( $entry->date ) ) ) . ') by ' . esc_attr( $entry->display_name ) . ' on Blipfoto"><img src="' . esc_attr( $entry->thumbnail ) . '"></a>';
			$out .= '</div>';

		}

		$out .= '<div class="blippress-info"></div>';

		$out .= '</div>';

		return $out;

	}



}



global $blippress_shortcodes;

$blippress_shortcodes = new blippress_shortcodes;
