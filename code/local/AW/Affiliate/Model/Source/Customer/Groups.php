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


class AW_Affiliate_Model_Source_Customer_Groups
{
    //labels
    const CUSTOMER_GROUP_ALL_LABEL = 'All groups';

    /**
     * @static
     * @return
     */
    public static function toOptionArray()
    {
        return Mage::helper('customer')->getGroups()->toOptionArray();
    }

    public static function toOptionArrayWithAllGroup()
    {
        $groups = Mage::helper('customer')->getGroups()->toOptionArray();
        array_unshift(
            $groups,
            array(
                'value' => Mage_Customer_Model_Group::CUST_GROUP_ALL,
                'label' => Mage::helper('awaffiliate')->__(self::CUSTOMER_GROUP_ALL_LABEL),
            )
        );
        return $groups;
    }
}
