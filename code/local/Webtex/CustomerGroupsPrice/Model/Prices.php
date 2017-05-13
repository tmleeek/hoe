<?php

/**
 * Webtex
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Webtex EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.webtex.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@webtex.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.webtex.com/ for more information
 * or send an email to sales@webtex.com
 *
 * @category   Webtex
 * @package    Webtex_CustomerGroupsPrice
 * @copyright  Copyright (c) 2010 Webtex (http://www.webtex.com/)
 * @license    http://www.webtex.com/LICENSE-1.0.html
 */

/**
 * Customer Groups Price extension
 *
 * @category   Webtex
 * @package    Webtex_CustomerGroupsPrice
 * @author     Webtex Dev Team <dev@webtex.com>
 */
class Webtex_CustomerGroupsPrice_Model_Prices extends Mage_Core_Model_Abstract {

    protected static $_url = null;
    protected $_product = null;
    protected $_pricesCollection = null;

    public function _construct() {
        parent::_construct();
        $this->_init('customergroupsprice/prices');
    }

    public function getPricesCollection($productId,$websiteId) {
        if (is_null($this->_pricesCollection)) {
            $this->_pricesCollection = Mage::getResourceModel('customergroupsprice/prices_collection')
                            ->addProductFilter($productId)
                            ->addWebsiteFilter($websiteId);
        }

        return $this->_pricesCollection;
    }

    public function deleteByProduct($productId,$websiteId) {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tablePrefix = (string) Mage::getConfig()->getTablePrefix();

        $connection->delete($tablePrefix . 'customergroupsprice_prices', 'product_id = ' . $productId . ' and website_id = ' . $websiteId );
    }

    public function getProductPrice($product)
    {
        if (!Mage::helper('customer')->isLoggedIn() || !($product->getId())) {
            return $product->getData('price');
        }
 
        $websiteId  = Mage::app()->getStore()->getWebsiteId();
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tablePrefix = (string) Mage::getConfig()->getTablePrefix();

        $groupId = Mage::helper('customer')->getCustomer()->getGroupId();
        if (Mage::helper('customergroupsprice')->isGroupActive($groupId)) {
            $query = $connection->select()->from($tablePrefix . 'customergroupsprice_prices')
                            ->where('product_id = ' . $product->getId() . ' AND group_id = ' . $groupId . ' AND website_id = ' . $websiteId);
            $row = $connection->fetchRow($query);

            if(isset($row['price'])){
		return $row['price'];
            }

            $query = $connection->select()->from($tablePrefix . 'customergroupsprice_prices')
                            ->where('product_id = ' . $product->getId() . ' AND group_id = ' . $groupId . ' AND website_id = 0');
            $row = $connection->fetchRow($query);

            if(isset($row['price'])){
		return $row['price'];
            }

			$query = $connection->select()->from($tablePrefix . 'customergroupsprice_prices_global')
                            ->where('group_id = ' . $groupId);
            $row = $connection->fetchRow($query);

			if(isset($row['price']) && isset($row['price_type'])){
                if(in_array(substr($row['price'], 0, 1), array('+', '-'))){
                    if($row['price_type'] == 2){
                        $price = $product->getData('price') + ($product->getData('price') * $row['price'] / 100);
                    } else {
                        $price = $product->getData('price') + $row['price'];
                    }
                } else {
                    if($row['price_type'] == 2){
                        $price = $product->getData('price') * $row['price'] / 100;
                    } else {
                        $price = $row['price'];
                    }
                }
		if(!$product->getData('applay_group_price')) {
	 	    $product->setApplayGroupPrice(1);
	 	    return $price;
	 	}
		}

            return $product->getData('price');
        } else {
            return $product->getData('price');
        }
    }


    public function getGroupProductPrice($product,$groupId,$website = null)
    {
        if(!$website){
            $websiteId  = Mage::app()->getStore()->getWebsiteId();
        } else {
            $websiteId  = $website;
        }
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tablePrefix = (string) Mage::getConfig()->getTablePrefix();

        if (Mage::helper('customergroupsprice')->isGroupActive($groupId)) {
            $query = $connection->select()->from($tablePrefix . 'customergroupsprice_prices')
                            ->where('product_id = ' . $product->getId() . ' AND group_id = ' . $groupId . ' AND website_id = ' . $websiteId);
            $row = $connection->fetchRow($query);
            if(isset($row['price'])){
                return $row['price'];
            }
            $query = $connection->select()->from($tablePrefix . 'customergroupsprice_prices')
                            ->where('product_id = ' . $product->getId() . ' AND group_id = ' . $groupId . ' AND website_id = 0');
            $row = $connection->fetchRow($query);
            if(isset($row['price'])){
                return $row['price'];
            }

		$query = $connection->select()->from($tablePrefix . 'customergroupsprice_prices_global')
                            ->where('group_id = ' . $groupId);
                 $row = $connection->fetchRow($query);

		if(isset($row['price']) && isset($row['price_type'])){
                if(in_array(substr($row['price'], 0, 1), array('+', '-'))){
                    if($row['price_type'] == 2){
                        $price = $product->getData('price') + ($product->getData('price') * $row['price'] / 100);
                    } else {
                        $price = $product->getData('price') + $row['price'];
                    }
                } else {
                    if($row['price_type'] == 2){
                        $price = $product->getData('price') * $row['price'] / 100;
                    } else {
                        $price = $row['price'];
                    }
                }
		if(!$product->getData('applay_group_price')) {
	 	    $product->setApplayGroupPrice(1);
	 	    return $price;
	 	} else {
	 	    return $price;
	 	}
		} else {
                   return $product->getData('price');
                }
        } else {
            return $product->getData('price');
        }
    }



	public function loadByGroup($productId, $groupId,$website = 0)
	{
	    if($website == 0){
	        $this->setData($this->getResource()->loadByGroup($productId, $groupId,Mage::app()->getStore()->getWebsiteId()));
	    } else {
	        $this->setData($this->getResource()->loadByGroup($productId, $groupId,$website));
	    }
            return $this;
	}
}
