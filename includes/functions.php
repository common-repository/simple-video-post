<?php

/**
 * Helper Functions.
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
 * Allow iframe & script tags in the "Iframe Embed Code" field.
 * 
 * @since  1.0.0
 * @param  array $allowed_tags Allowed HTML Tags.
 * @return array               Iframe & script tags included.
 */
function svp_allow_iframe_script_tags( $allowed_tags ) {
	// Only change for users who has "unfiltered_html" capability
	if ( ! current_user_can( 'unfiltered_html' ) ) return $allowed_tags;
	
	// Allow script and the following attributes
	$allowed_tags['script'] = array(
		'type'   => true,
		'src'    => true,
		'width'  => true,
		'height' => true
	);

	// Allow iframes and the following attributes
	$allowed_tags['iframe'] = array(
		'align'        => true,
		'width'        => true,
		'height'       => true,
		'frameborder'  => true,
		'name'         => true,
		'src'          => true,
		'id'           => true,
		'class'        => true,
		'style'        => true,
		'scrolling'    => true,
		'marginwidth'  => true,
		'marginheight' => true,
	);
	
	return $allowed_tags;	
}

/**
 * Combine video attributes as a string.
 * 
 * @since 2.0.0
 * @param array  $atts Array of video attributes.
 * @param string       Combined attributes string.
 */
function svp_combine_video_attributes( $atts ) {
	$attributes = array();
	
	foreach ( $atts as $key => $value ) {
		if ( '' === $value ) {
			$attributes[] = $key;
		} else {
			$attributes[] = sprintf( '%s="%s"', $key, $value );
		}
	}
	
	return implode( ' ', $attributes );
}

/**
 * Delete video attachments.
 *
 * @since 1.0.0
 * @param int   $post_id Post ID.
 */
function svp_delete_video_attachments( $post_id ) {	
	$general_settings = get_option( 'svp_general_settings' );
	
	if ( ! empty( $general_settings['delete_media_files'] ) ) {
		$mp4_id = get_post_meta( $post_id, 'mp4_id', true );
		if ( ! empty( $mp4_id ) ) wp_delete_attachment( $mp4_id, true );
		
		$webm_id = get_post_meta( $post_id, 'webm_id', true );
		if ( ! empty( $webm_id ) ) wp_delete_attachment( $webm_id, true );
		
		$ogv_id = get_post_meta( $post_id, 'ogv_id', true );
		if ( ! empty( $ogv_id ) ) wp_delete_attachment( $ogv_id, true );
		
		$image_id = get_post_meta( $post_id, 'image_id', true );
		if ( ! empty( $image_id ) ) wp_delete_attachment( $image_id, true );
		
		$tracks = get_post_meta( $post_id, 'track' );	
		if ( count( $tracks ) ) {
			foreach ( $tracks as $key => $track ) {
				if ( 'src_id' == $key ) wp_delete_attachment( (int) $track['src_id'], true );
			}
		}
	}
}

/**
 * Get attachment ID of the given URL.
 * 
 * @since  1.0.0
 * @param  string $url   Media file URL.
 * @param  string $media "image" or "video". Type of the media. 
 * @return int           Attachment ID on success, 0 on failure.
 */
function svp_get_attachment_id( $url, $media = 'image' ) {
	$attachment_id = 0;
	
	if ( empty( $url ) ) {
		return $attachment_id;
	}	
	
	if ( 'image' == $media ) {
		$dir = wp_upload_dir();
	
		if ( false !== strpos( $url, $dir['baseurl'] . '/' ) ) { // Is URL in uploads directory?	
			$file = basename( $url );
	
			$query_args = array(
				'post_type' => 'attachment',
				'post_status' => 'inherit',
				'fields' => 'ids',
				'no_found_rows' => true,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
				'meta_query' => array(
					array(
						'key' => '_wp_attachment_metadata',
						'value' => $file,
						'compare' => 'LIKE'						
					),
				)
			);
	
			$query = new WP_Query( $query_args );
	
			if ( $query->have_posts() ) {	
				foreach ( $query->posts as $post_id ) {	
					$meta = wp_get_attachment_metadata( $post_id );
	
					$original_file = basename( $meta['file'] );
					$cropped_image_files = wp_list_pluck( $meta['sizes'], 'file' );
	
					if ( $original_file === $file || in_array( $file, $cropped_image_files ) ) {
						$attachment_id = $post_id;
						break;
					}	
				}	
			}	
		}	
	} else {
		$url = wp_make_link_relative( $url );
		
		if ( ! empty( $url ) ) {
			global $wpdb;
			
			$query = $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE guid RLIKE %s", $url );
			$attachment_id = $wpdb->get_var( $query );
		}		
	}

	return $attachment_id;	
}

