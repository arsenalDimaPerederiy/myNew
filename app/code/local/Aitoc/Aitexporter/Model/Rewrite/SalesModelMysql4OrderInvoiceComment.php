<?php
/**
 * Orders Export and Import
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitexporter
 * @version      1.2.8
 * @license:     ou1zlIlUK4jGhUJZLohhJ5b8jdvumX7FXHqMPgZHkF
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
class Aitoc_Aitexporter_Model_Rewrite_SalesModelMysql4OrderInvoiceComment extends Mage_Sales_Model_Mysql4_Order_Invoice_Comment
{
	/**
	 * To save correct create and update dates.
	 * 
	 * @override
	 */
	protected function _prepareDataForSave(Mage_Core_Model_Abstract $object)
	{
		if(Mage::registry('current_import'))
		{
            if(version_compare(Mage::getVersion(), '1.6.0.0', '>=')) {
                return Mage_Core_Model_Resource_Db_Abstract::_prepareDataForSave($object);
            } else {
			    return Mage_Core_Model_Mysql4_Abstract::_prepareDataForSave($object);
            }
		}
		else
		{
			return parent::_prepareDataForSave($object);
		}
	}
}