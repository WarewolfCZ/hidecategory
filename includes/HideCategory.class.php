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

	private $initiated = false;

	const HCAT_CATEGORIES_OPTION = 'hcat_hidden_categories';
	const HCAT_OPTIONS_DEFINITION = array(
		'exclude_main' => array(),
		'exclude_menu' => array(),
		'exclude_feed' => array(),
		'exclude_archives' => array(),
		'exclude_search' => array()
	);
	const HCAT_OPTIONS_NAMES = array(
		'exclude_main' => 'Exclude from Front Page?',
		'exclude_menu' => 'Exclude from Menus',
		'exclude_feed' => 'Exclude from Feeds?',
		'exclude_archives' => 'Exclude from All Archives?',
		'exclude_search' => 'Exclude from Search?'
	);

	public function init() {
		if ( !$this->initiated ) {
			$this->init_hooks();
		}
	}

	/**
	 * Initializes WordPress hooks®
	 */
	private function init_hooks() {
		$this->initiated = true;
		add_filter( 'pre_get_posts', array( $this, 'hcat_exclude_categories' ) );
		add_filter( "widget_categories_args", array( $this, "hcat_exclude_widget_categories" ) );
		add_filter( 'wp_nav_menu_items', array( $this, 'hcat_nav_menu_items' ) );
		add_filter( 'nav_menu_css_class', array( $this, 'hcat_category_nav_class' ), 10, 2 );
	}

	/**
	 * Attached to activate_{ plugin_basename( __FILES__ ) } by register_activation_hook()
	 * @static
	 */
	public function plugin_activation() {
		if ( version_compare( $GLOBALS['wp_version'], HCAT_MINIMUM_WP_VERSION, '<' ) ) {
			load_plugin_textdomain( 'hcat' );

			$message = '<strong>' . sprintf( esc_html__( 'HideCategory %s requires '
									. 'WordPress %s or higher.', 'hcat' ), HCAT_VERSION, HCAT_MINIMUM_WP_VERSION
					) . '</strong> ' .
					sprintf( __( 'Please <a href="%1$s">upgrade WordPress</a> to '
									. 'a current version</a>.', 'hcat' ), ''
							. 'https://codex.wordpress.org/Upgrading_WordPress' );

			$this->bail_on_activation( $message );
		}
	}

	/**
	 * Removes all connection options
	 * @static
	 */
	public function plugin_deactivation() {
		return '';
	}

	private function bail_on_activation( $message, $deactivate = true ) {
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

	public static function hcat_get_options() {
		$options = get_option( self::HCAT_CATEGORIES_OPTION );

		if ( !is_array( $options ) || empty( $options ) ) {
			$options = self::array_clone( self::HCAT_OPTIONS_DEFINITION );
			update_option( self::HCAT_CATEGORIES_OPTION, $options );
		}
		return $options;
	}

	private static function array_clone( $array ) {
		array_walk_recursive( $array, function(&$value) {
			if ( is_object( $value ) ) {
				$value = clone $value;
			}
		} );
		return $array;
	}

	function hcat_exclude_widget_categories( $args ) {
		$options = self::hcat_get_options();
		if ( isset( $options['exclude_menu'] ) ) {
			$excludes = array();
			foreach ( $options['exclude_menu'] as $value ) {
				$excludes[] = -1 * $value;
			}

			$args["exclude"] = implode( ',', $excludes ); // The IDs of the excluded categories
		}
		return $args;
	}

	public function hcat_exclude_categories( $query ) {
		$backtrace = debug_backtrace();
		$array2[0] = "";
		unset( $array2[0] );
		$options = self::hcat_get_options();

		//Exclude calls from the Yoast SEO Video Sitemap plugin
		if ( $query->is_home && !$this->in_array_recursive( 'WPSEO_Video_Sitemap', $backtrace ) ) {
			$mbccount = 0;
			foreach ( $options['exclude_main'] as $value ) {
				$array2[$mbccount] = $value;
				$mbccount++;
			}
			$query->set( 'category__not_in', $array2 );
		}

		if ( $query->is_feed ) {
			$mbccount = 0;
			foreach ( $options['exclude_feed'] as $value ) {
				$array2[$mbccount] = $value;
				$mbccount++;
			}
			$query->set( 'category__not_in', $array2 );
		}

		if ( !current_user_can( 'manage_options' ) && $query->is_search ) {
			$mbccount = 0;
			foreach ( $options['exclude_search'] as $value ) {
				$array2[$mbccount] = $value;
				$mbccount++;
			}
			$query->set( 'category__not_in', $array2 );
		}

		if ( !current_user_can( 'manage_options' ) && $query->is_archive ) {
			$mbccount = 0;
			foreach ( $options['exclude_archives'] as $value ) {
				$array2[$mbccount] = $value;
				$mbccount++;
			}
			$query->set( 'category__not_in', $array2 );
		}

		return $query;
	}

	public function hcat_category_nav_class( $classes, $item ) {
		if ( 'category' == $item->object ) {
			$classes[] = 'hcat-menu-category-' . $item->object_id;
		}
		return $classes;
	}

	public function hcat_nav_menu_items( $menu ) {
		$lines = explode( "\n", $menu );
		$result = '';
		if ( !current_user_can( 'manage_options' ) ) {
			$options = self::hcat_get_options();
			if ( isset( $options['exclude_menu'] ) ) {
				$excludes = array();
				foreach ( $options['exclude_menu'] as $value ) {
					$excludes[] = -1 * $value;
				}

				foreach ( $lines as $line ) {
					foreach ( $excludes as $id ) {
						if ( strpos( $line, 'hcat-menu-category-' . $id ) !== false ) {
							continue 2;
						}
					}
					$result .= $line . "\n";
				}
			}
		} else {
			$result = $menu;
		}
		return $result;
	}

	private function in_array_recursive( $needle, $haystack ) {
		$found = false;

		foreach ( $haystack as $item ) {
			if ( $item === $needle ) {
				$found = true;
				break;
			} elseif ( is_array( $item ) ) {
				$found = in_array_recursive( $needle, $item );
				if ( $found ) {
					break;
				}
			}
		}

		return $found;
	}

}
