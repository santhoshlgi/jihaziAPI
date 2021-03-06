<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Custom\Api\Model;

class ProductManagement implements \Custom\Api\Api\ProductManagementInterface
{

    /**
     * {@inheritdoc}
     */
    public function getCartProduct($userid){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $paymentFees = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('paymentfee/paymentfee_settings/paymentfee');
        $serialize = $objectManager->get('Magento\Framework\Serialize\Serializer\Json');
        if (is_string($paymentFees) && !empty($paymentFees)) {
            $paymentFees = $serialize->unserialize($paymentFees);
        }
        if (is_array($paymentFees)) {
            foreach ($paymentFees as $paymentFee) {
                $fee = $paymentFee['fee'];
            }
        }
        // echo $fee ;
        // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerObj = $objectManager->create('Magento\Customer\Model\Customer')->load($userid);
        $Adminusername = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('api/general/api_admin');
        $Adminpassword = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('api/general/api_password');
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $BaseUrl = $storeManager->getStore()->getBaseUrl();

        $userData = array("username" => $Adminusername, "password" => $Adminpassword);
        $adminUrl = $BaseUrl.'rest/V1/integration/admin/token';
        $ch = curl_init($adminUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Content-Lenght: " . strlen(json_encode($userData))));

        $token = curl_exec($ch);
        // print_r($token);
        $customerData = [
            'customer_id' => $userid
        ];
        $CartsUrl = $BaseUrl.'rest/V1/carts/mine';
        $ch = curl_init($CartsUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($customerData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . json_decode($token)));
        
        $result = curl_exec($ch);
        
        $quote_id = json_decode($result);
        $getHeaders = apache_request_headers();
        if(isset($getHeaders['lang'])){
            $lang = $getHeaders['lang'];
            $langData = [
                'quote' => [
                    'id' => $quote_id,
                    'store_id' => $lang
                ]
            ];
            $langCartsUrl = $BaseUrl.'rest/V1/carts/mine';
            $ch = curl_init($langCartsUrl);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($langData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . json_decode($token)));
            $result = curl_exec($ch);
        
            $chkkkk = json_decode($result);
        
        }
        $customer = $objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface')->getById($userid);
        $addressRepository = $objectManager->create('Magento\Customer\Api\AddressRepositoryInterface');
        $shippingAddressId = $customer->getDefaultShipping();
        if(isset($shippingAddressId)){
            $url = $BaseUrl."rest/default/V1/carts/".$quote_id."/estimate-shipping-methods-by-address-id";
            $json = [
                "addressId" => $shippingAddressId
            ];
            // echo $url;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . json_decode($token)));
            $result = curl_exec($ch);
            $result = json_decode($result);
            curl_close($ch);
        }else{
            $url = $BaseUrl."rest/default/V1/carts/".$quote_id."/estimate-shipping-methods";
            $json = [
                "address" => [
                    "country_id" => "SA",
                    "street" => [
                        "test"
                    ],
                    "telephone" => "1234567890",
                    "postcode" => "12211",
                    "city" => "riyadh",
                    "firstname" => "abc",
                    "lastname" => "xyz",
                    "customer_id" => $userid,
                    "email" => $customerObj->getEmail()
                ]
            ];
            // echo $url;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . json_decode($token)));
            $result = curl_exec($ch);
            $result = json_decode($result);
            curl_close($ch);
            // print_r($result);
        }
        
        // $ShippingMethodManagementInterface = $objectManager->create('Magento\Quote\Api\ShippingMethodManagementInterface');
        // $getShippingMethods = $ShippingMethodManagementInterface->getList($quote_id);
 
        // $methodCodes = [];
        // foreach ($getShippingMethods as $method) {
        //     $methodCodes[] = $method->getMethodTitle() . '=>' . $method->getAmount();
        // }
        // print_r($methodCodes); exit(); https://smvatech.in/ecommerce/rest/V1/carts/326/shipping-methods
        // $url = $BaseUrl."rest/default/V1/carts/".$quote_id."/estimate-shipping-methods-by-address-id" post;
        // $url = "https://smvatech.in/ecommerce/rest/default/V1/carts/".$quote_id."/shipping-methods";
        
        // print_r($result); exit();

        $taxurl = $BaseUrl."rest/default/V1/carts/".$quote_id."/totals";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$taxurl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        // curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . json_decode($token)));
        $results = curl_exec($ch);
        $taxresult = json_decode($results,true);
        curl_close($ch);
        // print_r($taxresult); exit();
        $customerId = $userid;
    
        $customerObj = $objectManager->create('Magento\Customer\Model\Customer')->load($customerId);

        $customerFirstName = $customerObj->getFirstname();
        $customerLastName = $customerObj->getLastname(); 

        $quoteFactory = $objectManager->get('\Magento\Quote\Model\QuoteFactory');

        $quote = $quoteFactory->create()->loadByCustomer($customerObj);
    
        $items = $quote->getAllItems();
        $helperImport = $objectManager->get('\Magento\Catalog\Helper\Image');

        $cart_data = array();
        foreach($items as $item)
        {
            $product = $item->getProduct();
            $cartProductId = $product->getId();
            /*if($cartProductId == $productid)
            {*/
                $imageUrl = $helperImport->init($product, 'product_page_image_small')
                                ->setImageFile($product->getSmallImage()) // image,small_image,thumbnail
                                ->resize(380)
                                ->getUrl();
                                $productStockObj = $objectManager->get('Magento\CatalogInventory\Api\StockRegistryInterface')->getStockItem($item['product_id']);
                $cart_data[] = array(
                    'name'=> $item['name'],
                    'product_id'=> $item['product_id'],
                    'price'=> doubleval(number_format((double)$item['price'], 2, '.', '')),                               
                    'qty'=> $item['qty'],
                    'is_in_stock' => boolval($productStockObj['is_in_stock']) ? true : false,
                    'subtotal' =>  doubleval(number_format((double)$item['price'] * $item['qty'], 2, '.', '')),
                    'image'=> $imageUrl
                );
            /*}*/
        } 
                
        if($cart_data != null){
            $getHeaders = apache_request_headers();
            if(isset($getHeaders['lang'])){
                $lang = $getHeaders['lang'];
                if($lang == 1){
                    $msg = 'Transaction was Successful';
                }else{
                    $msg = 'المعاملة ناجحه';
                }
            }else{
                $msg = 'Transaction was Successful';   
            }
            $notif = array('SuccessCode'=> 200 ,'message' => $msg, 'data' => array('Cart Data'=> $cart_data), 
            'shipping-methods' => $result , 'tax' => doubleval(number_format((double)$taxresult['total_segments'][2]['value'], 2, '.', '')),
            'COD_price' => doubleval(number_format((double)$fee, 2, '.', '')),
            'discount_amount' => doubleval(number_format((double)$taxresult['discount_amount'], 2, '.', '')));
        }
        else{
            $getHeaders = apache_request_headers();
            if(isset($getHeaders['lang'])){
                $lang = $getHeaders['lang'];
                if($lang == 1){
                    $msg = 'Transaction Failure';
                }else{
                    $msg = 'المعاملة فاشله';
                }
            }else{
                $msg = 'Transaction Failure';   
            }
            $notif = array('SuccessCode'=> 400 ,'message' => $msg, 'data' => array('Cart Data'=> []),
            'shipping-methods' => '' , 'tax' => '', 'COD_price' => '','discount_amount' => '');
        }
        header("Content-Type: application/json; charset=utf-8");
        $ns = json_encode($notif);
        print_r($ns,false);
        die();
    }


