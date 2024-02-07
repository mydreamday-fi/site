/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'jquery',
        'underscore',
        'Magento_Ui/js/form/form',
        'ko',
        'Magento_Customer/js/model/customer',
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/model/address-converter',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/create-shipping-address',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-address/form-popup-state',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/action/select-shipping-method',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Magento_Checkout/js/action/set-shipping-information',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Ui/js/modal/modal',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'Magento_Checkout/js/checkout-data',
        'uiRegistry',
        'mage/translate',
        'Magento_Checkout/js/model/shipping-rate-service'
    ],
    function (
        $,
        _,
        Component,
        ko,
        customer,
        addressList,
        addressConverter,
        quote,
        createShippingAddress,
        selectShippingAddress,
        shippingRatesValidator,
        formPopUpState,
        shippingService,
        selectShippingMethodAction,
        rateRegistry,
        setShippingInformationAction,
        stepNavigator,
        modal,
        checkoutDataResolver,
        checkoutData,
        registry,
        $t
    ) {
        'use strict';

    var popUp = null;
	return Component.extend({
		defaults: {
			template: 'Magento_Checkout/shipping',
			currentShipping: quote.shippingMethod,
			checkedPickpoint: ko.observable(true),
			selectedPickupPoints: ko.observable(),
			selectedPostnord: ko.observable(),
			dataTriggerModal: ko.observable('mdd-shipping-option-select'),
			pktkppickuppoint: ko.observableArray([]).extend({
				rateLimit: 500
			}),
			postnord: ko.observableArray([]).extend({
				rateLimit: 500
			}),
			rates: ko.observableArray([]).extend({
				rateLimit: 500
			}),
			changePostcode: ko.observable('input[name="postcode"]'),
		},
		openPickupOptionsModal: function(method) {
            console.log(method.carrier_code);
			$('.mdd-shipping-pickup-options-container-' + method.carrier_code).modal('openModal');
		},
		closePickupOptionsModal: function(method) {
            console.log(method.carrier_code);
			$('.mdd-shipping-pickup-options-container-' + method.carrier_code).modal('closeModal');
		},
		currentPickpoint: function(data) {
            if(data.carrier_code == 'pktkppickuppoint'){
                this.selectedPickupPoints(data);
		//this.handlePickupPointError(data);
            }else if(data.carrier_code == 'postnord'){
                this.selectedPostnord(data);
            }
			this.selectShippingMethod(data);
			var dataRates = [];
			_.each(this.rates(), function(rate) {
				if (rate.carrier_code == data.carrier_code) {
					dataRates.push(data);
				} else {
					dataRates.push(rate);
				}
			});
			this.rates(dataRates);
			this.closePickupOptionsModal(data);
		},
		visible: ko.observable(!quote.isVirtual()),
		errorValidationMessage: ko.observable(false),
		isCustomerLoggedIn: customer.isLoggedIn,
		isFormPopUpVisible: formPopUpState.isVisible,
		isFormInline: addressList().length == 0,
		isNewAddressAdded: ko.observable(false),
		saveInAddressBook: 1,
		quoteIsVirtual: quote.isVirtual(),
		initialize: function() {
			var self = this,
				hasNewAddress, fieldsetName = 'checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset';
			this._super();
			shippingRatesValidator.initFields(fieldsetName);
			$(document).ajaxComplete(function() {
				$('.mdd-shipping-method').each(function() {
					var element = $(this);
					if (element.text().indexOf("Posti Noutopiste - TARKISTA POSTINUMERO") >= 0) {
						element.css('color', 'red');
					}
				});
			});
			shippingService.getShippingRates().subscribe(function(rates) {
				var dataRates = [];
				self.pktkppickuppoint.removeAll();
				self.postnord.removeAll();
                var postnordorder = 0;
				if (rates.length > 0) {
					_.each(rates, function(rate) {
					
						if (rate.carrier_code == 'pktkppickuppoint') {
							rate.order = window.getPostiPickPoint?window.getPostiPickPoint:0;
							self.pktkppickuppoint.push(rate);
							if (self.selectedPickupPoints() && self.selectedPickupPoints().method_code == rate.method_code) {
								dataRates.push(rate);
							} else {
                                if (!dataRates.filter(item => item.carrier_code == 'pktkppickuppoint').length) {
                                    dataRates.push(rate);
                                    self.selectedPickupPoints(rate);
                                }
							}
						} else if(rate.carrier_code == 'postnord'){
                           	rate.order = parseFloat(window.getPostNord?window.getPostNord:0);
                            postnordorder -= 0.1;
                            rate.order += postnordorder;
							self.postnord.push(rate);
                            if (self.selectedPostnord() && self.selectedPostnord().method_code == rate.method_code) {
								dataRates.push(rate);
							} else {
                                if (!dataRates.filter(item => item.carrier_code == 'postnord').length) {
                                    dataRates.push(rate);
                                    self.selectedPostnord(rate);
                                }
							}
						} else {
							
							if(rate.method_code=="2104"){
								rate.order = window.getPostiKotipaketti?window.getPostiKotipaketti:0;
							}
							if(rate.method_code=="2102"){
								rate.order = window.getPostiExpress?window.getPostiExpress:0;
							}
							if(rate.carrier_code=="postnord_homedelivery"){
								rate.order = window.getPostNordHomeDelivery?window.getPostNordHomeDelivery:0;
							}
							dataRates.push(rate);
						}
					});

					dataRates.sort(function(a, b) {
						return a.order - b.order
					});

//                    self.postnord.sort(function(a, b) {
//						return a.order - b.order
//					});
                             		rates.sort(function (a,b) {
            				    return Number(a.extension_attributes.raw_distance) - Number(b.extension_attributes.raw_distance);
          		     		});

					self.rates(dataRates);
				}
			});
			if (!quote.isVirtual()) {
				stepNavigator.registerStep('shipping', '', $t('Shipping'), this.visible, _.bind(this.navigate, this), 10);
			}
			checkoutDataResolver.resolveShippingAddress();
			hasNewAddress = addressList.some(function(address) {
				return address.getType() == 'new-customer-address';
			});
			this.isNewAddressAdded(hasNewAddress);
			this.isFormPopUpVisible.subscribe(function(value) {
				if (value) {
					self.getPopUp().openModal();
				}
			});
			quote.shippingMethod.subscribe(function() {
				self.errorValidationMessage(false);
			});
			registry.async('checkoutProvider')(function(checkoutProvider) {
				var shippingAddressData = checkoutData.getShippingAddressFromData();
				if (shippingAddressData) {
					checkoutProvider.set('shippingAddress', $.extend({}, checkoutProvider.get('shippingAddress'), shippingAddressData));
				}
				checkoutProvider.on('shippingAddress', function(shippingAddressData) {
					checkoutData.setShippingAddressFromData(shippingAddressData);
				});
			});
			return this;
		},
		navigate: function() {},
		getPopUp: function() {
			var self = this,
				buttons;
			if (!popUp) {
				buttons = this.popUpForm.options.buttons;
				this.popUpForm.options.buttons = [{
					text: buttons.save.text ? buttons.save.text : $t('Save Address'),
					class: buttons.save.class ? buttons.save.class : 'action primary action-save-address',
					click: self.saveNewAddress.bind(self)
				}, {
					text: buttons.cancel.text ? buttons.cancel.text : $t('Cancel'),
					class: buttons.cancel.class ? buttons.cancel.class : 'action secondary action-hide-popup',
					click: function() {
						this.closeModal();
					}
				}];
				this.popUpForm.options.closed = function() {
					self.isFormPopUpVisible(false);
				};
				popUp = modal(this.popUpForm.options, $(this.popUpForm.element));
			}
			return popUp;
		},
		showFormPopUp: function() {
			this.isFormPopUpVisible(true);
		},
		saveNewAddress: function() {
			var addressData, newShippingAddress;
			this.source.set('params.invalid', false);
			this.source.trigger('shippingAddress.data.validate');
			if (!this.source.get('params.invalid')) {
				addressData = this.source.get('shippingAddress');
				addressData.save_in_address_book = this.saveInAddressBook ? 1 : 0;
				newShippingAddress = createShippingAddress(addressData);
				selectShippingAddress(newShippingAddress);
				checkoutData.setSelectedShippingAddress(newShippingAddress.getKey());
				checkoutData.setNewCustomerShippingAddress(addressData);
				this.getPopUp().closeModal();
				this.isNewAddressAdded(true);
			}
		},
		rates: shippingService.getShippingRates(),
		isLoading: shippingService.isLoading,
		isSelected: ko.computed(function() {
			return quote.shippingMethod() ? quote.shippingMethod().carrier_code + '_' + quote.shippingMethod().method_code : null;
		}),
		checkExistmethod: function() {
			$(".mdd-shipping-option input.radio").prop('disabled', false);
			return shippingService.getShippingRates().some(function(o) {
				return o["carrier_code"] === "pktkppickuppoint" || o["carrier_code"] === "postnord";
			})
		},
		selectShippingMethod: function(shippingMethod) {
			selectShippingMethodAction(shippingMethod);
			checkoutData.setSelectedShippingRate(shippingMethod.carrier_code + '_' + shippingMethod.method_code);
			return true;
		},
		setShippingInformation: function() {
			if (this.validateShippingInformation()) {
				setShippingInformationAction().done(function() {
					stepNavigator.next();
				});
			}
		},
		validateShippingInformation: function() {
			var shippingAddress, addressData, loginFormSelector = 'form[data-role=email-with-possible-login]',
				emailValidationResult = customer.isLoggedIn();
			if (!quote.shippingMethod()) {
				this.errorValidationMessage('Please specify a shipping method.');
				return false;
			}
			if (!customer.isLoggedIn()) {
				$(loginFormSelector).validation();
				emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
			}
			if (this.isFormInline) {
				this.source.set('params.invalid', false);
				this.source.trigger('shippingAddress.data.validate');
				if (this.source.get('shippingAddress.custom_attributes')) {
					this.source.trigger('shippingAddress.custom_attributes.data.validate');
				}
				if (this.source.get('params.invalid') || !quote.shippingMethod().method_code || !quote.shippingMethod().carrier_code || !emailValidationResult) {
					return false;
				}
				shippingAddress = quote.shippingAddress();
				addressData = addressConverter.formAddressDataToQuoteAddress(this.source.get('shippingAddress'));
				for (var field in addressData) {
					if (addressData.hasOwnProperty(field) && shippingAddress.hasOwnProperty(field) && typeof addressData[field] != 'function' && _.isEqual(shippingAddress[field], addressData[field])) {
						shippingAddress[field] = addressData[field];
					} else if (typeof addressData[field] != 'function' && !_.isEqual(shippingAddress[field], addressData[field])) {
						shippingAddress = addressData;
						break;
					}
				}
				if (customer.isLoggedIn()) {
					shippingAddress.save_in_address_book = 1;
				}
				selectShippingAddress(shippingAddress);
			}
			if (!emailValidationResult) {
				$(loginFormSelector + ' input[name=username]').focus();
				return false;
			}
			return true;
		}
	});
});
