<?php

/**
 * Settings
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
 * SVP_Admin_Settings class.
 *
 * @since 1.0.0
 */
class SVP_Admin_Settings {

	/**
     * Settings tabs array.
     *
	 * @since  1.0.0
	 * @access protected
     * @var    array
     */
    protected $tabs = array();
	
	/**
     * Settings sections array.
     *
	 * @since  1.0.0
	 * @access protected
     * @var    array
     */
    protected $sections = array();
	
	/**
     * Settings fields array
     *
	 * @since  1.0.0
	 * @access protected
     * @var    array
     */
    protected $fields = array();
	
	/**
	 * Add a settings menu for the plugin.
	 *
	 * @since 1.0.0
	 */
	public function svp_options_page() {	
		add_options_page(
			__( 'Simple Video Post - Settings', 'simple-video-post' ),
			__( 'Simple Video Post - Settings', 'simple-video-post' ),
			'manage_options',
			'svp_settings',
			array( $this, 'display_settings_form' )
		);
	}
	
	/**
	 * Display settings form.
	 *
	 * @since 1.0.0
	 */
	public function display_settings_form() {
		require_once SVP_PLUGIN_DIR . 'admin/partials/settings.php';		
	}
	
	/**
	 * Initiate settings.
	 *
	 * @since 1.0.0
	 */
	public function admin_init() {	
		$this->tabs     = $this->get_tabs();
        $this->sections = $this->get_sections();
        $this->fields   = $this->get_fields();
		
        // Initialize settings
        $this->initialize_settings();		
	}
	
	/**
     * Get settings tabs.
     *
	 * @since  1.0.0
     * @return array $tabs Setting tabs array.
     */
    public function get_tabs() {	
		$tabs = array(
			'general'  => __( 'General', 'simple-video-post' ),
        );
		
		return apply_filters( 'svp_settings_tabs', $tabs );	
	}
	
	/**
     * Get settings sections.
     *
	 * @since  1.0.0
     * @return array $sections Setting sections array.
     */
    public function get_sections() {		
		$sections = array(			
            array(
                'id'          => 'svp_player_settings',
                'title'       => __( 'Player Settings', 'simple-video-post' ),
                'description' => '',
                'tab'         => 'general',
                'page'        => 'svp_player_settings'
            ),			
        );

        if ( false !== get_option( 'svp_brand_settings' ) ) {
            $sections[] = array(
                'id'          => 'svp_brand_settings',
                'title'       => __( 'Logo & Branding', 'simple-video-post' ),
                'description' => '',
                'tab'         => 'general',
                'page'        => 'svp_brand_settings'
            );
        }
		
		return apply_filters( 'svp_settings_sections', $sections );		
	}
	
