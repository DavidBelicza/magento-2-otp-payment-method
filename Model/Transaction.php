<?php
/**
 * Youama_OTP
 *
 * @author  David Belicza <87.bdavid@gmail.com>
 * @license David Belicza e.v. (http://youama.hu)
 */

declare(strict_types=1);

namespace Youama\OTP\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Youama\OTP\Api\Data\TransactionInterface;

/**
 * Class Transaction
 *
 * The module's transaction entity to store the payment process status and XML
 * requests and responses.
 *
 * @package Youama\OTP\Model
 */
class Transaction extends AbstractModel implements TransactionInterface, IdentityInterface
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Youama\OTP\Model\ResourceModel\Transaction::class);
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities(): array
    {
        return [
            TransactionInterface::ENTITY_TABLE . '_' . $this->getId()
        ];
    }

    /**
     * @param int $id
     *
     * @return void
     * @see TransactionInterface
     */
    public function setTransactionId(int $id)
    {
        $this->setData(TransactionInterface::ENTITY_ID, $id);
    }

    /**
     * @return int
     * @see TransactionInterface
     */
    public function getTransactionId(): int
    {
        return (int)$this->getData(TransactionInterface::ENTITY_ID);
    }

    /**
     * @param int $storeId
     * @see TransactionInterface
     */
    public function setStoreId(int $storeId)
    {
        $this->setData(TransactionInterface::STORE_ID, $storeId);
    }

    /**
     * @return int
     * @see TransactionInterface
     */
    public function getStoreId(): int
    {
        return (int)$this->getData(TransactionInterface::STORE_ID);
    }

    /**
     * @param int $id
     * @see TransactionInterface
     */
    public function setIsCron(int $id)
    {
        $this->setData(TransactionInterface::IS_CRON, $id);
    }

    /**
     * @return int
     * @see TransactionInterface
     */
    public function getIsCron(): int
    {
        return (int)$this->getData(TransactionInterface::IS_CRON);
    }

    /**
     * @param string $orderIncrementId
     * @see TransactionInterface
     */
    public function setOrderIncrementId(string $orderIncrementId)
    {
        $this->setData(TransactionInterface::ORDER_INCREMENT_ID, $orderIncrementId);
    }

    /**
     * @return string
     * @see TransactionInterface
     */
    public function getOrderIncrementId(): string
    {
        return $this->getData(TransactionInterface::ORDER_INCREMENT_ID);
    }

    /**
     * @param string $shopFirstRequest
     * @see TransactionInterface
     */
    public function setShopFirstRequest(string $shopFirstRequest)
    {
        $this->setData(TransactionInterface::SHOP_FIRST_REQUEST, $shopFirstRequest);
    }

    /**
     * @return string
     * @see TransactionInterface
     */
    public function getShopFirstRequest(): string
    {
        return $this->getData(TransactionInterface::SHOP_FIRST_REQUEST);
    }

    /**
     * @param string $bankFirstResponse
     * @see TransactionInterface
     */
    public function setBankFirstResponse(string $bankFirstResponse)
    {
        $this->setData(TransactionInterface::BANK_FIRST_RESPONSE, $bankFirstResponse);
    }

    /**
     * @return string
     * @see TransactionInterface
     */
    public function getBankFirstResponse(): string
    {
        return $this->getData(TransactionInterface::BANK_FIRST_RESPONSE);
    }

    /**
     * @param string $bankSecondResponse
     * @see TransactionInterface
     */
    public function setBankSecondResponse(string $bankSecondResponse)
    {
        $this->setData(TransactionInterface::BANK_SECOND_RESPONSE, $bankSecondResponse);
    }

    /**
     * @return string
     * @see TransactionInterface
     */
    public function getBankSecondResponse(): string
    {
        return $this->getData(TransactionInterface::BANK_SECOND_RESPONSE);
    }

    /**
     * @param int $status
     * @see TransactionInterface
     */
    public function setStatus(int $status)
    {
        $this->setData(TransactionInterface::STATUS, $status);
    }

    /**
     * @return int
     * @see TransactionInterface
     */
    public function getStatus(): int
    {
        return (int)$this->getData(TransactionInterface::STATUS);
    }
}
