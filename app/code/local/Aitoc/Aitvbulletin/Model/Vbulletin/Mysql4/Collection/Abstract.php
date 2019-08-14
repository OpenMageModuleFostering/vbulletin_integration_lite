<?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/

abstract class Aitoc_Aitvbulletin_Model_Vbulletin_Mysql4_Collection_Abstract extends Mage_Core_Model_Mysql4_Collection_Abstract 
{
    /**
     * @return Aitoc_Aitvbulletin_Model_Vbulletin_Mysql4_Forum
     */
    public function getResource()
    {
        return parent::getResource();
    }
    
    public function getVbTable($tableName, $as_array = true)
    {
        return $this->getResource()->getVbTable($tableName, $as_array);
    }
    
}