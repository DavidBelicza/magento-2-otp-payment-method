<?php
/**
 * Youama_OTP
 *
 * @author  David Belicza <87.bdavid@gmail.com>
 * @license David Belicza e.v. (http://youama.hu)
 */

declare(strict_types=1);

namespace Youama\OTP\Api;

use Youama\OTP\Model\Transaction;

/**
 * Interface TransactionFinderInterface
 *
 * @package Youama\OTP\Api
 */
interface TransactionFinderInterface
{
    /**
     * It retrieves the Transaction by the related Order's increment_id.
     *
     * @param string $orderIncrementId
     *
     * @return Transaction
     */
    public function findByOrderIncrementId(string $orderIncrementId): Transaction;

    /**
     * It retrieves the unfinished payments from Transaction table.
     *
     * @param int $storeId
     *
     * @return array
     */
    public function findNonFinishedPayments(int $storeId): array;

    /**
     * It retrieves the Transaction's authorization code by the related Order's
     * increment ID.
     *
     * @param string $orderIncrementId
     *
     * @return string
     */
    public function findAuthorizationCodeByOrderIncrementId(string $orderIncrementId): string;
}
