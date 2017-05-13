<?php
class Conversion_Profitshare_Block_ProductTracking extends Mage_Catalog_Block_Product_View
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
    private function getProductData()
    {
    	// Get product
    	$product 	= $this->getProduct();
    	 
    	// Get category
    	$cat 		= array_values($product->getCategoryCollection()->exportToArray());
    	$catName	= Mage::getModel('catalog/category')->load($cat[0]['entity_id'])->getName();
    	$entityId	= $cat[0]['entity_id'];
    	$brand		= $product->getAttributeText('manufacturer');
    	
    	if(count($cat) > 1) {
    		$catName = Mage::getModel('catalog/category')->load($cat[count($cat)-1]['entity_id'])->getName();
    		$entityId= $cat[count($cat)-1]['entity_id'];
    	}
    	
    	$store = Mage::getModel('core/store')->load(Mage::getStoreConfig('profitshare/shop'));
    	if(in_array($product->getTypeId(), array(Mage_Catalog_Model_Product_Type::TYPE_GROUPED, Mage_Catalog_Model_Product_Type::TYPE_BUNDLE)))
    		$price =  $store->convertPrice($product->getMinimalPrice(), false, false);
    	else
    		$price = $store->convertPrice($product->getFinalPrice(), false, false);

    	$price_vat 			= Mage::helper('tax')->getPrice($product, $price);
    	$advertiser_code 	= Mage::getStoreConfig('profitshare/advertiser_code');
    	
    	if(empty($catName) || empty($price_vat) || empty($advertiser_code)){
    		$enabled = false;
    	}else{
    		$enabled = true;
    	}
    	
    	$manufacturer = $product->getManufacturer();

    	return array(
    		'enabled'			=> $enabled,
			'advertiser_code'	=> Mage::getStoreConfig('profitshare/advertiser_code'),
    		'product_code' 		=> $product->getId(),
    		'product_price' 	=> number_format($price_vat/1.24,4, '.', ''), //PSP
    		'category_code' 	=> $entityId,
    		'category_name' 	=> urlencode(trim($catName)),
    		'brand_code' 		=> (!empty($manufacturer)?$manufacturer:0),
    		'brand_name' 		=> (!empty($brand)?urlencode(trim($brand)):'nobrand')
    	);
    	 
    }
    
    public function isEnabled()
    {
    	$data = $this->getProductData();
    	return $data["enabled"];
    }
    
    public function getAdvertiserCode()
    {
    	$data = $this->getProductData();
    	return $data["advertiser_code"];
    }
    
    public function getProductCode()
    {
    	$data = $this->getProductData();
    	return $data["product_code"];
    }
    
    public function getProductPrice()
    {
    	$data = $this->getProductData();
    	return $data["product_price"];
    }
    
    public function getCategoryCode()
    {
    	$data = $this->getProductData();
    	return $data["category_code"];
    }
    
    public function getCategoryName()
    {
    	$data = $this->getProductData();
    	return $data["category_name"];
    }
    
    public function getBrandCode()
    {
    	$data = $this->getProductData();
    	return $data["brand_code"];
    }
    
    public function getBrandName()
    {
    	$data = $this->getProductData();
    	return $data["brand_name"];
    }
}