	/**
     * Get settings fields.
     *
	 * @since  1.0.0
     * @return array $fields Setting fields array.
     */
    public function get_fields() {
        $video_templates = svp_get_video_templates();

		$fields = array(			
			'svp_player_settings' => array(
                array(
                    'name'              => 'width',
                    'label'             => __( 'Width', 'simple-video-post' ),
                    'description'       => __( 'In pixels. Maximum width of the player. Leave this field empty to scale 100% of its enclosing container/html element.', 'simple-video-post' ),
                    'type'              => 'text',
                    'sanitize_callback' => 'svp_sanitize_int'
                ),
				array(
                    'name'              => 'ratio',
                    'label'             => __( 'Ratio', 'simple-video-post' ),
                    'description'       => sprintf(
						'%s<br /><br /><strong>%s:</strong><br />"56.25" - %s<br />"62.5" - %s<br />"75" - %s<br />"67" - %s<br />"100" - %s<br />"41.7" - %s', 
						__( "In percentage. 1 to 100. Calculate player's height using the ratio value entered.", 'simple-video-post' ),
						__( 'Examples', 'simple-video-post' ),
						__( 'Wide Screen TV', 'simple-video-post' ),
						__( 'Monitor Screens', 'simple-video-post' ),
						__( 'Classic TV', 'simple-video-post' ),
						__( 'Photo Camera', 'simple-video-post' ),
						__( 'Square', 'simple-video-post' ),
						__( 'Cinemascope', 'simple-video-post' )
					),
                    'type'              => 'text',
                    'sanitize_callback' => 'floatval'
                ),
				array(
                    'name'              => 'autoplay',
                    'label'             => __( 'Autoplay', 'simple-video-post' ),
                    'description'       => __( 'Check this to start playing the video as soon as it is ready', 'simple-video-post' ),
                    'type'              => 'checkbox',
					'sanitize_callback' => 'intval'
                ),
				array(
                    'name'              => 'loop',
                    'label'             => __( 'Loop', 'simple-video-post' ),
                    'description'       => __( 'Check this, so that the video will start over again, every time it is finished', 'simple-video-post' ),
                    'type'              => 'checkbox',
					'sanitize_callback' => 'intval'
                ),
                array(
                    'name'              => 'muted',
                    'label'             => __( 'Muted', 'simple-video-post' ),
                    'description'       => __( 'Check this to turn OFF the audio output of the video by default', 'simple-video-post' ),
                    'type'              => 'checkbox',
					'sanitize_callback' => 'intval'
                ),
				array(
                    'name'              => 'preload',
                    'label'             => __( 'Preload', 'simple-video-post' ),
                    'description'       => sprintf(
						'%s<br /><br />%s<br />%s<br />%s',
						__( 'Specifies if and how the video should be loaded when the page loads.', 'simple-video-post' ),
						__( '"Auto" - the video should be loaded entirely when the page loads', 'simple-video-post' ),
						__( '"Metadata" - only metadata should be loaded when the page loads', 'simple-video-post' ),
						__( '"None" - the video should not be loaded when the page loads', 'simple-video-post' )
					),
                    'type'              => 'select',
					'options'           => array(
						'auto'     => __( 'Auto', 'simple-video-post' ),
						'metadata' => __( 'Metadata', 'simple-video-post' ),
						'none'     => __( 'None', 'simple-video-post' )
					),
					'sanitize_callback' => 'sanitize_key'
                ),
				array(
                    'name'              => 'controls',
                    'label'             => __( 'Player Controls', 'simple-video-post' ),
                    'description'       => '',
                    'type'              => 'multicheck',
					'options'           => array(
						'playpause'  => __( 'Play / Pause', 'simple-video-post' ),
						'current'    => __( 'Current Time', 'simple-video-post' ),
						'progress'   => __( 'Progressbar', 'simple-video-post' ),
						'duration'   => __( 'Duration', 'simple-video-post' ),
                        'tracks'     => __( 'Subtitles', 'simple-video-post' ),
                        'quality'    => __( 'Quality Selector', 'simple-video-post' ),
                        'speed'      => __( 'Speed Control', 'simple-video-post' ),
						'volume'     => __( 'Volume', 'simple-video-post' ),
						'fullscreen' => __( 'Fullscreen', 'simple-video-post' )						
					),
					'sanitize_callback' => 'svp_sanitize_array'
                ),
                array(
                    'name'              => 'quality_levels',
                    'label'             => __( 'Quality Levels', 'simple-video-post' ),
                    'description'       => __( 'Enter the video quality levels, one per line.', 'simple-video-post' ),
					'type'              => 'textarea',
					'sanitize_callback' => 'sanitize_textarea_field'
				),
                array(
                    'name'              => 'use_native_controls',
                    'label'             => __( 'Use Native Controls', 'simple-video-post' ),
                    'description'       => __( 'Enables native player controls on the selected source types. For example, uses YouTube Player for playing YouTube videos & Vimeo Player for playing Vimeo videos. Note that none of our custom player features will work on the selected sources.', 'simple-video-post' ),
                    'type'              => 'multicheck',
					'options'           => array(
						'youtube'     => __( 'YouTube', 'simple-video-post' ),
						'vimeo'       => __( 'Vimeo', 'simple-video-post' ),
						'dailymotion' => __( 'Dailymotion', 'simple-video-post' ),
						'facebook'    => __( 'Facebook', 'simple-video-post' )						
					),
					'sanitize_callback' => 'svp_sanitize_array'
                )
			),		
        );
        
        if ( false !== get_option( 'svp_brand_settings' ) ) {
            $fields['svp_brand_settings'] = array(
				array(
                    'name'              => 'show_logo',
                    'label'             => __( 'Show Logo', 'simple-video-post' ),
                    'description'       => __( 'Check this option to show the watermark on the video.', 'simple-video-post' ),
                    'type'              => 'checkbox',
                    'sanitize_callback' => 'intval'
               	),
				array(
                    'name'              => 'logo_image',
                    'label'             => __( 'Logo Image', 'simple-video-post' ),
                    'description'       => __( 'Upload the image file of your logo. We recommend using the transparent PNG format with width below 100 pixels. If you do not enter any image, no logo will displayed.', 'simple-video-post' ),
                    'type'              => 'file',
                    'sanitize_callback' => 'svp_sanitize_url'
               	),
				array(
                    'name'              => 'logo_link',
                    'label'             => __( 'Logo Link', 'simple-video-post' ),
                    'description'       => __( 'The URL to visit when the watermark image is clicked. Clicking a logo will have no affect unless this is configured.', 'simple-video-post' ),
                    'type'              => 'text',
                    'sanitize_callback' => 'esc_url_raw'
               	),
				array(
                    'name'              => 'logo_position',
                    'label'             => __( 'Logo Position', 'simple-video-post' ),
                    'description'       => __( 'This sets the corner in which to display the watermark.', 'simple-video-post' ),
                    'type'              => 'select',
					'options'           => array(
						'topleft'     => __( 'Top Left', 'simple-video-post' ),
						'topright'    => __( 'Top Right', 'simple-video-post' ),
						'bottomleft'  => __( 'Bottom Left', 'simple-video-post' ),
						'bottomright' => __( 'Bottom Right', 'simple-video-post' )
					),
                    'sanitize_callback' => 'sanitize_key'
               	),
				array(
                    'name'              => 'logo_margin',
                    'label'             => __( 'Logo Margin', 'simple-video-post' ),
                    'description'       => __( 'The distance, in pixels, of the logo from the edges of the display.', 'simple-video-post' ),
                    'type'              => 'text',
                    'sanitize_callback' => 'floatval'
               	),
				array(
                    'name'              => 'copyright_text',
                    'label'             => __( 'Copyright Text', 'simple-video-post' ),
                    'description'       => __( 'Text that is shown when a user right-clicks the player with the mouse.', 'simple-video-post' ),
                    'type'              => 'text',
                    'sanitize_callback' => 'sanitize_text_field'
               	)
			);
        }
		
		return apply_filters( 'svp_settings_fields', $fields );		
	}
	
