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

	private $initiated = false;

	const HCAT_HIDDEN_FIELD_NAME = 'hcat_submit_hidden';

	public function init() {
		if ( !$this->initiated ) {
			$this->init_hooks();
		}
	}

	public function init_hooks() {
		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		$this->register_mysettings();
		$this->initiated = true;
	}

	public function admin_menu() {
		add_options_page(
				__( 'HideCategory Plugin Options', 'hcat' ), __( 'HideCategory', 'hcat' ), 'manage_options', 'hcat_options', array( $this, 'hcat_plugin_options' )
		);
	}

	public function register_mysettings() { // whitelist options
		add_option( HideCategory::HCAT_CATEGORIES_OPTION, HideCategory::HCAT_OPTIONS_DEFINITION, NULL, 'yes' );
	}

	private function hcat_process() {
		foreach ( HideCategory::HCAT_OPTIONS_DEFINITION as $key => $value ) {
			if ( !isset( $_POST[$key] ) ) {
				$_POST[$key] = array();
			}
			$options[$key] = $_POST[$key];
		}
		update_option( HideCategory::HCAT_CATEGORIES_OPTION, $options );
		$message = "<div class='updated'><p>" . ( __( 'Settings saved.', 'hcat' ) ) . "</p></div>";
		return $message;
	}

	public function hcat_plugin_options() {
		if ( !current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		// variables for the field and option names 
		// See if the user has posted us some information
		// If they did, this hidden field will be set to 'Y'
		if ( isset( $_POST[self::HCAT_HIDDEN_FIELD_NAME] ) && $_POST[self::HCAT_HIDDEN_FIELD_NAME] == 'Y' ) {
			$message = $this->hcat_process();

			if ( isset( $message ) ) {
				echo $message;
			}
		}


		$options = HideCategory::hcat_get_options();
		?>
		<div class="wrap">
			<h2><?php _e( 'HideCategory Plugin Settings', 'hcat' ); ?></h2>
			<p><?php _e( 'Use this page to select the categories you wish to exclude and where you would like to exclude them from.', 'hcat' ); ?></p>
			<form action="" method="post">
				<table class="widefat">
					<thead>
						<tr>
							<th scope="col"><?php _e( 'Category', 'hcat' ); ?></th>
							<?php foreach ( $options as $key => $option ) { ?>
							<th scope="col"><?php _e( HideCategory::HCAT_OPTIONS_NAMES[$key], 'hcat' ); ?></th>
							<?php } ?>
						</tr>
					</thead>
					<tbody id="the-list">
						<?php
						$args = array(
							'hide_empty' => 0,
							'order' => 'ASC'
						);
						$cats = get_categories( $args );
						$alternate = TRUE;
						foreach ( $cats as $cat ) {
							$alternate = !$alternate;
							?><tr class='<?php echo ($alternate ? 'alternate' : ''); ?>'>
								<th scope="row"><?php echo $cat->cat_name; ?></th>
								<?php foreach ( $options as $key => $option ) { ?>
									<td>
										<input type="checkbox" name="<?php echo $key; ?>[]" value="-<?php echo $cat->cat_ID ?>" <?php echo (in_array( '-' . $cat->cat_ID, $option ) ? 'checked="true"' : ''); ?>/>
									</td>
								<?php } ?>
							</tr>			
						<?php } ?>
				</table>
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e( 'Update', 'hcat' ); ?>" />
				</p>
				<input type="hidden" name="<?php echo self::HCAT_HIDDEN_FIELD_NAME; ?>" value="Y">
			</form>
		</div>

		<?php
	}

}
