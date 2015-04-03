<?php

$installer = $this;

$installer->startSetup();
$installer->run("

  ALTER TABLE   `{$this->getTable('qbanner/qbanner_image')}`  add column link varchar(100) after `label`;
    ");

$installer->endSetup();
