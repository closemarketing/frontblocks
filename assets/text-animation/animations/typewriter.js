( function () {
	'use strict';

	var CHAR_INTERVAL = 80;

	function play( el ) {
		var text = el.getAttribute( 'data-original-text' ) || el.textContent;
		el.setAttribute( 'data-original-text', text );
		el.innerHTML = '';

		var chars = text.split( '' );
		var i     = 0;

		function type() {
			if ( i < chars.length ) {
				var span       = document.createElement( 'span' );
				span.className = 'frbl-char';
				span.textContent = chars[ i ];
				el.appendChild( span );
				i++;
				setTimeout( type, CHAR_INTERVAL );
			} else {
				setTimeout( function () { play( el ); }, 2500 );
			}
		}

		type();
	}

	window.FrblAnimations = window.FrblAnimations || {};
	window.FrblAnimations['typewriter'] = play;
} )();
