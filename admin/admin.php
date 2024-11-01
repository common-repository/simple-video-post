<?php

/**
 * The admin-specific functionality of the plugin.
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
 * SVP_Admin class.
 *
 * @since 1.0.0
 */
class SVP_Admin {
	
	/**
	 * Insert missing plugin options.
	 *
	 * @since 1.5.2
	 */
	public function insert_missing_options() {		
		if ( SVP_PLUGIN_VERSION !== get_option( 'svp_version' ) ) {	
			$defaults = svp_get_default_settings();
			
			// Update the plugin version		
			update_option( 'svp_version', SVP_PLUGIN_VERSION );			
			
			// Insert the missing player settings
			$player_settings = get_option( 'svp_player_settings' );

			$new_player_settings = array();
			
			if ( ! array_key_exists( 'muted', $player_settings ) ) {
				$new_player_settings['muted'] = $defaults['svp_player_settings']['muted'];				
			}
			
			if ( ! array_key_exists( 'quality_levels', $player_settings ) ) {
				$new_player_settings['quality_levels'] = $defaults['svp_player_settings']['quality_levels'];				
			}
			
			if ( ! array_key_exists( 'use_native_controls', $player_settings ) ) {
				$new_player_settings['use_native_controls'] = $defaults['svp_player_settings']['use_native_controls'];				
			}

			if ( count( $new_player_settings ) ) {
				update_option( 'svp_player_settings', array_merge( $player_settings, $new_player_settings ) );
			}					
		}
	}
	
	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_style( 
			SVP_PLUGIN_SLUG . '-admin-font-awesome', 
			'https://use.fontawesome.com/releases/v5.15.3/css/all.css', 
			array(), 
			'1.0.0', 
			'all' 
		);
		wp_enqueue_style( 
			SVP_PLUGIN_SLUG . '-magnific-popup', 
			SVP_PLUGIN_URL . 'public/assets/css/magnific-popup.css', 
			array(), 
			'1.1.0', 
			'all' 
		);
		
		wp_enqueue_style( 
			SVP_PLUGIN_SLUG . '-admin', 
			SVP_PLUGIN_URL . 'admin/assets/css/admin.css', 
			array(), 
			SVP_PLUGIN_VERSION, 
			'all' 
		);
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_media();
		
		wp_enqueue_script( 
			SVP_PLUGIN_SLUG . '-admin', 
			SVP_PLUGIN_URL . 'admin/assets/js/admin.js', 
			array( 'jquery' ), 
			SVP_PLUGIN_VERSION, 
			false 
		);

		wp_localize_script( 
			SVP_PLUGIN_SLUG . '-admin', 
			'svp_admin', 
			array(
				'ajax_nonce' => wp_create_nonce( 'svp_ajax_nonce' ),
				'site_url'   => esc_url_raw( get_site_url() ),
				'i18n'       => array(
					'no_issues_slected' => __( 'Please select at least one issue.', 'simple-video-post' ),
					'quality_exists'    => __( 'Sorry, there is already a video with this quality level.', 'simple-video-post' )
				)				
			)
		);
	}

	/**
	 * Add a settings link on the plugin listing page.
	 *
	 * @since  1.0.0
	 * @param  array  $links An array of plugin action links.
	 * @return string $links Array of filtered plugin action links.
	 */
	public function plugin_action_links( $links ) {
		$settings_link = sprintf( 
			'<a href="%s">%s</a>', 
			esc_url( admin_url( 'options-general.php?page=svp_settings' ) ), 
			__( 'Settings', 'simple-video-post' ) 
		);

        array_unshift( $links, $settings_link );
		
    	return $links;
	}

	/**
	 * Sets the extension and mime type for .vtt files.
	 *
	 * @since  1.0.0
	 * @param  array  $types    File data array containing 'ext', 'type', and 'proper_filename' keys.
     * @param  string $file     Full path to the file.
     * @param  string $filename The name of the file (may differ from $file due to $file being in a tmp directory).
     * @param  array  $mimes    Key is the file extension with value as the mime type.
	 * @return array  $types    Filtered file data array.
	 */
	public function add_filetype_and_ext( $types, $file, $filename, $mimes ) {
		if ( false !== strpos( $filename, '.vtt' ) ) {			
			$types['ext']  = 'vtt';
			$types['type'] = 'text/vtt';
		}
	
		return $types;
	}

}
