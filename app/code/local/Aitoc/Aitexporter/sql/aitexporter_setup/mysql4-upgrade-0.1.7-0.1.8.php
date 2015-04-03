<?php
/**
 * Orders Export and Import
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitexporter
 * @version      1.2.9
 * @license:     ou1zlIlUK4jGhUJZLohhJ5b8jdvumX7FXHqMPgZHkF
 * @copyright:   Copyright (c) 2015 AITOC, Inc. (http://www.aitoc.com)
 */
$installer = $this;


$installer->startSetup();

$installer->run('

ALTER TABLE `'.$this->getTable('aitexporter_import').'` 
    ADD COLUMN `add_count` MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
    ADD COLUMN `update_count` MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
    ADD COLUMN `fail_count` MEDIUMINT UNSIGNED NOT NULL DEFAULT 0;

');

$installer->endSetup();