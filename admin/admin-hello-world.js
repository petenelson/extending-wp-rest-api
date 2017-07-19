( function( $ )  {

	'use strict';

	$.extend( {

		extend_rest_api: {

			bind_events: function() {

				$( '.options-form' ).submit( function ( e ) {
					e.preventDefault();
				});

				$('.button-get').click( function( e ) {
					$.extend_rest_api.send_get();
				});

				$('.button-post').click( function( e ) {
					$.extend_rest_api.send_post();
				});

				$('.button-delete').click( function( e ) {
					$.extend_rest_api.send_delete();
				});
			},

			display_response: function( response ) {
				$('.postbox pre code').text( JSON.stringify( response, null, 2 ) ).each( function( i, block ) {
					hljs.highlightBlock( block );
				});
			},

			send_get: function() {
				$.get( ExtendingRESTAPI.endpoints.hello_world, {}, function( response ) {
				} ).always( function ( response ) {
					$.extend_rest_api.display_response( response );
				});
			},

			send_post: function() {
				$.post( ExtendingRESTAPI.endpoints.hello_world, { my_number:$('.extend-my-number').val() }, function() {
				} ).always( function ( response ) {
					$.extend_rest_api.display_response( response );
				});
			},

			send_delete: function() {
				$.ajax( {
					url: ExtendingRESTAPI.endpoints.hello_world,
					data: { },
					type: 'DELETE',
					success: function() {
					}
				} ).always( function ( response ) {
					$.extend_rest_api.display_response( response );
				});

			}



		}
	} );

	// send nonce to the WP API
	$( document ).ajaxSend( function( event, xhr ) {
		// you can also send _wp_rest_nonce in the GET or POST params
		xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
	});

	$(document).ready( function() {
		$.extend_rest_api.bind_events();
	});


})( jQuery );