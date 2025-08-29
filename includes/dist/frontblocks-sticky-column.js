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
            
            const columns = wrapper.querySelectorAll('.gb-grid-column');
            
            if (columns.length > stickyColumnIndex) {
                const stickyColumn = columns[stickyColumnIndex];
                setupStickyColumn(stickyColumn, stickyOffset);
            }
        });
    }

    function setupStickyColumn(column, offset) {
        let isSticky = false;
        
        function checkStickyPosition() {
            const rect = column.getBoundingClientRect();
            const wrapper = column.closest('.frontblocks-sticky-wrapper');
            const wrapperRect = wrapper.getBoundingClientRect();
            
            // Check if the column should be sticky
            if (wrapperRect.top <= offset && !isSticky) {
                column.classList.add('sticky-active');
                // Apply offset to the gb-container within the sticky column
                const container = column.querySelector('.gb-container');
                if (container) {
                    container.style.top = offset + 'px';
                }
                isSticky = true;
            } else if (wrapperRect.top > offset && isSticky) {
                column.classList.remove('sticky-active');
                // Remove offset from the gb-container
                const container = column.querySelector('.gb-container');
                if (container) {
                    container.style.top = '';
                }
                isSticky = false;
            }
            
            // Check if we've scrolled past the wrapper
            if (wrapperRect.bottom <= offset && isSticky) {
                column.classList.remove('sticky-active');
                // Remove offset from the gb-container
                const container = column.querySelector('.gb-container');
                if (container) {
                    container.style.top = '';
                }
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
        wp.hooks.addAction('frontblocks_sticky_columns_reinit', 'frontblocks_sticky_columns', initStickyColumns);
    }

})(); 