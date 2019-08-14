<?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/

class Aitoc_Aitvbulletin_Model_Curl extends Varien_Http_Adapter_Curl
{
    const CURL_TIMEOUT_SEC = 30;
    
    public function __construct()
    {
        curl_setopt($this->_getResource(), CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($this->_getResource(), CURLOPT_REFERER, 'http://' . $_SERVER['HTTP_HOST']);
        curl_setopt($this->_getResource(), CURLOPT_TIMEOUT, self::CURL_TIMEOUT_SEC);
    }
    
    public function readJson()
    {
        $result = $this->read();
        
        if ($this->getErrno())
        {
            $err = $this->getError();
            $this->close();
            Mage::throwException($err);
        }
        
        $this->close(); 
        
        if (!$result) return array();
        
        $oResponse = Zend_Http_Response::fromString($result);
        $sResponce = trim($oResponse->getRawBody());
        $aResponce = Zend_Json::decode($sResponce);
        $aResponce['__raw__'] = $sResponce;

        return $aResponce;
    }
    
}