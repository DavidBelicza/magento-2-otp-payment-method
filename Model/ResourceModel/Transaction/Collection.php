<?php
/**
 * Youama_OTP
 *
 * @author  David Belicza <87.bdavid@gmail.com>
 * @license David Belicza e.v. (http://youama.hu)
 */

declare(strict_types=1);

namespace Youama\OTP\Model\ResourceModel\Transaction;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Youama\OTP\Api\Data\TransactionInterface;

/**
 * Class Collection
 *
 * @package Youama\OTP\Model\ResourceModel\Transaction
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = TransactionInterface::ENTITY_ID;

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Youama\OTP\Model\Transaction::class,
            \Youama\OTP\Model\ResourceModel\Transaction::class
        );
    }
}
