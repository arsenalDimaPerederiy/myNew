<?php

class CJM_ColorSelectorPlus_Helper_Data extends Mage_Core_Helper_Abstract
{	
	public function getImageSizes()
	{
		$theSizes = array();
		
		$b = str_replace(" ", "", Mage::getStoreConfig('color_selector_plus/templatesettings/prodimgsize', Mage::app()->getStore()));
		$b = !$b ? '265x265' : strtolower($b);
		$exploder = preg_match("/x/i", $b) ? $b : $b.'x'.$b;
		$sizes = explode("x", $exploder);
        $theSizes['base']['width'] = $sizes[0];
        $theSizes['base']['height'] = $sizes[1];
        
        $m = str_replace(" ", "", Mage::getStoreConfig('color_selector_plus/templatesettings/moreimgsize', Mage::app()->getStore()));
		$m = !$m ? '56x56' : strtolower($m);
		$exploder = preg_match("/x/i", $m) ? $m : $m.'x'.$m;
		$sizes = explode("x", $exploder);
        $theSizes['more']['width'] = $sizes[0];
        $theSizes['more']['height'] = $sizes[1];
        
        $l = str_replace(" ", "", Mage::getStoreConfig('color_selector_plus/templatesettings/listimgsize', Mage::app()->getStore()));
		$l = !$l ? '135x135' : strtolower($l);
		$exploder = preg_match("/x/i", $l) ? $l : $l.'x'.$l;
		$sizes = explode("x", $exploder);
        $theSizes['list']['width'] = $sizes[0];
        $theSizes['list']['height'] = $sizes[1];

  		return $theSizes;
	}
	
	public function getUsesSwatchAttribs($_product)
	{
		$swatch_attributes = Mage::helper('colorselectorplus')->getSwatchAttributes();
		$confAttributes = $_product->getTypeInstance(true)->getConfigurableAttributesAsArray($_product);
        foreach($confAttributes as $confAttribute):				
			$thecode = $confAttribute["attribute_code"];
			if(in_array($thecode, $swatch_attributes)) {
				return 'yes';} 
		endforeach;
		
		return 'no';	
	}
	
	public function getConfigQueryString($_options, $confAttributes)
	{
		$swatch_attributes = Mage::helper('colorselectorplus')->getSwatchAttributes();
		$query = array();
		
		foreach($confAttributes as $confAttribute) :
			if(in_array($confAttribute['attribute_code'], $swatch_attributes))
			{ 
				$attributeCode = $confAttribute['attribute_code'];
				$attributeName = $confAttribute['label'];
					
				foreach($_options as $_option) :
					if($attributeName == $_option['label']) {
						$attributeoption = $_option['value']; }
				endforeach;
						
				if($attributeoption) {
					$query[$attributeCode] = $attributeoption; }
			}
		endforeach;	 
		
		return '?'.http_build_query($query);
	}
	
	public function getSortedByPosition($array)
	{
        $new = '';
        $new1 = '';
        foreach ($array as $k=>$na)
            $new[$k] = serialize($na);
        $uniq = array_unique($new);
        foreach($uniq as $k=>$ser)
            $new1[$k] = unserialize($ser);
        if(isset($new1)){
        	return $new1; }
        else {
        	return '';}
    }
	
	public function getAssociatedOptions($product, $att)
	{
		$swatch_attributes = Mage::helper('colorselectorplus')->getSwatchAttributes();
		$confAttributes = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
        $availattribs = array();
		$thecode = '';
		$html = '';  
       	
		foreach ($confAttributes as $confAttribute) {
			$thecode = $confAttribute["attribute_code"];
			if(in_array($thecode, $swatch_attributes))
			{
				$attributeCode = $confAttribute['attribute_code'];
				$attributeName = $confAttribute['label'];
				$options = $confAttribute["values"];
				
				foreach($options as $option) {
					$string = $option["label"];
					$label = trim(substr($string, 0, strpos("$string#", "#")));
					$optvalue = $option["value_index"]; 
					$availattribs['value'][] = $optvalue;
					$availattribs['label'][] = $label;;							
               	}
			}
		}
		
		if($availattribs) {
			$count = count($availattribs['value']);
		} else {
			$count = 0; }
		
		if($count < 1) {
			$html .= '<select class="'.$att.'" id="'.$att.'__value_id__" name="'.$att.'[__value_id__]" disabled="disabled" style="width:100px;">'; }
		else {
			$html .= '<select class="'.$att.'" id="'.$att.'__value_id__" name="'.$att.'[__value_id__]" style="width:100px;">';
			if($att == 'cjm_moreviews') {
				$html .= '<option value="">';
				$html .= Mage::helper('colorselectorplus')->__('*Always Visible*');
				$html .= '</option>'; 
				$html .= '<option value="none">';
				$html .= Mage::helper('colorselectorplus')->__('*None*');
				$html .= '</option>'; }
			else {
				$html .= '<option value="">&nbsp;</option>'; }
			for($s = 0; $s < $count; $s++) {
    			$html .= '<option value="'.$availattribs['value'][$s].'">'.$availattribs['label'][$s].'</option>'; }
			$html .= '</select><br />'; 
		}

		return $html;	
	}
	
