<?php
/**
 * Youama_OTP
 *
 * @author  David Belicza <87.bdavid@gmail.com>
 * @license David Belicza e.v. (http://youama.hu)
 */

namespace Youama\OTP\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Youama\OTP\Api\Data\TransactionInterface;

/**
 * Class InstallSchema
 *
 * @package Youama\OTP\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $transactionTable = $setup->getConnection()
            ->newTable($setup->getTable(TransactionInterface::ENTITY_TABLE))
            ->addColumn(
                TransactionInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true
                ],
                'Transaction ID'

            )->addColumn(
                TransactionInterface::STORE_ID,
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ],
                'Store ID'

            )->addColumn(
                TransactionInterface::IS_CRON,
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false,
                    'default'  => 0
                ],
                'Is Cron'

            )->addColumn(
                TransactionInterface::ORDER_INCREMENT_ID,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Order Increment ID'

            )->addColumn(
                TransactionInterface::SHOP_FIRST_REQUEST,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Shop First Request'

            )->addColumn(
                TransactionInterface::BANK_FIRST_RESPONSE,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Bank First Response'

            )->addColumn(
                TransactionInterface::BANK_SECOND_RESPONSE,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Bank Second Response'

            )->addColumn(
                TransactionInterface::STATUS,
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false,
                    'default'  => 0
                ],
                'Status'

            )->addColumn(
                TransactionInterface::STATUS,
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false,
                    'default'  => 0
                ],
                'Status'

            )->addColumn(
                TransactionInterface::CREATED,
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default'  => Table::TIMESTAMP_INIT
                ],
                'Created at'

            )->addColumn(
                TransactionInterface::UPDATED,
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default'  => Table::TIMESTAMP_INIT_UPDATE
                ],
                'Updated at'

            )->setComment(
                'Youama OTP Transaction Table'
            );

        $setup->getConnection()->createTable($transactionTable);

        $setup->endSetup();
    }
}
