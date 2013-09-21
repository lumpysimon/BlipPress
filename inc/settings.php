<?php



class blippress_settings {



	var $slug   = 'settings';
	var $option = 'general';
	var $notice = array();



	function __construct() {

		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'add_page' ) );

	}



	function slug() {

		return blippress_prefix() . $this->slug;

	}



	function option() {

		return blippress_prefix() . $this->option;

	}



	function init() {

		register_setting(
			$this->option(),
			$this->option(),
			array( $this, 'validate' )
			);

	}



	function validate( $inputs ) {

		global $blippress_cache;

		$new = array();

		$opts = array(
			'num'        => 'numeric',
			'size'       => 'text',
			'css'        => 'boolean',
			'meta'       => 'boolean',
			'post-types' => 'array'
			);

		if ( $inputs ) {
			foreach ( $inputs as $k => $v ) {
				switch ( $opts[$k] ) {
					case 'numeric' :
						$new[$k] = (int) $v;
					break;
					case 'text' :
						$new[$k] = wp_kses( $v );
					break;
					case 'boolean' :
						$new[$k] = (int) $v;
					break;
					case 'array' :
						foreach ( $v as $array_k => $array_v ) {
							$new[$k][$array_k] = (int) $array_v;
						}
					break;
				}
				if ( ! $new[$k] ) {
					unset( $new[$k] );
				}
			}
		}

		$blippress_cache->clear();

		return $new;

	}



	function get() {

		if ( ! $opts = get_option( $this->option() ) ) {
			$opts = $this->defaults();
			$this->update( $opts );
		}

		return $opts;

	}



	function update( $opts ) {

		global $blippress_cache;

		update_option( $this->option(), $opts );
		$blippress_cache->clear();

	}



	function add_page() {

		add_options_page(
			'BlipPress Settings',
			'BlipPress',
			'manage_options',
			$this->slug(),
			array( $this, 'render_page' )
			);

	}



	function defaults() {

		global $blippress;

		return array(
			'num'        => $blippress->default_num,
			'size'       => 'big',
			'css'        => 1,
			'meta'       => 1,
			'post-types' => array( 'post' => 1 )
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

		if ( !isset( $opts['post-types'] ) or !is_array( $opts['post-types'] ) ) {
			$opts['post-types'] = array();
		}

		?>

		<div class="wrap">

			<h2>BlipPress Settings</h2>

			<?php if ( blippress_check_permission() ) { ?>

				<form method="post" action="options.php">

					<?php settings_fields( $this->option() ); ?>

					<table class="form-table">

						<tbody>

							<tr valign="top">
								<th scope="row">Number of images</th>
								<td>
									<input name="<?php echo $this->option(); ?>[num]" class="small-text" type="number" step="1" min="1" max="40" value="<?php echo $opts['num']; ?>">
									<p class="description">How many images to display when retrieving multiple blips.<br />This can be manually overridden in the shortcode.</p>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row">Size</th>
								<td>
									<fieldset>
										<label title="Big">
											<input name="<?php echo $this->option(); ?>[size]" type="radio" value="big" <?php checked( $opts['size'], 'big' ); ?>>
											<span>Big</span>
										</label><br>
										<label title="Small">
											<input name="<?php echo $this->option(); ?>[size]" type="radio" value="small" <?php checked( $opts['size'], 'small' ); ?>>
											<span>Small</span>
										</label>
									<p class="description">The thumbnail size to show when retrieving multiple blips.</p>
									<fieldset>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row">Styling</th>
								<td>
									<label for="<?php echo $this->option(); ?>[css]">
										<input name="<?php echo $this->option(); ?>[css]" type="checkbox" value="1" <?php checked( $opts['css'] ); ?>>
										Use BlipPress styles?
									</label>
									<p class="description">Untick if you prefer to use your own CSS styling.</p>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row">Metadata</th>
								<td>
									<label for="<?php echo $this->option(); ?>[meta]">
										<input name="<?php echo $this->option(); ?>[meta]" type="checkbox" value="1" <?php checked( $opts['meta'] ); ?>>
										Show image metadata?
									</label>
									<p class="description">Choose whether to display the camera, aperture, exposure, focal length and ISO.<br />Please note that images will always show the date taken and a link to the entry on Blipfoto.</p>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row">Post types</th>
								<td>
									<fieldset>
										<?php foreach ( $this->types() as $type => $label ) { ?>
											<label for="<?php echo $this->option(); ?>[post-types][<?php echo $type; ?>]">
												<input name="<?php echo $this->option(); ?>[post-types][<?php echo $type; ?>]" type="checkbox" value="1" <?php checked( array_key_exists( $type, $opts['post-types'] ) ); ?>>
												<?php echo $label; ?>
											</label><br>
										<?php } ?>
									<p class="description">Choose which post types blips can be created from.</p>
									<fieldset>
								</td>
							</tr>

						</tbody>

					</table>

					<p class="submit">
						<input class="button-primary" name="blippress-submit" type="submit" value="Save Settings">
					</p>

				</form>

			<?php } else {
				echo blippress_authenticate_message( ' to show this page.' );
			} ?>

		</div>

		<?php

	}



} // class



global $blippress_settings;

$blippress_settings = new blippress_settings;
