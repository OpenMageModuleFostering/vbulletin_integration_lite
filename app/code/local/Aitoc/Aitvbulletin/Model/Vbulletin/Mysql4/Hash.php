<?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/
class Aitoc_Aitvbulletin_Model_Vbulletin_Mysql4_Hash extends Aitoc_Aitvbulletin_Model_Vbulletin_Mysql4_Abstract 
{
    protected function _construct()
    {
        $this->_init('aitvbulletin_vbulletin/aitmagentovb_hash', 'hashid');
        parent::_construct();
    }
    
    protected function _getLoadSelect($field, $value, $object)
    {
        $aFields = array(
            'main_table.*',
            'is_expired' => 'IF(main_table.`validthru` < NOW(), 1, 0)',
        );
        
        $select = $this->_getReadAdapter()->select()
            ->from(array('main_table' => $this->getMainTable()), $aFields)
            ->where('main_table.`'.$field.'`=?', $value);
        
        return $select;
    }
    
}