(function() {
    'use strict';

    // Initialize sticky columns when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initStickyColumns();
    });

    // Also initialize on window load for dynamic content
    window.addEventListener('load', function() {
        initStickyColumns();
    });

    function initStickyColumns() {
        const stickyWrappers = document.querySelectorAll('.frontblocks-sticky-wrapper[data-sticky-enabled="true"]');
        
        stickyWrappers.forEach(function(wrapper) {
            const stickyOffset = parseInt(wrapper.getAttribute('data-sticky-offset')) || 0;
            const stickyColumnIndex = parseInt(wrapper.getAttribute('data-sticky-column-index')) || 0;
            
            const columns = wrapper.querySelectorAll('.gb-grid-column, .wp-block-column');
            
            if (columns.length > stickyColumnIndex) {
                const stickyColumn = columns[stickyColumnIndex];
                setupStickyColumn(stickyColumn, stickyOffset);
            }
        });
    }

    function setupStickyColumn(column, offset) {
        let isSticky = false;
        // GenerateBlocks uses an inner .gb-container; native columns use the column itself.
        const stickyTarget = column.querySelector('.gb-container') || column;

        function checkStickyPosition() {
            const wrapper = column.closest('.frontblocks-sticky-wrapper');
            const wrapperRect = wrapper.getBoundingClientRect();

            // Check if the column should be sticky
            if (wrapperRect.top <= offset && !isSticky) {
                column.classList.add('sticky-active');
                stickyTarget.style.top = offset + 'px';
                isSticky = true;
            } else if (wrapperRect.top > offset && isSticky) {
                column.classList.remove('sticky-active');
                stickyTarget.style.top = '';
                isSticky = false;
            }

            // Check if we've scrolled past the wrapper
            if (wrapperRect.bottom <= offset && isSticky) {
                column.classList.remove('sticky-active');
                stickyTarget.style.top = '';
                isSticky = false;
            }
        }
        
        // Add scroll event listener
        window.addEventListener('scroll', checkStickyPosition, { passive: true });
        
        // Initial check
        checkStickyPosition();
        
        // Check on resize
        window.addEventListener('resize', checkStickyPosition, { passive: true });
    }

    // Re-initialize when new content is loaded (for AJAX content)
    if (typeof wp !== 'undefined' && wp.hooks) {
        wp.hooks.addAction('frontblocks/sticky-columns-reinit', 'frontblocks/sticky-columns', initStickyColumns);
    }

})(); 