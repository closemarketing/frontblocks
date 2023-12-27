window.addEventListener('load', function (event) {
	const carouselItem = document.querySelectorAll('.frontblocks-carousel');
	
	if (carouselItem.length > 0) {
		carouselItem.forEach( (item) => {
			// First Parent.
			const parent = item.parentNode;
			const wrapper = document.createElement('div');

			parent.replaceChild(wrapper, item);
			wrapper.appendChild(item);

			wrapper.classList.add('glide__track');
			wrapper.setAttribute('data-glide-el', 'track');

			// Second Parent.
			const parentwrap = wrapper.parentNode;
			const wrapperParent = document.createElement('div');

			parentwrap.replaceChild(wrapperParent, wrapper);
			wrapperParent.appendChild(wrapper);
			wrapperParent.classList.add('glide');

			// Options
			carouselType = item.getAttribute('data-type') ? item.getAttribute('data-type') : 'carousel';
			carouselbuttons = item.getAttribute('data-buttons') ? item.getAttribute('data-buttons') : 'bullets';
			carouselView = item.getAttribute('data-view') ? item.getAttribute('data-view') : 4;
			carouselAutoplay = item.getAttribute('data-autoplay') ? item.getAttribute('data-autoplay') : 0;

			// Add classes
			item.classList.add( 'glide__slides', 'glide-' + Math.floor(Math.random() * 1000) );
			for (const child of item.children) {
				child.classList.add('glide__slide');
			}

			if ( carouselbuttons == 'bullets' ) {
				const bullets = document.createElement('div');
				bullets.classList.add('glide__bullets');
				bullets.setAttribute('data-glide-el', 'controls[nav]');

				for (let i = 0; i < item.children.length; i++) {
					const bullet = document.createElement('button');
					bullet.classList.add('glide__bullet');
					bullet.setAttribute('data-glide-dir', '=' + i);
					bullets.appendChild(bullet);
				}

				wrapperParent.appendChild(bullets);
			}

			new Glide( wrapperParent, {
				type: carouselType,
				perView: carouselView,
				startAt: 0,
				autoplay: 2000,
				gap: 0,
				breakpoints: {
					600: {
						perView: 1
					},
					768: {
						perView: 1
					},
				}
			}).mount();
		});
	}
});