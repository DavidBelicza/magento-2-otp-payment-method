<?php
/**
 * Youama_OTP
 *
 * @author  David Belicza <87.bdavid@gmail.com>
 * @license David Belicza e.v. (http://youama.hu)
 */

declare(strict_types=1);

namespace Youama\OTP\Model;

use Youama\OTP\Api\Data\PaymentStatusInterface;
use Youama\OTP\Api\OrderFinderInterface;
use Youama\OTP\Api\TransactionFinderInterface;
use Youama\OTP\Api\TransactionRepositoryInterface;
use Youama\OTP\Helper\Config;
use Youama\OTP\Model\Connect\CheckPayment;
use Youama\OTP\Model\Connect\CheckPaymentFactory;
use Youama\OTP\Model\Connect\SendPayment;
use Youama\OTP\Model\Connect\SendPaymentFactory;
use Youama\OTP\Model\Order\StatusUpdater;
use Youama\OTP\Model\Order\TransactionManager;

/**
 * Class OTPMediator
 *
 * Super high end Mediator class to manage the whole OTP bank payment process.
 *
 * @package Youama\OTP\Model
 */
class OTPMediator
{
    /**
     * @var Config
     */
    private $configHelper;

    /**
     * @var OrderFinderInterface
     */
    private $orderFinder;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var SendPaymentFactory
     */
    private $sendPaymentFactory;

    /**
     * @var CheckPaymentFactory
     */
    private $checkPaymentFactory;

    /**
     * @var StatusUpdater
     */
    private $statusUpdater;

    /**
     * @var TransactionManager
     */
    private $transactionManager;

    /**
     * @var TransactionFinderInterface
     */
    private $transactionFinder;

    /**
     * @var bool
     */
    private $isCron = false;

    /**
     * OTPMediator constructor.
     *
     * @param Config                         $configHelper
     * @param OrderFinderInterface           $orderFinder
     * @param TransactionRepositoryInterface $transactionRepository
     * @param SendPaymentFactory             $sendPaymentFactory
     * @param CheckPaymentFactory            $checkPaymentFactory
     * @param StatusUpdater                  $statusUpdater
     * @param TransactionManager             $transactionManager
     * @param TransactionFinderInterface     $transactionFinder
     */
    public function __construct(
        Config $configHelper,
        OrderFinderInterface $orderFinder,
        TransactionRepositoryInterface $transactionRepository,
        SendPaymentFactory $sendPaymentFactory,
        CheckPaymentFactory $checkPaymentFactory,
        StatusUpdater $statusUpdater,
        TransactionManager $transactionManager,
        TransactionFinderInterface $transactionFinder
    ) {
        $this->configHelper = $configHelper;
        $this->orderFinder = $orderFinder;
        $this->transactionRepository = $transactionRepository;
        $this->sendPaymentFactory = $sendPaymentFactory;
        $this->checkPaymentFactory = $checkPaymentFactory;
        $this->statusUpdater = $statusUpdater;
        $this->transactionManager = $transactionManager;
        $this->transactionFinder = $transactionFinder;
    }

    /**
     * When it is true it means the process runs by Cron instead of the
     * Customer.
     *
     * @param bool $isCron
     */
    public function setIsCron(bool $isCron)
    {
        $this->isCron = $isCron;
    }

    /**
     * It checks the module's transaction entity can be updated or can not.
     *
     * @param string $orderIncrementId
     *
     * @return bool
     */
    public function isTransactionCanBeUpdated(string $orderIncrementId): bool
    {
        /** @var Transaction $transaction */
        $transaction = $this->transactionFinder
            ->findByOrderIncrementId($orderIncrementId);

        return $transaction->getStatus() === PaymentStatusInterface::OTP_PAYMENT_TRY_AGAIN_LATER;
    }

    /**
     * Create Customer-with-last-Order-to-OTP Url. It is based on the session.
     * It is used by the Customer.
     *
     * @return string
     */
    public function createCustomerWithLastOrderToOTPUrl(): string
    {
        return $this->createCustomerToOTPUrl(
            $this->orderFinder->getLastOrderIncrementIdFromSession()
        );
    }

