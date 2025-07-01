window.addEventListener('load', (event) => {
    /**
     * onIntersection will be triggered each time an observed item is visible in the viewport.
     * 
     * @param {elements} The list of queried elements  
     * @param {*} Defines the option parameters for the observer API 
     */
    const onIntersection = (elements, options) => {
        elements.forEach((element) => {
            // Check for both old and new animation attribute formats
            let animationData = element.target.dataset.frontblocks_animation;
            const hasModernFormat = element.target.classList.contains('animate__animated');
            
            if (animationData) {
                // Handle legacy format
                // Check if the data attribute doesn't include the prefix, and add it otherwise.
                if (!animationData.includes('animate__')) {
                    animationData = 'animate__'.concat(animationData);
                }

                // Add the CSS class if the element is visible.
                if (element.isIntersecting) {
                    element.target.classList.add('animate__animated', animationData);
                }
            } else if (hasModernFormat && element.isIntersecting) {
                // For modern format (added by block attributes)
                // The classes are already there from the block rendering
                // Just trigger animation by ensuring animation-play-state is running
                element.target.style.visibility = 'visible';
                element.target.style.animationPlayState = 'running';
                
                // If this element should repeat, set up the animation to repeat
                // Check multiple possible sources for repeat value
                const repeatValue = element.target.style.getPropertyValue('--animate-repeat') || 
                                   element.target.dataset.animateRepeat ||
                                   element.target.getAttribute('data-animate-repeat');
                
                if (repeatValue === 'infinite') {
                    // For infinite animations, set the iteration count to infinite
                    element.target.style.animationIterationCount = 'infinite';
                } else if (repeatValue && repeatValue !== '1') {
                    // For numbered repeats, we handle it
                    const repeat = parseInt(repeatValue);
                    if (!isNaN(repeat) && repeat > 1) {
                        // Only add the event listener if it hasn't been added before
                        if (!element.target.dataset.repeatListenerAdded) {
                            element.target.dataset.repeatListenerAdded = 'true';
                            element.target.dataset.currentRepeat = '0';
                            
                            element.target.addEventListener('animationend', () => {
                                let currentRepeat = parseInt(element.target.dataset.currentRepeat || 0);
                                if (currentRepeat < repeat - 1) {
                                    element.target.dataset.currentRepeat = currentRepeat + 1;
                                    // Trigger animation again by restarting it
                                    element.target.style.animation = 'none';
                                    setTimeout(() => {
                                        element.target.style.animation = '';
                                        element.target.style.animationPlayState = 'running';
                                    }, 10);
                                }
                            });
                        }
                    }
                }
            }
        });
    }

    /**
     * Declares an instance of the IntersectionObserver API, which receives a function that will execute each time an observed
     * item is present in the viewport.
     */
    const observer = new IntersectionObserver(onIntersection, {
        root: null, 
        threshold: 0.2, // Lower threshold to start animation sooner
        rootMargin: '0px 0px -10% 0px' // Trigger slightly before element is fully in view
    });

    /**
     * Gather a list of all the DOM elements which contain animations.
     * This includes both legacy data-attribute animations and new class-based animations.
     * Then, observe the presence of each one in the viewport.
     */
    const animatedElements = document.querySelectorAll('[data-frontblocks_animation], .animate__animated');
    
    // For modern animated elements, initially hide them until they're in view
    document.querySelectorAll('.animate__animated:not([data-frontblocks_animation])').forEach(el => {
        el.style.visibility = 'hidden';
        el.style.animationPlayState = 'paused';
    });
    
    // Observe all animated elements
    animatedElements.forEach((element) => {
        observer.observe(element);
    });
});