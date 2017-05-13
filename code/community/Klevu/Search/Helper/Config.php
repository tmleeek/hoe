<?php

class Klevu_Search_Helper_Config extends Mage_Core_Helper_Abstract {

    const XML_PATH_EXTENSION_ENABLED = "klevu_search/general/enabled";
    const XML_PATH_TEST_MODE         = "klevu_search/general/test_mode";
    const XML_PATH_JS_API_KEY        = "klevu_search/general/js_api_key";
    const XML_PATH_REST_API_KEY      = "klevu_search/general/rest_api_key";
    const XML_PATH_TEST_JS_API_KEY   = "klevu_search/general/test_js_api_key";
    const XML_PATH_TEST_REST_API_KEY = "klevu_search/general/test_rest_api_key";
    const XML_PATH_PRODUCT_SYNC_ENABLED   = "klevu_search/product_sync/enabled";
    const XML_PATH_PRODUCT_SYNC_FREQUENCY = "klevu_search/product_sync/frequency";
    const XML_PATH_PRODUCT_SYNC_LAST_RUN = "klevu_search/product_sync/last_run";
    const XML_PATH_ATTRIBUTES_ADDITIONAL  = "klevu_search/attributes/additional";
    const XML_PATH_ORDER_SYNC_ENABLED   = "klevu_search/order_sync/enabled";
    const XML_PATH_ORDER_SYNC_FREQUENCY = "klevu_search/order_sync/frequency";
    const XML_PATH_ORDER_SYNC_LAST_RUN = "klevu_search/order_sync/last_run";
    const XML_PATH_FORCE_LOG = "klevu_search/developer/force_log";
    const XML_PATH_LOG_LEVEL = "klevu_search/developer/log_level";
    const XML_PATH_STORE_ID = "stores/%s/system/store/id";

    const DATETIME_FORMAT = "Y-m-d H:i:s T";

    /**
     * Set the Enable on Frontend flag in System Configuration for the given store.
     *
     * @param      $flag
     * @param Mage_Core_Model_Store|int|null $store Store to set the flag for. Defaults to current store.
     *
     * @return $this
     */
    public function setExtensionEnabledFlag($flag, $store = null) {
        $flag = ($flag) ? 1 : 0;
        $this->setStoreConfig(static::XML_PATH_EXTENSION_ENABLED, $flag, $store);
        return $this;
    }

    /**
     * Check if the Klevu_Search extension is enabled in the system configuration for the current store.
     *
     * @param $store_id
     *
     * @return bool
     */
    public function isExtensionEnabled($store_id = null) {
        return Mage::getStoreConfigFlag(static::XML_PATH_EXTENSION_ENABLED, $store_id);
    }

    /**
     * Set the Test Mode flag in System Configuration for the given store.
     *
     * @param      $flag
     * @param null $store Store to use. If not specified, uses the current store.
     *
     * @return $this
     */
    public function setTestModeEnabledFlag($flag, $store = null) {
        $flag = ($flag) ? 1 : 0;
        $this->setStoreConfig(static::XML_PATH_TEST_MODE, $flag, $store);
        return $this;
    }

    /**
     * Return the configuration flag for enabling test mode.
     *
     * @param Mage_Core_Model_Store|int $store
     *
     * @return bool
     */
    public function getTestModeEnabledFlag($store = null) {
        return Mage::getStoreConfigFlag(static::XML_PATH_TEST_MODE, $store);
    }

    /**
     * Check if Test Mode is enabled for the given store.
     *
     * @param Mage_Core_Model_Store|int $store
     *
     * @return bool
     */
    public function isTestModeEnabled($store = null) {
        return $this->getTestModeEnabledFlag($store) || !Mage::helper("klevu_search")->isProductionDomain(Mage::app()->getStore($store)->getBaseUrl());
    }

    /**
     * Set the JS API key in System Configuration for the given store.
     *
     * @param string                    $key
     * @param Mage_Core_Model_Store|int $store     Store to use. If not specified, will use the current store.
     * @param bool                      $test_mode Set the key to be used in Test Mode.
     *
     * @return $this
     */
    public function setJsApiKey($key, $store = null, $test_mode = false) {
        $path = ($test_mode) ? static::XML_PATH_TEST_JS_API_KEY : static::XML_PATH_JS_API_KEY;
        $this->setStoreConfig($path, $key, $store);
        return $this;
    }

