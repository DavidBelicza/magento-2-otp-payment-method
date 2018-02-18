<?php
/**
 * Youama_OTP
 *
 * @author  David Belicza <87.bdavid@gmail.com>
 * @license David Belicza e.v. (http://youama.hu)
 */

declare(strict_types=1);

namespace Youama\OTP\Model;

use Magento\Store\Model\StoreManagerInterface;
use Youama\OTP\Api\Data\PaymentStatusInterface;
use Youama\OTP\Api\TransactionRepositoryInterface;
use Youama\OTP\Model\TransactionFactory;

/**
 * Class TransactionRepository
 *
 * Service class to write module's transaction entities.
 *
 * @package Youama\OTP\Model
 */
class TransactionRepository implements TransactionRepositoryInterface
{
    /**
     * @var TransactionFinder
     */
    private $transactionFinder;

    /**
     * @var TransactionFactory
     */
    private $transactionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * TransactionRepository constructor.
     *
     * @param TransactionFinder     $transactionFinder
     * @param TransactionFactory    $transactionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        TransactionFinder $transactionFinder,
        TransactionFactory $transactionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->transactionFactory = $transactionFactory;
        $this->transactionFinder = $transactionFinder;
        $this->storeManager = $storeManager;
    }

    /**
     * It creates the entity and saves the XML request.
     *
     * @param string $orderIncrementId
     * @param string $xml
     * @param int    $storeId
     */
    public function addShopFirstRequest(string $orderIncrementId, string $xml, int $storeId = null)
    {
        $storeId = ($storeId === null) ? (int) $this->storeManager->getStore()->getId() : $storeId;

        /** @var Transaction $transaction */
        $transaction = $this->transactionFactory->create();

        $transaction->setOrderIncrementId($orderIncrementId);
        $transaction->setShopFirstRequest($xml);
        $transaction->setStoreId($storeId);
        $transaction->setStatus(PaymentStatusInterface::OTP_PAYMENT_TRY_AGAIN_LATER);
        $transaction->getResource()->save($transaction);
    }

    /**
     * It updates the entity with the XML response.
     *
     * @param string $orderIncrementId
     * @param string $data
     */
    public function addBankFirstResponse(string $orderIncrementId, string $data)
    {
        /** @var Transaction $transaction */
        $transaction = $this->transactionFinder
            ->findByOrderIncrementId($orderIncrementId);

        $transaction->setBankFirstResponse($data);
        $transaction->getResource()->save($transaction);
    }

    /**
     * It updates the entity with the second XML response.
     *
     * @param string $orderIncrementId
     * @param string $data
     */
    public function addBankSecondResponse(string $orderIncrementId, string $data)
    {
        /** @var Transaction $transaction */
        $transaction = $this->transactionFinder
            ->findByOrderIncrementId($orderIncrementId);

        $transaction->setBankSecondResponse($data);
        $transaction->getResource()->save($transaction);
    }

    /**
     * It updated the entity status.
     *
     * @param string $orderIncrementId
     * @param int    $status
     * @param int    $isCron
     */
    public function updateStatus(string $orderIncrementId, int $status, int $isCron)
    {
        /** @var Transaction $transaction */
        $transaction = $this->transactionFinder
            ->findByOrderIncrementId($orderIncrementId);

        $transaction->setStatus($status);
        $transaction->setIsCron($isCron);
        $transaction->getResource()->save($transaction);
    }
}
