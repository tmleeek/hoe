<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2008-2014 Amasty (http://www.amasty.com)
 */

$this->startSetup();

$this->run("
CREATE TABLE IF NOT EXISTS `{$this->getTable('amfile/file')}` (
  `file_id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `file_url` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_link` varchar(255) NOT NULL,
  PRIMARY KEY (`file_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `{$this->getTable('amfile/icon')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `image` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

INSERT INTO `{$this->getTable('amfile/icon')}` (`type`, `image`) VALUES
('flash', 'fla-24_32.png'),
('ini', 'ini-24_32.png'),
('jpg', 'jpeg-24_32.png'),
('mp3', 'mp3-24_32.png'),
('readme', 'readme-24_32.png'),
('txt', 'text-24_32.png'),
('zip', 'zip-24_32.png'),
('avi', 'avi-24_32.png');

CREATE TABLE IF NOT EXISTS `{$this->getTable('amfile/stat')}` (
  `product_id` mediumint(9) NOT NULL,
  `store` int(3) NOT NULL,
  `file_url` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `rating` int(10) NOT NULL DEFAULT '1',
  `date` datetime NOT NULL,
  `file_id` mediumint(9) NOT NULL,
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `{$this->getTable('amfile/store')}` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `file_id` mediumint(9) NOT NULL,
  `store_id` smallint(5) unsigned NOT NULL,
  `label` varchar(255) DEFAULT '',
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `position` int(10) NOT NULL,
  `use_default_label` tinyint(1) NOT NULL DEFAULT '0',
  `use_default_visible` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `file_id` (`file_id`,`store_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB;

ALTER TABLE `{$this->getTable('amfile/store')}`
  ADD FOREIGN KEY (`file_id`) REFERENCES `{$this->getTable('amfile/file')}` (`file_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core/store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");

$this->endSetup();
