<?php
/**
 * Youama_OTP
 *
 * @author  David Belicza <87.bdavid@gmail.com>
 * @license David Belicza e.v. (http://youama.hu)
 */

declare(strict_types=1);

namespace Youama\OTP\Controller\Handler;

use Youama\OTP\Controller\AbstractAction;

/**
 * Class Payment
 *
 * @package Youama\OTP\Controller\Handler
 */
class Payment extends AbstractAction
{
    /**
     * Dispatch request
     *
     * @return null
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $followUp = $this->getRequest()->getParam('follow_up', false);

        if (!$followUp) {
            $asyncCalls = $this->asyncSendOrderToOtp($followUp);
            $asyncCalls .= $this->asyncSendCustomerToOtp($followUp);

            $this->getResponse()->setBody($asyncCalls);
        }

        return null;
    }
}
