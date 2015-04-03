<?php
class Mementia_Pickup_WarehousesController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Sales'))->_title($this->__('Store Pickup Offices'));

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('store_pickup/adminhtml_warehouses'))
            ->renderLayout();

        return $this;
    }



    /**
     * Initialize action
     *
     * @return Mementia_Pickup_WarehousesController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/store_pickup/warehouses')
            ->_addBreadcrumb($this->__('Sales'), $this->__('Sales'))
            ->_addBreadcrumb($this->__('Store Pickup Offices'), $this->__('Store Pickup Offices'))
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
        $model = Mage::getModel('store_pickup/warehouse');
        if ($id) {
            $model->load((int) $id);
            if ($model->getId()) {
                $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
                if ($data) {
                    $model->setData($data)->setId($id);
                }
            } else {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('store_pickup')->__('Office does not exist'));
                $this->_redirect('*/*/');
            }
        }
        Mage::register('store_pickup_warehouse', $model);

        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('store_pickup/adminhtml_warehouses_edit'));

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->renderLayout();
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost())
        {
            $model = Mage::getModel('store_pickup/warehouse');
            $id = $this->getRequest()->getParam('id');
            if (!is_null($id)) {
                $model->load($id);
            }
            $model->setNameRu($this->getRequest()->getParam('name_ru'));
            $model->setNameUa($this->getRequest()->getParam('name_ua',""));
            $model->setCityId($this->getRequest()->getParam('city_id'));

            Mage::getSingleton('adminhtml/session')->setFormData($data);
            try {
                $model->save();
                if (!$model->getId()) {
                    Mage::throwException(Mage::helper('store_pickup')->__('Error saving office'));
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('store_pickup')->__('Office was successfully saved.'));
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
                $model = Mage::getModel('store_pickup/warehouse');
                $model->setId($id);
                $model->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('store_pickup')->__('The office has been deleted.'));
                $this->_redirect('*/*/');
                return;
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Unable to find the office to delete.'));
        $this->_redirect('*/*/');
    }
}
