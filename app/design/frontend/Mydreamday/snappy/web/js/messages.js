/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'jquery',
    'uiComponent',
    'Magento_Ui/js/model/messageList',
    'jquery-ui-modules/effect-blind'
], function (ko, $, Component, globalMessages) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Ui/messages',
            selector: '[data-role=checkout-messages]',
            isHidden: false,
            hideTimeout: 5000,
            hideSpeed: 500,
            listens: {
                isHidden: 'onHiddenChange'
            }
        },

        /** @inheritdoc */
        initialize: function (config, messageContainer) {
            console.log("Initializing messages component...");
            
            this._super().initObservable();

            this.messageContainer = messageContainer || config.messageContainer || globalMessages;

            // Listen for changes in the messageContainer
            this.messageContainer.messages.subscribe(function (messages) {
                console.log("Message container update detected...");

                if (messages.length) {
                    messages.forEach(function (message) {
                        if (message.type !== 'cookie') {
                            console.log("New message of non-cookie type detected...");

                            $(document).trigger('newMessageReceived', [message]);
                            
                            // Appear effect
                            setTimeout(function() {
                                var $unshownMessages = $('.message:not(.shown)');
                                if ($unshownMessages.length) {
                                    console.log("Adding 'shown' class and close button to new messages...");
                                    $unshownMessages.addClass('shown').append('<button type="button" class="button-close">Close</button>');
                                }
                            }, 0);
                        }
                    });
                }
            });

            // Close button click handler
            $(document).on('click', '.message > .button-close', function () {
                console.log("Close button clicked...");

                var message = $(this).closest('.message').removeClass('shown');

                setTimeout(function () {
                    $(message).remove();
                }, 100);
            });

            return this;
        },

        /** @inheritdoc */
        initObservable: function () {
            console.log("Initializing observables...");
            
            this._super().observe('isHidden');
            return this;
        },

        /**
         * Checks visibility.
         *
         * @return {Boolean}
         */
        isVisible: function () {
            return this.isHidden(this.messageContainer.hasMessages());
        },

        /**
         * Remove all messages.
         */
        removeAll: function () {
            console.log("Removing all messages...");
            this.messageContainer.clear();
        },

        /**
         * @param {Boolean} isHidden
         */
        onHiddenChange: function (isHidden) {
            console.log("onHiddenChange triggered...");
            
            // Hide message block if needed
            if (isHidden) {
                setTimeout(function () {
                    $(this.selector).hide('slow');
                }.bind(this), this.hideTimeout);
            }
        }
    });
});
