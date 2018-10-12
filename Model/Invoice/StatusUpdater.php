<?php
/**
 * Youama_OTP
 *
 * @author  David Belicza <87.bdavid@gmail.com>
 * @license David Belicza e.v. (http://youama.hu)
 */

declare(strict_types=1);

namespace Youama\OTP\Model\Invoice;

use Magento\Sales\Api\Data\OrderStatusHistoryInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Model\Order\Status\History;
use Youama\OTP\Helper\Config;

/**
 * Class StatusUpdater
 *
 * This class manage to create an invoice for the order if all the items are virtual and the auto-invoicing option is
 * enabled in the admin
 * History and Comment changes.
 *
 * @package Youama\OTP\Model\Order
 */
class StatusUpdater
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderStatusHistoryInterfaceFactory
     */
    private $orderStatusHistoryInterfaceFactory;

    /**
     * @var Config
     */
    private $configHelper;

    /**
     * @var InvoiceService
     */
    protected $invoiceService;

    /**
     * @var Transaction
     */
    protected $transaction;

    /**
     * @var InvoiceSender
     */
    protected $invoiceSender;

    /**
     * @var InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * StatusUpdater constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderStatusHistoryInterfaceFactory $orderStatusHistoryInterfaceFactory
     * @param Config $configHelper
     * @param InvoiceService $invoiceService
     * @param Transaction $transaction
     * @param InvoiceSender $invoiceSender
     * @param InvoiceRepositoryInterface $invoiceRepository
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderStatusHistoryInterfaceFactory $orderStatusHistoryInterfaceFactory,
        Config $configHelper,
        InvoiceService $invoiceService,
        Transaction $transaction,
        InvoiceSender $invoiceSender,
        InvoiceRepositoryInterface $invoiceRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderStatusHistoryInterfaceFactory = $orderStatusHistoryInterfaceFactory;
        $this->configHelper = $configHelper;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->invoiceSender = $invoiceSender;
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * Determinate the order is virtual and the auto invoice is setting is enabled
     * @param Order $order
     * @return bool
     */
    private function canInvoice(Order $order): bool
    {
        return (
            $order->getIsVirtual()
            && $order->canInvoice()
            && $this->configHelper->getValue('can_auto_invoice_virtual')
        );
    }

    /**
     * @param Order $order
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createInvoice(Order $order)
    {
        if ($this->canInvoice($order)) {
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
}
