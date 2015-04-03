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
class Mage_Dataflow_Model_Convert_Parser_Xml extends Mage_Dataflow_Model_Convert_Parser_Csv
{
    protected $_fields;
    protected $_colorId = 85;
    protected $_sizeId = 128;
	protected $_relationsId =203;
    protected $_sizeRelationsId =204;
    protected $_colorCode = 'color';
    protected $_sizeCode = 'product_size';
	protected $_vidCode = 'vid_tovara';
	protected $_vidRelationsCode = 'import_vid_tovara_relations';
	protected $_vidyTovarov = array();



    public function parse()
    {

        // fixed for multibyte characters
        setlocale(LC_ALL, Mage::app()->getLocale()->getLocaleCode().'.UTF-8');

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

        $xmlString='';
        while (($xmlOriginalString = $batchIoAdapter->read()) !== false) {
            $xmlString .= $xmlOriginalString;
        }

        $this->saveColors($xmlString)->saveSizes($xmlString)->saveVidTovara($xmlString);


        $parse = $this->getXmlBlock($xmlString, $this->toUtf8("<Товары>"), $this->toUtf8("</Товары>"));
        $products=explode($this->toUtf8('<Товар'),$parse);

        $countRows=0;
        foreach($products as $product)
        {
            $productArr = array();
            if($countRows){

                $temp=explode($this->toUtf8('</Товар>'),$product);

                //parsed from xml
                $productArr['name'] = $this->getXmlBlock($temp[0], $this->toUtf8('Наименование="'), '"');
                if (empty($productArr['name'])) $productArr['name']='-';
                $productArr['sku'] = preg_replace("/[^0-9]/","", $this->getXmlBlock($temp[0], $this->toUtf8('Код="'), '"'));
                $productArr['description'] = $this->getXmlBlock($temp[0], $this->toUtf8('<Описание>'), $this->toUtf8('</Описание>'));
                if (empty($productArr['description'])) $productArr['description']='-';
                $productArr['short_description'] = $this->getXmlBlock($temp[0], $this->toUtf8('<Состав>'), $this->toUtf8('</Состав>'));
                if (empty($productArr['short_description'])) $productArr['short_description']='-';
                $productArr['price'] = $this->getXmlBlock($temp[0], $this->toUtf8('<Цена>'), $this->toUtf8('</Цена>'));
                $productArr['special_price'] = $this->getXmlBlock($temp[0], $this->toUtf8('<ЦенаАкция>'), $this->toUtf8('</ЦенаАкция>'));
                $productArr['category_ids'] = $this->parseCategories($temp[0]);
                $productArr['country_madein'] = $this->getXmlBlock($temp[0], $this->toUtf8('<СтранаИзготовитель>'), $this->toUtf8('</СтранаИзготовитель>'));
                $productArr['meta_title'] = $productArr['name'].$this->toUtf8(" | Код товара: ").$productArr['sku'].$this->toUtf8(', купить в интернет-магазине, Киев: цены, фото – A-Shop');
				$productArr['meta_description'] = $this->toUtf8('Интернет-магазин A-Shop – ').$productArr['name'].$this->toUtf8(" | Код товара: ").$productArr['sku'].$this->toUtf8('. Модные товары и идеи для Вашего неповторимого стиля в интернет-магазине A-Shop. Фотографии, цены, доставка по Киеву и Украине. Тел: 098-022-65-90.');
                //default for all products
                $productArr['type'] = Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE;
                $productArr['status'] = Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
                $productArr['visibility'] = Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH;
                $productArr['attribute_set'] = 9;
                $productArr['tax_class_id'] = 0;
                
				
				$_configurableQty = 0;
				foreach(explode($this->toUtf8('Остаток="'),$temp[0]) as $_qtyItem) {
				    $_tempQty = explode($this->toUtf8('"'),$_qtyItem);
					$_configurableQty += intval($_tempQty[0]);
				}
				
				if($_configurableQty) {
					$productArr['is_in_stock'] = 1;
				} else {
					$productArr['is_in_stock'] = 0;
				}
				
                $productArr['qty'] = 0;
                $productArr['weight'] = '1';
                $productArr['websites'] = array('1');
				$vidtovara = $this->getXmlBlock($temp[0], $this->toUtf8('<ВидТовара>'), $this->toUtf8('</ВидТовара>'));

                //simple products arr
                $productArr['simple_products'] = $this->addSimpleProducts($productArr, $this->getXmlBlock($temp[0], $this->toUtf8('<Цвета>'), $this->toUtf8('</Цвета>')),$vidtovara);
				

                //configurable image data


                $colorsData = $this->getXmlBlock($temp[0], $this->toUtf8('<Цвета>'), $this->toUtf8('</Цвета>'));
                $colorArr = explode($this->toUtf8('<Цвет'),$colorsData);
                $productArr['gallery']=''; $productArr['gallery_position']=''; $productArr['gallery_color']=''; $firstFlag=1;
				$counter=0;
                foreach($colorArr as $colorData) {
				    
					if(!$counter) {
					    $counter++;
					    continue;
					}

                    $simpleColor = explode($this->toUtf8('</Цвет>'),$colorData);
                    $simpleColorData = explode($this->toUtf8('>'),$simpleColor[0]);

                    $colorOptionId=false;
		            $_relateCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                        ->setStoreFilter(1)
                        ->setAttributeFilter($this->_relationsId)
                        ->load();
					
		            $_relateCodesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                        ->setStoreFilter(0)
                        ->setAttributeFilter($this->_relationsId)
                        ->load();

                    $colorCode = trim($this->getXmlBlock($simpleColorData[0], $this->toUtf8('Код="'), '"'));
                    foreach( $_relateCollection->toOptionArray() as $someOption ) {

                        if($someOption['label'] == $colorCode) {
						    
							foreach( $_relateCodesCollection->toOptionArray() as $codeOption ) {
							
							    if($someOption['value'] == $codeOption['value']) {
						            $colorOptionId = $codeOption['label']; 
									//echo "1 ".$colorCode."-".$colorOptionId."<br>";
								}
								
							}
							//print_r($_relateLabels);
							//echo $colorCode."-".$colorOptionId."<br>";
						}
						
					}
					//print_r($_relateCollection->toOptionArray()); 

					
					
                    /*$attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $this->_colorCode);
                    if ($attribute->usesSource()) {
                        $options = $attribute->getSource()->getAllOptions(false);
                    }

                    $colorLabel = $this->getTranslit($this->getXmlBlock($simpleColor[0], $this->toUtf8('Наименование="'), '"'));
                    foreach($options as $option) {   
                        if($option['label'] == $colorLabel) {        
                            $colorOptionId = $option['value'];    
                        }      
                    }*/

                    $imagesData = $this->getXmlBlock($simpleColor[0], $this->toUtf8('<Изображения>'), $this->toUtf8('</Изображения>'));
                    $imageArr = explode($this->toUtf8('<Изображения'),$imagesData);

                    $imageFlag=0; 
                    foreach($imageArr as $imageData) {

                        if($imageFlag) {

                            if($firstFlag == 1 && $this->getXmlBlock($imageData, $this->toUtf8('НомерИзображения="'), $this->toUtf8('"')) == 1) {
                                
                                $productArr['image'] = '/images/'.$this->getXmlBlock($imageData, $this->toUtf8('ИмяФайла="'), $this->toUtf8('"')).'.jpg';
                                $productArr['thumbnail'] = '/images/'.$this->getXmlBlock($imageData, $this->toUtf8('ИмяФайла="'), $this->toUtf8('"')).'.jpg';
                                $productArr['small_image'] = '/images/'.$this->getXmlBlock($imageData, $this->toUtf8('ИмяФайла="'), $this->toUtf8('"')).'.jpg';
                                $productArr['image_color'] = $colorOptionId;
                                $firstFlag=0;
                                
                            } else {
                                if(!empty($productArr['gallery'])) {$productArr['gallery'] .= ';'; $productArr['gallery_position'] .= ';'; $productArr['gallery_color'] .= ';';}
                                $productArr['gallery'] .= '/images/'.$this->getXmlBlock($imageData, $this->toUtf8('ИмяФайла="'), $this->toUtf8('"')).'.jpg';
                                $productArr['gallery_position'] .= $this->getXmlBlock($imageData, $this->toUtf8('НомерИзображения="'), $this->toUtf8('"'));
                                $productArr['gallery_color'] .= $colorOptionId;

                            }
    
                        }
                        $imageFlag++; 
                        

                    }
                    

                }

                $batchImportModel = $this->getBatchImportModel()
                    ->setId(null)
                    ->setBatchId($this->getBatchModel()->getId())
                    ->setBatchData($productArr)
                    ->setStatus(1)
                    ->save();

            }
            $countRows++;
            //if($countRows>5) break;
            
        } 

        

        $this->addException(Mage::helper('dataflow')->__('Found %d rows.', $countRows));
        $this->addException(Mage::helper('dataflow')->__('Starting %s :: %s', $adapterName, $adapterMethod));

        $batchModel->setParams($this->getVars())
            ->setAdapter($adapterName)
            ->save();

        //$adapter->$adapterMethod();

        return $this;
    }
	
