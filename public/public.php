<?php

/**
 * The public-facing functionality of the plugin.
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
 * SVP_Public class.
 *
 * @since 1.0.0
 */
class SVP_Public {
	
	/**
	 * Remove 'redirect_canonical' hook to fix secondary loop pagination issue on single video 
	 * pages.
	 *
	 * @since 1.5.5
	 */
	public function template_redirect() {	
		if ( is_singular() ) {		
			global $wp_query;
			
			$page = (int) $wp_query->get( 'page' );
			if ( $page > 1 ) {
		  		// Convert 'page' to 'paged'
		 	 	$wp_query->set( 'page', 1 );
		 	 	$wp_query->set( 'paged', $page );
			}
			
			// Prevent redirect
			remove_action( 'template_redirect', 'redirect_canonical' );		
	  	}	
	}
	
	/**
	 * Add rewrite rules, set necessary plugin cookies.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		global $svp;

		$url = home_url();
		
		// Set MySQL's RAND function seed value in a cookie
		if ( isset( $_COOKIE['svp_rand_seed'] ) ) {
			$svp['rand_seed'] = sanitize_text_field( $_COOKIE['svp_rand_seed'] );
			$transient_seed = get_transient( 'svp_rand_seed_' . $svp['rand_seed'] );

			if ( ! empty( $transient_seed ) ) {
				delete_transient( 'svp_rand_seed_' . $svp['rand_seed'] );

				$svp['rand_seed'] = sanitize_text_field( $transient_seed );
				setcookie( 'svp_rand_seed', $svp['rand_seed'], time() + ( 24 * 60 * 60 ), COOKIEPATH, COOKIE_DOMAIN );				
			}
		} else {
			$svp['rand_seed'] = wp_rand();
			setcookie( 'svp_rand_seed', $svp['rand_seed'], time() + ( 24 * 60 * 60 ), COOKIEPATH, COOKIE_DOMAIN );
		}
	}
	
	/**
	 * Flush rewrite rules when it's necessary.
	 *
	 * @since 1.0.0
	 */
	 public function maybe_flush_rules() {
		$rewrite_rules = get_option( 'rewrite_rules' );
				
		if ( $rewrite_rules ) {		
			global $wp_rewrite;
			
			foreach ( $rewrite_rules as $rule => $rewrite ) {
				$rewrite_rules_array[ $rule ]['rewrite'] = $rewrite;
			}
			$rewrite_rules_array = array_reverse( $rewrite_rules_array, true );
		
			$maybe_missing = $wp_rewrite->rewrite_rules();
			$missing_rules = false;		
		
			foreach ( $maybe_missing as $rule => $rewrite ) {
				if ( ! array_key_exists( $rule, $rewrite_rules_array ) ) {
					$missing_rules = true;
					break;
				}
			}
		
			if ( true === $missing_rules ) {
				flush_rewrite_rules();
			}		
		}	
	}
	
	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {
		$general_settings = get_option( 'svp_general_settings' );
		
		wp_register_style( 
			SVP_PLUGIN_SLUG . '-select2', 
			SVP_PLUGIN_URL . 'public/assets/css/select2.min.css', 
			array(), 
			'4.1.0', 
			'all' 
		);

		wp_register_style( 
			SVP_PLUGIN_SLUG . '-magnific-popup', 
			SVP_PLUGIN_URL . 'public/assets/css/magnific-popup.css', 
			array(), 
			'1.1.0', 
			'all' 
		);

		wp_register_style( 
			SVP_PLUGIN_SLUG . '-backward-compatibility', 
			SVP_PLUGIN_URL . 'public/assets/css/backward-compatibility.css', 
			array(), 
			SVP_PLUGIN_VERSION, 
			'all' 
		);

		wp_register_style( 
			SVP_PLUGIN_SLUG . '-public', 
			SVP_PLUGIN_URL . 'public/assets/css/public.css', 
			array( SVP_PLUGIN_SLUG . '-backward-compatibility' ), 
			SVP_PLUGIN_VERSION, 
			'all' 
		);
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		
		wp_register_script( 
			SVP_PLUGIN_SLUG . '-player', 
			SVP_PLUGIN_URL . 'public/assets/js/player.js', 
			array( 'jquery' ), 
			SVP_PLUGIN_VERSION, 
			false 
		);
		
		wp_localize_script( 
			SVP_PLUGIN_SLUG . '-player', 
			'svp_player', 
			array(
				'ajax_url'   => admin_url( 'admin-ajax.php' ),
				'ajax_nonce' =>	wp_create_nonce( 'svp_ajax_nonce' )							
			)
		);
		
		wp_register_script( 
			SVP_PLUGIN_SLUG . '-public', 
			SVP_PLUGIN_URL . 'public/assets/js/public.js', 
			array( 'jquery' ), 
			SVP_PLUGIN_VERSION, 
			false 
		);
		
		wp_localize_script( 
			SVP_PLUGIN_SLUG . '-public', 
			'svp_public', 
			array(
				'i18n' => array(
					'no_tags_found' => __( 'No tags found', 'simple-video-post' )
				)						
			)
		);
	}

