( function () {
	'use strict';

	function initBeforeAfterBlock( block ) {
		var initialPosition = parseFloat( block.dataset.initialPosition ) || 50;
		var beforeEl        = block.querySelector( '.frbl-before-after__before' );
		var handle          = block.querySelector( '.frbl-before-after__handle' );

		if ( ! beforeEl || ! handle ) {
			return;
		}

		var isDragging = false;

		function clamp( value, min, max ) {
			return Math.max( min, Math.min( max, value ) );
		}

		function setPosition( pos ) {
			pos = clamp( pos, 0, 100 );
			beforeEl.style.clipPath  = 'inset(0 ' + ( 100 - pos ) + '% 0 0)';
			handle.style.left        = pos + '%';
			handle.setAttribute( 'aria-valuenow', Math.round( pos ) );
		}

		function getPositionFromEvent( e ) {
			var rect    = block.getBoundingClientRect();
			var clientX = e.touches && e.touches.length ? e.touches[ 0 ].clientX : e.clientX;
			return ( ( clientX - rect.left ) / rect.width ) * 100;
		}

		setPosition( initialPosition );

		handle.addEventListener( 'mousedown', function ( e ) {
			isDragging = true;
			e.preventDefault();
		} );

		handle.addEventListener( 'touchstart', function () {
			isDragging = true;
		}, { passive: true } );

		document.addEventListener( 'mousemove', function ( e ) {
			if ( ! isDragging ) { return; }
			setPosition( getPositionFromEvent( e ) );
		} );

		document.addEventListener( 'touchmove', function ( e ) {
			if ( ! isDragging ) { return; }
			setPosition( getPositionFromEvent( e ) );
		}, { passive: true } );

		document.addEventListener( 'mouseup', function () {
			isDragging = false;
		} );

		document.addEventListener( 'touchend', function () {
			isDragging = false;
		} );

		handle.setAttribute( 'tabindex', '0' );

		handle.addEventListener( 'keydown', function ( e ) {
			var current = parseFloat( handle.style.left ) || initialPosition;
			if ( e.key === 'ArrowLeft' ) {
				setPosition( current - 1 );
				e.preventDefault();
			} else if ( e.key === 'ArrowRight' ) {
				setPosition( current + 1 );
				e.preventDefault();
			}
		} );
	}

	function init() {
		var blocks = document.querySelectorAll( '.frbl-before-after:not(.frbl-before-after--editor)' );
		blocks.forEach( initBeforeAfterBlock );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
