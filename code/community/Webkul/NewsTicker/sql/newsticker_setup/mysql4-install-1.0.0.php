<?php
$installer = $this;
$installer->startSetup();
$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('wk_newsticker')};
CREATE TABLE {$this->getTable('wk_newsticker')} (
    `news_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `newsticker` varchar(255) NOT NULL,
    `status` smallint(6) NOT NULL DEFAULT '0',
    `sort_order` int(11) NOT NULL DEFAULT '0',
    `created_time` datetime DEFAULT NULL,
    `update_time` datetime DEFAULT NULL,
    PRIMARY KEY (`news_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 
-- DROP TABLE IF EXISTS {$this->getTable('wk_newstickergroup')};
CREATE TABLE {$this->getTable('wk_newstickergroup')} (
    `group_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `news_ids` varchar(255) NOT NULL DEFAULT '',
    `group_code` varchar(255) NOT NULL DEFAULT '',
    `group_name` varchar(255) NOT NULL DEFAULT '',
    `texttitle` text NOT NULL,
    `tickerwidth` int(11) NOT NULL,
    `direction` int(11) NOT NULL,
    `controls` int(11) NOT NULL,
    `displaytype` int(11) NOT NULL,
    `pauseonitems` varchar(255) NOT NULL,
    `fadeinspeed` varchar(255) NOT NULL,
    `fadeoutspeed` varchar(255) NOT NULL,
    `speed` int(11) NOT NULL,
    `status` smallint(6) NOT NULL DEFAULT '0',
    `cms_pages` varchar(255) NOT NULL,
    `created_time` datetime DEFAULT NULL,
    `update_time` datetime DEFAULT NULL,
    PRIMARY KEY (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$installer->endSetup();
