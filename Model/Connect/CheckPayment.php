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
 * Class CheckPayment
 *
 * @package Youama\OTP\Model\Connect
 */
class CheckPayment extends AbstractPayment implements PaymentStatusInterface, OTPCodeInterface
{
    /**
     * Prepare data and pass them to sign to function sign.
     */
    public function signPayment()
    {
        $data = $this->getDataToSign(
            $this->getConfigHelper()->getPosId(),
            $this->getConfigHelper()->getTransactionId($this->getOrder()->getIncrementId())
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
        return $this->soapConnect(self::VALIDATE_REQUEST);
    }

    /**
     * It retrieves the status of the payment from the given response.
     *
     * @param \stdClass $response
     *
     * @return int
     */
    public function getStatus(\stdClass $response): int
    {
        $xml = simplexml_load_string($response->result);

        if (!isset($xml->resultset->record->responsecode) || !isset($xml->resultset->record->state)) {
            return self::OTP_PAYMENT_TRY_AGAIN_LATER;
        }

        $responseCode = (string)$xml->resultset->record->responsecode;

        if (substr($responseCode, 0, 2) === self::SUCCESS_PAYMENT) {
            return self::OTP_PAYMENT_SUCCESS;
        } elseif (strlen($responseCode) >= 1) {
            return self::OTP_PAYMENT_FAILED;
        }

        return self::OTP_PAYMENT_TRY_AGAIN_LATER;
    }

    /**
     * Prepare the specific data and pass them into the function createOtpXML.
     */
    protected function createXml()
    {
        $variables = [
            'isClientCode'      => $this->getConfigHelper()->getClientCode(),
            'isPOSID'           => $this->getConfigHelper()->getPosId(),
            'isTransactionID'   => $this->getConfigHelper()->getTransactionId($this->getOrder()->getIncrementId()),
            'isClientSignature' => $this->getSign()
        ];

        $this->createOtpXML(
            self::VALIDATE_REQUEST,
            $variables
        );
    }
}
