<?php

class Magestore_Megamenu_Block_Adminhtml_Megamenu_Edit_Tab_Content_Featureditem extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        if (Mage::getSingleton('adminhtml/session')->getMegamenuData()) {
            $data = Mage::getSingleton('adminhtml/session')->getMegamenuData();
            Mage::getSingleton('adminhtml/session')->setMegamenuData(null);
        } elseif (Mage::registry('megamenu_data'))
            $data = Mage::registry('megamenu_data')->getData();

        $fieldset = $form->addFieldSet('megamenu_featuredcategories', array('legend' => Mage::helper('megamenu')->__('Featured Item')));
        $categoryBlock = $this->getLayout()->createBlock(
                        'megamenu/adminhtml_megamenu_edit_tab_content_featureditem_categories', 'featured_category', array('js_form_object' => 'megamenu_featuredcategories')
                )
                ->setCategoryIds(array());
        $productBlock = $this->getLayout()->createBlock(
                        'megamenu/adminhtml_megamenu_edit_tab_content_featureditem_products', 'featured_product', array('js_form_object' => 'megamenu_featuredcategories')
                )
                ->setProductIds(array());
        $categoryIds = implode(", ", Mage::getResourceModel('catalog/category_collection')->addFieldToFilter('level', array('gt' => 0))->getAllIds());
        $fieldset->addField('featured_type', 'select', array(
            'label' => Mage::helper('megamenu')->__('Featured Type'),
            'name' => 'featured_type',
            'onchange' => 'changeType()',
            'values' => Mage::helper('megamenu')->getFeaturedTypes(),
            'after_element_html' => '
                <input type="hidden" value="' . $categoryIds . '" id="category_all_ids" />
                <script type="text/javascript">
                    var count = 1;
                    var fieldset = new VarienRulesForm("megamenu_featuredproducts");
                    function toggleFeaturedCategories(check){
                        count = count + 1;
                        if($("featured_categories_select").style.display == "none" || (check ==1) || (check == 2)){
                            $("featured_categories_check").style.display ="";
                            var url = "' . $this->getUrl('megamenuadmin/adminhtml_megamenu/chooserCategories') . '";
                            if(check == 1){
                                $("featured_categories").value = $("category_all_ids").value;
                            }else if(check == 2){
                                $("featured_categories").value = "";
                            }
                            var params = $("featured_categories").value.split(", ");
                            var parameters = {"form_key": FORM_KEY,"selected[]":params };
                            var request = new Ajax.Request(url,
                                {
                                    evalScripts: true,
                                    parameters: parameters,
                                    onComplete:function(transport){
                                        $("featured_categories_select").update(transport.responseText);
                                        $("featured_categories_select").style.display = "block"; 
                                    }
                                }
                            );
                        }else{
                              $("featured_categories_select").style.display = "none";
                              $("featured_categories_check").style.display ="none";
                        }
                    };
                </script>'
                )
        );
        $type = 0;
        if (isset($data['featured_type']) && $data['featured_type']) {
            $type = $data['featured_type'];
        }
        if(isset($data['featured_categories_box_title']) && !$data['featured_categories_box_title']){
            $data['featured_categories_box_title'] = Mage::helper('megamenu')->__('Categories');
        }
        $fieldset->addField('featured_categories_box_title', 'text', array(
            'label' => Mage::helper('megamenu')->__('Featured Box Title'),
            'index' => 'featured_categories_box_title',
            'name' => 'featured_categories_box_title'
        ));
        if(isset($data['featured_categories_box_title']) && !$data['featured_products_box_title']){
            !$data['featured_products_box_title'] = Mage::helper('megamenu')->__('Products');
        }
        $fieldset->addField('featured_products_box_title', 'text', array(
            'label' => Mage::helper('megamenu')->__('Featured Box Title'),
            'index' => 'featured_products_box_title',
            'name' => 'featured_products_box_title'
        ));
        $fieldset->addField('featured_categories', 'text', array(
            'label' => Mage::helper('megamenu')->__('Featured Categories'),
            'name' => 'featured_categories',
            'disabled' => 'disabled',
            'after_element_html' => '<a id="category_link" href="javascript:void(0)" onclick="toggleFeaturedCategories()"><img src="' . $this->getSkinUrl('images/rule_chooser_trigger.gif') . '" alt="" class="v-middle rule-chooser-trigger" title="Select Categories"></a>
            <div  id="featured_categories_check" style="display:none">
                <a href="javascript:toggleFeaturedCategories(1)">Check All</a> / <a href="javascript:toggleFeaturedCategories(2)">Uncheck All</a>
            </div>
            <div id="featured_categories_select" style="display:none">
            </div>
                '
        ));
        $productIds = implode(", ", Mage::getResourceModel('catalog/product_collection')->getAllIds());
        $fieldset->addField('featured_products', 'text', array(
            'label' => Mage::helper('megamenu')->__('Featured Products'),
            'name' => 'featured_products',
            'class' => 'rule-param',
            'disabled' => 'disabled',
            'after_element_html' => '<a id="item_product_link" href="javascript:void(0)" onclick="toggleFeaturedProducts()"><img src="' . $this->getSkinUrl('images/rule_chooser_trigger.gif') . '" alt="" class="v-middle rule-chooser-trigger" title="Select Products"></a><input type="hidden" value="' . $productIds . '" id="item_product_all_ids"/><div id="featured_products_select" style="display:none;width:640px"></div>
		<script type="text/javascript">
                    function toggleFeaturedProducts(){
                        if($("featured_products_select").style.display == "none"){
                        var url = "' . $this->getUrl('megamenuadmin/adminhtml_megamenu/chooserFeaturedProducts') . '";
                        var params = $("featured_products").value.split(", ");
                        var parameters = {"form_key": FORM_KEY,"selected[]":params };
                        var request = new Ajax.Request(url,
                            {
                                evalScripts: true,
                                parameters: parameters,
                                onComplete:function(transport){
                                    $("featured_products_select").update(transport.responseText);
                                    $("featured_products_select").style.display = "block"; 
                                }
                            });
                            }else{
                                $("featured_products_select").style.display = "none";
                            }
                    };
                     var featured_grid;
                    function constructFeaturedData(div){
                        featured_grid = window[div.id+"JsObject"];
                        if(!featured_grid.reloadParams){
                            featured_grid.reloadParams = {};
                            featured_grid.reloadParams["selected[]"] = $("featured_products").value.split(", ");
                        }
                    }
                    function selectFeaturedProduct(e) {
                        if(e.checked == true){
                            if(e.id == "featured_on"){
                                $("featured_products").value = $("item_product_all_ids").value;
                            }else{
                                if($("featured_products").value == "")
                                    $("featured_products").value = e.value;
                                else
                                    $("featured_products").value = $("featured_products").value + ", "+e.value;
                            }
                            featured_grid.reloadParams["selected[]"] = $("featured_products").value.split(", ");
                        }else{
                             if(e.id == "featured_on"){
                                $("featured_products").value = "";
                            }else{
                                var vl = e.value;
                                if($("featured_products").value.search(vl) == 0){
                                    $("featured_products").value = $("featured_products").value.replace(vl+", ","");
                                }else{
                                    $("featured_products").value = $("featured_products").value.replace(", "+ vl,"");
                                }
                            }
                        }
                    }
                    changeType();
                    function changeType(){
                        if($("featured_type").value == "2"){
                            $("featured_categories").disabled = false;
                            $("featured_products").disabled = true;
                            $("featured_products").up().up().style.display = "none";
                            $("featured_categories").up().up().style.display = "";
                            
                            $("featured_products_box_title").up().up().style.display = "none";
                            $("featured_categories_box_title").up().up().style.display = "";
                            
                            $("category_link").style.display = "";
                            $("product_link").style.display = "none";
                        }else if($("featured_type").value == "1"){
                            $("featured_products").disabled = false;
                            $("featured_categories").disabled = true;
                            $("featured_categories").up().up().style.display = "none";
                            $("featured_products").up().up().style.display = "";
                            
                            $("featured_categories_box_title").up().up().style.display = "none";
                            $("featured_products_box_title").up().up().style.display = "";

                            $("category_link").style.display = "none";
                            $("product_link").style.display = "";
                        }else{
                            $("featured_categories").disabled = true;
                            $("featured_products").disabled = true;
                            $("featured_products").up().up().style.display = "none";
                            $("featured_categories").up().up().style.display = "none";
                            
                            $("featured_products_box_title").up().up().style.display = "none";
                            $("featured_categories_box_title").up().up().style.display = "none";
                            
                            $("category_link").style.display = "none";
                            $("product_link").style.display = "none";
                        }
                    }
                </script>'
        ));

        $form->setValues($data);
        return parent::_prepareForm();
    }

}