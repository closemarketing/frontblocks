( function () {
	'use strict';

	function init() {
		document.querySelectorAll( '.frbl-text-animation[data-animation]' ).forEach( function ( el ) {
			var type    = el.getAttribute( 'data-animation' );
			var handler = window.FrblAnimations && window.FrblAnimations[ type ];
			if ( typeof handler === 'function' ) {
				handler( el );
			}
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
