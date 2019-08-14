<?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/

class Aitoc_Aitvbulletin_AccountController extends Mage_Core_Controller_Front_Action
{
    /**
     * Retrieve customer session model object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     */
    public function preDispatch()
    {
        parent::preDispatch();

        if (!$this->getRequest()->isDispatched()) 
        {
            return;
        }

        if (!$this->_getSession()->authenticate($this)) 
        {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
        
        return $this;
    }
    
    public function forumAction()
    {
        if (!Mage::helper('aitvbulletin')->isModuleEnabled()) 
        {
            return $this->_forward('noRoute');
        }
        
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        
        // original customer info
        $customer = $this->_getSession()->getCustomer(); 
        /* @var $customer Aitoc_Aitvbulletin_Model_Rewrite_FrontCustomer */
        
        $savedForumUserId = $customer->getAitvbulletinUserId();
        
        // posted values, they were set in forumVerifyPostAction on error
        $data = $this->_getSession()->getCustomerFormData(true);
        if (!empty($data)) 
        {
            $customer->addData($data);
        }
        
        $this->getLayout()->getBlock('head')->setTitle($this->__('Forum Integration'));
        
        $this->renderLayout();
    }

    public function forumVerifyPostAction()
    {
        if (!Mage::helper('aitvbulletin')->isModuleEnabled()) 
        {
            return $this->_forward('noRoute');
        }
        
        $sRedirRoute = '*/*/forum';

        if (!$this->_validateFormKey() OR !$this->getRequest()->isPost()) 
        {
            return $this->_redirect($sRedirRoute);
        }
        
        $customer = $this->_getSession()->getCustomer();
        /* @var $customer Aitoc_Aitvbulletin_Model_Rewrite_FrontCustomer */
        
        $savedForumUserId = $this->_getSession()->getCustomer()->getAitvbulletinUserId();
        
        if ($savedForumUserId)
        {
            return $this->_redirect($sRedirRoute);
        }
        // validate posted values
        
        $forumUser = Mage::getModel('aitvbulletin_vbulletin/user')
            ->setUsername(trim($this->getRequest()->getPost('verify_username')))
            ->setPassword($this->getRequest()->getPost('verify_password'))
            ;
        /* @var $forumUser Aitoc_Aitvbulletin_Model_Vbulletin_User */
        
        $validate = $forumUser->preverify();
        
        if ($validate === true) 
        {
            try 
            {
                $forumUser->verify();
                if ($forumUser->getUserid())
                {
                    $customer->setData('aitvbulletin_user_id', $forumUser->getUserid());
                    
                    $customer->save();
                    $this->_getSession()->setCustomer($customer);
                    
                    $this->_getSession()->addSuccess(Mage::helper('aitvbulletin')->__('You have successfully confirmed your Forum Account'));
                }
                else 
                {
                    $this->_getSession()
                        ->setCustomerFormData($this->getRequest()->getPost())
                        ->addError(Mage::helper('aitvbulletin')->__('Invalid Username or Password.'));
                }
            }
            catch (Mage_Core_Exception $e) 
            {
                $this->_getSession()
                    ->setCustomerFormData($this->getRequest()->getPost())
                    ->addError($e->getMessage());
            }
            catch (Exception $e) {
                $this->_getSession()
                    ->setCustomerFormData($this->getRequest()->getPost())
                    ->addException($e, Mage::helper('aitvbulletin')->__('Can\'t verify forum account'));
            }
        }
        else
        {
            $this->_getSession()->setCustomerFormData($this->getRequest()->getPost());
            
            if (is_array($validate)) 
            {
                foreach ($validate as $errorMessage) 
                {
                    $this->_getSession()->addError($errorMessage);
                }
            }
            else 
            {
                $this->_getSession()->addError(Mage::helper('aitvbulletin')->__('Unable to verify forum account. Please, try again later.'));
            }
        }
        
        return $this->_redirect($sRedirRoute);
    }
    
}
