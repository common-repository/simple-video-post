<?php

/**
 * Videos: "Video Sources" meta box.
 *
 * @link    http://divyarthinfotech.com
 * @since   1.0.0
 *
 * @package Simple_Video_Post
 */
?>

<table class="svp-table widefat">
  	<tbody>
    	<tr>
      		<td class="label svp-hidden-xs">
        		<label><?php esc_html_e( 'Is Video Post?', 'simple-video-post' ); ?></label>
      		</td>
      		<td>        
        		<p class="svp-hidden-sm svp-hidden-md svp-hidden-lg">
					<strong><?php esc_html_e( 'Is Video Post?', 'simple-video-post' ); ?></strong>
				</p>
				<select name="is_video_post" id="svp-video-show" class="select">
                	<option value="no" <?php if(esc_attr( $is_video_post ) == 'no'): ?>selected<?php endif; ?>>No</option>
					<option value="yes" <?php if(esc_attr( $is_video_post ) == 'yes'): ?>selected<?php endif; ?>>Yes</option>
        		</select>
      		</td>
    	</tr>
    	<tr>
      		<td class="label svp-hidden-xs">
        		<label><?php esc_html_e( 'Source Type', 'simple-video-post' ); ?></label>
      		</td>
      		<td>        
        		<p class="svp-hidden-sm svp-hidden-md svp-hidden-lg">
					<strong><?php esc_html_e( 'Source Type', 'simple-video-post' ); ?></strong>
				</p>
				  
				<select name="type" id="svp-video-type" class="select">
                	<?php 
					$types = svp_get_video_source_types( true );
					foreach ( $types as $key => $label ) {
						printf( '<option value="%s"%s>%s</option>', $key, selected( $key, $type, false ), $label );
					}
					?>
        		</select>
      		</td>
    	</tr>
    	<tr id="svp-field-mp4" class="svp-toggle-fields svp-type-default">
      		<td class="label svp-hidden-xs">
        		<label><?php esc_html_e( 'Video', 'simple-video-post' ); ?></label>
				<div class="svp-text-muted">(mp4, webm, ogv, m4v, mov)</div>
      		</td>
      		<td>
        		<p class="svp-hidden-sm svp-hidden-md svp-hidden-lg">
					<strong><?php esc_html_e( 'Video', 'simple-video-post' ); ?></strong> <span class="svp-text-muted">(mp4, webm, ogv, m4v, mov)</span>
				</p>
				
				<div class="svp-input-wrap svp-media-uploader">
					<?php
					if ( ! empty( $quality_levels ) ) {
						printf(
							'<div class="svp-quality-selector"%s>',
							( empty( $sources ) ? ' style="display: none;"' : '' )
						);

						printf( 
							'<p><span class="dashicons dashicons-arrow-down-alt2"></span> %s (%s)</p>',
							esc_html__( 'Select a Quality Level', 'simple-video-post' ),
							esc_html__( 'This will be the default quality level for this video', 'simple-video-post' )
						);

						echo '<ul class="svp-radio horizontal">';

						printf( 
							'<li><label><input type="radio" name="quality_level" value=""%s/>%s</label></li>',
							checked( $quality_level, '', false ),
							esc_html__( 'None', 'simple-video-post' )
						);

						foreach ( $quality_levels as $quality ) {
							printf( 
								'<li><label><input type="radio" name="quality_level" value="%s"%s/>%s</label></li>',
								esc_attr( $quality ),
								checked( $quality_level, $quality, false ),
								esc_html( $quality )
							);
						}

						echo '</ul>';
						echo '</div>';
					}
					?>                                                
					<input type="text" name="mp4" id="svp-mp4" class="text" placeholder="<?php esc_attr_e( 'Enter your direct file URL (OR) upload your file using the button here', 'simple-video-post' ); ?> &rarr;" value="<?php echo esc_attr( $mp4 ); ?>" />
					<div class="svp-upload-media hide-if-no-js">
						<a href="javascript:;" id="svp-upload-mp4" class="button button-secondary" data-format="mp4">
							<?php esc_html_e( 'Upload File', 'simple-video-post' ); ?>
						</a>
					</div>
				</div>

				<?php if ( ! empty( $sources ) ) : 
					foreach ( $sources as $index => $source ) :	?>
						<div class="svp-input-wrap svp-media-uploader svp-source">
							<?php
							echo '<div class="svp-quality-selector">';

							printf( 
								'<p><span class="dashicons dashicons-arrow-down-alt2"></span> %s</p>',
								esc_html__( 'Select a Quality Level', 'simple-video-post' )
							);

							echo '<ul class="svp-radio horizontal">';

							printf( 
								'<li><label><input type="radio" name="quality_levels[%d]" value=""%s/>%s</label></li>',
								$index,
								checked( $source['quality'], '', false ),
								esc_html__( 'None', 'simple-video-post' )
							);

							foreach ( $quality_levels as $quality ) {
								printf( 
									'<li><label><input type="radio" name="quality_levels[%d]" value="%s"%s/>%s</label></li>',
									$index,
									esc_attr( $quality ),
									checked( $source['quality'], $quality, false ),
									esc_html( $quality )
								);
							}
							
							echo '</ul>';
							echo '</div>';
							?>
							<input type="text" name="sources[<?php echo $index; ?>]" class="text" placeholder="<?php esc_attr_e( 'Enter your direct file URL (OR) upload your file using the button here', 'simple-video-post' ); ?> &rarr;" value="<?php echo esc_attr( $source['src'] ); ?>" />
							<div class="svp-upload-media hide-if-no-js">
								<a href="javascript:;" class="button button-secondary svp-button-upload" data-format="mp4">
									<?php esc_html_e( 'Upload File', 'simple-video-post' ); ?>
								</a>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>

				<?php if ( ! empty( $quality_levels ) && count( $sources ) < ( count( $quality_levels ) - 1 ) ) : ?>
					<a href="javascript:;" id="svp-add-new-source" data-limit="<?php echo count( $quality_levels ); ?>"><?php esc_html_e( '[+] Add More Quality Levels', 'simple-video-post' ); ?></a>
				<?php endif; ?> 
      		</td>
    	</tr>
		<?php if ( ! empty( $webm ) ) : ?>
			<tr id="svp-field-webm" class="svp-toggle-fields svp-type-default">
				<td class="label svp-hidden-xs">
					<label><?php esc_html_e( 'WebM', 'simple-video-post' ); ?></label>
					<div class="svp-text-error">(<?php esc_html_e( 'deprecated', 'simple-video-post' ); ?>)</div>
				</td>
				<td>
					<p class="svp-hidden-sm svp-hidden-md svp-hidden-lg">
						<strong><?php esc_html_e( 'WebM', 'simple-video-post' ); ?></strong> <span class="svp-text-error">(<?php esc_html_e( 'deprecated', 'simple-video-post' ); ?>)</span>
					</p>

					<div class="svp-input-wrap svp-media-uploader">                                                
						<input type="text" name="webm" id="svp-webm" class="text" placeholder="<?php esc_attr_e( 'Enter your direct file URL (OR) upload your file using the button here', 'simple-video-post' ); ?> &rarr;" value="<?php echo esc_attr( $webm ); ?>" />
						<div class="svp-upload-media hide-if-no-js">
							<a href="javascript:;" id="svp-upload-webm" class="button button-secondary" data-format="webm">
								<?php esc_html_e( 'Upload File', 'simple-video-post' ); ?>
							</a>
						</div>
					</div>
				</td>
			</tr>
		<?php endif; ?>

		<?php if ( ! empty( $ogv ) ) : ?>
			<tr id="svp-field-ogv" class="svp-toggle-fields svp-type-default">
				<td class="label svp-hidden-xs">
					<label><?php esc_html_e( 'OGV', 'simple-video-post' ); ?></label>
					<div class="svp-text-error">(<?php esc_html_e( 'deprecated', 'simple-video-post' ); ?>)</div>
				</td>
				<td>
					<p class="svp-hidden-sm svp-hidden-md svp-hidden-lg">
						<strong><?php esc_html_e( 'OGV', 'simple-video-post' ); ?></strong> <span class="svp-text-error">(<?php esc_html_e( 'deprecated', 'simple-video-post' ); ?>)</span>
					</p>
					
					<div class="svp-input-wrap svp-media-uploader">                                                
						<input type="text" name="ogv" id="svp-ogv" class="text" placeholder="<?php esc_attr_e( 'Enter your direct file URL (OR) upload your file using the button here', 'simple-video-post' ); ?> &rarr;" value="<?php echo esc_attr( $ogv ); ?>" />
						<div class="svp-upload-media hide-if-no-js">
							<a href="javascript:;" id="svp-upload-ogv" class="button button-secondary" data-format="ogv">
								<?php esc_html_e( 'Upload File', 'simple-video-post' ); ?>
							</a>
						</div>
					</div> 
				</td>
			</tr> 
		<?php endif; ?> 
    	<tr class="svp-toggle-fields svp-type-youtube">
      		<td class="label svp-hidden-xs">
        		<label><?php esc_html_e( 'YouTube', 'simple-video-post' ); ?></label>
      		</td>
      		<td>
        		<p class="svp-hidden-sm svp-hidden-md svp-hidden-lg">
					<strong><?php esc_html_e( 'YouTube', 'simple-video-post' ); ?></strong>
				</p>
				  
				<div class="svp-input-wrap">
          			<input type="text" name="youtube" id="svp-youtube" class="text" placeholder="<?php printf( '%s: https://www.youtube.com/watch?v=twYp6W6vt2U', esc_attr__( 'Example', 'simple-video-post' ) ); ?>" value="<?php echo esc_url( $youtube ); ?>" />
				</div>
      		</td>
    	</tr>
    	<tr class="svp-toggle-fields svp-type-vimeo">
      		<td class="label svp-hidden-xs">
        		<label><?php esc_html_e( 'Vimeo', 'simple-video-post' ); ?></label>
      		</td>
      		<td>
        		<p class="svp-hidden-sm svp-hidden-md svp-hidden-lg">
					<strong><?php esc_html_e( 'Vimeo', 'simple-video-post' ); ?></strong>
				</p>
				  
				<div class="svp-input-wrap">
          			<input type="text" name="vimeo" id="svp-vimeo" class="text" placeholder="<?php printf( '%s: https://vimeo.com/108018156', esc_attr__( 'Example', 'simple-video-post' ) ); ?>" value="<?php echo esc_url( $vimeo ); ?>" />
				</div>
      		</td>
    	</tr>
        <tr class="svp-toggle-fields svp-type-dailymotion">
      		<td class="label svp-hidden-xs">
        		<label><?php esc_html_e( 'Dailymotion', 'simple-video-post' ); ?></label>
      		</td>
      		<td>
        		<p class="svp-hidden-sm svp-hidden-md svp-hidden-lg">
					<strong><?php esc_html_e( 'Dailymotion', 'simple-video-post' ); ?></strong>
				</p>
				  
				<div class="svp-input-wrap">
          			<input type="text" name="dailymotion" id="svp-dailymotion" class="text" placeholder="<?php printf( '%s: https://www.dailymotion.com/video/x11prnt', esc_attr__( 'Example', 'simple-video-post' ) ); ?>" value="<?php echo esc_url( $dailymotion ); ?>" />
				</div>
      		</td>
    	</tr>
        <tr class="svp-toggle-fields svp-type-facebook">
      		<td class="label svp-hidden-xs">
        		<label><?php esc_html_e( 'Facebook', 'simple-video-post' ); ?></label>
      		</td>
      		<td>
        		<p class="svp-hidden-sm svp-hidden-md svp-hidden-lg">
					<strong><?php esc_html_e( 'Facebook', 'simple-video-post' ); ?></strong>
				</p>
				  
				<div class="svp-input-wrap">
          			<input type="text" name="facebook" id="svp-facebook" class="text" placeholder="<?php printf( '%s: https://www.facebook.com/facebook/videos/10155278547321729', esc_attr__( 'Example', 'simple-video-post' ) ); ?>" value="<?php echo esc_url( $facebook ); ?>" />
				</div>
      		</td>
    	</tr>
        <tr class="svp-toggle-fields svp-type-embedcode">
            <td class="label svp-hidden-xs">
                <label><?php esc_html_e( 'Embed Code', 'simple-video-post' ); ?></label>
            </td>
            <td>
                <p class="svp-hidden-sm svp-hidden-md svp-hidden-lg">
					<strong><?php esc_html_e( 'Embed Code', 'simple-video-post' ); ?></strong>
				</p>
				
				<textarea name="embedcode" id="svp-embedcode" class="textarea" rows="6" placeholder="<?php esc_attr_e( 'Enter your Iframe Embed Code', 'simple-video-post' ); ?>"><?php echo esc_textarea( $embedcode ); ?></textarea>

				<p>
					<?php
					printf(
						'<span class="svp-text-error"><strong>%s</strong></span>: %s',
						esc_html__( 'Warning', 'simple-video-post' ),
						esc_html__( 'This field allows "iframe" and "script" tags. So, make sure the code you\'re adding with this field is harmless to your website.', 'simple-video-post' )
					);
					?>
				</p>
            </td>
        </tr>
        <?php do_action( 'svp_admin_add_video_source_fields', $post->ID ); ?>
   	 	<tr>
      		<td class="label svp-hidden-xs">
        		<label><?php esc_html_e( 'Image', 'simple-video-post' ); ?></label>
      		</td>
      		<td>
        		<p class="svp-hidden-sm svp-hidden-md svp-hidden-lg">
					<strong><?php esc_html_e( 'Image', 'simple-video-post' ); ?></strong>
				</p>
				
				<div id="svp-image-uploader" class="svp-input-wrap svp-media-uploader">                                                
					<input type="text" name="image" id="svp-image" class="text" placeholder="<?php esc_attr_e( 'Enter your direct file URL (OR) upload your file using the button here', 'simple-video-post' ); ?> &rarr;" value="<?php echo esc_attr( $image ); ?>" />
					<div class="svp-upload-media hide-if-no-js">
						<a href="javascript:;" id="svp-upload-image" class="button button-secondary" data-format="image">
							<?php esc_html_e( 'Upload File', 'simple-video-post' ); ?>
						</a>
					</div>
				</div>

				<?php do_action( 'svp_admin_after_image_field' ); ?> 
      		</td>
    	</tr> 
    	<tr>
      		<td class="label svp-hidden-xs">
        		<label><?php esc_html_e( 'Duration', 'simple-video-post' ); ?></label>
      		</td>
      		<td>
        		<p class="svp-hidden-sm svp-hidden-md svp-hidden-lg">
					<strong><?php esc_html_e( 'Duration', 'simple-video-post' ); ?></strong>
				</p>
				  
				<div class="svp-input-wrap">
          			<input type="text" name="duration" id="svp-duration" class="text" placeholder="6:30" value="<?php echo esc_attr( $duration ); ?>" />
       			</div>
      		</td>
    	</tr>
    	<tr>
      		<td class="label svp-hidden-xs">
        		<label><?php esc_html_e( 'Views', 'simple-video-post' ); ?></label>
      		</td>
      		<td>
        		<p class="svp-hidden-sm svp-hidden-md svp-hidden-lg">
					<strong><?php esc_html_e( 'Views', 'simple-video-post' ); ?></strong>
				</p>
				  
				<div class="svp-input-wrap">
          			<input type="text" name="views" id="svp-views" class="text" value="<?php echo esc_attr( $views ); ?>" />
       			</div>
      		</td>
    	</tr>     
  	</tbody>
