( function () {
	'use strict';

	function sizeIframe( container ) {
		var media  = container.querySelector( '.frbl-vimeo-bg__media' );
		var iframe = container.querySelector( '.frbl-vimeo-bg__iframe' );
		if ( ! iframe || ! media ) return;

		var w = media.offsetWidth;
		var h = media.offsetHeight;
		if ( ! w || ! h ) return;

		var ratio = 16 / 9;
		var iframeW, iframeH;

		if ( w / h > ratio ) {
			// Container wider than 16:9 — scale by width
			iframeW = w;
			iframeH = Math.ceil( w / ratio );
		} else {
			// Container taller than 16:9 — scale by height
			iframeH = h;
			iframeW = Math.ceil( h * ratio );
		}

		iframe.style.width  = iframeW + 'px';
		iframe.style.height = iframeH + 'px';
		iframe.style.top    = Math.floor( ( h - iframeH ) / 2 ) + 'px';
		iframe.style.left   = Math.floor( ( w - iframeW ) / 2 ) + 'px';
	}

	function sizeAll() {
		document.querySelectorAll( '.frbl-vimeo-bg' ).forEach( sizeIframe );
	}

	document.addEventListener( 'DOMContentLoaded', sizeAll );
	window.addEventListener( 'load', sizeAll );
	window.addEventListener( 'resize', sizeAll );
} )();
