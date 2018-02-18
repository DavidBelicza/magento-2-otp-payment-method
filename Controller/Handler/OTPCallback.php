<?php
/**
 * Youama_OTP
 *
 * @author  David Belicza <87.bdavid@gmail.com>
 * @license David Belicza e.v. (http://youama.hu)
 */

declare(strict_types=1);

namespace Youama\OTP\Controller\Handler;

use Magento\Framework\App\ResponseInterface;
use Youama\OTP\Api\Data\PaymentStatusInterface;
use Youama\OTP\Controller\AbstractAction;

/**
 * Class OTPCallback
 *
 * @package Youama\OTP\Controller\Handler
 */
class OTPCallback extends AbstractAction
{
    /**
     * Dispatch request
     *
     * Customer arrives to this action after payment.
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $orderIncrementId = $this->getRequest()->getParam('order_increment_id', '');

        if ($this->getOtpMediator()->isTransactionCanBeUpdated($orderIncrementId)) {
            if ($this->getOtpMediator()->validatePayment($orderIncrementId)
                === PaymentStatusInterface::OTP_PAYMENT_SUCCESS
            ) {
                return $this->_redirect($this->getConfigHelper()->getSuccessPageUrl());
            } else {
                return $this->_redirect($this->getConfigHelper()->getFailPageUrl());
            }
        }

        return $this->_redirect('/*/*/*');
    }
}
