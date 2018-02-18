<?php
/**
 * Youama_OTP
 *
 * @author  David Belicza <87.bdavid@gmail.com>
 * @license David Belicza e.v. (http://youama.hu)
 */

declare(strict_types=1);

namespace Youama\OTP\Model;

use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Youama\OTP\Api\TransactionFinderInterface;
use Youama\OTP\Helper\Config;

/**
 * Class Updater
 *
 * This class validates lost transactions. When Customer does not come back to
 * the shop after payment the Updater checks the transaction status at the bank
 * and finish the payment process on Magento's side.
 *
 * @package Youama\OTP\Model
 */
class Updater
{
    /**
     * @var Config
     */
    private $configHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var TransactionFinder
     */
    private $transactionFinder;

    /**
     * @var OTPMediator
     */
    private $otpMediator;

    /**
     * Updater constructor.
     *
     * @param Config                     $configHelper
     * @param StoreManagerInterface      $storeManager
     * @param TransactionFinderInterface $transactionFinder
     * @param OTPMediator                  $otpMediator
     */
    public function __construct(
        Config $configHelper,
        StoreManagerInterface $storeManager,
        TransactionFinderInterface $transactionFinder,
        OTPMediator $otpMediator
    ) {
        $this->configHelper = $configHelper;
        $this->storeManager = $storeManager;
        $this->transactionFinder = $transactionFinder;
        $this->otpMediator = $otpMediator;
    }

    /**
     * Update orders for each store.
     *
     * @param bool $isCron
     */
    public function updateOrders(bool $isCron)
    {
        $this->otpMediator->setIsCron($isCron);

        if ($this->configHelper->isActive()) {
            $stores = $this->storeManager->getStores();

            foreach ($stores as $store) {
                $this->updateOrdersForSpecifiedStore($store);
            }
        }
    }

    /**
     * Update order by the given store.
     *
     * @param StoreInterface $store
     */
    protected function updateOrdersForSpecifiedStore(StoreInterface $store)
    {
        $this->storeManager->setCurrentStore($store->getId());
        $transactionCollection = $this->transactionFinder->findNonFinishedPayments(
            (int)$store->getId()
        );

        if (!empty($transactionCollection)) {
            /** @var Transaction $transaction */
            foreach ($transactionCollection as $transaction) {
                $this->otpMediator->validatePayment($transaction->getOrderIncrementId());
            }
        }
    }
}
