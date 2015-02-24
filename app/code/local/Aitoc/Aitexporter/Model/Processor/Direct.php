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
class Aitoc_Aitexporter_Model_Processor_Direct extends Aitoc_Aitexporter_Model_Processor
{
    /**
    * This class is small update for our process system to divide checkout/invoice exports from cron, because cron may block exewcuting profiles on this events. 
    * Most probably this will require refactoring whole process system.
    */
    protected $_configModel = 'aitexporter/processor_direct_config';    
    
    /**
    * Direct process can't be blocked by configuration
    * 
    */
    public function isBusy()
    {
        return false;
    }
    
    
}