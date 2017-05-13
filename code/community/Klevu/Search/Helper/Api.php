<?php

class Klevu_Search_Helper_Api extends Mage_Core_Helper_Abstract {

    /**
     * Create a new Klevu user using the API and return the user details.
     *
     * @param $email
     * @param $password
     * @param $url
     *
     * @return array An array containing the following keys:
     *                 success:     boolean value indicating whether the user was created successfully.
     *                 customer_id: the customer ID for the newly created user (on success only).
     *                 message:     a message to be shown to the user.
     */
    public function createUser($email, $password, $url) {
        $response = Mage::getModel("klevu_search/api_action_adduser")->execute(array(
            "email"    => $email,
            "password" => $password,
            "url"      => $url
        ));

        if ($response->isSuccessful()) {
            return array(
                "success"     => true,
                "customer_id" => $response->getCustomerId(),
                "message"     => $response->getMessage()
            );
        } else {
            return array(
                "success" => false,
                "message" => $response->getMessage()
            );
        }
    }

    /**
     * Retrieve the details for the given Klevu user from the API.
     *
     * @param $email
     * @param $password
     *
     * @return array An array containing the following keys:
     *                 success: boolean value indicating whether the operation was successful.
     *                 customer_id: (on success only) The customer ID of the requested user.
     *                 webstores: (on success only) A list of webstores the given user has configured.
     *                 message: (on failure only) Error message to be shown to the user.
     */
    public function getUser($email, $password) {
        $response = Mage::getModel("klevu_search/api_action_getuserdetail")->execute(array(
            "email"    => $email,
            "password" => $password
        ));

        if ($response->isSuccessful()) {
            $webstores = array();

            // Add each webstore as a Varien_Object
            $webstores_data = $response->getWebstores();
            if ($webstores_data && isset($webstores_data['webstore'])) {
                $webstores_data = $webstores_data['webstore'];

                if (isset($webstores_data['storeName'])) {
                    // Got a single webstore
                    $webstores_data = array($webstores_data);
                }

                $i = 0;
                foreach ($webstores_data as $webstore_data) {
                    $webstore = array(
                        'id' => $i++
                    );
                    foreach($webstore_data as $key => $value) {
                        // Convert field names from camelCase to underscore (code taken from Varien_Object)
                        $webstore[strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $key))] = $value;
                    }
                    $webstores[] = new Varien_Object($webstore);
                }
            }

            return array(
                "success"     => true,
                "customer_id" => $response->getCustomerId(),
                "webstores"   => $webstores
            );
        } else {
            return array(
                "success" => false,
                "message" => $response->getMessage()
            );
        }
    }

    /**
     * Create a Klevu Webstore using the API for the given Magento store.
     *
     * @param                       $customer_id
     * @param Mage_Core_Model_Store $store
     * @param bool                  $test_mode
     *
     * @return array An array with the following keys:
     *                 success: boolean value indicating whether the operation was successful.
     *                 webstore: (success only) Varien_Object containing Webstore information.
     *                 message: message to be displayed to the user.
     */
    public function createWebstore($customer_id, Mage_Core_Model_Store $store, $test_mode = false) {
        $name = sprintf("%s - %s - %s",
            $store->getWebsite()->getName(),
            $store->getName(),
            $store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB)
        );
        $language = Mage::helper("klevu_search")->getStoreLanguage($store);
        $timezone = $store->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);

        // Convert $test_mode to string
        $test_mode = ($test_mode) ? "true" : "false";

        $response = Mage::getModel("klevu_search/api_action_addwebstore")->execute(array(
            "customerId" => $customer_id,
            "storeName"  => $name,
            "language"   => $language,
            "timezone"   => $timezone,
            "testMode"   => $test_mode
        ));

        if ($response->isSuccessful()) {
            $webstore = new Varien_Object(array(
                "store_name"           => $name,
                "js_api_key"           => $response->getJsApiKey(),
                "rest_api_key"         => $response->getRestApiKey(),
                "test_account_enabled" => $test_mode
            ));

            return array(
                "success"  => true,
                "webstore" => $webstore,
                "message"  => $response->getMessage()
            );
        } else {
            return array(
                "success" => false,
                "message" => $response->getMessage()
            );
        }
    }

    public function getTimezoneOptions() {
        $response = Mage::getModel('klevu_search/api_action_gettimezone')->execute();

        if ($response->isSuccessful()) {
            $options = array();

            $data = $response->getTimezone();

            if (!is_array($data)) {
                $data = array($data);
            }

            foreach ($data as $timezone) {
                $options[] = array(
                    "label" => $this->__($timezone),
                    "value" => $this->escapeHtml($timezone)
                );
            }

            return $options;
        } else {
            return $response->getMessage();
        }
    }
}
