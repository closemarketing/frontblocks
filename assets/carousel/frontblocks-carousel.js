window.addEventListener('load', function (event) {
    const carouselItem = document.querySelectorAll('.frontblocks-carousel');

    if (carouselItem.length > 0) {
        carouselItem.forEach((item) => {
            // Check if carousel should be disabled on desktop.
            const disableOnDesktop = item.getAttribute('data-disable-on-desktop') === 'true';
            const isDesktop = window.innerWidth >= 768;

            // Skip initialization if disabled on desktop and current viewport is desktop.
            if (disableOnDesktop && isDesktop) {
                return;
            }

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
            const carouselView = item.getAttribute('data-view') ? parseInt(item.getAttribute('data-view')) : 4;
            const carouselLaptopView = item.getAttribute('data-laptop-view') ? parseInt(item.getAttribute('data-laptop-view')) : 3;
            const carouselTabletView = item.getAttribute('data-tablet-view') ? parseInt(item.getAttribute('data-tablet-view')) : 2;
            const carouselMobileView = item.getAttribute('data-mobile-view') ? parseInt(item.getAttribute('data-mobile-view')) : 1;
            const carouselAutoplay = item.getAttribute('data-autoplay') ? item.getAttribute('data-autoplay') : 0;
            const carouselGap = item.getAttribute('data-gap') ? parseInt(item.getAttribute('data-gap'), 10) : 20;
            const carouselRewind = item.getAttribute('data-rewind') ? item.getAttribute('data-rewind') : false;
            const carouselbuttonsColor = item.getAttribute('data-buttons-color') ? item.getAttribute('data-buttons-color') : 'black';
            const carouselbuttonsBackgroundColor = item.getAttribute('data-buttons-background-color') ? item.getAttribute('data-buttons-background-color') : 'transparent';
            const carouselbuttonsPosition = item.getAttribute('data-buttons-position') ? item.getAttribute('data-buttons-position') : 'side';


            // Add classes
            item.classList.add('glide__slides', 'glide-' + Math.floor(Math.random() * 1000));
            for (const child of item.children) {
                child.classList.add('glide__slide');
            }

            // Don't show bullets on responsive and more than 10 items.
            let showBullets = false;
            if (window.screen.availWidth < 768 && item.children.length <= 10) {
                showBullets = true;
            } else if (window.screen.availWidth > 768) {
                showBullets = true;
            }

            if (showBullets && carouselbuttons == 'bullets') {
                const bullets = document.createElement('div');
                bullets.classList.add('glide__bullets');
                bullets.setAttribute('data-glide-el', 'controls[nav]');

                for (let i = 0; i < item.children.length; i++) {
                    const bullet = document.createElement('button');
                    bullet.classList.add('glide__bullet');
                    bullet.setAttribute('data-glide-dir', '=' + i);
                    bullet.setAttribute('aria-label', 'Go to slide ' + (i + 1));
                    bullet.style.backgroundColor = carouselbuttonsBackgroundColor;
                    bullets.appendChild(bullet);
                }

                wrapperParent.appendChild(bullets);

                // Add custom CSS for active bullet color
                const style = document.createElement('style');
                style.textContent = `
					.glide__bullet.glide__bullet--active {
						background-color: ${carouselbuttonsColor} !important;
					}
				`;
                document.head.appendChild(style);
            }

            if (carouselbuttons == 'arrows') {
                const arrows = document.createElement('div');
                arrows.classList.add('glide__arrows');
                if (carouselbuttonsPosition == 'bottom') {
                    arrows.classList.add('glide__arrows--bottom');
                } else {
                    arrows.classList.add('glide__arrows--top');
                }

                arrows.setAttribute('data-glide-el', 'controls');
                arrowsHTML = '<button class="glide__arrow glide__arrow--left glide__arrow glide__arrow--left" data-glide-dir="<"';
                arrowsHTML += ' aria-label="Previous slide"';
                arrowsHTML += ' style="background-color: ' + carouselbuttonsBackgroundColor + '"';
                arrowsHTML += '><svg width="7" height="12" viewBox="0 0 7 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 1L1 6L6 11" stroke="';
                arrowsHTML += carouselbuttonsColor;
                arrowsHTML += '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button><button class="glide__arrow glide__arrow--right glide__arrow glide__arrow--right" data-glide-dir=">"';
                arrowsHTML += ' aria-label="Next slide"';
                arrowsHTML += ' style="background-color: ' + carouselbuttonsBackgroundColor + '"';
                arrowsHTML += '><svg width="7" height="12" viewBox="0 0 7 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 11L6 6L1 1" stroke="';
                arrowsHTML += carouselbuttonsColor;
                arrowsHTML += '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>';
                arrows.innerHTML = arrowsHTML;
                wrapperParent.appendChild(arrows);
            }
            const glideFrontBlocks = new Glide(wrapperParent, {
                type: carouselType,
                perView: carouselView,
                startAt: 0,
                autoplay: carouselAutoplay === 0 ? 2500 : carouselAutoplay,
                gap: isNaN(carouselGap) ? 20 : carouselGap,
                rewind: carouselRewind,
                breakpoints: {
                    768: {
                        perView: carouselMobileView
                    },
                    1024: {
                        perView: carouselTabletView
                    },
                    1440: {
                        perView: carouselLaptopView
                    }
                }
            });

            if ('slider' === carouselType) {
                glideFrontBlocks.on('run.after', () => {
                    const currentIndex = glideFrontBlocks.index;
                    const lastIndex = glideFrontBlocks.selector.querySelectorAll('.glide__slide').length;

                    actualView = parseInt(currentIndex) + parseInt(carouselView);

                    if (actualView > lastIndex) {
                        setTimeout(() => {
                            glideFrontBlocks.go('=0');
                        }, 5);
                    }
                });
            }

            glideFrontBlocks.mount();
        });
    }
});
