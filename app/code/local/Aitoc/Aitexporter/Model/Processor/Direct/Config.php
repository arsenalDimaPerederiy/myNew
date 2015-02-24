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
class Aitoc_Aitexporter_Model_Processor_Direct_Config extends Aitoc_Aitexporter_Model_Processor_Config
{
    protected $_config_path = 'sales/aitexporter/direct_iterator';
    
    /**
     * Init resource table and prevend loading default config
     */
    protected function _construct()
    {
        $this->_init('aitexporter/processor_direct_config');
        #$this->load($this->_config_path, 'path');
    }
    
    /**
    * Prevent saving this model
    * 
    */
    public function save() {
        return $this;
    }
	
}