<?php
/**
 * Youama_OTP
 *
 * @author  David Belicza <87.bdavid@gmail.com>
 * @license David Belicza e.v. (http://youama.hu)
 */

declare(strict_types=1);

namespace Youama\OTP\Controller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Youama\OTP\Helper\Config;
use Youama\OTP\Model\OTPMediator;

/**
 * Class AbstractAction
 *
 * OTP requires to connect to bank by two threads in the same time, so two
 * processes have to send requests to the bank. These work by Ajax while the
 * main/parent process still untouched from the bank.
 *
 * @package Youama\OTP\Controller
 */
abstract class AbstractAction extends Action
{
    /**
     * @var OTPMediator
     */
    private $otpMediator;

    /**
     * @var Config
     */
    private $configHelper;

    /**
     * AbstractAction constructor.
     *
     * @param Context   $context
     * @param OTPMediator $OTPMediator
     * @param Config    $configHelper
     */
    public function __construct(
        Context $context,
        OTPMediator $OTPMediator,
        Config $configHelper
    ) {
        parent::__construct($context);

        $this->otpMediator = $OTPMediator;
        $this->configHelper = $configHelper;
    }

    /**
     * @return OTPMediator
     */
    protected function getOtpMediator(): OTPMediator
    {
        return $this->otpMediator;
    }

    /**
     * @return Config
     */
    protected function getConfigHelper(): Config
    {
        return $this->configHelper;
    }

    /**
     * It sends the Customer to the bank's user interface.
     *
     * @param bool   $followUp When it is true it will use the given URL.
     * @param string $url
     *
     * @return string
     */
    protected function asyncSendCustomerToOtp(bool $followUp, string $url = '')
    {
        if (!$followUp) {
            $url = $this->otpMediator->createCustomerWithLastOrderToOTPUrl();
        }

        return '<script>setTimeout(function() { window.location = "' . $url .
            '"; }, 1000);</script>';
    }

    /**
     * It sends the data of the Order to the bank in the background while
     * Customer arrives to the bank user interface.
     *
     * @param bool $followUp
     *
     * @return string
     */
    protected function asyncSendOrderToOtp(bool $followUp)
    {
        return '<script>
            var ajaxRequest = new XMLHttpRequest();
            ajaxRequest.open("POST", "' . $this->configHelper->getSendOrderUrl($followUp) . '", true);
            ajaxRequest.send();
        </script>';
    }
}
