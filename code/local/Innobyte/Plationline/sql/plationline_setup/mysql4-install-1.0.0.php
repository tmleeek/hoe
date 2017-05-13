<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('plationline/api_debug')};
CREATE TABLE IF NOT EXISTS {$this->getTable('plationline/api_debug')} (
  `debug_id` int(11) unsigned NOT NULL auto_increment,
  `dir` varchar(255) NOT NULL default '',
  `data` text NOT NULL default '',
  `created_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`debug_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

// Required tables
$statusTable = $installer->getTable('sales/order_status');
$statusStateTable = $installer->getTable('sales/order_status_state');

$data = array(
    array('status' => 'processing_plationline', 'label' => 'PlatiOnline Processing'),
    array('status' => 'processed_plationline', 'label' => 'PlatiOnline Processed'),
);

foreach ($data as $row) {
    $installer->getConnection()->insertOnDuplicate($statusTable, $row, array('status', 'label'));
}

// Insert statuses (old)
// $installer->getConnection()->insertArray(
//     $statusTable,
//     array(
//         'status',
//         'label',
//     ),
//     array(
//         array('status' => 'processing_plationline', 'label' => 'PlatiOnline Processing'),
//         array('status' => 'processed_plationline', 'label' => 'PlatiOnline Processed'),
//     )
// );

$data = array(
    array(
        'status' => 'processing_plationline',
        'state' => 'processing',
        'is_default' => 0,
    ),
    array(
        'status' => 'processed_plationline',
        'state' => 'processing',
        'is_default' => 0,
    ),
);

foreach ($data as $row) {
    $installer->getConnection()->insertOnDuplicate($statusStateTable, $row, array('status', 'state', 'is_default'));
}

// Insert states and mapping of statuses to states
// $installer->getConnection()->insertArray(
//     $statusStateTable,
//     array(
//         'status',
//         'state',
//         'is_default',
//     ),
//     array(
//         array(
//             'status' => 'processing_plationline',
//             'state' => 'processing',
//             'is_default' => 0,
//         ),
//         array(
//             'status' => 'processed_plationline',
//             'state' => 'processing',
//             'is_default' => 0,
//         ),
//     )
// );
//
$installer->endSetup();
