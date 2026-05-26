( function () {
	'use strict';

	var INTERVAL = 100;

	function play( el ) {
		var text = el.getAttribute( 'data-original-text' ) || el.textContent.trim();
		el.setAttribute( 'data-original-text', text );
		el.innerHTML = '';

		var textSpan         = document.createElement( 'span' );
		var cursor           = document.createElement( 'span' );
		cursor.style.cssText = 'display:inline-block;width:0.6em;height:1.1em;background:currentColor;margin-left:4px;vertical-align:middle;animation:frblBlinkCursor 1s infinite;';

		el.appendChild( textSpan );
		el.appendChild( cursor );

		var chars = text.split( '' );
		var i     = 0;
		var timer;

		function type() {
			if ( i < chars.length ) {
				textSpan.textContent += chars[ i ];
				i++;
				timer = setTimeout( type, INTERVAL );
			} else {
				timer = el._frblLoop && setTimeout( function () { play( el ); }, 2500 );
			}
		}

		type();
	}

	window.FrblAnimations = window.FrblAnimations || {};
	window.FrblAnimations['terminal-type'] = play;
} )();
