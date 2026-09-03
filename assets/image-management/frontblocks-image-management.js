/**
 * FrontBlocks Image Management — settings page UI.
 *
 * Renders the registered-sizes table from the localized config, keeps the
 * hidden JSON field in sync so it saves with the normal settings form, and
 * drives the bulk regenerate/convert actions via batched admin-ajax calls.
 */
( function () {
	'use strict';

	const BATCH_SIZE = 5;

	document.addEventListener( 'DOMContentLoaded', function () {
		const enableCheckbox = document.getElementById( 'enable_image_management' );
		const fieldsWrapper  = document.getElementById( 'image-management-fields-wrapper' );
		const configInput    = document.getElementById( 'frbl-image-sizes-config' );
		const tableContainer = document.getElementById( 'frbl-image-sizes-table' );
		const configScript   = document.getElementById( 'frbl-image-management-config' );
		const maxDimensionToggle = document.getElementById( 'image_max_upload_dimension_enabled' );
		const maxDimensionField  = document.getElementById( 'image-max-upload-dimension-field' );
		const qualityInputs  = [
			{ input: document.getElementById( 'image_format_quality_webp' ), value: document.getElementById( 'frbl-image-quality-webp-value' ) },
			{ input: document.getElementById( 'image_format_quality_avif' ), value: document.getElementById( 'frbl-image-quality-avif-value' ) },
		];

		if ( ! enableCheckbox || ! fieldsWrapper || ! configInput || ! tableContainer || ! configScript ) {
			return;
		}

		let payload;
		try {
			payload = JSON.parse( configScript.textContent );
		} catch ( e ) {
			payload = { sizes: [], disabled: [], overrides: {}, custom: [], usage: {} };
		}

		const state = {
			disabled:  payload.disabled || [],
			overrides: payload.overrides || {},
			custom:    payload.custom || [],
		};

		enableCheckbox.addEventListener( 'change', function () {
			fieldsWrapper.style.display = enableCheckbox.checked ? '' : 'none';
		} );

		if ( maxDimensionToggle && maxDimensionField ) {
			maxDimensionToggle.addEventListener( 'change', function () {
				maxDimensionField.style.display = maxDimensionToggle.checked ? '' : 'none';
			} );
		}

		qualityInputs.forEach( function ( pair ) {
			if ( pair.input && pair.value ) {
				pair.input.addEventListener( 'input', function () {
					pair.value.textContent = pair.input.value;
				} );
			}
		} );

		function escapeHtml( value ) {
			return String( value )
				.replace( /&/g, '&amp;' )
				.replace( /</g, '&lt;' )
				.replace( />/g, '&gt;' )
				.replace( /"/g, '&quot;' )
				.replace( /'/g, '&#039;' );
		}

		function formatBytes( bytes ) {
			if ( ! bytes ) {
				return '—';
			}
			const units = [ 'B', 'KB', 'MB', 'GB' ];
			let value = bytes;
			let unitIndex = 0;
			while ( value >= 1024 && unitIndex < units.length - 1 ) {
				value /= 1024;
				unitIndex++;
			}
			return value.toFixed( 1 ) + ' ' + units[ unitIndex ];
		}

		function syncConfigInput() {
			configInput.value = JSON.stringify( {
				disabled:  state.disabled,
				overrides: state.overrides,
				custom:    state.custom,
			} );
		}

		function renderTable() {
			const rows = payload.sizes.map( function ( size ) {
				const override   = state.overrides[ size.name ] || {};
				const isDisabled = state.disabled.indexOf( size.name ) !== -1;
				const width      = override.width || size.width;
				const height     = override.height || size.height;
				const crop       = override.crop !== undefined ? override.crop : size.crop;
				const usage      = formatBytes( ( payload.usage || {} )[ size.name ] || 0 );

				return (
					'<tr data-size="' + escapeHtml( size.name ) + '">' +
					'<td>' + escapeHtml( size.name ) + '</td>' +
					'<td>' + escapeHtml( size.source ) + '</td>' +
					'<td><input type="number" class="frbl-size-width" min="0" value="' + width + '" style="width:70px" /></td>' +
					'<td><input type="number" class="frbl-size-height" min="0" value="' + height + '" style="width:70px" /></td>' +
					'<td><input type="checkbox" class="frbl-size-crop" ' + ( crop ? 'checked' : '' ) + ' /></td>' +
					'<td>—</td>' +
					'<td>—</td>' +
					'<td>' + usage + '</td>' +
					'<td><input type="checkbox" class="frbl-size-disable" ' + ( isDisabled ? 'checked' : '' ) + ' /></td>' +
					'</tr>'
				);
			} );

			const customRows = state.custom.map( function ( size, index ) {
				return (
					'<tr data-custom-index="' + index + '">' +
					'<td><input type="text" class="frbl-custom-name" value="' + escapeHtml( size.name ) + '" style="width:120px" /></td>' +
					'<td>custom</td>' +
					'<td><input type="number" class="frbl-custom-width" min="0" value="' + size.width + '" style="width:70px" /></td>' +
					'<td><input type="number" class="frbl-custom-height" min="0" value="' + size.height + '" style="width:70px" /></td>' +
					'<td><input type="checkbox" class="frbl-custom-crop" ' + ( size.crop ? 'checked' : '' ) + ' /></td>' +
					'<td><input type="text" class="frbl-custom-label" placeholder="' + escapeHtml( size.name ) + '" value="' + escapeHtml( size.label || '' ) + '" style="width:110px" /></td>' +
					'<td><input type="checkbox" class="frbl-custom-show-in-picker" ' + ( size.show_in_picker ? 'checked' : '' ) + ' /></td>' +
					'<td>—</td>' +
					'<td><button type="button" class="button-link frbl-custom-remove">&times;</button></td>' +
					'</tr>'
				);
			} );

			tableContainer.innerHTML =
				'<table class="widefat striped">' +
				'<thead><tr>' +
				'<th>Name</th><th>Source</th><th>Width</th><th>Height</th><th>Hard crop</th><th>Label</th><th>Show in picker</th><th>Est. size</th><th>Disabled</th>' +
				'</tr></thead>' +
				'<tbody>' + rows.join( '' ) + customRows.join( '' ) + '</tbody>' +
				'</table>' +
				'<p style="margin: 6px 0 0; color: #6b7280; font-size: 12px;">"Show in picker" adds a custom size, under its label, to the image size dropdown shown when inserting or editing an image.</p>' +
				'<button type="button" class="button tw:mt-3" id="frbl-add-custom-size">Add custom size</button>';

			attachRowHandlers();
		}

		function attachRowHandlers() {
			tableContainer.querySelectorAll( 'tr[data-size]' ).forEach( function ( row ) {
				const name = row.getAttribute( 'data-size' );

				row.querySelector( '.frbl-size-disable' ).addEventListener( 'change', function ( e ) {
					state.disabled = state.disabled.filter( function ( n ) {
						return n !== name;
					} );
					if ( e.target.checked ) {
						state.disabled.push( name );
					}
					syncConfigInput();
				} );

				function updateOverride() {
					const width  = parseInt( row.querySelector( '.frbl-size-width' ).value, 10 ) || 0;
					const height = parseInt( row.querySelector( '.frbl-size-height' ).value, 10 ) || 0;
					const crop   = row.querySelector( '.frbl-size-crop' ).checked;
					state.overrides[ name ] = { width: width, height: height, crop: crop };
					syncConfigInput();
				}

				row.querySelector( '.frbl-size-width' ).addEventListener( 'change', updateOverride );
				row.querySelector( '.frbl-size-height' ).addEventListener( 'change', updateOverride );
				row.querySelector( '.frbl-size-crop' ).addEventListener( 'change', updateOverride );
			} );

			tableContainer.querySelectorAll( 'tr[data-custom-index]' ).forEach( function ( row ) {
				const index = parseInt( row.getAttribute( 'data-custom-index' ), 10 );

				function updateCustom() {
					state.custom[ index ] = {
						name:           row.querySelector( '.frbl-custom-name' ).value.trim(),
						width:          parseInt( row.querySelector( '.frbl-custom-width' ).value, 10 ) || 0,
						height:         parseInt( row.querySelector( '.frbl-custom-height' ).value, 10 ) || 0,
						crop:           row.querySelector( '.frbl-custom-crop' ).checked,
						label:          row.querySelector( '.frbl-custom-label' ).value.trim(),
						show_in_picker: row.querySelector( '.frbl-custom-show-in-picker' ).checked,
					};
					syncConfigInput();
				}

				row.querySelector( '.frbl-custom-name' ).addEventListener( 'change', updateCustom );
				row.querySelector( '.frbl-custom-width' ).addEventListener( 'change', updateCustom );
				row.querySelector( '.frbl-custom-height' ).addEventListener( 'change', updateCustom );
				row.querySelector( '.frbl-custom-crop' ).addEventListener( 'change', updateCustom );
				row.querySelector( '.frbl-custom-label' ).addEventListener( 'change', updateCustom );
				row.querySelector( '.frbl-custom-show-in-picker' ).addEventListener( 'change', updateCustom );

				row.querySelector( '.frbl-custom-remove' ).addEventListener( 'click', function () {
					state.custom.splice( index, 1 );
					syncConfigInput();
					renderTable();
				} );
			} );

			const addButton = document.getElementById( 'frbl-add-custom-size' );
			if ( addButton ) {
				addButton.addEventListener( 'click', function () {
					state.custom.push( { name: '', width: 300, height: 300, crop: false, label: '', show_in_picker: false } );
					syncConfigInput();
					renderTable();
				} );
			}
		}

		renderTable();
		syncConfigInput();

		function runBulkAction( action, button ) {
			const progress = document.getElementById( 'frbl-image-bulk-progress' );
			const fill     = progress.querySelector( '.frbl-image-bulk-progress__fill' );
			const label    = progress.querySelector( '.frbl-image-bulk-progress__label' );

			button.disabled = true;
			progress.style.display = '';
			label.textContent = 'Loading media library items…';

			const params = new URLSearchParams();
			params.set( 'action', 'frbl_list_image_attachment_ids' );
			params.set( 'nonce', frblImageManagement.nonce );

			fetch( frblImageManagement.ajaxUrl, { method: 'POST', body: params } )
				.then( function ( response ) {
					return response.json();
				} )
				.then( function ( json ) {
					if ( ! json.success ) {
						throw new Error( 'list failed' );
					}
					return processBatches( action, json.data.ids, fill, label );
				} )
				.then( function ( processed ) {
					label.textContent = frblImageManagement.i18n.done.replace( '%d', processed );
					button.disabled = false;
				} )
				.catch( function () {
					label.textContent = frblImageManagement.i18n.error;
					button.disabled = false;
				} );
		}

		function processBatches( action, ids, fill, label ) {
			let offset = 0;
			const total = ids.length;

			function next() {
				if ( offset >= total ) {
					return Promise.resolve( total );
				}

				const batch  = ids.slice( offset, offset + BATCH_SIZE );
				const params = new URLSearchParams();
				params.set( 'action', action );
				params.set( 'nonce', frblImageManagement.nonce );
				batch.forEach( function ( id ) {
					params.append( 'ids[]', id );
				} );

				return fetch( frblImageManagement.ajaxUrl, { method: 'POST', body: params } )
					.then( function ( response ) {
						return response.json();
					} )
					.then( function () {
						offset += batch.length;
						const pct = total > 0 ? Math.round( ( offset / total ) * 100 ) : 100;
						fill.style.width = pct + '%';
						label.textContent = frblImageManagement.i18n.processing.replace( '%1$d', offset ).replace( '%2$d', total );
						return next();
					} );
			}

			return next();
		}

		const regenerateButton = document.getElementById( 'frbl-bulk-regenerate' );
		const convertButton    = document.getElementById( 'frbl-bulk-convert' );
		const cleanupButton    = document.getElementById( 'frbl-bulk-cleanup' );

		if ( regenerateButton ) {
			regenerateButton.addEventListener( 'click', function () {
				runBulkAction( 'frbl_bulk_regenerate_thumbnails', regenerateButton );
			} );
		}

		if ( convertButton ) {
			convertButton.addEventListener( 'click', function () {
				runBulkAction( 'frbl_bulk_convert_images', convertButton );
			} );
		}

		if ( cleanupButton ) {
			cleanupButton.addEventListener( 'click', function () {
				runBulkAction( 'frbl_bulk_cleanup_disabled_sizes', cleanupButton );
			} );
		}
	} );
} )();
