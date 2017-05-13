<?php
/**
 * Magedelight
 * Copyright (C) 2014  Magedelight <info@krishinc.com>
 *
 * NOTICE OF LICENSE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://opensource.org/licenses/gpl-3.0.html.
 *
 * @category   Ktpl
 * @package    Ktpl_Autocomplete
 * @copyright  Copyright (c) 2014 Mage Delight (http://www.magedelight.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     Magedelight <info@krishinc.com>
 */
class Ktpl_Autocomplete_Block_Suggestionlist extends Mage_Catalog_Block_Product_Abstract
{
	public function __construct(){
		$this->setTemplate('autocomplete/suggestionlist.phtml');
	}
	public function getProductCollection(){
		$searchAttributes = array();

		$collection = $this->getData('result_collection');
		$productLimit = Mage::getStoreConfig('autocomplete/general/product_limit');

		$collection = Mage::getModel('autocomplete/autocomplete')->getCollection();

		/* Start: Filter by Attribute */
		$attrSearch = Mage::getStoreConfig('autocomplete/general/search_attributes');
		if(!empty($attrSearch)){
			$selectedSearchAttributes = explode(',',$attrSearch);
			$searchArray = array();
			foreach($selectedSearchAttributes as $searchAttrib){
				$searchArray[] = array('attribute' => $searchAttrib, 'like' => '%'.Mage::helper('catalogsearch')->getQuery()->getQueryText().'%');
			}
			$collection->addAttributeToFilter($searchArray);
		}
		/* End: Filter by Attribute */
		/*if($this->getOutStockProducts()){
			$collection->addAttributeToFilter('is_in_stock',array('in' => array(0,1)));
		}else{
			$collection->addAttributeToFilter('is_salable',1);
		}*/
        $sortAttri = '';
        $sortAttri = Mage::getStoreConfig('autocomplete/general/sort_attributes');

        if(!empty($sortAttri)){
            $collection->addAttributeToSort($sortAttri,'asc');
        }


		$collection->getSelect()->limit($this->getProductLimit());
		return $collection;
	}
	public function getProductLimit()
	{
		$productLimit = Mage::getStoreConfig('autocomplete/general/product_limit');
		return $productLimit;
	}
	public function getMode()
	{
		return Mage::getStoreConfig('autocomplete/general/result_list_mode');
	}
	public function getOutStockProducts()
	{
		$isOutstockProducts = Mage::getStoreConfig('autocomplete/general/show_outstock_products');
		if($isOutstockProducts){
			return 1;
		}else{
			return 0;
		}
	}
}
