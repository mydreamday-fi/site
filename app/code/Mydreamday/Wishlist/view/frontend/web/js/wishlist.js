define([
    'jquery',
    'Magento_Customer/js/customer-data'
], function ($, customerData) {
    'use strict';

    function updateWishlistUI() {
        var wishlistData = customerData.get('wishlist')(); // Retrieve wishlist data

        // Logic to update the UI based on wishlist data
        // Loop through products on the page and update the wishlist icon
    }

    // Optionally, you can listen for changes to the wishlist data
    $(document).on('customer-data-reload', function (event, data) {
        if (data.indexOf('wishlist') !== -1) {
            updateWishlistUI();
        }
    });
});
