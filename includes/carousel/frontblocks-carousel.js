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
			const carouselType = item.getAttribute('data-type') ? item.getAttribute('data-type') : 'carousel';
			const carouselbuttons = item.getAttribute('data-buttons') ? item.getAttribute('data-buttons') : 'bullets';
			const carouselView = item.getAttribute('data-view') ? item.getAttribute('data-view') : 4;
			const carouselAutoplay = item.getAttribute('data-autoplay') ? item.getAttribute('data-autoplay') : 0;
			const carouselResView = item.getAttribute('data-res-view') ? item.getAttribute('data-res-view') : 1;
            const carouselRewind = item.getAttribute('data-rewind') ? item.getAttribute('data-rewind') : false;
			const carouselbuttonsColor = item.getAttribute('data-buttons-color') ? item.getAttribute('data-buttons-color') : 'black';
			const carouselbuttonsBackgroundColor = item.getAttribute('data-buttons-background-color') ? item.getAttribute('data-buttons-background-color') : 'transparent';


			// Add classes
			item.classList.add( 'glide__slides', 'glide-' + Math.floor(Math.random() * 1000) );
			for (const child of item.children) {
				child.classList.add('glide__slide');
			}

			// Don't show bullets on responsive and more than 6 items.
            let showBullets = false;
			if ( window.screen.availWidth < 768 && item.children.length < 6 ) {
				showBullets = true;
			} else if ( window.screen.availWidth > 768 ) {
				showBullets = true;
			}

			if ( showBullets && carouselbuttons == 'bullets' ) {
				const bullets = document.createElement('div');
				bullets.classList.add('glide__bullets');
				bullets.setAttribute('data-glide-el', 'controls[nav]');

				for (let i = 0; i < item.children.length; i++) {
					const bullet = document.createElement('button');
					bullet.classList.add('glide__bullet');
					bullet.setAttribute('data-glide-dir', '=' + i);
					bullet.style.backgroundColor = carouselbuttonsBackgroundColor;
					bullets.appendChild(bullet);
				}

				wrapperParent.appendChild(bullets);
			}

			if ( carouselbuttons == 'arrows' ) {
				const arrows = document.createElement('div');
				arrows.classList.add('glide__arrows');
				arrows.setAttribute('data-glide-el', 'controls');
				arrowsHTML = '<button class="glide__arrow glide__arrow--left glide__arrow glide__arrow--left" data-glide-dir="<"';
				arrowsHTML += ' style="background-color: ' + carouselbuttonsBackgroundColor + '"';
				arrowsHTML += '><svg width="7" height="12" viewBox="0 0 7 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 1L1 6L6 11" stroke="';
				arrowsHTML += carouselbuttonsColor;
				arrowsHTML += '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button><button class="glide__arrow glide__arrow--right glide__arrow glide__arrow--right" data-glide-dir=">"';
				
				arrowsHTML += ' style="background-color: ' + carouselbuttonsBackgroundColor + '"';
				arrowsHTML += '><svg width="7" height="12" viewBox="0 0 7 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 11L6 6L1 1" stroke="';
				arrowsHTML += carouselbuttonsColor;
				arrowsHTML += '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>';
				arrows.innerHTML = arrowsHTML;
				wrapperParent.appendChild(arrows);
			}
			new Glide( wrapperParent, {
				type: carouselType,
				perView: carouselView,
				startAt: 0,
				autoplay: carouselAutoplay === 0 ? 2500 : carouselAutoplay,
				gap: 0,
                rewind: carouselRewind,
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