( function( $ )  {

	'use strict';

	function Extend_rest_api()  {

		this.bind_events = function() {

			$( '.options-form' ).submit( function ( e ) {
				e.preventDefault();
			});

			$('.button-get').click( function( e ) {

			});

			$('.button-post').click( function( e ) {

			});

			$('.button-delete').click( function( e ) {

			});

		};

	}


	window.extend_rest_api = window.extend_rest_api || new Extend_rest_api();

	$(document).ready( function() {
		window.extend_rest_api.bind_events();
	});


})( jQuery );