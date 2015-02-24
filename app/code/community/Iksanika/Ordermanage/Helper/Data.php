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

class Iksanika_Ordermanage_Helper_Data 
    extends Mage_Core_Helper_Abstract 
{
    
    protected static $imagesWidth = null;
    protected static $imagesHeight = null;
    protected static $imagesScale = null;
    
    protected static $includeProducts = null;
    protected static $showProducts = null;
    
    protected static $statusList = null;
    
    public function getImageUrl($image_file)
    {
        $url = false;
        $url = Mage::getBaseUrl('media').'catalog/product'.$image_file;
        return $url;
    }
    
    public function getFileExists($image_file)
    {
        $file_exists = false;
        $file_exists = file_exists('media/catalog/product'. $image_file);
        return $file_exists;
    }
    
    protected static function initSettings()
    {
        
        if(!self::$imagesWidth)
            self::$imagesWidth = Mage::getStoreConfig('ordermanage/images/width');
        if(!self::$imagesHeight)
            self::$imagesHeight = Mage::getStoreConfig('ordermanage/images/height');
        if(!self::$imagesScale)
            self::$imagesScale = Mage::getStoreConfig('ordermanage/images/scale');
        
        if(!self::$includeProducts)
            self::$includeProducts = Mage::getStoreConfig('ordermanage/products/includeproducts');
        if(!self::$showProducts)
            self::$showProducts = Mage::getStoreConfig('ordermanage/products/showproducts');
        
    }
    
    public function getImage($orderItem)
    {
        self::initSettings();
        
        //if($orderItem->getProductType() == 'configurable') 
        //{
          //  $productItem = $orderItem->getProduct();
			
        //}else
        //{
            $productItem = Mage::getModel('catalog/product')->load($orderItem->getProductId()); 
			
        //}

        if($productItem && $productItem->getData('small_image') && ($productItem->getData('small_image') != 'no_selection') && ($productItem->getData('small_image') != ""))
        {
            $outImagesWidth = self::$imagesWidth ? "width='".self::$imagesWidth."'":'';
            if(self::$imagesScale)
                $outImagesHeight = (self::$imagesHeight) ? "height='".self::$imagesHeight."'":'';
            else
                $outImagesHeight = (self::$imagesHeight && !self::$imagesWidth) ? "height='".self::$imagesHeight."'":'';

            return '<img src="'.(Mage::helper("catalog/image")->init($productItem, "small_image")->resize(self::$imagesWidth)).'" '.$outImagesWidth.' '.$outImagesHeight.' alt="" />';
        }else
        {
            return '[NO IMAGES]';
        }
    }

    
    public function getStatusesByState($filter = '')
    {
        // status, label, state, is_default
        if(!self::$statusList)
        {
            $collection = Mage::getModel('sales/order_status')->getCollection()->joinStates();
            self::$statusList = $collection->load();
        }
        if(self::$statusList && !empty(self::$statusList))
        {
            $returnStatusList = array();
            foreach(self::$statusList as $statusItem)
            {
                if($statusItem['state'] == $filter || $filter == '')
                {
                    $returnStatusList[$statusItem['status']] = $statusItem['label'];
                }
            }
            return $returnStatusList;
        }else
            return array();
    }    
    
    
    
}