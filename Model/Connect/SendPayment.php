<?php
/**
 * Youama_OTP
 *
 * @author  David Belicza <87.bdavid@gmail.com>
 * @license David Belicza e.v. (http://youama.hu)
 */

declare(strict_types=1);

namespace Youama\OTP\Model\Connect;

use Youama\OTP\Api\Data\OTPCodeInterface;
use Youama\OTP\Api\Data\PaymentStatusInterface;

/**
 * Class SendPayment
 *
 * Sends order payment data to OTP.
 *
 * @package Youama\OTP\Model\Connect
 */
class SendPayment extends AbstractPayment implements PaymentStatusInterface, OTPCodeInterface
{
    /**
     * Prepare data and pass them to sign to function sign.
     */
    public function signPayment()
    {
        $data = $this->getDataToSign(
            $this->getConfigHelper()->getPosId(),
            $this->getConfigHelper()->getTransactionId($this->getOrder()->getIncrementId()),
            (string)round($this->getOrder()->getGrandTotal()),
            $this->getConfigHelper()->getCurrencyCode()
        );

        $this->sign($data);
    }

    /**
     * Start the connection logic.
     *
     * @return \stdClass
     */
    public function send(): \stdClass
    {
        return $this->soapConnect(self::PAYMENT_REQUEST);
    }

    /**
     * Prepare the specific data and pass them into the function createOtpXML.
     */
    protected function createXml()
    {
        $variables = [
            'isClientCode'                 => $this->getConfigHelper()->getClientCode(),
            'isPOSID'                      => $this->getConfigHelper()->getPosId(),
            'isTransactionID'              => $this->getConfigHelper()->getTransactionId((string)$this->getOrder()->getIncrementId()),
            'isAmount'                     => round($this->getOrder()->getGrandTotal()),
            'isExchange'                   => $this->getConfigHelper()->getCurrencyCode(),
            'isLanguageCode'               => $this->getConfigHelper()->getLanguageCode(),
            'isCardPocketId'               => '',
            'isNameNeeded'                 => 'FALSE',
            'isCountryNeeded'              => 'FALSE',
            'isCountyNeeded'               => 'FALSE',
            'isSettlementNeeded'           => 'FALSE',
            'isZipcodeNeeded'              => 'FALSE',
            'isStreetNeeded'               => 'FALSE',
            'isMailAddressNeeded'          => 'FALSE',
            'isNarrationNeeded'            => 'FALSE',
            'isConsumerReceiptNeeded'      => 'FALSE',
            'isBackURL'                    => $this->getConfigHelper()->getResponseCallbackUrl((string)$this->getOrder()->getIncrementId()),
            'isShopComment'                => $this->getConfigHelper()->getShopComment(),
            'isConsumerRegistrationNeeded' => 'FALSE',
            'isClientSignature'            => $this->getSign()
        ];

        $this->createOtpXML(
            self::PAYMENT_REQUEST,
            $variables
        );
    }
}