    public function parseLight()
    {
        // fixed for multibyte characters
        setlocale(LC_ALL, Mage::app()->getLocale()->getLocaleCode().'.UTF-8');

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

        $xmlString='';
        while (($xmlOriginalString = $batchIoAdapter->read()) !== false) {
            $xmlString .= $xmlOriginalString;
        }


        $parse = $this->getXmlBlock($xmlString, $this->toUtf8("<Товары>"), $this->toUtf8("</Товары>"));
        $products=explode($this->toUtf8('<Товар'),$parse);

        $countRows=0;
        foreach($products as $product)
        {
            $productArr = array();
            if($countRows){

                $temp=explode($this->toUtf8('</Товар>'),$product);

                //parsed from xml
                $productArr['name'] = $this->getXmlBlock($temp[0], $this->toUtf8('Наименование="'), '"');
                if (empty($productArr['name'])) $productArr['name']='-';
                $productArr['sku'] = preg_replace("/[^0-9]/","", $this->getXmlBlock($temp[0], $this->toUtf8('Код="'), '"'));
                $productArr['description'] = $this->getXmlBlock($temp[0], $this->toUtf8('<Описание>'), $this->toUtf8('</Описание>'));
                if (empty($productArr['description'])) $productArr['description']='-';
                $productArr['short_description'] = $this->getXmlBlock($temp[0], $this->toUtf8('<Состав>'), $this->toUtf8('</Состав>'));
                if (empty($productArr['short_description'])) $productArr['short_description']='-';
                $productArr['price'] = $this->getXmlBlock($temp[0], $this->toUtf8('<Цена>'), $this->toUtf8('</Цена>'));
                $productArr['special_price'] = $this->getXmlBlock($temp[0], $this->toUtf8('<ЦенаАкция>'), $this->toUtf8('</ЦенаАкция>'));
                $productArr['category_ids'] = $this->parseCategories($temp[0]);
                $productArr['country_madein'] = $this->getXmlBlock($temp[0], $this->toUtf8('<СтранаИзготовитель>'), $this->toUtf8('</СтранаИзготовитель>'));
                $productArr['meta_title'] = $productArr['name'].$this->toUtf8(" | Код товара: ").$productArr['sku'].$this->toUtf8(', купить в интернет-магазине, Киев: цены, фото – A-Shop');
				$productArr['meta_description'] = $this->toUtf8('Интернет-магазин A-Shop – ').$productArr['name'].$this->toUtf8(" | Код товара: ").$productArr['sku'].$this->toUtf8('. Модные товары и идеи для Вашего неповторимого стиля в интернет-магазине A-Shop. Фотографии, цены, доставка по Киеву и Украине. Тел: 098-022-65-90.');
                //default for all products
                $productArr['type'] = Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE;
                $productArr['status'] = Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
                $productArr['visibility'] = Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH;
                $productArr['attribute_set'] = 9;
                $productArr['tax_class_id'] = 0;
                
				
				$_configurableQty = 0;
				foreach(explode($this->toUtf8('Остаток="'),$temp[0]) as $_qtyItem) {
				    $_tempQty = explode($this->toUtf8('"'),$_qtyItem);
					$_configurableQty += intval($_tempQty[0]);
				}
				
				if($_configurableQty) {
					$productArr['is_in_stock'] = 1;
				} else {
					$productArr['is_in_stock'] = 0;
				}
				
                $productArr['qty'] = 0;
                $productArr['weight'] = '1';
                $productArr['websites'] = array('1');
				$vidtovara = $this->getXmlBlock($temp[0], $this->toUtf8('<ВидТовара>'), $this->toUtf8('</ВидТовара>'));

                //simple products arr
                $productArr['simple_products'] = $this->addSimpleProductsLight($productArr, $this->getXmlBlock($temp[0], $this->toUtf8('<Цвета>'), $this->toUtf8('</Цвета>')),$vidtovara);
				

                //configurable image data


                

                $batchImportModel = $this->getBatchImportModel()
                    ->setId(null)
                    ->setBatchId($this->getBatchModel()->getId())
                    ->setBatchData($productArr)
                    ->setStatus(1)
                    ->save();

            }
            $countRows++;
            //if($countRows>5) break;
            
        } 

        

        $this->addException(Mage::helper('dataflow')->__('Found %d rows.', $countRows));
        $this->addException(Mage::helper('dataflow')->__('Starting %s :: %s', $adapterName, $adapterMethod));

        $batchModel->setParams($this->getVars())
            ->setAdapter($adapterName)
            ->save();

        //$adapter->$adapterMethod();

        return $this;
    }

