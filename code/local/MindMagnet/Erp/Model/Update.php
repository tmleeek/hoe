<?php
class MindMagnet_Erp_Model_Update
{
    public function updateFromErp($skuArray, $stockArray, $priceArray)
    {
		Mage::log('skuArray'.var_dump($skuArray));
		//Mage::log('stockArray'.var_dump($stockArray));
		//Mage::log('priceArray'.var_dump($priceArray));
		
        if ((count($skuArray) != count($stockArray)) || (count($skuArray) != count($priceArray)))
        {
            Mage::log('Number of items are not equal', null, 'erp_import'.date("Y_m_d").'.log');
            die ('Number of items are not equal');
        }

        for ($i = 0 ; $i < count($skuArray) ; $i ++) {

            //$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $skuArray[$i]);
			
		
			$product = Mage::getModel('catalog/product');
			$product->load($product->getIdBySku($skuArray[$i]));
			
			
			if(!is_object($product) || !$product->getId()) {
                continue;
            } 
			
            $product->setData('price', $priceArray[$i]);
            $product->getResource()->saveAttribute($product, 'price');

            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());
            $stockItem->setProductId($product->getId());
            $stockItem->setStockId(Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID);
            $stockItem->setData("qty",$stockArray[$i]);
			Mage::log('----update------');
            //@TODO - check if in stock - new product vs manage stock

            try{
                $stockItem->save();
            }catch(Exception $e) {
                Mage::log('The sku:'.$skuArray[$i].' can not save the stock:'.$e->getMessage(), null, 'erp_import'.date("Y_m_d").'.log');
            }
        }

    }
}