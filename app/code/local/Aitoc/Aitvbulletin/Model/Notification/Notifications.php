<?php

class Aitoc_Aitvbulletin_Model_Notification_Notifications extends Aitoc_Aitvbulletin_Model_Notification_Abstract
{
    protected $_cacheKey = 'AITOC_NOTIFICATIONS';
    protected $_serviceMethod = 'getNewsNotification';
    protected $_severity = Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE;
    
    /**
     * @return Aitoc_Aitvbulletin_Model_Notification_News
     */
    public function saveData()
    {
        $feedData = array();
        foreach ($this->getData() as $item)
        {
            if ($item AND !empty($item['title']) AND !empty($item['content']))
            {
                $feedData[] = array(
                    'severity'      => isset($item['severity']) ? $item['severity'] : $this->_severity,
                    'date_added'    => isset($item['pubDate']) ? $item['pubDate'] : date('Y-m-d H:i:s') ,
                    'title'         => $item['title'] ,
                    'description'   => $item['content'] ,
                    'url'           => isset($item['link']) ? $item['link'] : 'http://aitoc.com/#'.strtolower(preg_replace('/\W+/','_',$item['title']))
                );
            }
        }
        if ($feedData)
        {
            Mage::getModel('adminnotification/inbox')->parse(array_reverse($feedData));
        }
        
        return $this;
    }
    
}