    /**
     * Return the JS API key configured for the specified store.
     *
     * @param Mage_Core_Model_Store|int $store
     *
     * @return string
     */
    public function getJsApiKey($store = null) {
        if ($this->isTestModeEnabled($store)) {
            return Mage::getStoreConfig(static::XML_PATH_TEST_JS_API_KEY, $store);
        } else {
            return Mage::getStoreConfig(static::XML_PATH_JS_API_KEY, $store);
        }
    }

    /**
     * Set the REST API key in System Configuration for the given store.
     *
     * @param string                    $key
     * @param Mage_Core_Model_Store|int $store     Store to use. If not specified, will use the current store.
     * @param bool                      $test_mode Set the key to be used in Test Mode.
     *
     * @return $this
     */
    public function setRestApiKey($key, $store = null, $test_mode = false) {
        $path = ($test_mode) ? static::XML_PATH_TEST_REST_API_KEY : static::XML_PATH_REST_API_KEY;
        $this->setStoreConfig($path, $key, $store);
        return $this;
    }

    /**
     * Return the REST API key configured for the specified store.
     *
     * @param Mage_Core_Model_Store|int $store
     *
     * @return mixed
     */
    public function getRestApiKey($store = null) {
        if ($this->isTestModeEnabled($store)) {
            return Mage::getStoreConfig(static::XML_PATH_TEST_REST_API_KEY, $store);
        } else {
            return Mage::getStoreConfig(static::XML_PATH_REST_API_KEY, $store);
        }
    }

    /**
     * Check if the Klevu Search extension is configured for the given store.
     *
     * @param null $store_id
     *
     * @return bool
     */
    public function isExtensionConfigured($store_id = null) {
        $js_api_key = $this->getJsApiKey($store_id);
        $rest_api_key = $this->getRestApiKey($store_id);

        return (
            $this->isExtensionEnabled($store_id)
            && !empty($js_api_key)
            && !empty($rest_api_key)
        );
    }

    /**
     * Return the system configuration setting for enabling Product Sync for the specified store.
     * The returned value can have one of three possible meanings: Yes, No and Forced. The
     * values mapping to these meanings are available as constants on
     * Klevu_Search_Model_System_Config_Source_Yesnoforced.
     *
     * @param $store_id
     *
     * @return int
     */
    public function getProductSyncEnabledFlag($store_id = null) {
        return intval(Mage::getStoreConfig(static::XML_PATH_PRODUCT_SYNC_ENABLED, $store_id));
    }

    /**
     * Check if Product Sync is enabled for the specified store and domain.
     *
     * @param $store_id
     *
     * @return bool
     */
    public function isProductSyncEnabled($store_id = null) {
        $flag = $this->getProductSyncEnabledFlag($store_id);

        // Require "Forced" configuration setting to enable Product Sync on non production environments
        if (Mage::helper("klevu_search")->isProductionDomain(Mage::getBaseUrl())) {
            return in_array($flag, array(
                Klevu_Search_Model_System_Config_Source_Yesnoforced::YES,
                Klevu_Search_Model_System_Config_Source_Yesnoforced::FORCED
            ));
        } else {
            return $flag === Klevu_Search_Model_System_Config_Source_Yesnoforced::FORCED;
        }
    }

    /**
     * Return the configured frequency expression for Product Sync.
     *
     * @return string
     */
    public function getProductSyncFrequency() {
        return Mage::getStoreConfig(static::XML_PATH_PRODUCT_SYNC_FREQUENCY);
    }

    /**
     * Set the last Product Sync run time in System Configuration for the given store.
     *
     * @param DateTime|string                $datetime If string is passed, it will be converted to DateTime.
     * @param Mage_Core_Model_Store|int|null $store
     *
     * @return $this
     */
    public function setLastProductSyncRun($datetime = "now", $store = null) {
        if (!$datetime instanceof DateTime) {
            $datetime = new DateTime($datetime);
        }

        $this->setStoreConfig(static::XML_PATH_PRODUCT_SYNC_LAST_RUN, $datetime->format(static::DATETIME_FORMAT), $store);

        return $this;
    }

    /**
     * Check if Product Sync has ever run for the given store.
     *
     * @param Mage_Core_Model_Store|int|null $store
     *
     * @return bool
     */
    public function hasProductSyncRun($store = null) {
        $config = Mage::getConfig();

        if (!$config->getNode(static::XML_PATH_PRODUCT_SYNC_LAST_RUN, "store", $store)) {
            return false;
        }

        return true;
    }

