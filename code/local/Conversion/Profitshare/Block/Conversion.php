<?php
class Conversion_Profitshare_Block_Conversion extends Mage_Checkout_Block_Onepage_Success
{
	
	private $params = array();
	
	public function _prepareLayout()
	{
		$this->setOrderData();
		return parent::_prepareLayout();
	}
	
	private function setOrderData()
    {
    	// Get Checkout
    	$session = $this->_getCheckout();
    	 
    	// Get Order
    	$order = Mage::getModel('sales/order');
    	$order->loadByIncrementId($session->getLastRealOrderId());
    	 
    	if ($order->getId()) {
    		$this->setOrderID($session->getLastRealOrderId());

    		$items = $order->getAllItems();
    		foreach ($items as $itemId => $item)
    		{
    			// Get Product
    			$product 	= Mage::getModel('catalog/product')->load($item->getProductId());
    			$cat 		= array_values($product->getCategoryCollection()->exportToArray());
    			$catName 	= Mage::getModel('catalog/category')->load($cat[0]['entity_id'])->getName();
    				
    			if(empty($cat[0]['entity_id'])){
    				$parentIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($product->getId()); // check for grouped product
    				if(!$parentIds)
    					$parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId()); //check for config product
    				if(!$parentIds)
    					$parentIds = Mage::getModel('bundle/product_type')->getParentIdsByChild($product->getId());
    					
    				if(isset($parentIds[0])){
    					$parentProduct 	= Mage::getModel('catalog/product')->load($parentIds[0]);
    					$cat 			= array_values($parentProduct->getCategoryCollection()->exportToArray());
    					$catName 		= Mage::getModel('catalog/category')->load($cat[0]['entity_id'])->getName();
    				}
    			}
    	
    			if($item->getQtyToInvoice() > 0){
    				$qty = $item->getQtyToInvoice();
    			}else{
    				$qty = $item->getQtyOrdered();
    			}

    			if(!empty($cat[0]['entity_id']) && $item->getQtyToInvoice() > 0){
    				$manufacturer 		= $product->getManufacturer();
    				$manufacturer_name 	= urlencode($product->getAttributeText('manufacturer'));

    				$this->setProductData(
    						$item->getProductId(),
    						number_format(($item->getRowTotal()/round($qty)), 2, '.', ''),
    						urlencode(trim($item->getName())),
    						urlencode($product->getProductUrl()),
    						$cat[0]['entity_id'],
    						urlencode(trim($catName)),
    						$item->getSku(),
    						(!empty($manufacturer)?$manufacturer:0),
    						(!empty($manufacturer_name)?urlencode(trim($manufacturer_name)):'nobrand'),
    						round($qty)
    				);
    			}
    		}
    	}
    }
    
    private function setOrderID($orderID)
    {
    	$this->params["external_reference"] = $orderID;
    }
    
    private function setProductData($productCode, $productPrice, $productName, $productLink, $productCategory, $productCategoryName, $productPartNo, $productBrandCode, $productBrand, $productQTY)
    {
    	$this->params["product_code"][] 			= $productCode;
    	$this->params["product_price"][] 			= number_format($productPrice/1.24,4, '.', '');
    	$this->params["product_name"][] 			= $productName;
    	$this->params["product_link"][] 			= $productLink;
    	$this->params["product_category"][] 		= $productCategory;
    	$this->params["product_category_name"][] 	= $productCategoryName;
    	$this->params["product_part_no"][] 			= $productPartNo;
    	$this->params["product_brand_code"][] 		= $productBrandCode;
    	$this->params["product_brand"][] 			= $productBrand;
    	$this->params["product_qty"][]	 			= $productQTY;
    }
    
    public function getProfitshareData()
    {
    	$data = "";
    	foreach ($this->params as $k=>$p){
    		if(is_array($p)){
    			foreach ($p as $k2=>$p2){
    				$data.= "{$k}[]={$p2}&";
    			}
    		}else{
    			$data.= "{$k}={$p}&";
    		}
    	}
    	
    	$conversion = array(
    		"encrypt" 		 => $this->encryptProfitshareConversionCode(
    								rtrim($data,'&'),
    								Mage::getStoreConfig('profitshare/encrypted_params')),
    		"advertiserCode" => Mage::getStoreConfig('profitshare/advertiser_code')
    	);
    	
    	return $conversion;
    }
    
    /*
     * Encrypt Profitshare params
     */
    private function encryptProfitshareConversionCode($value = '', $advertiserKey)
    {
    	$secretKey = md5($advertiserKey);
    	return rtrim(
    			bin2hex(
    					mcrypt_encrypt(
    							MCRYPT_RIJNDAEL_256,
    							$secretKey, $value,
    							MCRYPT_MODE_ECB,
    							mcrypt_create_iv(
    									mcrypt_get_iv_size(
    											MCRYPT_RIJNDAEL_256,
    											MCRYPT_MODE_CBC
    									),
    									MCRYPT_RAND
    							)
    					)
    			),
    			"\0"
    	);
    }
    

    /*
     * Get Checkout Session
    */
    protected function _getCheckout()
    {
    	return Mage::getSingleton('checkout/session');
    }
}