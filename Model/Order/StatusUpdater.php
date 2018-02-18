<?php
/**
 * Youama_OTP
 *
 * @author  David Belicza <87.bdavid@gmail.com>
 * @license David Belicza e.v. (http://youama.hu)
 */

declare(strict_types=1);

namespace Youama\OTP\Model\Order;

use Magento\Sales\Api\Data\OrderStatusHistoryInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderCommentSender;
use Magento\Sales\Model\Order\Status\History;
use Youama\OTP\Api\OrderFinderInterface;
use Youama\OTP\Helper\Config;

/**
 * Class Status
 *
 * This class manages the status updates of the Order and changes of the
 * History and Comment changes.
 *
 * @package Youama\OTP\Model\Order
 */
class StatusUpdater
{
    /**
     * Placeholder for the OTP authorization code.
     */
    const PLACEHOLDER = 'OTP_TR_ID';

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderFinderInterface
     */
    private $orderFinder;

    /**
     * @var OrderStatusHistoryInterfaceFactory
     */
    private $orderStatusHistoryInterfaceFactory;

    /**
     * @var OrderCommentSender
     */
    private $orderCommentSender;

    /**
     * @var Config
     */
    private $configHelper;

    /**
     * Status constructor.
     *
     * @param OrderRepositoryInterface           $orderRepository
     * @param OrderFinderInterface               $orderFinder
     * @param OrderStatusHistoryInterfaceFactory $orderStatusHistoryInterfaceFactory
     * @param OrderCommentSender                 $orderCommentSender
     * @param Config                             $configHelper
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderFinderInterface $orderFinder,
        OrderStatusHistoryInterfaceFactory $orderStatusHistoryInterfaceFactory,
        OrderCommentSender $orderCommentSender,
        Config $configHelper
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderFinder = $orderFinder;
        $this->orderStatusHistoryInterfaceFactory = $orderStatusHistoryInterfaceFactory;
        $this->orderCommentSender = $orderCommentSender;
        $this->configHelper = $configHelper;
    }

    /**
     * Put order to pending payment status and void the notification.
     *
     * @param string $orderIncrementId
     */
    public function inProgress(string $orderIncrementId)
    {
        $order = $this->orderFinder->getOrderByOrderIncrementId($orderIncrementId);
        $order->setStatus(Order::STATE_PENDING_PAYMENT);
        $order->setCustomerNoteNotify(0);
        $order->addStatusHistoryComment('');

        $this->orderRepository->save($order);
    }

    /**
     * Put order to processing, notify customer about it.
     *
     * @param string $orderIncrementId
     * @param int    $transactionId
     */
    public function success(string $orderIncrementId, int $transactionId)
    {
        $order = $this->orderFinder->getOrderByOrderIncrementId($orderIncrementId);

        if ($order->getStatus() == Order::STATE_PENDING_PAYMENT) {
            $comment = str_replace(
                self::PLACEHOLDER,
                $transactionId,
                $this->configHelper->getPaidMessage()
            );

            /** @var OrderRepositoryInterface|History $history */
            $history = $this->orderStatusHistoryInterfaceFactory->create();

            $history->setComment($comment);
            $history->setIsVisibleOnFront(1);
            $history->setIsCustomerNotified(1);
            $history->setStatus(Order::STATE_PROCESSING);

            $order->addStatusHistory($history);
            $order->setStatus(Order::STATE_PROCESSING);

            $this->orderRepository->save($order);
            $this->orderCommentSender->send($order, true, $comment);
        }
    }

    /**
     * Put order to canceled state or canceled status - it depends on admin
     * settings. Notify customer about it.
     *
     * @param string $orderIncrementId
     */
    public function fail(string $orderIncrementId)
    {
        $order = $this->orderFinder->getOrderByOrderIncrementId($orderIncrementId);

        if ($order->getStatus() == Order::STATE_PENDING_PAYMENT) {
            $comment = $this->configHelper->getUnpaidMessage();

            /** @var OrderRepositoryInterface|History $history */
            $history = $this->orderStatusHistoryInterfaceFactory->create();

            $history->setComment($comment);
            $history->setIsVisibleOnFront(1);
            $history->setIsCustomerNotified(1);
            $history->setStatus(Order::STATE_CANCELED);

            $order->addStatusHistory($history);

            $this->orderRepository->save($order);
            $this->orderCommentSender->send($order, true, $comment);

            if ($this->configHelper->orderCancelIsAllowed()) {
                $order->cancel();
            }

            $this->orderRepository->save($order);
        }
    }
}