/**
 * Get Dailymotion ID from URL.
 *
 * @since  1.5.0
 * @param  string $url Dailymotion video URL.
 * @return string $id  Dailymotion video ID.
 */
function svp_get_dailymotion_id_from_url( $url ) {	
	$id = '';
	
	if ( preg_match( '!^.+dailymotion\.com/(video|hub)/([^_]+)[^#]*(#video=([^_&]+))?|(dai\.ly/([^_]+))!', $url, $m ) ) {
        if ( isset( $m[6] ) ) {
            $id = $m[6];
        }
		
        if ( isset( $m[4] ) ) {
            $id = $m[4];
        }
		
        $id = $m[2];
    }

	return $id;	
}

/**
 * Get Dailymotion image from URL.
 *
 * @since  1.5.0
 * @param  string $url Dailymotion video URL.
 * @return string $url Dailymotion image URL.
 */
function svp_get_dailymotion_image_url( $url ) {	
	$id  = svp_get_dailymotion_id_from_url( $url );		
	$url = '';
	
	if ( ! empty( $id ) ) {
		$dailymotion_response = wp_remote_get( 'https://api.dailymotion.com/video/' . $id . '?fields=thumbnail_large_url,thumbnail_medium_url' );

		if ( ! is_wp_error( $dailymotion_response ) ) {
			$dailymotion_response = json_decode( $dailymotion_response['body'] );

			if ( isset( $dailymotion_response->thumbnail_large_url ) ) {
				$url = $dailymotion_response->thumbnail_large_url;
			} else {
				$url = $dailymotion_response->thumbnail_medium_url;
			}
		}
	}
    	
	return $url;	
}

/**
 * Get default plugin settings.
 *
 * @since  1.5.3
 * @return array $defaults Array of plugin settings.
 */
function svp_get_default_settings() {
	$defaults = array(		
		'svp_player_settings' => array(
			'player'   => 'iframe',
			'width'    => '',
			'ratio'    => 56.25,
			'autoplay' => 0,
			'loop'     => 0,
			'muted'    => 0,
			'preload'  => 'metadata',
			'controls' => array(
				'playpause'  => 'playpause',
				'current'    => 'current',
				'progress'   => 'progress', 
				'duration'   => 'duration',
				'tracks'     => 'tracks',
				'quality'    => 'quality',
				'speed'      => 'speed',
				'volume'     => 'volume', 
				'fullscreen' => 'fullscreen'					
			),
			'quality_levels' => implode( "\n", array( '360', '480', '720', '1080' ) ),
			'use_native_controls' => array(
				'facebook' => 'facebook'
			)
		),					
	);
		
	return $defaults;		
}

/**
 * Get image from the Iframe Embed Code.
 *
 * @since  1.0.0
 * @param  string $embedcode Iframe Embed Code.
 * @return string $url       Image URL.
 */
function svp_get_embedcode_image_url( $embedcode ) {
	$url = '';

	$document = new DOMDocument();
  	@$document->loadHTML( $embedcode );	

	$iframes = $document->getElementsByTagName( 'iframe' ); 
	if ( $iframes->length > 0 ) {
		if ( $iframes->item(0)->hasAttribute( 'src' ) ) {
			$src = $iframes->item(0)->getAttribute( 'src' );

			// YouTube
			if ( false !== strpos( $src, 'youtube.com' ) || false !== strpos( $src, 'youtu.be' ) ) {
				$url = svp_get_youtube_image_url( $src );
			}
			
			// Vimeo
			elseif ( false !== strpos( $src, 'vimeo.com' ) ) {
				$oembed = svp_get_vimeo_oembed_data( $src );
				$url = $oembed['thumbnail_url'];
			}
			
			// Dailymotion
			elseif ( false !== strpos( $src, 'dailymotion.com' ) ) {
				$url = svp_get_dailymotion_image_url( $src );
			}
		}
	}
    	
	// Return image url
	return $url;	
}

