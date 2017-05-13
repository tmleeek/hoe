<?php

class MindMagnet_CustomDesign_Helper_Data extends Mage_Core_Helper_Abstract
{
    const CONFIG_DATA_ROOT_CAT_FILTER = 'mm_extra/navigation/cat_root';
    
    public function getCatRootFilter()
    {
        return Mage::getStoreConfig(self::CONFIG_DATA_ROOT_CAT_FILTER);
    }
}
