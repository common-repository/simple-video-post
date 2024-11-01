(function( $ ) {
	'use strict';

	/**
 	 * Display the media uploader.
 	 *
 	 * @since 1.0.0
 	 */
	function svp_render_media_uploader( $elem, form ) { 
    	var file_frame, attachment;
 
     	// If an instance of file_frame already exists, then we can open it rather than creating a new instance
    	if ( file_frame ) {
        	file_frame.open();
        	return;
    	}; 

     	// Use the wp.media library to define the settings of the media uploader
    	file_frame = wp.media.frames.file_frame = wp.media({
        	frame: 'post',
        	state: 'insert',
        	multiple: false
    	});
 
     	// Setup an event handler for what to do when a media has been selected
    	file_frame.on( 'insert', function() { 
        	// Read the JSON data returned from the media uploader
    		attachment = file_frame.state().get( 'selection' ).first().toJSON();
		
			// First, make sure that we have the URL of the media to display
    		if ( 0 > $.trim( attachment.url.length ) ) {
        		return;
    		};
		
			// Set the data
			switch ( form ) {
				case 'tracks':
					var id = $elem.closest( 'tr' ).find( '.svp-track-src' ).attr( 'id' );
					$( '#' + id ).val( attachment.url );
					break;
				case 'categories':
					$( '#svp-categories-image-id' ).val( attachment.id );
					$( '#svp-categories-image-wrapper' ).html( '<img src="' + attachment.url + '" />' );
				
					$( '#svp-categories-upload-image' ).hide();
					$( '#svp-categories-remove-image' ).show();
					break;
				case 'settings':
					$elem.prev( '.svp-url' ).val( attachment.url );
					break;
				default:					
					$elem.closest( '.svp-media-uploader' ).find( 'input[type=text]' ).val( attachment.url ).trigger( 'file.uploaded' );
			}; 
    	});
 
    	// Now display the actual file_frame
    	file_frame.open(); 
	};

	/**
	 *  Make tracks inside the video form sortable.
     *
	 *  @since 1.0.0
	 */
	function svp_sort_tracks() {		
		var $sortable_element = $( '#svp-tracks tbody' );
			
		if ( $sortable_element.hasClass( 'ui-sortable' ) ) {
			$sortable_element.sortable( 'destroy' );
		};
			
		$sortable_element.sortable({
			handle: '.svp-handle'
		});		
	};

	/**
 	 * Widget: Initiate color picker 
 	 *
 	 * @since 1.0.0
 	 */
	function svp_widget_color_picker( widget ) {
		if ( $.fn.wpColorPicker ) {
			widget.find( '.svp-color-picker-field' ).wpColorPicker( {
				change: _.throttle( function() { // For Customizer
					$( this ).trigger( 'change' );
				}, 3000 )
			});
		}
	}

	function on_svp_widget_update( event, widget ) {
		svp_widget_color_picker( widget );
	}

	/**
	 * Called when the page has loaded.
	 *
	 * @since 1.0.0
	 */
	$(function() {
			   
		// Common: Upload Files
		$( document ).on( 'click', '.svp-upload-media', function( e ) { 
            e.preventDefault();
            svp_render_media_uploader( $( this ), 'default' ); 
		});
		
		// Common: Initiate color picker
		if ( $.fn.wpColorPicker ) {
			$( '.svp-color-picker' ).wpColorPicker();
		}

		// Dashboard: On shortcode type changed
		$( 'input[type=radio]', '#svp-shortcode-selector' ).on( 'change', function( e ) {
			var shortcode = $( 'input[type=radio]:checked', '#svp-shortcode-selector' ).val();

			$( '.svp-shortcode-form' ).hide();
			$( '.svp-shortcode-instructions' ).hide();

			$( '#svp-shortcode-form-' + shortcode ).show();
			$( '#svp-shortcode-instructions-' + shortcode ).show();
		}).trigger( 'change' );

		// Dashboard: Toggle between field sections
		$( document ).on( 'click', '.svp-shortcode-section-header', function( e ) {
			var $elem = $( this ).parent();

			if ( ! $elem.hasClass( 'svp-active' ) ) {
				$( this ).closest( '.svp-shortcode-form' )
					.find( '.svp-shortcode-section.svp-active' )
					.toggleClass( 'svp-active' )
					.find( '.svp-shortcode-controls' )
					.slideToggle();
			}			

			$elem.toggleClass( 'svp-active' )
				.find( '.svp-shortcode-controls' )
				.slideToggle();
		});		

		// Dashboard: Toggle fields based on the selected video source type
		$( 'select[name=type]', '#svp-shortcode-form-video' ).on( 'change', function() {			
			var type = $( this ).val();
			
			$( '#svp-shortcode-form-video' ).removeClass(function( index, classes ) {
				var matches = classes.match( /\svp-type-\S+/ig );
				return ( matches ) ? matches.join(' ') : '';	
			}).addClass( 'svp-type-' + type );
		});

		// Dashboard: Toggle fields based on the selected videos template
		$( 'select[name=template]', '#svp-shortcode-form-videos' ).on( 'change', function() {			
			var template = $( this ).val();
			
			$( '#svp-shortcode-form-videos' ).removeClass(function( index, classes ) {
				var matches = classes.match( /\svp-template-\S+/ig );
				return ( matches ) ? matches.join(' ') : '';	
			}).addClass( 'svp-template-' + template );
		}).trigger( 'change' );

		// Dashboard: Toggle fields based on the selected categories template
		$( 'select[name=template]', '#svp-shortcode-form-categories' ).on( 'change', function() {			
			var template = $( this ).val();
			
			$( '#svp-shortcode-form-categories' ).removeClass(function( index, classes ) {
				var matches = classes.match( /\svp-template-\S+/ig );
				return ( matches ) ? matches.join(' ') : '';	
			}).addClass( 'svp-template-' + template );
		}).trigger( 'change' );

		// Dashboard: Generate shortcode
		$( '#svp-generate-shortcode' ).on( 'click', function( e ) { 
			e.preventDefault();			

			// Shortcode
			var shortcode = $( 'input[type=radio]:checked', '#svp-shortcode-selector' ).val();

			// Attributes
			var props = {};
			
			$( '.svp-shortcode-field', '#svp-shortcode-form-' + shortcode ).each(function() {							
				var $this = $( this );
				var type  = $this.attr( 'type' );
				var name  = $this.attr( 'name' );				
				var value = $this.val();
				var def   = 0;
				
				if ( 'undefined' !== typeof $this.data( 'default' ) ) {
					def = $this.data( 'default' );
				}				
				
				// type = checkbox
				if ( 'checkbox' == type ) {
					value = $this.is( ':checked' ) ? 1 : 0;
				} else {
					// name = category|tag
					if ( 'category' == name || 'tag' == name ) {					
						value = $( 'input[type=checkbox]:checked', $this ).map(function() {
							return this.value;
						}).get().join( "," );
					}
				}				
				
				// Add only if the user input differ from the global configuration
				if ( value != def ) {
					props[ name ] = value;
				}				
			});

			var attrs = shortcode;
			for ( var name in props ) {
				if ( props.hasOwnProperty( name ) ) {
					attrs += ( ' ' + name + '="' + props[ name ] + '"' );
				}
			}

			// Shortcode output		
			$( '#svp-shortcode').val( '[svp_' + attrs + ']' ); 
		});
		
		// Dashboard: Check/Uncheck all checkboxes in the issues table list
		$( '#svp-issues-check-all' ).on( 'change', function( e ) {
			var value = $( this ).is( ':checked' ) ? 1 : 0;	

			if ( value ) {
				$( '.svp-issue', '#svp-issues' ).prop( 'checked', true );
			} else {
				$( '.svp-issue', '#svp-issues' ).prop( 'checked', false );
			}
		});	

		// Dashboard: Validate the issues form
		$( '#svp-issues-form' ).submit(function() {
			var has_input = 0;

			$( '.svp-issue:checked', '#svp-issues' ).each(function() {
				has_input = 1;
			});

			if ( ! has_input ) {
				alert( svp_admin.i18n.no_issues_slected );
				return false;
			}			
		});
		
		// Videos: Toggle fields based on the selected video source type
		$( '#svp-video-type' ).on( 'change', function( e ) { 
            e.preventDefault();
 
 			var type = $( this ).val();
			
			$( '.svp-toggle-fields' ).hide();
			$( '.svp-type-' + type ).show( 300 );
		}).trigger( 'change' );
		
		// Videos: Add new source fields when "Add More Quality Levels" link clicked
		$( '#svp-add-new-source' ).on( 'click', function( e ) {
			e.preventDefault();				
			
			var limit = $( this ).data( 'limit' );
			var length = $( '.svp-quality-selector', '#svp-field-mp4' ).length;	
			var index = length - 1;
			
			if ( 0 == index ) {
				$( '.svp-quality-selector', '#svp-field-mp4' ).show();
			}

			var $row = $( '#svp-source-clone' ).find( '.svp-media-uploader' ).clone();	
			$row.find( 'input[type=radio]' ).attr( 'name', 'quality_levels[' + index + ']' );
			$row.find( 'input[type=text]' ).attr( 'name', 'sources[' + index + ']' );

			$( this ).before( $row ); 		
			
			if ( ( length + 1 ) >= limit ) {
				$( this ).hide();
			}
		});

		// Videos: On quality level selected
		$( '#svp-field-mp4' ).on( 'change', '.svp-quality-selector input[type=radio]', function() {
			var $this = $( this);
			var values = [];

			$( '.svp-quality-selector' ).each(function() {
				var value = $( this ).find( 'input[type=radio]:checked' ).val();
				if (  value ) {
					if ( values.includes( value ) ) {
						$this.prop( 'checked', false );
						alert( svp_admin.i18n.quality_exists );
					} else {
						values.push( value );
					}					
				}
			});
		});
		
		// Videos: Add new subtitle fields when "Add New File" button clicked
		$( '#svp-add-new-track' ).on( 'click', function( e ) { 
            e.preventDefault();
			
			var id = $( '.svp-tracks-row', '#svp-tracks' ).length;
			
			var $row = $( '#svp-tracks-clone' ).find( 'tr' ).clone();
			$row.find( '.svp-track-src' ).attr( 'id', 'svp-track-'+id );
			
            $( '#svp-tracks' ).append( $row ); 
        });
		
		if ( ! $( '.svp-tracks-row', '#svp-tracks' ).length ) {
			$( '#svp-add-new-track' ).trigger( 'click' );
		}

		// Videos: Upload Tracks	
		$( 'body' ).on( 'click', '.svp-upload-track', function( e ) { 
            e.preventDefault();
            svp_render_media_uploader( $( this ), 'tracks' ); 
        });
		
		// Videos: Delete a subtitles fields set when "Delete" button clicked
		$( 'body' ).on( 'click', '.svp-delete-track', function( e ) { 
            e.preventDefault();			
            $( this ).closest( 'tr' ).remove(); 
        });
		
		// Videos: Make the subtitles fields sortable
		svp_sort_tracks();
		
		// Categories: Upload Image	
		$( '#svp-categories-upload-image' ).on( 'click', function( e ) { 
            e.preventDefault();
			svp_render_media_uploader( $( this ), 'categories' ); 
        });
		
		// Categories: Remove Image
		$( '#svp-categories-remove-image' ).on( 'click', function( e ) {														 
            e.preventDefault();
				
			$( '#svp-categories-image-id' ).val( '' );
			$( '#svp-categories-image-wrapper' ).html( '' );
			
			$( '#svp-categories-remove-image' ).hide();
			$( '#svp-categories-upload-image' ).show();	
		});
		
		// Categories: Clear the image field after a category was created
		$( document ).ajaxComplete(function( e, xhr, settings ) {			
			if ( $( "#svp-categories-image-id" ).length ) {				
				var queryStringArr = settings.data.split( '&' );
			   
				if ( -1 !== $.inArray( 'action=add-tag', queryStringArr ) ) {
					var xml = xhr.responseXML;
					var response = $( xml ).find( 'term_id' ).text();
					if ( '' != response ) {
						$( '#svp-categories-image-id' ).val( '' );
						$( '#svp-categories-image-wrapper' ).html( '' );
						
						$( '#svp-categories-remove-image' ).hide();
						$( '#svp-categories-upload-image' ).show();
					};
				};			
			};			
		});

		// Settings: Set Section ID
		$( '.form-table', '#svp-settings' ).each(function() { 
			var str = $( this ).find( 'tr:first th label' ).attr( 'for' );
			var id = str.split( '[' );
			id = id[0].replace( /_/g, '-' );

			$( this ).attr( 'id', id );
		});
		
		// Settings: Upload Files
		$( '.svp-browse', '#svp-settings' ).on( 'click', function( e ) {																	  
			e.preventDefault();			
			svp_render_media_uploader( $( this ), 'settings' );			
		});

		// Settings: Toggle fields based on the selected categories template
		$( 'tr.template', '#svp-categories-settings' ).find( 'select' ).on( 'change', function() {			
			var template = $( this ).val();
			
			$( '#svp-categories-settings' ).removeClass(function( index, classes ) {
				var matches = classes.match( /\svp-template-\S+/ig );
				return ( matches ) ? matches.join(' ') : '';	
			}).addClass( 'svp-template-' + template );
		}).trigger( 'change' );

		// Settings: Toggle fields based on the selected videos template
		$( 'tr.template', '#svp-videos-settings' ).find( 'select' ).on( 'change', function() {			
			var template = $( this ).val();
			
			$( '#svp-videos-settings' ).removeClass(function( index, classes ) {
				var matches = classes.match( /\svp-template-\S+/ig );
				return ( matches ) ? matches.join(' ') : '';	
			}).addClass( 'svp-template-' + template );
		}).trigger( 'change' );	

		// Categories Widget: Toggle fields based on the selected categories template
		$( document ).on( 'change', '.svp-widget-form-categories .svp-widget-input-template', function() {			
			var template = $( this ).val();
			
			$( this ).closest( '.svp-widget-form-categories' ).removeClass(function( index, classes ) {
				var matches = classes.match( /\svp-template-\S+/ig );
				return ( matches ) ? matches.join(' ') : '';	
			}).addClass( 'svp-template-' + template );
		});

		// Videos Widget: Toggle fields based on the selected videos template
		$( document ).on( 'change', '.svp-widget-form-videos .svp-widget-input-template', function() {			
			var template = $( this ).val();
			
			$( this ).closest( '.svp-widget-form-videos' ).removeClass(function( index, classes ) {
				var matches = classes.match( /\svp-template-\S+/ig );
				return ( matches ) ? matches.join(' ') : '';	
			}).addClass( 'svp-template-' + template );
		});

		// Videos Widget: Initiate color picker
		$( '#widgets-right .widget:has(.svp-color-picker-field)' ).each(function() {
			svp_widget_color_picker( $( this ) );
		});

		$( document ).on( 'widget-added widget-updated', on_svp_widget_update );
			   
	});	

})( jQuery );