	public function getMoreViews($_product)
	{
		$html = '';
		$html_first = '';
		$onloads = '';
		$defaults = '';
		$defaultmoreviews = 0;
		$product_base = Mage::helper('colorselectorplus')->decodeImages($_product);
		$theSizes = Mage::helper('colorselectorplus')->getImageSizes();
		
		if($product_base):
			$swatch_attributes = Mage::helper('colorselectorplus')->getSwatchAttributes();
			$confAttributes = $_product->getTypeInstance(true)->getConfigurableAttributesAsArray($_product);
			$images = $product_base['image'];
			
			/*foreach($images as $key => $image): 
				if($product_base['defaultimg'][$key] == 1 && $product_base['morev'][$key] != ''):
					$defaultmoreviews = 1;
					$product_image = $product_base['image'][$key];
					$product_thumb = $product_base['thumb'][$key];
					$product_label = $product_base['label'][$key];
					$onloads .= '<li class="onload-moreviews"><a href="'.$product_image.'" onclick="$(\'image\').src = this.href; return false;"><img src="'.$product_thumb.'"  alt="'.$product_label.'" title="'.$product_label.'" /></a></li>';	
				elseif($product_base['morev'][$key] == ''):
					$defaultmoreviews = 1;
					$product_image = $product_base['image'][$key];
					$product_thumb = $product_base['thumb'][$key];
					$product_label = $product_base['label'][$key];
					$defaults .= '<li class="default-moreviews"><a href="'.$product_image.'" onclick="$(\'image\').src = this.href; return false;"><img src="'.$product_thumb.'"  alt="'.$product_label.'" title="'.$product_label.'" /></a></li>';
				endif;
			endforeach;*/
			
			if($defaultmoreviews == 1) {
				$html_first = ''; }
			else {
				$html_first = ''; }
			
			$html = $html_first .= $html;
			
			if($onloads){ $html .= $onloads; }
			if($defaults){ $html .= $defaults; }
		
			foreach($confAttributes as $confAttribute):				
				$thecode = $confAttribute["attribute_code"];
				if(in_array($thecode, $swatch_attributes)): 
					$options = $confAttribute["values"];
					foreach($options as $option):
						$value = $option['value_index'];
						
						$temp_html = '';
						$counter = 0;
						foreach($images as $key => $image):
							if($product_base['morev'][$key] == $value):
								$product_image = $product_base['image'][$key];
								$product_thumb = $product_base['thumb'][$key];
								$product_label = $product_base['label'][$key];
								$product_popup = $product_base['popup'][$key];
								$product_gallery = Mage::helper('colorselectorplus')->getGalUrl($product_base['id'][$key], $_product->getId());
								$temp_html .= '<div class="item"><a href="'.$product_popup.'" class="cloud-zoom-gallery fancybox-more"  onclick="$(\'image\').src = this.href; $(\'cloudZoom\').href = this.href; slideMore(this); return false;"><img src="'.$product_thumb.'"  alt="'.$product_label.'" title="'.$product_label.'" /></a></div>';
								$counter++;
							endif;
						endforeach;
						
						if($counter > 0):
							$html .= '<div  class="moreview'.$value;
							$html .= ($counter > 3) ? ' more-views-product-slider' : ' more-views-product-noslider';
							$html .= '" style="display:none;">';
							$html .= $temp_html;
							$html .= '</div>';
						endif;
						
               		endforeach;
				endif;
			endforeach;
		
			$html .= '';
			return $html;
		else:
			return '';
		endif;
	}


        public function getBaseImage($_product) {

				$baseImage = '';
                if(Mage::getModel('core/cookie')->get("productcolors") != '')
                {
                   $PreSelectedColor = Mage::getModel('core/cookie')->get("productcolors");
                   $ownData = explode("-", $PreSelectedColor); 
                   if ($_product->getId() == $ownData[0])
                   {  
                      $product_base = Mage::helper('colorselectorplus')->decodeImages($_product); //var_dump($product_base['morev']);
                      for($i=0; $i<count($product_base['morev']); $i++)
                      {  
                         if (count($product_base['morev'])>1) {

                             if(($product_base['morev'][$i] == $ownData[1]) && ($product_base['position'][$i] == '1'))
                             {
                                $baseImage = $product_base['image'][$i];
                             }
                         }

                      }
                   }
                   return $baseImage;
                }
                else {
                   return '';
                }
 
           
        }
	
