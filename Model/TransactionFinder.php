<?php
/**
 * Youama_OTP
 *
 * @author  David Belicza <87.bdavid@gmail.com>
 * @license David Belicza e.v. (http://youama.hu)
 */

declare(strict_types=1);

namespace Youama\OTP\Model;

use Youama\OTP\Api\Data\PaymentStatusInterface;
use Youama\OTP\Api\Data\TransactionInterface;
use Youama\OTP\Api\TransactionFinderInterface;
use Youama\OTP\Model\Transaction\XmlAdapter;
use Youama\OTP\Model\TransactionFactory;

/**
 * Class TransactionFinder
 *
 * Service class to find the module's transaction entities.
 *
 * @package Youama\OTP\Model
 */
class TransactionFinder implements TransactionFinderInterface
{
    /**
     * @var TransactionFactory
     */
    private $transactionFactory;

    /**
     * @var XmlAdapter
     */
    private $xmlAdapter;

    /**
     * TransactionFinder constructor.
     *
     * @param TransactionFactory $transactionFactory
     * @param XmlAdapter         $xmlAdapter
     */
    public function __construct(
        TransactionFactory $transactionFactory,
        XmlAdapter $xmlAdapter
    ) {
        $this->transactionFactory = $transactionFactory;
        $this->xmlAdapter = $xmlAdapter;
    }

    /**
     * It retrieves the Transaction by the related Order's increment_id.
     *
     * @param string $orderIncrementId
     *
     * @return Transaction
     */
    public function findByOrderIncrementId(string $orderIncrementId): Transaction
    {
        /** @var Transaction $transaction */
        $transaction = $this->transactionFactory->create();

        $transaction
            ->getResource()
            ->load(
                $transaction,
                $orderIncrementId,
                TransactionInterface::ORDER_INCREMENT_ID
            );

        return $transaction;
    }

    /**
     * It retrieves the unfinished payments from Transaction table.
     *
     * @param int $storeId
     *
     * @return array
     */
    public function findNonFinishedPayments(int $storeId): array
    {
        return $this->transactionFactory
            ->create()
            ->getCollection()
            ->addFieldToSelect('*')
            ->addFieldToFilter(
                TransactionInterface::STORE_ID,
                ['eq' => $storeId]
            )
            ->addFieldToFilter(
                TransactionInterface::STATUS,
                ['eq' => PaymentStatusInterface::OTP_PAYMENT_TRY_AGAIN_LATER]
            )
            ->getItems();
    }

    /**
     * It retrieves the Transaction's authorization code by the related Order's
     * increment ID.
     *
     * @param string $orderIncrementId
     *
     * @return string
     */
    public function findAuthorizationCodeByOrderIncrementId(string $orderIncrementId): string
    {
        /** @var Transaction $transaction */
        $transaction = $this->transactionFactory->create();

        $transaction
            ->getResource()
            ->load(
                $transaction,
                $orderIncrementId,
                TransactionInterface::ORDER_INCREMENT_ID
            );

        $this->xmlAdapter->setXml(
            $transaction->getBankSecondResponse()
        );

        $data = $this->xmlAdapter->getAllDataAsArray(XmlAdapter::PREFIX_SECOND_STEP);

        if (isset($data[XmlAdapter::XML_TAG_AUTHORIZATION_CODE])) {
            return $data[XmlAdapter::XML_TAG_AUTHORIZATION_CODE];
        } else {
            return '';
        }
    }
}
