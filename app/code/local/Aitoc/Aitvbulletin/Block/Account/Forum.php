<?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/

class Aitoc_Aitvbulletin_Block_Account_Forum extends Mage_Customer_Block_Account_Dashboard
{
    public function getActionUrl()
    {
        return $this->getUrl('*/*/forumVerifyPost');
    }
    
    public function getCustomerAvatarPath()
    {
        $vbUser = $this->getCustomer()->getAitvbulletinUser();
        /* @var $vbUser Aitoc_Aitvbulletin_Model_Vbulletin_User */
        return $vbUser->getVbAvatarPath();
    }
    
    public function getCustomerPostsCount()
    {
        $vbUser = $this->getCustomer()->getAitvbulletinUser();
        /* @var $vbUser Aitoc_Aitvbulletin_Model_Vbulletin_User */
        return $vbUser->getData('posts');
    }
    
    public function getForumForgotUrl()
    {
        return $this->helper('aitvbulletin')->getForumUrl()
             . 'login.php?do=lostpw';
    }
    public function getForumProfileUrl()
    {
        return $this->helper('aitvbulletin')->getForumUrl()
             . 'member.php?u='.$this->getCustomer()->getAitvbulletinUserId();
    }
    
}
