<?php
/**
 * Youama_OTP
 *
 * @author  David Belicza <87.bdavid@gmail.com>
 * @license David Belicza e.v. (http://youama.hu)
 */

declare(strict_types=1);

namespace Youama\OTP\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Store\Model\StoreManagerInterface;
use Youama\OTP\Api\Data\ConfigInterface;
use Youama\OTP\Gateway\Config\OTPValueHandlerPool;

/**
 * Class Config
 *
 * Configuration helper. The configuration red by the Value Handler.
 *
 * @package Youama\OTP\Helper
 */
class Config extends AbstractHelper
{
    const METHOD_CODE = 'youama_otp';

    /**
     * @var OTPValueHandlerPool
     */
    private $valueHandlerPool;

    /**
     * @var int
     */
    private $storeId;

    /**
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    /**
     * Config constructor.
     *
     * @param Context               $context
     * @param OTPValueHandlerPool   $valueHandlerPool
     * @param StoreManagerInterface $storeManager
     * @param ComponentRegistrar    $componentRegistrar
     */
    public function __construct(
        Context $context,
        OTPValueHandlerPool $valueHandlerPool,
        StoreManagerInterface $storeManager,
        ComponentRegistrar $componentRegistrar
    ) {
        parent::__construct($context);

        $this->valueHandlerPool = $valueHandlerPool;
        $this->storeId = $storeManager->getStore()->getId();
        $this->componentRegistrar = $componentRegistrar;
    }

    /**
     * @return string
     */
    public function getFrontendComment()
    {
        return nl2br($this->getValue(ConfigInterface::FRONTEND_COMMENT));
    }

    /**
     * It retrieves the Payment's method code.
     *
     * @return string
     */
    public function getMethodCode(): string
    {
        return self::METHOD_CODE;
    }

    /**
     * It retrieves the module action's URL what proceed the redirection to
     * the bank.
     *
     * @return string
     */
    public function getRequestUrl(): string
    {
        return $this->_urlBuilder->getUrl(
            $this->getValue('request_payment'),
            ['_secure' => true]
        );
    }

    /**
     * It retrieves the bank's url.
     *
     * @param string $transactionId It is from a generated prefix and the
     *                              Order's increment_id.
     *
     * @return string
     */
    public function getOtpBankUserInterfaceUrl(string $transactionId): string
    {
        $posId = $this->getEncodedPosId();

        return $this->getValue(ConfigInterface::PAYMENT_USER_INTERFACE)
            . "?posId={$posId}&azonosito={$transactionId}";
    }

    /**
     * This payment method is active or not.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool)$this->getValue(ConfigInterface::ACTIVE);
    }

    /**
     * It retrieves the Seller's POS ID.
     *
     * @return string
     */
    public function getPosId(): string
    {
        return $this->getValue(ConfigInterface::POS_ID);
    }

    /**
     * It retrieves the transaction ID prefix.
     *
     * @return string
     */
    public function getTransactionIdPrefix(): string
    {
        return $this->getValue(ConfigInterface::TRANSACTION_ID_PREFIX);
    }

    /**
     * It retrieves the transaction ID with prefix.
     *
     * @param string $orderIncrementId
     *
     * @return string
     */
    public function getTransactionId(string $orderIncrementId): string
    {
        return $this->getTransactionIdPrefix() . $orderIncrementId;
    }

    /**
     * It retrieves the POS ID prepared to pass in to URL.
     *
     * @return string
     */
    public function getEncodedPosId(): string
    {
        return urlencode($this->getValue(ConfigInterface::POS_ID));
    }

    /**
     * It retrieves the Currency Code. This will be used on the bank interface.
     *
     * @return string
     */
    public function getCurrencyCode(): string
    {
        return $this->getValue(ConfigInterface::CURRENCY_CODE);
    }

    /**
     * It retrieves the client's code.
     *
     * @return string
     */
    public function getClientCode(): string
    {
        return $this->getValue(ConfigInterface::CLIENT_CODE);
    }

    /**
     * It retrieves the language code. This will be used on the bank interface.
     *
     * @return string
     */
    public function getLanguageCode(): string
    {
        return $this->getValue(ConfigInterface::LANGUAGE_CODE);
    }