	public function getMoreViewsZoom($_product)
	{
		$html = '';
		$html_first = '';
		$onloads = '';
		$defaults = '';
		$defaultmoreviews = 0;
		$product_base = Mage::helper('colorselectorplus')->decodeImages($_product);
		$theSizes = Mage::helper('colorselectorplus')->getImageSizes();
		
		if($product_base):
			$swatch_attributes = Mage::helper('colorselectorplus')->getSwatchAttributes();
			$confAttributes = $_product->getTypeInstance(true)->getConfigurableAttributesAsArray($_product);
			$images = $product_base['image'];
			
			/*foreach($images as $key => $image): 
				if($product_base['defaultimg'][$key] == 1 && $product_base['morev'][$key] != ''):
					$defaultmoreviews = 1;
					$product_image = $product_base['image'][$key];
					$product_thumb = $product_base['thumb'][$key];
					$product_label = $product_base['label'][$key];
					$onloads .= '<li class="onload-moreviews"><a href="'.$product_image.'" onclick="$(\'image\').src = this.href; return false;"><img src="'.$product_thumb.'"  alt="'.$product_label.'" title="'.$product_label.'" /></a></li>';	
				elseif($product_base['morev'][$key] == ''):
					$defaultmoreviews = 1;
					$product_image = $product_base['image'][$key];
					$product_thumb = $product_base['thumb'][$key];
					$product_label = $product_base['label'][$key];
					$defaults .= '<li class="default-moreviews"><a href="'.$product_image.'" onclick="$(\'image\').src = this.href; return false;"><img src="'.$product_thumb.'"  alt="'.$product_label.'" title="'.$product_label.'" /></a></li>';
				endif;
			endforeach;*/
			
			if($defaultmoreviews == 1) {
				$html_first = ''; }
			else {
				$html_first = ''; }
			
			$html = $html_first .= $html;
			
			if($onloads){ $html .= $onloads; }
			if($defaults){ $html .= $defaults; }
		
			foreach($confAttributes as $confAttribute):				
				$thecode = $confAttribute["attribute_code"];
				if(in_array($thecode, $swatch_attributes)): 
					$options = $confAttribute["values"];
					foreach($options as $option):
						$value = $option['value_index'];
						
						$temp_html = '';
						$counter = 0;
						foreach($images as $key => $image):
							if($product_base['morev'][$key] == $value):
								$product_image = $product_base['image'][$key];
								$product_thumb = $product_base['thumb'][$key];
								$product_label = $product_base['label'][$key];
								//$product_zoom = $product_base['zoom'][$key];
								$product_gallery = Mage::helper('colorselectorplus')->getGalUrl($product_base['id'][$key], $_product->getId());
								$temp_html .= '<div class="item"><a href="'.$product_image.'" rel="popupWin:\''.$product_gallery.'\', useZoom: \'fancyboxImage\', smallImage: \''.$product_image.'\'" class="cloud-zoom-gallery fancybox-more"  onclick="$(\'image\').src = this.href; $(\'cloudZoom\').href = this.href; slideMore(this); return false;"><img height="130px" width="94px" src="'.$product_thumb.'"  alt="'.$product_label.'" title="'.$product_label.'" /></a></div>';
								$counter++;
							endif;
						endforeach;
						
						
						if($counter > 0):
							$html .= '<div  class="moreview'.$value;
							$html .= ($counter > 3) ? ' more-views-product-slider' : ' more-views-product-noslider';
							$html .= '" style="display:none;">';
							$html .= $temp_html;
							$html .= '</div>';
						endif;
						
               		endforeach;
				endif;
			endforeach;
		
			$html .= '';
			return $html;
		else:
			return '';
		endif;
	}
	
	public function getQueryString($_product)
	{
		$swatch_attributes = Mage::helper('colorselectorplus')->getSwatchAttributes();
		$query = array();
		$configurable_product = Mage::getModel('catalog/product_type_configurable');
		$parentIdArray = $configurable_product->getParentIdsByChild($_product->getId());
			
		if($_product->getTypeId() == 'simple' && isset($parentIdArray[0])) {
			foreach($_product->getAttributes() as $_attribute):
				if(in_array($_attribute->getAttributeCode(), $swatch_attributes))
				{ 
					$attributename = $_attribute->getAttributeCode();
					$attributeoption = Mage::getModel('catalog/product')->load($_product->getId())->getAttributeText($attributename);
					if($attributeoption) {
						$query[$attributename] = $attributeoption; }
				}
			endforeach;	 
		}
		
		if($query) {
			return $query; }
		
		return '';
	}
	
	public function getSwatchList()
	{
		$swatch_attributes = Mage::helper('colorselectorplus')->getSwatchAttributes();
		$html = '';
		
		$count = count($swatch_attributes);
		
		for($i = 0; $i < $count; $i++) {
			
			if($i == $count-1) {
				$html .= $swatch_attributes[$i];
			} else {
				$html .= $swatch_attributes[$i].'&nbsp;&#8226;&nbsp;';
			}
		}
		return $html;
	}
	
	public function getSwatchAttributes()
	{
		$swatch_attributes = array();
		$swatchattributes = Mage::getStoreConfig('color_selector_plus/colorselectorplusgeneral/colorattributes',Mage::app()->getStore());
		$swatch_attributes = explode(",", $swatchattributes);
		
		 foreach($swatch_attributes as &$attribute) {
         	$attribute = Mage::getModel('eav/entity_attribute')->load($attribute)->getAttributeCode();
		 }
		 unset($attribute);
	
		return $swatch_attributes;
	}
	
	public function getSwatchSize($theCode)
	{
		if($theCode == 'list'):
			$swatchsize = str_replace(" ", "", Mage::getStoreConfig('color_selector_plus/colorselectorplusgeneral/listsize', Mage::app()->getStore()));
			if (!$swatchsize){
				$swatchsize = '12x12';}
			if(preg_match("/x/i", $swatchsize)){
				return $swatchsize;}
			else {
				return $swatchsize.'x'.$swatchsize;}
			return $swatchsize;
		elseif($theCode == 'shopby'):
			$swatchsize = str_replace(" ", "", Mage::getStoreConfig('color_selector_plus/colorselectorplusgeneral/layersize', Mage::app()->getStore()));
			if (!$swatchsize){
				$swatchsize = '15x15';}
			if(preg_match("/x/i", $swatchsize)){
				return $swatchsize;}
			else {
				return $swatchsize.'x'.$swatchsize;}
			return $swatchsize;
		elseif($theCode != 'null'):
			$swatchsize = str_replace(" ", "", Mage::getStoreConfig('color_selector_plus/swatchsizes/swatchsize_'.$theCode.'_swatchsizes', Mage::app()->getStore()));
			if($swatchsize){
				if(preg_match("/x/i", $swatchsize)){
					return $swatchsize;}
				else {
					return $swatchsize.'x'.$swatchsize;}
			} else {
				$swatchsize = str_replace(" ", "", Mage::getStoreConfig('color_selector_plus/colorselectorplusgeneral/size',Mage::app()->getStore()));
				if (!$swatchsize){
					$swatchsize = '25x25';}
				if(preg_match("/x/i", $swatchsize)){
					return $swatchsize;}
				else {
					return $swatchsize.'x'.$swatchsize;}
				return $swatchsize;
			}
		else:
			$swatchsize = str_replace(" ", "", Mage::getStoreConfig('color_selector_plus/colorselectorplusgeneral/size',Mage::app()->getStore()));
			if (!$swatchsize){
				$swatchsize = '25x25';}
			if(preg_match("/x/i", $swatchsize)){
				return $swatchsize;}
			else {
				return $swatchsize.'x'.$swatchsize;}
			return $swatchsize;
		endif;
	}
	
