( function () {
	'use strict';

	var INTERVAL = 50;

	function play( el ) {
		var text = el.getAttribute( 'data-original-text' ) || el.textContent;
		el.setAttribute( 'data-original-text', text );
		el.innerHTML = '';

		var chars   = text.split( '' );
		var indices = chars.map( function ( _, i ) { return i; } ).sort( function () { return Math.random() - 0.5; } );

		chars.forEach( function ( char ) {
			var span           = document.createElement( 'span' );
			span.className     = 'frbl-char';
			span.textContent   = char;
			span.style.cssText = 'display:inline-block;white-space:pre;opacity:0;';
			el.appendChild( span );
		} );

		if ( window.FrblWordWrap ) { window.FrblWordWrap( el ); }
		var spans = el.querySelectorAll( '.frbl-char' );
		var i     = 0;

		function reveal() {
			if ( i < indices.length ) {
				spans[ indices[ i ] ].style.opacity = '1';
				i++;
				setTimeout( reveal, INTERVAL );
			} else {
				el._frblLoop && setTimeout( function () { play( el ); }, 2500 );

			}
		}

		reveal();
	}

	window.FrblAnimations = window.FrblAnimations || {};
	window.FrblAnimations['random-reveal'] = play;
} )();
