<?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/

class Aitoc_Aitvbulletin_Model_Rewrite_FrontCustomer extends Mage_Customer_Model_Customer
{
    protected function _afterLoad()
    {
        $forumUserId = $this->getAitvbulletinUserId();
        if ($forumUserId AND Mage::helper('aitvbulletin')->isModuleEnabled())
        {
            $vbUser = Mage::getModel('aitvbulletin_vbulletin/user')->load($forumUserId);
            /* @var $vbUser Aitoc_Aitvbulletin_Model_Vbulletin_User */
            if ($vbUser->getId())
            {
                $vbUser->setCustomer($this);
                $this->setAitvbulletinUser($vbUser);
            }
            else 
            {
                $this->setAitvbulletinUserId(0);
            }
        }
        
        return parent::_afterLoad();
    }
    
    /**
     * Load customer by vbulletin user id
     *
     * @param   integer $forumUserId
     * @param   integer $storeId
     * @return  Aitoc_Aitvbulletin_Model_Rewrite_Customer
     */
    public function loadByForumUserId($forumUserId, $storeId)
    {
        $collection = $this->getCollection();
        /* @var $collection Mage_Customer_Model_Entity_Customer_Collection */
        
        $collection
            ->addAttributeToFilter('aitvbulletin_user_id', array('eq' => $forumUserId))
            ;
        
        if ($data = $collection->getFirstItem())
        {
            $this->load($data->getId());
        }
        else 
        {
            $this->reset();
        }
            
        return $this;
    }
    
}