	public function findColorImage($value, $arr, $key, $type)
	{
		$found = '';
		if(isset($arr[$key])) {
 			$total = count($arr[$key]);
			if($total>0)
 			{
				for($i=0; $i<$total;$i++)
 				{
					if($value == ucwords($arr[$key][$i]))//if it matches the color listed in the attribute
 					{
 						$found = $arr[$type][$i];//return the image src
					}
 				}
 			}
		}
 		return $found;
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

        public function getIconListHtml($_product, $_currentColor)
        {
		
			$usedOptions = $this->getIconListProducts($_product);

			$html = '';
			$filredColor = array();
			if(count($usedOptions)>1){
				foreach($usedOptions as $key => $option) {
				
					if(in_array($option['value_index'], $_currentColor))
					{
						$filredColor[] = $option['value_index'];
					} 
					else {
						$html .= '<li><img width="15px" height="15px" id="prod'.$_product->getId().'swatch'.$option['value_index'].'" src="'.$this->getSwatchUrl($option['value_index']).'"/></li>';
					}
				
				}
			}
			
			foreach($_currentColor as $key=>$color){
				
				if(in_array($color, $filredColor))
				$html = '<li><img width="15px" height="15px" id="prod'.$_product->getId().'swatch'.$color.'" src="'.$this->getSwatchUrl($color).'"/></li>'.$html;
				
			}
			
			$html = '<ul class="color-icon">'.$html.'</ul>';

           return $html;
        }
		
        public function getIconListProducts($_product) 
        {
            if($_product->getTypeId() == "configurable" && Mage::getStoreConfig('color_selector_plus/colorselectorplusgeneral/showonlist', Mage::app()->getStore()))
            {

                $_attributes = $_product->getTypeInstance(true)->getConfigurableAttributesAsArray($_product); 
                if(sizeof($_attributes))
                {
				
					$_simple = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null, $_product);
					foreach($_simple as $simple_product){
						if($simple_product->isSalable()){

						} else {

							foreach($_attributes['0']['values'] as $key => $color){

								if ($color['value_index'] == $simple_product->getColor()){
									unset($_attributes['0']['values'][$key]);
									
								}
							
							}
						}
					} 
				
                }

            }

            return $_attributes[0]['values'];
        }


		
		public function getCurrentColor()
		{
			if(Mage::registry('current_category')) {
				$_filters = Mage::getSingleton('catalog/layer')->getState()->getFilters();
			} elseif(Mage::registry('splash_page')) {
				$_filters = Mage::getSingleton('splash/layer')->getState()->getFilters();
			} else {
				$_filters = Mage::getSingleton('catalogsearch/layer')->getState()->getFilters();
			}

			$currentColor=NULL;
			if (($page = Mage::registry('splash_page')) !== null) {
				if (is_array($optionFilters = $page->getOptionFilters())) {
					foreach($optionFilters as $attribute => $data) {
						if (is_array($_filters)){
							foreach($data['value'] as $value) {
								$currentColor[] = $value;
							}
						} else {
							$currentColor[] = $data['value'];
						}

					}
				}
			}
			
			if (is_array($_filters)):
				foreach ($_filters as $_filter): 
				if ($_filter->getFilter()->getAttributeModel()->getAttributeCode() == "color"):
					//  echo $_filter->getValueString()."+++";
					$currentColor[] = $_filter->getValueString();
				endif;
				endforeach;
			endif;



		   return $currentColor;
		}
			
        public function getSwatchIconLabel($colorId)
        {
			$swatchUrl = $this->getSwatchUrl($colorId);
			$productModel = Mage::getModel('catalog/product');
			$attr = $productModel->getResource()->getAttribute("color");

			if ($attr->usesSource()) {
				$color_label = $attr->getSource()->getOptionText($colorId);
			}
			$html = '<div class = "swatch-icon"><img src="'.$swatchUrl.'" width="12px" height="12px" alt="'.$color_label.'"/></div>';
			$html .= '<div class = "swatch-label">'.$color_label.'</div>';
    
           return $html;
        }		

        public function getSwatchesListHtml($_product) 
        {
            if($_product->getTypeId() == "configurable" && Mage::getStoreConfig('color_selector_plus/colorselectorplusgeneral/showonlist', Mage::app()->getStore()))
            {

                $_attributes = $_product->getTypeInstance(true)->getConfigurableAttributesAsArray($_product); 
                if(sizeof($_attributes))
                {
				
					$_simple = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null, $_product);

					foreach($_simple as $simple_product){
						if($simple_product->isSalable()){

						} else {

							foreach($_attributes['0']['values'] as $key => $color){

								if ($color['value_index'] == $simple_product->getColor()){
									unset($_attributes['0']['values'][$key]);
									
								}
							
							}
						}

					} 

				
                    $className = 'CJM_ColorSelectorPlus_Block_Listswatch';
                    $block = new $className();               
                    $html = $block->getListSwatchHtml($_attributes, $_product, count($_attributes['0']['values']));
                }

            }

