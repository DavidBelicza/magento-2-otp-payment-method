<?php
/**
 * Youama_OTP
 *
 * @author  David Belicza <87.bdavid@gmail.com>
 * @license David Belicza e.v. (http://youama.hu)
 */

declare(strict_types=1);

namespace Youama\OTP\Api;

/**
 * Interface TransactionRepositoryInterface
 *
 * @package Youama\OTP\Api
 */
interface TransactionRepositoryInterface
{
    /**
     * It creates the entity and saves the XML request.
     *
     * @param string $orderIncrementId
     * @param string $xml
     * @param int    $storeId
     */
    public function addShopFirstRequest(string $orderIncrementId, string $xml, int $storeId = null);

    /**
     * It updates the entity with the XML response.
     *
     * @param string $orderIncrementId
     * @param string $data
     */
    public function addBankFirstResponse(string $orderIncrementId, string $data);

    /**
     * It updates the entity with the second XML response.
     *
     * @param string $orderIncrementId
     * @param string $data
     */
    public function addBankSecondResponse(string $orderIncrementId, string $data);

    /**
     * It updated the entity status.
     *
     * @param string $orderIncrementId
     * @param int    $status
     * @param int    $isCron
     */
    public function updateStatus(string $orderIncrementId, int $status, int $isCron);
}
