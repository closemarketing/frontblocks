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
			carouselResView = item.getAttribute('data-res-view') ? item.getAttribute('data-res-view') : 1;

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

			if ( carouselbuttons == 'arrows' ) {
				const arrows = document.createElement('div');
				arrows.classList.add('glide__arrows');
				arrows.setAttribute('data-glide-el', 'controls');
				arrows.innerHTML = '<button class="glide__arrow glide__arrow--left glide__arrow glide__arrow--left" data-glide-dir="<"><svg width="7" height="12" viewBox="0 0 7 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 1L1 6L6 11" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button><button class="glide__arrow glide__arrow--right glide__arrow glide__arrow--right" data-glide-dir=">"><svg width="7" height="12" viewBox="0 0 7 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 11L6 6L1 1" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>';
				wrapperParent.appendChild(arrows);
			}

			new Glide( wrapperParent, {
				type: carouselType,
				perView: carouselView,
				startAt: 0,
				autoplay: 2000,
				gap: 0,
				breakpoints: {
					430: {
						perView: carouselResView
					},
					600: {
						perView: carouselResView
					},
					768: {
						perView: carouselResView
					},
				}
			}).mount();
		});
	}
});