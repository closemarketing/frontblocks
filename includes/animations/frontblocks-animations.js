(function($)

{
	const scrollElements  = document.querySelectorAll(".js-scroll");
	const scrollElements2 = document.querySelectorAll(".js-scroll-2");
	const scrollElements3 = document.querySelectorAll(".js-scroll-3");

	const elementInView = (el, dividend = 1) => {
		const elementTop = el.getBoundingClientRect().top;

		return (
			elementTop <=
			(window.innerHeight || document.documentElement.clientHeight) / dividend
		);
	};

	const elementOutofView = (el) => {
		const elementTop = el.getBoundingClientRect().top;

		return (
			elementTop > (window.innerHeight || document.documentElement.clientHeight)
		);
	};

	const displayScrollElement = (element) => {
		element.classList.add("scrolled");
	};

	const handleScrollAnimation = () => {
		scrollElements.forEach((el) => {
			if (elementInView(el, 1.25)) {
				displayScrollElement(el);
			} else if (elementOutofView(el)) {
				hideScrollElement(el)
			}
		})
	}

	const handleScrollAnimation2 = () => {
		scrollElements2.forEach((el) => {
			if (elementInView(el, 1.45)) {
				displayScrollElement(el);
			} else if (elementOutofView(el)) {
				hideScrollElement(el)
			}
		})
	}

	const handleScrollAnimation3 = () => {
		scrollElements3.forEach((el) => {
			if (elementInView(el, 1.65)) {
				displayScrollElement(el);
			} else if (elementOutofView(el)) {
				hideScrollElement(el)
			}
		})
	}

	window.addEventListener("scroll", () => { 
		handleScrollAnimation();
		handleScrollAnimation2();
		handleScrollAnimation3();
	});

})( jQuery );

document.querySelectorAll('a[href^="#"]').forEach(anchor => {
	anchor.addEventListener('click', function (e) {
	    e.preventDefault();
		
	    document.querySelector(this.getAttribute('href')).scrollIntoView({
		  behavior: 'smooth'
	    });
	});
});