<?php
/**
 * Youama_OTP
 *
 * @author  David Belicza <87.bdavid@gmail.com>
 * @license David Belicza e.v. (http://youama.hu)
 */

declare(strict_types=1);

namespace Youama\OTP\Cron;

use Youama\OTP\Model\Updater;

/**
 * Class PaymentValidator
 *
 * PaymentValidator is initiated from the Magento's Cron API. When Customer
 * does not come back to the shop after payment the Cron checks the orders and
 * updated the payment data by the Updater class.
 *
 * @package Youama\OTP\Cron
 * @see Updater
 */
class PaymentValidator
{
    /**
     * @var Updater
     */
    private $updater;

    /**
     * PaymentValidator constructor.
     *
     * @param Updater                $updater
     */
    public function __construct(
        Updater $updater
    ) {
        $this->updater = $updater;
    }

    /**
     * It runs the update process.
     *
     * @return void
     * @see Updater
     */
    public function execute()
    {
        $this->updater->updateOrders(true);
    }
}
