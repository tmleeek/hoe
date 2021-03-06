<?php
/**
 * Abtract.php
 * CommerceExtensions @ InterSEC Solutions LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.commerceextensions.com/LICENSE-M1.txt
 *
 * @category   Abstract
 * @package    Hide Product Price For Non Registered Users
 * @copyright  Copyright (c) 2003-2009 CommerceExtensions @ InterSEC Solutions LLC. (http://www.commerceextensions.com)
 * @license    http://www.commerceextensions.com/LICENSE-M1.txt
 */
class CommerceExtensions_HideProductPrice_Helper_Abstract extends Mage_Catalog_Model_Product {
	public function isSalable()
	{
		$moduleenabled = Mage::getStoreConfig('advanced/modules_disable_output/CommerceExtensions_HideProductPrice'); 
		//0=enabled and 1=disabled
		if ($moduleenabled == 0) {
			$loggedIn = Mage::getSingleton('customer/session')->isLoggedIn();
			$adminloggedIn = Mage::getSingleton('admin/session')->isLoggedIn();
			$allow_display_by_customer_group = (bool)Mage::getStoreConfig('hideproductprice/hideproductprice/allow_display_by_customer_group');
			$customer_group_to_display = Mage::getStoreConfig('hideproductprice/hideproductprice/customer_group_to_display');
			$allow_display_by_category_ids = (bool)Mage::getStoreConfig('hideproductprice/hideproductprice/allow_display_by_category_ids');
			$category_ids_to_display = Mage::getStoreConfig('hideproductprice/hideproductprice/category_ids_to_display');
										
			if ($allow_display_by_category_ids == true && Mage::registry('current_category')) {
				$categoryIdstocheck = explode(",",$category_ids_to_display);
				foreach($categoryIdstocheck as $categoryID) {
					if($categoryID == Mage::registry('current_category')->getId()) {
						return parent::isSalable();
						break;
					}
				}
			} else if ($allow_display_by_category_ids == true && $this->getId() !="") {
				 #echo "ID: " . $this->getId();
         #$current_product = Mage::getModel('catalog/product')->load($this->getId());
				 $resource = Mage::getSingleton('core/resource');
				 $prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix'); 
				 $read = $resource->getConnection('core_read');
				 $select_qryvalues2 = $read->query("SELECT category_id FROM `".$prefix."catalog_category_product` WHERE product_id = '".$this->getId()."'");
				 foreach($select_qryvalues2->fetchAll() as $datavalues2)
				 { 
						$categoryIdstocheck = explode(",",$category_ids_to_display);
						foreach($categoryIdstocheck as $categoryID) {
							if($categoryID == $datavalues2['category_id']) {
								return parent::isSalable();
								break;
							}
						}
				 }
			}
			
			if (! $loggedIn && ! $adminloggedIn) {
				return false;
			} else if ($allow_display_by_customer_group == true) { 
			
				$customergroupIdstocheck = explode(",",$customer_group_to_display);
				foreach($customergroupIdstocheck as $customergroupID) {
					if($customergroupID == Mage::getSingleton('customer/session')->getCustomer()->getGroupId()) {
						 $stockItem = Mage::getModel('cataloginventory/stock_item');
						 $stockItem->loadByProduct($this->getId());
						 if ($stockItem->getData('is_in_stock') == 0 && $loggedIn) { 
							return false;
						 } else {
							return true;
						 }
					}
				}
				return false;
			}
		}
		return parent::isSalable();
	}
	
	public function getTierPrice($qty=null)
	{
		$moduleenabled = Mage::getStoreConfig('advanced/modules_disable_output/CommerceExtensions_HideProductPrice'); 
		//0=enabled and 1=disabled
		if ($moduleenabled == 0) {
			$loggedIn = Mage::getSingleton('customer/session')->isLoggedIn();
			$adminloggedIn = Mage::getSingleton('admin/session')->isLoggedIn();
			$allow_display_by_customer_group = (bool)Mage::getStoreConfig('hideproductprice/hideproductprice/allow_display_by_customer_group');
			$customer_group_to_display = Mage::getStoreConfig('hideproductprice/hideproductprice/customer_group_to_display');
			$allow_display_by_category_ids = (bool)Mage::getStoreConfig('hideproductprice/hideproductprice/allow_display_by_category_ids');
			$category_ids_to_display = Mage::getStoreConfig('hideproductprice/hideproductprice/category_ids_to_display');
															
			if ($allow_display_by_category_ids == true && Mage::registry('current_category')) {
				$categoryIdstocheck = explode(",",$category_ids_to_display);
				foreach($categoryIdstocheck as $categoryID) {
					if($categoryID == Mage::registry('current_category')->getId()) {
						return $this->getPriceModel()->getTierPrice($qty, $this);
						break;
					}
				}
			} else if ($allow_display_by_category_ids == true && $this->getId() !="") {
				 #echo "ID: " . $this->getId();
         #$current_product = Mage::getModel('catalog/product')->load($this->getId());
				 $resource = Mage::getSingleton('core/resource');
				 $prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix'); 
				 $read = $resource->getConnection('core_read');
				 $select_qryvalues2 = $read->query("SELECT category_id FROM `".$prefix."catalog_category_product` WHERE product_id = '".$this->getId()."'");
				 foreach($select_qryvalues2->fetchAll() as $datavalues2)
				 { 
						$categoryIdstocheck = explode(",",$category_ids_to_display);
						foreach($categoryIdstocheck as $categoryID) {
							if($categoryID == $datavalues2['category_id']) {
								return $this->getPriceModel()->getTierPrice($qty, $this);
								break;
							}
						}
				 }
			}
			
			if (! $loggedIn && ! $adminloggedIn) {
				return false;
			} else if ($allow_display_by_customer_group == true) { 
			
				$customergroupIdstocheck = explode(",",$customer_group_to_display);
				foreach($customergroupIdstocheck as $customergroupID) {
					if($customergroupID == Mage::getSingleton('customer/session')->getCustomer()->getGroupId()) {
						return $this->getPriceModel()->getTierPrice($qty, $this);
						break;
					}
				}
				return false;
			}
		}
		return $this->getPriceModel()->getTierPrice($qty, $this);
	}
}
?>
