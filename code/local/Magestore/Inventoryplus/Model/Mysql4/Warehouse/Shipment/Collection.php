<?php
/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Inventory
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventory Resource Collection Model
 * 
 * @category    Magestore
 * @package     Magestore_Inventory
 * @author      Magestore Developer
 */
class Magestore_Inventoryplus_Model_Mysql4_Warehouse_Shipment_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_isGroupSql = true;
        $this->_init('inventoryplus/warehouse_shipment');
    }
    
    public function setIsGroupCountSql($value) {
        $this->_isGroupSql = $value;
        return $this;
    }
    
    public function getSelectCountSql() {
        if ($this->_isGroupSql) {
            $this->_renderFilters();
            $countSelect = clone $this->getSelect();
            $countSelect->reset(Zend_Db_Select::ORDER);
            $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
            $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
            
            $countSelect->reset(Zend_Db_Select::COLUMNS);
            if (count($this->getSelect()->getPart(Zend_Db_Select::GROUP)) > 0) {
                $countSelect->reset(Zend_Db_Select::GROUP);
                $countSelect->distinct(true);
                $group = $this->getSelect()->getPart(Zend_Db_Select::GROUP);
                $countSelect->columns("COUNT(DISTINCT " . implode(", ", $group) . ")");
            } else {
                $countSelect->columns('COUNT(*)');
            }
            return $countSelect;
        }
        return parent::getSelectCountSql();
    }
}