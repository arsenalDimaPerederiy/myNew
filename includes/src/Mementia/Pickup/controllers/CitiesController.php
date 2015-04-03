<?php
class Mementia_Pickup_CitiesController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Sales'))->_title($this->__('Store Pickup Cities'));

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('store_pickup/adminhtml_cities'))
            ->renderLayout();

        return $this;
    }



    /**
     * Initialize action
     *
     * @return Mementia_Pickup_CitiesController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/store_pickup/cities')
            ->_addBreadcrumb($this->__('Sales'), $this->__('Sales'))
            ->_addBreadcrumb($this->__('Store Pickup Cities'), $this->__('Store Pickup Cities'))
        ;
        return $this;
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {

        $id = $this->getRequest()->getParam('id', null);
        $model = Mage::getModel('store_pickup/city');
        if ($id) {
            $model->load((int) $id);
            if ($model->getId()) {
                $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
                if ($data) {
                    $model->setData($data)->setId($id);
                }
            } else {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('store_pickup')->__('City does not exist'));
                $this->_redirect('*/*/');
            }
        }
        Mage::register('store_pickup_city', $model);

        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('store_pickup/adminhtml_cities_edit'));

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->renderLayout();
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost())
        {
            $model = Mage::getModel('store_pickup/city');
            $id = $this->getRequest()->getParam('id');
            if (!is_null($id)) {
                $model->load($id);
            }
            $model->setNameRu($this->getRequest()->getParam('name_ru'));
            $model->setNameUa($this->getRequest()->getParam('name_ua',""));
            Mage::getSingleton('adminhtml/session')->setFormData($data);
            try {

                $model->save();
                if (!$model->getId()) {
                    Mage::throwException(Mage::helper('store_pickup')->__('Error saving city'));
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('store_pickup')->__('City was successfully saved.'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                // The following line decides if it is a "save" or "save and continue"
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                } else {
                    $this->_redirect('*/*/');
                }

            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                if ($model && $model->getId()) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
            }

            return;
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('store_pickup')->__('No data found to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('store_pickup/city');
                $model->setId($id);
                $model->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('store_pickup')->__('The city has been deleted.'));
                $this->_redirect('*/*/');
                return;
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Unable to find the city to delete.'));
        $this->_redirect('*/*/');
    }
}