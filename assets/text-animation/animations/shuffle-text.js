( function () {
	'use strict';

	var SYMBOLS     = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()';
	var FRAME_DELAY = 30;

	function randomSymbol() {
		return SYMBOLS[ Math.floor( Math.random() * SYMBOLS.length ) ];
	}

	function play( el ) {
		var text = el.getAttribute( 'data-original-text' ) || el.textContent;
		el.setAttribute( 'data-original-text', text );
		el.innerHTML = '';

		var chars    = text.split( '' );
		var spanList = chars.map( function ( char ) {
			var span       = document.createElement( 'span' );
			span.className = 'frbl-char';
			el.appendChild( span );
			return { el: span, char: char, isSpace: char === ' ' };
		} );

		var frame = 0;

		function update() {
			var allDone = true;

			spanList.forEach( function ( item, i ) {
				if ( item.isSpace ) { item.el.textContent = ' '; return; }

				var startFrame = i * 2;
				var endFrame   = startFrame + 15;

				if ( frame < startFrame ) {
					allDone = false;
					item.el.textContent = '';
				} else if ( frame < endFrame ) {
					allDone = false;
					item.el.textContent = randomSymbol();
				} else {
					item.el.textContent = item.char;
				}
			} );

			frame++;

			if ( ! allDone ) {
				setTimeout( function () { requestAnimationFrame( update ); }, FRAME_DELAY );
			} else {
				setTimeout( function () { play( el ); }, 2500 );
			}
		}

		update();
	}

	window.FrblAnimations = window.FrblAnimations || {};
	window.FrblAnimations['shuffle-text'] = play;
} )();
