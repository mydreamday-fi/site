/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomShippingMethod
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    "jquery",
    "mage/adminhtml/form"
], function ($) {
    'use strict';
    return function () {
        function hideAreaField()
        {
            $('.field-specific_country').hide();
            $('.field-specific_regions').hide();
            $('.field-specific_regions_').hide();
        }
        var $applicableCountry = $('#bss_applicable_countries');
        var val = parseInt($applicableCountry.children("option:selected").val());
        if (val === 0) {
            $('#bss_specific_countries').attr("disabled",true);
            hideAreaField();
        }
        if (val=== 1) {
            $('#bss_specific_countries').attr("disabled",false);
            hideAreaField();
        }
        if (val=== 2) {
            $('.field-specific_countries').hide();
        }
        $applicableCountry.on('change',function () {
            if (parseInt(this.value) !== 0) {
                if (parseInt(this.value) === 1) {
                    $('.field-specific_countries').show();
                    $('#bss_specific_countries').attr("disabled", false);
                    hideAreaField();

                } else if (parseInt(this.value) === 2) {
                    $('.field-specific_country').show();
                    $('.field-specific_regions_').show();
                    $('.field-specific_countries').hide();

                }
            } else {
                $('.field-specific_countries').show();
                $('#bss_specific_countries').attr("disabled", true);
                hideAreaField()
            }
        });
    }
});
