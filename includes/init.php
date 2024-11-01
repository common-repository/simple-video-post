<?php

/**
 * The file that defines the core plugin class.
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
 * SVP_Init - The main plugin class.
 *
 * @since 1.0.0
 */
class SVP_Init {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    SVP_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * Get things started.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function load_dependencies() {
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once SVP_PLUGIN_DIR . 'includes/loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once SVP_PLUGIN_DIR . 'includes/i18n.php';
		
		/**
		 * The class responsibe for the video player related functionalities.
		 */
		require_once SVP_PLUGIN_DIR . 'includes/player.php';
		
		/**
		 * The file that holds the general helper functions.
		 */
		require_once SVP_PLUGIN_DIR . 'includes/functions.php';

		/**
		 * The classes responsible for defining all actions that occur in the admin area.
		 */
		require_once SVP_PLUGIN_DIR . 'admin/admin.php';
		require_once SVP_PLUGIN_DIR . 'admin/videos.php';
		require_once SVP_PLUGIN_DIR . 'admin/settings.php';

		/**
		 * The classes responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once SVP_PLUGIN_DIR . 'public/public.php';		
		//require_once SVP_PLUGIN_DIR . 'public/videos.php';		
		require_once SVP_PLUGIN_DIR . 'public/video.php';
		
		$this->loader = new SVP_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function set_locale() {
		$i18n = new SVP_i18n();
		$this->loader->add_action( 'plugins_loaded', $i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function define_admin_hooks() {
		// Hooks common to all admin pages
		$admin = new SVP_Admin();
				
		$this->loader->add_action( 'admin_init', $admin, 'insert_missing_options', 1 );		
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );
		
		$this->loader->add_filter( 'plugin_action_links_' . SVP_PLUGIN_FILE_NAME, $admin, 'plugin_action_links' );
		$this->loader->add_filter( 'wp_check_filetype_and_ext', $admin, 'add_filetype_and_ext', 10, 4 );	
		
		// Hooks specific to the videos page
		$videos = new SVP_Admin_Videos();
		$this->loader->add_action( 'before_delete_post', $videos, 'before_delete_post' );
		
		if ( is_admin() ) {		
			$this->loader->add_action( 'add_meta_boxes', $videos, 'add_meta_boxes' );
			$this->loader->add_action( 'save_post', $videos, 'save_meta_data', 10, 2 );
			$this->loader->add_action( 'manage_svp_videos_posts_custom_column', $videos, 'custom_column_content', 10, 2 );			
			
			$this->loader->add_filter( 'manage_edit-svp_videos_columns', $videos, 'get_columns' );
		}
		
		// Hooks specific to the settings page
		$settings = new SVP_Admin_Settings();
		
		$this->loader->add_action( 'admin_menu', $settings, 'svp_options_page' );
		$this->loader->add_action( 'admin_init', $settings, 'admin_init' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function define_public_hooks() {
		// Hooks common to all public pages
		$public = new SVP_Public();

		$this->loader->add_action( 'template_redirect', $public, 'template_redirect', 0 );
		$this->loader->add_action( 'init', $public, 'init' );
		$this->loader->add_action( 'wp_loaded', $public, 'maybe_flush_rules' );
		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue_scripts' );		
		$this->loader->add_action( 'wp_ajax_svp_set_cookie', $public, 'set_gdpr_cookie' );
		$this->loader->add_action( 'wp_ajax_nopriv_svp_set_cookie', $public, 'set_gdpr_cookie' );
						
		$this->loader->add_filter( 'has_post_thumbnail', $public, 'has_post_thumbnail', 10, 3 );
		$this->loader->add_filter( 'post_thumbnail_html', $public, 'post_thumbnail_html', 10, 5 );
		
		// Hooks specific to the single video page
		$video = new SVP_Public_Video();
		
		$this->loader->add_action( 'template_include', $video, 'template_include', 999 );		
		$this->loader->add_action( 'wp_ajax_svp_update_views_count', $video, 'ajax_callback_update_views_count' );
		$this->loader->add_action( 'wp_ajax_nopriv_svp_update_views_count', $video, 'ajax_callback_update_views_count' );	
		
		$this->loader->add_filter( 'the_content', $video, 'the_content', 20 );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

}
