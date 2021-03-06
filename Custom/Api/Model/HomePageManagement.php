<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Custom\Api\Model;

use Magento\Catalog\Model\Product;

class HomePageManagement implements \Custom\Api\Api\HomePageManagementInterface
{

    /**
     * {@inheritdoc}
     */
    public function homePageBanner($identifier)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $banner = $objectManager->create(\Magiccart\Magicslider\Model\Magicslider::class);
        $collection = $banner->getCollection();
        $check = 0;
        if(isset($collection)){
            foreach($collection as $item){
                if($item['identifier'] == $identifier){
                    $bannerImage = $item['config'];
                    $check = 1;
                }
            }
            if($check == 1){
                $bannerImages = json_decode($bannerImage);
                $obj_user = (array)  $bannerImages;
                $obj_users = (array) $obj_user['media_gallery'];
                $obj_images = (array) $obj_users['images'];
                $len = sizeof($obj_images);
                foreach ($obj_images as $key => $value) {
                    $bannersLists[] = array('bannerId' => $value->position, 'bannerImg' => 'https://smvatech.in/ecommerce/pub/media/magiccart/magicslider'.$value->file);
                }
                $passData = array('bannerList' => $bannersLists);
                $data = $passData;
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
                $notif = array('SuccessCode'=> 200 ,'message' => $msg, 'data' => $data);
            }else{
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
                $notif = array('SuccessCode'=> 400 , 'message' => $msg , 'data' => '');
            }
        }else{
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
                $notif = array('SuccessCode'=> 400 , 'message' => $msg , 'data' => '');
        }
        
