window.addEventListener('load', (event) => {
	/**
	 * onIntersection will be triggered each time an observed item is visible in the viewport.
	 * 
	 * @param {elements} The list of queried elements  
	 * @param {*} Defines the option parameters for the observer API 
	 */
	const onIntersection = (elements, options) => {
		elements.forEach((element) => {
			let animationData = element.target.dataset.frontblocks_animation;

			// Check if the data attribute doesn't include the prefix, and add it otherwise.
			if (!animationData.includes('animate__')) {
				animationData = 'animate__'.concat(animationData);
			}

			// Add the CSS class if the element is visible.
			if (element.isIntersecting) {
				element.target.classList.add('animate__animated', animationData);
			}
		});
	}

	/**
	 * Declares an instance of the IntersectionObserver API, which receives a function that will execute each time an observed
	 * item is present in the viewport.
	 */
	const observer = new IntersectionObserver(onIntersection, {
		root: null, 
		threshold: 0.5,
	});

	/**
	 * Gather a list of all the DOM elements which contain the 'data-frontblocks-animation' attribute.
	 * Then, observe the presence of each one in the viewport.
	 */
	const animatedElements = document.querySelectorAll('[data-frontblocks_animation]');
	animatedElements.forEach((element) => {
		observer.observe(element);
	});
});