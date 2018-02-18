<?php
/**
 * Youama_OTP
 *
 * @author  David Belicza <87.bdavid@gmail.com>
 * @license David Belicza e.v. (http://youama.hu)
 */

declare(strict_types=1);

namespace Youama\OTP\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Youama\OTP\Api\Data\TransactionInterface;

/**
 * Class Transaction
 *
 * @package Youama\OTP\Model\ResourceModel
 */
class Transaction extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            TransactionInterface::ENTITY_TABLE,
            TransactionInterface::ENTITY_ID
        );
    }
}
