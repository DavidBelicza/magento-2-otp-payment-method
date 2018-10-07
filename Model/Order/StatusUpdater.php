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
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderCommentSender;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
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
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;

    /**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $transaction;

    protected $invoiceSender;

    protected $invoiceRepository;

    /**
     * StatusUpdater constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderFinderInterface $orderFinder
     * @param OrderStatusHistoryInterfaceFactory $orderStatusHistoryInterfaceFactory
     * @param OrderCommentSender $orderCommentSender
     * @param Config $configHelper
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Framework\DB\Transaction $transaction
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderFinderInterface $orderFinder,
        OrderStatusHistoryInterfaceFactory $orderStatusHistoryInterfaceFactory,
        OrderCommentSender $orderCommentSender,
        Config $configHelper,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        InvoiceSender $invoiceSender,
        InvoiceRepositoryInterface $invoiceRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderFinder = $orderFinder;
        $this->orderStatusHistoryInterfaceFactory = $orderStatusHistoryInterfaceFactory;
        $this->orderCommentSender = $orderCommentSender;
        $this->configHelper = $configHelper;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->invoiceSender = $invoiceSender;
        $this->invoiceRepository = $invoiceRepository;
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
     * @param Order $order
     */
    private function createInvoice(Order $order)
    {
        if ($order->canInvoice()) {
            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->register();
            $invoice->setState(Order\Invoice::STATE_PAID);
            $this->invoiceRepository->save($invoice);
            $transactionSave = $this->transaction->addObject(
                $invoice
            )->addObject(
                $invoice->getOrder()
            );
            $transactionSave->save();
            $this->invoiceSender->send($invoice);
            //send notification code
            /** @var OrderRepositoryInterface|History $history */
            $history = $this->orderStatusHistoryInterfaceFactory->create();

            $history->setComment(__('Notified customer about invoice #%1.', $invoice->getId()));
            $history->setIsVisibleOnFront(0);
            $history->setIsCustomerNotified(1);
            $history->setStatus(Order::STATE_COMPLETE);

            $order->addStatusHistory($history);
            $order->setStatus(Order::STATE_COMPLETE);
            $this->orderRepository->save($order);
        }
    }

    /**
     * Put order to processing, notify customer about it.
     *
     * @param string $orderIncrementId
     * @param int $transactionId
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


            /** Create Invoice if order was virtual */
            if ($order->getIsVirtual()) {
                $this->createInvoice($order);
            }
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
