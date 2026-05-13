( function () {
	'use strict';

	function play( el ) {
		var text = el.getAttribute( 'data-original-text' ) || el.textContent.trim();
		el.setAttribute( 'data-original-text', text );

		el.style.position = 'relative';
		el.style.overflow = 'hidden';
		el.innerHTML      = '';

		var textSpan           = document.createElement( 'span' );
		textSpan.textContent   = text;
		textSpan.style.cssText = 'display:inline-block;opacity:0;';

		var block           = document.createElement( 'span' );
		block.style.cssText = 'position:absolute;top:0;left:0;width:100%;height:100%;background:currentColor;transform-origin:left;transform:scaleX(0);transition:transform 0.5s cubic-bezier(0.86,0,0.07,1);';

		el.appendChild( textSpan );
		el.appendChild( block );

		var t1 = setTimeout( function () {
			block.style.transform = 'scaleX(1)';
		}, 50 );

		var t2 = setTimeout( function () {
			textSpan.style.opacity    = '1';
			block.style.transformOrigin = 'right';
			block.style.transform       = 'scaleX(0)';
		}, 550 );

		var t3 = setTimeout( function () {
			play( el );
		}, 550 + 500 + 2500 );

		el._blockRevealTimers = [ t1, t2, t3 ];
	}

	window.FrblAnimations = window.FrblAnimations || {};
	window.FrblAnimations['block-reveal'] = play;
} )();
