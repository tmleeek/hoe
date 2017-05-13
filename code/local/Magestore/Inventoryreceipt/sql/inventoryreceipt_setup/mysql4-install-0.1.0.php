<?php

$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('erp_inventory_receipt_log')};
CREATE TABLE {$this->getTable('erp_inventory_receipt_log')} (
  `receipt_log_id` int(11) unsigned NOT NULL auto_increment,
  `created_by` varchar(255) default NULL,
  `created_at` datetime default NULL,
  `filename` varchar(255) NOT NULL default '',
  `content` text NOT NULL default '',
  `status` smallint(6) NOT NULL default '0', 
  PRIMARY KEY (`receipt_log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('erp_inventory_receipt_log_product')};
CREATE TABLE {$this->getTable('erp_inventory_receipt_log_product')} (
    `receipt_product_id` int(11) unsigned NOT NULL auto_increment,
    `warehouse_id` int(11) unsigned NOT NULL,
    `product_id` int(11) unsigned NOT NULL,
    `receipt_qty` decimal(10,0) default '0', 
    `receipt_log_id` int(11),     
    PRIMARY KEY (`receipt_product_id`)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 