    /**
     * Create Customer-to-OTP Url. It is used by Cron.
     *
     * @param string $orderIncrementId
     *
     * @return string
     */
    public function createCustomerToOTPUrl(string $orderIncrementId): string
    {
        return $this->configHelper->getOtpBankUserInterfaceUrl(
            $this->configHelper->getTransactionId($orderIncrementId)
        );
    }

    /**
     * It sends the Last Order From Session to OTP
     */
    public function sendLastOrderFromSessionToOTP()
    {
        $this->sendOrderToOTP(
            $this->orderFinder->getLastOrderIncrementIdFromSession()
        );
    }

    /**
     * It sends the Order to OTP bank by increment ID of the order. It also
     * starts the order status changing, create module's transaction entity and
     * Magento's transaction entity to store the Request and the state of the
     * payment.
     *
     * @param string $orderIncrementId
     */
    public function sendOrderToOTP(string $orderIncrementId)
    {
        $order = $this->orderFinder->getOrderByOrderIncrementId($orderIncrementId);

        /** @var SendPayment $sendPayment */
        $sendPayment = $this->sendPaymentFactory->create();

        $sendPayment->addOrder($order);
        $sendPayment->signPayment();
        $sendPayment->composeXmlData();

        $this->transactionRepository->addShopFirstRequest(
            $orderIncrementId,
            $sendPayment->getCreatedXml()
        );

        $response = $sendPayment->send();

        $this->transactionRepository->addBankFirstResponse(
            $orderIncrementId,
            $response->result
        );

        $this->transactionManager->addOrder($order);
        $this->transactionManager->createTransaction();
        $this->transactionManager->updateTransactionWithFirstResponse();

        $this->statusUpdater->inProgress($orderIncrementId);
    }

    /**
     * It validates the payment to get that order is paid or not. It starts the
     * success or fail methodology based on the given response. It retrieves
     * the status of payment.
     *
     * @param string $orderIncrementId
     *
     * @return int Value from class PaymentStatusInterface.
     * @see PaymentStatusInterface
     */
    public function validatePayment(string $orderIncrementId): int
    {
        $status = $this->getPaymentStatusFromOTP($orderIncrementId);

        if ($status === PaymentStatusInterface::OTP_PAYMENT_SUCCESS) {
            $this->updateOrderStatusSuccess($orderIncrementId);
        } elseif ($status === PaymentStatusInterface::OTP_PAYMENT_FAILED) {
            $this->updateOrderStatusFail($orderIncrementId);
        }

        return $status;
    }

    /**
     * It retrieves the payment status from the bank by make a request to the
     * bank interface.
     *
     * @param string $orderIncrementId
     *
     * @return int Value from class PaymentStatusInterface.
     * @see PaymentStatusInterface
     */
    public function getPaymentStatusFromOTP(string $orderIncrementId): int
    {
        $order = $this->orderFinder->getOrderByOrderIncrementId($orderIncrementId);
        $this->transactionManager->addOrder($order);

        /** @var CheckPayment $checkPayment */
        $checkPayment = $this->checkPaymentFactory->create();

        $checkPayment->addOrder($order);
        $checkPayment->signPayment();
        $checkPayment->composeXmlData();

        $response = $checkPayment->send();

        $this->transactionRepository->addBankSecondResponse(
            $orderIncrementId,
            $response->result
        );

        return $checkPayment->getStatus($response);
    }

    /**
     * It starts the success paid process by updates the order and its related
     * entities.
     *
     * @param string $orderIncrementId
     */
    protected function updateOrderStatusSuccess(string $orderIncrementId)
    {
        $this->transactionRepository->updateStatus(
            $orderIncrementId,
            PaymentStatusInterface::OTP_PAYMENT_SUCCESS,
            (int)$this->isCron
        );

        $this->transactionManager->updateTransactionWithSecondResponse(true);
        $this->statusUpdater->success(
            $orderIncrementId,
            $this->transactionManager->getTransactionId()
        );
    }

    /**
     * It starts the fail paid process by updates the order and its related
     * entities.
     *
     * @param string $orderIncrementId
     */
    protected function updateOrderStatusFail(string $orderIncrementId)
    {
        $this->transactionRepository->updateStatus(
            $orderIncrementId,
            PaymentStatusInterface::OTP_PAYMENT_FAILED,
            (int)$this->isCron
        );

        $this->transactionManager->updateTransactionWithSecondResponse(true);
        $this->statusUpdater->fail($orderIncrementId);
    }
}