    public function getWishlistProduct($userid){
        
        $customerId = $userid;
    
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerObj = $objectManager->create('Magento\Customer\Model\Customer')->load($customerId);

        $customerFirstName = $customerObj->getFirstname();
        $customerLastName = $customerObj->getLastname(); 

        $wishlist = $objectManager->get('\Magento\Wishlist\Model\Wishlist');

        $wishlist_Collection = $wishlist->loadByCustomerId($customerId)->getItemCollection();          
        
        /*$items = $wishlist_Collection->getAllItems();*/
        $items = $wishlist_Collection;
        
        $helperImport = $objectManager->get('\Magento\Catalog\Helper\Image');

        $wishlist_data = array();
        foreach($items as $item)
        {
            // var_dump($item['qty']); exit();
            $product = $item->getProduct();
            $cartProductId = $product->getId();
    
            /*if($cartProductId == $productid)
            {*/
                $imageUrl = $helperImport->init($product, 'product_page_image_small')
                                ->setImageFile($product->getSmallImage()) // image,small_image,thumbnail
                                ->resize(380)
                                ->getUrl();
                
                                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                                $getHeaders = apache_request_headers();
                                if(isset($getHeaders['lang'])){
                                    $lang = $getHeaders['lang'];
                                    $product = $objectManager->get('Magento\Catalog\Model\Product')->setStoreId($lang)->load($item['product_id']);
                                }else{
                                    $product = $objectManager->get('Magento\Catalog\Model\Product')->load($item['product_id']);
                                }
                                $productStockObj = $objectManager->get('Magento\CatalogInventory\Api\StockRegistryInterface')->getStockItem($item['product_id']);
                $wishlist_data[] = array(
                    'name'=> $product->getName(),
                    'product_id'=> $item['product_id'],
                    'price'=>$item['price'],                               
                    'qty'=> $item['qty'],
                    'is_in_stock' => boolval($productStockObj['is_in_stock']) ? true : false,
                    'image'=> $imageUrl
                );
            /*}*/
        } 
                
        if($wishlist_data != null){
            $getHeaders = apache_request_headers();
            if(isset($getHeaders['lang'])){
                $lang = $getHeaders['lang'];
                if($lang == 1){
                    $msg = 'Transaction was Successful';
                }else{
                    $msg = 'المعاملة ناجحه';
                }
            }else{
                $msg = 'Transaction was Successful';   
            }
            $notif = array('SuccessCode'=> 200 ,'message' => $msg, 'data' => array('Cart Data'=> $wishlist_data));
        }
        else{
            $getHeaders = apache_request_headers();
            if(isset($getHeaders['lang'])){
                $lang = $getHeaders['lang'];
                if($lang == 1){
                    $msg = 'Wishlist is empty';
                }else{
                    $msg = 'قائمة الرغبات';
                }
            }else{
                $msg = 'Wishlist is empty';   
            }
            $notif = array('SuccessCode'=> 400 ,'message' => $msg, 'data' => '');
        }
        header("Content-Type: application/json; charset=utf-8");
        $ns = json_encode($notif);
        print_r($ns,false);
        die();
    }


