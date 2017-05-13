<?php
/**
 * Checkout.php
 * CommerceExtensions @ InterSEC Solutions LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.commerceextensions.com/LICENSE-M1.txt
 *
 * @category   Checkout
 * @package    Hide Product Price For Non Registered Users
 * @copyright  Copyright (c) 2003-2009 CommerceExtensions @ InterSEC Solutions LLC. (http://www.commerceextensions.com)
 * @license    http://www.commerceextensions.com/LICENSE-M1.txt
 */
class CommerceExtensions_HideProductPrice_Helper_Checkout extends Mage_Checkout_Helper_Data {
	public function formatPrice($price)
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
			
			if ($allow_display_by_category_ids == true) {
				$session = Mage::getSingleton('checkout/session');
				foreach($session->getQuote()->getAllItems() as $item)
				{
					$categoryIdstocheck = explode(",",$category_ids_to_display);
					foreach($categoryIdstocheck as $categoryID) {
						$productincartcategoryIds = $item->getProduct()->getCategoryIds();
						foreach($productincartcategoryIds as $productcategoryID) {
							if($categoryID == $productcategoryID) {
								return parent::formatPrice($price);
								break;
							}
						}
					}
				}
			}
			
			if (! $loggedIn && ! $adminloggedIn) {
				return Mage::getStoreConfig('hideproductprice/hideproductprice/displayed_text');
				#return "You must be logged into to see the price.";
			} else if ($allow_display_by_customer_group == true) { 
			
				$customergroupIdstocheck = explode(",",$customer_group_to_display);
				foreach($customergroupIdstocheck as $customergroupID) {
					if($customergroupID == Mage::getSingleton('customer/session')->getCustomer()->getGroupId()) {
						return parent::formatPrice($price);
					}
				}
				return Mage::getStoreConfig('hideproductprice/hideproductprice/displayed_text');
				#return "You must be logged into to see the price.";
			}
		}
		return parent::formatPrice($price);
	}
}
?>
