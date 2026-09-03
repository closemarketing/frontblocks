/**
 * Editor registration for the "Google Login" block.
 *
 * Written without a build step (no JSX): uses wp.element.createElement
 * directly, matching the dependencies declared for this script.
 */
( function ( blocks, element, blockEditor, components, i18n, serverSideRender ) {
	'use strict';

	var el = element.createElement;
	var Fragment = element.Fragment;
	var __ = i18n.__;
	var registerBlockType = blocks.registerBlockType;
	var InspectorControls = blockEditor.InspectorControls;
	var PanelBody = components.PanelBody;
	var SelectControl = components.SelectControl;
	var TextControl = components.TextControl;
	var ServerSideRender = serverSideRender && ( serverSideRender.default || serverSideRender );

	var googleIcon = el(
		'svg',
		{ viewBox: '0 0 48 48', width: 20, height: 20 },
		el( 'path', { fill: '#4285F4', d: 'M45.12 24.5c0-1.56-.14-3.06-.4-4.5H24v9h11.8c-.51 2.75-2.06 5.08-4.4 6.64v5.52h7.11c4.16-3.83 6.61-9.48 6.61-16.16z' } ),
		el( 'path', { fill: '#34A853', d: 'M24 46c5.94 0 10.92-1.97 14.56-5.34l-7.11-5.52c-1.97 1.32-4.49 2.1-7.45 2.1-5.73 0-10.58-3.87-12.33-9.07H4.34v5.7C7.96 41.07 15.4 46 24 46z' } ),
		el( 'path', { fill: '#FBBC05', d: 'M11.67 28.17A13.9 13.9 0 0 1 10.9 24c0-1.45.25-2.86.77-4.17v-5.7H4.34A22.87 22.87 0 0 0 2 24c0 3.7.89 7.19 2.34 10.87l7.33-5.7z' } ),
		el( 'path', { fill: '#EA4335', d: 'M24 10.75c3.23 0 6.13 1.11 8.41 3.29l6.31-6.31C34.91 4.18 29.93 2 24 2 15.4 2 7.96 6.93 4.34 14.13l7.33 5.7c1.75-5.2 6.6-9.08 12.33-9.08z' } )
	);

	registerBlockType( 'frontblocks/google-login', {
		icon: googleIcon,
		edit: function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;

			return el(
				Fragment,
				{},
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ title: __( 'Google Login settings', 'frontblocks' ) },
						el( SelectControl, {
							label: __( 'Button type', 'frontblocks' ),
							value: attributes.mode,
							options: [
								{ label: __( 'Sign in with Google', 'frontblocks' ), value: 'login' },
								{ label: __( 'Sign up with Google', 'frontblocks' ), value: 'register' },
							],
							onChange: function ( value ) {
								setAttributes( { mode: value } );
							},
						} ),
						el( TextControl, {
							label: __( 'Redirect URL after sign-in', 'frontblocks' ),
							value: attributes.redirect,
							onChange: function ( value ) {
								setAttributes( { redirect: value } );
							},
							help: __( 'Leave empty to stay on the current page.', 'frontblocks' ),
						} )
					)
				),
				ServerSideRender
					? el( ServerSideRender, {
							block: 'frontblocks/google-login',
							attributes: attributes,
					  } )
					: el(
							'p',
							{},
							'signup' === attributes.mode
								? __( 'Sign up with Google', 'frontblocks' )
								: __( 'Sign in with Google', 'frontblocks' )
					  )
			);
		},
		save: function () {
			// Dynamic block: markup is generated server-side by render_callback.
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.i18n, window.wp.serverSideRender );
