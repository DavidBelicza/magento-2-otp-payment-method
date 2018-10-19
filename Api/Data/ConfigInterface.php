<?php
/**
 * Youama_OTP
 *
 * @author  David Belicza <87.bdavid@gmail.com>
 * @license David Belicza e.v. (http://youama.hu)
 */

declare(strict_types=1);

namespace Youama\OTP\Api\Data;

/**
 * Class ConfigInterface
 *
 * This interface constants contain the config.xml file's tags for
 * configuration.
 *
 * @package Youama\OTP\Api\Data
 */
interface ConfigInterface
{
    const TITLE                   = 'title';
    const ACTIVE                  = 'active';
    const MODEL                   = 'model';
    const PAYMENT_ACTION          = 'payment_action';
    const API_KEY                 = 'api_key';
    const ALLOW_SPECIFIC          = 'allowspecific';
    const MIN_ORDER_TOTAL         = 'min_order_total';
    const REQUEST_PAYMENT         = 'request_payment';
    const REQUEST_SEND_ORDER      = 'request_send_order';
    const REQUEST_SEND_CUSTOMER   = 'request_send_customer';
    const RESPONSE_CALLBACK       = 'response_callback';
    const CHECKOUT_SUCCESS_URL    = 'checkout_success_url';
    const CHECKOUT_FAIL_URL       = 'checkout_fail_url';
    const PAYMENT_INTERFACE       = 'payment_interface';
    const PAYMENT_USER_INTERFACE  = 'payment_user_interface';
    const POS_ID                  = 'pos_id';
    const CLIENT_CODE             = 'client_code';
    const PRIVATE_KEY_PATH        = 'private_key_path';
    const ORDER_CANCEL_IS_ALLOWED = 'order_cancel_is_allowed';
    const PAID_MESSAGE            = 'paid_message';
    const UNPAID_MESSAGE          = 'unpaid_message';
    const CURRENCY_CODE           = 'currency_code';
    const LANGUAGE_CODE           = 'language_code';
    const TRANSACTION_ID_PREFIX   = 'transaction_id_prefix';
    const SHOP_COMMENT            = 'shop_comment';
    const FRONTEND_COMMENT        = 'frontend_comment';
}
