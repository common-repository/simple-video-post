<?php

/**
 * The plugin bootstrap file.
 *
 * @link            http://divyarthinfotech.com
 * @since           1.0.0
 * @package         Simple_Video_Post
 *
 * @wordpress-plugin
 * Plugin Name:     Simple Video Post
 * Plugin URI:      http://divyarthinfotech.com/simple-video-post
 * Description:     A simple video post plugin that support YouTube/Vimeo/Facebook/Dailymotion like video sharing website. No coding required.
 * Version:         1.0.0
 * Author:          Divyarth Infotech
 * Author URI:      http://divyarthinfotech.com
 * License:         GPL-2.0+
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:     simple-video-post
 * Domain Path:     /languages
 * 
 */
// Exit if accessed directly
if ( !defined( 'WPINC' ) ) {
    die;
}

// The current version of the plugin
if ( !defined( 'SVP_PLUGIN_VERSION' ) ) {
    define( 'SVP_PLUGIN_VERSION', '1.0.0' );
}
// The unique identifier of the plugin
if ( !defined( 'SVP_PLUGIN_SLUG' ) ) {
    define( 'SVP_PLUGIN_SLUG', 'simple-video-post' );
}
// Path to the plugin directory
if ( !defined( 'SVP_PLUGIN_DIR' ) ) {
    define( 'SVP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
// URL of the plugin
if ( !defined( 'SVP_PLUGIN_URL' ) ) {
    define( 'SVP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
// The plugin file name
if ( !defined( 'SVP_PLUGIN_FILE_NAME' ) ) {
    define( 'SVP_PLUGIN_FILE_NAME', plugin_basename( __FILE__ ) );
}
// The global plugin variable
$svp = array();

if ( !function_exists( 'svp_activate' ) ) {
    /**
     * The code that runs during plugin activation.
     * This action is documented in includes/activator.php
     */
    function svp_activate()
    {
        require_once SVP_PLUGIN_DIR . 'includes/activator.php';
        SVP_Activator::activate();
    }
    
    register_activation_hook( __FILE__, 'svp_activate' );
}


if ( !function_exists( 'svp_deactivate' ) ) {
    /**
     * The code that runs during plugin deactivation.
     * This action is documented in includes/deactivator.php
     */
    function svp_deactivate()
    {
        require_once SVP_PLUGIN_DIR . 'includes/deactivator.php';
        SVP_Deactivator::deactivate();
    }
    
    register_deactivation_hook( __FILE__, 'svp_deactivate' );
}


if ( !function_exists( 'svp_run' ) ) {
    /**
     * Begins execution of the plugin.
     *
     * @since 1.0.0
     */
    function svp_run()
    {
        require SVP_PLUGIN_DIR . 'includes/init.php';
        $plugin = new SVP_Init();
        $plugin->run();
    }
    
    svp_run();
}


if ( !function_exists( 'svp_uninstall' ) ) {
    /**
     * The code that runs during plugin uninstallation.
     * This action is documented in includes/uninstall.php
     */
    function svp_uninstall()
    {
        require_once SVP_PLUGIN_DIR . 'includes/uninstall.php';
        SVP_Uninstall::uninstall();
    }
}
