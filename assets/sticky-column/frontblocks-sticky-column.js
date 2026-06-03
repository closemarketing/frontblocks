(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        initStickyColumns();
    });

    window.addEventListener('load', function() {
        initStickyColumns();
    });

    function initStickyColumns() {
        const stickyWrappers = document.querySelectorAll('.frontblocks-sticky-wrapper[data-sticky-enabled="true"]');

        stickyWrappers.forEach(function(wrapper) {
            const stickyOffset    = parseInt(wrapper.getAttribute('data-sticky-offset')) || 0;
            const stickyColumnIndex = parseInt(wrapper.getAttribute('data-sticky-column-index')) || 0;

            // Only direct children to avoid matching nested blocks.
            const columns = wrapper.querySelectorAll(':scope > .gb-grid-column, :scope > .wp-block-column');

            if (columns.length <= stickyColumnIndex) {
                return;
            }

            const column = columns[stickyColumnIndex];

            // For GenerateBlocks, apply sticky to the inner .gb-container (it's not the flex item).
            // For native wp-block-column, create an inner wrapper — applying sticky directly to
            // a flex item is unreliable across browsers; sticky works best on a descendant of the flex item.
            let stickyTarget = column.querySelector('.gb-container');

            if (!stickyTarget) {
                stickyTarget = document.createElement('div');
                stickyTarget.className = 'frontblocks-sticky-inner';
                while (column.firstChild) {
                    stickyTarget.appendChild(column.firstChild);
                }
                column.appendChild(stickyTarget);
            }

            // Apply sticky via inline styles — avoids specificity wars with WP-generated .wp-container-* rules.
            stickyTarget.style.position = 'sticky';
            stickyTarget.style.top      = stickyOffset + 'px';
            stickyTarget.style.zIndex   = '100';

            // flex-start needed so the column has natural height; without it position:sticky has no room to scroll.
            if (wrapper.classList.contains('wp-block-columns')) {
                wrapper.style.alignItems = 'flex-start';
            }
        });
    }

    if (typeof wp !== 'undefined' && wp.hooks) {
        wp.hooks.addAction('frontblocks/sticky-columns-reinit', 'frontblocks/sticky-columns', initStickyColumns);
    }

})(); 