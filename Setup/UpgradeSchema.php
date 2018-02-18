<?php
/**
 * Youama_OTP
 *
 * @author  David Belicza <87.bdavid@gmail.com>
 * @license David Belicza e.v. (http://youama.hu)
 */

declare(strict_types=1);

namespace Youama\OTP\Setup;

use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Youama\OTP\Api\Data\ConfigInterface;
use Youama\OTP\Helper\Config as ConfigHelper;

/**
 * Class UpgradeSchema
 *
 * @package Youama\OTP\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var Config
     */
    private $resourceConfig;

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * UpgradeSchema constructor.
     *
     * @param Config       $resourceConfig
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        Config $resourceConfig,
        ConfigHelper $configHelper
    ) {
        $this->resourceConfig = $resourceConfig;
        $this->configHelper = $configHelper;
    }

    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $this->upgrade_1_0_1($setup);
        }
        
        $setup->endSetup();
    }

    /**
     * Version 1.0.1
     *
     * @param SchemaSetupInterface $setup
     */
    protected function upgrade_1_0_1(SchemaSetupInterface $setup)
    {
        $this->resourceConfig->saveConfig(
            'payment/' . $this->configHelper->getMethodCode() . '/' . ConfigInterface::TRANSACTION_ID_PREFIX,
            rand(10, 99) . time(),
            'default',
            0
        );
    }
}