    public function addSimpleProducts(array $data, $str , $vidtovara) 
    {

        $simple_products = array();
        $products=explode($this->toUtf8('<Цвет'),$str);

        $flag=0;
        foreach($products as $product)
        {
             if ($flag) {

                 $simpledata=explode($this->toUtf8('</Цвет>'),$product);
				 $simpleColorData = explode($this->toUtf8('>'),$simpledata[0]);
                 if ($this->getXmlBlock($simpledata[0], $this->toUtf8('Наименование="'), '"') != '') {
                     $productData=array(); $productData=$data;
                     
                     //parsed from xml
                     $productData['name'] .= '-'.$this->getXmlBlock($simpledata[0], $this->toUtf8('Наименование="'), '"');
                     $productData['sku'] .= '-'.$this->getTranslit($this->getXmlBlock($simpledata[0], $this->toUtf8('Код="'), '"'));
                     $productData['qty'] = $this->getXmlBlock($simpledata[0], $this->toUtf8('Остаток="'), '"');
                     $productData['color'] = $this->getTranslit($this->getXmlBlock($simpledata[0], $this->toUtf8('Наименование="'), '"'));
					 $productData['vid_tovara'] = $this->_vidyTovarov[$vidtovara];
					 //echo $productData['vid_tovara'];


                     //default for simple products oher defaults from configurable product
                     $productData['type'] = Mage_Catalog_Model_Product_Type::TYPE_SIMPLE;
                     $productData['status'] = Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
                     $productData['visibility'] = Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE;


                    $colorOptionId=false;
		            $_relateCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                        ->setStoreFilter(1)
                        ->setAttributeFilter($this->_relationsId)
                        ->load();
					
		            $_relateCodesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                        ->setStoreFilter(0)
                        ->setAttributeFilter($this->_relationsId)
                        ->load();

                    $colorCode = trim($this->getXmlBlock($simpleColorData[0], $this->toUtf8('Код="'), '"'));
                    foreach( $_relateCollection->toOptionArray() as $someOption ) {

                        if($someOption['label'] == $colorCode) {
						    
							foreach( $_relateCodesCollection->toOptionArray() as $codeOption ) {
							
							    if($someOption['value'] == $codeOption['value']) {
						            $colorOptionId = $codeOption['label']; 
									//echo "2 ".$colorCode."-".$colorOptionId."<br>";
								}
								
							}
							//print_r($_relateLabels);
							//echo $colorCode."-".$colorOptionId."<br>";
						}
						
					}
					$colorLabel = $this->getTranslit($this->getXmlBlock($simpleColorData[0], $this->toUtf8('Наименование="'), '"'));
					
                     /*$attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $this->_colorCode);
                     if ($attribute->usesSource()) {
                         $options = $attribute->getSource()->getAllOptions(false);
                     }
 
                     $colorLabel = $this->getTranslit($this->getXmlBlock($simpledata[0], $this->toUtf8('Наименование="'), '"'));
                     $colorFrontendLabel = $this->getXmlBlock($simpledata[0], $this->toUtf8('Наименование="'), '"');
                     foreach($options as $option) {   
                         if($option['label'] == $colorLabel) {        
                             $colorOptionId = $option['value'];    
                         }      
                     }*/


                    $sizeOptionId=false;
		            $_relateSizeCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                        ->setStoreFilter(1)
                        ->setAttributeFilter($this->_sizeRelationsId)
                        ->load();
					
		            $_relateSizeCodesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                        ->setStoreFilter(0)
                        ->setAttributeFilter($this->_sizeRelationsId)
                        ->load();

					
                     /*$attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $this->_sizeCode);
                     if ($attribute->usesSource()) {
                         $options = $attribute->getSource()->getAllOptions(false);
                     }*/
 
                     $sizes = $this->getXmlBlock($simpledata[0], $this->toUtf8('<Размеры>'), $this->toUtf8('</Размеры>'));

                     if (!strpos('<Размер',$sizes)) {

                         $simple_products[$flag][0] = array(
                                 "sku" => $productData['sku'],
                                 "price" => $data['special_price'],
                                 "attr_code" => $this->_colorCode,
                                 "attr_id" => $this->_colorId,
                                 "value" => $colorOptionId,
                                 "label" => $colorLabel,
                                 "frontend_label" => $colorFrontendLabel
                         );
                     } else {

                         $sizesList=explode($this->toUtf8('<Размер',$sizes));
                         
                         $sizeFlag=0; $imported=false; $productDataSize=array();
                         foreach($sizesList as $size) {
    
                             $productDataSize=$productData;
                             if($sizeFlag) {
                    
                             $sizeLabel = $this->getTranslit($this->getXmlBlock($size, $this->toUtf8('Наименование="'), '"'));
                             $sizeQty = $this->getXmlBlock($size, $this->toUtf8('Остаток="'), '"');
                             $sizeCode = $this->getXmlBlock($size, $this->toUtf8('Код="'), '"');
        
                                 if (!empty($sizeCode) && !empty($sizeLabel)) {
								 
                                    foreach( $_relateSizeCollection->toOptionArray() as $someOption ) {

                                        if($someOption['label'] == $sizeCode) {
						    
					                		foreach( $_relateSizeCodesCollection->toOptionArray() as $codeOption ) {
							
					                		    if($someOption['value'] == $codeOption['value']) {
						                            $sizeOptionId = $codeOption['label']; 
													//echo "3 ".$sizeCode."-".$sizeOptionId."<br>";
							                	}
								
						                   	}
 
				                 		}
						
				                	}
									
                                    /* foreach($options as $option) {   
                                         if($option['label'] == $sizeLabel) {        
                                             $sizeOptionId = $option['value'];    
                                         }      
                                     }*/
    
                                     $productDataSize['name'] .= '-'.$sizeLabel;
                                     $productDataSize['sku'] .= '-'.$sizeCode;
                                     $productDataSize['qty'] = $sizeQty;
                                     $productDataSize['product_size'] = $sizeLabel;
     
                                     $simple_products[$flag][0] = array(
                                             "sku" => $productDataSize['sku'],
                                             "price" => $data['special_price'],
                                             "attr_code" => $this->_colorCode,
                                             "attr_id" => $this->_colorId,
                                             "value" => $optionId,
                                             "label" => $colorLabel
                                     );
                                     $simple_products[$flag][1] = array(
                                             "sku" => $productDataSize['sku'],
                                             "price" => $data['special_price'],
                                             "attr_code" => $this->_sizeCode,
                                             "attr_id" => $this->_sizeId,
                                             "value" => $sizeOptionId,
                                             "label" => $sizeLabel
                                     );
    
    
    
                                     $batchImportModel = $this->getBatchImportModel()
                                         ->setId(null)
                                         ->setBatchId($this->getBatchModel()->getId())
                                         ->setBatchData($productDataSize)
                                         ->setStatus(1)
                                         ->save();
    
                                     $imported=true;
        
                                 } else {
        
                                     $simple_products[$flag][0] = array(
                                             "sku" => $productData['sku'],
                                             "price" => $data['special_price'],
                                             "attr_code" => $this->_colorCode,
                                             "attr_id" => $this->_colorId,
                                             "value" => $colorOptionId,
                                             "label" => $colorLabel
                                     );
        
                                 }
                             }
    
                         $sizeFlag++;
                         $flag += $sizeFlag;
                         }
                     }

                     if (!$imported) {
                     $batchImportModel = $this->getBatchImportModel()
                         ->setId(null)
                         ->setBatchId($this->getBatchModel()->getId())
                         ->setBatchData($productData)
                         ->setStatus(1)
                         ->save();

                     }


                 }

             }
        $flag++;     
        }
        

        return $simple_products;
    }
	
