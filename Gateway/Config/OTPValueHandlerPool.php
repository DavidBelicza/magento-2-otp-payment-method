<?php
/**
 * Youama_OTP
 *
 * @author  David Belicza <87.bdavid@gmail.com>
 * @license David Belicza e.v. (http://youama.hu)
 */

declare(strict_types=1);

namespace Youama\OTP\Gateway\Config;

use Magento\Framework\ObjectManager\TMapFactory;
use Magento\Payment\Gateway\Config\ValueHandlerPool;

/**
 * Class OTPValueHandlerPool
 *
 * @package Youama\OTP\Gateway\Config
 */
class OTPValueHandlerPool extends ValueHandlerPool
{
    /**
     * OTPValueHandlerPool constructor.
     *
     * @param TMapFactory $tmapFactory
     * @param array       $handlers
     */
    public function __construct(
        TMapFactory $tmapFactory,
        array $handlers
    ) {
        parent::__construct($tmapFactory, $handlers);
    }
}
