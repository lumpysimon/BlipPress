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

		global $blippress, $blippress_cache;

		if ( ! blippress_check_permission() )
			return;

		extract(
			shortcode_atts(
				array(
					'id'        => null,
					'show_date' => true,
					'show_meta' => blippress_option( 'meta' )
					),
				$atts
				)
			);

		if ( !$id or !is_numeric( $id ) )
			return;

		if ( 'false' == $show_meta ) {
			$show_meta = false;
		}

		$id = absint( $id );

		$transient = blippress_prefix() . $id;
		if ( $show_date ) {
			$transient .= '-d';
		}
		if ( $show_meta ) {
			$transient .= '-m';
		}

		if ( false === $out = get_transient( $transient ) ) {

			$blip = new blipWP( $blippress->key, blippress_auth_option( 'secret' ) );

			if ( $data = $blip->get_entry_by_id( $id ) ) {
				$out = $this->render_single_blip( $data, $show_date, $show_meta );
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
					'user'      => blippress_auth_option( 'username' ),
					'date'      => date( 'd-m-Y' ),
					'show_date' => true,
					'show_meta' => blippress_option( 'meta' )
					),
				$atts
				)
			);

		if ( ! $this->sanitise_user_attribute( $user ) )
			return;

		if ( 10 != strlen( $date ) )
			return;

		$day   = substr( $date, 0, 2 );
		$month = substr( $date, 3, 2 );
		$year  = substr( $date, -4 );

		if ( ! checkdate( $month, $day, $year ) )
			return;

		$date = sprintf( '%s-%s-%s', $day, $month, $year );

		$transient = blippress_prefix() . $date . '-' . substr( $user, 0, 20 );
		if ( $show_date ) {
			$transient .= '-d';
		}
		if ( $show_meta ) {
			$transient .= '-m';
		}

		if ( false === $out = get_transient( $transient ) ) {

			$blip = new blipWP( $blippress->key, blippress_auth_option( 'secret' ) );

			if ( $data = $blip->get_entry_by_date( $user, $date ) ) {
				$out = $this->render_single_blip( $data, $show_date, $show_meta );
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
					'user'      => blippress_auth_option( 'username' ),
					'show_date' => true,
					'show_meta' => blippress_option( 'meta' )
					),
				$atts
				)
			);

		if ( ! $this->sanitise_user_attribute( $user ) )
			return;

		$transient = blippress_prefix() . substr( $user, 0, 20 );
		if ( $show_date ) {
			$transient .= '-d';
		}
		if ( $show_meta ) {
			$transient .= '-m';
		}

		if ( false === $out = get_transient( $transient ) ) {

			$blip = new blipWP( $blippress->key, blippress_auth_option( 'secret' ) );

			if ( $data = $blip->get_latest_entry_by_user( $user ) ) {
				$out = self::render_single_blip( $data, $show_date, $show_meta );
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

		if ( ! $this->sanitise_user_attribute( $user ) )
			return;

		if ( ! $num = absint( $num ) ) {
			$num = $blippress->default_num;
		}

		$size = strtolower( $size );
		if ( ! in_array( $size, array( 'big', 'small' ) ) ) {
			$size = 'big';
		}

		$transient = blippress_prefix() . substr( $user, 0, 20 ) . '-' . $num . '-' . substr( $size, 0, 1 );

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



	private function render_single_blip( $data, $show_date, $show_meta ) {

		if ( ! $title = trim( $data->title ) ) {
			$title = 'Untitled';
		}

		$out  = '<div class="blippress blippress-single" ' . apply_filters( 'blippress_div_width', 'style="width: ' . $data->dimensions->width . 'px;"', $data->dimensions->width ) . '>';

		$out .= '<img class="blippress-image blippress-image-single" id="blippress-image-single-' . $data->entry_id . '" src="' . esc_url( $data->image ) . '"';
		if ( isset( $data->dimensions ) ) {
			$out .= ' height="' . $data->dimensions->height . '" width="' . $data->dimensions->width . '"';
		}
		$out .= '>';

		$out .= '<div class="blippress-info">';
		$out .= '<p class="blippress-title"><a title="View &quot;' . esc_attr( $title ) . '&quot;on Blipfoto" href="' . esc_url( $data->url ) . '">' . $title . '</a> by ' . $data->display_name . '</p>';
		if ( ( $show_meta and $meta = $this->meta( $data ) ) or $show_date ) {
			$out .= '<p class="blippress-details">';
			if ( $show_date ) {
				$out .= date( get_option( 'date_format' ), strtotime( $data->date ) );
			}
			if ( $show_meta and $meta ) {
				$out .=  ' : ' . $meta;
			}
			$out .= '</p>';
		}
		$out .= '</div>';

		$out .= '</div>';

		return $out;

	}



	private function render_multi_blips( $data, $size ) {

		$out = '<div class="blippress blippress-multi blippress-multi-' . $size . '">';

		foreach ( $data as $entry ) {

			if ( ! $title = trim( $entry->title ) ) {
				$title = 'Untitled';
			}

			$out .= '<div class="blippress-image blippress-image-multi blippress-image-multi-' . esc_attr( $size ) . '" id="blippress-image-multi-' . $entry->entry_id . '">';
			$out .= '<a href="' . esc_url( $entry->url ) . '" title="View &quot;' . esc_attr( $title ) . '&quot; (' . esc_attr( date( get_option( 'date_format' ), strtotime( $entry->date ) ) ) . ') by ' . esc_attr( $entry->display_name ) . ' on Blipfoto"><img src="' . esc_url( $entry->thumbnail ) . '"></a>';
			$out .= '</div>';

		}

		$out .= '<div class="blippress-info"></div>';

		$out .= '</div>';

		return $out;

	}



	function sanitise_user_attribute( $user ) {

		return trim( wp_kses( $user, array() ) );

	}



}



global $blippress_shortcodes;

$blippress_shortcodes = new blippress_shortcodes;
