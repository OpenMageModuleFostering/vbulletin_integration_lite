<?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/

class Aitoc_Aitvbulletin_Model_Vbulletin_User extends Aitoc_Aitvbulletin_Model_Vbulletin_Abstract 
{
    protected function _construct()
    {
        $this->_init('aitvbulletin_vbulletin/user');
        parent::_construct();
    }
    
    /**
     * Enter description here...
     *
     * @param string $username
     * @return Aitoc_Aitvbulletin_Model_Vbulletin_User
     */
    public function loadByForumUsername($username)
    {
        $this->getResource()->loadByForumUsername($this, $username);
        return $this;
    }
    
    public function getVbAvatarPath()
    {
        $result = '';
        
        if ($this->getAvatarid())
        {
            $result = $this->getAvatarpath();
        }
        else
        {
            $_vb_options = $this->getResource()->getVbOptions();
            if ($this->getHascustomavatar() AND $_vb_options->getAvatarenabled())
            {
                if ($_vb_options->getUsefileavatar())
                {
                    $result = $_vb_options->getAvatarurl() . '/avatar' . $this->getId() . '_' . $this->getAvatarrevision() . '.gif';
                }
                else
                {
                    $result = 'image.php?' . 'u=' . $this->getId() . '&amp;dateline=' . $this->getAvatardateline();
                }
            }
        }
        if ($result)
        {
            $result = Mage::helper('aitvbulletin')->getForumUrl() . '/' . $result;
        }
        
        return $result;
    }
    
    public function verify()
    {
        // process post request
        $aVars = array(
            'do'            => 'verify',
            'username'      => $this->getUsername(),
            'password_md5'  => md5($this->getPassword()),
        );
        
        $http = Mage::getModel('aitvbulletin/curl');
        /* @var $http Aitoc_Aitvbulletin_Model_Curl */
        $http->write(Zend_Http_Client::POST, 
                     $this->getResource()->getProxyUrl(array(), $aVars['do'].$aVars['username']), 
                     '1.1', 
                     array(), 
                     Mage::helper('aitvbulletin')->mergeRequestVars($aVars, true));
        
        $aResponce = $http->readJson();
        
        if (isset($aResponce['userid']) AND $userId = intval($aResponce['userid']))
        {
            $this->load($userId);
        }
        else 
        {
            $this->setData(array());
        }
        
        return $this;
    }
    
    /**
     * Method to validate before verify()
     *
     * @return mixed
     */
    public function preverify()
    {
        $aErrors = array();

        $helper = Mage::helper('aitvbulletin');

        if (!Zend_Validate::is($this->getUsername(), 'NotEmpty'))
        {
            $aErrors[] = $helper->__('Forum Username can\'t be empty');
        }
        if (!Zend_Validate::is($this->getPassword(), 'NotEmpty'))
        {
            $aErrors[] = $helper->__('Forum Password can\'t be empty');
        }

        if (empty($aErrors)) 
        {
            
            return true;
        }
        
        return $aErrors;
    }
    
}