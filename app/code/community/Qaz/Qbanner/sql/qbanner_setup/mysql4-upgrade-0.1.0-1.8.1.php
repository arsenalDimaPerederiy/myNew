<?php

$installer = $this;

$installer->startSetup();
$installer->run("
  ALTER TABLE   {$this->getTable('qbanner/qbanner')} add column show_next_prev  TINYINT( 4 )  after `show_pagination`;
    ");

$installer->endSetup();
