<?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/

class Aitoc_Aitvbulletin_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_vbOptions;

    /**
     * Generate a password salt
     *
     * @return string
     */
    public function generateVbPasswordSalt()
    {
        $hashSalt = '';
        for ($i = 0; $i < 3; $i++)
        {
            $hashSalt .= chr(rand(0x41, 0x5A)); // A..Z
        }
        return $hashSalt;
    }
    
    public function mergeRequestVars($aVars, $urlencode = true, $varskey = '')
    {
        if (!$aVars) return '';
        
        $sReq = '';
        foreach ($aVars as $key => $val) 
        {
            if (is_array($val))
            {
                $key = $varskey ? $varskey.'['.$key.']' : $key;
                $sReq .= '&'.$this->mergeRequestVars($val, $urlencode, $key);
            }
            else 
            {
                $val = ($urlencode ? rawurlencode($val) : $val);
                if ($varskey)
                {
                    $sReq .= '&'.$varskey.'['.$key.']='.$val;
                }
                else 
                {
                    $sReq .= '&'.$key.'='.$val;
                }
            }
        }
        $sReq = substr($sReq, 1); 
        return $sReq;
    }
    
    public function getProxyPhpFilename()
    {
        return 'aitmagentovb.php';
    }
    
    /**
     * Encrypt Password with the VB3 salt routine
     *
     * @param string $pass
     * @param string $salt
     * @return string
     */
    public function hashVbPassword($pass, $salt)
    {
        return md5(md5($pass) .$salt);
    }
    
    /**
     * Get database connection adapter
     * Mage_Core_Model_Resource_Type_Db_Pdo_Mysql
     * or
     * Mage_Core_Model_Resource_Type_Db_Mysqli
     *
     * @param string $dbtype
     * @return Mage_Core_Model_Resource_Type_Db_Pdo_Mysql
     */
    public function getVbAdapterInstance($dbType)
    {
        if (strtolower(substr($dbType, 0, 6)) == 'mysqli')
        {
            $dbType = 'mysqli';
        }
        else
        {
            $dbType = 'pdo_mysql';
        }
        
        $dbAdapterClassName = (string) Mage::getConfig()->getNode()->global->resource->connection->types->{$dbType}->class;
        if ($dbAdapterClassName)
        {
            return new $dbAdapterClassName();
        }
        
        return null;
    }
    
    /**
     * Print transparent GIF 1x1 pixel
     *
     * @param Mage_Core_Controller_Response_Http $response
     */
    public function outputBlankGif($response)
    {
        $sBlankGifBase64 = 'R0lGODlhAQABAID/AP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';
        $sBlankGif = base64_decode($sBlankGifBase64);
        
        $response
            ->setHttpResponseCode(200)
            //->setHeader('Pragma', 'public', true)
            //->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-Type', 'image/gif', true)
            ->setHeader('Content-Transfer-Encoding', 'binary', true)
            ->setHeader('Content-Length', strlen($sBlankGif));
        
        $response->clearBody();
        $response
            ->setBody($sBlankGif)
            ->sendResponse()
            ;
        
        exit(0);
    }
    
    /**
     * Get forum URL for footer link (see layout)
     *
     * @return string
     */
    public function getForumUrl()
    {
        $vbserver = Mage::getModel('aitvbulletin/vbserver');
        
        return $vbserver->getLink();
    }
    /**
     * Get forum title for footer link (see layout)
     *
     * @return string
     */
    public function getForumTitle()
    {
        return Mage::getStoreConfig('aitvbulletin/forum/linktitle');
    }
    /**
     * Get forum label for footer link (see layout)
     *
     * @return string
     */
    public function getForumLabel()
    {
        return $this->getForumTitle();
    }
    
    public function checkConfigFlag($flag)
    {
        $flag = strtolower($flag);
        if (!empty($flag) AND 'false'!==$flag AND '0'!==$flag) 
        {
            return true;
        }
        return false;
    }
    
    public function isVbulletinActive()
    {
        if (empty($this->_vbOptions))
        {
            $vbResource = Mage::getResourceSingleton('aitvbulletin_vbulletin/user');
            /* @var $vbResource Aitoc_Aitvbulletin_Model_Vbulletin_Mysql4_User */
            $this->_vbOptions = $vbResource->getVbOptions();
        }
        
        return $this->checkConfigFlag($this->_vbOptions['bbactive']);
    }
    
    public function isModuleEnabled()
    {
        return Mage::getStoreConfigFlag('aitvbulletin/forum/enabled') AND $this->isVbulletinActive();
    }
    
}