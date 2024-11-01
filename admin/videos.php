<?php

/**
 * Videos
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
 * SVP_Admin_Videos class.
 *
 * @since 1.0.0
 */
class SVP_Admin_Videos {
	
	/**
	 * Register meta boxes.
	 *
	 * @since 1.0.0
	 */
	public function add_meta_boxes() {
		add_meta_box( 
			'svp-video-sources', 
			__( 'Video Sources', 'simple-video-post' ), 
			array( $this, 'display_meta_box_video_sources' ), 
			'post', 
			'normal', 
			'high' 
		);
		
		add_meta_box( 
			'svp-video-tracks', 
			__( 'Subtitles', 'simple-video-post' ), 
			array( $this, 'display_meta_box_video_tracks' ), 
			'post', 
			'normal', 
			'high' 
		);		
	}

	/**
	 * Display "Video Sources" meta box.
	 *
	 * @since 1.0.0
	 * @param WP_Post $post WordPress Post object.
	 */
	public function display_meta_box_video_sources( $post ) {		
		$post_meta = get_post_meta( $post->ID );
		$player_settings = get_option( 'svp_player_settings' );

		$quality_levels = explode( "\n", $player_settings['quality_levels'] );
		$quality_levels = array_filter( $quality_levels );
		$quality_levels = array_map( 'sanitize_text_field', $quality_levels );
		
		$is_video_post = isset( $post_meta['is_video_post'] ) ? $post_meta['is_video_post'][0] : 'no';
		$type          = isset( $post_meta['type'] ) ? $post_meta['type'][0] : 'default';
		$mp4           = isset( $post_meta['mp4'] ) ? $post_meta['mp4'][0] : '';
		$has_webm      = isset( $post_meta['has_webm'] ) ? $post_meta['has_webm'][0] : 0;
		$webm          = isset( $post_meta['webm'] ) ? $post_meta['webm'][0] : '';
		$has_ogv       = isset( $post_meta['has_ogv'] ) ? $post_meta['has_ogv'][0] : 0;
		$ogv           = isset( $post_meta['ogv'] ) ? $post_meta['ogv'][0] : '';
		$quality_level = isset( $post_meta['quality_level'] ) ? $post_meta['quality_level'][0] : '';
		$sources       = isset( $post_meta['sources'] ) ? unserialize( $post_meta['sources'][0] ) : array();
		$youtube       = isset( $post_meta['youtube'] ) ? $post_meta['youtube'][0] : '';
		$vimeo         = isset( $post_meta['vimeo'] ) ? $post_meta['vimeo'][0] : '';
		$dailymotion   = isset( $post_meta['dailymotion'] ) ? $post_meta['dailymotion'][0] : '';
		$facebook      = isset( $post_meta['facebook'] ) ? $post_meta['facebook'][0] : '';
		$embedcode     = isset( $post_meta['embedcode'] ) ? $post_meta['embedcode'][0] : '';
		$image         = isset( $post_meta['image'] ) ? $post_meta['image'][0] : '';
		$duration      = isset( $post_meta['duration'] ) ? $post_meta['duration'][0] : '';
		$views         = isset( $post_meta['views'] ) ? $post_meta['views'][0] : '';

		require_once SVP_PLUGIN_DIR . 'admin/partials/video-sources.php';
	}
	
	/**
	 * Display "Subtitles" meta box.
	 *
	 * @since 1.0.0
	 * @param WP_Post $post WordPress Post object.
	 */
	public function display_meta_box_video_tracks( $post ) {		
		$tracks = get_post_meta( $post->ID, 'track' );
		require_once SVP_PLUGIN_DIR . 'admin/partials/video-tracks.php';
	}
	
	/**
	 * Save meta data.
	 *
	 * @since  1.0.0
	 * @param  int     $post_id Post ID.
	 * @param  WP_Post $post    The post object.
	 * @return int     $post_id If the save was successful or not.
	 */
	public function save_meta_data( $post_id, $post ) {	
		if ( ! isset( $_POST['post_type'] ) ) {
        	return $post_id;
    	}
	
		// Check this post type
    	if ( 'post' != $post->post_type ) {
        	return $post_id;
    	}
		
		// If this is an autosave, our form has not been submitted, so we don't want to do anything
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        	return $post_id;
		}
		
		// Check the logged in user has permission to edit this post
    	if ( !current_user_can( 'edit_post', $post_id ) ) {
        	return $post_id;
    	}
		
