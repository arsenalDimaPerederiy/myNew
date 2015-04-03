<?php

/**
 * Iksanika llc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.iksanika.com/products/IKS-LICENSE.txt
 *
 * @category   Iksanika
 * @package    Iksanika_Ordermanage
 * @copyright  Copyright (c) 2013 Iksanika llc. (http://www.iksanika.com)
 * @license    http://www.iksanika.com/products/IKS-LICENSE.txt
 */

include_once "Mage/Adminhtml/controllers/Sales/OrderController.php";

class Iksanika_Ordermanage_Sales_OrderController 
    extends Mage_Adminhtml_Sales_OrderController
{
    
    protected function _construct()
    {
        $this->setUsedModuleName('Iksanika_Ordermanage');
    }
    
    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('sales/ordermanage');
        $this->_addContent($this->getLayout()->createBlock('ordermanage/sales_order'));
        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('ordermanage/sales_order_grid')->toHtml()
        );
    }
/*   
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/products');
    }
*/

    public function massUpdateOrdersAction()
    {
        $orderIds = $this->getRequest()->getParam('order_ids');

        if (is_array($orderIds)) 
        {
            try {
                
                foreach ($orderIds as $itemId => $orderId) 
                {
                    $order = Mage::getModel('sales/order')->load($orderId);
                    
                    // event was not dispached by some reasons ??? so the code to prove product is below
                    // if ($this->massactionEventDispatchEnabled)
                    //    Mage::dispatchEvent('catalog_product_prepare_save', array('product' => $order, 'request' => $this->getRequest()));
                    
                    $columnForUpdate = Iksanika_Ordermanage_Block_Sales_Order_Grid::getColumnForUpdate();
//                    $columnForUpdateFlip = array_flip($columnForUpdate);
//                    var_dump($columnForUpdate);
//                    var_dump($orderIds);
                    
                    $billingDataFlag = false;
                    $billingAddress = null;
                    $shippingDataFlag = false;
                    $shippingAddress = null;
                    
                    foreach($columnForUpdate as $columnName)
                    {
                        $columnValuesForUpdate = $this->getRequest()->getParam($columnName);
                        
                        if($columnName == 'billing_firstname' || 
                           $columnName == 'billing_middlename' || 
                           $columnName == 'billing_lastname' || 
                           $columnName == 'billing_company' ||
                           $columnName == 'billing_city' || 
                           $columnName == 'billing_region' || 
                           $columnName == 'billing_postcode' || 
                           $columnName == 'billing_email' || 
                           $columnName == 'billing_telephone' || 
                           $columnName == 'billing_fax' ||
                                
                           $columnName == 'billing_street' ||
                           $columnName == 'billing_country'
                        )
                        {
                            $billingDataFlag = true;
                            
                            list($prefix, $fieldName) = explode('_', $columnName);
                            
                            if(!$billingAddress)
                                $billingAddress = $order->getBillingAddress();
                            
                            if($fieldName == 'street')
                                $billingAddress->setStreet($columnValuesForUpdate[$itemId]);
                            else
                            if($fieldName == 'country')
                            {
                                $billingAddress->setCountryId($columnValuesForUpdate[$itemId]);
                            }
                            else
                                $billingAddress->setData($fieldName, $columnValuesForUpdate[$itemId]);
                        }else
                        if($columnName == 'shipping_firstname' || 
                           $columnName == 'shipping_middlename' || 
                           $columnName == 'shipping_lastname' || 
                           $columnName == 'shipping_company' ||
                           $columnName == 'shipping_city' || 
                           $columnName == 'shipping_region' || 
                           $columnName == 'shipping_postcode' || 
                           $columnName == 'shipping_email' || 
                           $columnName == 'shipping_telephone' || 
                           $columnName == 'shipping_fax' ||
                                
                           $columnName == 'shipping_street' ||
                           $columnName == 'shipping_country'
                        )
                        {
                            $shippingDataFlag = true;
                            
                            list($prefix, $fieldName) = explode('_', $columnName);
                            
                            if(!$shippingAddress)
                                $shippingAddress = $order->getShippingAddress();
                            
                            if($fieldName == 'street')
                                $shippingAddress->setStreet($columnValuesForUpdate[$itemId]);
                            else
                            if($fieldName == 'country')
                            {
                                $shippingAddress->setCountryId($columnValuesForUpdate[$itemId]);
                            }
                            else
                                $shippingAddress->setData($fieldName, $columnValuesForUpdate[$itemId]);
                        }
                        
                        $order->$columnName =  $columnValuesForUpdate[$itemId];
                        //echo $columnName.' = '.$columnValuesForUpdate.'['.$itemId.'] => '.$columnValuesForUpdate[$itemId].'<br/>';
                    }
                    
                    // save billing address changes if exist
                    if($billingDataFlag)
                        $billingAddress->save();
                    
                    // save shipping address changes if exist
                    if($shippingDataFlag)
                        $shippingAddress->save();
                    
                    // save order changes
                    $order->save();
                }
                $this->_getSession()->addSuccess($this->__('Total of %d record(s) were successfully refreshed.', count($orderIds)));
            } catch (Exception $e) 
            {
                $this->_getSession()->addError($e->getMessage());
            }
        }else
        {
            $this->_getSession()->addError($this->__('Please select product(s)').'. '.$this->__('You should select checkboxes for each product row which should be updated. You can click on checkboxes or use CTRL+Click on product row which should be selected.'));
        }
        $this->_redirect('*/*/index');
    }
    
    
    
    public function _initInvoice($order)
    {
        /**
        * Check invoice create availability
        */
        if (!$order->canInvoice()) 
        {
            $order->addStatusHistoryComment('Order cannot be invoiced.', false);
            $order->save();
            return null;
        }
        $savedQtys = $this->_getItemQtys($order);

        $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice($savedQtys);
        if (!$invoice->getTotalQty())
        {
            $order->addStatusHistoryComment('Order cannot be invoiced: total products quantity is zero.', false);
            $order->save();
            return null;
        }
        return $invoice;
    }

    
    public function _initShipment($order, $itemId = 0)
    {
        /**
        * Check order existing
        */
        if (!$order->getId()) 
        {
            $order->addStatusHistoryComment($this->__('The order no longer exists.'), false);
            $order->save();
            continue;
        }
        /**
        * Check shipment is available to create separate from invoice
        */
        if ($order->getForcedDoShipmentWithInvoice()) 
        {
            $order->addStatusHistoryComment($this->__('Cannot do shipment for the order separately from invoice.'), false);
            $order->save();
            continue;
        }
        /**
        * Check shipment create availability
        */
        if (!$order->canShip()) 
        {
            $order->addStatusHistoryComment($this->__('Cannot do shipment for the order.'), false);
            $order->save();
            continue;
        }

        $savedQtys = $this->_getItemQtys($order);

        $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($savedQtys);
        if (!$shipment->getTotalQty())
        {
            $order->addStatusHistoryComment('Order cannot be invoiced: total products quantity is zero.', false);
            $order->save();
            continue;
        }
        
        $trackNumber            =   $this->getRequest()->getParam('shipping_tracking_number');
        $trackNumberCarrierCode =   $this->getRequest()->getParam('shipping_tracking_number_carrier');
        
        if(!$trackNumberCarrierCode)
        {
            $trackItem = array(
                'carrier_code'  =>  Mage::getStoreConfig('ordermanage/ship/carrier_code'),
                'title'         =>  (Mage::getStoreConfig('ordermanage/ship/carrier_code') == 'custom') ? Mage::getStoreConfig('ordermanage/ship/carrier_title') : Mage::getStoreConfig("carriers/".Mage::getStoreConfig('ordermanage/ship/carrier_code')."/title"),
                'number'        =>  ' ',
            );
        }else
        {
            $trackItem = array(
                'carrier_code' => $trackNumberCarrierCode[$itemId],
                'title' => ($trackNumberCarrierCode[$itemId] == 'custom') ? Mage::getStoreConfig('ordermanage/ship/carrier_title') : Mage::getStoreConfig("carriers/".$trackNumberCarrierCode[$itemId]."/title"),
                'number' => ($trackNumber ? $trackNumber[$itemId] : ' '),
            );
        }
/*        
        $trackItem = array(
            'carrier_code' => (($trackNumberCarrierCode || $trackNumberCarrierCode[0] == 'custom') ? Mage::getStoreConfig('ordermanage/ship/carrier_code') : $trackNumberCarrierCode[0]),
            // Mage::getStoreConfig('ordermanage/ship/carrier_code') == 'custom'
            'title' => (($trackNumberCarrierCode || $trackNumberCarrierCode[0] == 'custom') ? Mage::getStoreConfig('ordermanage/ship/carrier_title') : Mage::getStoreConfig("carriers/".$trackNumberCarrierCode[0]."/title")),
            'number' => ($trackNumber ? $trackNumber[0] : ' '),
        );
*/        
//        echo Mage::getStoreConfig('ordermanage/ship/carrier_title');
//        echo '<pre>';
//        var_dump($trackItem);
        $track = Mage::getModel('sales/order_shipment_track')->addData($trackItem);
//        var_dump($track);
//        echo '</pre>';
//        die();
        $shipment->addTrack($track);
        
        return $shipment;
    }

    
    

    /**
     * Prepare shipment
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @return Mage_Sales_Model_Order_Shipment
     */
    protected function _prepareShipment($invoice, $itemId = 0)
    {
        $savedQtys = $this->_getItemQtys($invoice->getOrder());
        $shipment = Mage::getModel('sales/service_order', $invoice->getOrder())->prepareShipment($savedQtys);
        if (!$shipment->getTotalQty())
        {
            return false;
        }
        
        $shipment->register();
        
        $trackNumber            =   $this->getRequest()->getParam('shipping_tracking_number');
        $trackNumberCarrierCode =   $this->getRequest()->getParam('shipping_tracking_number_carrier');
        
        if(!$trackNumberCarrierCode)
        {
            $trackItem = array(
                'carrier_code'  =>  Mage::getStoreConfig('ordermanage/ship/carrier_code'),
                'title'         =>  (Mage::getStoreConfig('ordermanage/ship/carrier_code') == 'custom') ? Mage::getStoreConfig('ordermanage/ship/carrier_title') : Mage::getStoreConfig("carriers/".Mage::getStoreConfig('ordermanage/ship/carrier_code')."/title"),
                'number'        =>  ' ',
            );
        }else
        {
            $trackItem = array(
                'carrier_code' => $trackNumberCarrierCode[$itemId],
                'title' => ($trackNumberCarrierCode[$itemId] == 'custom') ? Mage::getStoreConfig('ordermanage/ship/carrier_title') : Mage::getStoreConfig("carriers/".$trackNumberCarrierCode[$itemId]."/title"),
                'number' => ($trackNumber ? $trackNumber[$itemId] : ' '),
            );
        }
        
        /*
        $trackItem = array(
                'carrier_code' => Mage::getStoreConfig('ordermanage/ship/carrier_code'), 
                'title' => (Mage::getStoreConfig('ordermanage/ship/carrier_code') == 'custom') ? Mage::getStoreConfig('ordermanage/ship/carrier_title') : Mage::getStoreConfig("carriers/".Mage::getStoreConfig('ordermanage/ship/carrier_code')."/title"),
                'number' => ($trackNumber) ? $trackNumber : ' ',
            );
         */
        $track = Mage::getModel('sales/order_shipment_track')->addData($trackItem);
        $shipment->addTrack($track);
        
        return $shipment;
    }
    
    
    

    
    public function massInvoiceCaptureAction()
    {
        $proceedCapture = $this->getRequest()->getParam('proceedCapture');
        $proceedShipment = $this->getRequest()->getParam('proceedShipment');
        $orderIds = $this->getRequest()->getParam('order_ids');
        if (is_array($orderIds)) 
        {
            try {
                foreach ($orderIds as $itemId => $orderId) 
                {
                    $order = Mage::getModel('sales/order')->load($orderId);
                    
                    $invoice = $this->_initInvoice($order);
                    if(!$invoice) continue;
                    
                    ////////////////////////////////////////////////////////////
                    
                    if($proceedCapture)
                    {
                        if (Mage::getStoreConfig('ordermanage/capture/case') != Mage_Sales_Model_Order_Invoice::NOT_CAPTURE)
                        {
                            $invoice->setRequestedCaptureCase(Mage::getStoreConfig('ordermanage/capture/case'));
                        }
                    }
                    
                    if (Mage::getStoreConfig('ordermanage/invoice/notify'))
                    {
                        $invoice->addComment(
                            'Mass Invoice Proceeding',
                            Mage::getStoreConfig('ordermanage/capture/case'),
                            Mage::getStoreConfig('ordermanage/capture/case')
                        );
                    }

                    $invoice->register();

                    if (Mage::getStoreConfig('ordermanage/invoice/notify')) 
                    {
                        $invoice->setEmailSent(true);
                    }
                    
                    $invoice->getOrder()->setCustomerNoteNotify(Mage::getStoreConfig('ordermanage/invoice/notify'));
                    //$invoice->getOrder()->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
                    $invoice->getOrder()->setIsInProcess(true);
                    if(Mage::getStoreConfig('ordermanage/invoice/status') != 'default')
                    {
                        $invoice->getOrder()->setStatus(Mage::getStoreConfig('ordermanage/invoice/status'));
                    }

                    $transactionSave = Mage::getModel('core/resource_transaction')
                        ->addObject($invoice)
                        ->addObject($invoice->getOrder());
                  
                    $shipment = false;
                    if ($proceedShipment || (int) $invoice->getOrder()->getForcedDoShipmentWithInvoice()) 
                    {
                        $shipment = $this->_prepareShipment($invoice, $itemId);
                        if ($shipment) 
                        {
                            $shipment->setEmailSent($invoice->getEmailSent());
                            $transactionSave->addObject($shipment);
                        }
                    }

                    $transactionSave->save();

                    if ($proceedShipment) 
                    {
                        $order->addStatusHistoryComment($this->__('The invoice and shipment have been created.'), false);
                        $order->save();
                        //$this->_getSession()->addSuccess($this->__('The invoice and shipment have been created.'));
                    } else 
                    {
                        $order->addStatusHistoryComment($this->__('The invoice has been created.'), false);
                        $order->save();
                        //$this->_getSession()->addSuccess($this->__('The invoice has been created.'));
                    }

                    // send invoice/shipment emails

                    try {
                        //$invoice->sendEmail(Mage::getStoreConfig('ordermanage/invoice/notify'), 'Email has been send.');
                        $invoice->sendEmail(Mage::getStoreConfig('ordermanage/invoice/notify'));
                    } catch (Exception $e) 
                    {
                        Mage::logException($e);
                        $order->addStatusHistoryComment($this->__('Unable to send the invoice email.'), false);
                        $order->save();
                    }


                    if ($shipment) 
                    {
                        try {
                            $shipment->sendEmail(Mage::getStoreConfig('ordermanage/ship/notify'));
                        } catch (Exception $e) 
                        {
                            Mage::logException($e);
                            $order->addStatusHistoryComment($this->__('Unable to send the invoice email.'), false);
                            $order->save();
                            //$this->_getSession()->addError($this->__('Unable to send the shipment email.'));
                        }
                    }

                }
                $this->_getSession()->addSuccess($this->__('Total of %d record(s) were successfully updated.', count($orderIds)));
            } catch (Exception $e) 
            {
                $this->_getSession()->addError($e->getMessage());
            }
        }else
        {
            $this->_getSession()->addError($this->__('Please select product(s)').'. '.$this->__('You should select checkboxes for each product row which should be updated. You can click on checkboxes or use CTRL+Click on product row which should be selected.'));
        }
        $this->_redirect('*/*/index');
    }
    
    
    
    public function massShipAction()
    {
        $orderIds               =   $this->getRequest()->getParam('order_ids');
        if (is_array($orderIds)) 
        {
            try {
                
                foreach ($orderIds as $itemId => $orderId) 
                {
                    $order = Mage::getModel('sales/order')->load($orderId);
                    
                    $shipment = $this->_initShipment($order, $itemId);
                    if(!$shipment) continue;

//var_dump($_POST);                    
//var_dump($orderIds);
//die();
                   
                    ////////////////////////////////////////////////////////////
                    
                    $shipment->register();
                    /*
                    $comment = '';
                    if (!empty($data['comment_text'])) {
                        $shipment->addComment(
                            $data['comment_text'],
                            isset($data['comment_customer_notify']),
                            isset($data['is_visible_on_front'])
                        );
                        if (isset($data['comment_customer_notify'])) {
                            $comment = $data['comment_text'];
                        }
                    }*/

                    if (Mage::getStoreConfig('ordermanage/invoice/notify')) 
                    {
                        $shipment->setEmailSent(true);
                    }

                    $shipment->getOrder()->setCustomerNoteNotify(Mage::getStoreConfig('ordermanage/invoice/notify'));
/*                    $responseAjax = new Varien_Object();
                    $isNeedCreateLabel = isset($data['create_shipping_label']) && $data['create_shipping_label'];

                    if ($isNeedCreateLabel && $this->_createShippingLabel($shipment)) {
                        $responseAjax->setOk(true);
                    }*/

                    $shipment->getOrder()->setIsInProcess(true);
                    if(Mage::getStoreConfig('ordermanage/ship/status') != 'default')
                    {
                        $shipment->getOrder()->setStatus(Mage::getStoreConfig('ordermanage/ship/status'));
                    }
                    
                    $transactionSave = Mage::getModel('core/resource_transaction')
                        ->addObject($shipment)
                        ->addObject($shipment->getOrder())
                        ->save();

                    $shipment->sendEmail(Mage::getStoreConfig('ordermanage/invoice/notify'));

                }
                $this->_getSession()->addSuccess($this->__('Total of %d record(s) were successfully updated.', count($orderIds)));
            } catch (Exception $e) 
            {
                $this->_getSession()->addError($e->getMessage());
            }
        }else
        {
            $this->_getSession()->addError($this->__('Please select product(s)').'. '.$this->__('You should select checkboxes for each product row which should be updated. You can click on checkboxes or use CTRL+Click on product row which should be selected.'));
        }
        $this->_redirect('*/*/index');
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    /**
     * Get requested items qty's from request
     */
    protected function _getItemQtys($order)
    {
        $qtys = array();
        $orderItemsList = $order->getAllItems();
        foreach($orderItemsList as $orderItem)
        {
            $qtys[$orderItem->getItemId()] = $orderItem->getQtyOrdered();
        }
        return $qtys;
    }

    /**
     * Initialize invoice model instance
     *
     * @return Mage_Sales_Model_Order_Invoice
     */
    protected function __initInvoice($update = false)
    {
//        $this->_title($this->__('Sales'))->_title($this->__('Invoices'));

        $invoice = false;
        $itemsToInvoice = 0;
//        $invoiceId = $this->getRequest()->getParam('invoice_id');
        
        // TODO: get from list of order - specify order_id insteed getting through get request
        $orderId = $this->getRequest()->getParam('order_id');
        if ($orderId) 
        {
            $order = Mage::getModel('sales/order')->load($orderId);
            /**
             * Check order existing
             */
            if (!$order->getId()) 
            {
 //               $this->_getSession()->addError($this->__('The order no longer exists.'));
                //TODO: exit from function or continue loop
                return false;
            }
            /**
             * Check invoice create availability
             */
            if (!$order->canInvoice()) 
            {
//                $this->_getSession()->addError($this->__('The order does not allow creating an invoice.'));
                //TODO: exit from function or continue loop
                con;
            }
            
            $savedQtys = $this->_getItemQtys($order);
            
            
            
            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice($savedQtys);
            if (!$invoice->getTotalQty()) 
            {
                //TODO: exit from function or contiue of skip this loop
                Mage::throwException($this->__('Cannot create an invoice without products.'));
            }
        }

        Mage::register('current_invoice', $invoice);
        return $invoice;
    }

    /**
     * Save data for invoice and related order
     *
     * @param   Mage_Sales_Model_Order_Invoice $invoice
     * @return  Mage_Adminhtml_Sales_Order_InvoiceController
     */
    protected function _saveInvoice($invoice)
    {
        $invoice->getOrder()->setIsInProcess(true);
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($invoice)
            ->addObject($invoice->getOrder())
            ->save();

        return $this;
    }
    
    /**
     * Export order grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName   = 'orders.csv';
        $grid       = $this->getLayout()->createBlock('adminhtml/sales_order_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }
    
    /**
     *  Export order grid to Excel XML format
     */
    public function exportExcelAction()
    {
        $fileName   = 'orders.xml';
        $grid       = $this->getLayout()->createBlock('adminhtml/sales_order_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }
    
}