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
 * Interface OTPCode
 *
 * This interface contains the OTP XML request and response tags and codes.
 *
 * @package Youama\OTP\Api\Data
 */
interface OTPCodeInterface
{
    /**
     * OTP request code to check.
     */
    const VALIDATE_REQUEST = 'WEBSHOPTRANZAKCIOLEKERDEZES';

    /**
     * OTP request code to payment.
     */
    const PAYMENT_REQUEST = 'WEBSHOPFIZETESINDITAS';

    /**
     * OTP response code about transaction, It is paid between 000 and 009.
     */
    const SUCCESS_PAYMENT = '00';

    /**
     * OTP response code about transaction - canceled by Customer.
     */
    const CANCELED_PAYMENT = 'VEVOOLDAL_VISSZAVONT';

    /**
     * OTP response code about transaction - canceled because of timeout.
     */
    const TIMEOUT_PAYMENT = 'VEVOOLDAL_TIMEOUT';

    /**
     * OTP response code about transaction - payment is in progress, waiting
     * for Customer.
     */
    const IN_PROGRESS_PAYMENT = 'VEVOOLDAL_INPUTVARAKOZAS';

    /**
     * OTP response code about transaction - payment has already finished.
     */
    const ALREADY_EXISTS_PAYMENT = 'LETEZOFIZETESITRANZAKCIO';
}
