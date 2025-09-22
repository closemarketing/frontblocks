window.addEventListener('load', function() {
    initFrontBlocksGallery();
});

function initFrontBlocksGallery() {
    initMasonryGalleries();
    initGridGalleries();
    initLightbox();
}

function initMasonryGalleries() {
    const masonryGalleries = document.querySelectorAll('.frontblocks-gallery-masonry');
    
    masonryGalleries.forEach(function(gallery) {
        const columns = parseInt(gallery.getAttribute('data-columns')) || 3;
        const gutter = parseInt(gallery.getAttribute('data-gutter')) || 20;
        setupMasonryLayout(gallery, columns, gutter);
    });
}

function initGridGalleries() {
    const gridGalleries = document.querySelectorAll('.frontblocks-gallery-grid');
    
    gridGalleries.forEach(function(gallery) {
        const columns = parseInt(gallery.getAttribute('data-columns')) || 3;
        const gutter = parseInt(gallery.getAttribute('data-gutter')) || 20;
        // Set up grid layout
        setupGridLayout(gallery, columns, gutter);
    });
}

function setupGridLayout(gallery, columns, gutter) {
    gallery.style.setProperty('--frontblocks-gutter', gutter + 'px');
}

function setupMasonryLayout(gallery, columns, gutter) {
    // Set container styles for masonry
    gallery.style.position = 'relative';
    gallery.style.display = 'block';
    gallery.style.width = '100%';
    gallery.style.setProperty('--frontblocks-gutter', gutter + 'px');
    
    // Get all figure elements
    const figures = gallery.querySelectorAll('figure.wp-block-image');
    if (figures.length === 0) return;
    
    // Calculate optimal column count based on available width and breakpoints
    const containerWidth = gallery.offsetWidth;
    const minColumnWidth = 200;
    const totalGutterWidth = gutter * (columns - 1);
    const availableWidthForColumns = containerWidth - totalGutterWidth;
    const actualColumnWidth = availableWidthForColumns / columns;
    
    let optimalColumns = getResponsiveColumns(columns, containerWidth);
    
    // If columns are too narrow, reduce the number of columns
    if (actualColumnWidth < minColumnWidth) {
        optimalColumns = Math.max(1, Math.floor((containerWidth + gutter) / (minColumnWidth + gutter)));
    }
    
    // Only set margin-bottom for gutter spacing
    figures.forEach(function(figure) {
        figure.style.margin = '0';
        figure.style.marginBottom = gutter + 'px';
        // Do NOT set width, float, or clear - let Masonry handle this
    });
    
    // Initialize Masonry with calculated column width
    const masonry = new Masonry(gallery, {
        itemSelector: 'figure.wp-block-image',
        columnWidth: '.wp-block-image', // Let Masonry calculate column width
        gutter: gutter,
        percentPosition: true,
        transitionDuration: '0.3s'
    });
    
    // Layout Masonry after images load
    const images = gallery.querySelectorAll('img');
    let loadedImages = 0;
    
    if (images.length === 0) {
        masonry.layout();
        return;
    }
    
    images.forEach(function(img) {
        if (img.complete) {
            loadedImages++;
            if (loadedImages === images.length) {
                masonry.layout();
            }
        } else {
            img.addEventListener('load', function() {
                loadedImages++;
                if (loadedImages === images.length) {
                    masonry.layout();
                }
            });
        }
    });
    
    // Layout immediately if all images are already loaded
    if (loadedImages === images.length) {
        masonry.layout();
    }
    
    // Store masonry instance for resize handling
    gallery.masonryInstance = masonry;
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (gallery.masonryInstance) {
            gallery.masonryInstance.layout();
        }
    });
}

function getResponsiveColumns(originalColumns, containerWidth) {
    // Responsive breakpoints
    if (containerWidth <= 480) {
        return Math.min(originalColumns, 2);
    } else if (containerWidth <= 768) {
        return Math.min(originalColumns, 3);
    } else if (containerWidth <= 1024) {
        return Math.min(originalColumns, 4);
    } else {
        return originalColumns;
    }
}

function initLightbox() {
    // Create lightbox container if it doesn't exist
    let lightbox = document.getElementById('frontblocks-lightbox');
    if (!lightbox) {
        lightbox = createLightbox();
        document.body.appendChild(lightbox);
    }
    
    // Add click event listeners to gallery images
    const galleries = document.querySelectorAll('.frontblocks-gallery-masonry, .frontblocks-gallery-grid');
    
    galleries.forEach(function(gallery) {
        const enableLightbox = gallery.getAttribute('data-lightbox') === 'true';
        
        if (enableLightbox) {
            const images = gallery.querySelectorAll('img');
            images.forEach(function(img, index) {
                img.style.cursor = 'pointer';
                img.addEventListener('click', function(e) {
                    e.preventDefault();
                    openLightbox(images, index);
                });
            });
        }
    });
}

