/**
 * WooCommerce Categories Grid Block JS
 */

(function($) {
  'use strict';
  
  // Initialize when DOM is ready
  $(document).ready(function() {
    // Add any additional JS functionality here
    // For example, you might want to add lazy loading or filtering options
    
    // Example: Add hover class for touch devices
    $('.frbl-woo-category').on('touchstart', function() {
      $(this).addClass('touch-hover');
    }).on('touchend touchcancel', function() {
      $(this).removeClass('touch-hover');
    });
  });
  
})(jQuery); 