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


class AW_Affiliate_Adminhtml_AbstractController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('awaffiliate');
        return $this;
    }

    protected function _setTitle($title)
    {
        if (Mage::helper('awaffiliate')->checkExtensionVersion('Mage_Core', '0.8.25')) {
            $this->_title($title);
        }
        return $this;
    }

    /**
     * Returns true when admin session contain error messages
     * @return bool
     */
    protected function _hasErrors()
    {
        return (bool)count($this->_getSession()->getMessages()->getItemsByType('error'));
    }
}
