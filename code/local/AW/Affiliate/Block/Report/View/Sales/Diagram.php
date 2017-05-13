<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Affiliate
 * @version    1.1.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Affiliate_Block_Report_View_Sales_Diagram extends Mage_Core_Block_Template
{
    protected static $_chartColumnTypes = array('string', 'number', 'boolean', 'date', 'datetime', 'timeofday');
    protected $_chartColumns = array();

    public function __construct()
    {
        $this->setTemplate('aw_affiliate/report/view/sales/diagram.phtml');
    }

    /**
     * Adds column to chart.
     * Outputs column object for google.vizualization.DataTable.addColumn
     * The column name is not being checked against the columns list;
     * the defective column will be filled with zeros in the data array instead.
     *
     * @param string $name
     * @param string $type
     * @param string $label
     * @return array
     */
    public function addChartColumn($name, $type = '', $label = '')
    {
        $type = strtolower($type);
        if (!in_array($type, self::$_chartColumnTypes)) {
            $type = self::$_chartColumnTypes[0];
        }

        $this->_chartColumns[$name] = array('type' => $type, 'label' => $label);
        return $this->_chartColumns[$name];

    }

    /**
     * Returns all chart columns
     *
     * @return array
     */
    public function getChartColumns()
    {
        return $this->_chartColumns;
    }

    /**
     * Returns chart data as array of rows, with the row data as indexed array
     * instead of associative array.
     * Use JSON encode to pass this data to google.vizualization.DataTable.addRows
     *
     * @return array
     */
    public function getChartRows()
    {
        if (!$this->getItems()) {
            return array();
        }
        $columns = array();
        foreach ($this->_chartColumns as $column => $spec) {
            $stub = Mage::helper('awaffiliate/report')->arrayColumnPad($this->getItems(), $column);
            if ($spec['type'] == 'number') {
                # convert numbers in strings into numerical float values
                array_walk(
                    $stub,
                    function(&$value, $index)
                    {
                        $value = floatval($value);
                    }
                );
            }
            $columns[] = $stub;
        }
        return Mage::helper('awaffiliate/report')->arrayInverse($columns);
    }

}