    public function saveReview($userid,$productid,$nickname,$headline,$review,$rating){

        $customerId = $userid;
    
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerObj = $objectManager->create('Magento\Customer\Model\Customer')->load($customerId);

        $customerFirstName = $customerObj->getFirstname();
        $customerLastName = $customerObj->getLastname(); 

    
        $productId=$productid;
        $customerNickName=$nickname;
        $reviewTitle=$headline;
        $reviewDetail=$review;
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $StoreId = $storeManager->getStore()->getStoreId();
        
        $_review = $objectManager->get("Magento\Review\Model\Review")
        ->setEntityPkValue($productId)    //product Id
        ->setStatusId(\Magento\Review\Model\Review::STATUS_PENDING)// pending/approved
        ->setTitle($reviewTitle)
        ->setDetail($reviewDetail)
        ->setEntityId(1)
        ->setStoreId($StoreId)
        ->setStores(1)
        ->setCustomerId($customerId)//get dynamically here 
        ->setNickname($customerNickName)
        ->save();
                    


        /* 
        $_ratingOptions = array(
            1 => array(1 => 1,  2 => 2,  3 => 3,  4 => 4,  5 => 5),   //quality
            2 => array(1 => 6,  2 => 7,  3 => 8,  4 => 9,  5 => 10),  //value
            3 => array(1 => 11, 2 => 12, 3 => 13, 4 => 14, 5 => 15),  //price 
            4 => array(1 => 16, 2 => 17, 3 => 18, 4 => 19, 5 => 20)   //rating
        );*/
        /*$ratingOptions = array(
            '1' => '1',
            '2' => '7',
            '3' => '13',
            '4' => '19'
        );   */   
        $ratingOptions = array(
            '4' => $rating
        );

        foreach ($ratingOptions as $ratingId => $optionIds) 
        {     
            $objectManager->get("Magento\Review\Model\Rating")
                          ->setRatingId($ratingId)
                          ->setReviewId($_review->getId())
                          ->addOptionVote($optionIds, $productId);

        }

        $_review->aggregate();

        /*$RatingOb = $objectManager->create('Magento\Review\Model\Rating')->getEntitySummary($productId);   
        $ratings = $RatingOb->getSum()/$RatingOb->getCount();                     

        echo $ratings;*/
    
        if($_review != null){
            $getHeaders = apache_request_headers();
            if(isset($getHeaders['lang'])){
                $lang = $getHeaders['lang'];
                if($lang == 1){
                    $msg = 'Review added Successfully';
                }else{
                    $msg = 'تم اضافة التقييم بنجاح';
                }
            }else{
                $msg = 'Review added Successfully';   
            }
            $notif = array('SuccessCode'=> 200 ,'message' => $msg);
        }
        else{
            $getHeaders = apache_request_headers();
            if(isset($getHeaders['lang'])){
                $lang = $getHeaders['lang'];
                if($lang == 1){
                    $msg = 'Review added Failure';
                }else{
                    $msg = 'فشل اضافة التقييم';
                }
            }else{
                $msg = 'Review added Failure';   
            }
            $notif = array('SuccessCode'=> 400 ,'message' => $msg);
        }
        header("Content-Type: application/json; charset=utf-8");
        $ns = json_encode($notif);
        print_r($ns,false);
        die();
    }


