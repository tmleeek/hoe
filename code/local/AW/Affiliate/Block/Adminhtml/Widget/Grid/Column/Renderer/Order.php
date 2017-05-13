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


class AW_Affiliate_Block_Adminhtml_Widget_Grid_Column_Renderer_Order extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        switch ($row->getType()) {
            case AW_Affiliate_Model_Source_Transaction_Profit_Type::ADMIN :
            case AW_Affiliate_Model_Source_Transaction_Profit_Type::CUSTOMER_VISIT :
                $html = $this->__('N/A');
                break;
            case AW_Affiliate_Model_Source_Transaction_Profit_Type::CUSTOMER_PURCHASE :
                $html = $this->__getLinkToOrder($row);
                break;
            default:
                $html = $this->_getValue($row);
        }
        return $html;
    }

    private function __getLinkToOrder($row)
    {
        switch ($row->getData('linked_entity_type')) {
            case AW_Affiliate_Model_Source_Transaction_Profit_Linked::ORDER_ITEM:
                $linkedEntity = $row->getLinkedEntity();
                if ($linkedEntity instanceof Mage_Sales_Model_Order_Item) {
                    $order = $linkedEntity->getOrder();
                    $orderId = $order->getId();
                    $orderIncrementId = $order->getIncrementId();
                }
                break;
            case AW_Affiliate_Model_Source_Transaction_Profit_Linked::INVOICE_ITEM:
                /** @var $order Mage_Sales_Model_Order */
                $orderIncrementId = $this->_getValue($row);
                $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
                $orderId = $order->getId();
                break;
            default:
                $orderId = null;
                $orderIncrementId = null;
        }
        if ($orderId) {
            $_href = Mage::helper('adminhtml')->getUrl('adminhtml/sales_order/view', array('order_id' => $orderId));
            return "<a href='" . $_href . "' target='_blank'>" . $orderIncrementId . "</a>";
        }
        return $this->__('N/A');
    }

}
