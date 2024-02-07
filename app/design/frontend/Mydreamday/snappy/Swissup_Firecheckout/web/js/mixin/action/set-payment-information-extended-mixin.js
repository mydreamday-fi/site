define([
    'mage/utils/wrapper',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/quote'
], function (wrapper, customer, quote) {
    'use strict';

    var checkoutConfig = window.checkoutConfig;

    return function (target) {
        if (!checkoutConfig || !checkoutConfig.isFirecheckout) {
            return target;
        }

        return wrapper.wrap(target, function (originalAction, messageContainer, paymentData, skipBilling) {
            var placeOrderPressed = quote.firecheckout && quote.firecheckout.state.placeOrderPressed;

            if (!customer.isLoggedIn() && !quote.guestEmail && !placeOrderPressed) {
                quote.guestEmail = 'mail@example.com'; // Prevent '400 Bad Request' response
                skipBilling = true;
            }

            return originalAction(messageContainer, paymentData, skipBilling);
        });
    };
});