    public function getProductDetails($token,$productid){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $BaseUrl = $storeManager->getStore()->getBaseUrl();
        
        $res = $BaseUrl.'rest/default/V1/customers/me';
        $send = array("Authorization: Bearer ".$token);
        $curl1 = curl_init();
        curl_setopt_array($curl1, array(
        CURLOPT_URL => $res,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => $send,
        ));
        $response1 = curl_exec($curl1);
        $AllData = json_decode($response1, true);
        if(!empty($AllData['id'])){
            $isfav = true;
        }else{
            $isfav = false;
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $getHeaders = apache_request_headers();
        if(isset($getHeaders['lang'])){
            $lang = $getHeaders['lang'];
            $product = $objectManager->get('Magento\Catalog\Model\Product')->setStoreId($lang)->load($productid);
        }else{
            $product = $objectManager->get('Magento\Catalog\Model\Product')->load($productid);
        }
        /*code for getting rating of product*/
        /*$reviewFactory = $objectManager->get('\Magento\Review\Model\ReviewFactory');   */

        /*$reviewFactory->create()->getEntitySummary($product, $StoreId);
        $ratingSummary = $product->getRatingSummary()->getRatingSummary();*/

        // if($isArabic == 1){
            
        // }else{
            // $product = $objectManager->get('Magento\Catalog\Model\Product')->load($productid);
        // }
        
        // $product->setStoreId(2);

        $images = $product->getMediaGalleryImages();
        $image_url = array();
        $i=0;
        foreach($images as $child){ 
         $image_url[$i] =  array("url" => $child->getUrl());
         $i++;
        }  
        
        $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $currentStoreId = $storeManager->getStore()->getId();
        /*$rating = $objectManager->get("Magento\Review\Model\ResourceModel\Review\CollectionFactory");*/
        $rating = $objectManager->get("Magento\Review\Model\ResourceModel\Review\Collection");


        $collection = $rating->addStoreFilter(
                    $currentStoreId
                )->addStatusFilter(
                    \Magento\Review\Model\Review::STATUS_APPROVED
                )->addEntityFilter(
                    'product',
                    $productid
                )->setDateOrder()->addRateVotes();

        $review_collection = array();

        $i=0;
            function chkvote($vars)
            {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $objectManager =   \Magento\Framework\App\ObjectManager::getInstance();
                $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION'); 
                $result1 = $connection->fetchAll("SELECT * FROM rating_option_vote where review_id =".$vars);
                // $votecollection = $objectManager->get('Magento\Review\Model\ResourceModel\Rating\Option\Vote\Collection');
                // $ratingCollection = $votecollection
                //                ->addRatingInfo()
                //                ->addOptionInfo()
                //                ->addRatingOptions()
                //                ->addFieldToFilter('review_id',$vars);  
                //     $rateData = $ratingCollection->getData();
                return $result1[0]['value'];
                    // return $rateData[0]['value'];
            }
        foreach($collection as $item)
        {
            // $countRatings = $objectManager->get("Magento\Review\Model\Rating")>getResourceCollection()->getData();
                        //   ->getReviewId($item->get)->getData();
            // print_r($ratingCollection->getData());
            // $countRatings = count($item->getRatingVotes());
            // echo $countRatings.' ok';
            /*$countRatings = count($item->getRatingVotes());
            if ($countRatings > 0) {
                $allRatings = 0;
                foreach ($item->getRatingVotes() as $vote) {
                    $allRatings = $allRatings + $vote->getPercent();
                }
                $allRatingsAvg = $allRatings / $countRatings;
                echo $allRatingsAvg;
                print_r($item->getRatingVotes()->getData());
            }*/
            // var_dump($countRatings);
            $review_collection[$i]['title'] = $item->getTitle();
            $review_collection[$i]['rating'] = chkvote($item->getId());
            $review_collection[$i]['description'] = $item->getDetail();
            $review_collection[$i]['customerName'] = $item->getNickname();
            $review_collection[$i]['date'] = date("d-m-Y",strtotime($item->getCreatedAt()));
            $i++;
        }
        

         $ratingFactory = $objectManager->get("\Magento\Review\Model\RatingFactory");

        $ratingCollection = $ratingFactory->create()
        ->getResourceCollection()
        ->addEntityFilter(
            'product' 
        )->setPositionOrder()->setStoreFilter(
            $currentStoreId
        )->addRatingPerStoreName(
            $currentStoreId
        )->load();

        /*print_r($ratingCollection->getData());*/
        $reviewFactory = $objectManager->get('\Magento\Review\Model\ReviewFactory');   
        $rat = $reviewFactory->create()->getEntitySummary($product, $currentStoreId);
        
        $ratingSummary = $product->getRatingSummary()->getRatingSummary();
        
        $additional = [];
        $attributes = $product->getAttributes();
        $i=0;
        foreach($attributes as $attribute) {
            if ($attribute->getIsVisibleOnFront()) {
                $value = $attribute->getFrontend()->getValue($product);
                if($value != "")
                {
                    $additional[$i] = array(
                        'label' => $attribute->getStoreLabel(),
                        'value' => $value
                    );
                    /*$additional[$attribute->getStoreLabel()] = $value;*/
                    $i++;
                }
            }
        }
        $desc1 = str_replace("&nbsp;", "", $product->getDescription());
        $desc2 = str_replace("<p>", "", $desc1);
        $desc3 = str_replace("</p>", "", $desc2);
        $botarray = explode("<br>", $desc3);
        // $botarray = preg_split("/\r\n|\n|\r/", $product->getDescription());
        $product_data = array(
                    'productId'=> $product->getId(),
                    'isFavorate'=> $isfav,
                    'title'=> $product->getName(),
                    'rating'=> $ratingSummary,
                    'price'=> $product->getPrice(),
                    'prodAmount'=> number_format( $product->getFinalPrice(), 2, '.', '' ),
                    'topDesc'=> strip_tags($product->getShortDescription()), 
                    'productImageUrl'=> $image_url,
                    'bottomDesc'=> $botarray, 
                    'moreInfo'=> $additional,
                    'reviewsList'=> $review_collection
                );

        
        /*print_r($collection->getData()); //Get all review data of product*/

        /*$data = $product->getData();*/
        if($product_data != null){
            $getHeaders = apache_request_headers();
            if(isset($getHeaders['lang'])){
                $lang = $getHeaders['lang'];
                if($lang == 1){
                    $msg = 'Transaction was Successful';
                }else{
                    $msg = 'المعاملة ناجحه';
                }
            }else{
                $msg = 'Transaction was Successful';   
            }
            $notif = array('SuccessCode'=> 200 ,'message' => $msg, // 'token' => $token,
            'data' => $product_data);
        }
        else{
            $getHeaders = apache_request_headers();
            if(isset($getHeaders['lang'])){
                $lang = $getHeaders['lang'];
                if($lang == 1){
                    $msg = 'Transaction Failure';
                }else{
                    $msg = 'المعاملة فاشله';
                }
            }else{
                $msg = 'Transaction Failure';   
            }
            $notif = array('SuccessCode'=> 400 ,'message' => $msg, 'data' => '');
        }
        header("Content-Type: application/json; charset=utf-8");
        $ns = json_encode($notif);
        print_r($ns,false);
        die();
    }
    
    public function addtocartProduct($userid,$productid){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $Adminusername = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('api/general/api_admin');
        $Adminpassword = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('api/general/api_password');
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $BaseUrl = $storeManager->getStore()->getBaseUrl();

        $userData = array("username" => $Adminusername, "password" => $Adminpassword);
        $adminUrl = $BaseUrl.'rest/V1/integration/admin/token';
        $ch = curl_init($adminUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Content-Lenght: " . strlen(json_encode($userData))));

        $token = curl_exec($ch);
        // print_r($token);
        $customerData = [
            'customer_id' => $userid
        ];
        $CartsUrl = $BaseUrl.'rest/V1/carts/mine';
        $ch = curl_init($CartsUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($customerData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . json_decode($token)));
        
        $result = curl_exec($ch);
        
        $quote_id = json_decode($result);
        // echo '<pre>';print_r($quote_id);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productRepo = $objectManager->get('Magento\Catalog\Model\ProductRepository');
        $_product = $productRepo->getById($productid);
        $productData = [
            'cart_item' => [
                'quote_id' => $quote_id,
                'sku' => $_product->getSku(),
                'qty' => 1
            ]
        ];
        $CartsUrl = $BaseUrl.'rest/V1/carts/mine/items';
        $ch = curl_init($CartsUrl);
        // $ch = curl_init("https://smvatech.in/ecommerce/rest/V1/carts/mine/items");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($productData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . json_decode($token)));
        
        $result = curl_exec($ch);
        
        $result = json_decode($result , true);
        // echo '<pre>';
        // print_r($result);
        if(isset($result['item_id'])){
            $getHeaders = apache_request_headers();
            if(isset($getHeaders['lang'])){
                $lang = $getHeaders['lang'];
                if($lang == 1){
                    $msg = 'Product Added';
                }else{
                    $msg = 'تمت إضافة المنتج';
                }
            }else{
                $msg = 'Product Added';   
            }
            $notif = array('SuccessCode'=> 200 ,'message' => $msg, 'data' => array($result));
        }else{
            $getHeaders = apache_request_headers();
            if(isset($getHeaders['lang'])){
                $lang = $getHeaders['lang'];
                if($lang == 1){
                    $msg = 'Product Not Added';
                }else{
                    $msg = 'المنتج غير مضاف';
                }
            }else{
                $msg = 'Product Not Added';   
            }
            $notif = array('SuccessCode'=> 400 ,'message' => $msg, 'data' => array());
        }

        // $ch = curl_init("https://smvatech.in/ecommerce/rest/V1/carts/".$quote_id);
        // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . json_decode($token)));

        // $result = curl_exec($ch);

        // $result = json_decode($result);
        // echo '<pre>';print_r($result);

        // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // $prod_id = $productid;
        // $qty = 1;
        // $cart = $objectManager->get('Magento\Checkout\Model\Cart');
        // $productRepo = $objectManager->get('Magento\Catalog\Model\ProductRepository');
        // $_product = $productRepo->getById($prod_id);
        // $productData = [
        //     'qty' => $qty,
        //     'product' => $prod_id
        // ];
        // $cart->addProduct($_product, $productData);
        // $cart->save();
        // $customerRepositoryInterface = $objectManager->get('Magento\Customer\Api\CustomerRepositoryInterface');
        // $customer= $customerRepositoryInterface->getById($userid);
        // $cart->getQuote()->assignCustomer($customer);
        // $cart->getQuote()->setTotalsCollectedFlag(false)->collectTotals();
        // $cart->getQuote()->save();
//         $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
//   $prod_id = $productid;
//   $qty = 1;
//   $cart = $objectManager->get('Magento\Checkout\Model\Cart');
//   $productRepo = $objectManager->get('Magento\Catalog\Model\ProductRepository');
//   $_product = $productRepo->getById($prod_id);
//         $productData = [
//              'qty' => $qty,
//              'product' => $prod_id,
//              // 'options' => $itemOptions
//               ];
//         $cart->addProduct($_product, $productData);
//         $cart->save();
//         $customerRepositoryInterface = $objectManager->get('Magento\Customer\Api\CustomerRepositoryInterface');
//         $customer= $customerRepositoryInterface->getById($userid);
//         $cart->getQuote()->assignCustomer($customer);
//          $cart->getQuote()->setTotalsCollectedFlag(false)->collectTotals();
//          $cart->getQuote()->save();
        // $cart->getQuote()->assignCustomer($customer);
        // $quote->save
        // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // $customerSession = $objectManager->create('Magento\Customer\Model\Session');
        // $product = $objectManager->get('Magento\Catalog\Model\Product')->load($productid);
        // $cartManagementInterface = $objectManager->get('Magento\Quote\Api\CartManagementInterface');
        //             $cartId = $cartManagementInterface->createEmptyCart(); //Create empty cart
        //             $sendcartRepositoryInterface = $objectManager->get('Magento\Quote\Api\CartRepositoryInterface');
        //             $quote = $sendcartRepositoryInterface->get($cartId); // load empty cart quote
        //             $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
        //             $quote->setStoreId($storeManager->getStore()->getId());
        //             $customerRepositoryInterface = $objectManager->get('Magento\Customer\Api\CustomerRepositoryInterface');
        //             $customer= $customerRepositoryInterface->getById($userid);
        //             $quote->setCurrency();
        //             $quote->assignCustomer($customer); 
        //             $productRepository = $objectManager->get('Magento\Catalog\Api\ProductRepositoryInterface');
        //             $_product = $productRepository->getById($productid);  
        //             // $_product = $this->productRepository->getById($value);   
        //             $params = [
        //                 'qty' => 1
        //             ];   
                    // $request = new Varien_Object($params);
                    // $quoteItem = $quote->addProduct($_product, $request);
                    // $quote->addProduct($_product,$params);    
                    // $quote->collectTotals()->save();

        
        
        header("Content-Type: application/json; charset=utf-8");
        $ns = json_encode($notif);
        print_r($ns,false);
        die();     
    }

    public function removeCartProduct($userid,$productid){

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $Adminusername = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('api/general/api_admin');
        $Adminpassword = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('api/general/api_password');
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $BaseUrl = $storeManager->getStore()->getBaseUrl();

        $userData = array("username" => $Adminusername, "password" => $Adminpassword);
        $adminUrl = $BaseUrl.'rest/V1/integration/admin/token';
        $ch = curl_init($adminUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Content-Lenght: " . strlen(json_encode($userData))));

        $token = curl_exec($ch);
        // print_r($token);
        $customerData = [
            'customer_id' => $userid
        ];
        $CartsUrl = $BaseUrl.'rest/V1/carts/mine';
        $ch = curl_init($CartsUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($customerData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . json_decode($token)));
        
        $result = curl_exec($ch);
        
        $quote_id = json_decode($result);
        $QuoteUrl = $BaseUrl."rest/V1/carts/".$quote_id;
        $ch = curl_init($QuoteUrl);
        // $ch = curl_init("https://smvatech.in/ecommerce/rest/V1/carts/".$quote_id);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . json_decode($token)));

        $result = curl_exec($ch);

        $result = json_decode($result , true);
        // echo '<pre>';print_r($result);

        $len = sizeof($result['items']);
        $check = 0 ; 
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 

        $productRepository = $objectManager->get('\Magento\Catalog\Model\ProductRepository');
        // $productObj = $productRepository->get('<SKU>');

        for ($i=0; $i < $len; $i++) { 
            $productObj = $productRepository->get($result['items'][$i]['sku']);
            // $product->loadByAttribute();
            // echo $result['items'][$i]['sku'];
            if($productObj->getId() == $productid){
                $check = 1;
                $itemId = $result['items'][$i]['item_id']; 
            }
        }

        if($check == 1){
            $url = $BaseUrl."rest/default/V1/carts/".$quote_id."/items/".$itemId;
            // echo $url;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            // curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . json_decode($token)));
            $result = curl_exec($ch);
            $result = json_decode($result);
            curl_close($ch);

            // $url = "https://smvatech.in/ecommerce/rest/default/V1/carts/".$quote_id."items/".$itemId;

            // $ch = curl_init("https://smvatech.in/ecommerce/rest/default/V1/carts/".$quote_id."items/".$itemId);
            // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            // curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . json_decode($token)));

            // $result = curl_exec($ch);
            // echo $result;
            $getHeaders = apache_request_headers();
            if(isset($getHeaders['lang'])){
                $lang = $getHeaders['lang'];
                if($lang == 1){
                    $msg = 'Product Removed';
                }else{
                    $msg = 'تمت إزالة المنتج';
                }
            }else{
                $msg = 'Product Removed';   
            }
            $notif = array('SuccessCode'=> 200 ,'message' => $msg, 'data' => $result);
        }else{
            $getHeaders = apache_request_headers();
            if(isset($getHeaders['lang'])){
                $lang = $getHeaders['lang'];
                if($lang == 1){
                    $msg = 'Product Not Found';
                }else{
                    $msg = 'المنتج غير موجود';
                }
            }else{
                $msg = 'Product Not Found';   
            }
            $notif = array('SuccessCode'=> 400 ,'message' => $msg, 'data' => '');
        }

        header("Content-Type: application/json; charset=utf-8");
        $ns = json_encode($notif);
        print_r($ns,false);
        die();
    }

    public function updateCartProduct($userid,$productid,$qty){

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $Adminusername = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('api/general/api_admin');
        $Adminpassword = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('api/general/api_password');
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $BaseUrl = $storeManager->getStore()->getBaseUrl();

        $userData = array("username" => $Adminusername, "password" => $Adminpassword);
        $adminUrl = $BaseUrl.'rest/V1/integration/admin/token';
        $ch = curl_init($adminUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Content-Lenght: " . strlen(json_encode($userData))));

        $token = curl_exec($ch);
        // print_r($token);
        $customerData = [
            'customer_id' => $userid
        ];
        $CartsUrl = $BaseUrl.'rest/V1/carts/mine';
        $ch = curl_init($CartsUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($customerData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . json_decode($token)));
        
        $result = curl_exec($ch);
        
        $quote_id = json_decode($result);

        $QuoteUrl = $BaseUrl."rest/V1/carts/".$quote_id;
        $ch = curl_init($QuoteUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . json_decode($token)));

        $result = curl_exec($ch);

        $result = json_decode($result , true);
        // echo '<pre>';print_r($result);

        $len = sizeof($result['items']);
        $check = 0 ; 
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 

        $productRepository = $objectManager->get('\Magento\Catalog\Model\ProductRepository');
        // $productObj = $productRepository->get('<SKU>');

        for ($i=0; $i < $len; $i++) { 
            $productObj = $productRepository->get($result['items'][$i]['sku']);
            // $product->loadByAttribute();
            // echo $result['items'][$i]['sku'];
            if($productObj->getId() == $productid){
                $check = 1;
                $itemId = $result['items'][$i]['item_id']; 
            }
        }

        if($check == 1){
            $url = $BaseUrl."rest/default/V1/carts/".$quote_id."/items/".$itemId;
            // echo $url;
            $json = array(
                'cartItem' => [
                    'qty' => $qty,
                    'quoteId' => $quote_id
                ]
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . json_decode($token)));
            $result = curl_exec($ch);
            $result = json_decode($result);
            curl_close($ch);
            // print_r($result);
            // $url = "https://smvatech.in/ecommerce/rest/default/V1/carts/".$quote_id."items/".$itemId;

            // $ch = curl_init("https://smvatech.in/ecommerce/rest/default/V1/carts/".$quote_id."items/".$itemId);
            // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            // curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . json_decode($token)));

            // $result = curl_exec($ch);
            // echo $result;
            $getHeaders = apache_request_headers();
            if(isset($getHeaders['lang'])){
                $lang = $getHeaders['lang'];
                if($lang == 1){
                    $msg = 'Product qty Updated';
                }else{
                    $msg = 'كمية المنتج محدثة';
                }
            }else{
                $msg = 'Product qty Updated';   
            }
            $notif = array('SuccessCode'=> 200 ,'message' => $msg, 'data' => $result);
        }else{
            $getHeaders = apache_request_headers();
            if(isset($getHeaders['lang'])){
                $lang = $getHeaders['lang'];
                if($lang == 1){
                    $msg = 'Product Not Found';
                }else{
                    $msg = 'المنتج غير موجود';
                }
            }else{
                $msg = 'Product Not Found';   
            }
            $notif = array('SuccessCode'=> 400 ,'message' => $msg, 'data' => '');
        }

        // $notif = array('FailureCode'=> 400 ,'message' => 'Product Not Found', 'data' => '');
        header("Content-Type: application/json; charset=utf-8");
        $ns = json_encode($notif);
        print_r($ns,false);
        die();
    }

}


