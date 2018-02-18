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
 * Class SendOrder
 *
 * @package Youama\OTP\Controller\Handler
 */
class SendOrder extends AbstractAction
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
            $this->getOtpMediator()->sendLastOrderFromSessionToOTP();
        }

        return null;
    }
}
