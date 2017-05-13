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


class AW_Affiliate_Block_Customer_Accountlink extends Mage_Core_Block_Abstract
{
    public function addLink()
    {
        if (Mage::helper('awaffiliate')->isEnabled() && $this->_isAffiliateDetected()) {
            $parentBlock = $this->getParentBlock();
            if ($parentBlock instanceof Mage_Customer_Block_Account_Navigation)
                $parentBlock->addLink('awaffiliate', 'awaffiliate/customer_affiliate/view', $this->__('Affiliate Program'));
        }
    }

    protected function _isAffiliateDetected()
    {
        $session = Mage::getSingleton('customer/session');
        $_id = $session->getCustomer()->getId();
        $affiliate = Mage::getModel('awaffiliate/affiliate')->loadByCustomerId($_id);
        if (is_null($affiliate->getId())) {
            return false;
        }
        return true;
    }
}
