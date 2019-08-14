<?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/

abstract class Aitoc_Aitvbulletin_Model_Vbulletin_Abstract extends Mage_Core_Model_Abstract 
{
    protected function _construct()
    {
        if ($this->getData('__resource_config__'))
        {
            $this->getResource()->setConfig($this->getData('__resource_config__'));
            $this->setData('__resource_config__', null);
        }
        else 
        {
            $this->getResource()->getConfig();
        }
    }
    
    /**
     * @return Aitoc_Aitvbulletin_Model_Vbulletin_Mysql4_Thread
     */
    public function getResource()
    {
        return parent::getResource();
    }
    
}