	/**
	 * Filters whether a video post has a thumbnail.
	 *
	 * @since  2.4.0
	 * @param bool             $has_thumbnail true if the post has a post thumbnail, otherwise false.
	 * @param int|WP_Post|null $post          Post ID or WP_Post object. Default is global `$post`.
	 * @param int|string       $thumbnail_id  Post thumbnail ID or empty string.
	 * @return bool            $has_thumbnail true if the video post has an image attached.
	 */
	public function has_post_thumbnail( $has_thumbnail, $post, $thumbnail_id ) {
		$post = get_post( $post );
		if ( ! $post ) {
			return $has_thumbnail;
		}

		if ( empty( $thumbnail_id ) ) {		
			$image_url = get_post_meta( $post->ID, 'image', true );
			$image_id  = get_post_meta( $post->ID, 'image_id', true );

			$image = svp_get_image_url( $image_id, 'large', $image_url, 'core' );
			if ( ! empty( $image ) ) {
				return true;
			}
		}

		return $has_thumbnail;		
	}

	/**
	 * Filters the video post thumbnail HTML.
	 *
	 * @since  2.4.0
	 * @param string       $html              The post thumbnail HTML.
	 * @param int          $post_id           The post ID.
	 * @param string       $post_thumbnail_id The post thumbnail ID.
	 * @param string|array $size              The post thumbnail size. Image size or array of width and height
	 *                                        values (in that order). Default 'post-thumbnail'.
	 * @param string       $attr              Query string of attributes.
	 * @return bool        $html              Filtered video post thumbnail HTML.
	 */
	public function post_thumbnail_html( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
		if ( is_singular() ) {
			if ( $post_id == get_the_ID() ) {
				return '';
			}
		}

		if ( empty( $post_thumbnail_id ) ) {
			$_html = '';

			$image_id = get_post_meta( $post_id, 'image_id', true );
			if ( ! empty( $image_id ) ) {
				$_html = wp_get_attachment_image( $image_id, $size, false, $attr );
			} 
			
			if ( empty( $_html ) ) {
				$image_url = get_post_meta( $post_id, 'image', true );
				if ( ! empty( $image_url ) ) {
					$alt  = get_post_field( 'post_title', $post_id );
					$attr = array( 'alt' => $alt );
					$attr = apply_filters( 'wp_get_attachment_image_attributes', $attr, NULL, $size );
					$attr = array_map( 'esc_attr', $attr );
					$_html = sprintf( '<img src="%s"', esc_url( $image_url ) );
					foreach ( $attr as $name => $value ) {
						$_html .= " $name=" . '"' . $value . '"';
					}
					$_html .= ' />';
				}
			}

			if ( ! empty( $_html ) ) {
				$html = $_html;
			}
		}

		return $html;		
	}
	
	/**
	 * Set cookie for accepting the privacy consent.
	 *
	 * @since 1.0.0
	 */
	public function set_gdpr_cookie() {	
		check_ajax_referer( 'svp_ajax_nonce', 'security' );	
		setcookie( 'svp_gdpr_consent', 1, time() + ( 30 * 24 * 60 * 60 ), COOKIEPATH, COOKIE_DOMAIN );		
		wp_send_json_success();			
	}

}
