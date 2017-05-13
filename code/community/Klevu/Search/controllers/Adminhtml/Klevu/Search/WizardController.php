<?php

class Klevu_Search_Adminhtml_Klevu_Search_WizardController extends Mage_Adminhtml_Controller_Action {

    public function configure_userAction() {
        $this->loadLayout();
        $this->initLayoutMessages('klevu_search/session');
        $this->renderLayout();
    }

    public function configure_user_postAction() {
        $request = $this->getRequest();

        if (!$request->isPost() || !$request->isAjax()) {
            return $this->_redirect('adminhtml/dashboard');
        }

        $api = Mage::helper("klevu_search/api");
        $session = Mage::getSingleton('klevu_search/session');

        if ($request->getPost("klevu_existing_email")) {
            $result = $api->getUser(
                $request->getPost("klevu_existing_email"),
                $request->getPost("klevu_existing_password")
            );
        } else {
            $result = $api->createUser(
                $request->getPost("klevu_new_email"),
                $request->getPost("klevu_new_password"),
                $request->getPost("klevu_new_url")
            );
        }

        if ($result["success"]) {
            $session->setConfiguredCustomerId($result["customer_id"]);
            if ($result["message"]) {
                $session->addSuccess(Mage::helper("klevu_search")->__($result["message"]));
            }
            return $this->_forward("configure_store");
        } else {
            $session->addError(Mage::helper("klevu_search")->__($result["message"]));
            return $this->_forward("configure_user");
        }
    }

    public function configure_storeAction() {
        $request = $this->getRequest();

        if (!$request->isAjax()) {
            return $this->_redirect("adminhtml/dashboard");
        }

        $session = Mage::getSingleton("klevu_search/session");

        if (!$session->getConfiguredCustomerId()) {
            $session->addError(Mage::helper("klevu_search")->__("You must configure a user first."));
            return $this->_redirect("*/*/configure_user");
        }

        $this->loadLayout();
        $this->initLayoutMessages('klevu_search/session');
        $this->renderLayout();
    }

    public function configure_store_postAction() {
        $request = $this->getRequest();

        if (!$request->isPost() || !$request->isAjax()) {
            return $this->_redirect("adminhtml/dashboard");
        }

        $config = Mage::helper("klevu_search/config");
        $api = Mage::helper("klevu_search/api");
        $session = Mage::getSingleton('klevu_search/session');
        $customer_id = $session->getConfiguredCustomerId();

        if (!$customer_id) {
            $session->addError(Mage::helper("klevu_search")->__("You must configure a user first."));
            return $this->_redirect("*/*/configure_user");
        }

        $store_code = $request->getPost("store");
        if (strlen($store_code) == 0) {
            $session->addError(Mage::helper("klevu_search")->__("Must select a store"));
            return $this->_forward("configure_store");
        }

        try {
            $store = Mage::app()->getStore($store_code);
        } catch (Mage_Core_Model_Store_Exception $e) {
            $session->addError(Mage::helper("klevu_search")->__("Selected store does not exist."));
            return $this->_forward("configure_store");
        }

        // Setup the live and test Webstores
        foreach (array(false, true) as $test_mode) {
            $result = $api->createWebstore($customer_id, $store, $test_mode);

            if ($result["success"]) {
                $config->setJsApiKey($result["webstore"]->getJsApiKey(), $store, $test_mode);
                $config->setRestApiKey($result["webstore"]->getRestApiKey(), $store, $test_mode);

                if ($result["message"]) {
                    $session->addSuccess(Mage::helper("klevu_search")->__($result["message"]));
                }
            } else {
                $session->addError(Mage::helper("klevu_search")->__($result["message"]));
                return $this->_forward("configure_store");
            }
        }

        $config->setTestModeEnabledFlag($request->getPost("test_mode"), $store);

        // Clear Product Sync and Order Sync data for the newly configured store
        Mage::getModel("klevu_search/product_sync")->clearAllProducts($store);
        Mage::getModel("klevu_search/order_sync")->clearQueue($store);

        $session->setConfiguredStoreCode($store_code);

        $session->addSuccess("Store configured successfully. Saved API credentials for test and live modes.");

        return $this->_forward("configure_attributes");
    }

    public function configure_attributesAction() {
        $request = $this->getRequest();

        if (!$request->isAjax()) {
            return $this->_redirect("adminhtml/dashboard");
        }

        $session = Mage::getSingleton("klevu_search/session");

        if (!$session->getConfiguredCustomerId()) {
            $session->addError(Mage::helper("klevu_search")->__("You must configure a user first."));
            return $this->_redirect("*/*/configure_user");
        }

        if (!$session->getConfiguredStoreCode()) {
            $session->addError(Mage::helper("klevu_search")->__("Must select a store"));
            return $this->_redirect("*/*/configure_store");
        }

        $this->loadLayout();
        $this->initLayoutMessages('klevu_search/session');
        $this->renderLayout();
    }

    public function configure_attributes_postAction() {
        $request = $this->getRequest();

        if (!$request->isPost() || !$request->isAjax()) {
            return $this->_redirect("adminhtml/dashboard");
        }

        $session = Mage::getSingleton("klevu_search/session");

        if (!$session->getConfiguredCustomerId()) {
            $session->addError(Mage::helper("klevu_search")->__("You must configure a user first."));
            return $this->_redirect("*/*/configure_user");
        }

        if (!$session->getConfiguredStoreCode()) {
            $session->addError(Mage::helper("klevu_search")->__("Must select a store"));
            return $this->_redirect("*/*/configure_store");
        }

        if ($attributes = $request->getPost("attributes")) {
            $store = Mage::app()->getStore($session->getConfiguredStoreCode());

            Mage::helper("klevu_search/config")->setAdditionalAttributesMap($attributes, $store);

            $session->addSuccess(Mage::helper("klevu_search")->__("Attributes configured successfully. Attribute mappings saved to System Configuration."));

            // Schedule a Product Sync
            Mage::getModel("klevu_search/product_sync")->schedule();

            $this->loadLayout();
            $this->initLayoutMessages("klevu_search/session");
            $this->renderLayout();
            return;
        } else {
            $session->addError(Mage::helper("klevu_search")->__("Missing attributes!"));
            return $this->_forward("configure_attributes");
        }
    }
}
