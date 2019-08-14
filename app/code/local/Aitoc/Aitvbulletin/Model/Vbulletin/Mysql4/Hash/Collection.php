<?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/

class Aitoc_Aitvbulletin_Model_Vbulletin_Mysql4_Hash_Collection extends Aitoc_Aitvbulletin_Model_Vbulletin_Mysql4_Collection_Abstract 
{
    
    protected function _construct()
    {
        $this->_init('aitvbulletin_vbulletin/hash');
        parent::_construct();
    }
    
    protected function _initSelect()
    {
        $aFields = array(
            'main_table.*',
            'is_expired' => 'IF(main_table.`validthru` < NOW(), 1, 0)',
        );
        
        $this->getSelect()
            ->from(array('main_table' => $this->getResource()->getMainTable()), $aFields);
        
        return $this;
    }
    
    public function addHashFilter($hash)
    {
        $this->getSelect()
            ->where('main_table.hash = ?', $hash);
        
        return $this;
    } 
    
}