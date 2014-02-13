<?php



class blippress_single_widget extends WP_Widget {



	function __construct() {

		$opts = array(
			'classname'   => 'blippress_single_widget',
			'description' => 'Display a single Blipfoto entry'
			);

		parent::__construct(
			'blippress-single',
			'BlipPress Single',
			$opts
			);

	}



	function widget( $args, $instance ) {

		global $blippress_shortcodes;

		if ( ! blippress_check_permission() )
			return;

		extract( $args );

		if ( ! $entry_id = absint( $instance['entry_id'] ) )
			return;

		$title = apply_filters(
			'widget_title',
			empty( $instance['title'] ) ? 'Blipfoto' : $instance['title'],
			$instance,
			$this->id_base
			);

		echo $before_widget;

		echo $before_title . $title . $after_title;

		$args = array(
			'id'          => $entry_id,
			'show_date'   => false,
			'show_meta'   => false,
			'show_rating' => false
			);

		echo $blippress_shortcodes->single_id( $args );

		echo $after_widget;

	}



	function update( $new, $old ) {

		$instance = $old;

		$instance['title']    = strip_tags( $new['title'] );
		$instance['entry_id'] = absint( $new['entry_id'] );

		return $instance;

	}



	function form( $instance ) {

		$instance = wp_parse_args(
			(array) $instance,
			array(
				'title'    => 'Blipfoto',
				'entry_id' => 0
				)
			);

		$title = esc_attr( $instance['title'] );
		$entry_id   = absint( $instance['entry_id'] );

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
			$this->get_field_id( 'entry_id' ),
			'Entry ID of the blip:',
			$this->get_field_id( 'entry_id' ),
			$this->get_field_name( 'entry_id' ),
			$entry_id
			);

	}



} // class
