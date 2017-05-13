<?php

Mage::log('Upgrading Plationline module to v1.1.1');

$installer = $this;
$installer->startSetup();

// Required tables
$statusTable = $installer->getTable('sales/order_status');
$statusStateTable = $installer->getTable('sales/order_status_state');

$data = array(
    array('status' => 'pending_plationline', 'label' => 'PlatiOnline Pending'),
    array('status' => 'cancel_plationline', 'label' => 'PlatiOnline Canceled'),
    array('status' => 'decline_plationline', 'label' => 'PlatiOnline Declined'),
    array('status' => 'error_plationline', 'label' => 'PlatiOnline Error'),
    array('status' => 'onhold_plationline', 'label' => 'PlatiOnline On Hold'),
    array('status' => 'settled_plationline', 'label' => 'PlatiOnline Settled'),
    array('status' => 'credited_plationline', 'label' => 'PlatiOnline Credited'),
    array('status' => 'payment_refused_plationline', 'label' => 'PlatiOnline Payment Refused'),
    array('status' => 'expired30_plationline', 'label' => 'PlatiOnline Expired 30'),
    array('status' => 'pending_settled_plationline', 'label' => 'PlatiOnline Pending Settle'),
    array('status' => 'pending_credited_plationline', 'label' => 'PlatiOnline Pending Credit'),
    array('status' => 'pending_cancel_plationline', 'label' => 'PlatiOnline Pending Cancel'),
);

foreach ($data as $row) {
    $installer->getConnection()->insertOnDuplicate($statusTable, $row, array('status', 'label'));
}

// Insert statuses
// $installer->getConnection()->insertArray(
//     $statusTable,
//     array(
//         'status',
//         'label',
//     ),
//     array(
//         array('status' => 'pending_plationline', 'label' => 'PlatiOnline Pending'),
//         array('status' => 'cancel_plationline', 'label' => 'PlatiOnline Canceled'),
//         array('status' => 'decline_plationline', 'label' => 'PlatiOnline Declined'),
//         array('status' => 'error_plationline', 'label' => 'PlatiOnline Error'),
//         array('status' => 'onhold_plationline', 'label' => 'PlatiOnline On Hold'),
//         array('status' => 'settled_plationline', 'label' => 'PlatiOnline Settled'),
//         array('status' => 'credited_plationline', 'label' => 'PlatiOnline Credited'),
//         array('status' => 'payment_refused_plationline', 'label' => 'PlatiOnline Payment Refused'),
//         array('status' => 'expired30_plationline', 'label' => 'PlatiOnline Expired 30'),
//         array('status' => 'pending_settled_plationline', 'label' => 'PlatiOnline Pending Settle'),
//         array('status' => 'pending_credited_plationline', 'label' => 'PlatiOnline Pending Credit'),
//         array('status' => 'pending_cancel_plationline', 'label' => 'PlatiOnline Pending Cancel'),
//     )
// );

$data = array(
    array(
        'status' => 'pending_plationline',
        'state' => 'processing',
        'is_default' => 0,
    ),
    array(
        'status' => 'cancel_plationline',
        'state' => 'processing',
        'is_default' => 0,
    ),
    array(
        'status' => 'decline_plationline',
        'state' => 'processing',
        'is_default' => 0,
    ),
    array(
        'status' => 'error_plationline',
        'state' => 'processing',
        'is_default' => 0,
    ),
    array(
        'status' => 'onhold_plationline',
        'state' => 'processing',
        'is_default' => 0,
    ),
    array(
        'status' => 'settled_plationline',
        'state' => 'processing',
        'is_default' => 0,
    ),
    array(
        'status' => 'credited_plationline',
        'state' => 'processing',
        'is_default' => 0,
    ),
    array(
        'status' => 'payment_refused_plationline',
        'state' => 'processing',
        'is_default' => 0,
    ),
    array(
        'status' => 'expired30_plationline',
        'state' => 'processing',
        'is_default' => 0,
    ),
    array(
        'status' => 'pending_settled_plationline',
        'state' => 'processing',
        'is_default' => 0,
    ),
    array(
        'status' => 'pending_credited_plationline',
        'state' => 'processing',
        'is_default' => 0,
    ),
    array(
        'status' => 'pending_cancel_plationline',
        'state' => 'processing',
        'is_default' => 0,
    ),
);

foreach ($data as $row) {
    $installer->getConnection()->insertOnDuplicate($statusStateTable, $row, array('status', 'state', 'is_default'));
}

// $installer->getConnection()->insertArray(
//     $statusStateTable,
//     array(
//         'status',
//         'state',
//         'is_default',
//     ),
//     array(
//         array(
//             'status' => 'pending_plationline',
//             'state' => 'processing',
//             'is_default' => 0,
//         ),
//         array(
//             'status' => 'cancel_plationline',
//             'state' => 'processing',
//             'is_default' => 0,
//         ),
//         array(
//             'status' => 'decline_plationline',
//             'state' => 'processing',
//             'is_default' => 0,
//         ),
//         array(
//             'status' => 'error_plationline',
//             'state' => 'processing',
//             'is_default' => 0,
//         ),
//         array(
//             'status' => 'onhold_plationline',
//             'state' => 'processing',
//             'is_default' => 0,
//         ),
//         array(
//             'status' => 'settled_plationline',
//             'state' => 'processing',
//             'is_default' => 0,
//         ),
//         array(
//             'status' => 'credited_plationline',
//             'state' => 'processing',
//             'is_default' => 0,
//         ),
//         array(
//             'status' => 'payment_refused_plationline',
//             'state' => 'processing',
//             'is_default' => 0,
//         ),
//         array(
//             'status' => 'expired30_plationline',
//             'state' => 'processing',
//             'is_default' => 0,
//         ),
//         array(
//             'status' => 'pending_settled_plationline',
//             'state' => 'processing',
//             'is_default' => 0,
//         ),
//         array(
//             'status' => 'pending_credited_plationline',
//             'state' => 'processing',
//             'is_default' => 0,
//         ),
//         array(
//             'status' => 'pending_cancel_plationline',
//             'state' => 'processing',
//             'is_default' => 0,
//         ),
//     )
// );

$installer->endSetup();
