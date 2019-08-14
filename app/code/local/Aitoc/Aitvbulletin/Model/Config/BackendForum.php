<?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/

class Aitoc_Aitvbulletin_Model_Config_BackendForum extends Mage_Core_Model_Config_Data
{
    protected function _beforeSave()
    {
        $value = $this->getValue();
        if ($value)
        { 
            try 
            {
                Mage::getModel('aitvbulletin/vbserver')->validate();
            }
            catch (Aitoc_Aitvbulletin_Model_Exception $e)
            {
                Mage::throwException(Mage::helper('aitvbulletin')->__('Please setup vBulletin Connection first')); 
            }
        }
        
        return $this; 
    }
}