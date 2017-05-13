<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Advanced Sphinx Search Pro
 * @version   2.3.2
 * @revision  634
 * @copyright Copyright (C) 2014 Mirasvit (http://mirasvit.com/)
 */


class Mirasvit_MstCore_Helper_Validator_Abstract extends Mage_Core_Helper_Abstract
{
    const SUCCESS = 1;
    const WARNING = 2;
    const INFO    = 3;
    const FAILED  = 0;

    public function runTests()
    {
        $results = array();

        $methods = get_class_methods($this);
        foreach ($methods as $method) {
            if (substr($method, 0, 4) == 'test') {
                $key = get_class($this).$method;
                try {
                    $results[$key] = call_user_func(array($this, $method));
                } catch (Exception $e) {
                    $results[$key] = array(self::FAILED, "Test '$method'", $e->getMessage());
                }
            }
        }

        return $results;
    }

    public function validateRewrite($class, $classNameB)
    {
        $classNameA = get_class(Mage::getModel($class));
        if ($classNameA == $classNameB) {
            return true;
        } else {
            return "$class must be $classNameB, current rewrite is $classNameA";
        }
    }

    public function dbTableExists($tableName)
    {
        $table = $this->_dbRes()->getTableName($tableName);

        return $this->_dbConn()->showTableStatus($table) !== false;
    }

    public function dbDescribeTable($tableName)
    {
        $table = $this->_dbRes()->getTableName($tableName);

        return $this->_dbConn()->describeTable($table); 
    }

    public function dbTableColumnExists($tableName, $column)
    {
        $desribe = $this->dbDescribeTable($tableName);

        return array_key_exists($column, $desribe);
    }

    public function dbTableIsEmpty($table)
    {   
        $select = $this->_dbConn()->select()->from($this->_dbRes()->getTableName($table));
        $row = $this->_dbConn()->fetchRow($select);

        if (is_array($row)) {
            return false;
        }

        return true;
    }

    public function ioIsReadable($path)
    {
        if (is_file($path) && !is_readable($path)) {
            return false;
        }

        return true;
    }

    public function ioIsWritable($path)
    {
        if (is_writable($path)) {
            return true;
        }

        return false;
    }

    public function ioNumberOfFiles($path)
    {
        $cnt = 0;
        $dir = new DirectoryIterator($path);
        foreach($dir as $file) {
            $cnt += (is_file($path.DS.$file)) ? 1 : 0;
        }

        return $cnt;
    }

    protected function _dbRes()
    {
        return Mage::getSingleton('core/resource');
    }

    protected function _dbConn()
    {
        return $this->_dbRes()->getConnection('core_write');
    }   
}
