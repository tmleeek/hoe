<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Innobyte
 * @package     Innobyte_Plationline
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Plationline Cron Controller
 */
//require_once(BP . DS . 'app/code/local' . DS . 'Innobyte/Plationline' . DS . 'clspo.php');
class Innobyte_Plationline_CronController extends Mage_Core_Controller_Front_Action
{

    /**
     * Get singleton with Checkout by Plationline Api
     *
     * @return Innobyte_Plationline_Model_Api
     */
    protected function _getApi()
    {
        return Mage::getSingleton('plationline/api');
    }

	/**
	 * all orders that have not been payed get cancelled
	 * the timeout is set in System -> Configuration -> Payment Methods -> Plati Online -> Payment Timeout
	 * a "Payment timeout" comment is appended to cancelled orders
	 */
	public function cancelOnHoldOrdersAction() {
		
		$logFile = 'plationlineCancel.log';
		Mage::log('Starting cron...', null, $logFile);
		$timeoutInterval = (int)$this->_getApi()->getConfig()->getConfigData('payment_timeout');
		$last = date('Y-m-d H:i:s', strtotime('+' . (180 - $timeoutInterval) . ' minutes'));
		
        $orders = Mage::getResourceModel('sales/order_collection')
            ->addAttributeToSelect('status')
			->addAttributeToFilter('status', 'holded')
			->addAttributeToFilter('created_at', array('lt' => $last))
            ->addAttributeToSort('created_at', 'desc')
            ;
// 		$orders->getSelect()->limit(1);


/*		foreach($orders as $order) {
			print("<pre>");
			print_r($order->getData());
			print("</pre>");
		}
		exit();*/
        $conn = Mage::getSingleton('core/resource')->getConnection('core_read');
        
        if(count($orders)) {

			$rule = Mage::getModel('salesrule/rule');
			$ruleCustomer = Mage::getModel('salesrule/rule_customer');

			foreach($orders as $order) {

				Mage::log('Cancelling order no ' . $order->getIncrementId() . '...', null, $logFile);
				$order->addStatusToHistory(Mage_Sales_Model_Order::STATE_CANCELED, 'Payment timeout');
				if($order->cancel()) {
					$order->save();
					Mage::log('Cancelled order no ' . $order->getIncrementId(), null, $logFile);
				}
				else {
					Mage::log('Failed to cancel order no ' . $order->getIncrementId(), null, $logFile);
					continue;
				}

				$query = $conn->query("SELECT applied_rule_ids FROM sales_flat_quote WHERE reserved_order_id=" . $order->getIncrementId());
				$result = $query->fetchColumn();
				$discountAmount = $order->getDiscountAmount();

				if(!empty($result)) {
					Mage::log('Refunding vouchers for order ' . $order->getIncrementId() . '(rules ' . $result . ') ...', null, $logFile);
					$rules = explode(',', $result);
					foreach($rules as $ruleId) {
						$rule->load($ruleId);
						$ruleCustomer->loadByCustomerRule($order->getCustomerId(), $ruleId);
						if(!$ruleCustomer->getId()) {
							continue;
						}
						if($ruleCustomer->getAmountUsed() > $discountAmount) {
							$ruleCustomer->setAmountUsed($ruleCustomer->getAmountUsed() - $discountAmount);
							$discountAmount = 0;
						}
						else {
							$discountAmount -= $ruleCustomer->getAmountUsed();
							$ruleCustomer->setAmountUsed(0);
						}

						$ruleCustomer->save();
						
						if($discountAmount == 0) {
							break;
						}
					}
					Mage::log('Refunded vouchers for order ' . $order->getIncrementId() . ' complete', null, $logFile);
				}
				else {
					Mage::log('no quote for order ' . $order->getIncrementId(), null, $logFile);
// 					echo 'No quote' . PHP_EOL;
				}
			}
		}
		else {
			Mage::log('No orders to cancel', null, $logFile);
		}
	}

}
