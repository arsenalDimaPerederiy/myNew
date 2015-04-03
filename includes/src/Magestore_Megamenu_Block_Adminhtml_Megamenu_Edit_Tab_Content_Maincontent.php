<?php

class Magestore_Megamenu_Block_Adminhtml_Megamenu_Edit_Tab_Content_Maincontent extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        if (Mage::getSingleton('adminhtml/session')->getMegamenuData()) {
            $data = Mage::getSingleton('adminhtml/session')->getMegamenuData();
            Mage::getSingleton('adminhtml/session')->setMegamenuData(null);
        } elseif (Mage::registry('megamenu_data'))
            $data = Mage::registry('megamenu_data')->getData();
        
        $fieldset = $form->addFieldSet('megamenu_maincontent', array('legend' => Mage::helper('megamenu')->__('Main Content')));
     
        if( isset($data['categories_box_title']) && !$data['categories_box_title']){
            $data['categories_box_title'] = Mage::helper('megamenu')->__('Categories');
        }
        $fieldset->addField('categories_box_title','text', array(
            'label' =>  Mage::helper('megamenu')->__('Categories Box Title'),
            'index' =>  'categories_box_title',
            'name'  => 'categories_box_title'
        ));
        if(isset($data['products_box_title']) && !$data['products_box_title']){
           $data['products_box_title'] = Mage::helper('megamenu')->__('Products'); 
        }
        $fieldset->addField('products_box_title','text', array(
            'label' =>  Mage::helper('megamenu')->__('Products Box Title'),
            'index' =>  'products_box_title',
            'name'  => 'products_box_title',
            'value' =>  'Products'
        ));
        $categoryIds = implode(", ", Mage::getResourceModel('catalog/category_collection')->addFieldToFilter('level', array('gt' => 0))->getAllIds());
        if(!isset($data['categories'])){
            $data['categories'] = $categoryIds;
        }
        $fieldset->addField('categories', 'text', array(
            'label' => Mage::helper('megamenu')->__('Categories'),
            'name' => 'categories',
            'after_element_html' => '<a id="category_link" href="javascript:void(0)" onclick="toggleMainCategories()"><img src="' . $this->getSkinUrl('images/rule_chooser_trigger.gif') . '" alt="" class="v-middle rule-chooser-trigger" title="Select Categories"></a>
                <div  id="categories_check" style="display:none">
                    <a href="javascript:toggleMainCategories(1)">Check All</a> / <a href="javascript:toggleMainCategories(2)">Uncheck All</a>
                </div>
                <div id="main_categories_select" style="display:none"></div>
                    <script type="text/javascript">
                    function toggleMainCategories(check){
                        var cate = $("main_categories_select");
                        if($("main_categories_select").style.display == "none" || (check ==1) || (check == 2)){
                            $("categories_check").style.display ="";
                            var url = "' . $this->getUrl('megamenuadmin/adminhtml_megamenu/chooserMainCategories') . '";
                            if(check == 1){
                                $("categories").value = $("category_all_ids").value;
                            }else if(check == 2){
                                $("categories").value = "";
                            }
                            var params = $("categories").value.split(", ");
                            var parameters = {"form_key": FORM_KEY,"selected[]":params };
                            var request = new Ajax.Request(url,
                                {
                                    evalScripts: true,
                                    parameters: parameters,
                                    onComplete:function(transport){
                                        $("main_categories_select").update(transport.responseText);
                                        $("main_categories_select").style.display = "block"; 
                                    }
                                });
                        if(cate.style.display == "none"){
                            cate.style.display = "";
                        }else{
                            cate.style.display = "none";
                        } 
                    }else{
                        cate.style.display = "none";
                        $("categories_check").style.display ="none";
                    }
                };
		</script>
            '
        ));
        $productIds = implode(", ", Mage::getResourceModel('catalog/product_collection')->getAllIds());
        $fieldset->addField('products', 'text', array(
            'label' => Mage::helper('megamenu')->__('Products'),
            'name' => 'products',
            'class' => 'rule-param',
            'after_element_html' => '<a id="product_link" href="javascript:void(0)" onclick="toggleMainProducts()"><img src="' . $this->getSkinUrl('images/rule_chooser_trigger.gif') . '" alt="" class="v-middle rule-chooser-trigger" title="Select Products"></a><input type="hidden" value="'.$productIds.'" id="product_all_ids"/><div id="main_products_select" style="display:none;width:640px"></div>
                <script type="text/javascript">
                    function toggleMainProducts(){
                        if($("main_products_select").style.display == "none"){
                            var url = "' . $this->getUrl('megamenuadmin/adminhtml_megamenu/chooserMainProducts') . '";
                            var params = $("products").value.split(", ");
                            var parameters = {"form_key": FORM_KEY,"selected[]":params };
                            var request = new Ajax.Request(url,
                            {
                                evalScripts: true,
                                parameters: parameters,
                                onComplete:function(transport){
                                    $("main_products_select").update(transport.responseText);
                                    $("main_products_select").style.display = "block"; 
                                }
                            });
                        }else{
                            $("main_products_select").style.display = "none";
                        }
                    };
                    var grid;
                   
                    function constructData(div){
                        grid = window[div.id+"JsObject"];
                        if(!grid.reloadParams){
                            grid.reloadParams = {};
                            grid.reloadParams["selected[]"] = $("products").value.split(", ");
                        }
                    }
                    function toogleCheckAllProduct(el){
                        if(el.checked == true){
                            $$("#main_products_select input[type=checkbox][class=checkbox]").each(function(e){
                                if(e.name != "check_all"){
                                    if(!e.checked){
                                        if($("products").value == "")
                                            $("products").value = e.value;
                                        else
                                            $("products").value = $("products").value + ", "+e.value;
                                        e.checked = true;
                                        grid.reloadParams["selected[]"] = $("products").value.split(", ");
                                    }
                                }
                            });
                        }else{
                            $$("#main_products_select input[type=checkbox][class=checkbox]").each(function(e){
                                if(e.name != "check_all"){
                                    if(e.checked){
                                        var vl = e.value;
                                        if($("products").value.search(vl) == 0){
                                            if($("products").value == vl) $("products").value = "";
                                            $("products").value = $("products").value.replace(vl+", ","");
                                        }else{
                                            $("products").value = $("products").value.replace(", "+ vl,"");
                                        }
                                        e.checked = false;
                                        grid.reloadParams["selected[]"] = $("products").value.split(", ");
                                    }
                                }
                            });
                            
                        }
                    }
                    function selectProduct(e) {
                        if(e.checked == true){
                            if(e.id == "main_on"){
                                $("products").value = $("product_all_ids").value;
                            }else{
                                if($("products").value == "")
                                    $("products").value = e.value;
                                else
                                    $("products").value = $("products").value + ", "+e.value;
                                grid.reloadParams["selected[]"] = $("products").value.split(", ");
                            }
                        }else{
                             if(e.id == "main_on"){
                                $("products").value = "";
                            }else{
                                var vl = e.value;
                                if($("products").value.search(vl) == 0){
                                    $("products").value = $("products").value.replace(vl+", ","");
                                }else{
                                    $("products").value = $("products").value.replace(", "+ vl,"");
                                }
                            }
                        }
                        
                    }
                </script>'
        ));
        
        $form->setValues($data);
        return parent::_prepareForm();
    }

    public function getLoadUrl() {
        return $this->getUrl('*/*/chooser');
    }

}