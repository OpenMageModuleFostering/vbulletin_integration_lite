<?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/

class Aitoc_Aitvbulletin_Model_Vbulletin_Mysql4_User_Collection extends Aitoc_Aitvbulletin_Model_Vbulletin_Mysql4_Collection_Abstract 
{
    protected function _construct()
    {
        $this->_init('aitvbulletin_vbulletin/user');
        parent::_construct();
    }
    
    protected function _initSelect()
    {
        $aFields = array(
            'user.*',
            'avatar.avatarpath',
            'hascustomavatar' => 'NOT ISNULL(customavatar.userid)',
            'avatardateline' => 'customavatar.dateline',
        );
        
        $this->getSelect()
            ->from    ($this->getVbTable('user'), $aFields)
            ->joinLeft($this->getVbTable('avatar'), 'avatar.avatarid = user.avatarid', array())
            ->joinLeft($this->getVbTable('customavatar'), 'customavatar.userid = user.userid', array())
            ;
        
        return $this;
    }
    
}