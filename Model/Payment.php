<?php
/**
 * Youama_OTP
 *
 * @author  David Belicza <87.bdavid@gmail.com>
 * @license David Belicza e.v. (http://youama.hu)
 */

declare(strict_types=1);

namespace Youama\OTP\Model;

use Magento\Payment\Model\Method\Adapter;

/**
 * Class Payment
 *
 * Regular Payment model to initiate the payment for Magento 2.1 and up.
 * Dependencies are injected from XML.
 *
 * @package Youama\OTP\Model
 */
class Payment extends Adapter
{
    /**
     * Check fetch transaction info availability.
     *
     * @return bool It is false, the admin contribution is not allowed.
     *
     */
    public function canFetchTransactionInfo(): bool
    {
        return false;
    }

    /**
     * Flag if we need to run payment initialize while order place
     *
     * @return bool
     *
     */
    public function isInitializeNeeded(): bool
    {
        return true;
    }
}
