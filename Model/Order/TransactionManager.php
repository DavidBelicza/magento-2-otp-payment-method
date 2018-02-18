<?php
/**
 * Youama_OTP
 *
 * @author  David Belicza <87.bdavid@gmail.com>
 * @license David Belicza e.v. (http://youama.hu)
 */

declare(strict_types=1);

namespace Youama\OTP\Model\Order;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\TransactionInterface as OrderTransactionInterface;
use Magento\Sales\Api\TransactionRepositoryInterface as OrderTransactionRepository;
use Magento\Sales\Model\Order\Payment\Transaction as PaymentTransaction;
use Magento\Sales\Model\Order\Payment\TransactionFactory as OrderTransactionFactory;
use Youama\OTP\Api\OrderFinderInterface;
use Youama\OTP\Api\TransactionFinderInterface;
use Youama\OTP\Model\OrderFinder;
use Youama\OTP\Model\Transaction\XmlAdapter;

/**
 * Class TransactionManager
 *
 * This class manages the transactions. There are two transaction entities. The
 * first one is the Magento's transaction entity and the second one is the
 * module's unique transaction entity.
 *
 * @package Youama\OTP\Model\Order
 */
class TransactionManager
{
    /**
     * @var OrderInterface
     */
    private $order;

    /**
     * @var OrderTransactionRepository
     */
    private $orderTransactionRepository;

    /**
     * @var OrderTransactionFactory
     */
    private $magentoTransactionFactory;

    /**
     * @var OrderFinder
     */
    private $orderFinder;

    /**
     * @var TransactionFinderInterface
     */
    private $transactionFinder;

    /**
     * @var XmlAdapter
     */
    private $xmlAdapter;

    /**
     * @var int
     */
    private $transactionId;

    /**
     * TransactionManager constructor.
     *
     * @param OrderTransactionRepository $orderTransactionRepository
     * @param OrderTransactionFactory    $orderTransactionFactory
     * @param OrderFinderInterface       $orderFinder
     * @param TransactionFinderInterface $transactionFinder
     * @param XmlAdapter                 $xmlAdapter
     */
    public function __construct(
        OrderTransactionRepository $orderTransactionRepository,
        OrderTransactionFactory $orderTransactionFactory,
        OrderFinderInterface $orderFinder,
        TransactionFinderInterface $transactionFinder,
        XmlAdapter $xmlAdapter
    ) {
        $this->orderTransactionRepository = $orderTransactionRepository;
        $this->transactionFactory = $orderTransactionFactory;
        $this->orderFinder = $orderFinder;
        $this->transactionFinder = $transactionFinder;
        $this->xmlAdapter = $xmlAdapter;
    }

    /**
     * It retrieves the bank transaction ID.
     *
     * @return int
     */
    public function getTransactionId(): int
    {
        return (int)$this->transactionId;
    }

    /**
     * It adds order to the property.
     *
     * @param OrderInterface $order
     */
    public function addOrder(OrderInterface $order)
    {
        $this->order = $order;
    }

    /**
     * It loads the module's transaction entity and its sent XML request and
     * creates a Magento's transaction with the XML request.
     */
    public function createTransaction()
    {
        $orderTransaction = $this->transactionFactory->create();
        $transactionCount = $this->getOrderTransactionCollection()
            ->getSize();

        if ($transactionCount <= 0) {
            $transaction = $this->transactionFinder->findByOrderIncrementId($this->order->getIncrementId());
            $this->xmlAdapter->setXml($transaction->getShopFirstRequest());
            $data = $this->xmlAdapter->getAllDataAsArray('PAYMENT_REQUEST');

            $orderTransaction->setAdditionalInformation(PaymentTransaction::RAW_DETAILS, $data);
            $orderTransaction->setOrder($this->order);
            $orderTransaction->setIsClosed(0);
            $orderTransaction->setTxnType(OrderTransactionInterface::TYPE_ORDER);

            $this->orderTransactionRepository->save($orderTransaction);
        }
    }

    /**
     * It loads the module's transaction entity and its got XML response and
     * updates the Magento's transaction with the response data.
     */
    public function updateTransactionWithFirstResponse()
    {
        /** @var OrderTransactionInterface|AbstractModel $orderTransaction */
        $orderTransaction = $this->getOrderTransactionCollection()
            ->getFirstItem();
        $transaction = $this->transactionFinder->findByOrderIncrementId($this->order->getIncrementId());
        $this->xmlAdapter->setXml($transaction->getBankFirstResponse());

        $this->setAdditionalData(
            $orderTransaction,
            $this->xmlAdapter->getAllDataAsArray(XmlAdapter::PREFIX_FIRST_STEP)
        );

        $this->orderTransactionRepository->save($orderTransaction);
    }

    /**
     * It loads the module's transaction entity and its got XML response and
     * updates the Magento's transaction with the response data.
     *
     * It also set the transaction ID to the property and the Magento's
     * transaction entity when there is transaction ID. The transaction ID will
     * be the given authorization code from bank - when payment is successfully
     * done.
     *
     * @param bool $close Transaction will be closed when it is 1.
     */
    public function updateTransactionWithSecondResponse(bool $close)
    {
        /** @var OrderTransactionInterface|AbstractModel $orderTransaction */
        $orderTransaction = $this->getOrderTransactionCollection()
            ->getFirstItem();
        $transaction = $this->transactionFinder->findByOrderIncrementId($this->order->getIncrementId());

        $this->xmlAdapter->setXml($transaction->getBankSecondResponse());
        $data = $this->xmlAdapter->getAllDataAsArray(XmlAdapter::PREFIX_SECOND_STEP);

        $this->setAdditionalData($orderTransaction, $data);

        if (isset($data[XmlAdapter::XML_TAG_AUTHORIZATION_CODE])) {
            $orderTransaction->setTxnId($data[XmlAdapter::XML_TAG_AUTHORIZATION_CODE]);
            $this->transactionId = $data[XmlAdapter::XML_TAG_AUTHORIZATION_CODE];
        }

        $orderTransaction->setIsClosed((int) $close);

        $this->orderTransactionRepository->save($orderTransaction);
    }

    /**
     * It sets the extra Additional data to the Magento's transaction entity to
     * show them to the admin.
     *
     * @param OrderTransactionInterface $orderTransaction Transaction entity.
     * @param array                     $data             The given XML data
     *                                                    from the bank.
     */
    private function setAdditionalData(OrderTransactionInterface $orderTransaction, array $data)
    {
        $additionalData = [];
        $additionalDataFull = $orderTransaction->getAdditionalInformation();

        if (isset($additionalDataFull[PaymentTransaction::RAW_DETAILS])) {
            $additionalData = $additionalDataFull[PaymentTransaction::RAW_DETAILS];
        }

        $orderTransaction->setAdditionalInformation(
            PaymentTransaction::RAW_DETAILS,
            array_merge($additionalData, $data)
        );
    }

    /**
     * It retrieves the Magento's transaction collection by the current order
     * ID.
     *
     * @return AbstractCollection
     */
    private function getOrderTransactionCollection(): AbstractCollection
    {
        /** @var OrderTransactionInterface|AbstractModel $orderTransaction */
        $orderTransaction = $this->transactionFactory->create();

        return $orderTransaction->getCollection()
            ->addFieldToFilter(
                OrderTransactionInterface::ORDER_ID,
                ['eq' => $this->order->getId()]
            );
    }
}
