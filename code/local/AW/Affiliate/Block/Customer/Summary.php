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


class AW_Affiliate_Block_Customer_Summary extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $_template = 'aw_affiliate/customer/summary.phtml';
        $this->setTemplate($_template);
        return $this;
    }

    public function getCurrentBalance()
    {
        $currentBalance = Mage::registry('current_affiliate')->getCurrentBalance();
        return Mage::helper('core')->formatCurrency($currentBalance);
    }

    public function getActiveBalance()
    {
        $activeBalance = Mage::registry('current_affiliate')->getActiveBalance();
        return Mage::helper('core')->formatCurrency($activeBalance);
    }

    public function getPendingBalance()
    {
        $pendingBalance = Mage::registry('current_affiliate')->getCurrentBalance() - Mage::registry('current_affiliate')->getActiveBalance();
        return Mage::helper('core')->formatCurrency($pendingBalance);
    }

    public function getTotalAffiliated()
    {
        $totalAffiliated = Mage::registry('current_affiliate')->getTotalAffiliated();
        return Mage::helper('core')->formatCurrency($totalAffiliated);
    }

    public function getAffiliatedLastMonth()
    {
        $_helper = Mage::helper('awaffiliate/affiliate');
        $affiliatedInLastMonth = $_helper->getLastMonthAmountForAffiliate(Mage::registry('current_affiliate'));
        return Mage::helper('core')->formatCurrency($affiliatedInLastMonth);
    }

    /**
     * Subtract this from ActiveBalance to see what amount can still be requested for payout
     *
     * @return int
     */
    protected function _getWithdrawalRequestSum() {
        $sum = 0;
        $requestWithdrawal = Mage::getModel('awaffiliate/withdrawal_request')->getCollection();
        $requestWithdrawal->addFieldToFilter('status', array('eq' => AW_Affiliate_Model_Source_Withdrawal_Status::PENDING));
        $requestWithdrawal->addAffiliateFilter(Mage::registry('current_affiliate')->getId());
        foreach ($requestWithdrawal as $item) {
            $sum += $item->getAmount();
        }
        return $sum;
    }
}