    /**
     * It retrieves the Shop Comment. This will be used as description on the
     * bank interface.
     *
     * @return string
     */
    public function getShopComment(): string
    {
        return $this->getValue(ConfigInterface::SHOP_COMMENT);
    }

    /**
     * It retrieves the paid message for notify Customer.
     *
     * @return string
     */
    public function getPaidMessage(): string
    {
        return $this->getValue(ConfigInterface::PAID_MESSAGE);
    }

    /**
     * It retrieves the unpaid message for notify Customer.
     *
     * @return string
     */
    public function getUnpaidMessage(): string
    {
        return $this->getValue(ConfigInterface::UNPAID_MESSAGE);
    }

    /**
     * It retrieves the SOAP interface URL.
     *
     * @return string
     */
    public function getPaymentInterfaceUrl(): string
    {
        return $this->getValue(ConfigInterface::PAYMENT_INTERFACE);
    }

    /**
     *
     * @return bool It is true when allowed.
     */
    public function orderCancelIsAllowed(): bool
    {
        return (bool)$this->getValue(ConfigInterface::ORDER_CANCEL_IS_ALLOWED);
    }

    /**
     * It retrieves the success page url. User will arrive to here.
     *
     * @return string
     */
    public function getSuccessPageUrl(): string
    {
        return $url = $this->_urlBuilder->getUrl(
            $this->getValue(ConfigInterface::CHECKOUT_SUCCESS_URL),
            ['_secure' => true]
        );
    }

    /**
     * It retrieves the fail page url. User will arrive to here.
     *
     * @return string
     */
    public function getFailPageUrl(): string
    {
        return $url = $this->_urlBuilder->getUrl(
            $this->getValue(ConfigInterface::CHECKOUT_FAIL_URL),
            ['_secure' => true]
        );
    }

    /**
     * It retrieves the module's action what proceed the order sending to the
     * bank.
     *
     * @param bool $followUp
     *
     * @return string
     */
    public function getSendOrderUrl(bool $followUp): string
    {
        $params = ['_secure' => true];

        if ($followUp) {
            $params['query']['follow_up'] = true;
        }

        return $this->_urlBuilder->getUrl(
            $this->getValue(ConfigInterface::REQUEST_SEND_ORDER),
            $params
        );
    }

    /**
     * It retrieves the callback URL what proceed the redirection to back to
     * the shop.
     *
     * @param string $orderIncrementId
     *
     * @return string
     */
    public function getResponseCallbackUrl(string $orderIncrementId): string
    {
        return $this->_urlBuilder->getUrl(
            $this->getValue(ConfigInterface::RESPONSE_CALLBACK),
            [
                '_secure' => true,
                'NOCACHE' => time() . rand(111111,999999),
                'NO_CACHE' => time() . rand(111111,999999),
                'order_increment_id' => $orderIncrementId
            ]
        );
    }

    /**
     * It retrieves the path of the private key. When private key is not given
     * by admin then the default demo version will be used instead.
     *
     * @return string
     */
    public function getPrivateKeyPath(): string
    {
        $privateKeyPath = (string)$this->getValue(ConfigInterface::PRIVATE_KEY_PATH);

        if (strlen(trim($privateKeyPath)) <= 0) {
            $privateKeyPath = $this->componentRegistrar
                    ->getPath(ComponentRegistrar::MODULE, 'Youama_OTP')
                . DIRECTORY_SEPARATOR . 'key'
                . DIRECTORY_SEPARATOR . 'demoPrivateKey.pem';
        } else {
            $privateKeyPath = BP . DIRECTORY_SEPARATOR . $privateKeyPath;
        }

        return $privateKeyPath;
    }

    /**
     * It retrieves the values from the Value Handler.
     *
     * @param string   $field
     * @param int|null $storeId
     *
     * @return string
     */
    public function getValue(string $field, int $storeId = null): string
    {
        $handler = $this->valueHandlerPool->get($field);

        $subject = [
            'field' => $field
        ];

        return (string)$handler->handle($subject, $storeId ?: $this->storeId);
    }
}
