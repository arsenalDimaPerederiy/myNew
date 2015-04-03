<?php

class CJM_ColorSelectorPlus_Block_Listswatch extends Mage_Core_Block_Template
{
	protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('colorselectorplus/listswatches.phtml');
        if(Mage::app()->useCache('swatch')):
        	$this->addData(array(
        		'cache_lifetime'	=> 9999999999
        	));
        else:
        	$this->addData(array(
        		'cache_lifetime'	=> null
        	));
        endif;
    }
    
    public function getCacheTags()
    {
       return array(Mage_Catalog_Model_Product::CACHE_TAG . "_" . $this->getProduct()->getId(), 'swatch');
    }

    public function getCacheKey()
    {
        return 'SWATCH' . $this->getProduct()->getId();
    }
    
    protected function _toHtml()
    {
    	return parent::_toHtml();
    }
	
	public function getMediaGalleryByProduct($_product) {
		$_mediaGalleryAttributeId = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'media_gallery')->getAttributeId();
		$_read = Mage::getSingleton('core/resource')->getConnection('catalog_read');
		
		$_mediaGalleryData = $_read->fetchAll('
			SELECT
				main.entity_id, `main`.`value_id`, `main`.`value` AS `file`,
				`value`.`label`, `value`.`position`, `value`.`disabled`, `default_value`.`label` AS `label_default`,
				`default_value`.`position` AS `position_default`,
				`default_value`.`disabled` AS `disabled_default`
			FROM `catalog_product_entity_media_gallery` AS `main`
				LEFT JOIN `catalog_product_entity_media_gallery_value` AS `value`
					ON main.value_id=value.value_id AND value.store_id=' . Mage::app()->getStore()->getId() . '
				LEFT JOIN `catalog_product_entity_media_gallery_value` AS `default_value`
					ON main.value_id=default_value.value_id AND default_value.store_id=0
			WHERE (
				main.attribute_id = ' . $_read->quote($_mediaGalleryAttributeId) . ') 
				AND (main.entity_id = ' . $_product->getId() . ')
			ORDER BY IF(value.position IS NULL, default_value.position, value.position) ASC    
		');
		return $_mediaGalleryData;
	} 
    
    public function decodeImagesForList($_product)
	{
		$_gallery = $this->getMediaGalleryByProduct($_product); 
		$theSizes = Mage::helper('colorselectorplus')->getImageSizes();

		if(count($_gallery) >= 1):

			$imgIdsBase = array();
			$imgIdsMoreViews = array();
			$product_base = array();
			
			$cjm_colorselector_base = unserialize($_product->getData('cjm_imageswitcher'));
			$cjm_colorselector_more = unserialize($_product->getData('cjm_moreviews'));

			if($cjm_colorselector_base && $cjm_colorselector_more):

				foreach($cjm_colorselector_base as $cjm_colorselectorItem => $cjm_colorselectorVal):
					$imgIdsBase[$cjm_colorselectorItem]['colorval'] = $cjm_colorselectorVal;
				endforeach;
				
				foreach($cjm_colorselector_more as $cjm_colorselectorItem => $cjm_colorselectorVal):
					$imgIdsMoreViews[$cjm_colorselectorItem]['moreviews'] = $cjm_colorselectorVal;
				endforeach;
				
				foreach ($_gallery as $_image ):  
 					$product_base['color'][] 		= strval($imgIdsBase[$_image['value_id']]['colorval']);
					$product_base['full'][] 		= strval(Mage::helper('catalog/image')->init($_product, 'image', $_image['file']));
					$product_base['image'][] 		= strval(Mage::helper('catalog/image')->init($_product, 'image', $_image['file'])->resize($theSizes['list']['width'],$theSizes['list']['height']));
					$product_base['thumb'][] 		= strval(Mage::helper('catalog/image')->init($_product, 'thumbnail', $_image['file'])->resize($theSizes['more']['width'],$theSizes['more']['height']));
					$product_base['morev'][] 		= strval($imgIdsMoreViews[$_image['value_id']]['moreviews']);
					$product_base['label'][] 		= $_image['label'];
					$product_base['id'][] 			= $_image['value_id'];
					$product_base['position'][] 			= $_image['position_default'];
					$product_base['defaultimg'][] 	= $_image['defaultimg'];
				endforeach;
			
			endif;

			return $product_base;
		
		else:
			
			return '';
		
		endif;		
	}
    
    public function getListSwatchAttributes()
	{
		$swatch_attributes = array();
		$swatchattributes = Mage::getStoreConfig('color_selector_plus/colorselectorplusgeneral/toshow',Mage::app()->getStore());
		$swatch_attributes = explode(",", $swatchattributes);
		
		 foreach($swatch_attributes as &$attribute) {
         	$attribute = Mage::getModel('eav/entity_attribute')->load($attribute)->getAttributeCode();
		 }
		 unset($attribute);
	
		return $swatch_attributes;
	}

    public function getCurrentColor()
    {
	  if(Mage::registry('current_category')) {
		   $_filters = Mage::getSingleton('catalog/layer')->getState()->getFilters();
	   } else {
		   $_filters = Mage::getSingleton('catalogsearch/layer')->getState()->getFilters();
	   }

       $currentColor=NULL;
       if (is_array($_filters)):
         foreach ($_filters as $_filter): 
           if ($_filter->getFilter()->getAttributeModel()->getAttributeCode() == "color"):
             $currentColor[] = $_filter->getValueString();
           endif;
         endforeach;
       endif;

       if (($page = Mage::registry('splash_page')) !== null) {
           if (is_array($optionFilters = $page->getOptionFilters())) {
		foreach($optionFilters as $attribute => $data) {

                    $currentColor = $data['value'];

                }
           }
       }

       return $currentColor;
    }

    public function colorSort(array $_checked, array $_base)
    {
      $find=0;
      $_checked=array_reverse($_checked);
         foreach ($_checked as $currentColor):
             foreach ($_base as $baseColorsKey => $baseColor):
               if ($currentColor == $baseColor['value_index']):
                  $temp = $_base[$find];
                  $_base[$find] = $baseColor;
                  $_base[$baseColorsKey]=$temp;
                  $find++;
               endif;
             endforeach;
         endforeach;

      return $_base;
    }

    public function colorbyfilter(array $_checked, array $_base)
    {
      $find=0;
      //$_checked=array_reverse($_checked);
         foreach ($_checked as $currentColor):
             foreach ($_base as $baseColorsKey => $baseColor):
               if ($currentColor == $baseColor['value_index'] && $find==0):
                  $filterColor = $currentColor;
               endif;
             endforeach;
         endforeach;

      return $filterColor;
    }

    public function getListSwatchHtml($_attributes, $_product, $available)
    {
		$html = '';
		$swatchesShown=0;
		$productId = $_product->getId();
        $currentColor=NULL;
		$swatch_attributes = $this->getListSwatchAttributes();
        $selectedColor = $this->getCurrentColor();

		foreach($_attributes as $_attribute){
			
			if(in_array($_attribute['attribute_code'], $swatch_attributes)){
				
				$_option_vals = array();
				$attName = $_attribute['label'];
 				$swatchsize = Mage::helper('colorselectorplus/data')->getSwatchSize('list');
 				$sizes = explode("x", $swatchsize);

				$width = $sizes[0];
				$height = $sizes[1];
				//$_product = Mage::getModel('catalog/product')->load($productId);
		
				$attrid = $_attribute['attribute_id'];
				$attributed = Mage::getModel('eav/config')->getAttribute('catalog_product', $_attribute['attribute_code']);	
		 		
				foreach($attributed->getSource()->getAllOptions(true, true) as $option){
					$_option_vals[$option['value']] = array( 'internal_label' => $option['label'] );
				}
 				
 				//$html .= '<span class="swatchLabel-category">'.$attName.':</span><p class="float-clearer"></p>';
 				$html .= '<div class="swatch-category-container" style="clear:both;" id="ul-attribute'.$attrid.'-'.$productId.'">';
                
                              

				if ($selectedColor){
					$_attribute['values'] = $this->colorSort($selectedColor, $_attribute['values']);
				}

				if (!$selectedColor)
				{

					$product_base = $this->decodeImagesForList($_product);	

					foreach($_attribute["values"] as $_key => $value) {

						foreach($product_base['color'] as $key => $colorId) {

							if($colorId == $value['value_index']) $_attribute["values"][$_key]['order'] = $product_base['id'][$key];

						}
					}
					
					
					foreach($_attribute["values"] as $_key => $_value) {

						foreach($_attribute["values"] as $key => $value) {

							if($_attribute["values"][$_key]['order'] < $_attribute["values"][$key]['order']) {
								$temp = $_attribute["values"][$_key];
								$_attribute["values"][$_key] = $_attribute["values"][$key];
								$_attribute["values"][$key] = $temp;
							}

						}
					}
				}
                    
				$swatchSliderClass = ($available > 4) ? '' : '-none';
				$html .= '<ul id="mycarousel" class="jcarousel'.$swatchSliderClass.' jcarousel-skin-tango">';

				foreach($_attribute['values'] as $value){

        			$theId = $value['value_index'];
                                if (!$currentColor):
                                   $currentColor = $theId;
                                endif;
					$altText = $value['store_label'];
					$adminLabel = $_option_vals[$value['value_index']]['internal_label'];
			
					preg_match_all('/((#?[A-Za-z0-9]+))/', $adminLabel, $matches);
				
					if (count($matches[0]) > 0){
					
						$color_value = $matches[1][count($matches[0])-1];
						$findme = '#';
						$pos = strpos($color_value, $findme);
						
                        if ($selectedColor)
						$product_base = $this->decodeImagesForList($_product);	

						$product_image = Mage::helper('colorselectorplus')->findColorImage($theId,$product_base,'color', 'image');//returns url for base image

                                               
						//$product_image = Mage::helper('catalog/image')->init($_product, 'image')->resize(700,700);
						$extra_view="";
						$more_views = $product_base ;

						foreach($more_views['morev'] as $image_key => $more_images):

						  if ($more_images==$theId):  
							 if ($more_views['position'][$image_key]=='2') {  
								 $extra_view=$more_views['image'][$image_key];

							 }
						  endif;

						endforeach;

						if (empty($extra_view)) {
							$extra_view=$product_image;
						}

						$product_test_image =  $_product->getMediaConfig()->getMediaUrl($_product->getData('image'));
                                                              
						if ($_product->getCjm_useimages() == 1 && $product_image):
							$html = $html.'<li><img src="'.$product_image.'" id="a33'.$attrid.'-'.$theId.'" class="swatch-category" alt="'.$altText.'" width="'.$width.'px" height="'.$height.'px" title="'.$altText.'" ';
                                                        $html = $html.'onclick="listSwitcher';
							$html = $html."(this,'".$productId."','".$product_image."','".$attrid."','".$extra_view."', '".$theId."')".'"';
                                                        $html = $html.' onmouseover="listSwitcher';
							$html = $html."(this,'".$productId."','".$product_image."','".$attrid."','".$extra_view."', '".$theId."')";
							$html = $html.'" /></li>';
						elseif (Mage::helper('colorselectorplus')->getSwatchUrl($theId)):
							$swatchimage = Mage::helper('colorselectorplus')->getSwatchUrl($theId);
							$html .= '<li><img onclick="listSwitcher(';
							$html .= "this,'".$productId."','".$product_image."','".$attrid."','".$extra_view."', '".$theId."');".'"';
							$html .= ' onmouseover="listSwitcher(';
							$html .= "this,'".$productId."','".$product_image."','".$attrid."','".$extra_view."', '".$theId."');";
							$html .= '" src="'.$swatchimage.'" id="a'.$attrid.'-'.$theId.'" class="swatch-category" alt="'.$altText.'" width="'.$width.'px" height="'.$height.'px" title="'.$altText.'" /></li>';
						elseif($pos !== false):
							$html .= '<div onclick="listSwitcher(';
							$html .= "this,'".$productId."','".$product_image."','".$attrid."','".$extra_view."', '".$theId."');".'"';
							$html .= ' onmouseover="listSwitcher(';
							$html .= "this,'".$productId."','".$product_image."','".$attrid."','".$extra_view."', '".$theId."');";
							$html .= '" id="a'.$attrid.'-'.$theId.'" class="swatch-category" style="background-color:'.$color_value.'; width:'.$width.'px; height:'.$height.'px;" title="'.$altText.'">';
							$html .= '</div>';
						elseif(isset($product_test_image) && !empty($product_test_image)):

						else:

						endif;

					
					}
 				    $swatchesShown++;
 				}
 					
 				$html .= '</ul></div>';


 			}

 		}

//$html .= "<script type=\"text/javascript\"> changecolor('".$productId."', '".$attrid."', '".$currentColor."'); </script>";
		 						
		return $html;
	}
}