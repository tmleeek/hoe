<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Affiliate
 * @version    1.1.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Affiliate_Model_Resource_Campaign_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('awaffiliate/campaign');
    }

    /**
     * @param array $ids
     * @return AW_Affiliate_Model_Resource_Campaign_Collection
     */
    public function addFilterByIds(array $ids)
    {
        return $this->addFieldToFilter('id', array('in' => $ids));
    }

    public function joinProfitCollection()
    {
        $profitTableName = $this->getTable('awaffiliate/profit');
        $this->getSelect()->joinLeft(array('profit_table' => $profitTableName),
            'profit_table.campaign_id = main_table.id',
            array('rate_type' => 'profit_table.type',
                'rate_settings' => 'profit_table.rate_settings'
            )
        );
        return $this;
    }

    public function addFilterByWebsite($value)
    {
        if ($value) {
            $this->addFieldToFilter('main_table.store_ids', array("finset" => $value));
        }
        return $this;
    }

    public function addFilterByCustomerGroup($value)
    {
        if ($value) {
            $this->addFieldToFilter('main_table.allowed_groups', array("finset" => $value));
        }
        return $this;
    }

    /*apply only after $this->joinProfitCollection() */
    public function addFilterByRateType($value)
    {
        $this->addFieldToFilter('profit_table.type', array("eq" => $value));
        return $this;
    }

    public function addDateFilter()
    {
        $this->getSelect()->where(
            '(active_from is NULL) OR (active_from <= ?)',
            $this->_getCurrentDateMysqlFormat()
        );
        $this->getSelect()->where(
            '(active_to is NULL) OR (active_to >= ?)',
            $this->_getCurrentDateMysqlFormat()
        );
        return $this;
    }

    protected function _afterLoad()
    {
        foreach ($this->getItems() as $item) {
            $item->callAfterLoad();
        }
        return parent::_afterLoad();
    }

    public function addStoreFilter($stores = array())
    {
        if (!empty($stores)) {
            $websites = array();
            foreach ($stores as $storeId) {
                $store = Mage::app()->getSafeStore($storeId);
                if ($store) {
                    $websites[] = $store->getWebsiteId();
                }
            }
            $websites = array_unique($websites);
            $_sqlString = '(';
            $i = 0;
            foreach ($websites as $_store) {
                $_sqlString .= sprintf('find_in_set(%s, store_ids)', $this->getConnection()->quote($_store));
                if (++$i < count($websites))
                    $_sqlString .= ' OR ';
            }
            $_sqlString .= ')';
            $this->getSelect()->where($_sqlString);
        }
        return $this;
    }

    public function addStatusFilter($active = true)
    {
        $this->addFieldToFilter('status', $active ? 1 : 0);
        return $this;
    }

    public function addStatusExpandedFilter($statusId = 1)
    {
        $statusId = intval($statusId);
        if ($statusId == AW_Affiliate_Model_Source_Campaign_Status_Expanded::EXPIRED) {
            $this->getSelect()->where(
                'active_to < ?',
                $this->_getCurrentDateMysqlFormat()
            );
        }
        elseif ($statusId == AW_Affiliate_Model_Source_Campaign_Status_Expanded::PENDING) {
            $this->getSelect()->where(
                'active_from > ?',
                $this->_getCurrentDateMysqlFormat()
            );
            $this->addFieldToFilter('status', AW_Affiliate_Model_Source_Campaign_Status::ACTIVE);
        }
        elseif ($statusId == AW_Affiliate_Model_Source_Campaign_Status_Expanded::ACTIVE) {
            $this->addFieldToFilter('status', $statusId);
            $this->addDateFilter();
        }
        elseif ($statusId == AW_Affiliate_Model_Source_Campaign_Status_Expanded::INACTIVE) {
            $this->getSelect()->where(
                '(active_to is NULL) OR (active_to >= ?)',
                $this->_getCurrentDateMysqlFormat()
            );
            $this->addFieldToFilter('status', $statusId);
        }
        else {
            $this->addFieldToFilter('status', $statusId);
        }
        return $this;
    }

    protected function _getCurrentDateMysqlFormat()
    {
        return date(
            AW_Affiliate_Model_Resource_Campaign::MYSQL_DATE_FORMAT,
            Mage::app()->getLocale()->storeTimeStamp()
        );
    }
}
