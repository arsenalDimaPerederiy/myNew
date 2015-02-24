<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Dataflow
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Convert csv parser
 *
 * @category   Mage
 * @package    Mage_Dataflow
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Artle_Productimport_Model_Export extends Mage_Dataflow_Model_Convert_Parser_Abstract
{
    protected $_fields;

    protected $_mapfields = array();

    public function parse()
    {
        // fixed for multibyte characters
        setlocale(LC_ALL, Mage::app()->getLocale()->getLocaleCode().'.UTF-8');

        $fDel = $this->getVar('delimiter', ',');
        $fEnc = $this->getVar('enclose', '"');
        if ($fDel == '\t') {
            $fDel = "\t";
        }

        $adapterName   = $this->getVar('adapter', null);
        $adapterMethod = $this->getVar('method', 'saveRow');

        if (!$adapterName || !$adapterMethod) {
            $message = Mage::helper('dataflow')->__('Please declare "adapter" and "method" nodes first.');
            $this->addException($message, Mage_Dataflow_Model_Convert_Exception::FATAL);
            return $this;
        }

        try {
            $adapter = Mage::getModel($adapterName);
        }
        catch (Exception $e) {
            $message = Mage::helper('dataflow')->__('Declared adapter %s was not found.', $adapterName);
            $this->addException($message, Mage_Dataflow_Model_Convert_Exception::FATAL);
            return $this;
        }

        if (!is_callable(array($adapter, $adapterMethod))) {
            $message = Mage::helper('dataflow')->__('Method "%s" not defined in adapter %s.', $adapterMethod, $adapterName);
            $this->addException($message, Mage_Dataflow_Model_Convert_Exception::FATAL);
            return $this;
        }

        $batchModel = $this->getBatchModel();
        $batchIoAdapter = $this->getBatchModel()->getIoAdapter();

        if (Mage::app()->getRequest()->getParam('files')) {
            $file = Mage::app()->getConfig()->getTempVarDir().'/import/'
                . urldecode(Mage::app()->getRequest()->getParam('files'));
            $this->_copy($file);
        }

        $batchIoAdapter->open(false);

        $isFieldNames = $this->getVar('fieldnames', '') == 'true' ? true : false;
        if (!$isFieldNames && is_array($this->getVar('map'))) {
            $fieldNames = $this->getVar('map');
        }
        else {
            $fieldNames = array();
            foreach ($batchIoAdapter->read(true, $fDel, $fEnc) as $v) {
                $fieldNames[$v] = $v;
            }
        }

        $countRows = 0;
        while (($csvData = $batchIoAdapter->read(true, $fDel, $fEnc)) !== false) {
            if (count($csvData) == 1 && $csvData[0] === null) {
                continue;
            }

            $itemData = array();
            $countRows ++; $i = 0;
            foreach ($fieldNames as $field) {
                $itemData[$field] = isset($csvData[$i]) ? $csvData[$i] : null;
                $i ++;
            }

            $batchImportModel = $this->getBatchImportModel()
                ->setId(null)
                ->setBatchId($this->getBatchModel()->getId())
                ->setBatchData($itemData)
                ->setStatus(1)
                ->save();
        }

        $this->addException(Mage::helper('dataflow')->__('Found %d rows.', $countRows));
        $this->addException(Mage::helper('dataflow')->__('Starting %s :: %s', $adapterName, $adapterMethod));

        $batchModel->setParams($this->getVars())
            ->setAdapter($adapterName)
            ->save();

        //$adapter->$adapterMethod();

        return $this;

//        // fix for field mapping
//        if ($mapfields = $this->getProfile()->getDataflowProfile()) {
//            $this->_mapfields = array_values($mapfields['gui_data']['map'][$mapfields['entity_type']]['db']);
//        } // end
//
//        if (!$this->getVar('fieldnames') && !$this->_mapfields) {
//            $this->addException('Please define field mapping', Mage_Dataflow_Model_Convert_Exception::FATAL);
//            return;
//        }
//
//        if ($this->getVar('adapter') && $this->getVar('method')) {
//            $adapter = Mage::getModel($this->getVar('adapter'));
//        }
//
//        $i = 0;
//        while (($line = fgetcsv($fh, null, $fDel, $fEnc)) !== FALSE) {
//            $row = $this->parseRow($i, $line);
//
//            if (!$this->getVar('fieldnames') && $i == 0 && $row) {
//                $i = 1;
//            }
//
//            if ($row) {
//                $loadMethod = $this->getVar('method');
//                $adapter->$loadMethod(compact('i', 'row'));
//            }
//            $i++;
//        }
//
//        return $this;
    }

    public function parseRow($i, $line)
    {
        if (sizeof($line) == 1) return false;

        if (0==$i) {
            if ($this->getVar('fieldnames')) {
                $this->_fields = $line;
                return;
            } else {
                foreach ($line as $j=>$f) {
                    $this->_fields[$j] = $this->_mapfields[$j];
                }
            }
        }

        $resultRow = array();

        foreach ($this->_fields as $j=>$f) {
            $resultRow[$f] = isset($line[$j]) ? $line[$j] : '';
        }
        return $resultRow;
    }

    /**
     * Read data collection and write to temporary file
     *
     * @return Mage_Dataflow_Model_Convert_Parser_Csv
     */
    public function unparse()
    {
        $batchExport = $this->getBatchExportModel()
            ->setBatchId($this->getBatchModel()->getId());
        $fieldList = $this->getBatchModel()->getFieldList();

        /*  CDL Export  start*/
        $fieldList['gallery'] = 'gallery';
        $fieldList['gallery_position'] = 'gallery_position';
        $fieldList['_super_attribute_code'] = '_super_attribute_code';
        $fieldList['_super_attribute_value'] = '_super_attribute_value';
        /* CDL Export finish*/

        $batchExportIds = $batchExport->getIdCollection();

        $io = $this->getBatchModel()->getIoAdapter();
        $io->open();

        if (!$batchExportIds) {
            $io->write("");
            $io->close();
            return $this;
        }

        if ($this->getVar('fieldnames')) {
            $csvData = $this->getCsvString($fieldList);
            $io->write($csvData);
        }

        foreach ($batchExportIds as $batchExportId) {
            $csvData = array();
            $batchExport->load($batchExportId);
            $row = $batchExport->getBatchData();

            /* CDL Export start*/
            $productid = Mage::getModel('catalog/product')->getIdBySku($row['sku']);
            // Initiate product model
            $product = Mage::getModel('catalog/product');
            // Load specific product whose tier price want to update
            $product->load($productid);

            if(!$product->isConfigurable()) {

                $parentid = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($productid);
                if(!is_null($parentid)) {
                    $parentProduct = Mage::getModel('catalog/product');
                    // Load specific product whose tier price want to update
                    $parentProduct->load($parentid);

                    $configurableAttributeCollection=$parentProduct->getTypeInstance()->getConfigurableAttributes();
 
                    /**
                    * Use the collection to get the desired values of attribute
                    */
                    $attributecode = '';
                    $attributevalue = '';
                    foreach($configurableAttributeCollection as $attribute){
                        $attributecode .= ($attributecode === "") ? "": ",";
                        $attributevalue .= ($attributevalue === "") ? "": ",";
 
                        $attributecode .= $attribute->getProductAttribute()->getAttributeCode();
                        $attributeid = $attribute->getProductAttribute()->getId();
                        $attributevalue .= $product->getResource()->getAttribute($attribute->getProductAttribute()->getAttributeCode())->getFrontend()->getValue($product);
                    }
                }
            }


            $mediaGallery = $product->getMediaGallery();
            $mediaGallery = $mediaGallery['images'];
            $add_images = '';
            $add_images_position = '';
            foreach ($mediaGallery as $add_image) {
                if (!$add_image['disabled']){
                    $add_images .= $add_image['file'].';';
                    $add_images_position .= $add_image['position'].';';
                }
            }
            // Remove last ';' from images list
            $add_images = substr_replace($add_images ,"",-1);
            $add_images_position = substr_replace($add_images_position ,"",-1);
 
            $row['gallery'] =  $add_images;
            $row['gallery_position'] =  $add_images_position;
            $row['_super_attribute_code'] =  $attributecode;
            $row['_super_attribute_value'] =  $attributevalue;
            /* CDL Export finish*/

            foreach ($fieldList as $field) {
                $csvData[] = isset($row[$field]) ? $row[$field] : '';
            }
            $csvData = $this->getCsvString($csvData);
            $io->write($csvData);
        }

        $io->close();

        return $this;
    }

    public function unparseRow($args)
    {
        $i = $args['i'];
        $row = $args['row'];

        $fDel = $this->getVar('delimiter', ',');
        $fEnc = $this->getVar('enclose', '"');
        $fEsc = $this->getVar('escape', '\\');
        $lDel = "\r\n";

        if ($fDel == '\t') {
            $fDel = "\t";
        }

        $line = array();
        foreach ($this->_fields as $f) {
            $v = isset($row[$f]) ? str_replace(array('"', '\\'), array($fEnc.'"', $fEsc.'\\'), $row[$f]) : '';
            $line[] = $fEnc.$v.$fEnc;
        }

        return join($fDel, $line);
    }

    /**
     * Retrieve csv string from array
     *
     * @param array $fields
     * @return sting
     */
    public function getCsvString($fields = array()) {
        $delimiter  = $this->getVar('delimiter', ',');
        $enclosure  = $this->getVar('enclose', '');
        $escapeChar = $this->getVar('escape', '\\');

        if ($delimiter == '\t') {
            $delimiter = "\t";
        }

        $str = '';

        foreach ($fields as $value) {
            if (strpos($value, $delimiter) !== false ||
                empty($enclosure) ||
                strpos($value, $enclosure) !== false ||
                strpos($value, "\n") !== false ||
                strpos($value, "\r") !== false ||
                strpos($value, "\t") !== false ||
                strpos($value, ' ') !== false) {
                $str2 = $enclosure;
                $escaped = 0;
                $len = strlen($value);
                for ($i=0;$i<$len;$i++) {
                    if ($value[$i] == $escapeChar) {
                        $escaped = 1;
                    } else if (!$escaped && $value[$i] == $enclosure) {
                        $str2 .= $enclosure;
                    } else {
                        $escaped = 0;
                    }
                        $str2 .= $value[$i];
                }
                $str2 .= $enclosure;
                $str .= $str2.$delimiter;
            } else {
                $str .= $enclosure.$value.$enclosure.$delimiter;
            }
        }
        return substr($str, 0, -1) . "\n";
    }
}