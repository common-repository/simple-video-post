<?php

/**
 * Video
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
 * SVP_Public_Video class.
 *
 * @since 1.0.0
 */
class SVP_Public_Video {
	
	/**
	 * Get things started.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Register shortcode(s)
		add_shortcode( "svp_video", array( $this, "run_shortcode_video" ) );
	}
	
	/**
	 * Always keep using our custom template for AIOVG player page.
	 *
	 * @since  1.0.0
	 * @param  string $template The path of the template to include.
	 * @return string $template Filtered template path.
	 */
	public function template_include( $template ) {	
		$page_settings = get_option( 'svp_page_settings' );

		/*
		if ( is_page( (int) $page_settings['player'] ) ) {
			$template = apply_filters( 'svp_load_template', SVP_PLUGIN_DIR . 'public/templates/player.php' );
		}*/
		
		return $template;		
	}	
	
	/**
	 * Run the shortcode [svp_video].
	 *
	 * @since 1.0.0
	 * @param array $atts An associative array of attributes.
	 */
	public function run_shortcode_video( $atts ) {		
		// Vars
		if ( ! $atts ) {
			$atts = array();
		}
		
		$post_id = 0;
		
		if ( ! empty( $atts['id'] ) ) {
			$post_id = (int) $atts['id'];
		} else {			
			$supported_formats = array( 'mp4', 'webm', 'ogv', 'youtube', 'vimeo', 'dailymotion', 'facebook', 'dash', 'hls' );
			$is_video_available = 0;
			
			foreach ( $supported_formats as $format ) {			
				if ( array_key_exists( $format, $atts ) ) {
					$is_video_available = 1;
				}				
			}
			
			if ( 0 == $is_video_available ) {			
				$args = array(				
					'post_status' => 'publish',
					'posts_per_page' => 1,
					'fields' => 'ids',
					'no_found_rows' => true,
					'update_post_term_cache' => false,
					'update_post_meta_cache' => false
				);
		
				$svp_query = new WP_Query( $args );
				
				if ( $svp_query->have_posts() ) {
					$posts = $svp_query->posts;
					$post_id = (int) $posts[0];
				}			
			}			
		}
		
		// Enqueue dependencies
		wp_enqueue_style( SVP_PLUGIN_SLUG . '-public' );		
			
		// Return
		return svp_get_player_html( $post_id, $atts );		
	}
	
	/**
	 * Filter the post content.
	 *
	 * @since  1.0.0
	 * @param  string $content Content of the current post.
	 * @return string $content Modified Content.
	 */
	public function the_content( $content ) {	
		if ( is_singular() && in_the_loop() && is_main_query() ) {		
			global $post, $wp_query;
			
			if ( $post->ID != $wp_query->get_queried_object_id() ) {
				return $content;
			}
			
			if ( post_password_required( $post->ID ) ) {
				return $content;
			}
			
			$is_video = get_post_meta( $post->ID, 'is_video_post' );
			$attributes = array(
				'id'  => $post->ID,				
				'show_views'  => true,
				'show_video' => $is_video[0],
			);
			
			// Enqueue dependencies
			wp_enqueue_style( SVP_PLUGIN_SLUG . '-public' );
			
			// Process output
			ob_start();
			include apply_filters( 'svp_load_template', SVP_PLUGIN_DIR . 'public/templates/single-video.php' );
			$content = ob_get_clean();			
		}
		
		return $content;	
	}

	/**
	 * Update video views count.
	 *
	 * @since 1.0.0
	 */
	public function ajax_callback_update_views_count() {
		if ( isset( $_REQUEST['post_id'] ) ) {		
			$post_id = (int) $_REQUEST['post_id'];
						
			if ( $post_id > 0 ) {
				check_ajax_referer( "svp_video_{$post_id}_views_nonce", 'security' );
				svp_update_views_count( $post_id );
			}		
		}
		
		wp_send_json_success();	
	}
	
}
