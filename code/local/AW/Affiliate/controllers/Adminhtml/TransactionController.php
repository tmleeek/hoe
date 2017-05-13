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


class AW_Affiliate_Adminhtml_TransactionController extends Mage_Adminhtml_Controller_Action
{
    /*delete all transactions*/
    protected function resetTransactionsAction()
    {
        $withdrawalsTable = Mage::getSingleton('core/resource')->getTableName('awaffiliate/transaction_withdrawal');
        $profitsTable = Mage::getSingleton('core/resource')->getTableName('awaffiliate/transaction_profit');
        $withdrawalRequestsTable = Mage::getSingleton('core/resource')->getTableName('awaffiliate/withdrawal_request');
        $clientTable = Mage::getSingleton('core/resource')->getTableName('awaffiliate/client');
        $clientHistoryTable = Mage::getSingleton('core/resource')->getTableName('awaffiliate/client_history');
        $trafficSourceTable = Mage::getSingleton('core/resource')->getTableName('awaffiliate/traffic_source');
        $affiliateTable = Mage::getSingleton('core/resource')->getTableName('awaffiliate/affiliate');
        /** @var $write Varien_Db_Adapter_Pdo_Mysql*/
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        try {
            $write->delete($withdrawalsTable);
            $write->delete($profitsTable);
            $write->delete($withdrawalRequestsTable);
            $write->delete($clientTable);
            $write->delete($clientHistoryTable);
            $write->delete($trafficSourceTable);

            # older versions like CE 1.4 have no such method
            if (method_exists($write, 'updateFromSelect')) {
                $updateAffiliateCommissions = Mage::getModel('awaffiliate/affiliate')
                    ->getCollection()
                    ->addFieldToSelect(new Zend_Db_Expr('0'), 'current_balance')
                    ->addFieldToSelect(new Zend_Db_Expr('0'), 'active_balance')
                    ->getSelect();
                $query = $write->updateFromSelect($updateAffiliateCommissions, $affiliateTable);
            }
            else {
                $query = "UPDATE ".$affiliateTable." SET current_balance=0, active_balance=0";
            }
            $write->raw_query($query);

            $this->_getSession()->addSuccess(Mage::helper('awaffiliate')->__('All transactions has been deleted'));
        }
        catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_redirectReferer();
        return;
    }
}