    public function setAdditionalAttributesMap($map, $store = null) {
        unset($map["__empty"]);
        $this->setStoreConfig(static::XML_PATH_ATTRIBUTES_ADDITIONAL, serialize($map), $store);
        return $this;
    }

    /**
     * Return the map of additional Klevu attributes to Magento attributes.
     *
     * @param int|Mage_Core_Model_Store $store
     *
     * @return array
     */
    public function getAdditionalAttributesMap($store = null) {
        $map = unserialize(Mage::getStoreConfig(static::XML_PATH_ATTRIBUTES_ADDITIONAL, $store));

        return (is_array($map)) ? $map : array();
    }

    /**
     * Return the System Configuration setting for enabling Order Sync for the given store.
     * The returned value can have one of three possible meanings: Yes, No and Forced. The
     * values mapping to these meanings are available as constants on
     * Klevu_Search_Model_System_Config_Source_Yesnoforced.
     *
     * @param Mage_Core_Model_Store|int $store
     *
     * @return int
     */
    public function getOrderSyncEnabledFlag($store = null) {
        return intval(Mage::getStoreConfig(static::XML_PATH_ORDER_SYNC_ENABLED, $store));
    }

    /**
     * Check if Order Sync is enabled for the given store on the current domain.
     *
     * @param Mage_Core_Model_Store|int $store
     *
     * @return bool
     */
    public function isOrderSyncEnabled($store = null) {
        $flag = $this->getOrderSyncEnabledFlag($store);

        // Require "Forced" configuration setting to enable Sync on non production environments
        if (Mage::helper("klevu_search")->isProductionDomain(Mage::getBaseUrl())) {
            return in_array($flag, array(
                Klevu_Search_Model_System_Config_Source_Yesnoforced::YES,
                Klevu_Search_Model_System_Config_Source_Yesnoforced::FORCED
            ));
        } else {
            return $flag === Klevu_Search_Model_System_Config_Source_Yesnoforced::FORCED;
        }
    }

    /**
     * Return the configured frequency expression for Order Sync.
     *
     * @return string
     */
    public function getOrderSyncFrequency() {
        return Mage::getStoreConfig(static::XML_PATH_ORDER_SYNC_FREQUENCY);
    }

    /**
     * Set the last Order Sync run time in System Configuration.
     *
     * @param DateTime|string $datetime If string is passed, it will be converted to DateTime.
     *
     * @return $this
     */
    public function setLastOrderSyncRun($datetime = "now") {
        if (!$datetime instanceof DateTime) {
            $datetime = new DateTime($datetime);
        }

        $this->setGlobalConfig(static::XML_PATH_ORDER_SYNC_LAST_RUN, $datetime->format(static::DATETIME_FORMAT));

        return $this;
    }

    /**
     * Check if default Magento log settings should be overridden to force logging for this module.
     *
     * @return bool
     */
    public function isLoggingForced() {
        return Mage::getStoreConfigFlag(static::XML_PATH_FORCE_LOG);
    }

    /**
     * Return the minimum log level configured. Default to Zend_Log::WARN.
     *
     * @return int
     */
    public function getLogLevel() {
        $log_level = Mage::getStoreConfig(static::XML_PATH_LOG_LEVEL);

        return ($log_level !== null) ? intval($log_level) : Zend_Log::INFO;
    }

    /**
     * Set the global scope System Configuration value for the given key.
     *
     * @param string $key
     * @param string $value
     *
     * @return $this
     */
    protected function setGlobalConfig($key, $value) {
        Mage::getConfig()
            ->saveConfig($key, $value, "default")
            ->reinit();

        return $this;
    }

    /**
     * Set the store scope System Configuration value for the given key.
     *
     * @param string                         $key
     * @param string                         $value
     * @param Mage_Core_Model_Store|int|null $store If not given, current store will be used.
     *
     * @return $this
     */
    protected function setStoreConfig($key, $value, $store = null) {
        $config = Mage::getConfig();

        $store_code = Mage::app()->getStore($store)->getCode();
        $scope_id = $config->getNode(sprintf(static::XML_PATH_STORE_ID, $store_code));
        if ($scope_id !== null) {
            $scope_id = (int) $scope_id;

            $config->saveConfig($key, $value, "stores", $scope_id);

            $config->reinit();
        }

        return $this;
    }
}