    public function addSimpleProductsLight(array $data, $str , $vidtovara) 
    {

        $simple_products = array();
        $products=explode($this->toUtf8('<Цвет'),$str);

        $flag=0;
        foreach($products as $product)
        {
             if ($flag) {

                 $simpledata=explode($this->toUtf8('</Цвет>'),$product);
				 $simpleColorData = explode($this->toUtf8('>'),$simpledata[0]);
                 if ($this->getXmlBlock($simpledata[0], $this->toUtf8('Наименование="'), '"') != '') {
                     $productData=array(); $productData=$data;
                     
                     //parsed from xml
                     $productData['name'] .= '-'.$this->getXmlBlock($simpledata[0], $this->toUtf8('Наименование="'), '"');
                     $productData['sku'] .= '-'.$this->getTranslit($this->getXmlBlock($simpledata[0], $this->toUtf8('Код="'), '"'));
                     $productData['qty'] = $this->getXmlBlock($simpledata[0], $this->toUtf8('Остаток="'), '"');
                     $productData['color'] = $this->getTranslit($this->getXmlBlock($simpledata[0], $this->toUtf8('Наименование="'), '"'));
					 $productData['vid_tovara'] = $this->_vidyTovarov[$vidtovara];
					 //echo $productData['vid_tovara'];


                     //default for simple products oher defaults from configurable product
                     $productData['type'] = Mage_Catalog_Model_Product_Type::TYPE_SIMPLE;
                     $productData['status'] = Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
                     $productData['visibility'] = Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE;


                    $colorOptionId=false;
		            $_relateCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                        ->setStoreFilter(1)
                        ->setAttributeFilter($this->_relationsId)
                        ->load();
					
		            $_relateCodesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                        ->setStoreFilter(0)
                        ->setAttributeFilter($this->_relationsId)
                        ->load();








                     if (!$imported) {
                     $batchImportModel = $this->getBatchImportModel()
                         ->setId(null)
                         ->setBatchId($this->getBatchModel()->getId())
                         ->setBatchData($productData)
                         ->setStatus(1)
                         ->save();

                     }


                 }

             }
        $flag++;     
        }
        

        return $simple_products;
    }

