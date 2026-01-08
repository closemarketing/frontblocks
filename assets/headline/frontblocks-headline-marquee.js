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

            // Marquee speed presets mapping
            const MARQUEE_SPEEDS = {
                'fast': 10,    // 10 seconds - fast
                'medium': 20,  // 20 seconds - medium
                'slow': 40     // 40 seconds - slow
            };
            
            // Function to get marquee speed from element or parent - VERY AGGRESSIVE SEARCH
            const getMarqueeSpeed = function(el) {
                let speed = null;
                
                // Method 1: Check dataset
                if (el.dataset && el.dataset.marqueeSpeed) {
                    speed = el.dataset.marqueeSpeed;
                }
                
                // Method 2: Check getAttribute
                if (!speed) {
                    speed = el.getAttribute('data-marquee-speed');
                }
                
                // Method 3: Check all attributes manually
                if (!speed && el.attributes) {
                    try {
                        Array.from(el.attributes).forEach(function(attr) {
                            if (attr.name === 'data-marquee-speed' && attr.value) {
                                speed = attr.value;
                            }
                        });
                    } catch(e) {}
                }
                
                // Method 4: Check parent element
                if (!speed && el.parentElement) {
                    if (el.parentElement.dataset && el.parentElement.dataset.marqueeSpeed) {
                        speed = el.parentElement.dataset.marqueeSpeed;
                    }
                    if (!speed) {
                        speed = el.parentElement.getAttribute('data-marquee-speed');
                    }
                }
                
                // Method 5: Search in all ancestors
                if (!speed) {
                    let parent = el.parentElement;
                    while (parent && parent !== document.body) {
                        if (parent.dataset && parent.dataset.marqueeSpeed) {
                            speed = parent.dataset.marqueeSpeed;
                            break;
                        }
                        if (parent.getAttribute && parent.getAttribute('data-marquee-speed')) {
                            speed = parent.getAttribute('data-marquee-speed');
                            break;
                        }
                        parent = parent.parentElement;
                    }
                }
                
                // Method 6: Search in children
                if (!speed) {
                    const speedElement = el.querySelector('[data-marquee-speed]');
                    if (speedElement) {
                        speed = speedElement.dataset.marqueeSpeed || speedElement.getAttribute('data-marquee-speed');
                    }
                }
                
                // Parse and validate - handle both numeric and preset values
                let speedValue = 20; // default
                
                if (speed) {
                    // Check if it's a preset string (fast, medium, slow)
                    if (MARQUEE_SPEEDS.hasOwnProperty(speed)) {
                        speedValue = MARQUEE_SPEEDS[speed];
                    } else {
                        // Try to parse as number
                        const parsed = parseFloat(speed);
                        if (!isNaN(parsed) && parsed > 0) {
                            speedValue = parsed;
                        }
                    }
                }
                
                return speedValue;
            };
            
            // Get initial speed value
            const speedValue = getMarqueeSpeed(element);

            // Function to calculate and setup marquee
            const setupMarquee = function() {
                // Create a temporary element to measure text width with same font styles
                const tempElement = document.createElement('span');
                tempElement.style.cssText = 'position: absolute; visibility: hidden; white-space: nowrap; padding-right: 2em;';
                tempElement.innerHTML = textContent;
                
                // Copy computed styles from original element if possible
                if (textElement !== element) {
                    const computedStyle = window.getComputedStyle(textElement);
                    tempElement.style.fontSize = computedStyle.fontSize;
                    tempElement.style.fontFamily = computedStyle.fontFamily;
                    tempElement.style.fontWeight = computedStyle.fontWeight;
                    tempElement.style.letterSpacing = computedStyle.letterSpacing;
                }
                
                document.body.appendChild(tempElement);
                
                // Get container width - ensure we have the actual width
                let containerWidth = element.offsetWidth || element.clientWidth;
                if (!containerWidth || containerWidth === 0) {
                    // Try to get from computed style
                    const computedWidth = window.getComputedStyle(element).width;
                    containerWidth = parseFloat(computedWidth) || 0;
                }
                
                // Fallback to parent width if still 0
                if (!containerWidth || containerWidth === 0) {
                    containerWidth = element.parentElement ? (element.parentElement.offsetWidth || 0) : 0;
                }
                
                // Get single text copy width
                const singleTextWidth = tempElement.offsetWidth;
                
                // Remove temporary element
                document.body.removeChild(tempElement);
                
                // Calculate how many copies we need to fill the container width
                // We need at least 2 copies for seamless loop, but more if text is short
                let copiesNeeded = 2;
                if (containerWidth > 0 && singleTextWidth > 0) {
                    // Calculate copies needed to fill container width
                    // Add 2 extra copies to ensure smooth continuous scrolling
                    copiesNeeded = Math.ceil((containerWidth / singleTextWidth) + 2);
                    // Ensure minimum of 2 copies for seamless loop
                    copiesNeeded = Math.max(copiesNeeded, 2);
                }

                // Create a wrapper for the marquee content
                const wrapper = document.createElement('div');
                wrapper.className = 'gb-marquee-wrapper';
                // Store speed directly in wrapper as data attribute AND CSS variable
                wrapper.setAttribute('data-marquee-speed', speedValue);
                wrapper.style.setProperty('--marquee-speed', speedValue + 's');
                wrapper.style.cssText += 'display: flex; white-space: nowrap; will-change: transform; backface-visibility: hidden; -webkit-backface-visibility: hidden; transform: translateZ(0); -webkit-transform: translateZ(0); width: auto; min-width: 100%;';

                // Create copies based on calculated need
                for (let i = 0; i < copiesNeeded; i++) {
                    const copy = document.createElement('span');
                    copy.className = 'gb-marquee-copy';
                    copy.innerHTML = textContent;
                    copy.style.cssText = 'display: inline-block; padding-right: 2em; flex-shrink: 0; backface-visibility: hidden; -webkit-backface-visibility: hidden;';
                    wrapper.appendChild(copy);
                }
                
                return wrapper;
            };
            
            // Setup marquee - use requestAnimationFrame to ensure layout is ready
            requestAnimationFrame(function() {
                const wrapper = setupMarquee();

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

                // Function to update speed - can be called from outside
                const updateSpeed = function(newSpeed) {
                    if (newSpeed && !isNaN(newSpeed) && newSpeed > 0) {
                        wrapper.setAttribute('data-marquee-speed', newSpeed);
                        wrapper.style.setProperty('--marquee-speed', newSpeed + 's');
                        applyAnimation();
                    }
                };
                
                // Function to apply animation - reads speed from wrapper
                const applyAnimation = function() {
                    // Get speed from wrapper (most reliable source)
                    let currentSpeed = wrapper.getAttribute('data-marquee-speed');
                    
                    // If not in wrapper, try to get from element and save to wrapper
                    if (!currentSpeed) {
                        currentSpeed = getMarqueeSpeed(element);
                        if (currentSpeed) {
                            wrapper.setAttribute('data-marquee-speed', currentSpeed);
                            wrapper.style.setProperty('--marquee-speed', currentSpeed + 's');
                        }
                    } else {
                        currentSpeed = parseFloat(currentSpeed);
                    }
                    
                    // Validate speed
                    if (!currentSpeed || isNaN(currentSpeed) || currentSpeed <= 0) {
                        currentSpeed = 20;
                        wrapper.setAttribute('data-marquee-speed', currentSpeed);
                        wrapper.style.setProperty('--marquee-speed', currentSpeed + 's');
                    }
                    
                    // Get the actual width of one copy after it's rendered
                    const firstCopy = wrapper.querySelector('.gb-marquee-copy');
                    if (!firstCopy) return;
                    
                    const copyWidth = firstCopy.offsetWidth;
                    
                    if (copyWidth > 0) {
                        // Calculate animation distance in pixels (exactly one copy width)
                        // This ensures perfect seamless loop - no jumps or resets
                        const animationDistancePx = copyWidth;
                        
                        // Get or create style ID
                        let styleId = wrapper.getAttribute('data-marquee-style-id');
                        if (!styleId) {
                            styleId = 'marquee-style-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
                            wrapper.setAttribute('data-marquee-style-id', styleId);
                        }
                        
                        // Check if style already exists, if not create it
                        let style = document.getElementById(styleId);
                        if (!style) {
                            style = document.createElement('style');
                            style.id = styleId;
                            document.head.appendChild(style);
                        }
                        
                        // Update keyframes
                        style.textContent = '@keyframes marquee-scroll-' + styleId + ' { 0% { transform: translateX(0) translateZ(0); } 100% { transform: translateX(-' + animationDistancePx + 'px) translateZ(0); } }';
                        
                        // FORCE UPDATE - Remove animation completely
                        wrapper.style.animation = 'none';
                        wrapper.style.animationName = 'none';
                        wrapper.style.animationDuration = 'none';
                        
                        // Force reflow
                        void wrapper.offsetWidth;
                        
                        // Re-apply animation with new speed
                        requestAnimationFrame(function() {
                            // Use CSS variable for speed
                            wrapper.style.setProperty('--marquee-speed', currentSpeed + 's');
                            wrapper.style.animation = 'marquee-scroll-' + styleId + ' var(--marquee-speed, ' + currentSpeed + 's) linear infinite';
                            wrapper.style.animationName = 'marquee-scroll-' + styleId;
                            wrapper.style.animationDuration = 'var(--marquee-speed, ' + currentSpeed + 's)';
                            wrapper.style.animationTimingFunction = 'linear';
                            wrapper.style.animationIterationCount = 'infinite';
                            wrapper.style.animationFillMode = 'none';
                            wrapper.style.animationPlayState = 'running';
                            
                            // Force another reflow to ensure it applies
                            void wrapper.offsetWidth;
                        });
                    }
                };

                // Wait for layout to calculate actual copy width after wrapper is in DOM
                requestAnimationFrame(function() {
                    applyAnimation();
                });

                // Watch for changes in data-marquee-speed attribute - VERY AGGRESSIVE
                if (typeof MutationObserver !== 'undefined') {
                    const speedObserver = new MutationObserver(function(mutations) {
                        let shouldUpdate = false;
                        mutations.forEach(function(mutation) {
                            // Check for attribute changes on element or wrapper
                            if (mutation.type === 'attributes') {
                                if (mutation.attributeName === 'data-marquee-speed') {
                                    // Update wrapper if element changed
                                    if (mutation.target === element || mutation.target === element.parentElement) {
                                        const newSpeed = getMarqueeSpeed(element);
                                        wrapper.setAttribute('data-marquee-speed', newSpeed);
                                        shouldUpdate = true;
                                    }
                                    // Update if wrapper changed
                                    if (mutation.target === wrapper) {
                                        shouldUpdate = true;
                                    }
                                }
                            }
                            // Also check for child changes (in case element is re-rendered)
                            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                                shouldUpdate = true;
                            }
                        });
                        
                        if (shouldUpdate) {
                            // Get new speed and update
                            const newSpeed = getMarqueeSpeed(element);
                            updateSpeed(newSpeed);
                        }
                    });

                    // Observe wrapper for speed changes
                    speedObserver.observe(wrapper, {
                        attributes: true,
                        attributeFilter: ['data-marquee-speed']
                    });

                    // Observe element for speed changes
                    speedObserver.observe(element, {
                        attributes: true,
                        attributeFilter: ['data-marquee-speed', 'class'],
                        childList: true,
                        subtree: false
                    });
                    
                    // Also observe parent element
                    if (element.parentElement) {
                        speedObserver.observe(element.parentElement, {
                            attributes: true,
                            attributeFilter: ['data-marquee-speed']
                        });
                    }
                    
                    // Store observer reference for cleanup if needed
                    element.setAttribute('data-marquee-observer', 'active');
                }
                
                // Expose updateSpeed function globally on wrapper for external access
                wrapper.updateMarqueeSpeed = updateSpeed;
                
                // AGGRESSIVE periodic check - check very frequently
                // Read speed from element and update wrapper if changed
                let lastKnownSpeed = speedValue;
                const speedCheckInterval = setInterval(function() {
                    // Only check if element is still in DOM
                    if (!document.body.contains(element)) {
                        clearInterval(speedCheckInterval);
                        return;
                    }
                    
                    // Always get fresh speed value from element
                    const currentSpeed = getMarqueeSpeed(element);
                    const wrapperSpeed = parseFloat(wrapper.getAttribute('data-marquee-speed')) || 0;
                    
                    // If speed from element is different from wrapper, update
                    if (Math.abs(currentSpeed - wrapperSpeed) > 0.01) {
                        updateSpeed(currentSpeed);
                    }
                }, 100); // Check every 100ms - EXTREMELY AGGRESSIVE
                
                // Store interval ID for potential cleanup
                wrapper.setAttribute('data-speed-check-interval', 'active');

                // Mark as initialized
                element.dataset.marqueeInitialized = 'true';
            });
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

