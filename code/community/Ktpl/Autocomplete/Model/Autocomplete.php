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
class Ktpl_Autocomplete_Model_Autocomplete extends Mage_Core_Model_Abstract
{
	protected function _construct(){
		$this->_init('autocomplete/autocomplete');
	}
	public function getCollection(){
		$collection = Mage::getResourceModel('catalogsearch/fulltext_collection');
		return $this->_prepareCollection($collection);
	}
	protected function _prepareCollection($collection){
		$collection->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
			->addSearchFilter(Mage::helper('catalogsearch')->getQuery()->getQueryText())
			->setStore(Mage::app()->getStore())
			->addMinimalPrice()
			->addFinalPrice()
			->addTaxPercents()
			->addStoreFilter()
			->addUrlRewrite();
                
                if(!Mage::getStoreConfig('autocomplete/general/show_outstock_products')){
                    Mage::getSingleton('cataloginventory/stock_status')->addIsInStockFilterToCollection($collection);
                }
                
		Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
		Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($collection);
		
		return $collection;
	}
}
