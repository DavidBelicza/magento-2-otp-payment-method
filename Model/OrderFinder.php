<?php
/**
 * Youama_OTP
 *
 * @author  David Belicza <87.bdavid@gmail.com>
 * @license David Belicza e.v. (http://youama.hu)
 */

namespace Youama\OTP\Model;

use Magento\Checkout\Model\Session;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order;
use Youama\OTP\Api\OrderFinderInterface;

/**
 * Class OrderFinder
 *
 * Service to find Order entities.
 *
 * @package Youama\OTP\Model
 */
class OrderFinder implements OrderFinderInterface
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * OrderFinder constructor.
     *
     * @param Session      $checkoutSession
     * @param OrderFactory $orderFactory
     */
    public function __construct(
        Session $checkoutSession,
        OrderFactory $orderFactory
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
    }

    /**
     * It retrieves the last Order's increment_id from the session.
     *
     * @return string
     */
    public function getLastOrderIncrementIdFromSession(): string
    {
        return (string)$this->checkoutSession->getLastRealOrder()->getIncrementId();
    }

    /**
     * It retrieves the Order by its increment_id.
     *
     * @param string $orderIncrementId
     *
     * @return Order
     */
    public function getOrderByOrderIncrementId(string $orderIncrementId): Order
    {
        return $this->orderFactory
            ->create()
            ->loadByIncrementId($orderIncrementId);
    }
}
