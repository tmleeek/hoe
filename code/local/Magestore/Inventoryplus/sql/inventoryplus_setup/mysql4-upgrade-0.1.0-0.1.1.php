<?php

/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Inventory
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('erp_inventory_lite_upgrade_package_content')};
    CREATE TABLE {$this->getTable('erp_inventory_lite_upgrade_package_content')} (
    `upgrade_content_id` int(11) unsigned NOT NULL auto_increment,
    `last_update` date default NULL,
    `content` mediumtext,
    PRIMARY KEY  (`upgrade_content_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();