    public function parseCategories($str) 
    {

        $parser = $this->getXmlBlock($str, $this->toUtf8("<Категории>"), $this->toUtf8("</Категории>"));
        $categories=explode($this->toUtf8('<Категория'),$parser);

        $flag=0; $cat_ids=array();
        foreach($categories as $category)
        {
             if ($flag) {
                 $category_id = $this->getXmlBlock($category, $this->toUtf8('IDНаСайте="'), '"');
                 $cat_ids[$flag]= $category_id;
             }
             $flag++;
        }
        

        return $cat_ids;
    }

    public function saveColors($str) 
    {

        $parse = $this->getXmlBlock($str, $this->toUtf8("</Товары>"), $this->toUtf8("</Корневой>"));

        $parser = $this->getXmlBlock($parse, $this->toUtf8("<Цвета>"), $this->toUtf8("</Цвета>"));
        $colors=explode($this->toUtf8('Наименование="'),$parser);

        $flag=0;$new=0;$checked=0;$updated=0;
        foreach($colors as $color)
        {
             if ($flag) {
                 $rusColor = explode('"',$color);
				 $optionCodeXml = explode($this->toUtf8('Код="'),$color);
				 $optionCode = explode('"',$optionCodeXml[1]);
                 
                 if(trim($rusColor[0]) != ''){

                     //Create options array
					 //$rusColor[0].='1';
					 $values['order'] = array('someoption' => 0);
                     $values['value']  = array('someoption'=>array('0'=>$this->getTranslit($rusColor[0]),'1'=>$rusColor[0]));
					 
					 if(intval($optionCode[0]) == 0 || strlen(intval(trim($optionCode[0]))) != strlen(trim($optionCode[0])))
					 {
					     echo Mage::helper('dataflow')->__('Color %s has incorrect code (%s)', $rusColor[0], $optionCode[0]);
						 exit();
					 }

					 
					 $relateValues['value'] = array('someoption'=>array('0'=>0,'1'=>trim($optionCode[0])));
    
	                 $returnValue = $this->addattributes($this->_colorId, $values, $relateValues, $this->_relationsId); 
                     if($returnValue == 2){
                         $new++;
                     } 
                     if($returnValue == 1){					 
					     $updated++;
					 }
                     $checked++;

                 }
             }
             $flag=1;
        }

        $this->addException(Mage::helper('dataflow')->__('Colors checked: %s. New colors added: %s. Updated colors: %s.', $checked, $new, $updated));
        

        return $this;
    }

