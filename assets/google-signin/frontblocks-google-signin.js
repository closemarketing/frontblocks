/**
 * Bridges Google Identity Services with the FrontBlocks Google Sign-In REST endpoint.
 */
( function () {
	'use strict';

	function getConfig() {
		return window.frblGoogleSignIn || null;
	}

	function getButtons() {
		return document.querySelectorAll( '.frbl-google-signin-button' );
	}

	function init() {
		var config = getConfig();

		if ( ! config || ! window.google || ! google.accounts || ! google.accounts.id ) {
			return;
		}

		google.accounts.id.initialize( {
			client_id: config.clientId,
			callback: handleCredentialResponse,
			auto_select: false,
			itp_support: true,
		} );

		getButtons().forEach( function ( el ) {
			if ( el.getAttribute( 'data-frbl-rendered' ) ) {
				return;
			}

			google.accounts.id.renderButton( el, {
				type: 'standard',
				theme: 'outline',
				size: 'large',
				shape: 'rectangular',
				text: el.getAttribute( 'data-text' ) || 'signin_with',
				width: el.getAttribute( 'data-width' ) || undefined,
			} );

			el.setAttribute( 'data-frbl-rendered', '1' );
		} );
	}

	function setButtonsBusy( busy ) {
		getButtons().forEach( function ( el ) {
			el.style.opacity = busy ? '0.5' : '1';
			el.style.pointerEvents = busy ? 'none' : '';
		} );
	}

	function showError( message ) {
		var existing = document.querySelector( '.frbl-google-signin-error' );
		if ( existing ) {
			existing.remove();
		}

		var buttons = getButtons();
		if ( ! buttons.length ) {
			return;
		}

		var notice = document.createElement( 'p' );
		notice.className = 'frbl-google-signin-error';
		notice.textContent = message;
		buttons[ 0 ].parentNode.appendChild( notice );
	}

	function handleCredentialResponse( response ) {
		var config = getConfig();
		if ( ! config ) {
			return;
		}

		setButtonsBusy( true );

		fetch( config.apiUrl, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify( {
				credential: response.credential,
				nonce: config.nonce,
				redirect_to: config.redirectTo || '',
			} ),
		} )
			.then( function ( res ) {
				return res.json().then( function ( body ) {
					return { ok: res.ok, body: body };
				} );
			} )
			.then( function ( result ) {
				if ( result.ok && result.body && result.body.success ) {
					window.location.href = result.body.redirect_to || config.redirectTo || '/';
					return;
				}

				setButtonsBusy( false );
				showError( ( result.body && result.body.message ) || config.i18n.error );
			} )
			.catch( function () {
				setButtonsBusy( false );
				showError( config.i18n.error );
			} );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}

	// The Google Identity Services script loads asynchronously and may not
	// be ready yet when DOMContentLoaded fires.
	window.addEventListener( 'load', init );
} )();
