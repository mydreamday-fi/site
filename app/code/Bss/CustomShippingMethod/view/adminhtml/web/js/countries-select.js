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
    "mage/adminhtml/form",
    'jquery/ui',
], function ($) {
    'use strict';
    $.widget('bss.countries_select', {
        options: {
            url_region: '',
            regions: '',
            country: ''
        },

        _create: function () {
            // Init code
            var url = this.options.url_region;
            var stateName = this.options.regions;
            var countryName = this.options.country;

            $('.field-specific_regions_').hide();
            var formKey = $("[name='form_key']").val();

            $('#bss_specific_country').change(function () {
                var countryNameSelected = $('#bss_specific_country').find(':selected').val();
                var urlRegion = url + "?country=" +countryNameSelected;
                $.ajax({
                    url: urlRegion,
                    data: {form_key:formKey},
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        if (data.htmlContent === '') {
                            $('.field-specific_regions_').show();
                            $('.field-specific_regions').hide();
                            $('#bss_specific_regions').val('');
                            $('#bss_specific_regions_').val('');
                            if (stateName !== '' && countryName === countryNameSelected) {
                                $('#bss_specific_regions_').val(stateName);
                            }
                        } else {
                            $('#bss_specific_regions').empty().append(data.htmlContent);
                            $('.field-specific_regions').show();
                            $('.field-specific_regions_').hide();
                            $('#bss_specific_regions_').val('');
                        }
                    }
                });
            });


            $('.field-specific_regions_').hide();
            $(document).ready(function () {
                setTimeout(function () {
                    stateName = stateName.split(',');
                    stateName.forEach(function (e) {
                        $('#bss_specific_regions option').each(function (a, b) {
                            if ($(this).text().toLowerCase() == e.toLowerCase()) {
                                $(this).attr('selected', 'selected');
                                $(this).trigger('change');
                            }
                        });
                    });
                }, 1000);
            });
            var countryNameSelected = $('#bss_specific_country').find(':selected').val();
            if (countryNameSelected != '') {
                jQuery('#bss_specific_country').trigger('change');
            }
        },

    });
    return $.bss.countries_select;
});
