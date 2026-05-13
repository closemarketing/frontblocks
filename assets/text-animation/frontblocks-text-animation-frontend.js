( function () {
	'use strict';

	var CHAR_DURATION = 0.8;
	var CHAR_DELAY    = 0.05;

	function splitChars( el ) {
		var text  = el.textContent;
		el.innerHTML = '';
		text.split( '' ).forEach( function ( char, i ) {
			var span       = document.createElement( 'span' );
			span.textContent = char;
			span.className   = 'frbl-char';
			span.style.cssText = 'display:inline-block;white-space:pre;opacity:0;';
			span.style.animation      = 'frblFadeIn ' + CHAR_DURATION + 's forwards';
			span.style.animationDelay = ( i * CHAR_DELAY ) + 's';
			el.appendChild( span );
		} );
	}

	function playFadeIn( el ) {
		splitChars( el );
		var totalMs = ( el.querySelectorAll( '.frbl-char' ).length * CHAR_DELAY + CHAR_DURATION ) * 1000 + 2500;
		setTimeout( function () { playFadeIn( el ); }, totalMs );
	}

	var handlers = {
		'fade-in': playFadeIn,
	};

	function init() {
		document.querySelectorAll( '.frbl-text-animation[data-animation]' ).forEach( function ( el ) {
			var type = el.getAttribute( 'data-animation' );
			if ( handlers[ type ] ) {
				handlers[ type ]( el );
			}
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