            return $html;
        }



        public function getFilterImage($_product)
        {

            if($_product->getTypeId() == "configurable" && Mage::getStoreConfig('color_selector_plus/colorselectorplusgeneral/showonlist', Mage::app()->getStore())) 
            {
            	if($this->getCurrentColor())
                {
              	    $_attributes = $_product->getTypeInstance(true)->getConfigurableAttributesAsArray($_product);
            
                    if(sizeof($_attributes))
                    {
                        $productId = $_product->getId();
                        foreach($_attributes as $_attribute)
                        {
                            $colorId = $this->colorbyfilter($this->getCurrentColor(),$_attribute['values']);
							$product = Mage::getModel('catalog/product')->load($_product->getId());
                            $product_base = $this->decodeImagesForList($product);

							$imageLink = Mage::helper('colorselectorplus')->findColorImage($colorId,$product_base,'color', 'image');//returns url for base image 
                        }
                    }
            
                 }
            }
            return $imageLink;
        }
		
       public function getBaseColorId($_product)
        {

            if($_product->getTypeId() == "configurable" && Mage::getStoreConfig('color_selector_plus/colorselectorplusgeneral/showonlist', Mage::app()->getStore())) 
            {

				$product = Mage::getModel('catalog/product')->load($_product->getId());
				$product_basecolor_id = $this->decodeImagesForListLight($product);

            }
            return $product_basecolor_id;
        }
	
        public function decodeImagesForList($_product)
	{
		$_gallery = $_product->getMediaGalleryImages(); 
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
					$product_base['full'][] 		= strval(Mage::helper('catalog/image')->init($_product, 'image', $_image->getFile()));
					$product_base['popup'][] 		= strval(Mage::helper('catalog/image')->init($_product, 'image', $_image->getFile())->resize(300,386));
					$product_base['image'][] 		= strval(Mage::helper('catalog/image')->init($_product, 'image', $_image->getFile())->resize($theSizes['list']['width'],$theSizes['list']['height']));
					$product_base['thumb'][] 		= strval(Mage::helper('catalog/image')->init($_product, 'thumbnail', $_image->getFile())->resize($theSizes['more']['width'],$theSizes['more']['height']));
					$product_base['morev'][] 		= strval($imgIdsMoreViews[$_image['value_id']]['moreviews']);
					$product_base['label'][] 		= $_image['label'];
					$product_base['id'][] 			= $_image['value_id'];
					$product_base['position'][] 			= $_image['position'];
					$product_base['defaultimg'][] 	= $_image['defaultimg'];
				endforeach;
			
			endif;
		
			return $product_base;
		
		else:
			
			return '';
		
		endif;		
	}
	
    public function decodeImagesForListLight($_product)
	{
		$_gallery = $_product->getMediaGalleryImages(); 
		
		if(count($_gallery) >= 1):
		
			$imgIdsBase = array();
			$imgIdsMoreViews = array();
			$product_base = array();
			
			$cjm_colorselector_base = unserialize($_product->getData('cjm_imageswitcher'));

			if($cjm_colorselector_base):
				
				foreach($cjm_colorselector_base as $cjm_colorselectorItem => $cjm_colorselectorVal):
					$imgIdsBase[$cjm_colorselectorItem]['colorval'] = $cjm_colorselectorVal;
				endforeach;

				foreach ($_gallery as $_image ):  
					if(strcmp($_product->getImage(),$_image->getFile()) == 0) {
						return strval($imgIdsBase[$_image['value_id']]['colorval']);
					}
				endforeach;
			
			endif;
		
			return false;
		
		else:
			
			return '';
		
		endif;			
	}

	public function decodeImages($_product)
	{
		$_gallery = $_product->getMediaGalleryImages(); 
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
					$product_base['full'][] 		= strval(Mage::helper('catalog/image')->init($_product, 'image', $_image->getFile()));
					$product_base['popup'][] 		= strval(Mage::helper('catalog/image')->init($_product, 'image', $_image->getFile())->resize(300,386));
					$product_base['image'][] 		= strval(Mage::helper('catalog/image')->init($_product, 'image', $_image->getFile())->resize($theSizes['base']['width'],$theSizes['base']['height']));
					$product_base['thumb'][] 		= strval(Mage::helper('catalog/image')->init($_product, 'thumbnail', $_image->getFile())->resize($theSizes['more']['width'],$theSizes['more']['height']));
					//$product_base['zoom'][] 		= strval(Mage::helper('catalog/image')->init($_product, 'thumbnail', $_image->getFile())->resize(1875,1285));
					$product_base['morev'][] 		= strval($imgIdsMoreViews[$_image['value_id']]['moreviews']);
					$product_base['label'][] 		= $_image['label'];
					$product_base['id'][] 			= $_image['value_id'];
					$product_base['position'][] 			= $_image['position'];
					$product_base['defaultimg'][] 	= $_image['defaultimg'];
				endforeach;
			
			endif;
		
			return $product_base;
		
		else:
			
			return '';
		
		endif;	
	}
	
	public function getSwatchUrl($optionId)
    {
        $uploadDir = Mage::getBaseDir('media') . DIRECTORY_SEPARATOR . 
                                                    'colorselectorplus' . DIRECTORY_SEPARATOR . 'swatches' . DIRECTORY_SEPARATOR;
        if (file_exists($uploadDir . $optionId . '.jpg'))
        {
            return Mage::getBaseUrl('media') . '/' . 'colorselectorplus' . '/' . 'swatches' . '/' . $optionId . '.jpg';
        }
        return '';
    }

	public function getSwatchHtml($attributeCode, $atid, $_product)
	{
		$storeId = Mage::app()->getStore();
		$frontendlabel = 'null';
		$urloption = '';
		$html = '';
		$cnt = 1;
		$_option_vals = array();				
		$_colors = array();
		$zoomenabled = Mage::getStoreConfig('color_selector_plus/zoom/enabled');
		$hide = Mage::getStoreConfig('color_selector_plus/colorselectorplusgeneral/hidedropdown', $storeId);
		$frontText = Mage::getStoreConfig('color_selector_plus/colorselectorplusgeneral/dropdowntext', $storeId);
		$swatchsize = Mage::helper('colorselectorplus')->getSwatchSize($attributeCode);
		$sizes = explode("x", $swatchsize);

                $width = $sizes[0];
                $height = $sizes[1];
                
                if(isset($_GET[$attributeCode])) {
                	$urloption = $_GET[$attributeCode]; }
        		
        		if($hide == 0) {
        			$html = $html.'<div class="swatchesContainerPadded"><ul id="ul-attribute'.$atid.'">'; }
        		else {
        			$html = $html.'<div class="swatchesContainer"><ul id="ul-attribute'.$atid.'">'; }			
                        				
                $_collection = Mage::getResourceModel('eav/entity_attribute_option_collection')->setPositionOrder('asc')->setAttributeFilter($atid)->setStoreFilter(0)->load();
        	
        		foreach( $_collection->toOptionArray() as $_cur_option ) {
        
        			$_option_vals[$_cur_option['value']] = array(
        				'internal_label' => $_cur_option['label'],
        				'order' => $cnt
        			);
        			$cnt++;
                }
        
                $product_base = Mage::helper('colorselectorplus')->decodeImages($_product);
        
                $configAttributes = $_product->getTypeInstance(true)->getConfigurableAttributesAsArray($_product);
            	foreach($configAttributes as $attribute) { 
        			if($attribute['attribute_code'] == $attributeCode) {
                                        $i=1;
        				foreach($attribute["values"] as $value) {
                                            $position = 0;
                                            foreach($product_base['color'] as $key => $colorId) {

                                                if($colorId == $value['value_index']) $position = $key;

                                            }

                 			array_push($_colors, array(
                 				'id' => $value['value_index'],
                 				'frontlabel' => $value['store_label'],
                 				'adminlabel' => $_option_vals[$value['value_index']]['internal_label'],
                 				'order' => $product_base['id'][$position] //$_option_vals[$value['value_index']]['order']
                 			));
                                        $i++;
                 		}
                 		break;
                 	}
             	}
			
		$_color_swatch = Mage::helper('colorselectorplus')->getSortedByPosition($_colors);

		$_color_swatch = array_values($_color_swatch);

		foreach ($_color_swatch as $key => $val) { 
   			 $sortSingle[$key] = $_color_swatch[$key]['order']; } 

		asort ($sortSingle); 
		reset ($sortSingle); 
		
		while (list ($singleKey, $singleVal) = each ($sortSingle)) { 
    		$newArr[] = $_color_swatch[$singleKey]; } 

		$_color_swatch = $newArr;  


		foreach($_color_swatch as $_inner_option_id)
 		{       

			$zoomStuff = '';
			$theId = $_inner_option_id['id'];
			$adminLabel = $_inner_option_id['adminlabel'];
			$altText = $_inner_option_id['frontlabel'];
                        $seoAltText = $this->__('Photo - %s, %s. Product code: %s', trim(str_replace(',', '', $_product->getName())),$altText,$_product->getSku());
			if($frontText == 0) {
				$frontendlabel = $altText; }
			else {
				$frontendlabel = 'null'; }
			
			preg_match_all('/((#?[A-Za-z0-9]+))/', $adminLabel, $matches);
				
			if ( count($matches[0]) > 0 )
			{
				$color_value = $matches[1][count($matches[0])-1];
				$findme = '#';
				$pos = strpos($color_value, $findme);
				
					
				$product_image = Mage::helper('colorselectorplus')->findColorImage($theId, $product_base, 'color', 'image'); //returns url for base image
				//$product_zoom = Mage::helper('colorselectorplus')->findColorImage($theId, $product_base, 'color', 'zoom'); //returns url for base image
				
				if($zoomenabled && $product_image):
					$product_image_full = Mage::helper('colorselectorplus')->findColorImage($theId, $product_base, 'color', 'image'); 
					$product_gallery = Mage::helper('colorselectorplus')->getGalUrl(Mage::helper('colorselectorplus')->findColorImage($theId, $product_base, 'color', 'id'), $_product->getId());
					$zoomStuff = '<a href="'.$product_image.'" id="anchor'.$theId.'"   title="'.$frontendlabel.'">';
				endif;

                                $product_image_small = Mage::helper('colorselectorplus')->findColorImage($theId, $product_base, 'color', 'thumb'); //returns url for thumb image
				
				if($urloption==$altText)
				{
					$html = $html.'<script type="text/javascript">Event.observe';
					$html = $html."(window, 'load', function() {";
					$html = $html."colorSelected('attribute".$atid."','".$theId."','".$product_image_small."','".$frontendlabel."');clicker(".$theId.");});</script>";
				}
					$product_test_image =  $_product->getMediaConfig()->getMediaUrl($_product->getData('image'));					
              	if ($_product->getCjm_useimages() == 1 && $product_image_small):
					$html = $html.'<li class="swatchContainer">';
					$html = $zoomStuff ? $html.$zoomStuff : $html;
					$html = $html.'<img src="'.$product_image_small.'" id="swatch'.$theId.'" class="swatch" alt="'.$seoAltText.'" width="'.$width.'px" height="'.$height.'px" title="'.$altText.'" ';
					$html = $html.'onclick="colorSelected';
					$html = $html."('attribute".$atid."','".$theId."','".$product_image_full."','".$frontendlabel."'); return false;";
					$html = $html.'" />';
					$html = $zoomStuff ? $html.'</a></li>' : $html.'</li>';
              	elseif(Mage::helper('colorselectorplus')->getSwatchUrl($theId) && $attributeCode == 'product_size'):
					$swatchimage = Mage::helper('colorselectorplus')->getSwatchUrl($theId);
					$html = $html.'<li class="swatchContainer">';
					$html = $zoomStuff ? $html.$zoomStuff : $html;
					$html = $html.'<span id="swatch'.$theId.'" class="swatch" alt="'.$seoAltText.'"  title="'.$altText.'" ';
					$html = $html.'onclick="colorSelected';
					$html = $html."('attribute".$atid."','".$theId."','".$product_image_full."','".$frontendlabel."'); return false;";
					$html = $html.'" >'.$altText.'</span>';
					$html = $zoomStuff ? $html.'</a></li>' : $html.'</li>';
              	elseif(Mage::helper('colorselectorplus')->getSwatchUrl($theId)):
					$swatchimage = Mage::helper('colorselectorplus')->getSwatchUrl($theId);
					$html = $html.'<li class="swatchContainer">';
					$html = $zoomStuff ? $html.$zoomStuff : $html;
					$html = $html.'<img src="'.$swatchimage.'" id="swatch'.$theId.'" class="swatch" alt="'.$seoAltText.'" width="'.$width.'px" height="'.$height.'px" title="'.$altText.'" ';
					$html = $html.'onclick="colorSelected';
					$html = $html."('attribute".$atid."','".$theId."','".$product_image_full."','".$frontendlabel."'); return false;";
					$html = $html.'" />';
					$html = $zoomStuff ? $html.'</a></li>' : $html.'</li>';
				elseif($pos !== false):
              		$html = $html.'<li class="swatchContainer">';
					$html = $zoomStuff ? $html.$zoomStuff : $html;
					$html = $html.'<div id="swatch'.$theId.'" title="'.$altText.'" class="swatch" style="background-color:'.$color_value.'; width:'.$width.'px; height:'.$height.'px;" ';
					$html = $html.' onclick="colorSelected';
					$html = $html."('attribute".$atid."','".$theId."','".$product_image_full."','".$frontendlabel."'); return false;";
					$html = $html.'">';
					$html = $zoomStuff ? $html.'</div></a></li>' : $html.'</div></li>';
         			elseif(isset($product_test_image) && !empty($product_test_image)):
					$html = $html.'<li class="swatchContainer">';
					$html = $zoomStuff ? $html.$zoomStuff : $html;
					$html = $html.'<img src="'.$product_test_image.'" id="swatch'.$theId.'" class="swatch" alt="'.$seoAltText.'" width="'.$width.'px" height="'.$height.'px" title="'.$altText.'" ';
					$html = $html.'onclick="colorSelected';
					$html = $html."('attribute".$atid."','".$theId."','".$product_test_image."','".$frontendlabel."'); return false;";
					$html = $html.'" />';
					$html = $zoomStuff ? $html.'</a></li>' : $html.'</li>';
				else:
					$swatchimage = Mage::helper('colorselectorplus')->getSwatchUrl('empty');
					$html = $html.'<li class="swatchContainer">';
					$html = $zoomStuff ? $html.$zoomStuff : $html;
					$html = $html.'<img src="'.$swatchimage.'" id="swatch'.$theId.'" class="swatch" alt="'.$seoAltText.'" width="'.$width.'px" height="'.$height.'px" title="'.$altText.' - Please Upload Swatch!" ';
					$html = $html.'onclick="colorSelected';
					$html = $html."('attribute".$atid."','".$theId."','".$product_image_small."','".$frontendlabel."'); return false;";
					$html = $html.'" />';
					$html = $zoomStuff ? $html.'</a></li>' : $html.'</li>';
				endif;
			}
 		}
		$html = $html.'</ul></div><p class="float-clearer"></p>';
		return $html;	
	}
	
	public function getSwatchImg($option)
    {
        return Mage::helper('colorselectorplus')->getSwatchUrl($option->getId());
    }
	
	public function getAttribOptions()
    {
   		$optionCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')->setAttributeFilter(Mage::registry('entity_attribute')->getId())->setPositionOrder('asc', true)->load();
        return $optionCollection;
    }
	
	public function gettheUrl()
    {
       $pageURL = 'http';
 		
 		if(isset($_SERVER["HTTPS"])) {
 			if ($_SERVER["HTTPS"] == "on") {
				$pageURL .= "s";}
		}
 		
 		$pageURL .= "://";
 		
 		if ($_SERVER["SERVER_PORT"] != "80") {
  			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 		} else {
  			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 		}
		
		return $pageURL;
    }
    
	public function getShopByHtml($_item)
	{
		$html = '';
		$swatch_attributes = Mage::helper('colorselectorplus')->getSwatchAttributes();
		$ss = Mage::helper('colorselectorplus/data')->getSwatchSize('shopby');
		$sizes = explode("x", $ss);
        $width = $sizes[0];
        $height = $sizes[1];
		$theAttribute = $_item['code'];
		$theUrl = $_item['url'];
		$theImage = $_item['image'];
		$theBGcolor = $_item['bgcolor'];
		$theLabel = $_item['label'].$_item['count'];
		
		if(in_array($theAttribute, $swatch_attributes) && Mage::getStoreConfig('color_selector_plus/colorselectorplusgeneral/showonlayer',Mage::app()->getStore()) == 1):
			if($theImage != ''):
        		$html = '<a href="'.$theUrl.'"><img class="swatch-shopby" src="'.$theImage.'" title="'.$theLabel.'" alt="'.$theLabel.'" style="width:'.$width.'px; height:'.$height.'px;"></a>';
      		elseif($theBGcolor != ''):
       			$html = '<a href="'.$theUrl.'">';
				$html .= '<div class="swatch-shopby" style="background-color:'.$theBGcolor.'; width:'.$width.'px; height:'.$height.'px;" title="'.$theLabel.'"></div>';
				$html .= '</a>';
			else:
				$html = '<a href="'.$theUrl.'" class="swatch-shopby-text">'.$theLabel.'</a>';
			endif;
		else:
			$html =  '<a href="'.$theUrl.'">'.$_item['label'].'</a>'.$_item['count'];
		endif;
		
		return $html;
	}
	
	public function getZoomOptions()
    {
        $options = '';
        
        $width = Mage::getStoreConfig('color_selector_plus/zoom/width');
        if (empty($width) || !is_numeric($width)) { $width = 'auto'; }
        
        $height = Mage::getStoreConfig('color_selector_plus/zoom/height');
        if (empty($height) || !is_numeric($height)) { $height = 'auto'; }
        
        $options .= "zoomWidth: '" . $width . "',";
        $options .= "zoomHeight: '" . $height . "',";
        $options .= "position: '" . Mage::getStoreConfig('color_selector_plus/zoom/position') . "',";
        $options .= "smoothMove: " . (int) Mage::getStoreConfig('color_selector_plus/zoom/smoothmove') . ",";
        $options .= "showTitle: " . Mage::getStoreConfig('color_selector_plus/zoom/title') . ",";
        $options .= "titleOpacity: " . (float) (Mage::getStoreConfig('color_selector_plus/zoom/titleopacity')/100) . ",";

        $adjustX = (int) Mage::getStoreConfig('color_selector_plus/zoom/xoffset');
        if ($adjustX > 0) { $options .= "adjustX: " . $adjustX . ","; }
        
        $adjustY = (int) Mage::getStoreConfig('color_selector_plus/zoom/yoffset');
        if ($adjustY > 0) { $options .= "adjustY: " . $adjustY . ","; }

        $options .= "lensOpacity: " . (float) (Mage::getStoreConfig('color_selector_plus/zoom/lensopacity')/100) . ",";

        $tint = Mage::getStoreConfig('color_selector_plus/zoom/tint');
        if (!empty($tint)) { $options .= "tint: '" . (Mage::getStoreConfig('color_selector_plus/zoom/softfocus') == 'true' ? '' : Mage::getStoreConfig('color_selector_plus/zoom/tint')) . "',"; }
        
        $options .= "tintOpacity: " . (float) (Mage::getStoreConfig('color_selector_plus/zoom/tintopacity')/100) . ",";
        $options .= "softFocus: " . Mage::getStoreConfig('color_selector_plus/zoom/softfocus') . "";

        return $options;
    }
    
   	public function getGalUrl($image=null, $prodId)
    {
        $params = array('id' => $prodId);
        if ($image) {
            $params['image'] = $image;
            return Mage::getUrl('*/*/gallery', $params);
        }
        return Mage::getUrl('*/*/gallery', $params);
    }
	
	

	// TO DO -- DISPLAYS SELECT BOXES FOR EACH SWATCH ATTRIBUTE
	//public function getAssociatedOptions($product)
//	{
//		$swatch_attributes = Mage::helper('colorselectorplus')->getSwatchAttributes();
//		$confAttributes = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
//      $availattribs = array();
//		$thecode = '';
//		$thename = '';
//		$html = '';   	
//		
//		foreach ($confAttributes as $confAttribute) {
//			$thename = $confAttribute["label"];
//			$thecode = $confAttribute["attribute_code"];
//			if(in_array($thecode, $swatch_attributes))
//			{
//           		$html .= '<label>'.$thename.'</label>';
//				$html .= '<select class="imageSwitch" id="imageSwitch__value_id__" name="imageSwitch[__value_id__]" style="width:100px;">';
//				$html .= '<option value="">&nbsp;</option>';
//              	$options = $confAttribute["values"];
//				foreach($options as $option) {
//    				$string = $option["label"];
//					$result = trim(substr($string, 0, strpos("$string#", "#")));
//					$availattribs[] = $result;
//                    $html .= '<option value="'.$result.'">'.$result.'</option>';
//				}
//				$html .= '</select><br />';
//			}
//		}
//		
//		return $html;	
//	}
}