    public function saveSizes($str) 
    {

        $parse = $this->getXmlBlock($str, $this->toUtf8("</Товары>"), $this->toUtf8("</Корневой>"));
        $parser = $this->getXmlBlock($parse, $this->toUtf8("<Размеры>"), $this->toUtf8("</Размеры>"));
        $sizes=explode($this->toUtf8('Наименование="'),$parser);

        $flag=0;$new=0;$checked=0;$updated=0;
        foreach($sizes as $size)
        {
             if ($flag) {
                 $rusSize = explode('"',$size);
				 $optionCodeXml = explode($this->toUtf8('Код="'),$size);
				 $optionCode = explode('"',$optionCodeXml[1]);
                 
                 if(trim($rusSize[0]) != ''){

                     //Create options array
					 $values['order'] = array('someoption' => 0);
                     $values['value']  = array('someoption'=>array('0'=>$this->getTranslit($rusSize[0]),'1'=>$rusSize[0]));
					 
					 if(intval($optionCode[0]) == 0 || strlen(intval(trim($optionCode[0]))) != strlen(trim($optionCode[0])))
					 {
					     echo Mage::helper('dataflow')->__('Size %s has incorrect code (%s)', $rusSize[0], $optionCode[0]);
						 exit();
					 }
					 
					 $relateValues['value'] = array('someoption'=>array('0'=>0,'1'=>trim($optionCode[0])));
    
	                 $returnValue = $this->addattributes($this->_sizeId, $values, $relateValues, $this->_sizeRelationsId);
                     if($returnValue==2){
                         $new++;
                     } 
					 if($returnValue==1){
					     $updated++;
					 }
                     $checked++;

                 }
             }
             $flag=1;
        }
        $this->addException(Mage::helper('dataflow')->__('Sizes checked: %s. New sizes added: %s. Updated sizes: %s.', $checked, $new, $updated));

        return $this;
    }
	
	
    public function saveVidTovara($str) 
    {

        $parse = $this->getXmlBlock($str, $this->toUtf8("</Товары>"), $this->toUtf8("</Корневой>"));

        $parser = $this->getXmlBlock($parse, $this->toUtf8("<ВидыТоваров>"), $this->toUtf8("</ВидыТоваров>"));
        $vids=explode($this->toUtf8('<ВидТовара'),$parser);

        $flag=0;$new=0;$checked=0;$updated=0;
        foreach($vids as $vid)
        {
             if ($flag) {
			     $vid_name_parse=explode($this->toUtf8('Наименование="'),$vid);
                 $rusVid = explode('"',$vid_name_parse['1']);
				 $optionCodeXml = explode($this->toUtf8('Код="'),$vid);
				 $optionCode = explode('"',$optionCodeXml[1]);
                 
                 if(trim($rusVid[0]) != ''){

                     //Create options array
					 //$rusColor[0].='1';
					 $values['order'] = array('someoption' => 0);
                     $values['value']  = array('someoption'=>array('0'=>$this->getTranslit($rusVid[0]),'1'=>$rusVid[0]));
					 
            
					 $this->_vidyTovarov[$optionCode[0]] = $this->getTranslit($rusVid[0]);
					 if(intval($optionCode[0]) == 0 || strlen(intval(trim($optionCode[0]))) != strlen(trim($optionCode[0])))
					 {
					     echo Mage::helper('dataflow')->__('Vid tovara %s has incorrect code (%s)', $rusVid[0], $optionCode[0]);
						 exit();
					 }

					 
					 $relateValues['value'] = array('someoption'=>array('0'=>0,'1'=>trim($optionCode[0])));
                     $_vidId = Mage::getModel('eav/entity_attribute')->loadByCode('4', $this->_vidCode)->getId();
					 $_vidRelationsId = Mage::getModel('eav/entity_attribute')->loadByCode('4', $this->_vidRelationsCode)->getId();
	                 $returnValue = $this->addattributes($_vidId, $values, $relateValues, $_vidRelationsId); 
                     if($returnValue == 2){
                         $new++;
                     } 
                     if($returnValue == 1){					 
					     $updated++;
					 }
                     $checked++;

                 }
             }
             $flag=1;
        }

        $this->addException(Mage::helper('dataflow')->__('Vid tovara checked: %s. New vid tovara added: %s. Updated vid tovara: %s.', $checked, $new, $updated));
        

        return $this;
    }

	
	public function setAllOutOfStock() 
    {
		try{
			$collection = Mage::getModel('catalog/product')
                        ->getCollection()
                        ->addAttributeToSelect('*');

			foreach ($collection as $product) {

$product = Mage::getModel('catalog/product')->load($product->getId());
$stockData = $product->getStockItem();
$stockData->setData('qty',0);
$stockData->setData('is_in_stock',0);
$stockData->setData('manage_stock',1);
$stockData->setData('use_config_manage_stock',1);
$stockData->save();

			} 

			
			$this->addException("Set all product out of stock");
		} catch (Exception $e) {
			// do something
			$this->addException($e->getMessage(), Mage_Dataflow_Model_Convert_Exception::FATAL);
		}
    }


