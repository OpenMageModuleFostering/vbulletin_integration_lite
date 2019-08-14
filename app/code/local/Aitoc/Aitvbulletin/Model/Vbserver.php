<?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/

class Aitoc_Aitvbulletin_Model_Vbserver extends Varien_Object 
{
    protected function _construct()
    {
        $this->load();
    }
    
    public function load()
    {
        $this->setData(Mage::getStoreConfig('aitvbulletin/vbserver'));
        $this->setOrigData();
        
        return $this;
    }
    
    public function save()
    {
        $_port = (int)$this->getPort();
        if (!$_port)
        {
            $this->setPort('');
        }
        
        $this->setLink(rtrim($this->getLink(), '/') . '/');
        
        foreach($this->getData() as $key => $value)
        {
            Mage::getConfig()->saveConfig('aitvbulletin/vbserver/'.$key, $value);
        }
        
        // reinit configuration
        Mage::getConfig()->reinit();
        Mage::app()->reinitStores();
        
        return $this;
    }
    
    public function delete()
    {
        foreach($this->getData() as $key => $value)
        {
            Mage::getConfig()->saveConfig('aitvbulletin/vbserver/'.$key, null);
        }
        
        // reinit configuration
        Mage::getConfig()->reinit();
        Mage::app()->reinitStores();
        
        return $this;
    }

    public function validate()
    {
        $_errEmpty = '%s can\'t be empty';
        $_errInvld = 'The %s you entered is invalid';

        $helper = Mage::helper('aitvbulletin');
        /* @var $helper Aitoc_Aitvbulletin_Helper_Data */
        $err = new Varien_Object();
        
        do {

            if (!Zend_Validate::is($this->getType(), 'NotEmpty'))
            {
                $err->setMessage('Please select the Database Type')
                    ->setField('vbserver_type');
                break;
            }
            
            if (!Zend_Validate::is($this->getName(), 'NotEmpty'))
            {
                $err->setMessage($helper->__($_errEmpty, 'Database Name'))
                    ->setField('vbserver_name');
                break;
            }
            
            if (!Zend_Validate::is($this->getUser(), 'NotEmpty'))
            {
                $err->setMessage($helper->__($_errEmpty, 'Database User'))
                    ->setField('vbserver_user');
                break;
            }
            
            if (!Zend_Validate::is($this->getHost(), 'NotEmpty'))
            {
                $err->setMessage($helper->__($_errEmpty, 'Database Host'))
                    ->setField('vbserver_host');
                break;
            }
            else if (!Zend_Validate::is($this->getHost(), 'Hostname', array(Zend_Validate_Hostname::ALLOW_ALL, true, true)))
            {
                $err->setMessage($helper->__($_errInvld, 'Database Host'))
                    ->setField('vbserver_host');
                break;
            }
            
            if ($this->getPort() AND !Zend_Validate::is($this->getPort(), 'Digits'))
            {
                $err->setMessage($helper->__($_errInvld, 'Database Port'))
                    ->setField('vbserver_port');
                break;
            }
            
            // try to connect to the vbulletin server
            $vbResource = Mage::getResourceSingleton('aitvbulletin_vbulletin/user')
                ->setConfig($this);
            /* @var $vbResource Aitoc_Aitvbulletin_Model_Vbulletin_Mysql4_Forum */
            
            $_tables_list = $vbResource->getReadConnection()->listTables();
            $_need_tables = array();
            $_entities = Mage::getConfig()->getNode('global/models/aitvbulletin_vbulletin_resource/entities');
            /* @var $_entities Mage_Core_Model_Config_Element */
            foreach($_entities->asArray() as $_entity => $_ent_config)
            {
                $_need_tables[] = $this->getPrefix() . $_ent_config['table'];
            }
            foreach($_need_tables as $_table_name)
            {
                if (!in_array($_table_name, $_tables_list))
                {
                    $err->setMessage($helper->__('Required vBulletin table \'%s\' was not found in the specified database', $_table_name))
                        ->setField('vbserver_name');
                    break 2;
                }
            }
            
            // validate forum link
            if (!Zend_Validate::is($this->getLink(), 'NotEmpty'))
            {
                $err->setMessage($helper->__($_errEmpty, 'Forum Link'))
                    ->setField('vbserver_link');
                break;
            }
            else
            {
                $parsedUrl = parse_url($this->getLink());
                if (!isset($parsedUrl['scheme']) OR !isset($parsedUrl['host'])) 
                {
                    $err->setMessage($helper->__($_errInvld, 'Forum Link'))
                        ->setField('vbserver_link');
                    break;
                }
                
                // try to find our proxy-script
                $this->setLink(rtrim($this->getLink(), '/') . '/');
                $reqFile = $this->getLink() . $helper->getProxyPhpFilename();
                
                $http = Mage::getModel('aitvbulletin/curl');
                /* @var $http Aitoc_Aitvbulletin_Model_Curl */
                $http->write(Zend_Http_Client::GET, $reqFile.'?do=ping', '1.1', array(), '');
                
                try 
                {
                    $aResponce = $http->readJson();
                }
                catch (Exception $e)
                {
                    $err->setMessage($e->getMessage())
                        ->setField('vbserver_link');
                    break;
                }
                if (empty($aResponce['THIS_SCRIPT']) OR 'aitmagentovb' != $aResponce['THIS_SCRIPT'])
                {
                    $err->setMessage($helper->__('%s is not available or vBulletin is turned off by administrator', $reqFile))
                        ->setField('vbserver_link');
                    break;
                }
            }
            
            // validate apikey
            if (!Zend_Validate::is($this->getApikey(), 'NotEmpty'))
            {
                $err->setMessage($helper->__($_errEmpty, 'Module API Key'))
                    ->setField('vbserver_apikey');
                break;
            }
            else 
            {
                // process post request
                $aVars = array(
                    'do' => 'options',
                );
                
                $http = Mage::getModel('aitvbulletin/curl');
                /* @var $http Aitoc_Aitvbulletin_Model_Curl */
                $http->write(Zend_Http_Client::POST, 
                             $vbResource->getProxyUrl(array(), $aVars['do']), 
                             '1.1', 
                             array(), 
                             $helper->mergeRequestVars($aVars, true));
                
                try 
                {
                    $aResponce = $http->readJson();
                }
                catch (Exception $e)
                {
                    $err->setMessage($e->getMessage())
                        ->setField('vbserver_apikey');
                    break;
                }
                if (empty($aResponce['done']) OR $aVars['do'] != $aResponce['done'])
                {
                    $err->setMessage($helper->__('Module API keys don\'t match or Magento domain is not in the vBulletin\'s whitelist'))
                        ->setField('vbserver_apikey');
                    break;
                }
                
                //$vbOptions = $aResponce;
            }
            
        } while (false);
        
        if ($err->getMessage())
        {
            $e = new Aitoc_Aitvbulletin_Model_Exception($err->getMessage());
            $e->setField($err->getField());
            throw $e;
        }
        
        return $this;
    }
    
}