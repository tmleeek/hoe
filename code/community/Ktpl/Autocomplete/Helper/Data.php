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

class Ktpl_Autocomplete_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getAjaxurl()
	{
		return Mage::getUrl('autocomplete/search/searchAutocomplete/',array('_secure' => Mage::app()->getFrontController()->getRequest()->isSecure()));
	}
	public function getMinChar()
	{
		$minCharacters = 0;
		$minChar = Mage::getStoreConfig('autocomplete/general/minchar');
		if($minChar != NULL){
			$minCharacters = $minChar;
		}else{
			$minCharacters = 3;
		}
		return $minCharacters;
	}
	public function getNoticeMessage()
	{
		return Mage::getStoreConfig('autocomplete/general/not_found_notice');
	}
	public function getTarget()
	{
		$target = '';
		$openNewTab = Mage::getStoreConfig('autocomplete/general/open_new_window');
		if($openNewTab == 1){
			$target = '_blank';
		}else{
			$target = '_self';
		}
		return $target;
	}
}
