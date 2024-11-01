(function( $ ) {
	'use strict';
	
	// Vars
	var svp_autoplay_allowed = false;
	var svp_autoplay_requires_muted = false;
	var svp_players = [];	

	/**
	 * Check unmuted autoplay support.
	 *
	 * @since 2.4.0
	 */
	function svp_check_unmuted_autoplay_support() {
		canAutoplay
			.video({ timeout: 100, muted: false })
			.then(function( response ) {
				if ( response.result === false ) {
					// Unmuted autoplay is not allowed
					svp_check_muted_autoplay_support();
				} else {
					// Unmuted autoplay is allowed
					svp_autoplay_allowed = true;
					svp_init_player();
				}
			});
	}

	/**
	 * Check muted autoplay support.
	 *
	 * @since 2.4.0
	 */
	function svp_check_muted_autoplay_support() {
		canAutoplay
			.video({ timeout: 100, muted: true })
			.then(function( response ) {
				if ( response.result === false ) {
					// Muted autoplay is not allowed
					svp_autoplay_allowed = false;
				} else {
					// Muted autoplay is allowed
					svp_autoplay_allowed = true;
					svp_autoplay_requires_muted = true;					
				};
				
				svp_init_player();
			});
	}

	/**
	 * Update video views count.
	 *
	 * @since 2.4.0
	 * @param {Object} settings The settings array.
	 */
	function svp_update_views_count( settings ) {
		if ( 'svp_videos' == settings.post_type ) {
			var data = {
				'action': 'svp_update_views_count',
				'post_id': settings.post_id,
				'security': settings.views_nonce
			};

			$.post( 
				svp_player.ajax_url, 
				data, 
				function( response ) {
					// Do nothing
				}
			);
		}
	}

	/**
	 * jQuery Plugin: svp_player
	 *
	 * @since 2.4.0
	 */
	$.fn.svp_player = function() {
		// Vars
		var $elem    = $( this );
		var id       = $elem.data( 'id' );
		var settings = window[ 'svp_player_' + id ];
		var player   = null;

		// GDPR consent
		var gdpr_consent = function() {		
			var data = {
				'action': 'svp_set_cookie',
				'security': svp_player.ajax_nonce
			};

			$.post( 
				svp_player.ajax_url, 
				data, 
				function( response ) {
					if ( response.success ) {
						init_player();
						$elem.find( '.svp-privacy-wrapper' ).remove();
					}
				}
			);
		}

		// Init player
		var init_player = function() {
			// Is iframe?
			if ( 'iframe' == settings.type ) {
				$( '#svp-player-' + id ).replaceWith( '<iframe width="560" height="315" src="' + settings.iframe_src + '" frameborder="0" scrolling="no" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>' );
				svp_update_views_count( settings );
			} else {
				player = videojs( 'svp-player-' + id, settings.player );
				
				settings.html5 = {
					hls: {
						overrideNative: !videojs.browser.IS_ANY_SAFARI,
					}
				};
				
				// Trigger ready event
				var config = {
					player: player,
					id: id,
					settings: settings					
				};

				$elem.trigger( 'player.init', config );

				// Fired when the player is ready
				player.ready(function() {
					svp_players.push( player );
				});

				// Fired the first time a video is played
				var viewed = false;

				player.on( 'play', function( e ) {
					if ( ! viewed ) {
						viewed = true;
						svp_update_views_count( settings );
					}
					
					// Determine which player the event is coming from
					var id = e.target.id;

					// Loop through the array of players
					for ( var i = 0; i < svp_players.length; i++ ) {
						// Get the player(s) that did not trigger the play event
						if ( svp_players[ i ].id() != id ) {
							// Pause the other player(s)
							videojs( svp_players[ i ].id() ).pause();
						}
					}
				});

				// Logo overlay
				if ( settings.show_logo ) {
					init_logo();
				}

				// Custom contextmenu
				if ( settings.copyright_text ) {
					init_contextmenu();
				}
			}
		}

		// Merge attributes
		var merge_attributes = function( attributes ) {
			var str = '';

			for ( var key in attributes ) {
				str += ( key + '="' + attributes[ key ] + '" ' );
			}
	
			return str;
		}

		// Logo overlay
		var init_logo = function() {
			var attributes = [];
			attributes['src'] = settings.logo_image;

			if ( settings.logo_margin ) {
				settings.logo_margin = settings.logo_margin - 5;
			}

			var align;
			switch ( settings.logo_position ) {
				case 'topleft':
					align = 'top-left';
					attributes['style'] = 'margin: ' + settings.logo_margin + 'px;';
					break;
				case 'topright':
					align = 'top-right';
					attributes['style'] = 'margin: ' + settings.logo_margin + 'px;';
					break;					
				case 'bottomright':
					align = 'bottom-right';
					attributes['style'] = 'margin: ' + settings.logo_margin + 'px;';
					break;
				default:						
					align = 'bottom-left';
					attributes['style'] = 'margin: ' + settings.logo_margin + 'px;';
					break;					
			}

			if ( settings.logo_link ) {
				attributes['onclick'] = "window.location.href='" + settings.logo_link + "';";
			}

			player.overlay({
				content: '<img ' +  merge_attributes( attributes ) + '/>',
				class: 'vjs-logo',
				align: align,
				showBackground: false					
			});
		}

		// Custom contextmenu
		var init_contextmenu = function() {
			if ( ! $( '#svp-contextmenu' ).length ) {
				$( 'body' ).append( '<div id="svp-contextmenu" style="display: none;"><div id="svp-contextmenu-item">' + settings.copyright_text + '</div></div>' );
			}

			var contextmenu = document.getElementById( 'svp-contextmenu' );
			var timeout_handler = '';
			
			$( '#svp-player-' + id ).on( 'contextmenu', function( e ) {						
				if ( 3 === e.keyCode || 3 === e.which ) {
					e.preventDefault();
					e.stopPropagation();
					
					var width = contextmenu.offsetWidth,
						height = contextmenu.offsetHeight,
						x = e.pageX,
						y = e.pageY,
						doc = document.documentElement,
						scrollLeft = ( window.pageXOffset || doc.scrollLeft ) - ( doc.clientLeft || 0 ),
						scrollTop = ( window.pageYOffset || doc.scrollTop ) - ( doc.clientTop || 0 ),
						left = x + width > window.innerWidth + scrollLeft ? x - width : x,
						top = y + height > window.innerHeight + scrollTop ? y - height : y;
			
					contextmenu.style.display = '';
					contextmenu.style.left = left + 'px';
					contextmenu.style.top = top + 'px';
					
					clearTimeout( timeout_handler );

					timeout_handler = setTimeout(function() {
						contextmenu.style.display = 'none';
					}, 1500 );				
				}														 
			});
			
			document.addEventListener( 'click', function() {
				contextmenu.style.display = 'none';								 
			});
		}

		// Init
    	this.init = function() {
			// Autoplay
			if ( 'iframe' != settings.type ) {
				if ( settings.player.autoplay ) {
					settings.player.autoplay = svp_autoplay_allowed;

					if ( svp_autoplay_requires_muted ) {
						settings.player.muted = true;
					}
				}
			}

			// Init player
			if ( settings.show_consent ) {
				$elem.find( '.svp-privacy-consent-button' ).on( 'click', function() {
					$( this ).html( '...' );

					if ( 'iframe' != settings.type ) {
						settings.player.autoplay = true;
					}

					gdpr_consent();
				});
			} else {
				init_player();
			}			
		}

		// ...
		return this.init();
	}

	/**
	 * Init player.
	 *
	 * @since 2.4.0
	 */
	function svp_init_player() {
		$( '.svp-player-standard' ).each(function() {
			$( this ).svp_player();
		});
	}

	/**
	 * Called when the page has loaded.
	 *
	 * @since 1.0.0
	 */
	$(function() {
		
		// Init Player
		if ( typeof canAutoplay === 'undefined' ) {
			svp_init_player();
		} else {
			svp_check_unmuted_autoplay_support();
		}

		// Update views count for the non-iframe embeds
		$( '.svp-player-raw' ).each(function() {
			var $elem    = $( this );
			var id       = $elem.data( 'id' );
			var settings = window[ 'svp_player_' + id ];

			svp_update_views_count( settings );
		});

	});

})( jQuery );
