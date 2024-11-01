<?php

/**
 * Fired during plugin activation.
 *
 * @link    http://divyarthinfotech.com
 * @since   1.0.0
 *
 * @package Simple_Video_Post
 */

// Exit if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * SVP_Activator class.
 *
 * @since 1.0.0
 */
class SVP_Activator {

	/**
	 * Called when the plugin is activated.
	 *
	 * @since 1.0.0
	 */
	public static function activate() {	
		// Insert the plugin settings and default values for the first time
		$defaults = svp_get_default_settings();

		foreach ( $defaults as $option_name => $values ) {
			if ( false == get_option( $option_name ) ) {	
        		add_option( $option_name, $values );						
    		}
		}
		
		// Insert the plugin version
		add_option( 'svp_version', SVP_PLUGIN_VERSION );
	}

}
