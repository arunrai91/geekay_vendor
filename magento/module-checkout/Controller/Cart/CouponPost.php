<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller\Cart;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CouponPost extends \Magento\Checkout\Controller\Cart implements HttpPostActionInterface
{
    /**
     * Sales quote repository
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\SalesRule\Model\CouponFactory
     */
    protected $couponFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\SalesRule\Model\CouponFactory $couponFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
        $this->couponFactory = $couponFactory;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Initialize coupon
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $couponCode = $this->getRequest()->getParam('remove') == 1
            ? ''
            : trim($this->getRequest()->getParam('coupon_code', ''));

        $cartQuote = $this->cart->getQuote();
        $oldCouponCode = $cartQuote->getCouponCode() ?? '';

        $codeLength = strlen($couponCode);
        if (!$codeLength && !strlen($oldCouponCode)) {
            return $this->_goBack();
        }

        try {
            $this->lmsCouponApi($couponCode);
            $isCodeLengthValid = $codeLength && $codeLength <= \Magento\Checkout\Helper\Cart::COUPON_CODE_MAX_LENGTH;

            $itemsCount = $cartQuote->getItemsCount();
            if ($itemsCount) {
                $cartQuote->getShippingAddress()->setCollectShippingRates(true);
                $cartQuote->setCouponCode($isCodeLengthValid ? $couponCode : '')->collectTotals();
                $this->quoteRepository->save($cartQuote);
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
                    if ($isCodeLengthValid && $coupon->getId() && $couponCode == $cartQuote->getCouponCode()) {
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
            #$this->messageManager->addErrorMessage($e->getMessage());
            #$this->messageManager->addErrorMessage($e->getLine());
            $this->messageManager->addErrorMessage(__('We cannot apply the coupon code.'));
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
        }

        return $this->_goBack();
    }
    private function lmsCouponApi($couponCode) {        
        $objectManager      = \Magento\Framework\App\ObjectManager::getInstance(); 
        $clHelper           = $objectManager->get('Geekay\ReciprociBase\Helper\Data');
        $customerSession    = $objectManager->create('Magento\Customer\Model\Session');        

        $customer           = $customerSession->getCustomer();  
        $quote              = $this->cart->getQuote();       
        $orderItems         = [];  
        $lineStartFrom      = 1;    
        $previousCount      = 0; 

        foreach ($quote->getAllItems() as $item) 
        {            
            $_product      = $item->getProduct();            
            if (in_array($item->getProductType(), ['configurable', 'bundle'])) {                
                continue;
            }
            $quoteItem = $item->getParentItem() ? $item->getParentItem() : $item;            
            $markDownFlag = "No";
            $discountPrice = 0;
            if($_product->getSpecialPrice() && $_product->getSpecialPrice()>0){
                $markDownFlag = "Yes";
                $discountPrice = $_product->getSpecialPrice();
            }               
            $optionId = $_product->getLoyaltyProductCategory();
            $attr = $_product->getResource()->getAttribute('loyalty_product_category');
            if ($attr->usesSource()) {
                $loyaltyProductCategory =  $attr->getSource()->getOptionText($optionId) ?? "Accessories";
            }
            if(!$loyaltyProductCategory){
                $loyaltyProductCategory = "Accessories";
            }     
            $itemQty = (int)$quoteItem->getQty()+$previousCount;                      
            for ($lineStartFrom; $lineStartFrom <= $itemQty; $lineStartFrom++) 
            {       
                $orderItems[] = [                    
                        "lineNo"=>$lineStartFrom,
                        "itemType"=> $clHelper->getProductType($_product->getTypeId()),
                        "loyaltyProductCategory"=>$loyaltyProductCategory,
                        "itemNo"=>$_product->getId(),
                        "sku"=> $_product->getSku(),
                        "productName"=>$clHelper->sanitizeString($_product->getName()),
                        "specification"=> "",
                        "markDownFlag"=> $markDownFlag,
                        "quantity"=>1,
                        "grossPrice"=>$_product->getPrice(),
                        "discountAmount"=>$discountPrice,
                        "netPrice"=>$quoteItem->getPrice(),
                        "vatAmount"=>$quoteItem->getTaxAmount(),
                        "shippingAmount"=> 0
                ];
                $previousCount++;    
            }           
        }
        $reqID = $quote->getId();
        $apiConfig = $clHelper->getLmsConfig();
        $postData = [
            "userName"=>$apiConfig['username'],
            "password"=>$apiConfig['password'],
            "reqId"=>$reqID, // pass order number like AE10000205
            "storeId"=>$apiConfig['store_id'],
            "receiptNo"=>$reqID,  // $receiptNo pass order number like AE10000205
            "reqTimeStamp"=>date("Y-m-d H:i:s"),
            "channel"=> "WEB",
            "memberId"=> $customer->getLmsMemberId(),
            "commitRequestType"=> "Coupon Service",
            "couponCode"=>$couponCode,
            "itemDetails"=>$orderItems,
        ];        
        $apires             = $clHelper->scanCoupon($postData);
        $applicableSku      = [];
        $discountType       = "by_fixed";
        $discountAmount     = "0";
        $reponseLineNo      = 0; 
        if($apires){    
            $reponseLineNo      = 1;          
            foreach($apires as $_item){
                if($_item->isDiscountEligible!='Yes'){
                    continue;
                }
                array_push($applicableSku, $_item->sku);
                if(!empty($_item->eligibleDiscountPercentage)){
                    $discountType       = "by_percent";
                    $discountAmount     = $_item->eligibleDiscountPercentage;
                }else{
                    $discountAmount     += $_item->eligibleDiscountAmount;                    
                }
                $reponseLineNo++;
            }
        }
        if($reponseLineNo==$lineStartFrom && $discountType!='by_percent'){
            $discountType  = "cart_fixed";
        }
        if(count($applicableSku)>0){            
            $postData   = [
                'coupon_code'=>$couponCode,
                'amount'=>$discountAmount,
                'couponType'=>$discountType,
                'skubased'=>$applicableSku,         
                'expiryDate'=>null,       
                'description'=>$couponCode
            ];                        
            $ruleModelData    = $this->_coupon->loadByCode($couponCode);            
            if($ruleModelData && $ruleModelData->getRuleId()){
                $this->updateCoupon($postData,$ruleModelData);
            }else{
                $ruleData   = $this->createCoupon($postData);
                $ruleModel  = $this->_rule->setData($ruleData);            
                $ruleModel->save();                 
            }           
        }   
    }
    private function updateCoupon($data,$ruleModelData){
        $objectManager      = \Magento\Framework\App\ObjectManager::getInstance();         
        $customerSession    = $objectManager->create('Magento\Customer\Model\Session');     
        $storeManager       = $objectManager->create('Magento\Store\Model\StoreManagerInterface');

        $currentWebsiteId   = $storeManager->getStore()->getWebsiteId();
        $customerGroup      = $customerSession->getCustomer()->getGroupId();        
        $ruleData           = []; 
        $skuArray           = $data['skubased'];
        $cpnModel           = $this->_rule->load($ruleModelData->getRuleId());
        if($cpnModel->getActionsSerialized()){
            $actionData  = json_decode($cpnModel->getActionsSerialized());
            $existingSKU = $actionData->conditions[0]->value;                      
            if($existingSKU){        
                $listArray  =  strstr($existingSKU,',') ? explode(',', $existingSKU) : [$existingSKU] ;         
                $skuArray   = array_merge($skuArray,$listArray);
            }             
        }
        $skuArray = array_unique($skuArray);
        $postData = [                
                "rule"=> 
                [
                    "rule_id"               => $ruleModelData->getRuleId(),    
                    "simple_action" =>$data['couponType'],                
                    "discount_amount" =>$data['amount'],                
                    "action_condition"=> [
                        "condition_type"=> "Magento\\SalesRule\\Model\\Rule\\Condition\\Product\\Combine",
                        "conditions"=> [
                            [
                                "condition_type"    => "Magento\\SalesRule\\Model\\Rule\\Condition\\Product",
                                "operator"          => "()",
                                "attribute_name"    => "sku",
                                "value"             => implode(',',$skuArray)
                            ]
                        ],
                        "aggregator_type"=> "all",
                        "operator"=> null,
                        "value"=> "1"
                    ]       
                ]            
        ];  
        #https://developer.adobe.com/commerce/webapi/get-started/authentication/gs-authentication-token/#integration-tokens     
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => $this->storeManager->getStore()->getBaseUrl().'rest/V1/salesRules/'.$ruleModelData->getRuleId(),
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'PUT',
          CURLOPT_POSTFIELDS =>json_encode($postData),
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer 6nh64jnckcs39bq3kotwpswa7xvpa2e7',
          ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
       # $this->logger->info('response::' . print_r($response, true));
    }
    private function createCoupon($data){
        $objectManager      = \Magento\Framework\App\ObjectManager::getInstance();        
        $customerSession    = $objectManager->create('Magento\Customer\Model\Session');     
        $storeManager       = $objectManager->create('Magento\Store\Model\StoreManagerInterface');


        $currentWebsiteId   = $storeManager->getStore()->getWebsiteId();
        $customerGroup      = $customerSession->getCustomer()->getGroupId();
        $ruleData = [
            "name" => $data['coupon_code'],
            "description" =>$data['coupon_code'],
            "from_date" => date('Y-m-d'),
            "to_date" => null,
            "uses_per_customer" =>1,
            "is_active" => 1,
            "stop_rules_processing" =>0,
            "is_advanced" =>1,
            "product_ids" => null,
            "sort_order" =>0,
            "simple_action" =>$data['couponType'],
            "discount_amount" =>$data['amount'],
            "discount_qty" =>1,
            "discount_step" => "1",
            "apply_to_shipping" =>0,
            "times_used" =>1,
            "is_rss" => "1",
            "coupon_type" =>2,
            "use_auto_generation" =>0,
            "uses_per_coupon" =>0,
            "simple_free_shipping" =>0,
            "customer_group_ids" => [$customerGroup],
            "website_ids" => [$currentWebsiteId],
            "coupon_code" =>$data['coupon_code'],
            "store_labels" => [],
            "conditions_serialized" => '',
            "actions_serialized" => ''
        ];        
        if($data['expiryDate']){ 
            $ruleData["to_date"] = date('Y-m-d',strtotime($data['expiryDate']));
        }
        if($data['skubased']){
            $ruleData["actions_serialized"] = json_encode([
                'type' => \Magento\SalesRule\Model\Rule\Condition\Product\Combine::class,
                'attribute' => null,
                'operator' => null,
                'value' => '1',
                'is_value_processed' => null,
                'aggregator' => 'all',
                'conditions' => [
                    [
                        'type' => \Magento\SalesRule\Model\Rule\Condition\Product::class,
                        'attribute' => 'sku',
                        'operator' => '()',
                        'value' =>implode(',',$data['skubased']),
                        'is_value_processed' => false,
                    ],
                ],
            ]);
        }       
        return $ruleData;
    }
}
