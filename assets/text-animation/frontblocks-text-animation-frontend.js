( function () {
	'use strict';

	// Groups .frbl-char spans into word-level wrappers so browser line-wrapping
	// respects word boundaries instead of breaking mid-word.
	// Each animation file calls this after creating its spans.
	window.FrblWordWrap = function ( el ) {
		var chars = Array.from( el.querySelectorAll( ':scope > .frbl-char' ) );
		if ( ! chars.length ) return;

		var frag   = document.createDocumentFragment();
		var wordEl = null;

		chars.forEach( function ( ch ) {
			// Use data-char attribute when textContent is not yet set (e.g. shuffle-text).
			var txt     = ch.dataset.char !== undefined ? ch.dataset.char : ch.textContent;
			var isSpace = txt === ' ' || txt === ' ';
			if ( isSpace ) {
				wordEl = null;
				frag.appendChild( ch );
			} else {
				if ( ! wordEl ) {
					wordEl = document.createElement( 'span' );
					wordEl.style.cssText = 'display:inline-block;white-space:nowrap;';
					frag.appendChild( wordEl );
				}
				wordEl.appendChild( ch );
			}
		} );

		el.appendChild( frag );
	};

	function init() {
		document.querySelectorAll( '.frbl-text-animation[data-animation]' ).forEach( function ( el ) {
			var type    = el.getAttribute( 'data-animation' );
			var handler = window.FrblAnimations && window.FrblAnimations[ type ];
			if ( typeof handler === 'function' ) {
				el._frblLoop = el.getAttribute( 'data-animation-loop' ) === '1';
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
