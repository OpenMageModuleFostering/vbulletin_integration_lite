<?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/
class Aitoc_Aitvbulletin_Model_Vbulletin_Mysql4_User extends Aitoc_Aitvbulletin_Model_Vbulletin_Mysql4_Abstract 
{
    protected function _construct()
    {
        $this->_init('aitvbulletin_vbulletin/user', 'userid');
        parent::_construct();
    }
    
    protected function _getLoadSelect($field, $value, $object)
    {
        $aFields = array(
            'user.*',
            'avatar.avatarpath',
            'hascustomavatar' => 'NOT ISNULL(customavatar.userid)',
            'avatardateline' => 'customavatar.dateline',
        );
        
        $select = $this->_getReadAdapter()->select()
            ->from    ($this->getVbTable('user'), $aFields)
            ->joinLeft($this->getVbTable('avatar'), 'avatar.avatarid = user.avatarid', array())
            ->joinLeft($this->getVbTable('customavatar'), 'customavatar.userid = user.userid', array())
            ->where('user.`'.$field.'`=?', $value)
            ;
        
        return $select;
    }

    /**
     * Enter description here...
     *
     * @param Aitoc_Aitvbulletin_Model_Vbulletin_User $user
     * @param string $username
     * @return Aitoc_Aitvbulletin_Model_Vbulletin_Mysql4_User
     * 
     */
    public function loadByForumUsername($user, $username)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getVbTable('user'), 'userid')
            ->where('user.username = ?', $username);
        $id = $this->_getReadAdapter()->fetchOne($select);
        if ($id)
        {
            $this->load($user, $id);
        }
        else 
        {
            $user->setData(array());
        }
        return $this;
    }
    
}