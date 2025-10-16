document.addEventListener('DOMContentLoaded', function() {
    // Apunta al elemento de texto dentro del headline que tiene la clase
    const counterElements = document.querySelectorAll('.gb-headline-text.count-up-container');
    
    if (counterElements.length === 0) return;

    const animationDuration = 2000; 

    const runCounterAnimation = (element) => {
        // Asegúrate de guardar el valor original antes de empezar la animación
        const originalText = element.getAttribute('data-original-text') || element.textContent.trim();
        element.setAttribute('data-original-text', originalText);
        
        // 1. Extrae el prefijo (ej: '+', '€', 'Hasta ')
        const prefixMatch = originalText.match(/^(\D*)/);
        const prefix = prefixMatch ? prefixMatch[1] : '';
        
        // 2. Extrae el número objetivo
        // Reemplaza el prefijo y los separadores de miles/decimales para obtener un número limpio.
        let numberString = originalText.replace(prefix, '').replace(/[.,]/g, ''); 
        const target = parseInt(numberString, 10);
        
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
            }
            
            // Usamos 'es-ES' para asegurar que el separador de miles sea el punto (1.234)
            element.textContent = prefix + Math.floor(current).toLocaleString('es-ES');
        }, interval);
    };

    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.5 // Se dispara cuando el 50% del elemento es visible
    };

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const element = entry.target;
                
                // Previene la animación si ya se ejecutó
                if (!element.classList.contains('count-up-animated')) {
                    runCounterAnimation(element);
                    element.classList.add('count-up-animated'); 
                }
                
                // Deja de observar el elemento una vez animado
                observer.unobserve(element);
            }
        });
    }, observerOptions);

    counterElements.forEach(counter => {
        // Inicializa el elemento al valor 0 antes de observar, para que el efecto sea más notorio
        const fullText = counter.textContent.trim();
        const prefixMatch = fullText.match(/^(\D*)/);
        const prefix = prefixMatch ? prefixMatch[1] : '';
        counter.textContent = prefix + '0';
        
        observer.observe(counter);
    });
});
