<?php

/**
 * Class Klevu_Search_Model_Observer
 *
 * @method setIsProductSyncScheduled($flag)
 * @method bool getIsProductSyncScheduled()
 */
class Klevu_Search_Model_Observer extends Varien_Object {

    /**
     * Schedule a Product Sync to run immediately.
     *
     * @param Varien_Event_Observer $observer
     */
    public function scheduleProductSync(Varien_Event_Observer $observer) {
        if (!$this->getIsProductSyncScheduled()) {
            Mage::getModel("klevu_search/product_sync")->schedule();
            $this->setIsProductSyncScheduled(true);
        }
    }

    /**
     * Schedule an Order Sync to run immediately. If the observed event
     * contains an order, add it to the sync queue before scheduling.
     *
     * @param Varien_Event_Observer $observer
     */
    public function scheduleOrderSync(Varien_Event_Observer $observer) {
        $model = Mage::getModel("klevu_search/order_sync");

        $order = $observer->getEvent()->getOrder();
        if ($order) {
            $model->addOrderToQueue($order);
        }

        $model->schedule();
    }
}
