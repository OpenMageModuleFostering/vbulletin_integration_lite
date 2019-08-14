<?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/

abstract class Aitoc_Aitvbulletin_Model_Vbulletin_Mysql4_Abstract extends Mage_Core_Model_Mysql4_Abstract
{
    const APIKEY_HASH_LIFETIME = 20; // in seconds, see storeHashLifetime()
    
    /**
     * @var Varien_Object
     */
    protected $_vb_config;
    
    /**
     * @var Varien_Object
     */
    protected $_vb_options;
    
    /**
     * vBulletin database adapter
     * Mage_Core_Model_Resource_Type_Db_Pdo_Mysql OR Mage_Core_Model_Resource_Type_Db_Mysqli
     *
     * @var Mage_Core_Model_Resource_Type_Db_Pdo_Mysql
     */
    protected $_vb_adapter;
    
    /**
     * vBulletin database adapter connection
     * Varien_Db_Adapter_Pdo_Mysql OR Varien_Db_Adapter_Mysqli
     *
     * @var Varien_Db_Adapter_Pdo_Mysql
     */
    protected $_vb_adapter_connection;
    
    /**
     * Connection config
     *
     * @var array
     */
    protected $_vb_adapter_connection_config;
    
    protected function _construct()
    {
        $vbserver = Mage::getModel('aitvbulletin/vbserver');
        /* @var $vbserver Aitoc_Aitvbulletin_Model_Vbserver */
        $this->setConfig($vbserver);
    }
    
    /**
     * @param Varien_Object $object
     * @return Aitoc_Aitvbulletin_Model_Vbulletin_Mysql4_Abstract
     */
    public function setConfig(Varien_Object $object)
    {
        $this->_vb_config  = $object;
        
        $this->_vb_adapter = Mage::helper('aitvbulletin')->getVbAdapterInstance($object->getType());
        
        $this->_vb_adapter_connection_config = array(
            'dbname'        => $object->getName(),
            'username'      => $object->getUser(),
            'password'      => $object->getPass(),
            'host'          => $object->getHost(),
            'port'          => $object->getPort(),
        );
        
        return $this;
    }
    
    public function getConfig($key = null)
    {
        if (empty($this->_vb_config))
        {
            $vbserver = Mage::getModel('aitvbulletin/vbserver');
            
            if (!$vbserver->getType())
            {
                throw new Aitoc_Aitvbulletin_Model_Exception(Mage::helper('aitvbulletin')->__('vBulletin Database Connection was not properly configured'));
            }
            
            $this->setConfig($vbserver);
        }
        
        $value = $this->_vb_config;
        
        if (!empty($key))
        {
            $value = $this->_vb_config->getData($key);
        }
        
        return $value;
    }
    
    public function getTable($tableName)
    {
        return $this->getVbTable($tableName, false);
    }
    
    /**
     * Get vBulletin tables prefix
     *
     * @return string
     */
    public function getVbPrefix()
    {
        return $this->getConfig('prefix');
    }
    
    /**
     * Get vBulletin table name with prefix
     *
     * @param string $tableName
     * @return string
     */
    public function getVbTable($tableName, $as_array = true)
    {
        $tableFullName = $this->getVbPrefix() . $tableName;
        if ($as_array)
        {
            return array($tableName => $tableFullName);
        }
        return $tableFullName;
    }
    
    /**
     * Retrieve connection for read data
     * Zend_Db_Adapter_Abstract
     *
     * @return  Varien_Db_Adapter_Pdo_Mysql
     */
    protected function _getReadAdapter()
    {
        if (empty($this->_vb_adapter_connection)) 
        {
            if (!($this->_vb_adapter instanceof Mage_Core_Model_Resource_Type_Abstract) OR !(trim(implode($this->_vb_adapter_connection_config))))
            {
                throw new Aitoc_Aitvbulletin_Model_Exception(Mage::helper('aitvbulletin')->__('vBulletin Database Connection was not properly configured'));
            }
            
            $this->_vb_adapter_connection = $this->_vb_adapter->getConnection($this->_vb_adapter_connection_config);
            
            $this->_connections['read']  = $this->_vb_adapter_connection;
            $this->_connections['write'] = $this->_vb_adapter_connection;
        }
        
        return $this->_vb_adapter_connection;
    }

    /**
     * Retrieve connection for write data
     * Zend_Db_Adapter_Abstract
     *
     * @return  Varien_Db_Adapter_Pdo_Mysql 
     */
    protected function _getWriteAdapter()
    {
        return $this->_getReadAdapter();
    }
    
    /**
     * Get vBulletin options and miscellaneous bitfields
     *
     * @return Varien_Object
     */
    public function getVbOptions()
    {
        if (empty($this->_vb_options))
        {
            $this->_vb_options = new Varien_Object();
            
            // process post request
            $aVars = array(
                'do' => 'options',
            );
            
            $http = Mage::getModel('aitvbulletin/curl');
            /* @var $http Aitoc_Aitvbulletin_Model_Curl */
            $http->write(Zend_Http_Client::POST, 
                         $this->getProxyUrl(array(), $aVars['do']), 
                         '1.1', 
                         array(), 
                         Mage::helper('aitvbulletin')->mergeRequestVars($aVars, true));
            
            $aResponce = $http->readJson();
            
            if (is_array($aResponce))
            {
                $this->_vb_options->setData($aResponce);
            }
            else 
            {
                throw new Aitoc_Aitvbulletin_Model_Exception(Mage::helper('aitvbulletin')->__('Module API keys don\'t match', get_class($this)));
            }
        }
        
        return $this->_vb_options;
    }

    public function getProxyUrl($aVars, $secureSalt)
    {
        $helper = Mage::helper('aitvbulletin');
        /* @var $helper Aitoc_Aitvbulletin_Helper_Data */
        
        $forumLink = $this->getConfig('link');
        
        if (!is_array($aVars))
        {
            $aVars = array($aVars);
        }
        
        $url = $forumLink . $helper->getProxyPhpFilename() . '?';
        
        $aVars['h'] = $this->generateApiHash(false, $secureSalt);
        
        $url .= $helper->mergeRequestVars($aVars, true);
        
        return $url;
    }
    
    public function generateApiHash($urlencode, $secureSalt)
    {
        $apiKey = $this->getConfig('apikey');
        $randomSalt = Mage::helper('aitvbulletin')->generateVbPasswordSalt();
        
        $hash = md5( md5($apiKey.$secureSalt) . $randomSalt ) . ':' . $randomSalt;
        
        // store hash and its lifetime to vbulletin database
        $this->storeHashLifetime($hash);
        
        if ($urlencode)
        {
            $hash = rawurlencode($hash);
        }
        
        return $hash;
    }
    
    /**
     * Save hash and its lifetime to the vBulletin database
     *
     * @param string $hash
     */
    public function storeHashLifetime($hash)
    {
        $validThru = $this->getReadConnection()->fetchOne('SELECT NOW() + INTERVAL '.self::APIKEY_HASH_LIFETIME.' SECOND');
        
        $vbhash = Mage::getModel('aitvbulletin_vbulletin/hash', array('__resource_config__' => $this->getConfig()));
        /* @var $vbhash Aitoc_Aitvbulletin_Model_Vbulletin_Hash */
        $vbhash
            ->setHash($hash)
            ->setValidthru($this->getReadConnection()->convertDateTime($validThru))
            ->setCreatedBy('mage')
            ->save();
        /* @var $vbhash Aitoc_Aitvbulletin_Model_Vbulletin_Hash */
    }

}