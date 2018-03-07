<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of HideCategoryAdmin
 *
 * @author WarewolfCZ
 */
class HideCategoryAdmin {

	private static $initiated = false;

	public static function init() {
		if ( !self::$initiated ) {
			self::init_hooks();
		}
	}

	public static function init_hooks() {
		// Add the options page and menu item.
		add_action( 'admin_menu', array( self::class, 'admin_menu' ) );
		self::register_mysettings();
		self::$initiated = true;
	}

	public static function admin_menu() {
		add_options_page(
				__( 'HideCategory Plugin Options', 'hcat' ), __( 'HideCategory', 'hcat' ), 'manage_options', 'hcat_options', array( self::class, 'hcat_plugin_options' )
		);
	}

	public static function register_mysettings() { // whitelist options
		add_option( 'mt_favorite_color', '', NULL, 'yes' );
	}

	/** Step 3. */
	public static function hcat_plugin_options() {
		if ( !current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
// variables for the field and option names 
		$opt_name = 'mt_favorite_color';
		$hidden_field_name = 'mt_submit_hidden';
		$data_field_name = 'mt_favorite_color';

		// Read in existing option value from database
		$opt_val = get_option( $opt_name );

		// See if the user has posted us some information
		// If they did, this hidden field will be set to 'Y'
		if ( isset( $_POST[$hidden_field_name] ) && $_POST[$hidden_field_name] == 'Y' ) {
			// Read their posted value
			$opt_val = $_POST[$data_field_name];

			// Save the posted value in the database
			update_option( $opt_name, $opt_val );

			// Put a "settings saved" message on the screen
			?>
			<div class="updated"><p><strong><?php _e( 'Settings saved.', 'hcat' ); ?></strong></p></div>
			<?php
		}

		// Now display the settings editing screen

		echo '<div class="wrap">';

		// header

		echo "<h2>" . __( 'HideCategory Plugin Settings', 'hcat' ) . "</h2>";

		// settings form
		?>

		<form name="form1" method="post" action="">
			<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

			<p><?php _e( "Favorite Color:", 'hcat' ); ?> 
				<input type="text" name="<?php echo $data_field_name; ?>" value="<?php echo $opt_val; ?>" size="20">
			</p><hr />

			<p class="submit">
				<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes' ) ?>" />
			</p>

		</form>
		</div>

		<?php
	}

}
