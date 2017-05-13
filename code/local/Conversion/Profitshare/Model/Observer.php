<?php 
class Conversion_Profitshare_Model_Observer
{
    public function reimportProduct(Mage_Cron_Model_Schedule $schedule)
    {
    	$lastImport = Mage::getStoreConfig('profitshare/last_import');
    	$needTime	= time() - (3600 * 24);

    	if(Mage::getStoreConfig('profitshare/import') == 1 && $lastImport < $needTime) {
    		Mage::getModel('core/config')->saveConfig('profitshare/last_import', time());
    		Mage::app()->cleanCache();
    		
    		// Start product count
    		$total = 0;
    		
    		// Set Store
    		Mage::app()->setCurrentStore(Mage::getStoreConfig('profitshare/shop'));
    		
    		for($i=1;$i<1000;$i++) {
    			$products = Mage::getModel('catalog/product')
	    			->getCollection()
	    			->addStoreFilter(Mage::getStoreConfig('profitshare/shop'))
	    			->addAttributeToSelect('name')
	    			->addAttributeToSelect('short_description')
	    			->addAttributeToSelect('price')
	    			->addAttributeToSelect('special_price')
	    			->addAttributeToSelect('manufacturer')
	    			->addAttributeToFilter(
	    					'status',
	    					array('eq' =>'1')
	    			)
	    			->setPageSize(20)
	    			->setCurPage($i)
	    			->load();
    			
    			// Add products
    			$apiConf = array(
    					'user'	=> Mage::getStoreConfig('profitshare/api_user'),
    					'key'	=> Mage::getStoreConfig('profitshare/api_key'),
    					'url'	=> 'http://api.profitshare.ro/'
    			);
    			
    			$api = new Conversion_Service_Profitshare_Product($apiConf['user'], $apiConf['key'], $apiConf['url']);
    			
    			foreach($products as $product) {
    				$current	= Mage::getModel('catalog/product')->load($product->getId());
    				$stock 		= Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
    				if($stock->getIsInStock() == 1 && $current->getVisibility() != 1){
    					$cat 			= array_values($product->getCategoryCollection()->exportToArray());
    					
    					if(empty($cat)){
    						continue;
    					}
    					
    					$parentCatName 	= Mage::getModel('catalog/category')->load($cat[0]['parent_id'])->getName();
    					$catName 		= Mage::getModel('catalog/category')->load($cat[0]['entity_id'])->getName();

    					if($parentCatName == "Root Catalog" && count($cat) > 1) {
    						$parentCatName 	= Mage::getModel('catalog/category')->load($cat[count($cat)-1]['parent_id'])->getName();
    						$catName 		= Mage::getModel('catalog/category')->load($cat[count($cat)-1]['entity_id'])->getName();
    					}
    					
    					$brand	= $product->getAttributeText('manufacturer');
    					$store 	= Mage::getModel('core/store')->load(Mage::getStoreConfig('profitshare/shop'));
    					
    					if(in_array($product->getTypeId(), array(Mage_Catalog_Model_Product_Type::TYPE_GROUPED, Mage_Catalog_Model_Product_Type::TYPE_BUNDLE)))
    						$price =  $store->convertPrice($product->getMinimalPrice(), false, false);
    					else
    						$price = $store->convertPrice($product->getFinalPrice(), false, false);
    					
    					$price_vat = Mage::helper('tax')->getPrice($product, $price);
    					
    					if(empty($parentCatName) || empty($price)){
    						continue;
    					}
    					
    					$image = (string)Mage::helper('catalog/image')->init(
    								$current,
    								'image'
    					);
    					
    					$manufacturer 	= $product->getManufacturer();
    					$part_no 		= $product->getSku();
    					//die();
    					$addProducts = array(
    							'category_code'		=> $cat[0]['entity_id'],
    							'category' 			=> $catName,
    							'parent_category' 	=> $parentCatName,
    							'brand' 			=> (!empty($brand)?urlencode($brand):'nobrand'),
    							'brand_code' 		=> (!empty($manufacturer)?$manufacturer:0),
    							'part_no' 			=> (!empty($part_no)?urlencode($part_no):'nopart'),
    							'code' 				=> $product->getId(),
    							'name' 				=> $product->getName(),
    							'description' 		=> $product->getShortDescription(),
    							'link' 				=> $product->getProductUrl(),
    							'image' 			=> $image,
    							'price'				=> number_format($price/1.24,4, '.', ''),
    							'price_discounted' 	=> (is_null($product->getSpecialPrice())?0:$product->getSpecialPrice()),
    							'price_vat' 		=> $price_vat,
    							'currency' 			=> 'RON',
    							'free_shipping' 	=> 0,
    							'availability' 		=> 'in stoc',
    							'gift_included' 	=> 0,
    							'status' 			=> 1
    					);

    					$add = new Conversion_Service_Profitshare_Objects_Product($addProducts);
    					$api->addProduct($add);
    				}
    			}
    			
    			try {
    				$api->updateMany();
    			} catch (Exception $e) {}

    			// End import
    			if($products->count() == 0) {
    				break;
    			}
    			
    			// Count total products
    			$total+= $products->count();
    			
    			unset($api);
    			unset($add);
    			unset($products);
    		}
    		
    		Mage::log('Profitshare total imported products: ' . $total);
    	}
    }
}