/**
 * Get the video excerpt.
 *
 * @since  1.0.0
 * @param  int    $post_id     Post ID.
 * @param  int    $char_length Excerpt length.
 * @param  string $append      String to append to the end of the excerpt.
 * @return string $content     Excerpt content.
 */
function svp_get_excerpt( $post_id = 0 , $char_length = 55, $append = '[...]' ) {
	$content = '';

	if ( $post_id > 0 ) {
		$post = get_post( $post_id );
	} else {
		global $post;
	}	

	if ( ! empty( $post->post_excerpt ) ) {
		$content = $post->post_excerpt;
	} elseif ( ! empty( $post->post_content ) ) {
		$excerpt = wp_strip_all_tags( $post->post_content, true );
		$char_length++;		

		if ( mb_strlen( $excerpt ) > $char_length ) {
			$subex = mb_substr( $excerpt, 0, $char_length - 5 );
			$exwords = explode( ' ', $subex );
			$excut = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) );
			if ( $excut < 0 ) {
				$content = mb_substr( $subex, 0, $excut );
			} else {
				$content = $subex;
			}
			$content .= $append;
		} else {
			$content = $excerpt;
		}
	}
	
	return trim( $content );	
}

/**
 * Get the file extension.
 *
 * @since  2.4.4
 * @param  string $url     File URL.
 * @param  string $default Default file extension.
 * @return string $ext     File extension.
 */
function svp_get_file_ext( $url, $default = 'mp4' ) {
	if ( $ext = pathinfo( $url, PATHINFO_EXTENSION ) ) {
		return $ext;
	}

	return $default;
}

/**
 * Get image URL using the attachment ID.
 *
 * @since  1.0.0
 * @param  int    $id      Attachment ID.
 * @param  string $size    Size of the image.
 * @param  string $default Default image URL.
 * @param  string $type    "gallery" or "player".
 * @return string $url     Image URL.
 */
function svp_get_image_url( $id, $size = "large", $default = '', $type = 'gallery' ) {
	$url = '';
	
	// Get image from attachment
	if ( $id ) {
		$attributes = wp_get_attachment_image_src( (int) $id, $size );
		if ( ! empty( $attributes ) ) {
			$url = $attributes[0];
		}
	}
	
	// Set default image
	if ( ! empty( $default ) ) {
		$default = svp_resolve_url( $default );
	} else {
		if ( 'gallery' == $type ) {
			$default = SVP_PLUGIN_URL . 'public/assets/images/placeholder-image.png';
		}
	}	
	
	if ( empty( $url ) ) {
		$url = $default;
	}
	
	// Return image url
	return $url;
}

/**
 * Get the client IP Address.
 *
 * @since  2.0.0
 * @return string $ip_address The client IP Address.
 */
function svp_get_ip_address() {
	// Whether ip is from share internet
	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		$ip_address = $_SERVER['HTTP_CLIENT_IP'];
	}
	
	// Whether ip is from proxy
	elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	
	// Whether ip is from remote address
	else {
		$ip_address = $_SERVER['REMOTE_ADDR'];
	}
	
	return $ip_address;		
}

/**
 * Get message to display based on the $type input.
 *
 * @since  1.0.0
 * @param  string $msg_id  Message Identifier.
 * @return string $message Message to display.
 */
function svp_get_message( $msg_id ) {
	$message = '';
	
	switch ( $msg_id ) {
		case 'videos_empty':
			$message = __( 'No Videos found.', 'simple-video-post' );
			break;
		case 'categories_empty':
			$message = __( 'No Categories found.', 'simple-video-post' );
			break;
	}
	
	return $message;	
}

/**
 * Get player HTML.
 * 
 * @since  1.0.0
 * @param  int    $post_id Post ID.
 * @param  array  $atts    Player configuration data.
 * @return string $html    Player HTML.
 */
function svp_get_player_html( $post_id = 0, $atts = array() ) {
	$player = SVP_Player::get_instance();
	return $player->create( $post_id, $atts );	
}

/**
 * Get shortcode builder form fields.
 *
 * @since 1.0.0
 */
