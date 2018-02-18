<?php
/**
 * Youama_OTP
 *
 * @author  David Belicza <87.bdavid@gmail.com>
 * @license David Belicza e.v. (http://youama.hu)
 */

declare(strict_types=1);

namespace Youama\OTP\Block;

use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template;
use Youama\OTP\Api\TransactionFinderInterface;
use Youama\OTP\Helper\Config;

/**
 * Class AuthorizationCode
 *
 * This block shows the OTP's Transaction Code about payment to Customer.
 *
 * @package Youama\OTP\Block
 */
class AuthorizationCode extends Template
{
    /**
     * @var bool
     */
    private $isYouamaOtpPaymentMethod = false;

    /**
     * @var string
     */
    private $authorizationCode;

    /**
     * TransactionCode constructor.
     *
     * @param Template\Context           $context
     * @param array                      $data
     * @param Session                    $checkoutSession
     * @param TransactionFinderInterface $transactionFinder
     */
    public function __construct(
        Template\Context $context,
        array $data,
        Session $checkoutSession,
        TransactionFinderInterface $transactionFinder
    ) {
        parent::__construct(
            $context,
            $data
        );

        $lastOrder = $checkoutSession->getLastRealOrder();
        $orderIncrementId = $lastOrder->getIncrementId();

        if ($orderIncrementId
            && $lastOrder->getPayment()->getMethod() === Config::METHOD_CODE
        ) {
            $this->isYouamaOtpPaymentMethod = true;
            $this->authorizationCode = $transactionFinder
                ->findAuthorizationCodeByOrderIncrementId($orderIncrementId);
        }
    }

    /**
     * This order at Success Page is paid via OTP or not.
     *
     * @return bool True when it is.
     */
    public function isYouamaOtpPaymentMethod(): bool
    {
        return $this->isYouamaOtpPaymentMethod;
    }

    /**
     * It retrieves the authorization code from OTP's payment.
     *
     * @return string
     */
    public function getAuthorizationCode(): string
    {
        return (string) $this->authorizationCode;
    }
}
