<?php
/**
 * Youama_OTP
 *
 * @author  David Belicza <87.bdavid@gmail.com>
 * @license David Belicza e.v. (http://youama.hu)
 */

declare(strict_types=1);

namespace Youama\OTP\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Youama\OTP\Helper\Config;

/**
 * Class PaymentConfigProvider
 *
 * Provider class to provide one way bridge from backend to frontend of the
 * Checkout. Dependencies are injected from XML.
 *
 * @package Youama\OTP\Model
 */
class PaymentConfigProvider implements ConfigProviderInterface
{
    /**
     * @var string
     */
    private $methodCode;

    /**
     * @var Config
     */
    private $configHelper;

    /**
     * PaymentConfigProvider constructor.
     *
     * @param Config $config
     * @param array  $data
     */
    public function __construct(
        Config $config,
        array $data
    ) {
        $this->methodCode = $data['method_code'];
        $this->configHelper = $config;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return ['payment' => [
            $this->methodCode => [
                'is_active'    => $this->configHelper->isActive(),
                'is_available' => true,
                'request_url'  => $this->configHelper->getRequestUrl()
            ],
        ]];
    }
}
