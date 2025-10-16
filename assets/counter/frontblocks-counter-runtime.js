const COUNTER_CLASS = 'frontblocks-counter-active'; 

document.addEventListener('DOMContentLoaded', function() {
    const counterElements = document.querySelectorAll(`.${COUNTER_CLASS}`);
    
    if (counterElements.length === 0) return;

    const runCounterAnimation = (element) => {
        if (element.classList.contains('count-up-animated')) {
            return;
        }
        
        const originalText = element.getAttribute('data-counter-target'); 
        if (!originalText) return; 

        const animationDuration = parseInt(element.getAttribute('data-counter-duration'), 10) || 2000;
        const customPrefix = element.getAttribute('data-counter-prefix') || '';
        const customSuffix = element.getAttribute('data-counter-suffix') || '';

        element.setAttribute('data-original-text', originalText);
        
        const prefix = customPrefix || '';
        
        let numberString = originalText;
        
        const numberMatch = numberString.match(/[\d\.,]+/);
        if (!numberMatch) return; 

        let targetString = numberMatch[0].replace(/[^0-9]/g, ''); 
        const target = parseInt(targetString, 10);
        
        if (isNaN(target) || target === 0) return;

        let current = 0;
        const interval = 10;
        const steps = animationDuration / interval;
        const stepValue = target / steps;

        const timer = setInterval(() => {
            current += stepValue;

            if (current >= target) {
                current = target;
                clearInterval(timer);
                element.classList.add('count-up-animated'); 
            }
            
            element.textContent = prefix + Math.floor(current).toLocaleString('en-US') + customSuffix;
        }, interval);
    };

    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.5 
    };

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const element = entry.target;
                
                if (!element.classList.contains('count-up-animated')) {
                    runCounterAnimation(element);
                }
                
                observer.unobserve(element);
            }
        });
    }, observerOptions);

    counterElements.forEach(counter => {
        const fullTargetText = counter.getAttribute('data-counter-target');
        if (!fullTargetText) return;

        const customPrefix = counter.getAttribute('data-counter-prefix') || '';
        const customSuffix = counter.getAttribute('data-counter-suffix') || '';
        
        counter.textContent = customPrefix + '0' + customSuffix;
        
        observer.observe(counter);
    });
});