<?php

abstract class Aitoc_Aitvbulletin_Model_Notification_Abstract extends Mage_Core_Model_Abstract
{
    const SERVICE_URL = 'http://aitoc.com/api/xmlrpc';
    
    protected $_cacheKey = '';
    protected $_serviceMethod = '';
    
    /**
     * @return Aitoc_Aitvbulletin_Model_Notification_Abstract
     */
    public function loadData()
    {
        if (!$this->_cacheKey OR !$this->_serviceMethod)
        {
            return $this;
        }
        
        $data = Mage::app()->loadCache($this->_cacheKey);
        if ($data)
        {
            $this->setData(unserialize($data));
        }
        else
        {
            $service = Mage::getModel('aitvbulletin/notification_service');
            /* @var $service Aitoc_Aitvbulletin_Model_Notification_Service */
            try
            {
                $service
                    ->setServiceUrl(self::SERVICE_URL)
                    ->connect();
                $this->setData($service->{$this->_serviceMethod}(array()));
                $service->disconnect();
            }
            catch (Exception $e)
            {
                /*
                $this->setData(array(
                    array(
                        'title'   => 'Exception',
                        'content' => 'Exception: '.$e->getMessage(), 
                    )
                ));
                */
                $this->setData(array());
            }
            $this->saveData();
            $this->saveCache($this->getData(), $this->_cacheKey);
        }
        
        return $this;
    }
    
    /**
     * @return Aitoc_Aitvbulletin_Model_Notification_Abstract
     */
    public function saveData()
    {
        return $this;
    }
    
    public function saveCache()
    {
        if ($this->_cacheKey)
        {
            Mage::app()->saveCache(serialize($this->getData()), $this->_cacheKey, array(), 3600*12);
        }
        
        return $this;
    }
    
}