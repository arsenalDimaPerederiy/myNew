<?php

class Qaz_Qbanner_Adminhtml_QbannerController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('qbanner')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Banners Manager'), Mage::helper('adminhtml')->__('Banner Manager'));

        return $this;
    }

    public function indexAction() {
        $this->_initAction()
                ->renderLayout();
    }

    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('qbanner/qbanner')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            $showIn = $model->getShowIn();
            if (strlen($showIn) > 0)
                $model->setShowIn(explode(",", $showIn));
            Mage::register('qbanner_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('qbanner');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Banner Manager'), Mage::helper('adminhtml')->__('Banner Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Banner News'), Mage::helper('adminhtml')->__('Banner News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('qbanner/adminhtml_qbanner_edit'))
                    ->_addLeft($this->getLayout()->createBlock('qbanner/adminhtml_qbanner_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('qbanner')->__('Banner does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('qbanner/qbanner');

            if (isset($data['categories'])) {
                $data['categories'] = explode(',', $data['categories']);
                if (is_array($data['categories'])) {
                    $data['categories'] = array_unique($data['categories']);
                }
            }
            if (isset($data['show_in']) and sizeof($data['show_in']) > 0) {
                $data['show_in'] = implode(",", $data['show_in']);
            } else {
                $data['show_in'] = 'no_where';
            }

            $model->setData($data)
                    ->setId($this->getRequest()->getParam('id'));
            try {
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('qbanner')->__('Banner was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('qbanner')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('qbanner/qbanner');

                $model->setId($this->getRequest()->getParam('id'))
                        ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Banner was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction() {
        $qbannerIds = $this->getRequest()->getParam('qbanner');
        if (!is_array($qbannerIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($qbannerIds as $qbannerId) {
                    $qbanner = Mage::getModel('qbanner/qbanner')->load($qbannerId);
                    $qbanner->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__(
                                'Total of %d record(s) were successfully deleted', count($qbannerIds)
                        )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction() {
        $qbannerIds = $this->getRequest()->getParam('qbanner');
        if (!is_array($qbannerIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($qbannerIds as $qbannerId) {
                    $qbanner = Mage::getSingleton('qbanner/qbanner')
                            ->load($qbannerId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) were successfully updated', count($qbannerIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function exportCsvAction() {
        $fileName = 'qbanner.csv';
        $content = $this->getLayout()->createBlock('qbanner/adminhtml_qbanner_grid')
                ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        $fileName = 'qbanner.xml';
        $content = $this->getLayout()->createBlock('qbanner/adminhtml_qbanner_grid')
                ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream') {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }

    public function uploadAction() {
        try {
            $uploader = new Mage_Core_Model_File_Uploader('image');
            $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $result = $uploader->save(
                            Mage::getSingleton('qbanner/config')->getBaseTmpMediaPath()
            );
            /**
             * Workaround for prototype 1.7 methods "isJSON", "evalJSON" on Windows OS
             */
            $result['tmp_name'] = str_replace(DS, "/", $result['tmp_name']);
            $result['path'] = str_replace(DS, "/", $result['path']);

            $result['url'] = Mage::getSingleton('qbanner/config')->getTmpMediaUrl($result['file']);
            $result['cookie'] = array(
                'name' => session_name(),
                'value' => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path' => $this->_getSession()->getCookiePath(),
                'domain' => $this->_getSession()->getCookieDomain()
            );
        } catch (Exception $e) {
            $result = array(
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode());
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
	public function categoriesJsonAction()
    {
        $bannerId     = $this->getRequest()->getParam('id');
        $_model  = Mage::getModel('qbanner/qbanner')->load($bannerId);
        Mage::register('qbanner_data', $_model);
        Mage::register('current_qbanner', $_model);

        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('qbanner/adminhtml_qbanner_edit_tab_category')
                ->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('admin/qbanner');
    }

}