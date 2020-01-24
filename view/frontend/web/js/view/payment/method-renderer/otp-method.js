/**
 * Youama_OTP
 *
 * @author  David Belicza <87.bdavid@gmail.com>
 * @license David Belicza e.v. (http://youama.hu)
 */

define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/payment/additional-validators'
    ],

    /**
     * @param Component
     * @param additionalValidators
     * @returns {*}
     */
    function (
        Component,
        additionalValidators
    ) {
        'use strict';

        var paymentCode = 'youama_otp';
        var config = window.checkoutConfig.payment[paymentCode];

        return Component.extend({
            defaults: {
                template: 'Youama_OTP/payment/otp.html'
            },

            /**
             * @returns {string}
             */
            getCode: function() {
                return paymentCode;
            },

            /**
             * @returns {*}
             */
            isActive: function() {
                return config['is_active'];
            },

            /**
             * @returns {*}
             */
            isAvailable: function() {
                return config['is_available'];
            },

            /**
             *
             * @returns {*}
             */
            getFrontendComment: function() {
                return config['frontend_comment'];
            },

            /**
             * @param data
             * @param event
             * @returns {boolean}
             */
            placeOrder: function (data, event) {
                var self = this;

                if (event) {
                    event.preventDefault();
                }

                if (this.validate() && additionalValidators.validate()) {
                    this.isPlaceOrderActionAllowed(false);

                    this.getPlaceOrderDeferredObject()
                        .fail(
                            function () {
                                self.isPlaceOrderActionAllowed(true);
                            }
                        ).done(
                        function () {
                            self.afterPlaceOrder();
                            window.location.replace(config['request_url']);
                        }
                    );

                    return true;
                }

                return false;
            }
        });
    }
);