		// Check if "svp_video_sources_nonce" nonce is set
    	if ( isset( $_POST['svp_video_sources_nonce'] ) ) {		
			// Verify that the nonce is valid
    		if ( wp_verify_nonce( $_POST['svp_video_sources_nonce'], 'svp_save_video_sources' ) ) {			
				// OK to save meta data		
				$is_video_post = isset( $_POST['is_video_post'] ) ? sanitize_text_field( $_POST['is_video_post'] ) : 'no';
				update_post_meta( $post_id, 'is_video_post', $is_video_post );
				
				$type = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : 'default';
				update_post_meta( $post_id, 'type', $type );
				
				$mp4 = isset( $_POST['mp4'] ) ? svp_sanitize_url( $_POST['mp4'] ) : '';
				update_post_meta( $post_id, 'mp4', $mp4 );
				update_post_meta( $post_id, 'mp4_id', svp_get_attachment_id( $mp4, 'video' ) );
				
				$has_webm = isset( $_POST['has_webm'] ) ? 1 : 0;
				update_post_meta( $post_id, 'has_webm', $has_webm );
				
				$webm = isset( $_POST['webm'] ) ? svp_sanitize_url( $_POST['webm'] ) : '';
				update_post_meta( $post_id, 'webm', $webm );
				update_post_meta( $post_id, 'webm_id', svp_get_attachment_id( $webm, 'video' ) );
				
				$has_ogv = isset( $_POST['has_ogv'] ) ? 1 : 0;
				update_post_meta( $post_id, 'has_ogv', $has_ogv );
				
				$ogv = isset( $_POST['ogv'] ) ? svp_sanitize_url( $_POST['ogv'] ) : '';
				update_post_meta( $post_id, 'ogv', $ogv );
				update_post_meta( $post_id, 'ogv_id', svp_get_attachment_id( $ogv, 'video' ) );

				$quality_level = isset( $_POST['quality_level'] ) ? sanitize_text_field( $_POST['quality_level'] ) : '';
				update_post_meta( $post_id, 'quality_level', $quality_level );

				if ( ! empty( $_POST['sources'] ) ) {					
					$values = array();

					$quality_levels = array_map( 'sanitize_text_field', $_POST['quality_levels'] );
					$sources = array_map( 'svp_sanitize_url', $_POST['sources'] );

					foreach ( $sources as $index => $source ) {
						if ( ! empty( $source ) ) {
							$values[] = array(
								'quality' => $quality_levels[ $index ],
								'src'     => $source
							);
						}
					}

					update_post_meta( $post_id, 'sources', $values );
				}
				
				$youtube = isset( $_POST['youtube'] ) ? esc_url_raw( $_POST['youtube'] ) : '';
				update_post_meta( $post_id, 'youtube', $youtube );
				
				$vimeo = isset( $_POST['vimeo'] ) ? esc_url_raw( $_POST['vimeo'] ) : '';
				update_post_meta( $post_id, 'vimeo', $vimeo );
				
				$dailymotion = isset( $_POST['dailymotion'] ) ? esc_url_raw( $_POST['dailymotion'] ) : '';
				update_post_meta( $post_id, 'dailymotion', $dailymotion );
				
				$facebook = isset( $_POST['facebook'] ) ? esc_url_raw( $_POST['facebook'] ) : '';
				update_post_meta( $post_id, 'facebook', $facebook );
				
				add_filter( 'wp_kses_allowed_html', 'svp_allow_iframe_script_tags' );
				$embedcode = isset( $_POST['embedcode'] ) ? wp_kses_post( str_replace( "'", '"', $_POST['embedcode'] ) ) : '';
				update_post_meta( $post_id, 'embedcode', $embedcode );
				remove_filter( 'wp_kses_allowed_html', 'svp_allow_iframe_script_tags' );
				
				$image    = '';
				$image_id = 0;
				if ( ! empty( $_POST['image'] ) ) {
					$image    = svp_sanitize_url( $_POST['image'] );
					$image_id = svp_get_attachment_id( $image, 'image' );
				} else {
					if ( 'youtube' == $type && ! empty( $youtube ) ) {
						$image = svp_get_youtube_image_url( $youtube );
					} elseif ( 'vimeo' == $type && ! empty( $vimeo ) ) {
						$oembed = svp_get_vimeo_oembed_data( $vimeo );
						$image = $oembed['thumbnail_url'];
					} elseif ( 'dailymotion' == $type && ! empty( $dailymotion ) ) {
						$image = svp_get_dailymotion_image_url( $dailymotion );
					} elseif ( 'embedcode' == $type && ! empty( $embedcode ) ) {
						$image = svp_get_embedcode_image_url( $embedcode );
					}
				}
				update_post_meta( $post_id, 'image', $image );
				update_post_meta( $post_id, 'image_id', $image_id );
				
				$duration = isset( $_POST['duration'] ) ? sanitize_text_field( $_POST['duration'] ) : '';
				update_post_meta( $post_id, 'duration', $duration );
				
				$views = isset( $_POST['views'] ) ? (int) $_POST['views'] : 0;
				update_post_meta( $post_id, 'views', $views );				
			}			
		}
		
		// Check if "svp_video_tracks_nonce" nonce is set
    	if ( isset( $_POST['svp_video_tracks_nonce'] ) ) {		
			// Verify that the nonce is valid
    		if ( wp_verify_nonce( $_POST['svp_video_tracks_nonce'], 'svp_save_video_tracks' ) ) {			
				// OK to save meta data
				delete_post_meta( $post_id, 'track' );
				
				if ( ! empty( $_POST['track_src'] ) ) {				
					$sources = $_POST['track_src'];
					$sources = array_map( 'esc_url_raw', $sources );
					$sources = array_filter( $sources, 'strlen' );
					
					foreach ( $sources as $key => $source ) {
						$track = array(
							'src'     => svp_sanitize_url( $source ),
							'src_id'  => svp_get_attachment_id( $source, 'track' ),  
							'label'   => sanitize_text_field( $_POST['track_label'][ $key ] ),
							'srclang' => sanitize_text_field( $_POST['track_srclang'][ $key ] )
						);
						
						add_post_meta( $post_id, 'track', $track );
					}					
				}
			}			
		}
		
		return $post_id;	
	}
	
	/**
	 * Delete video attachments.
	 *
	 * @since 1.0.0
	 * @param int   $post_id Post ID.
	 */
	public function before_delete_post( $post_id ) {		
		if ( 'post' != get_post_type( $post_id ) ) {
			return;
		}
		  
		svp_delete_video_attachments( $post_id );	
	}

}
