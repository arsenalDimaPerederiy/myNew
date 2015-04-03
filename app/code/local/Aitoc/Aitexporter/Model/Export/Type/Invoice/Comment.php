<?php
/**
 * Orders Export and Import
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitexporter
 * @version      1.2.9
 * @license:     ou1zlIlUK4jGhUJZLohhJ5b8jdvumX7FXHqMPgZHkF
 * @copyright:   Copyright (c) 2015 AITOC, Inc. (http://www.aitoc.com)
 */
class Aitoc_Aitexporter_Model_Export_Type_Invoice_Comment implements Aitoc_Aitexporter_Model_Export_Type_Interface
{
    /**
     * 
     * @param SimpleXMLElement $invoiceXml
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @param Varien_Object $exportConfig
     */
    public function prepareXml(SimpleXMLElement $invoiceXml, Mage_Core_Model_Abstract $invoice, Varien_Object $exportConfig)
    {
        /* @var $invoice Mage_Sales_Model_Order_Invoice */

        $invoiceCommentsXml = $invoiceXml->addChild('comments');

        foreach ($invoice->getCommentsCollection() as $invoiceComment)
        {
            $invoiceCommentXml = $invoiceCommentsXml->addChild('comment');

            foreach ($invoiceComment->getData() as $field => $value)
            {
                $invoiceCommentXml->addChild($field, $value);
            }
        }
    }
}