    public function toUtf8($str) 
    {
        return iconv('cp1251','utf8', $str);
    }

    public function getXmlBlock($str, $firstTag, $endTag) 
    {

       if (strpos($str,$firstTag)) {
           $tempBlock = explode($firstTag,$str);

          if (strpos($tempBlock[1],$endTag)) {
              $resultBlock = explode($endTag,$tempBlock[1]);
          } else {
              return false;
          }
      
       } else {
           return false;
       }

       return $resultBlock[0];
    }

    public function getTranslit($str) 
    {
       if (preg_match('/[^A-Za-z0-9_\-]/', $str)) {
           $urlstr = $this->translitIt($str);
           $urlstr = preg_replace('/[^A-Za-z0-9_\-]/', '', $urlstr);
       } else {
           return $str;
       }

       return $urlstr;
    }

    public function translitIt($str) 
    {
        $tr = array(
            "А"=>"a","Б"=>"b","В"=>"v","Г"=>"g",
            "Д"=>"d","Е"=>"e","Ж"=>"j","З"=>"z","И"=>"i",
            "Й"=>"y","К"=>"k","Л"=>"l","М"=>"m","Н"=>"n",
            "О"=>"o","П"=>"p","Р"=>"r","С"=>"s","Т"=>"t",
            "У"=>"u","Ф"=>"f","Х"=>"h","Ц"=>"ts","Ч"=>"ch",
            "Ш"=>"sh","Щ"=>"sch","Ъ"=>"","Ы"=>"yi","Ь"=>"",
            "Э"=>"e","Ю"=>"yu","Я"=>"ya","а"=>"a","б"=>"b",
            "в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"j",
            "з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
            "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
            "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
            "ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
            "ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya", 
            " "=> "_", "."=> "", "/"=> "_"
        );
        
        return strtr(iconv('utf8','cp1251',$str),$tr);
    }
	
