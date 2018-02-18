<?php
/**
 * Youama_OTP
 *
 * @author  David Belicza <87.bdavid@gmail.com>
 * @license David Belicza e.v. (http://youama.hu)
 */

declare(strict_types=1);

namespace Youama\OTP\Model\Connect;

use Magento\Framework\DomDocument\DomDocumentFactory;
use Magento\Framework\Webapi\Soap\ClientFactory;
use Magento\Sales\Model\Order;
use Youama\OTP\Exception\NoSuchOrderException;
use Youama\OTP\Helper\Config;

/**
 * Class AbstractPayment
 *
 * @package Youama\OTP\Model\Connect
 */
abstract class AbstractPayment
{
    /**
     * @var Config
     */
    private $configHelper;

    /**
     * @var DomDocumentFactory
     */
    private $domDocumentFactory;

    /**
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var string
     */
    private $sign;

    /**
     * @var string
     */
    private $xml;

    /**
     * AbstractPayment constructor.
     *
     * @param Config             $configHelper
     * @param DomDocumentFactory $documentFactory
     * @param ClientFactory      $clientFactory
     */
    public function __construct(
        Config $configHelper,
        DomDocumentFactory $documentFactory,
        ClientFactory $clientFactory
    ) {
        $this->configHelper = $configHelper;
        $this->domDocumentFactory = $documentFactory;
        $this->clientFactory = $clientFactory;
    }

    /**
     * Prepare data and pass them to sign to function sign.
     */
    abstract public function signPayment();

    /**
     * Start the connection logic.
     *
     * @return \stdClass
     */
    abstract public function send(): \stdClass;

    /**
     * Prepare the specific data and pass them into the function createOtpXML.
     */
    abstract protected function createXml();

    /**
     * It adds the Order to the property. Order has to be persistent, loaded
     * from resource.
     *
     * @param Order $order
     *
     * @throws NoSuchOrderException
     */
    public function addOrder(Order $order)
    {
        $this->order = $order;

        if (strlen((string)$this->order->getIncrementId()) <= 0) {
            throw new NoSuchOrderException('Order must be persistent object');
        }
    }

    /**
     * It retrieves the created XML as a string.
     *
     * @return string
     */
    public function getCreatedXml(): string
    {
        return $this->xml;
    }

    /**
     * It compose the XML.
     */
    public function composeXmlData()
    {
        $this->createXml();
    }

    /**
     * @return Order
     */
    protected function getOrder(): Order
    {
        return $this->order;
    }

    /**
     * @return Config
     */
    protected function getConfigHelper(): Config
    {
        return $this->configHelper;
    }

    /**
     * @return string
     */
    protected function getSign(): string
    {
        return $this->sign;
    }

    /**
     * It makes the connection to the bank and retrieves the whole result.
     *
     * @param string $request
     *
     * @return \stdClass
     */
    protected function soapConnect(string $request): \stdClass
    {
        $soap = $this->clientFactory->create(
            $this->configHelper->getPaymentInterfaceUrl()
        );

        return $soap->startWorkflowSynch(
            $request,
            $this->xml
        );
    }

    /**
     * It sign data with MD5 and convert it from binary to hexadecimal.
     *
     * @param string $dataToSign
     */
    protected function sign(string $dataToSign)
    {
        $filePath = $this->configHelper->getPrivateKeyPath();
        $privateKeyFile = fopen($filePath, 'r');
        $privateKey = fread($privateKeyFile, filesize($filePath));
        fclose($privateKeyFile);

        $pKeyId = openssl_get_privatekey($privateKey, '');
        openssl_sign(
            $dataToSign,
            $signature,
            $pKeyId,
            OPENSSL_ALGO_MD5
        );

        $sign = bin2hex($signature);
        $signature = false;

        $this->sign = $sign;
    }

    /**
     * It retrieves the data to sign.
     *
     * @param string $param1
     * @param string $param2
     * @param string $param3
     * @param string $param4
     * @param string $param5
     *
     * @return string
     */
    protected function getDataToSign(
        string $param1 = '',
        string $param2 = '',
        string $param3 = '',
        string $param4 = '',
        string $param5 = ''
    ): string {
        return $param1 . '|' . $param2 . '|' . $param3 . '|' . $param4 . '|' . $param5;
    }

    /**
     * It creates the XML by the given parameters and save it into the property
     * as a string.
     *
     * @param string $templateName
     * @param array  $variables
     */
    protected function createOtpXML(string $templateName, array $variables)
    {
        $domTree = $this->domDocumentFactory->create();
        $domTree->encoding = 'UTF-8';
        $domTree->version = '1.0';

        $xmlRoot = $domTree->createElement('StartWorkflow');
        $xmlRoot = $domTree->appendChild($xmlRoot);

        $templateNode = $domTree->createElement(
            'TemplateName',
            $templateName
        );

        $xmlRoot->appendChild($templateNode);
        $variablesNode = $domTree->createElement('Variables');
        $xmlRoot->appendChild($variablesNode);

        foreach ($variables as $_key => $_value) {
            $variablesNode->appendChild(
                $domTree->createElement($_key, (string)$_value)
            );
        }

        $this->xml = $domTree->saveXML();
    }
}
