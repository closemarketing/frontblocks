( function () {
	'use strict';

	var DURATION = 1.2;

	function play( el ) {
		var text = el.getAttribute( 'data-original-text' ) || el.textContent.trim();
		el.setAttribute( 'data-original-text', text );
		el.innerHTML = '';

		var span           = document.createElement( 'span' );
		span.textContent   = text;
		span.style.cssText = 'display:inline-block;opacity:0;';
		span.style.animation = 'frblTrackingExpand ' + DURATION + 's forwards';

		el.appendChild( span );

		setTimeout( function () { play( el ); }, DURATION * 1000 + 2500 );
	}

	window.FrblAnimations = window.FrblAnimations || {};
	window.FrblAnimations['tracking-expand'] = play;
} )();
