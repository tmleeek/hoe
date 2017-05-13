<?php
/**
 * Oro Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is published at http://opensource.org/licenses/osl-3.0.php.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magecore.com so we can send you a copy immediately
 *
 * @category Oro
 * @package Api
 * @copyright Copyright 2013 Oro Inc. (http://www.orocrm.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class Oro_Api_Model_Ping extends Mage_Api_Model_Resource_Abstract
{
    const VERSION = '1.1.0';
    /**
     * @return array
     */
    public function ping()
    {
        return array(
            'version'      => self::VERSION,
            'mage_version' => Mage::getVersion(),
            'admin_url'    => Mage::getUrl('adminhtml'),
        );
    }
}
