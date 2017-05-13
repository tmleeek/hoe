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
 * Config model
 */
class Innobyte_Plationline_Model_Config extends Mage_Payment_Model_Config {
	const PO_PAYMENT_PATH = 'payment/plationline/';

	/**
	 * Return plationline payment config information
	 *
	 * @param string $path
	 * @param int $storeId
	 * @return Simple_Xml
	 */
	public function getConfigData($path, $storeId = null) {
		if (!empty($path)) {
			return Mage::getStoreConfig(self::PO_PAYMENT_PATH . $path, $storeId);
		}
		return false;
	}

	/**
	 * Return KeyEnc crypt key from config. Setup on admin place.
	 *
	 * @param int $storeId
	 * @return string
	 */
	public function getKeyenc($storeId = null) {
//         return Mage::helper('core')->decrypt($this->getConfigData('secret_key_in', $storeId));
		return $this->getConfigData('keyenc', $storeId);
	}

	/**
	 * Return KeyMod crypt key from config. Setup on admin place.
	 * @param int $storeId
	 * @return string
	 */
	public function getKeymod($storeId = null) {
//         return Mage::helper('core')->decrypt($this->getConfigData('secret_key_out', $storeId));
		return $this->getConfigData('keymod', $storeId);
	}

	/**
	 * Return gateway path, get from confing. Setup on admin place.
	 *
	 * @param int $storeId
	 * @return string
	 */
	public function getGatewayPath($storeId = null) {
		return $this->getConfigData('plationline_gateway', $storeId);
	}

	/**
	 * Get Login ID, affiliation name in plationline system
	 *
	 * @param int $storeId
	 * @return string
	 */
	public function getLoginId($storeId = null) {
		return $this->getConfigData('login_id', $storeId);
	}

	/**
	 * Get Login ID, affiliation name in plationline system
	 *
	 * @param int $storeId
	 * @return string
	 */
	public function getRsaPub($storeId = null) {
		return $this->getConfigData('rsa_public_auth', $storeId);
	}

	/**
	 * Get Login ID, affiliation name in plationline system
	 *
	 * @param int $storeId
	 * @return string
	 */
	public function getRsaPvt($storeId = null) {
		return $this->getConfigData('rsa_private_itsn', $storeId);
	}

	/**
	 * Get Login ID, affiliation name in plationline system
	 *
	 * @param int $storeId
	 * @return string
	 */
	public function getPaymentAction($storeId = null) {
		return $this->getConfigData('payment_action', $storeId);
	}

	/**
	 * Get Login ID, affiliation name in plationline system
	 *
	 * @param int $storeId
	 * @return string
	 */
	public function getIVAuth($storeId = null) {
		return $this->getConfigData('iv_auth', $storeId);
	}

	/**
	 * Get Login ID, affiliation name in plationline system
	 *
	 * @param int $storeId
	 * @return string
	 */
	public function getIVITSN($storeId = null) {
		return $this->getConfigData('iv_itsn', $storeId);
	}

	/**
	 * Get paypage template for magento style templates using
	 *
	 * @return string
	 */
	public function getPayPageTemplate() {
		return Mage::getUrl('plationline/api/paypage');
	}

	/**
	 * Return url which plationline system will use as accept
	 *
	 * @return string
	 */
	public function getAcceptUrl() {
		return Mage::getUrl('plationline/api/accept');
	}

	/**
	 * Return url which plationline system will use as decline url
	 *
	 * @return string
	 */
	public function getDeclineUrl() {
		return Mage::getUrl('plationline/api/decline');
	}

	/**
	 * Return url which plationline system will use as exception url
	 *
	 * @return string
	 */
	public function getExceptionUrl() {
		return Mage::getUrl('plationline/api/exception');
	}

	/**
	 * Return url which plationline system will use as cancel url
	 *
	 * @return string
	 */
	public function getCancelUrl() {
		return Mage::getUrl('plationline/api/cancel');
	}

	/**
	 * Return url which plationline system will use as our magento home url on ogone success page
	 *
	 * @return string
	 */
	public function getHomeUrl() {
		return Mage::getUrl('checkout/cart');
	}
}
