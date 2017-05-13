<?php
class CustomHOEExport_Product_Adapter 
extends Mage_Catalog_Model_Convert_Adapter_Product
{

    /**
     * Extend the parent method to add filtering capability for additional fields
     *
     * This is required since the parent load() uses a parent::setFilter instead of $this->setFilter
     *
     * @return Mage_Dataflow_Model_Convert_Adapter_Interface
     */
    public function load()
    {
        // Add any additional attributes you want to filter on here
        $attrFilterArray = array(
            'manufacturer' => 'eq',
        );

        $this->setFilter($attrFilterArray, array());

        return parent::load();
    }

}
?>
