<?php
/**
 * Youama_OTP
 *
 * @author  David Belicza <87.bdavid@gmail.com>
 * @license David Belicza e.v. (http://youama.hu)
 */

declare(strict_types=1);

namespace Youama\OTP\Api;

use Magento\Sales\Model\Order;

/**
 * Interface OrderFinderInterface
 *
 * Service to find Order entities.
 *
 * @package Youama\OTP\Api
 */
interface OrderFinderInterface
{
    /**
     * It retrieves the last Order's increment_id from the session.
     *
     * @return string
     */
    public function getLastOrderIncrementIdFromSession(): string;

    /**
     * It retrieves the Order by its increment_id.
     *
     * @param string $orderIncrementId
     *
     * @return Order
     */
    public function getOrderByOrderIncrementId(string $orderIncrementId): Order;
}
