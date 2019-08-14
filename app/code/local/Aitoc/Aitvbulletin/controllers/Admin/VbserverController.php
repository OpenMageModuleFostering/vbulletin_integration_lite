<?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/

class Aitoc_Aitvbulletin_Admin_VbserverController extends Mage_Adminhtml_Controller_action
{

    protected function _construct()
    {
        $this->setFlag('index', 'no-preDispatch', true);
        return parent::_construct();
    }

    public function indexAction()
    {
        $this->_forward('edit');
    }
    
    public function editAction() 
    {
        $vbserver = $this->_initVbserver();
        $this->loadLayout();
        $this->_setActiveMenu('system/aitvbulletin');
        $this->renderLayout();
    }
    
    public function saveAction() 
    {
        $vbserver = $this->_initVbserver();
        $formData = $this->getRequest()->getPost();
        
        if (!empty($formData))
        {
            try 
            {
                $model = Mage::getModel('aitvbulletin/vbserver')
                    ->addData($formData['vbserver'])
                    ->save();
                
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('aitvbulletin')->__('vBulletin Server Connection was successfully saved'));
            } 
            catch (Exception $e) 
            {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        
        $this->_redirect('*/*/');
    }
    
    public function validateAction()
    {
        $response = new Varien_Object();
        $response->setError(false);
        
        $vbserver = $this->_initVbserver();
        
        try 
        {
            $formData = $this->getRequest()->getPost('vbserver');
            $vbserver->addData($formData);
            $vbserver->validate();
        }
        catch (Aitoc_Aitvbulletin_Model_Exception $e) 
        {
            $response->setError(true);
            $response->setMessage($e->getMessage());
            $response->setField($e->getField());
        }
        catch (Zend_Db_Adapter_Exception $e) 
        {
            $response->setError(true);
            $response->setMessage($e->getMessage());
            $response->setField('vbserver_name');
        }
        catch (Exception $e) 
        {
            $response->setError(true);
            $response->setMessage($e->getMessage());
        }

        $this->getResponse()->setBody($response->toJson());
    }
    
    /**
     * Initialize vbserver
     *
     * @return Aitoc_Aitvbulletin_Model_Vbserver
     */
    protected function _initVbserver()
    {
        $vbserver = Mage::getModel('aitvbulletin/vbserver');

        Mage::register('vbserver', $vbserver);
        
        return $vbserver;
    }

}