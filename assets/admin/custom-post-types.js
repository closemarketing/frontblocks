/**
 * Custom Post Types Admin Script
 *
 * @package    FrontBlocks
 * @author     Closemarketing
 * @copyright  2025 Closemarketing
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		const cptToggle = $('#enable_custom_post_types');
		const cptBuilder = $('#frbl-cpt-builder');
		const createBtn = $('#frbl-create-cpt-btn');
		const cptNameInput = $('#frbl-cpt-name');

		// Toggle visibility of CPT builder.
		if (cptToggle.length) {
			cptToggle.on('change', function() {
				if ($(this).is(':checked')) {
					cptBuilder.slideDown(300);
				} else {
					cptBuilder.slideUp(300);
				}
			});

			// Initial state.
			if (cptToggle.is(':checked')) {
				cptBuilder.show();
			}
		}

		// Handle create button click.
		if (createBtn.length && typeof frontblocksCpt !== 'undefined') {
			createBtn.on('click', function(e) {
				e.preventDefault();

				const cptName = cptNameInput.val().trim();

				if (!cptName) {
					alert(frontblocksCpt.i18n.error || 'Please enter a post type name.');
					return;
				}

				// Disable button and show loading state.
				const originalText = createBtn.text();
				createBtn.prop('disabled', true).text(frontblocksCpt.i18n.creating || 'Creating...');

				// AJAX request.
				$.ajax({
					url: frontblocksCpt.ajaxUrl,
					type: 'POST',
					data: {
						action: 'frontblocks_create_cpt',
						nonce: frontblocksCpt.nonce,
						cpt_name: cptName,
					},
					success: function(response) {
						if (response.success) {
							// Show success message.
							alert(response.data.message || frontblocksCpt.i18n.success);

							// Clear input.
							cptNameInput.val('');

							// Reload page to show new CPT in list.
							window.location.reload();
						} else {
							alert(response.data.message || frontblocksCpt.i18n.error);
							createBtn.prop('disabled', false).text(originalText);
						}
					},
					error: function() {
						alert(frontblocksCpt.i18n.error || 'An error occurred. Please try again.');
						createBtn.prop('disabled', false).text(originalText);
					},
				});
			});

			// Allow Enter key to trigger create.
			cptNameInput.on('keypress', function(e) {
				if (e.which === 13) {
					e.preventDefault();
					createBtn.trigger('click');
				}
			});
		}

		// Handle delete button click.
		$(document).on('click', '.frontblocks-delete-cpt', function(e) {
			e.preventDefault();

			const button = $(this);
			const cptKey = button.data('cpt-key');
			const cptName = button.data('cpt-name') || cptKey;

			if (!cptKey) {
				return;
			}

			// Confirm deletion.
			if (!confirm('¿Estás seguro de que quieres eliminar el post type "' + cptName + '"? Esta acción no se puede deshacer.')) {
				return;
			}

			// Disable button and show loading state.
			const originalText = button.text();
			button.prop('disabled', true).text('Eliminando...');

			// AJAX request.
			$.ajax({
				url: frontblocksCpt.ajaxUrl,
				type: 'POST',
				data: {
					action: 'frontblocks_delete_cpt',
					nonce: frontblocksCpt.nonce,
					cpt_key: cptKey,
				},
				success: function(response) {
					if (response.success) {
						// Remove the CPT row from the list.
						button.closest('[data-cpt-key="' + cptKey + '"]').fadeOut(300, function() {
							$(this).remove();
						});

						// Show success message.
						alert(response.data.message || 'Post type eliminado correctamente.');
					} else {
						alert(response.data.message || 'Error al eliminar el post type.');
						button.prop('disabled', false).text(originalText);
					}
				},
				error: function() {
					alert('Ocurrió un error. Por favor, intenta de nuevo.');
					button.prop('disabled', false).text(originalText);
				},
			});
		});
	});
})(jQuery);