    public function test() 
    {
        echo "test11";
        
        return "test";
    }

    public function addattributes($attribute_id, $values, $relateValues, $relateAttrId)
    {
        
        //Get the eav attribute model
        $attr_model = Mage::getModel('catalog/resource_eav_attribute');
        $relate_model = Mage::getModel('catalog/resource_eav_attribute');
		
        //Load the particular attribute by id
        //Here 73 is the id of 'manufacturer' attribute
        $attr_model->load($attribute_id);
		$relate_model->load($relateAttrId);

		
		$newAttr = TRUE;
		$_relateCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                    ->setStoreFilter(1)
                    ->setAttributeFilter($relateAttrId)
                    ->load();
					
		$_relateCodesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                    ->setStoreFilter(0)
                    ->setAttributeFilter($relateAttrId)
                    ->load();

        foreach( $_relateCollection->toOptionArray() as $someOption ) {
		
            if($someOption['label'] == $relateValues['value']['someoption']['1']) {
			
			    //attribute is in base
				$newAttr = FALSE;
				$_attrCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                    ->setStoreFilter(1)
                    ->setAttributeFilter($attribute_id)
                    ->load();
				
                foreach( $_attrCollection->toOptionArray() as $curentOption ) {		

                    foreach( $_relateCodesCollection->toOptionArray() as $codeOption ) {	

					    if($codeOption['value']==$someOption['value'])
						$curentOptionId=$codeOption['label'];

                    }					

				    if($curentOption['label'] != $values['value']['someoption']['1'] && $curentOptionId == $curentOption['value']) {
					
					    $updateData['option']['value'] = array($curentOption['value']=>$values['value']['someoption']);
						//print_r($updateData);
						//Add data to our attribute model
                        $attr_model->addData($updateData);
                        //Save the updated model
                        try {
                            $attr_model->save();
			
                            $session = Mage::getSingleton('adminhtml/session');
                            //$session->addSuccess(Mage::helper('catalog')->__('The product attribute has been saved.'));

                            /**
                            * Clear translation cache because attribute labels are stored in translation
                            */
                            Mage::app()->cleanCache(array(Mage_Core_Model_Translate::CACHE_TAG));
                            $session->setAttributeData(false);
                        } catch (Exception $e) {
                            $this->addException($e->getMessage());
                            return;
                        }
					    return 1;
					}

                }				
				
				return 0;
			
			}
			
        }

		
		
		
        //Create an array to store the attribute data
        $data = array();

        //Add the option values to the data

        $data['option']= $values;

        //Add data to our attribute model
        $attr_model->addData($data);
        //Save the updated model
        try {
            $attr_model->save();
			
            $session = Mage::getSingleton('adminhtml/session');
            //$session->addSuccess(Mage::helper('catalog')->__('The product attribute has been saved.'));

            /**
            * Clear translation cache because attribute labels are stored in translation
            */
            //Mage::app()->cleanCache(array(Mage_Core_Model_Translate::CACHE_TAG));
            $session->setAttributeData(false);
        } catch (Exception $e) {
        $this->addException($e->getMessage());
        return;
        }
		
		$_savedAttrCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                    ->setStoreFilter(1)
                    ->setAttributeFilter($attribute_id)
                    ->load();

        foreach( $_savedAttrCollection->toOptionArray() as $curentOption ) {

            if($curentOption['label'] == $values['value']['someoption']['1']) {

                $relateValues['value']['someoption']['0'] = $curentOption['value'];
			
			}
			
        }
		
		$dataRelations = array();
		$dataRelations['option']= $relateValues;
		
		//Add data to our attribute model
        $relate_model->addData($dataRelations);
        //Save the updated model
        try {
			$relate_model->save();
            $session = Mage::getSingleton('adminhtml/session');
            //$session->addSuccess(Mage::helper('catalog')->__('The product attribute has been saved.'));

            /**
            * Clear translation cache because attribute labels are stored in translation
            */
            Mage::app()->cleanCache(array(Mage_Core_Model_Translate::CACHE_TAG));
            $session->setAttributeData(false);
            return 2;
        } catch (Exception $e) {
        $this->addException($e->getMessage());
        return;
        }
		
    }

}