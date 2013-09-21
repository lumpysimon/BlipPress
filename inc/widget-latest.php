<?php



class blippress_latest_widget extends WP_Widget {



	function __construct() {

		$opts = array(
			'classname'   => 'blippress_latest_widget',
			'description' => 'Display the most recent Blipfoto entry'
			);

		parent::__construct(
			'blippress-latest',
			'BlipPress Latest',
			$opts
			);

	}



	function widget( $args, $instance ) {

		global $blippress_shortcodes;

		if ( ! blippress_check_permission() )
			return;

		extract( $args );

		$username = empty( $instance['username'] ) ? blippress_auth_option( 'username' ) : wp_kses( $instance['username'], array() );

		if ( ! $username )
			return;

		$title = apply_filters(
			'widget_title',
			empty( $instance['title'] ) ? $username . ' on Blipfoto' : $instance['title'],
			$instance,
			$this->id_base
			);

		echo $before_widget;

		echo $before_title . $title . $after_title;

		$args = array(
			'user'      => $username,
			'show_date' => false,
			'show_meta' => false
			);

		echo $blippress_shortcodes->single_latest( $args );

		echo $after_widget;

	}



	function update( $new, $old ) {

		$instance = $old;

		$instance['title']    = strip_tags( $new['title'] );
		$instance['username'] = wp_kses( $new['username'], array() );

		return $instance;

	}



	function form( $instance ) {

		$instance = wp_parse_args(
			(array) $instance,
			array(
				'title'    => blippress_auth_option( 'username' ) . ' on Blipfoto',
				'username' => blippress_auth_option( 'username' )
				)
			);

		$title    = esc_attr( $instance['title'] );
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
			'<p><label for="%s">%s</label><input type="text" class="widefat" id="%s" name="%s" value="%s"></p>',
			$this->get_field_id( 'username' ),
			'Blipfoto username:',
			$this->get_field_id( 'username' ),
			$this->get_field_name( 'username' ),
			$username
			);

	}



}