	/**
     * Initialize and registers the settings sections and fields to WordPress.
     *
     * @since 1.0.0
     */
    public function initialize_settings() {	
        // Register settings sections & fields
        foreach ( $this->sections as $section ) {		
			$page_hook = isset( $section['page'] ) ? $section['page'] : $section['id'];
			
			// Sections
            if ( false == get_option( $section['id'] ) ) {
                add_option( $section['id'] );
            }
			
            if ( isset( $section['description'] ) && ! empty( $section['description'] ) ) {
                $callback = array( $this, 'settings_section_callback' );
            } elseif ( isset( $section['callback'] ) ) {
                $callback = $section['callback'];
            } else {
                $callback = null;
            }
			
            add_settings_section( $section['id'], $section['title'], $callback, $page_hook );
			
			// Fields			
			$fields = $this->fields[ $section['id'] ];
			
			foreach ( $fields as $option ) {			
                $name     = $option['name'];
                $type     = isset( $option['type'] ) ? $option['type'] : 'text';
                $label    = isset( $option['label'] ) ? $option['label'] : '';
                $callback = isset( $option['callback'] ) ? $option['callback'] : array( $this, 'callback_' . $type );				
                $args     = array(
                    'id'                => $name,
                    'class'             => isset( $option['class'] ) ? $option['class'] : $name,
                    'label_for'         => "{$section['id']}[{$name}]",
                    'description'       => isset( $option['description'] ) ? $option['description'] : '',
                    'name'              => $label,
                    'section'           => $section['id'],
                    'size'              => isset( $option['size'] ) ? $option['size'] : null,
                    'options'           => isset( $option['options'] ) ? $option['options'] : '',
                    'sanitize_callback' => isset( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : '',
                    'type'              => $type,
                    'placeholder'       => isset( $option['placeholder'] ) ? $option['placeholder'] : '',
                    'min'               => isset( $option['min'] ) ? $option['min'] : '',
                    'max'               => isset( $option['max'] ) ? $option['max'] : '',
                    'step'              => isset( $option['step'] ) ? $option['step'] : ''					
                );
				
                add_settings_field( "{$section['id']}[{$name}]", $label, $callback, $page_hook, $section['id'], $args );
            }
			
			// Creates our settings in the options table
        	register_setting( $page_hook, $section['id'], array( $this, 'sanitize_options' ) );			
        }		
    }

    /**
 	 * Displays a section description.
 	 *
	 * @since 1.0.0
	 * @param array $args Settings section args.
 	 */
	public function settings_section_callback( $args ) {
        foreach ( $this->sections as $section ) {
            if ( $section['id'] == $args['id'] ) {
                printf( '<div class="inside">%s</div>', $section['description'] ); 
                break;
            }
        }
    }

	/**
     * Displays a text field for a settings field.
     *
	 * @since 1.0.0
     * @param array $args Settings field args.
     */
    public function callback_text( $args ) {	
        $value       = esc_attr( $this->get_option( $args['id'], $args['section'], '' ) );
        $size        = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
        $type        = isset( $args['type'] ) ? $args['type'] : 'text';
        $placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="' . $args['placeholder'] . '"';
		
        $html        = sprintf( '<input type="%1$s" class="%2$s-text" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s"%6$s/>', $type, $size, $args['section'], $args['id'], $value, $placeholder );
        $html       .= $this->get_field_description( $args );
		
        echo $html;		
    }
	
	/**
     * Displays a url field for a settings field.
     *
	 * @since 1.0.0
     * @param array $args Settings field args.
     */
    public function callback_url( $args ) {
        $this->callback_text( $args );
    }
	
	/**
     * Displays a number field for a settings field.
     *
	 * @since 1.0.0
     * @param array $args Settings field args.
     */
    public function callback_number( $args ) {	
        $value       = esc_attr( $this->get_option( $args['id'], $args['section'], 0 ) );
        $size        = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
        $type        = isset( $args['type'] ) ? $args['type'] : 'number';
        $placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="' . $args['placeholder'] . '"';
        $min         = empty( $args['min'] ) ? '' : ' min="' . $args['min'] . '"';
        $max         = empty( $args['max'] ) ? '' : ' max="' . $args['max'] . '"';
        $step        = empty( $args['max'] ) ? '' : ' step="' . $args['step'] . '"';
		
        $html        = sprintf( '<input type="%1$s" class="%2$s-number" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s"%6$s%7$s%8$s%9$s/>', $type, $size, $args['section'], $args['id'], $value, $placeholder, $min, $max, $step );
        $html       .= $this->get_field_description( $args );
		
        echo $html;		
    }
	
	/**
     * Displays a checkbox for a settings field.
     *
	 * @since 1.0.0
     * @param array $args Settings field args.
     */
    public function callback_checkbox( $args ) {	
        $value = esc_attr( $this->get_option( $args['id'], $args['section'], 0 ) );
		
        $html  = '<fieldset>';
        $html  .= sprintf( '<label for="%1$s[%2$s]">', $args['section'], $args['id'] );
        $html  .= sprintf( '<input type="hidden" name="%1$s[%2$s]" value="0" />', $args['section'], $args['id'] );
        $html  .= sprintf( '<input type="checkbox" class="checkbox" id="%1$s[%2$s]" name="%1$s[%2$s]" value="1" %3$s />', $args['section'], $args['id'], checked( $value, 1, false ) );
        $html  .= sprintf( '%1$s</label>', $args['description'] );
        $html  .= '</fieldset>';
		
        echo $html;		
    }
	
	/**
     * Displays a multicheckbox for a settings field.
     *
     * @since 1.0.0
     * @param array $args Settings field args.
     */
    public function callback_multicheck( $args ) {	
        $value = $this->get_option( $args['id'], $args['section'], array() );
		
        $html  = '<fieldset>';
        $html .= sprintf( '<input type="hidden" name="%1$s[%2$s]" value="" />', $args['section'], $args['id'] );
        foreach ( $args['options'] as $key => $label ) {
            $checked  = in_array( $key, $value ) ? 'checked="checked"' : '';
            $html    .= sprintf( '<label for="%1$s[%2$s][%3$s]">', $args['section'], $args['id'], $key );
            $html    .= sprintf( '<input type="checkbox" class="checkbox" id="%1$s[%2$s][%3$s]" name="%1$s[%2$s][%3$s]" value="%3$s" %4$s />', $args['section'], $args['id'], $key, $checked );
            $html    .= sprintf( '%1$s</label><br>',  $label );
        }
        $html .= $this->get_field_description( $args );
        $html .= '</fieldset>';
		
        echo $html;		
    }
	
	/**
     * Displays a radio button for a settings field.
     *
     * @since 1.0.0
     * @param array $args Settings field args.
     */
    public function callback_radio( $args ) {	
        $value = $this->get_option( $args['id'], $args['section'], '' );
		
        $html  = '<fieldset>';
        foreach ( $args['options'] as $key => $label ) {
            $html .= sprintf( '<label for="%1$s[%2$s][%3$s]">',  $args['section'], $args['id'], $key );
            $html .= sprintf( '<input type="radio" class="radio" id="%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s" %4$s />', $args['section'], $args['id'], $key, checked( $value, $key, false ) );
            $html .= sprintf( '%1$s</label><br>', $label );
        }
        $html .= $this->get_field_description( $args );
        $html .= '</fieldset>';
		
        echo $html;		
    }
	
	/**
     * Displays a selectbox for a settings field.
     *
     * @since 1.0.0
     * @param array $args Settings field args.
     */
    public function callback_select( $args ) {	
        $value = esc_attr( $this->get_option( $args['id'], $args['section'], '' ) );
        $size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
		
        $html  = sprintf( '<select class="%1$s" name="%2$s[%3$s]" id="%2$s[%3$s]">', $size, $args['section'], $args['id'] );
        foreach ( $args['options'] as $key => $label ) {
            $html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $value, $key, false ), $label );
        }
        $html .= sprintf( '</select>' );
        $html .= $this->get_field_description( $args );
		