function createLightbox() {
    const lightbox = document.createElement('div');
    lightbox.id = 'frontblocks-lightbox';
    lightbox.className = 'frontblocks-lightbox';
    
    lightbox.innerHTML = `
        <div class="frontblocks-lightbox-content">
            <button class="frontblocks-lightbox-close">&times;</button>
            <img src="" alt="">
            <div class="frontblocks-lightbox-caption"></div>
            <div class="frontblocks-lightbox-counter"></div>
            <button class="frontblocks-lightbox-nav frontblocks-lightbox-prev">&lt;</button>
            <button class="frontblocks-lightbox-nav frontblocks-lightbox-next">&gt;</button>
        </div>
    `;
    
    // Add event listeners
    const closeBtn = lightbox.querySelector('.frontblocks-lightbox-close');
    const prevBtn = lightbox.querySelector('.frontblocks-lightbox-prev');
    const nextBtn = lightbox.querySelector('.frontblocks-lightbox-next');
    
    closeBtn.addEventListener('click', closeLightbox);
    prevBtn.addEventListener('click', showPreviousImage);
    nextBtn.addEventListener('click', showNextImage);
    
    // Close on background click
    lightbox.addEventListener('click', function(e) {
        if (e.target === lightbox) {
            closeLightbox();
        }
    });
    
    // Close on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && lightbox.classList.contains('active')) {
            closeLightbox();
        } else if (e.key === 'ArrowLeft' && lightbox.classList.contains('active')) {
            showPreviousImage();
        } else if (e.key === 'ArrowRight' && lightbox.classList.contains('active')) {
            showNextImage();
        }
    });
    
    return lightbox;
}

let currentImages = [];
let currentImageIndex = 0;

function openLightbox(images, index) {
    currentImages = Array.from(images);
    currentImageIndex = index;
    
    const lightbox = document.getElementById('frontblocks-lightbox');
    const lightboxImg = lightbox.querySelector('img');
    
    // Set image source
    lightboxImg.src = currentImages[currentImageIndex].src;
    lightboxImg.alt = currentImages[currentImageIndex].alt || '';
    
    // Show lightbox
    lightbox.classList.add('active');
    document.body.style.overflow = 'hidden';
    
    // Update caption and counter
    updateLightboxCaption();
    updateLightboxCounter();
    updateLightboxNavigation();
}

function closeLightbox() {
    const lightbox = document.getElementById('frontblocks-lightbox');
    lightbox.classList.remove('active');
    document.body.style.overflow = '';
    
    currentImages = [];
    currentImageIndex = 0;
}

function showPreviousImage() {
    if (currentImages.length === 0) return;
    
    currentImageIndex = (currentImageIndex - 1 + currentImages.length) % currentImages.length;
    updateLightboxImage();
}

function showNextImage() {
    if (currentImages.length === 0) return;
    
    currentImageIndex = (currentImageIndex + 1) % currentImages.length;
    updateLightboxImage();
}

function updateLightboxImage() {
    const lightbox = document.getElementById('frontblocks-lightbox');
    const lightboxImg = lightbox.querySelector('img');
    
    lightboxImg.src = currentImages[currentImageIndex].src;
    lightboxImg.alt = currentImages[currentImageIndex].alt || '';
    
    updateLightboxCaption();
    updateLightboxCounter();
    updateLightboxNavigation();
}

function updateLightboxCaption() {
    const lightbox = document.getElementById('frontblocks-lightbox');
    const captionElement = lightbox.querySelector('.frontblocks-lightbox-caption');
    
    if (currentImages.length === 0) {
        captionElement.textContent = '';
        return;
    }
    
    const currentImage = currentImages[currentImageIndex];
    let caption = '';
    
    // Try to get caption from different sources
    if (currentImage.alt && currentImage.alt.trim() !== '') {
        caption = currentImage.alt;
    } else if (currentImage.title && currentImage.title.trim() !== '') {
        caption = currentImage.title;
    } else {
        // Try to get caption from parent figure element
        const figure = currentImage.closest('figure');
        if (figure) {
            const figcaption = figure.querySelector('figcaption');
            if (figcaption) {
                caption = figcaption.textContent.trim();
            }
        }
    }
    
    captionElement.textContent = caption;
    captionElement.style.display = caption ? 'block' : 'none';
}

function updateLightboxCounter() {
    const lightbox = document.getElementById('frontblocks-lightbox');
    const counterElement = lightbox.querySelector('.frontblocks-lightbox-counter');
    
    if (currentImages.length <= 1) {
        counterElement.style.display = 'none';
        return;
    }
    
    counterElement.style.display = 'block';
    counterElement.textContent = `${currentImageIndex + 1} / ${currentImages.length}`;
}

function updateLightboxNavigation() {
    const lightbox = document.getElementById('frontblocks-lightbox');
    const prevBtn = lightbox.querySelector('.frontblocks-lightbox-prev');
    const nextBtn = lightbox.querySelector('.frontblocks-lightbox-next');
    
    const showNav = currentImages.length > 1;
    prevBtn.style.display = showNav ? 'block' : 'none';
    nextBtn.style.display = showNav ? 'block' : 'none';
}

// Handle dynamic content loading (for AJAX-loaded galleries)
function reinitFrontBlocksGallery() {
    initFrontBlocksGallery();
}

// Expose function globally for external use
window.reinitFrontBlocksGallery = reinitFrontBlocksGallery; 