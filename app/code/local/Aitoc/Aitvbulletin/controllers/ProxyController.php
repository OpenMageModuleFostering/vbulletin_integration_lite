<?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/
class Aitoc_Aitvbulletin_ProxyController extends Mage_Core_Controller_Front_Action
{

    public function loginAction()
    {
        $helper = Mage::helper('aitvbulletin');
        /* @var $helper Aitoc_Aitvbulletin_Helper_Data */
        
        if ($this->_getSession()->isLoggedIn())
        {
            $helper->outputBlankGif($this->getResponse());
        }
        
        $userId     = (int) $this->getRequest()->getParam('u', 0);
        $secureSalt = $this->getRequest()->getActionName() . $userId;
        
        if (!$userId OR !$this->isValidApikeyHash($secureSalt))
        {
            return $this->_forward('noRoute');
        }

        $customer = Mage::getModel('customer/customer');
        /* @var $customer Aitoc_Aitvbulletin_Model_Rewrite_FrontCustomer */
        
        $customer->loadByForumUserId($userId, Mage::app()->getStore()->getId());
        
        if ($customer->getId())
        {
//header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"'); 
            $this->getResponse()->setHeader('P3P', 'CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
            $this->_getSession()->loginById($customer->getId());
            $helper->outputBlankGif($this->getResponse());
        }
        
        return $this->_forward('noRoute');
    }
    
    public function logoutAction()
    {
        $helper = Mage::helper('aitvbulletin');
        /* @var $helper Aitoc_Aitvbulletin_Helper_Data */
        
        if (!$this->_getSession()->isLoggedIn())
        {
            $helper->outputBlankGif($this->getResponse());
        }
        
        $secureSalt = $this->getRequest()->getActionName();
        
        if (!$this->isValidApikeyHash($secureSalt))
        {
            return $this->_forward('noRoute');
        }

//header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"'); 
        $this->getResponse()->setHeader('P3P', 'CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
        $this->_getSession()->logout();
        $helper->outputBlankGif($this->getResponse());
    }
    
    public function unlinkAction()
    {
        $helper = Mage::helper('aitvbulletin');
        /* @var $helper Aitoc_Aitvbulletin_Helper_Data */
        
        $userId     = (int) $this->getRequest()->getParam('u', 0);
        $secureSalt = $this->getRequest()->getActionName() . $userId;
        
        if (!$userId OR !$this->isValidApikeyHash($secureSalt))
        {
            return $this->_forward('noRoute');
        }

        $collection = Mage::getModel('customer/customer')->getCollection();
        /* @var $collection Mage_Customer_Model_Entity_Customer_Collection */
        $collection
            ->addAttributeToFilter('aitvbulletin_user_id', array('eq' => $userId))
            ;
        
        if ($collection->getSize())
        {
            foreach($collection->getItems() as $customer)
            {
                /* @var $customer Aitoc_Aitvbulletin_Model_Rewrite_FrontCustomer */
                $customer->setAitvbulletinUserId(0);
                $customer->save();
            }
            
            $helper->outputBlankGif($this->getResponse());
        }
        
        return $this->_forward('noRoute');
    }
    
    /**
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }
    
    protected function isValidApikeyHash($secureSalt)
    {
        $hash = $this->getRequest()->getParam('h', '');
        $hash = rawurldecode($hash);
        
        // $randomSalt - random
        // $key = md5( md5($apikey.$secureSalt) . $randomSalt )
        // $hash = "$key:$randomSalt"
        
        $vbserver = Mage::getModel('aitvbulletin/vbserver');
        $apiKey = $vbserver->getApikey();
        
        if (!$hash OR !$secureSalt OR !$apiKey) return false;
        
        $hashArr = explode(':', $hash);
        if (count($hashArr) == 2)
        {
            $thisKey    = $hashArr[0];
            $randomSalt = $hashArr[1];
            $realKey    = md5( md5($apiKey . $secureSalt) . $randomSalt);
            if ($realKey == $thisKey)
            {
                $hashEnt = Mage::getModel('aitvbulletin_vbulletin/hash');
                /* @var $hashEnt Aitoc_Aitvbulletin_Model_Vbulletin_Hash */
                $hashEnt->getResource()->setConfig($vbserver);
                $hashEnt->load($hash, 'hash');
                
                if ($hashEnt->getId()) 
                {
                    $isHashExpired = $hashEnt->getIsExpired();
                    $hashCreatedBy = $hashEnt->getCreatedBy();
                    
                    $hashEnt->delete();
                    
                    if (!$isHashExpired AND 'vbul' == $hashCreatedBy)
                    {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }

}