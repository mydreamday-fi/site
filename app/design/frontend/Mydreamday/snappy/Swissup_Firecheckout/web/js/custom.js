define([
    'underscore',
	'Swissup_Firecheckout/js/utils/form-field/manager',
    'Swissup_Firecheckout/js/utils/form-field/classname',
    'Swissup_Firecheckout/js/utils/form-field/dependency',
	'mage/translate'
], function (_, manager, classname, dependency, $t) {
    'use strict';

	manager('[name="street[0]"]', {
        label: $t('Street Address *'),
        placeholder: $t('Street Address *')
    });
	
    // Resize some fields to take 50% width
    classname(
        [
            '[name="firstname"], [name="lastname"]',
            '[name="company"], [name="vat_id"]',
            '[name="postcode"], [name="city"]',
			'[name="country_id"], [name="telephone"]'
        ].join(', '),
        'fc-col-6 fc-size-xs:fc-col-12'
    );
	
    // Resize 'is_business_account' to take 100% width
    classname('[name="custom_attributes[is_business_account]"]', 'fc-col-12');

    // setup dependent fields for shipping and billing forms
    var scopes = [
        '.form-shipping-address',
		'.billing-address-form',
        '.payment-method._active'   // or '.billing-address-form' if you use
                                    // 'Display Billing Address On Payment Page' option
    ];
    _.each(scopes, function (scope) {
        dependency({
            scope: scope,
            watch: {
                '[name="custom_attributes[is_business_account]"]': true
            },
            react: {
                '[name="company"]': 'required|hidden',
                '[name="vat_id"]': 'required|hidden'
            }
        });
    });
});