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
 * Interface PaymentStatusInterface
 *
 * The Payment statuses.
 *
 * @package Youama\OTP\Api\Data
 */
interface PaymentStatusInterface
{
    /**
     * It is not validated yet or it is in progress.
     */
    const OTP_PAYMENT_TRY_AGAIN_LATER = 0;

    /**
     * It is finished and paid.
     */
    const OTP_PAYMENT_SUCCESS = 1;

    /**
     * It is finished and unpaid.
     */
    const OTP_PAYMENT_FAILED = 2;
}
