<?php

class Klevu_Search_Helper_Data extends Mage_Core_Helper_Abstract {

    const LOG_FILE = "Klevu_Search.log";

    const ID_SEPARATOR = "-";

    /**
     * Given a locale code, extract the language code from it
     * e.g. en_GB => en, fr_FR => fr
     *
     * @param string $locale
     */
    function getLanguageFromLocale($locale) {
        if (strlen($locale) == 5 && strpos($locale, "_") == 2) {
            return substr($locale, 0, 2);
        }

        return $locale;
    }

    /**
     * Return the language code for the given store.
     *
     * @param int|Mage_Core_Model_Store $store
     *
     * @return string
     */
    function getStoreLanguage($store = null) {
        if ($store = Mage::app()->getStore($store)) {
            return $this->getLanguageFromLocale($store->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE));
        }
    }

    /**
     * Check if the given domain is considered to be a valid domain for a production environment.
     *
     * @param $domain
     *
     * @return bool
     */
    public function isProductionDomain($domain) {
        return preg_match("/\b(staging|dev|local)\b/", $domain) == 0;
    }

    /**
     * Generate a Klevu product ID for the given product.
     *
     * @param int      $product_id Magento ID of the product to generate a Klevu ID for.
     * @param null|int $parent_id  Optional Magento ID of the parent product.
     *
     * @return string
     */
    public function getKlevuProductId($product_id, $parent_id = 0) {
        if ($parent_id != 0) {
            $parent_id .= static::ID_SEPARATOR;
        } else {
            $parent_id = "";
        }

        return sprintf("%s%s", $parent_id, $product_id);
    }
	
	/**
     * Generate a Klevu product sku with parent product.
     *
     * @param string      $product_sku Magento Sku of the product to generate a Klevu sku for.
     * @param null $parent_sku  Optional Magento Parent Sku of the parent product.
     *
     * @return string
     */
    public function getKlevuProductSku($product_sku, $parent_sku = "") {
        if (!empty($parent_sku)) {
            $parent_sku .= static::ID_SEPARATOR;
        } else {
            $parent_sku = "";
        }
        return sprintf("%s%s", $parent_sku, $product_sku);
    }

    /**
     * Convert a Klevu product ID back into a Magento product ID. Returns an
     * array with "product_id" element for the product ID and a "parent_id"
     * element for the parent product ID or 0 if the Klevu product doesn't have
     * a parent.
     *
     * @param $klevu_id
     *
     * @return array
     */
    public function getMagentoProductId($klevu_id) {
        $parts = explode(static::ID_SEPARATOR, $klevu_id, 2);

        if (count($parts) > 1) {
            return array('product_id' => $parts[1], 'parent_id' => $parts[0]);
        } else {
            return array('product_id' => $parts[0], 'parent_id' => "0");
        }
    }

    /**
     * Format bytes into a human readable representation, e.g.
     * 6815744 => 6.5M
     *
     * @param     $bytes
     * @param int $precision
     *
     * @return string
     */
    public function bytesToHumanReadable($bytes, $precision = 2) {
        $suffixes = array("", "k", "M", "G", "T", "P");
        $base = log($bytes) / log(1024);
        return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
    }

    /**
     * Convert human readable formatting of bytes to bytes, e.g.
     * 6.5M => 6815744
     *
     * @param $string
     *
     * @return int
     */
    public function humanReadableToBytes($string) {
        $suffix = strtolower(substr($string, -1));
        $result = substr($string, 0, -1);

        switch ($suffix) {
            case 'g': // G is the max unit as of PHP 5.5.12
                $result *= 1024;
            case 'm':
                $result *= 1024;
            case 'k':
                $result *= 1024;
                break;
            default:
                $result = $string;
        }

        return ceil($result);
    }

    /**
     * Return the configuration data for a "Sync All Products" button displayed
     * on the Manage Products page in the backend.
     *
     * @return array
     */
    public function getSyncAllButtonData() {
        return array(
            'label'   => $this->__("Sync All Products to Klevu"),
            'onclick' => sprintf("setLocation('%s')", Mage::getModel('adminhtml/url')->getUrl("adminhtml/klevu_search/sync_all"))
        );
    }

    /**
     * Write a log message to the Klevu_Search log file.
     *
     * @param int    $level
     * @param string $message
     */
    public function log($level, $message) {
        $config = Mage::helper("klevu_search/config");

        if ($level <= $config->getLogLevel()) {
            Mage::log($message, $level, static::LOG_FILE, $config->isLoggingForced());
        }
    }
}
