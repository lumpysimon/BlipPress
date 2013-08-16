<?php



/**
 * Because WordPress has no transient garbage clearing mechanism,
 * we store an array  of transient names that we have set,
 * so we can later clear them out when needed.
 */
class blippress_cache {



	var $option            = 'transients';
	var $transient_timeout = 600; // 10 minutes



	function name() {

		global $blippress;

		return $blippress->prefix . $this->option;

	}



	function get() {

		return get_option( $this->name() );

	}



	function set( $transients ) {

		update_option( $this->name(), $transients );

	}



	function clear() {

		if ( $transients = $this->get() ) {

			foreach ( $transients as $transient ) {
				delete_transient( $transient );
			}

			delete_option( $this->name() );

		}

	}



	function add( $transient ) {

		if ( $transients = $this->get() ) {
			if ( ! in_array( $transient, $transients ) ) {
				$transients[] = $transient;
			}
		} else {
			$transients = array( $transient );
		}

		$this->set( $transients );

	}



} // class



global $blippress_cache;

$blippress_cache = new blippress_cache;
