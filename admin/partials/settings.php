<?php

/**
 * Settings Form.
 *
 * @link    http://divyarthinfotech.com
 * @since   1.0.0
 *
 * @package Simple_Video_Post
 */

$active_tab     = isset( $_GET['tab'] ) ?  sanitize_text_field( $_GET['tab'] ) : 'general';
$active_section = isset( $_GET['section'] ) ? sanitize_text_field( $_GET['section'] ) : '';

$sections = array();
foreach ( $this->sections as $section ) {
	$tab = $section['tab'];
	
	if ( ! isset( $sections[ $tab ] ) ) {
		$sections[ $tab ] = array();
    }
    
    $sections[ $tab ][] = $section;
}
?>
<div id="main">
	<div id="content" class="wrap svp-settings">
		<div class="page_head"><h1><i class="fa fa-star-of-life"></i><?php esc_html_e( 'Plugin Settings', 'simple-video-post' ); ?></h1></div>

		<?php settings_errors(); ?>

		<h2 class="nav-tab-wrapper">
			<?php		
			foreach ( $this->tabs as $tab => $title ) {
				$url = add_query_arg( 'tab', $tab, admin_url( 'admin.php?page=svp_settings' ) );

				foreach ( $sections[ $tab ] as $section ) {
					$url = add_query_arg( 'section', $section['id'], $url );

					if ( $tab == $active_tab && empty( $active_section ) ) {
						$active_section = $section['id'];
					}
					
					break;
				}
				
				printf( 
					'<a href="%s" class="%s">%s</a>', 
					esc_url( $url ), 
					( $tab == $active_tab ? 'nav-tab nav-tab-active' : 'nav-tab' ), 
					esc_html( $title )
				);
			}
			?>
		</h2>
		
		<?php	
		$section_links = array();

		foreach ( $sections[ $active_tab ] as $section ) {
			$page = $section['page'];

			$url = add_query_arg( 
				array(
					'tab'     => $active_tab,
					'section' => $page
				), 
				admin_url( 'admin.php?page=svp_settings' ) 
			);

			if ( ! isset(  $section_links[ $page ] ) ) {
				$section_links[ $page ] = sprintf( 
					'<a href="%s" class="%s">%s</a>',			
					esc_url( $url ),
					( $section['id'] == $active_section ? 'current' : '' ),
					esc_html( $section['title'] )
				);
			}
		}

		if ( count( $section_links ) > 1 ) : ?>
			<ul class="subsubsub"><li><?php echo esc_html(implode( ' | </li><li>', $section_links )); ?></li></ul>
			<div class="clear"></div>
		<?php endif; ?>
		
		<form method="post" action="options.php"> 
			<?php
			$page_hook = $active_section;
			
			settings_fields( $page_hook );
			do_settings_sections( $page_hook );
			
			submit_button();
			?>
		</form>
	</div>
	
	<div id="sidebar">
		<div class="side_card">
			<h2><i class="fas fa-life-ring"></i> Help &amp; Support</h2>
			<p>Got any issue or not sure how to achieve what you are looking for with the plugin or have any idea or missing feature ? Let me know at </p><a class="button button-primary cfe_btn" href="https://divyarthinfotech.com/contact-us/" target="_blank">Contact Us <i class="fas fa-arrow-right"></i></a>
		</div>
		<div class="happy_box">
		<h2>Happy with the plugin ?</h2>
			<div class="cfe_bottom side_card">
				<div class="icon"><img draggable="false" role="img" class="emoji" alt="☕" src="https://s.w.org/images/core/emoji/13.0.1/svg/2615.svg"></div>
				<h3>Buy me a Coffee !</h3>
				<p>If you like this plugin, buy me a coffee and help support this plugin !</p>
				<div class="cfe_form"><a class="button button-primary cfe_btn" href="https://paypal.me/rawalprashant/10" data-link="https://paypal.me/rawalprashant/" target="_blank">Buy me a coffee !</a></div>
			</div>
			<div class="rate_review side_card">
				<div class="icon"><img draggable="false" role="img" class="emoji" alt="⭐" src="https://s.w.org/images/core/emoji/13.0.1/svg/2b50.svg"></div>
				<h3>Rate &amp; Review</h3>
				<p>If you have a minute, please rate this plugin and let others know and try this plugin.</p><a href="https://wordpress.org/support/plugin/wp-simple-video-post/reviews/?rate=5#new-post" class="button button-primary" target="_blank">Rate &amp; Review <i class="fas fa-arrow-right"></i></a>
			</div>
		</div>
	</div>
</div>