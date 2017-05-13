<?php
/**
 * Product.php
 * CommerceExtensions @ InterSEC Solutions LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.commerceextensions.com/LICENSE-M1.txt
 *
 * @category   Product
 * @package    Hide Product Price For Non Registered Users
 * @copyright  Copyright (c) 2003-2009 CommerceExtensions @ InterSEC Solutions LLC. (http://www.commerceextensions.com)
 * @license    http://www.commerceextensions.com/LICENSE-M1.txt
 */
class CommerceExtensions_HideProductPrice_Helper_Product extends Mage_Core_Helper_Data {
	public static function currency($value, $format=true, $includeContainer = true)
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
						#return parent::currency($value, $format=true, $includeContainer = true);
						return parent::currency($value, $format=true, $includeContainer);
						break;
					}
				}
			} else if ($allow_display_by_category_ids == true) {
				#return parent::currency($value, $format=true, $includeContainer = true);
				return parent::currency($value, $format=true, $includeContainer);
				break;
			}
			
			if (! $loggedIn && ! $adminloggedIn) {
				return "";
			} else if ($allow_display_by_customer_group == true) { 
			
				$customergroupIdstocheck = explode(",",$customer_group_to_display);
				foreach($customergroupIdstocheck as $customergroupID) {
					if($customergroupID == Mage::getSingleton('customer/session')->getCustomer()->getGroupId()) {
						$value = Mage::app()->getStore()->convertPrice($value, $format, $includeContainer);
						return $value;
						break;
					}
				}
				return "";
			
			} else {
				$value = Mage::app()->getStore()->convertPrice($value, $format, $includeContainer);
				return $value;
			}
		}
		#return parent::currency($value, $format=true, $includeContainer = true);
		return parent::currency($value, $format=true, $includeContainer);
	}
}
?>