</table>

<?php if ( ! empty( $quality_levels ) ) : ?>
	<div id="svp-source-clone" style="display: none;">
		<div class="svp-input-wrap svp-media-uploader svp-source">
			<?php
			echo '<div class="svp-quality-selector">';
			printf( 
				'<p><span class="dashicons dashicons-arrow-down-alt2"></span> %s</p>',
				esc_html__( 'Select a Quality Level', 'simple-video-post' )
			);
			echo '<ul class="svp-radio horizontal">';
			printf( 
				'<li><label><input type="radio" value=""/>%s</label></li>',
				esc_html__( 'None', 'simple-video-post' )
			);
			foreach ( $quality_levels as $quality ) {
				printf( 
					'<li><label><input type="radio" value="%s"/>%s</label></li>',
					esc_attr( $quality ),
					esc_html( $quality )
				);
			}
			echo '</ul>';
			echo '</div>';
			?>
			<input type="text" class="text" placeholder="<?php esc_attr_e( 'Enter your direct file URL (OR) upload your file using the button here', 'simple-video-post' ); ?> &rarr;" value="" />
			<div class="svp-upload-media hide-if-no-js">
				<a href="javascript:;" class="button button-secondary svp-button-upload" data-format="mp4">
					<?php esc_html_e( 'Upload File', 'simple-video-post' ); ?>
				</a>
			</div>
		</div>
	</div>
<?php endif; ?>

<?php
// Add a nonce field
wp_nonce_field( 'svp_save_video_sources', 'svp_video_sources_nonce' );