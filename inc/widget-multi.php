<?php



class blippress_multi_widget extends WP_Widget {



	function __construct() {

		$opts = array(
			'classname'   => 'blippress_multi_widget',
			'description' => 'Display a thumbnail gallery of your latest Blipfoto entries'
			);

		parent::__construct(
			'blippress-multi',
			'BlipPress Multi',
			$opts
			);

	}



	function widget( $args, $instance ) {

		if ( ! blippress_check_permission() )
			return;

		extract( $args );

		$username = empty( $instance['username'] ) ? blippress_auth_option( 'username' ) : wp_kses( $instance['username'], array() );

		if ( ! $username )
			return;

		$title = apply_filters(
			'widget_title',
			empty( $instance['title'] ) ? 'My Blipfoto journal' : $instance['title'],
			$instance,
			$this->id_base
			);

		$num = empty( $instance['num'] ) ? blippress_option( 'num' ) : absint( $instance['num'] );

		echo $before_widget;

		echo $before_title . $title . $after_title;

		$args = array(
			'num'  => $num,
			'size' => 'small',
			'user' => $username
			);

		echo blippress_latest( $args );

		echo $after_widget;

	}



	function update( $new, $old ) {

		$instance = $old;

		$instance['title']    = strip_tags( $new['title'] );
		$instance['num']      = absint( $new['num'] );
		$instance['username'] = wp_kses( $new['username'], array() );

		if ( ! $instance['num'] ) {
			$instance['num'] = blippress_option( 'num' );
		}

		if ( ! $instance['username'] ) {
			$instance['username'] = blippress_auth_option( 'username' );
		}

		return $instance;

	}



	function form( $instance ) {

		$instance = wp_parse_args(
			(array) $instance,
			array(
				'title'    => 'My Blipfoto journal',
				'num'      => blippress_option( 'num' ),
				'username' => blippress_auth_option( 'username' )
				)
			);

		$title    = esc_attr( $instance['title'] );
		$num      = absint( $instance['num'] );
		$username = wp_kses( $instance['username'], array() );

		echo sprintf(
			'<p><label for="%s">%s</label><input type="text" class="widefat" id="%s" name="%s" value="%s"></p>',
			$this->get_field_id( 'title' ),
			_e( 'Title:' ),
			$this->get_field_id( 'title' ),
			$this->get_field_name( 'title' ),
			$title
			);

		echo sprintf(
			'<p><label for="%s">%s</label><input type="number" id="%s" name="%s" value="%s" step="1" min="1" max="40"></p>',
			$this->get_field_id( 'num' ),
			'Number of blips to show:',
			$this->get_field_id( 'num' ),
			$this->get_field_name( 'num' ),
			$num
			);

		echo sprintf(
			'<p><label for="%s">%s</label><input type="text" class="widefat" id="%s" name="%s" value="%s"></p>',
			$this->get_field_id( 'username' ),
			'Blipfoto username:',
			$this->get_field_id( 'username' ),
			$this->get_field_name( 'username' ),
			$username
			);

	}



} // class
