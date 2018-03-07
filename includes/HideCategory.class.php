<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of HideCategory
 *
 * @author WarewolfCZ
 */
class HideCategory {

	private static $initiated = false;

	public static function init() {
		if ( !self::$initiated ) {
			self::init_hooks();
		}
	}

	/**
	 * Initializes WordPress hooks®
	 */
	private static function init_hooks() {
		self::$initiated = true;
	}

	/**
	 * Attached to activate_{ plugin_basename( __FILES__ ) } by register_activation_hook()
	 * @static
	 */
	public static function plugin_activation() {
		if ( version_compare( $GLOBALS['wp_version'], HCAT_MINIMUM_WP_VERSION, '<' ) ) {
			load_plugin_textdomain( 'hcat' );

			$message = '<strong>' . sprintf( esc_html__( 'HideCategory %s requires '
									. 'WordPress %s or higher.', 'hcat' ), HCAT_VERSION, HCAT_MINIMUM_WP_VERSION
					) . '</strong> ' .
					sprintf( __( 'Please <a href="%1$s">upgrade WordPress</a> to '
									. 'a current version</a>.', 'hcat' ), ''
							. 'https://codex.wordpress.org/Upgrading_WordPress' );

			Dropship::bail_on_activation( $message );
		}
	}

	/**
	 * Removes all connection options
	 * @static
	 */
	public static function plugin_deactivation() {
		return '';
	}

	private static function bail_on_activation( $message, $deactivate = true ) {
		?>
		<!doctype html>
		<html>
			<head>
				<meta charset="<?php bloginfo( 'charset' ); ?>">
				<style>
					* {
						text-align: center;
						margin: 0;
						padding: 0;
						font-family: "Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif;
					}
					p {
						margin-top: 1em;
						font-size: 18px;
					}
				</style>
			<body>
				<p><?php echo esc_html( $message ); ?></p>
			</body>
		</html>
		<?php
		if ( $deactivate ) {
			$plugins = get_option( 'active_plugins' );
			$hcat = plugin_basename( HCAT_PLUGIN_DIR . 'HideCategory.php' );
			$update = false;
			foreach ( $plugins as $i => $plugin ) {
				if ( $plugin === $hcat ) {
					$plugins[$i] = false;
					$update = true;
				}
			}

			if ( $update ) {
				update_option( 'active_plugins', array_filter( $plugins ) );
			}
		}
		exit;
	}

}
