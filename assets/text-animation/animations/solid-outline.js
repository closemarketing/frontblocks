( function () {
	'use strict';

	var DURATION = 0.8;
	var DELAY    = 0.05;

	function play( el ) {
		var text = el.getAttribute( 'data-original-text' ) || el.textContent;
		el.setAttribute( 'data-original-text', text );

		var strokeColor = window.getComputedStyle( el ).color || '#ffffff';
		el.style.setProperty( '--frbl-stroke-color', strokeColor );

		el.innerHTML = '';

		text.split( '' ).forEach( function ( char, i ) {
			var span             = document.createElement( 'span' );
			span.textContent     = char;
			span.className       = 'frbl-char';
			span.style.cssText   = 'display:inline-block;white-space:pre;opacity:0;';
			span.style.animation      = 'frblSolidOutline ' + DURATION + 's forwards';
			span.style.animationDelay = ( i * DELAY ) + 's';
			el.appendChild( span );
		} );

		var totalMs = ( text.length * DELAY + DURATION ) * 1000 + 2500;
		el._frblLoop && setTimeout( function () { play( el ); }, totalMs );
	}

	window.FrblAnimations = window.FrblAnimations || {};
	window.FrblAnimations['solid-outline'] = play;
} )();
