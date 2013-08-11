<?php



class blipfoto_settings {



	var $slug   = 'blipfoto-settings';
	var $option = 'blipfoto-general';
	var $notice = array();



	function __construct() {

		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'add_page' ) );

	}



	function init() {

		register_setting(
			$this->option,
			$this->option,
			array( $this, 'validate' )
			);

	}



	// @TODO@
	// do it
	function validate( $inputs ) {

		$new = array();

		if ( $inputs ) {
			foreach ( $inputs as $k => $v ) {
				$new[$k] = $v;
			}
		}

		return $new;

	}



	function get() {

		if ( ! $opts = get_option( $this->option ) ) {
			$opts = $this->defaults();
			$this->update( $opts );
		}

		return $opts;

	}



	function update( $opts ) {

		update_option( $this->option, $opts );

	}



	function add_page() {

		add_options_page(
			'Blipfoto Settings',
			'Blipfoto',
			'manage_options',
			$this->slug,
			array( $this, 'render_page' )
			);

	}



	function defaults() {

		global $blipfoto;

		return array(
			'num'        => $blipfoto->default_num,
			'css'        => 1,
			'post-types' => array( 'post' )
			);

	}



	function types() {

		$types = array();

		$post_types = array(
			'post' => get_post_type_object( 'post' ),
			'page' => get_post_type_object( 'page' )
			);

		if ( $custom_post_types = get_post_types( array( '_builtin' => false ), 'objects' ) ) {
			$post_types = array_merge( $post_types, $custom_post_types );
		}
		foreach ( $post_types as $name => $object ) {
			$types[$name] = $object->labels->name;
		}

		return $types;

	}



	function render_page() {

		$opts = $this->get();

		?>

		<div class="wrap">

			<h2>Blipfoto Settings</h2>

			<?php if ( check_blip_permission() ) { ?>

				<form method="post" action="options.php">

					<?php settings_fields( $this->option ); ?>

					<table class="form-table">

						<tbody>

							<tr valign="top">
								<th scope="row">Number of images</th>
								<td>
									<input name="<?php echo $this->option; ?>[num]" class="small-text" type="number" step="1" min="0" value="<?php echo $opts['num']; ?>">
									<p class="description">How many images to display when retrieving multiple blips. This can be manually overridden in the shortcode.</p>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row">Styling</th>
								<td>
									<label for="<?php echo $this->option; ?>[css]">
										<input name="<?php echo $this->option; ?>[css]" type="checkbox" value="1" <?php checked( $opts['css'] ); ?>>
										Use in-built stylesheet?
									</label>
									<p class="description">Untick if you prefer to use your own styling. [@TODO@ LINK TO STYLE GUIDE]</p>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row">Post types</th>
								<td>
									<fieldset>
										<?php foreach ( $this->types() as $type => $label ) { ?>
											<label for="<?php echo $this->option; ?>[post-types][<?php echo $type; ?>]">
												<input name="<?php echo $this->option; ?>[post-types][<?php echo $type; ?>]" type="checkbox" value="1" <?php checked( array_key_exists( $type, $opts['post-types'] ) ); ?>>
												<?php echo $label; ?>
											</label><br>
										<?php } ?>
									<p class="description">Choose which post types blips can be created from</p>
									<fieldset>
								</td>
							</tr>

						</tbody>

					</table>

					<p class="submit">
						<input class="button-primary" name="blipfoto-submit" type="submit" value="Save Settings">
					</p>

				</form>

			<?php } else {
				echo blipfoto_authenticate_message( ' to show this page.' );
			} ?>

		</div>

		<?php

	}



} // class



$blipfoto_settings = new blipfoto_settings;
