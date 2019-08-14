<?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/

class Aitoc_Aitvbulletin_Block_Admin_News extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('aitvbulletin/aitoc_news.phtml');
    }
    
    public function getNews()
    {
        return Mage::getModel('aitvbulletin/notification_news')
            ->loadData()
            ->getData();
    }

}