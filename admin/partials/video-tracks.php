<?php

/**
 * Videos: "Subtitles" meta box.
 *
 * @link    http://divyarthinfotech.com
 * @since   1.0.0
 *
 * @package Simple_Video_Post
 */
?>

<table id="svp-tracks" class="svp-table widefat">
	<tr class="svp-hidden-xs">
  		<th style="width: 5%;"></th>
    	<th><?php esc_html_e( 'File URL', 'simple-video-post' ); ?></th>
    	<th style="width: 15%;"><?php esc_html_e( 'Label', 'simple-video-post' ); ?></th>
    	<th style="width: 10%;"><?php esc_html_e( 'Srclang', 'simple-video-post' ); ?></th>
    	<th style="width: 20%;"></th>
  	</tr>
  	<?php foreach ( $tracks as $key => $track ) : ?>
        <tr class="svp-tracks-row">
            <td class="svp-handle svp-hidden-xs"><span class="dashicons dashicons-move"></span></td>
            <td>
                <p class="svp-hidden-sm svp-hidden-md svp-hidden-lg"><strong><?php esc_html_e( 'File URL', 'simple-video-post' ); ?></strong></p>
                <div class="svp-input-wrap">
                    <input type="text" name="track_src[]" id="svp-track-<?php echo esc_attr( $key ); ?>" class="text svp-track-src" value="<?php echo esc_attr( $track['src'] ); ?>" />
                </div>
            </td>
            <td>
                <p class="svp-hidden-sm svp-hidden-md svp-hidden-lg"><strong><?php esc_html_e( 'Label', 'simple-video-post' ); ?></strong></p>
                    <div class="svp-input-wrap">
                        <input type="text" name="track_label[]" class="text svp-track-label" placeholder="<?php esc_attr_e( 'English', 'simple-video-post' ); ?>" value="<?php echo esc_attr( $track['label'] ); ?>" />
                    </div>
            </td>
            <td>
                <p class="svp-hidden-sm svp-hidden-md svp-hidden-lg"><strong><?php esc_html_e( 'Srclang', 'simple-video-post' ); ?></strong></p>
                <div class="svp-input-wrap">
                    <input type="text" name="track_srclang[]" class="text svp-track-srclang" placeholder="<?php esc_attr_e( 'en', 'simple-video-post' ); ?>" value="<?php echo esc_attr( $track['srclang'] ); ?>" />
                </div>
            </td>
            <td>
                <div class="hide-if-no-js">
                    <a class="svp-upload-track" href="javascript:;"><?php esc_html_e( 'Upload File', 'simple-video-post' ); ?></a>
                    <span class="svp-pipe-separator">|</span>
                    <a class="svp-delete-track" href="javascript:;"><?php esc_html_e( 'Delete', 'simple-video-post' ); ?></a>
	  			</div>
            </td>
        </tr>
  	<?php endforeach; ?>
</table>

<p class="hide-if-no-js">
   	<a id="svp-add-new-track" class="button" href="javascript:;"><?php esc_html_e( 'Add New File', 'simple-video-post' ); ?></a>
</p>

<table id="svp-tracks-clone" style="display: none;">
  	<tr class="svp-tracks-row">
    	<td class="svp-handle svp-hidden-xs"><span class="dashicons dashicons-move"></span></td>
  		<td>
      		<p class="svp-hidden-sm svp-hidden-md svp-hidden-lg"><strong><?php esc_html_e( 'File URL', 'simple-video-post' ); ?></strong></p>
      		<div class="svp-input-wrap">
        		<input type="text" name="track_src[]" class="text svp-track-src" />
      		</div>
    	</td>
    	<td>
      		<p class="svp-hidden-sm svp-hidden-md svp-hidden-lg"><strong><?php esc_html_e( 'Label', 'simple-video-post' ); ?></strong></p>
      		<div class="svp-input-wrap">
        		<input type="text" name="track_label[]" class="text svp-track-label" placeholder="<?php esc_attr_e( 'English', 'simple-video-post' ); ?>" />
      		</div>
    	</td>
    	<td>
      		<p class="svp-hidden-sm svp-hidden-md svp-hidden-lg"><strong><?php esc_html_e( 'Srclang', 'simple-video-post' ); ?></strong></p>
      		<div class="svp-input-wrap">
        		<input type="text" name="track_srclang[]" class="text svp-track-srclang" placeholder="<?php esc_attr_e( 'en', 'simple-video-post' ); ?>" />
      		</div>
    	</td>
    	<td>
      		<div class="hide-if-no-js">
        		<a class="svp-upload-track" href="javascript:;"><?php esc_html_e( 'Upload File', 'simple-video-post' ); ?></a>
        		<span class="svp-pipe-separator">|</span>
        		<a class="svp-delete-track" href="javascript:;"><?php esc_html_e( 'Delete', 'simple-video-post' ); ?></a>
	  		</div>
    	</td>
  	</tr>
</table>

<?php
// Add a nonce field
wp_nonce_field( 'svp_save_video_tracks', 'svp_video_tracks_nonce' );