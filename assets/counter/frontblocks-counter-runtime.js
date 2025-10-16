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

        element.setAttribute('data-original-text', originalText);
        
        const prefixMatch = originalText.match(/^(\D*)/);
        const prefix = prefixMatch ? prefixMatch[1] : '';
        
        let numberString = originalText.replace(prefix, ''); 
        
        const numberMatch = numberString.match(/[\d\.,]+/);
        if (!numberMatch) return; 

        let targetString = numberMatch[0].replace(/[^0-9]/g, ''); 
        const target = parseInt(targetString, 10);
        
        if (isNaN(target) || target === 0) return;

        let current = 0;
        const interval = 10;
        const steps = animationDuration / interval; // Usa la duración configurable
        const stepValue = target / steps;

        const timer = setInterval(() => {
            current += stepValue;

            if (current >= target) {
                current = target;
                clearInterval(timer);
                element.classList.add('count-up-animated'); 
            }
            
            element.textContent = prefix + Math.floor(current).toLocaleString('es-ES');
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

        const prefixMatch = fullTargetText.match(/^(\D*)/);
        const prefix = prefixMatch ? prefixMatch[1] : '';
        
        counter.textContent = prefix + '0';
        
        observer.observe(counter);
    });
});