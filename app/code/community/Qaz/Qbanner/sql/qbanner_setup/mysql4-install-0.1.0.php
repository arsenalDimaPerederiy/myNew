<?php

$installer = $this;

$installer->startSetup();
$installer->run("

   DROP TABLE IF EXISTS {$this->getTable('qbanner/qbanner')};
CREATE TABLE {$this->getTable('qbanner/qbanner')} (
  `qbanner_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
 `width` smallint(4),
    `height` smallint(4),
    `duration` INT NOT NULL DEFAULT '5000' ,
    `effect` VARCHAR( 30 ) NOT NULL,
    `show_caption` TINYINT( 4 ) NOT NULL ,
    `show_pagination` TINYINT( 4 ) NOT NULL,
    `auto_slide` TINYINT( 4 ) NOT NULL ,
    `mouseover_stop` TINYINT( 4 ) NOT NULL,
  `position` smallint (4) ,
  `status` smallint(6) NOT NULL default '0',
 `show_in` VARCHAR( 100 ) NOT NULL,
  PRIMARY KEY (`qbanner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  DROP TABLE IF EXISTS `{$this->getTable('qbanner/qbanner_image')}`;
CREATE TABLE `{$this->getTable('qbanner/qbanner_image')}` (
  `image_id` smallint(6) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `position` smallint(5) DEFAULT '0',
  `disabled` tinyint(1) DEFAULT '1',
  `qbanner_id` smallint(6) DEFAULT '0',
  PRIMARY KEY (`image_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Qbanner Image' ;

  DROP TABLE IF EXISTS `{$this->getTable('qbanner/qbanner_category')}`;
CREATE TABLE `{$this->getTable('qbanner/qbanner_category')}` (
  `qbanner_id` smallint(6) NOT NULL,
  `category_id` smallint(6) NOT NULL,
  PRIMARY KEY (`qbanner_id`,`category_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Qbanner Category' ;

  DROP TABLE IF EXISTS `{$this->getTable('qbanner/qbanner_page')}`;
CREATE TABLE `{$this->getTable('qbanner/qbanner_page')}` (
  `qbanner_id` smallint(6) NOT NULL,
  `page_id` smallint(6) NOT NULL,
  PRIMARY KEY (`qbanner_id`,`page_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Qbanner Page' ;

   DROP TABLE IF EXISTS `{$this->getTable('qbanner/qbanner_store')}`;
CREATE TABLE `{$this->getTable('qbanner/qbanner_store')}` (
  `qbanner_id` smallint(6) NOT NULL,
  `store_id` smallint(6) NOT NULL,
  PRIMARY KEY (`qbanner_id`,`store_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Qbanner Store' ;

    ");

$installer->endSetup();