function svp_get_shortcode_fields() {
	$defaults            = svp_get_default_settings();
	$player_settings     = array_merge( $defaults['svp_player_settings'], get_option( 'svp_player_settings', array() ) );
	$video_templates     = svp_get_video_templates();
	
	// Fields	
	$fields = array(
		'video' => array(
			'title'    => __( 'Single Video', 'simple-video-post' ),
			'sections' => array(
				'general' => array(
					'title'  => __( 'General', 'simple-video-post' ),
					'fields' => array(
						array(
							'name'        => 'id',
							'label'       => __( 'Select Video', 'simple-video-post' ),
							'description' => '',
							'type'        => 'video',
							'value'       => 0
						),
						array(
							'name'        => 'is_video_post',
							'label'       => __( 'Is Video Post?', 'simple-video-post' ),
							'description' => '',
							'type'        => 'select',
							'options'     => ['no','yes'],
							'value'       => 'no'
						),
						array(
							'name'        => 'type',
							'label'       => __( 'Source Type', 'simple-video-post' ),
							'description' => '',
							'type'        => 'select',
							'options'     => svp_get_video_source_types(),
							'value'       => 'default'
						),
						array(
							'name'        => 'mp4',
							'label'       => __( 'Video', 'simple-video-post' ),
							'description' => __( 'Enter your direct file URL in the textbox above (OR) upload your file using the "Upload File" link.', 'simple-video-post' ),
							'type'        => 'media',
							'value'       => ''
						),
						array(
							'name'        => 'youtube',
							'label'       => __( 'YouTube', 'simple-video-post' ),
							'description' => sprintf( '%s: https://www.youtube.com/watch?v=twYp6W6vt2U', __( 'Example', 'simple-video-post' ) ),
							'type'        => 'text',
							'value'       => ''
						),
						array(
							'name'        => 'vimeo',
							'label'       => __( 'Vimeo', 'simple-video-post' ),
							'description' => sprintf( '%s: https://vimeo.com/108018156', __( 'Example', 'simple-video-post' ) ),
							'type'        => 'text',
							'value'       => ''
						),
						array(
							'name'        => 'dailymotion',
							'label'       => __( 'Dailymotion', 'simple-video-post' ),
							'description' => sprintf( '%s: https://www.dailymotion.com/video/x11prnt', __( 'Example', 'simple-video-post' ) ),
							'type'        => 'text',
							'value'       => ''
						),
						array(
							'name'        => 'facebook',
							'label'       => __( 'Facebook', 'simple-video-post' ),
							'description' => sprintf( '%s: https://www.facebook.com/facebook/videos/10155278547321729', __( 'Example', 'simple-video-post' ) ),
							'type'        => 'text',
							'value'       => ''
						),
						array(
							'name'        => 'poster',
							'label'       => __( 'Image', 'simple-video-post' ),
							'description' => __( 'Enter your direct file URL in the textbox above (OR) upload your file using the "Upload File" link.', 'simple-video-post' ),
							'type'        => 'media',
							'value'       => ''
						),
						array(
							'name'        => 'width',
							'label'       => __( 'Width', 'simple-video-post' ),
							'description' => __( 'In pixels. Maximum width of the player. Leave this field empty to scale 100% of its enclosing container/html element.', 'simple-video-post' ),
							'type'        => 'text',
							'value'       => ''
						),
						array(
							'name'        => 'ratio',
							'label'       => __( 'Ratio', 'simple-video-post' ),
							'description' => __( "In percentage. 1 to 100. Calculate player's height using the ratio value entered.", 'simple-video-post' ),
							'type'        => 'text',
							'value'       => $player_settings['ratio']
						),
						array(
							'name'        => 'autoplay',
							'label'       => __( 'Autoplay', 'simple-video-post' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => $player_settings['autoplay']
						),
						array(
							'name'        => 'loop',
							'label'       => __( 'Loop', 'simple-video-post' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => $player_settings['loop']
						),
						array(
							'name'        => 'muted',
							'label'       => __( 'Muted', 'simple-video-post' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => $player_settings['muted']
						)					
					)
				),
				'controls' => array(
					'title'  => __( 'Player Controls', 'simple-video-post' ),
					'fields' => array(
						array(
							'name'        => 'playpause',
							'label'       => __( 'Play / Pause', 'simple-video-post' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => isset( $player_settings['controls']['playpause'] )
						),
						array(
							'name'        => 'current',
							'label'       => __( 'Current Time', 'simple-video-post' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => isset( $player_settings['controls']['current'] )
						),
						array(
							'name'        => 'progress',
							'label'       => __( 'Progressbar', 'simple-video-post' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => isset( $player_settings['controls']['progress'] )
						),
						array(
							'name'        => 'duration',
							'label'       => __( 'Duration', 'simple-video-post' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => isset( $player_settings['controls']['duration'] )
						),
						array(
							'name'        => 'tracks',
							'label'       => __( 'Subtitles', 'simple-video-post' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => isset( $player_settings['controls']['tracks'] )
						),
						array(
							'name'        => 'quality',
							'label'       => __( 'Quality Selector', 'simple-video-post' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => isset( $player_settings['controls']['quality'] )
						),
						array(
							'name'        => 'speed',
							'label'       => __( 'Speed Control', 'simple-video-post' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => isset( $player_settings['controls']['speed'] )
						),
						array(
							'name'        => 'volume',
							'label'       => __( 'Volume', 'simple-video-post' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => isset( $player_settings['controls']['volume'] )
						),
						array(
							'name'        => 'fullscreen',
							'label'       => __( 'Fullscreen', 'simple-video-post' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => isset( $player_settings['controls']['fullscreen'] )
						)
					)
				)
			)
		),		
		'videos' => array(
			'title'    => __( 'Video Gallery', 'simple-video-post' ),
			'sections' => array(
				'general' => array(
					'title'  => __( 'General', 'simple-video-post' ),
					'fields' => array(
						array(
							'name'        => 'title',
							'label'       => __( 'Title', 'simple-video-post' ),
							'description' => '',
							'type'        => 'text',
							'value'       => ''
						),
						array(
							'name'        => 'template',
							'label'       => __( 'Select Template', 'simple-video-post' ),
							'description' => '',
							'type'        => 'select',
							'options'     => $video_templates,
							'value'       => $videos_settings['template']
						),
						array(
							'name'        => 'category',
							'label'       => __( 'Select Categories', 'simple-video-post' ),
							'description' => '',
							'type'        => 'categories',
							'value'       => array()
						),
						array(
							'name'        => 'tag',
							'label'       => __( 'Select Tags', 'simple-video-post' ),
							'description' => '',
							'type'        => 'tags',
							'value'       => array()
						),
						array(
							'name'        => 'include',
							'label'       => __( 'Include Video ID(s)', 'simple-video-post' ),
							'description' => '',
							'type'        => 'text',
							'value'       => ''
						),	
						array(
							'name'        => 'exclude',
							'label'       => __( 'Exclude Video ID(s)', 'simple-video-post' ),
							'description' => '',
							'type'        => 'text',
							'value'       => ''
						),				
						array(
							'name'        => 'limit',
							'label'       => __( 'Limit (per page)', 'simple-video-post' ),
							'description' => '',
							'type'        => 'number',
							'min'         => 0,
							'max'         => 500,
							'step'        => 1,
							'value'       => $videos_settings['limit']
						),
						array(
							'name'        => 'orderby',
							'label'       => __( 'Order By', 'simple-video-post' ),
							'description' => '',
							'type'        => 'select',
							'options'     => array(
								'title' => __( 'Title', 'simple-video-post' ),
								'date'  => __( 'Date Posted', 'simple-video-post' ),
								'views' => __( 'Views Count', 'simple-video-post' ),
								'rand'  => __( 'Random', 'simple-video-post' )
							),
							'value'       => $videos_settings['orderby']
						),
						array(
							'name'        => 'order',
							'label'       => __( 'Order', 'simple-video-post' ),
							'description' => '',
							'type'        => 'select',
							'options'     => array(
								'asc'  => __( 'ASC', 'simple-video-post' ),
								'desc' => __( 'DESC', 'simple-video-post' )
							),
							'value'       => $videos_settings['order']
						),
						array(
							'name'        => 'featured',
							'label'       => __( 'Featured Only', 'simple-video-post' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => 0
						),
						array(
							'name'        => 'related',
							'label'       => __( 'Follow URL', 'simple-video-post' ) . ' (' . __( 'Related Videos', 'simple-video-post' ) . ')',
							'description' => '',
							'type'        => 'checkbox',
							'value'       => 0
						),					
					)
				),
				'gallery' => array(
					'title'  => __( 'Gallery', 'simple-video-post' ),
					'fields' => array(										
						array(
							'name'        => 'ratio',
							'label'       => __( 'Ratio', 'simple-video-post' ),
							'description' => '',
							'type'        => 'text',
							'value'       => $image_settings['ratio']
						),	
						array(
							'name'        => 'columns',
							'label'       => __( 'Columns', 'simple-video-post' ),
							'description' => '',
							'type'        => 'number',
							'min'         => 1,
							'max'         => 12,
							'step'        => 1,
							'value'       => $videos_settings['columns']
						),
						array(
							'name'        => 'thumbnail_style',
							'label'       => __( 'Image Position (Thumbnails)', 'simple-video-post' ),
							'description' => '',
							'type'        => 'select',
							'options'     => array(
								'standard'   => __( 'Top', 'simple-video-post' ),
								'image-left' => __( 'Left', 'simple-video-post' )
							),
							'value'       => $videos_settings['thumbnail_style']
						),
						array(
							'name'        => 'display',
							'label'       => __( 'Show / Hide (Thumbnails)', 'simple-video-post' ),
							'description' => '',
							'type'        => 'header',
							'value'       => 0
						),
						array(
							'name'        => 'show_count',
							'label'       => __( 'Videos Count', 'simple-video-post' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => 0
						),
						array(
							'name'        => 'show_category',
							'label'       => __( 'Category Name(s)', 'simple-video-post' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => isset( $videos_settings['display']['category'] )
						),
						array(
							'name'        => 'show_tag',
							'label'       => __( 'Tag Name(s)', 'simple-video-post' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => isset( $videos_settings['display']['tag'] )
						),				
						array(
							'name'        => 'show_date',
							'label'       => __( 'Date Added', 'simple-video-post' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => isset( $videos_settings['display']['date'] )
						),
						array(
							'name'        => 'show_user',
							'label'       => __( 'Author Name', 'simple-video-post' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => isset( $videos_settings['display']['user'] )
						),
						array(
							'name'        => 'show_views',
							'label'       => __( 'Views Count', 'simple-video-post' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => isset( $videos_settings['display']['views'] )
						),			
						array(
							'name'        => 'show_duration',
							'label'       => __( 'Video Duration', 'simple-video-post' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => isset( $videos_settings['display']['duration'] )
						),
						array(
							'name'        => 'show_excerpt',
							'label'       => __( 'Video Excerpt (Short Description)', 'simple-video-post' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => isset( $videos_settings['display']['excerpt'] )
						),
						array(
							'name'        => 'excerpt_length',
							'label'       => __( 'Excerpt Length', 'simple-video-post' ),
							'description' => '',
							'type'        => 'number',
							'value'       => $videos_settings['excerpt_length']
						),
						array(
							'name'        => 'show_pagination',
							'label'       => __( 'Pagination', 'simple-video-post' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => 1
						),
						array(
							'name'        => 'show_more',
							'label'       => __( 'More Button', 'simple-video-post' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => 0
						),
						array(
							'name'        => 'more_label',
							'label'       => __( 'More Button Label', 'simple-video-post' ),
							'description' => '',
							'type'        => 'text',
							'value'       => __( 'Show More', 'simple-video-post' )
						),
						array(
							'name'        => 'more_link',
							'label'       => __( 'More Button Link', 'simple-video-post' ),
							'description' => '',
							'type'        => 'url',
							'value'       => ''
						),
					)
				)
			)
		)
	);

	return apply_filters( 'svp_shortcode_fields', $fields );
}

/**
 * Get unique ID.
 *
 * @since  1.5.7
 * @return string Unique ID.
 */
function svp_get_uniqid() {
	global $svp;

	if ( ! isset( $svp['uniqid'] ) ) {
		$svp['uniqid'] = 0;
	}

	return uniqid() . ++$svp['uniqid'];
}

/**
 * Get video source types.
 * 
 * @since  1.0.0
 * @param  bool  $is_admin True if admin, false if not
 * @return array Array of source types.
 */
function svp_get_video_source_types( $is_admin = false ) {
	$types = array(
		'default'     => __( 'Self Hosted', 'simple-video-post' ) . ' / ' . __( 'External URL', 'simple-video-post' ),
		'youtube'     => __( 'YouTube', 'simple-video-post' ),
		'vimeo'       => __( 'Vimeo', 'simple-video-post' ),
		'dailymotion' => __( 'Dailymotion', 'simple-video-post' ),
		'facebook'    => __( 'Facebook', 'simple-video-post' )
	);

	if ( $is_admin ) {
		$types['embedcode'] = __( 'Iframe Embed Code', 'simple-video-post' );
	}
	
	return apply_filters( 'svp_video_source_types', $types );
}

/**
 * Get video templates.
 *
 * @since 1.5.7
 * @return array Array of video templates.
 */
function svp_get_video_templates() {
	$templates = array(
		'classic' => __( 'Classic', 'simple-video-post' )
	);
	
	return apply_filters( 'svp_video_templates', $templates );
}

/**
 * Get Vimeo data using oEmbed.
 *
 * @since  1.6.6
 * @param  string $url  Vimeo URL.
 * @return string $data Vimeo oEmbed response data.
 */
function svp_get_vimeo_oembed_data( $url ) {
	$data = array(
		'video_id'      => '',
		'thumbnail_url' => ''
	);

	if ( ! empty( $url ) ) {		
		$vimeo_response = wp_remote_get( 'https://vimeo.com/api/oembed.json?url=' . urlencode( $url ) );

		if ( is_array( $vimeo_response ) && ! is_wp_error( $vimeo_response ) ) {
			$vimeo_response = json_decode( $vimeo_response['body'] );

			if ( isset( $vimeo_response->video_id ) ) {
				$data = array(
					'video_id'      => $vimeo_response->video_id,
					'thumbnail_url' => isset( $vimeo_response->thumbnail_url ) ? $vimeo_response->thumbnail_url : ''
				);
			}			
		}

		// Fallback to our old method to get the Vimeo ID
		if ( empty( $data['video_id'] ) ) {			
			$is_vimeo = preg_match( '/vimeo\.com/i', $url );  

			if ( $is_vimeo ) {
				$data['video_id'] = preg_replace( '/[^\/]+[^0-9]|(\/)/', '', rtrim( $url, '/' ) );
			}
		}

		// Find large thumbnail using the Vimeo API v2
		if ( ! empty( $data['video_id'] ) ) {			
			$vimeo_response = wp_remote_get( 'https://vimeo.com/api/v2/video/' . $data['video_id'] . '.php' );
			
			if ( ! is_wp_error( $vimeo_response ) ) {
				$vimeo_response = maybe_unserialize( $vimeo_response['body'] );

				if ( is_array( $vimeo_response ) && isset( $vimeo_response[0]['thumbnail_large'] ) ) {
					$data['thumbnail_url'] = $vimeo_response[0]['thumbnail_large'];
				}
			}
		}
	}       
	
	return $data;
}

/**
 * Get YouTube ID from URL.
 *
 * @since  1.0.0
 * @param  string $url YouTube video URL.
 * @return string $id  YouTube video ID.
 */
function svp_get_youtube_id_from_url( $url ) {	
	$id  = '';
    $url = parse_url( $url );
		
    if ( 0 === strcasecmp( $url['host'], 'youtu.be' ) ) {
       	$id = substr( $url['path'], 1 );
    } elseif ( 0 === strcasecmp( $url['host'], 'www.youtube.com' ) ) {
       	if ( isset( $url['query'] ) ) {
       		parse_str( $url['query'], $url['query'] );
           	if ( isset( $url['query']['v'] ) ) {
           		$id = $url['query']['v'];
           	}
       	}
			
       	if ( empty( $id ) ) {
           	$url['path'] = explode( '/', substr( $url['path'], 1 ) );
           	if ( in_array( $url['path'][0], array( 'e', 'embed', 'v' ) ) ) {
               	$id = $url['path'][1];
           	}
       	}
    }
    	
	return $id;	
}

/**
 * Get YouTube image from URL.
 *
 * @since  1.0.0
 * @param  string $url YouTube video URL.
 * @return string $url YouTube image URL.
 */
function svp_get_youtube_image_url( $url ) {	
	$id  = svp_get_youtube_id_from_url( $url );
	$url = '';

	if ( ! empty( $id ) ) {
		$url = "https://img.youtube.com/vi/$id/mqdefault.jpg"; 
	}
	   	
	return $url;	
}

/**
 * Inserts a new key/value after the key in the array.
 *
 * @since  1.0.0
 * @param  string $key       The key to insert after.
 * @param  array  $array     An array to insert in to.
 * @param  array  $new_array An array to insert.
 * @return                   The new array if the key exists, FALSE otherwise.
 */
function svp_insert_array_after( $key, $array, $new_array ) {
	if ( array_key_exists( $key, $array ) ) {
    	$new = array();
    	foreach ( $array as $k => $value ) {
      		$new[ $k ] = $value;
      		if ( $k === $key ) {
				foreach ( $new_array as $new_key => $new_value ) {
        			$new[ $new_key ] = $new_value;
				}
      		}
    	}
    	return $new;
  	}
		
  	return $array;  
}

/**
 * Check whether the current post/page uses Gutenberg editor.
 *
 * @since  1.0.0
 * @return bool  True if the post/page uses Gutenberg, false if not.
 */
function svp_is_gutenberg_page() {
    if ( function_exists( 'is_gutenberg_page' ) && is_gutenberg_page() ) {
        // The Gutenberg plugin is on
        return true;
    }
	
    $current_screen = get_current_screen();
    if ( method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor() ) {
        // Gutenberg page on 5+
        return true;
    }
	
    return false;
}

/**
  * Removes an item or list from the query string.
  *
  * @since  1.0.0
  * @param  string|array $key   Query key or keys to remove.
  * @param  bool|string  $query When false uses the $_SERVER value. Default false.
  * @return string              New URL query string.
  */
function svp_remove_query_arg( $key, $query = false ) {
	if ( is_array( $key ) ) { // removing multiple keys
		foreach ( $key as $k ) {
			$query = str_replace( '#038;', '&', $query );
			$query = add_query_arg( $k, false, $query );
		}
		
		return $query;
	}
		
	return add_query_arg( $key, false, $query );	
}

/**
 * Resolve relative file paths as absolute URLs.
 * 
 * @since  2.4.0
 * @param  string $url Input file URL.
 * @return string $url Absolute file URL.
 */
function svp_resolve_url( $url ) {
	$host = parse_url( $url, PHP_URL_HOST );

	// Is relative path?
	if ( empty( $host ) ) {
		$url = get_site_url( null, $url );
	}

	return $url;
}

/**
 * Sanitize the array inputs.
 *
 * @since  1.0.0
 * @param  array $value Input array.
 * @return array        Sanitized array.
 */
function svp_sanitize_array( $value ) {
	return ! empty( $value ) ? array_map( 'sanitize_text_field', $value ) : array();
}

/**
 * Sanitize the integer inputs, accepts empty values.
 *
 * @since  1.0.0
 * @param  string|int $value Input value.
 * @return string|int        Sanitized value.
 */
function svp_sanitize_int( $value ) {
	$value = intval( $value );
	return ( 0 == $value ) ? '' : $value;	
}

/**
 * Sanitize the URLs. Accepts relative file paths, spaces.
 *
 * @since  2.4.0
 * @param  string $value Input value.
 * @return string        Sanitized value.
 */
function svp_sanitize_url( $value ) {
	$value = esc_url_raw( urldecode( $value ) );
	return $value;	
}

/**
 * Update video views count.
 *
 * @since 1.0.0
 * @param int   $post_id Post ID
 */
function svp_update_views_count( $post_id ) {				
	$visited = array();

	if ( isset( $_COOKIE['svp_videos_views'] ) ) {
		$visited = explode( '|', $_COOKIE['svp_videos_views'] );
		$visited = array_map( 'intval', $visited );
	}

	if ( ! in_array( $post_id, $visited ) ) {
		$count = (int) get_post_meta( $post_id, 'views', true );
		update_post_meta( $post_id, 'views', ++$count );

		// SetCookie
		$visited[] = $post_id;
		setcookie( 'svp_videos_views', implode( '|', $visited ), time() + ( 12 * 60 * 60 ), COOKIEPATH, COOKIE_DOMAIN );
	}
}

/**
 * Display the video excerpt.
 *
 * @since 1.0.0
 * @param int   $char_length Excerpt length.
 */
function the_svp_excerpt( $char_length ) {
	echo wp_kses_post( svp_get_excerpt( 0, $char_length ) );
}

/**
 * Display a video player.
 * 
 * @since 1.0.0
 * @param int   $post_id Post ID.
 * @param array $atts    Player configuration data.
 */
function the_svp_player( $post_id = 0, $atts = array() ) {
	echo svp_get_player_html( $post_id, $atts );	
}

/**
 * Build & display attributes using the $atts array.
 * 
 * @since 1.0.0
 * @param array $atts Array of attributes.
 */
function the_svp_video_attributes( $atts ) {
	echo svp_combine_video_attributes( $atts );
}