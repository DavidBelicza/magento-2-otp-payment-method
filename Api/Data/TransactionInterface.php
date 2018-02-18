<?php
/**
 * Youama_OTP
 *
 * @author  David Belicza <87.bdavid@gmail.com>
 * @license David Belicza e.v. (http://youama.hu)
 */

declare(strict_types = 1);

namespace Youama\OTP\Api\Data;

/**
 * Interface TransactionInterface
 *
 * @package Youama\OTP\Api\Data
 */
interface TransactionInterface
{
    /**
     * Entity table name.
     */
    const ENTITY_TABLE = 'youama_otp_transaction';

    /**
     * Entity ID.
     */
    const ENTITY_ID = 'transaction_id';

    /**
     * ID of the store where order has been placed.
     */
    const STORE_ID = 'store_id';

    /**
     * Transaction status checked by Cron or nor.
     */
    const IS_CRON = 'is_cron';

    /**
     * The Magento's readable Order ID.
     */
    const ORDER_INCREMENT_ID = 'order_increment_id';

    /**
     * XML request by from Magento to OTP about order.
     */
    const SHOP_FIRST_REQUEST = 'shop_first_request';

    /**
     * Bank's first response to Magento about get order.
     */
    const BANK_FIRST_RESPONSE = 'bank_first_response';

    /**
     * Bank's second response about the payment.
     */
    const BANK_SECOND_RESPONSE = 'bank_second_response';

    /**
     * Status of the transaction.
     */
    const STATUS = 'status';

    /**
     * Record created at this time.
     */
    const CREATED = 'created';

    /**
     * Record updated at this time.
     */
    const UPDATED = 'updated';

    /**
     * @param int $id
     */
    public function setTransactionId(int $id);

    /**
     * @return int
     */
    public function getTransactionId(): int;

    /**
     * @param int $storeId
     */
    public function setStoreId(int $storeId);

    /**
     * @return int
     */
    public function getStoreId(): int;

    /**
     * @param int $id
     */
    public function setIsCron(int $id);

    /**
     * @return int
     */
    public function getIsCron(): int;

    /**
     * @param string $orderIncrementId
     */
    public function setOrderIncrementId(string $orderIncrementId);

    /**
     * @return string
     */
    public function getOrderIncrementId(): string;

    /**
     * @param string $shopFirstRequest
     */
    public function setShopFirstRequest(string $shopFirstRequest);

    /**
     * @return string
     */
    public function getShopFirstRequest(): string;

    /**
     * @param string $bankFirstResponse
     */
    public function setBankFirstResponse(string $bankFirstResponse);

    /**
     * @return string
     */
    public function getBankFirstResponse(): string;

    /**
     * @param string $bankSecondResponse
     */
    public function setBankSecondResponse(string $bankSecondResponse);

    /**
     * @return string
     */
    public function getBankSecondResponse(): string;

    /**
     * @param int $status
     */
    public function setStatus(int $status);

    /**
     * @return int
     */
    public function getStatus(): int;
}