        header("Content-Type: application/json; charset=utf-8");
        $ns = json_encode($notif);
        print_r($ns,false);
        die();
    }

    public function homePageSearch($token,$name,$pageNo,$pageSize,$key,$value,$skey,$sorder){
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
        $pageNumber = $pageNo;
        $pageLimit = $pageSize;
        $checks = 0;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
        /** Apply filters here */
        $getHeaders = apache_request_headers();
            if(isset($getHeaders['lang'])){
                $lang = $getHeaders['lang'];
                if(isset($skey) && $skey != "null"){
                    if($sorder == 0){
                        $collection = $productCollection->addAttributeToSelect('*')->addStoreFilter($lang)->addAttributeToFilter('name', ['like'=>'%'.$name.'%'])->setOrder($skey,'ASC')->setPageSize($pageLimit)->setCurPage($pageNumber)->load();
                        // $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setOrder($skey,'ASC')->setPageSize($pageLimit)->setCurPage($pageNumber);
                    }else{
                        $collection = $productCollection->addAttributeToSelect('*')->addStoreFilter($lang)->addAttributeToFilter('name', ['like'=>'%'.$name.'%'])->setOrder($skey,'DESC')->setPageSize($pageLimit)->setCurPage($pageNumber)->load();
                        // $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setOrder($skey,'DESC')->setPageSize($pageLimit)->setCurPage($pageNumber); 
                    }
                }elseif(isset($key) && $key != "null"){
                    $collection = $productCollection->addAttributeToSelect('*')->addStoreFilter($lang)->addAttributeToFilter('name', ['like'=>'%'.$name.'%'])->setPageSize($pageLimit)->setCurPage($pageNumber)->load();
                    // $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setPageSize($pageLimit)->setCurPage($pageNumber);
                }else{
                    $collection = $productCollection->addAttributeToSelect('*')->addStoreFilter($lang)->addAttributeToFilter('name', ['like'=>'%'.$name.'%'])->setPageSize($pageLimit)->setCurPage($pageNumber)->load();
                    // $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setPageSize($pageLimit)->setCurPage($pageNumber); 
                }
            }else{
                if(isset($skey) && $skey != "null"){
                    if($sorder == 0){
                        $collection = $productCollection->addAttributeToSelect('*')->addAttributeToFilter('name', ['like'=>'%'.$name.'%'])->setOrder($skey,'ASC')->setPageSize($pageLimit)->setCurPage($pageNumber)->load();
                        // $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setOrder($skey,'ASC')->setPageSize($pageLimit)->setCurPage($pageNumber);
                    }else{
                        $collection = $productCollection->addAttributeToSelect('*')->addAttributeToFilter('name', ['like'=>'%'.$name.'%'])->setOrder($skey,'DESC')->setPageSize($pageLimit)->setCurPage($pageNumber)->load();
                        // $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setOrder($skey,'DESC')->setPageSize($pageLimit)->setCurPage($pageNumber); 
                    }
                }elseif(isset($key) && $key != "null"){
                    $collection = $productCollection->addAttributeToSelect('*')->addAttributeToFilter('name', ['like'=>'%'.$name.'%'])->setPageSize($pageLimit)->setCurPage($pageNumber)->load();
                    // $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setPageSize($pageLimit)->setCurPage($pageNumber);
                }else{
                    $collection = $productCollection->addAttributeToSelect('*')->addAttributeToFilter('name', ['like'=>'%'.$name.'%'])->setPageSize($pageLimit)->setCurPage($pageNumber)->load();
                    // $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setPageSize($pageLimit)->setCurPage($pageNumber); 
                }   
            }
            
            // print_r($collection->getData()); exit();
        if(isset($collection)){   
            $helperImport = $objectManager->get('\Magento\Catalog\Helper\Image');
            $serachCollection = [];
            foreach ($collection as $products) 
            {
                // print_r($products->getEntityId()); exit();
                $getHeaders = apache_request_headers();
                if(isset($getHeaders['lang'])){
                    $lang = $getHeaders['lang'];
                    $product = $objectManager->get('Magento\Catalog\Model\Product')->setStoreId($lang)->load($products->getEntityId());
                }else{
                    // $lang = $getHeaders['lang'];
                    $product = $objectManager->get('Magento\Catalog\Model\Product')->load($products->getEntityId());
                }
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                // $productStockObj = $objectManager->get('Magento\CatalogInventory\Api\StockRegistryInterface')->getStockItem($products->getEntityId());
                // print_r($productStockObj->getData()); exit();
                // print_r($product->getId()); exit();
                $imageUrl = $helperImport->init($product, 'product_page_image_small')
                                ->setImageFile($product->getSmallImage()) // image,small_image,thumbnail
                                ->resize(380)
                                ->getUrl();
                            if(isset($skey) && $skey != "null"){
                                    $checks = 1;
                                    $productStockObj = $objectManager->get('Magento\CatalogInventory\Api\StockRegistryInterface')->getStockItem($product->getId());
                                    $serachCollection[] =  array('prodId' => $product->getId() , 'prodName' => $product->getName() , 'price' =>  $product->getPrice() , 'prodAmount' => $product->getFinalPrice(),
                                    'is_in_stock' => boolval($productStockObj['is_in_stock']) ? true : false , 'qty' => $productStockObj['qty'], 'prodImage' => $imageUrl , 'isFavorite' => $isfav  );
                            }elseif($skey == "null" && $key == "null"){
                                $checks = 1;
                                $productStockObj = $objectManager->get('Magento\CatalogInventory\Api\StockRegistryInterface')->getStockItem($product->getId());
                                    $serachCollection[] =  array('prodId' => $product->getId() , 'prodName' => $product->getName() , 'price' =>  $product->getPrice() , 'prodAmount' => $product->getFinalPrice(),
                                    'is_in_stock' => boolval($productStockObj['is_in_stock']) ? true : false, 'qty' => $productStockObj['qty'], 'prodImage' => $imageUrl , 'isFavorite' => $isfav  );
                            }else{
                                if($key == 'price'){
                                    // print_r($product->getId()); exit();
                                    $checks = 1;
                                    $keyprice = explode("-",$value);
                                    if($product->getPrice() >= $keyprice[0] && $product->getPrice() <= $keyprice[1])
                                    {
                                        $productStockObj = $objectManager->get('Magento\CatalogInventory\Api\StockRegistryInterface')->getStockItem($product->getId());
                                        $serachCollection[] =  array('prodId' => $product->getId() , 'prodName' => $product->getName() , 'price' =>  $product->getPrice() , 'prodAmount' => $product->getFinalPrice(),
                                        'is_in_stock' => boolval($productStockObj['is_in_stock']) ? true : false, 'qty' => $productStockObj['qty'], 'prodImage' => $imageUrl , 'isFavorite' => $isfav  );
                                    }
                                }else{
                                    $checks = 1;
                                    if($product->getData($key) == $value){
                                        $productStockObj = $objectManager->get('Magento\CatalogInventory\Api\StockRegistryInterface')->getStockItem($product->getId());
                                        $serachCollection[] =  array('prodId' => $product->getId() , 'prodName' => $product->getName() , 'price' =>  $product->getPrice() , 'prodAmount' => $product->getFinalPrice(),
                                        'is_in_stock' => boolval($productStockObj['is_in_stock']) ? true : false, 'qty' => $productStockObj['qty'], 'prodImage' => $imageUrl , 'isFavorite' => $isfav  );
                                    }
                                }
                            }
            }         
            // foreach ($collection as $product){
            //     $checks = 1;
            //     $helperImport = $objectManager->get('\Magento\Catalog\Helper\Image');

            //     $imageUrl = $helperImport->init($product, 'product_page_image_small')
            //                     ->setImageFile($product->getSmallImage()) // image,small_image,thumbnail
            //                     ->resize(380)
            //                     ->getUrl();
            //     $serachCollection[] = array('prodId' => $product->getId() , 'prodName' => $product->getName() , 'prodAmount' => $product->getPrice() ,
            //     'prodImage' => $imageUrl , 'isFavorite' => true 
            // );
            // }  
            if($checks == 1 && $serachCollection != ""){
                $data = $serachCollection;
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
                $notif = array('SuccessCode'=> 200 ,'message' => $msg, 'data' => array('Products' => $data));
            }else{
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
                $notif = array('SuccessCode'=> 400 , 'message' => $msg , 'data' => '');    
            }
        }else{
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
            $notif = array('SuccessCode'=> 400 , 'message' => $msg , 'data' => '');
        }
        header("Content-Type: application/json; charset=utf-8");
        $ns = json_encode($notif);
        print_r($ns,false);
        die();
    } 
    public function getCategory($parentCatId , $lang = null)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // instance of object manager
        $categoryRepository = $objectManager->create('Magento\Catalog\Model\CategoryRepository');
        $parentcategories = $categoryRepository->get($parentCatId);
        $categories = $parentcategories->getChildrenCategories();
        $i=0;
        $ChildCategoryValue = [];

        
        foreach($categories as $category){
            $getHeaders = apache_request_headers();
            if(isset($getHeaders['lang'])){
                $lang = $getHeaders['lang'];
                $categorys = $objectManager->create('\Magento\Catalog\Model\Category')->setStoreId($lang)->load($category->getId());
            }else{
                $categorys = $objectManager->create('\Magento\Catalog\Model\Category')->load($category->getId());   
            }
            if(!empty($categorys->getImageUrl())){
                $ChildCategoryValue[$i] = ['categoryName' => $categorys->getName(), 'categoryId' => $categorys->getId() ,'categoryimage' => 'https://smvatech.in'.$categorys->getImageUrl()];
            }else{
                $ChildCategoryValue[$i] = ['categoryName' => $categorys->getName(), 'categoryId' => $categorys->getId() ,'categoryimage' => ''];
            }
            if(isset($getHeaders['lang'])){
                $lang = $getHeaders['lang'];
                $childCat = $this->getCategory($category->getId(),$lang);
            }else{
                $childCat = $this->getCategory($category->getId());
            }
            if($childCat){
                $ChildCategoryValue[$i]['child'] = $childCat;
            }else{
                $ChildCategoryValue[$i]['child'] = [];
            }   
            $i++;
        }

        return $ChildCategoryValue;
    }

    public function getTopCategory($parentCatId , $lang = null)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // instance of object manager

        $categoryRepository = $objectManager->create('Magento\Catalog\Model\CategoryRepository');
        $parentcategories = $categoryRepository->get($parentCatId);
        $categories = $parentcategories->getChildrenCategories();
        $i=0;
        $ChildCategoryValue = [];

        
        foreach($categories as $category){
            $getHeaders = apache_request_headers();
            if(isset($getHeaders['lang'])){
                $lang = $getHeaders['lang'];
                $categorys = $objectManager->create('\Magento\Catalog\Model\Category')->setStoreId($lang)->load($category->getId());
            }else{
                $categorys = $objectManager->create('\Magento\Catalog\Model\Category')->load($category->getId());   
            }
            $categorys->getCustomCategoryAttributes();
            if ($categorys->getCustomCategoryAttributes() == 1)
            {
                if(!empty($categorys->getImageUrl())){
                    $ChildCategoryValue[$i] = ['categoryName' => $categorys->getName(), 'categoryId' => $category->getId() ,'categoryimage' => 'https://smvatech.in'.$categorys->getImageUrl()];
                }else{
                    $ChildCategoryValue[$i] = ['categoryName' => $categorys->getName(), 'categoryId' => $category->getId() ,'categoryimage' => $categorys->getImageUrl()];
                }
                $getHeaders = apache_request_headers();
                if(isset($getHeaders['lang'])){
                    $lang = $getHeaders['lang'];
                    $childCat = $this->getTopCategory($category->getId(),$lang);
                }else{
                    $childCat = $this->getTopCategory($category->getId());
                }
                if($childCat){
                    $ChildCategoryValue[$i]['child'] = $childCat;
                }else{
                    $ChildCategoryValue[$i]['child'] = array("");
                }  
                $i++;
            }
        }

        return $ChildCategoryValue;
    }

    public function homePageCategories(){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // instance of object manager

        $parentCategoryId = 2; // You have to set website root category id here
        if(isset($getHeaders['lang'])){
            $lang = $getHeaders['lang'];
            $category = $objectManager->create('Magento\Catalog\Model\Category')->setStoreId($lang)->load($parentCategoryId);
        }else{
            $category = $objectManager->create('Magento\Catalog\Model\Category')->load($parentCategoryId);  
        }
            
        $childCategory['parent']['name'] = $category->getName();
        $childCategory['parent']['id'] = $category->getId();
        $getHeaders = apache_request_headers();
        if(isset($getHeaders['lang'])){
            $lang = $getHeaders['lang'];
            $childCategory['parent']["child"] = $this->getCategory($parentCategoryId,$lang);
        }else{
            $childCategory['parent']["child"] = $this->getCategory($parentCategoryId);
        }

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
        $notif = array('SuccessCode'=> 200 ,'message' => $msg, 'data' => array('categories' => $childCategory['parent']["child"]));
        header("Content-Type: application/json; charset=utf-8");
        $ns = json_encode($notif);
        print_r($ns,false);
        die();  
    }
    public function homePageTopCategories($token){
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
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // instance of object manager
        $parentCategoryId = 2; // You have to set website root category id here
        $category = $objectManager->create('Magento\Catalog\Model\Category')->load($parentCategoryId);
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $categorysFactory = $objectManager->get('\Magento\Catalog\Model\CategoryFactory');
        $i=0;
        $getHeaders = apache_request_headers();
            if(isset($getHeaders['lang'])){
                $lang = $getHeaders['lang'];
                $childCategory['parent']["child"] = $this->getTopCategory($parentCategoryId,$lang);
            }else{
                $childCategory['parent']["child"] = $this->getTopCategory($parentCategoryId);
            }
        $len= sizeof($childCategory['parent']["child"]);
        for ($i=0;$i<$len;$i++){
            $category = $categorysFactory->create()->load($childCategory['parent']["child"][$i]['categoryId']);
            $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*');
            $helperImport = $objectManager->get('\Magento\Catalog\Helper\Image');
            $productscollections = [];  
            foreach ($categoryProducts as $products) 
            {
                $getHeaders = apache_request_headers();
                if(isset($getHeaders['lang'])){
                    $lang = $getHeaders['lang'];
                    $product = $objectManager->get('Magento\Catalog\Model\Product')->setStoreId($lang)->load($products->getId());
                }else{
                    $product = $objectManager->get('Magento\Catalog\Model\Product')->load($products->getId());
                }
                $imageUrl = $helperImport->init($product, 'product_page_image_small')
                                ->setImageFile($product->getSmallImage()) // image,small_image,thumbnail
                                ->resize(380)
                                ->getUrl();
                                $productStockObj = $objectManager->get('Magento\CatalogInventory\Api\StockRegistryInterface')->getStockItem($product->getId());
                 $productscollections[] =  array('prodId' => $product->getId() , 'prodName' => $product->getName() ,'price' => $product->getPrice() ,
                 'is_in_stock' => boolval($productStockObj['is_in_stock']) ? true : false,'prodAmount' => $product->getFinalPrice() , 'prodImage' => $imageUrl , 'isFavorite' => $isfav  );
            }  
            if(!empty($childCategory['parent']["child"][$i]['categoryimage'])){
                $productscollection[]=array('topCategories' => array('catName'=> $childCategory['parent']["child"][$i]['categoryName'] , 'catId'=> $childCategory['parent']["child"][$i]['categoryId'], 'categoryImg'=> $childCategory['parent']["child"][$i]['categoryimage'] ,'Products'=> $productscollections));
            }else{
                $productscollection[]=array('topCategories' => array('catName'=> $childCategory['parent']["child"][$i]['categoryName'] , 'catId'=> $childCategory['parent']["child"][$i]['categoryId'], 'categoryImg'=> '' ,'Products'=> $productscollections));
            } 
            /*$productscollection[]=array('topCategories' => array('catName'=> $childCategory['parent']["child"][$i]['categoryName'] , 'catId'=> $childCategory['parent']["child"][$i]['categoryId'] , 'bannerImg'=> $childCategory['parent']["child"][$i]['categoryimage']
            ,'Products'=> $productscollections)); */
             
              
        }
        if($productscollection != null){
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
            $notif = array('SuccessCode'=> 200 ,'message' => $msg, 'data' => array('categoery' => $productscollection));
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
    public function homePageProductBrand(){
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
        $entityAttribute = $objectManager->get('Magento\Eav\Model\ResourceModel\Entity\Attribute');
        $attributeid = $entityAttribute->getIdByCode('catalog_product', 'manufacturer');
        $attributeOptionCollection = $objectManager->create('\Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection');
        $attributeOptions = $attributeOptionCollection
				->setPositionOrder('asc')
				->setAttributeFilter($attributeid)
				->setStoreFilter()
				->load();
        
        /*$brand = $objectManager->create('Magiccart\Shopbrand\Block\Widget\Brand');
        $collection = $brand->getBrands();
        print_r($collection->getData());*/

        $branding = $objectManager->create(\Magiccart\Shopbrand\Model\Shopbrand::class);
        $collect = $branding->getCollection();
        /*print_r($collect->getData());*/

        foreach($attributeOptions as $value){
            
            foreach($collect as $val){
                if($value->getOptionId() === $val->getOptionId())
                {
                    $attributedata[] = array('name' => $value->getDefaultValue() , 'brandId' => $value->getOptionId() , 'brandImage' => 'https://smvatech.in/ecommerce/pub/media/'.$val->getImage() );
                    break;
                }
            }
        }
        if($attributedata != null){
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
            $notif = array('SuccessCode'=> 200 ,'message' => $msg, 'data' => array('Brands' => $attributedata));
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
        public function homePageProductBrandOptions()
        {
           $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
           $attributeData = $objectManager->get(\Magento\Eav\Model\Entity\Attribute::class)
          ->loadByCode('catalog_product', 'manufacturer');

         //get attribute options
         $attributeOptionCollection = $objectManager->create('\Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection');
         $attributeOptions = $attributeOptionCollection
                ->setPositionOrder('asc')
                ->setAttributeFilter($attributeData->getAttributeId())
                ->setStoreFilter()
                ->load();

        foreach ($attributeOptions as $option) {
        $attributedata[] = array('name' => $option->getDefaultValue() , 'brandId' => $option->getOptionId() );
        }
        if($attributedata != null){
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
            $notif = array('SuccessCode'=> 200 ,'message' => $msg, 'data' => array('Brands' => $attributedata));
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

    /*changes start*/
    public function productList($token,$categoryId,$subcategoryId,$pageNo,$pageSize,$key,$value,$skey,$sorder){
        $send = array("Authorization: Bearer ".$token);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $BaseUrl = $storeManager->getStore()->getBaseUrl();
        
        $res = $BaseUrl.'rest/default/V1/customers/me';
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

        $pageNumber = $pageNo;
        $pageLimit = $pageSize;
        $start = $key;
        $end = $value;


        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // instance of object manager
        $request = $objectManager->get('\Magento\Framework\App\RequestInterface');

        /*$pageNo=($request->getPost('pageNo'))? $request->getPost('pageNo') : 1;
        $pageSize=($request->getPost('pageSize'))? $request->getPost('pageSize') : 1;

        $from=($request->getPost('from'))? $request->getPost('from') : 1;
        $to=($request->getPost('to'))? $request->getPost('to') : 1;*/

        $name= $request->getPost('name');
        $price= $request->getPost('price');
        $weight= $request->getPost('weight');
        
        /*$parentCategoryId = 2; // You have to set website root category id here*/
        $parentCategoryId = $categoryId; // You have to set website root category id here
        $childCategoryId = $subcategoryId;
        $category = $objectManager->create('Magento\Catalog\Model\Category')->load($parentCategoryId);
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $categorysFactory = $objectManager->get('\Magento\Catalog\Model\CategoryFactory');
        $i=0;
        /*$childCategory['parent']["child"] = $this->getTopCategory($parentCategoryId);*/
        $getHeaders = apache_request_headers();
            if(isset($getHeaders['lang'])){
                $lang = $getHeaders['lang'];
                $childCategory['parent']["child"] = $this->getCategory($parentCategoryId,$lang);
            }else{
                $childCategory['parent']["child"] = $this->getCategory($parentCategoryId);
            }
        $len= sizeof($childCategory['parent']["child"]);

        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $viewId = $storeManager->getStore()->getStoreId();
        
        for ($i=0;$i<$len;$i++){
                $category = $categorysFactory->create()->load($childCategory['parent']["child"][$i]['categoryId']); 
            // if($skey != "null" && $key != ""){
            //     $notif = array('FailureCode'=> 400 ,'message' => 'Plz enter only one Key Value', 'data' => '');
            //     header("Content-Type: application/json; charset=utf-8");
            //     $ns = json_encode($notif);
            //     print_r($ns,false);
            //     die();
            // }
            // else
            if(isset($skey) && $skey != "null"){
                if($sorder == 0){
                    $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setOrder($skey,'ASC')->setPageSize($pageLimit)->setCurPage($pageNumber);
                }else{
                    $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setOrder($skey,'DESC')->setPageSize($pageLimit)->setCurPage($pageNumber); 
                }
            }elseif(isset($key) && $key != "null"){
                $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setPageSize($pageLimit)->setCurPage($pageNumber);
            }else{
                $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setPageSize($pageLimit)->setCurPage($pageNumber); 
            }
            
            
            // if(isset($name))
            // {
            //     if($name == 0)
            //     {
            //         /*$categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setOrder('name','ASC');*/
            //         $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setOrder('name','ASC')->setPageSize($pageLimit)->setCurPage($pageNumber);;
            //     }
            //     elseif ($name == 1) {
            //         /*$categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setOrder('name','DESC');*/
            //         $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setOrder('name','DESC')->setPageSize($pageLimit)->setCurPage($pageNumber);;
            //     }
            // }
            // elseif(isset($price))
            // {
            //     if($price == 0)
            //     {
            //         /*$categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setOrder('price','ASC');*/
            //         $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setOrder('price','ASC')->setPageSize($pageLimit)->setCurPage($pageNumber);
            //     }
            //     elseif ($price == 1) {
            //         /*$categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setOrder('price','DESC');*/
            //         $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setOrder('price','DESC')->setPageSize($pageLimit)->setCurPage($pageNumber);
            //     }
            // }
            // elseif(isset($weight))
            // {
            //     if($price == 0)
            //     {
            //         /*$categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setOrder('price','ASC');*/
            //         $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setOrder('weight','ASC')->setPageSize($pageLimit)->setCurPage($pageNumber);
            //     }
            //     elseif ($price == 1) {
            //         /*$categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setOrder('price','DESC');*/
            //         $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setOrder('weight','DESC')->setPageSize($pageLimit)->setCurPage($pageNumber);
            //     }
            // }
            // else
            // {
            //     /*$categoryProducts = $category->getProductCollection()->addAttributeToSelect('*');*/
            //     $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setPageSize($pageLimit)->setCurPage($pageNumber);
            // }

            $helperImport = $objectManager->get('\Magento\Catalog\Helper\Image');
            $productscollections = [];
            if($childCategory['parent']["child"][$i]['categoryId']  == $childCategoryId)
            {
                foreach ($categoryProducts as $products) 
                {
                    $getHeaders = apache_request_headers();
                    if(isset($getHeaders['lang'])){
                        $lang = $getHeaders['lang'];
                        $product = $objectManager->get('Magento\Catalog\Model\Product')->setStoreId($lang)->load($products->getId());
                    }else{
                        // $lang = $getHeaders['lang'];
                        $product = $objectManager->get('Magento\Catalog\Model\Product')->load($products->getId());
                    }
                    /*print_r($product->getData());*/
                    $imageUrl = $helperImport->init($product, 'product_page_image_small')
                                    ->setImageFile($product->getSmallImage()) // image,small_image,thumbnail
                                    ->resize(380)
                                    ->getUrl();
                                    if(isset($skey) && $skey != "null"){
                                        $productStockObj = $objectManager->get('Magento\CatalogInventory\Api\StockRegistryInterface')->getStockItem($product->getId());
                                            $productscollections[] =  array('prodId' => $product->getId() , 'prodName' => $product->getName() , 'price' =>  $product->getPrice() , 'prodAmount' => $product->getFinalPrice(),
                                            'is_in_stock' => boolval($productStockObj['is_in_stock']) ? true : false , 'qty' => $productStockObj['qty'],'prodImage' => $imageUrl , 'isFavorite' => $isfav  );
                                    }elseif($skey == "null" && $key == "null"){
                                        $productStockObj = $objectManager->get('Magento\CatalogInventory\Api\StockRegistryInterface')->getStockItem($product->getId());
                                        $productscollections[] =  array('prodId' => $product->getId() , 'prodName' => $product->getName() , 'price' =>  $product->getPrice() , 'prodAmount' => $product->getFinalPrice(),
                                        'is_in_stock' => boolval($productStockObj['is_in_stock']) ? true : false , 'qty' => $productStockObj['qty'],'prodImage' => $imageUrl , 'isFavorite' => $isfav  );
                                    }else{
                                        if($key == 'price'){
                                            $keyprice = explode("-",$value);
                                            if($product->getPrice() >= $keyprice[0] && $product->getPrice() <= $keyprice[1])
                                            {
                                                $productStockObj = $objectManager->get('Magento\CatalogInventory\Api\StockRegistryInterface')->getStockItem($product->getId());
                                                $productscollections[] =  array('prodId' => $product->getId() , 'prodName' => $product->getName() , 'price' =>  $product->getPrice() , 'prodAmount' => $product->getFinalPrice(),
                                                'is_in_stock' => boolval($productStockObj['is_in_stock']) ? true : false , 'qty' => $productStockObj['qty'],'prodImage' => $imageUrl , 'isFavorite' => $isfav  );
                                            }
                                        }else{
                                            if($product->getData($key) == $value){
                                                $productStockObj = $objectManager->get('Magento\CatalogInventory\Api\StockRegistryInterface')->getStockItem($product->getId());
                                                $productscollections[] =  array('prodId' => $product->getId() , 'prodName' => $product->getName(), 'price' =>  $product->getPrice() , 'prodAmount' => $product->getFinalPrice(),
                                                'is_in_stock' => boolval($productStockObj['is_in_stock']) ? true : false , 'qty' => $productStockObj['qty'],'prodImage' => $imageUrl , 'isFavorite' => $isfav  );
                                            }
                                        }
                                    }
                    // if($product->getPrice() >= $start && $product->getPrice() <= $end)                                   
                    // {
                    //     $productscollections[] =  array('prodId' => $product->getId() , 'prodName' => $product->getName() , 'prodAmount' => $product->getPrice() ,
                    //     'prodImage' => $imageUrl , 'isFavorite' => $isfav  );
                    // }
                }   
                /*$productscollection[]=array('topCategories' => array('catName'=> $childCategory['parent']["child"][$i]['categoryName'] , 'catId'=> $childCategory['parent']["child"][$i]['categoryId'] , 'bannerImg'=> $childCategory['parent']["child"][$i]['categoryimage']
                ,'Products'=> $productscollections)); */
                if(!empty($childCategory['parent']["child"][$i]['categoryimage'])){
                    $productscollection[]=array('subCategories' => array('catName'=> $childCategory['parent']["child"][$i]['categoryName'] , 'catId'=> $childCategory['parent']["child"][$i]['categoryId'] ,'categoryImg'=> $childCategory['parent']["child"][$i]['categoryimage'], 'viewId'=> $viewId , 'Products'=> $productscollections));
                    // $productscollection[]=array('topCategories' => array('catName'=> $childCategory['parent']["child"][$i]['categoryName'] , 'catId'=> $childCategory['parent']["child"][$i]['categoryId'], 'categoryImg'=> $childCategory['parent']["child"][$i]['categoryimage'] ,'Products'=> $productscollections));
                }else{
                    $productscollection[]=array('subCategories' => array('catName'=> $childCategory['parent']["child"][$i]['categoryName'] , 'catId'=> $childCategory['parent']["child"][$i]['categoryId'],'categoryImg'=>  '' , 'viewId'=> $viewId , 'Products'=> $productscollections));
                    // $productscollection[]=array('topCategories' => array('catName'=> $childCategory['parent']["child"][$i]['categoryName'] , 'catId'=> $childCategory['parent']["child"][$i]['categoryId'], 'categoryImg'=>  ,'Products'=> $productscollections));
                } 
            }
        }

        if($productscollection != null){
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
            $notif = array('SuccessCode'=> 200 ,'message' => $msg, 'data' => array('categoery' => $productscollection));
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
            $notif = array('FailureCode'=> 400 ,'message' => $msg, 'data' => '');
        }
        header("Content-Type: application/json; charset=utf-8");
        $ns = json_encode($notif);
        print_r($ns,false);
        die(); 
    }


    public function categorySearch($token,$categoryName,$pageNo,$pageSize,$key,$value,$skey,$sorder){
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

        $pageNumber = $pageNo;
        $pageLimit = $pageSize;
        $start = $key;
        $end = $value;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // instance of object manager
        $request = $objectManager->get('\Magento\Framework\App\RequestInterface');

        /*$page=($request->getPost('pageNo'))? $request->getPost('pageNo') : 1;
        $pageSize=($request->getPost('pageSize'))? $request->getPost('pageSize') : 1;

        $from=($request->getPost('from'))? $request->getPost('from') : 1;
        $to=($request->getPost('to'))? $request->getPost('to') : 1;*/

        
        $name= $request->getPost('name');
        
        $price= $request->getPost('price');
        
        /*$categoryId = $request->getPost('categoryId');*/
        
        /*$categoryName = $request->getPost('categoryName');*/
        

        /*$parentCategoryId = 2; // You have to set website root category id here*/
        /*$parentCategoryId = $categoryId; // You have to set website root category id here*/
        /*$category = $objectManager->create('Magento\Catalog\Model\Category')->load($parentCategoryId);
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();*/
        $categorysFactory = $objectManager->get('\Magento\Catalog\Model\CategoryFactory');
        $i=0;
        /*$childCategory['parent']["child"] = $this->getTopCategory($parentCategoryId);*/
        
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $viewId = $storeManager->getStore()->getStoreId();
        
        $cname = $categoryName;
        /*
        if(isset($categoryId))
        {
            $category = $categorysFactory->create()->load($parentCategoryId);
            $childCategory['parent']["child"] = $this->getCategory($parentCategoryId);
        }*/
        

        if(isset($cname))
        {
            // $collection = $categorysFactory->create()->getCollection()->addAttributeToFilter('name',$cname);
            $collection = $categorysFactory->create()->getCollection()->addAttributeToFilter(
                [
                 ['attribute' => 'name', 'like' => '%'.$cname.'%']
                ]);
            if ($collection->getSize()) {
                $catId = $collection->getFirstItem()->getId();
            }
            if(isset($catId))
            {
                $category = $categorysFactory->create()->load($catId);
                $childCategory['parent']["child"] = $this->getCategory($catId);
            }
            // echo $catId;
        }
        if(isset($childCategory['parent']["child"]))
        {
            $len= sizeof($childCategory['parent']["child"]);
            // if($sorder == 0){
            //     $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setOrder($key,'ASC')->setPageSize($pageLimit)->setCurPage($pageNumber);
            // }else{
            //     $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setOrder($key,'DESC')->setPageSize($pageLimit)->setCurPage($pageNumber); 
            // }
            // if($skey != "" && $key != ""){
            //     $notif = array('FailureCode'=> 400 ,'message' => 'Plz enter only one Key Value', 'data' => '');
            //     header("Content-Type: application/json; charset=utf-8");
            //     $ns = json_encode($notif);
            //     print_r($ns,false);
            //     die();
            // }
            // else
            if(isset($skey) && $skey != "null"){
                if($sorder == 0){
                    $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setOrder($skey,'ASC')->setPageSize($pageLimit)->setCurPage($pageNumber);
                }else{
                    $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setOrder($skey,'DESC')->setPageSize($pageLimit)->setCurPage($pageNumber); 
                }
            }elseif(isset($key) && $key != "null"){
                $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setPageSize($pageLimit)->setCurPage($pageNumber);
            }else{
                $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setPageSize($pageLimit)->setCurPage($pageNumber); 
            }
            // if(isset($name))
            // {
            //     if($name == 0)
            //     {
            //         /*$categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setOrder('name','ASC');*/
            //         $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setOrder('name','ASC')->setPageSize($pageLimit)->setCurPage($pageNumber);;
            //     }
            //     elseif ($name == 1) {
            //         /*$categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setOrder('name','DESC');*/
            //         $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setOrder('name','DESC')->setPageSize($pageLimit)->setCurPage($pageNumber);;
            //     }
            // }
            // elseif(isset($price))
            // {
            //     if($price == 0)
            //     {
            //         /*$categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setOrder('price','ASC');*/
            //         $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setOrder('price','ASC')->setPageSize($pageLimit)->setCurPage($pageNumber);
            //     }
            //     elseif ($price == 1) {
            //         /*$categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setOrder('price','DESC');*/
            //         $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setOrder('price','DESC')->setPageSize($pageLimit)->setCurPage($pageNumber);
            //     }
            // }
            // else
            // {
            //     /*$categoryProducts = $category->getProductCollection()->addAttributeToSelect('*');*/
            //     $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setPageSize($pageLimit)->setCurPage($pageNumber);
            // }
            
            $helperImport = $objectManager->get('\Magento\Catalog\Helper\Image');
            $productscollections = [];
            foreach ($categoryProducts as $products) 
            {
                $getHeaders = apache_request_headers();
                if(isset($getHeaders['lang'])){
                    $lang = $getHeaders['lang'];
                    $product = $objectManager->get('Magento\Catalog\Model\Product')->setStoreId($lang)->load($products->getId());
                }else{
                    // $lang = $getHeaders['lang'];
                    $product = $objectManager->get('Magento\Catalog\Model\Product')->load($products->getId());
                }
                /*print_r($product->getData());*/
                $imageUrl = $helperImport->init($product, 'product_page_image_small')
                                ->setImageFile($product->getSmallImage()) // image,small_image,thumbnail
                                ->resize(380)
                                ->getUrl();
                                if(isset($skey) && $skey != "null"){
                                    $productscollections[] =  array('prodId' => $product->getId() , 'prodName' => $product->getName() , 'price' =>  $product->getPrice() , 'prodAmount' => $product->getFinalPrice(),
                                    'prodImage' => $imageUrl , 'isFavorite' => $isfav  );
                            }
                            elseif($skey == "null" && $key == "null"){
                                $productscollections[] =  array('prodId' => $product->getId() , 'prodName' => $product->getName() , 'price' =>  $product->getPrice() , 'prodAmount' => $product->getFinalPrice(),
                                    'prodImage' => $imageUrl , 'isFavorite' => $isfav  );
                            }else{
                                if($key == 'price'){
                                    $keyprice = explode("-",$value);
                                    if($product->getPrice() >= $keyprice[0] && $product->getPrice() <= $keyprice[1])
                                    {
                                        $productscollections[] =  array('prodId' => $product->getId() , 'prodName' => $product->getName() , 'price' =>  $product->getPrice() , 'prodAmount' => $product->getFinalPrice(),
                                        'prodImage' => $imageUrl , 'isFavorite' => $isfav  );
                                    }
                                }else{
                                    if($product->getData($key) == $value){
                                        $productscollections[] =  array('prodId' => $product->getId() , 'prodName' => $product->getName() , 'price' =>  $product->getPrice() , 'prodAmount' => $product->getFinalPrice(),
                                        'prodImage' => $imageUrl , 'isFavorite' => $isfav  );
                                    }
                                }
                            }
                // if($product->getPrice() >= $from && $product->getPrice() <= $to)
                // {
                //     $productscollections[] =  array('prodId' => $product->getId() , 'prodName' => $product->getName() , 'prodAmount' => $product->getPrice() ,
                //     'prodImage' => $imageUrl , 'isFavorite' => $isfav  );
                // }
            }   
            /*$productscollection[]=array('topCategories' => array('catName'=> $childCategory['parent']["child"][$i]['categoryName'] , 'catId'=> $childCategory['parent']["child"][$i]['categoryId'] , 'bannerImg'=> $childCategory['parent']["child"][$i]['categoryimage']
                ,'Products'=> $productscollections)); */
            /*$productscollection[]=array('Products'=> $productscollections); */
                  
            

            if($productscollections != null){
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
                /*$notif = array('SuccessCode'=> 200 ,'message' => 'Transaction was Successful', 'data' => array('categoery' => $productscollection));*/
                $notif = array('SuccessCode'=> 200 ,'message' => $msg, 'data' => array('Products'=> $productscollections));
                
            }
            else{
                $getHeaders = apache_request_headers();
                if(isset($getHeaders['lang'])){
                    $lang = $getHeaders['lang'];
                    if($lang == 1){
                        $msg = 'Category Not Found';
                    }else{
                        $msg = 'الفئة غير موجوده';
                    }
                }else{
                    $msg = 'Category Not Found';   
                }
                $notif = array('FailureCode'=> 400 ,'message' => $msg, 'data' => '');
            }
        }
        else
        {
            $getHeaders = apache_request_headers();
                if(isset($getHeaders['lang'])){
                    $lang = $getHeaders['lang'];
                    if($lang == 1){
                        $msg = 'Category Not Found';
                    }else{
                        $msg = 'الفئة غير موجوده';
                    }
                }else{
                    $msg = 'Category Not Found';   
                }
                $notif = array('FailureCode'=> 400 ,'message' => $msg, 'data' => '');
        }
        header("Content-Type: application/json; charset=utf-8");
        $ns = json_encode($notif);
        print_r($ns,false);
        die(); 
    } 


    public function getSubCategoryList($token,$categoryId){
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

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // instance of object manager
        $request = $objectManager->get('\Magento\Framework\App\RequestInterface');

        /*$parentCategoryId = 2; // You have to set website root category id here*/
        $parentCategoryId = $categoryId; // You have to set website root category id here
        $category = $objectManager->create('Magento\Catalog\Model\Category')->load($parentCategoryId);
        $categorysFactory = $objectManager->get('\Magento\Catalog\Model\CategoryFactory');
        $i=0;
        /*$childCategory['parent']["child"] = $this->getTopCategory($parentCategoryId);*/
        $getHeaders = apache_request_headers();
        if(isset($getHeaders['lang'])){
            $lang = $getHeaders['lang'];
            $childCategory['parent']["child"] = $this->getCategory($parentCategoryId , $lang);
        }else{
            $childCategory['parent']["child"] = $this->getCategory($parentCategoryId);
        }
        
        $len= sizeof($childCategory['parent']["child"]);

        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $viewId = $storeManager->getStore()->getStoreId();
        
        for ($i=0;$i<$len;$i++){
            $category = $categorysFactory->create()->load($childCategory['parent']["child"][$i]['categoryId']);
            if(!empty($childCategory['parent']["child"][$i]['categoryimage'])){
                $categoryscollection[]=array('subCategories' => array('catName'=> $childCategory['parent']["child"][$i]['categoryName'] , 'catId'=> $childCategory['parent']["child"][$i]['categoryId'] , 'categoryImg'=> $childCategory['parent']["child"][$i]['categoryimage']));
            }else{
                $categoryscollection[]=array('subCategories' => array('catName'=> $childCategory['parent']["child"][$i]['categoryName'] , 'catId'=> $childCategory['parent']["child"][$i]['categoryId'] , 'categoryImg'=> ''));
            }
             
              
        }

        if(isset($categoryscollection))
        {
            if($categoryscollection != null){
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
                $notif = array('SuccessCode'=> 200 ,'message' => $msg, 'data' => array('categoery' => $categoryscollection));
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
                $notif = array('FailureCode'=> 400 ,'message' => $msg, 'data' => '');
            }
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
            $notif = array('FailureCode'=> 400 ,'message' => $msg, 'data' => '');
        }
        header("Content-Type: application/json; charset=utf-8");
        $ns = json_encode($notif);
        print_r($ns,false);
        die(); 
    }
    /*changes end*/
    


}

