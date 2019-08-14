<?php

class Aitoc_Aitvbulletin_Model_Notification_Observer extends Mage_Core_Model_Abstract
{
    /**
     * adminhtml: controller_action_predispatch
     *
     * @param Varien_Event_Observer $observer
     */
    public function performPreDispatch( Varien_Event_Observer $observer )
    {
        $news = Mage::getModel('aitvbulletin/notification_news');
        /* @var $news Aitoc_Aitvbulletin_Model_Notification_News */
        $news->loadData();
        
        $note = Mage::getModel('aitvbulletin/notification_notifications');
        /* @var $note Aitoc_Aitvbulletin_Model_Notification_Notifications */
        $note->loadData();
    }
    
}