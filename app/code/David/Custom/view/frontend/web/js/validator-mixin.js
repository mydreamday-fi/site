define([
    'jquery',
    'moment'
], function ($, moment) {
    'use strict';

    return function (validator) {

        validator.addRule(
            'custom-validate-telephone',
            function (value, params) {                
                var matches = value.match(/\d+/g);
                if(value.length < 6 || value.length > 12 || matches == null){
                    logError("Invalid phone number: " + value); // Log the error
                    return false;
                }
                else{
                    return true;
                }       
            },
            $.mage.__("Phone number must be between 6 to 12 characters.")
        );

        function logError(errorMessage) {
            console.error("Validation Error: ", errorMessage);
        }

        return validator;
    };
});