        echo $html;		
    }
	
	/**
     * Displays a textarea for a settings field.
     *
     * @since 1.0.0
     * @param array $args Settings field args.
     */
    public function callback_textarea( $args ) {	
        $value       = esc_textarea( $this->get_option( $args['id'], $args['section'], '' ) );
        $size        = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
        $placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="'.$args['placeholder'].'"';
		
        $html        = sprintf( '<textarea rows="5" cols="55" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]"%4$s>%5$s</textarea>', esc_attr($size), esc_attr($args['section']), esc_attr($args['id']), esc_attr($placeholder), esc_textarea($value) );
        $html       .= $this->get_field_description( $args );
		
        echo $html;		
    }
	
	/**
     * Get field description for display.
     *
	 * @since 1.0.0
     * @param array $args Settings field args.
     */
    public function get_field_description( $args ) {	
        if ( ! empty( $args['description'] ) ) {
            if ( 'wysiwyg' == $args['type'] ) {
                $description = sprintf( '<pre>%s</pre>', $args['description'] );
            } else {
                $description = sprintf( '<p class="description">%s</p>', $args['description'] );
            }
        } else {
            $description = '';
        }
		
        return $description;		
    }
	
	/**
     * Sanitize callback for Settings API.
     *
	 * @since  1.0.0
     * @param  array $options The unsanitized collection of options.
     * @return                The collection of sanitized values.
     */
    public function sanitize_options( $options ) {	
        if ( ! $options ) {
            return $options;
        }
		
        foreach ( $options as $option_slug => $option_value ) {		
            $sanitize_callback = $this->get_sanitize_callback( $option_slug );
			
            // If callback is set, call it
            if ( $sanitize_callback ) {
                $options[ $option_slug ] = call_user_func( $sanitize_callback, $option_value );
                continue;
            }			
        }
		
        return $options;		
    }
	
	/**
     * Get sanitization callback for given option slug.
     *
	 * @since  1.0.0
     * @param  string $slug Option slug.
     * @return mixed        String or bool false.
     */
    public function get_sanitize_callback( $slug = '' ) {	
        if ( empty( $slug ) ) {
            return false;
        }
		
        // Iterate over registered fields and see if we can find proper callback
        foreach ( $this->fields as $section => $options ) {
            foreach ( $options as $option ) {
                if ( $option['name'] != $slug ) {
                    continue;
                }
				
                // Return the callback name
                return isset( $option['sanitize_callback'] ) && is_callable( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : false;
            }
        }
		
        return false;		
    }
	
	/**
     * Get the value of a settings field.
     *
	 * @since  1.0.0
     * @param  string $option  Settings field name.
     * @param  string $section The section name this field belongs to.
     * @param  string $default Default text if it's not found.
     * @return string
     */
    public function get_option( $option, $section, $default = '' ) {	
        $options = get_option( $section );
		
        if ( ! empty( $options[ $option ] ) ) {
            return $options[ $option ];
        }
		
        return $default;		
    }
	
}
