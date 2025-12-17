/**
 * FrontBlocks Headline Marquee Effect
 * Duplicates content for infinite scrolling marquee effect
 */

(function() {
    'use strict';

    /**
     * Initialize marquee effect for headlines
     */
    function initMarquee() {
        const marqueeElements = document.querySelectorAll('.gb-marquee-infinite-scroll:not([data-marquee-initialized="true"])');
        
        marqueeElements.forEach(function(element) {
            // Skip if already initialized or has wrapper
            if (element.dataset.marqueeInitialized === 'true' || element.querySelector('.gb-marquee-wrapper')) {
                return;
            }

            // Find the text content - prioritize .gb-headline-text
            let textElement = element.querySelector('.gb-headline-text');
            
            // If no .gb-headline-text, look for direct text content or first text node
            if (!textElement) {
                // Check if element has direct text content
                const hasDirectText = element.childNodes.length > 0 && 
                    Array.from(element.childNodes).some(node => 
                        node.nodeType === 3 && node.textContent.trim() !== ''
                    );
                
                if (hasDirectText) {
                    textElement = element;
                } else {
                    // Try to find a span or other inline element
                    textElement = element.querySelector('span, a, strong, em, b, i') || element;
                }
            }

            if (!textElement) {
                return;
            }

            // Get the HTML content (preserves formatting)
            const textContent = textElement.innerHTML || textElement.textContent;
            
            if (!textContent || textContent.trim() === '') {
                return;
            }

            // Create a wrapper for the marquee content
            const wrapper = document.createElement('div');
            wrapper.className = 'gb-marquee-wrapper';
            wrapper.style.cssText = 'display: inline-flex; white-space: nowrap;';

            // Create first copy
            const copy1 = document.createElement('span');
            copy1.className = 'gb-marquee-copy';
            copy1.innerHTML = textContent;
            copy1.style.cssText = 'display: inline-block; padding-right: 2em;';

            // Create second copy for seamless loop
            const copy2 = document.createElement('span');
            copy2.className = 'gb-marquee-copy';
            copy2.innerHTML = textContent;
            copy2.style.cssText = 'display: inline-block; padding-right: 2em;';

            // Add copies to wrapper
            wrapper.appendChild(copy1);
            wrapper.appendChild(copy2);

            // Replace content with wrapper
            if (textElement === element) {
                // Save any attributes that might be on the element
                const savedAttributes = {};
                Array.from(element.attributes).forEach(function(attr) {
                    if (attr.name !== 'class' && attr.name !== 'data-marquee-initialized') {
                        savedAttributes[attr.name] = attr.value;
                    }
                });
                
                element.innerHTML = '';
                element.appendChild(wrapper);
                
                // Restore attributes
                Object.keys(savedAttributes).forEach(function(key) {
                    element.setAttribute(key, savedAttributes[key]);
                });
            } else {
                textElement.innerHTML = '';
                textElement.appendChild(wrapper);
            }

            // Mark as initialized
            element.dataset.marqueeInitialized = 'true';
        });
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMarquee);
    } else {
        initMarquee();
    }

    // Re-initialize for dynamically loaded content (e.g., AJAX)
    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver(function(mutations) {
            let shouldReinit = false;
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length > 0) {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1) { // Element node
                            if (node.classList && node.classList.contains('gb-marquee-infinite-scroll')) {
                                shouldReinit = true;
                            } else if (node.querySelector && node.querySelector('.gb-marquee-infinite-scroll')) {
                                shouldReinit = true;
                            }
                        }
                    });
                }
            });
            if (shouldReinit) {
                initMarquee();
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
})();

