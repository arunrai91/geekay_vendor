<?php

namespace Magento\PaymentServicesPaypal\Controller\SmartButtons;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Customer\Model\Session as CustomerSession; // Import CustomerSession

/**
 * Class CouponPost
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CouponPost extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface
{
    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var CartTotalRepositoryInterface
     */
    protected $cartTotalRepository;

    /**
     * @var PaymentInformationManagementInterface
     */
    protected $paymentInformationManagement;

    /**
     * @var Quote
     */
    protected $quote;

    /**
     * @var CustomerSession
     */
    protected $customerSession; // Declare the property

    /**
     * @param Context $context
     * @param Cart $cart
     * @param JsonFactory $resultJsonFactory
     * @param Validator $formKeyValidator
     * @param CartRepositoryInterface $cartRepository
     * @param CartTotalRepositoryInterface $cartTotalRepository
     * @param PaymentInformationManagementInterface $paymentInformationManagement
     * @param Quote $quote
     * @param CustomerSession $customerSession // Add CustomerSession to constructor
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Cart $cart,
        JsonFactory $resultJsonFactory,
        Validator $formKeyValidator,
        CartRepositoryInterface $cartRepository,
        CartTotalRepositoryInterface $cartTotalRepository,
        PaymentInformationManagementInterface $paymentInformationManagement,
        Quote $quote,
        CustomerSession $customerSession // Add this argument
    ) {
        $this->cart = $cart;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->cartRepository = $cartRepository;
        $this->cartTotalRepository = $cartTotalRepository;
        $this->paymentInformationManagement = $paymentInformationManagement;
        $this->quote = $quote;
        $this->customerSession = $customerSession; // Assign to the property

        parent::__construct($context); // Ensure $context is passed to parent
    }

    /**
     * Initialize coupon
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.DuplicatedCode)
     */
    public function execute()
    {
        $couponCode = $this->getRequest()->getParam('remove') == 1
            ? ''
            : trim($this->getRequest()->getParam('coupon_code', ''));

        // Use checkout quote so coupon code works from product page as well
        $quote = $this->checkout->getQuote();

        $oldCouponCode = $quote->getCouponCode() ?? '';

        $codeLength = strlen($couponCode);
        if (!$codeLength && !strlen($oldCouponCode)) {
            return $this->_goBack();
        }

        try {
            $isCodeLengthValid = $codeLength && $codeLength <= \Magento\Checkout\Helper\Cart::COUPON_CODE_MAX_LENGTH;

            $itemsCount = $quote->getItemsCount();
            if ($itemsCount) {
                $quote->getShippingAddress()->setCollectShippingRates(true);
                $quote->setCouponCode($isCodeLengthValid ? $couponCode : '')->collectTotals();
                $this->quoteRepository->save($quote);
            }

            if ($codeLength) {
                $escaper = $this->_objectManager->get(\Magento\Framework\Escaper::class);
                $coupon = $this->couponFactory->create();
                $coupon->load($couponCode, 'code');
                if (!$itemsCount) {
                    if ($isCodeLengthValid && $coupon->getId()) {
                        $this->_checkoutSession->getQuote()->setCouponCode($couponCode)->save();
                        $this->messageManager->addSuccessMessage(
                            __(
                                'You used coupon code "%1".',
                                $escaper->escapeHtml($couponCode)
                            )
                        );
                    } else {
                        $this->messageManager->addErrorMessage(
                            __(
                                'The coupon code "%1" is not valid.',
                                $escaper->escapeHtml($couponCode)
                            )
                        );
                    }
                } else {
                    if ($isCodeLengthValid && $coupon->getId() && $couponCode == $quote->getCouponCode()) {
                        $this->messageManager->addSuccessMessage(
                            __(
                                'You used coupon code "%1".',
                                $escaper->escapeHtml($couponCode)
                            )
                        );
                    } else {
                        $this->messageManager->addErrorMessage(
                            __(
                                'The coupon code "%1" is not valid.',
                                $escaper->escapeHtml($couponCode)
                            )
                        );
                    }
                }
            } else {
                $this->messageManager->addSuccessMessage(__('You canceled the coupon code.'));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('We cannot apply the coupon code.'));
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
        }

        return $this->_goBack();
    }
}
