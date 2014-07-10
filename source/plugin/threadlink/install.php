<?php
/*
 *	Author: IAN - zhouxingming
 *	Last modified: 2011-09-07 10:38
 *	Filename: install.php
 *	Description: 
 */

if(!defined('IN_DISCUZ')) {
	exit('Access denied');
}

$sql = <<<SQL
CREATE TABLE pre_threadlink_base (
  `tid` mediumint(8) unsigned NOT NULL,
  `pid` int(10) unsigned NOT NULL,
  `tltpl` char(100) NOT NULL,
  `maxrow` smallint(4) unsigned NOT NULL,
  `subject` varchar(80) NOT NULL,
  `dateline` int(10) NOT NULL,
  `summarylength` smallint(6) NOT NULL,
  `picwidth` smallint(3) unsigned NOT NULL default '100',
  `picheight` smallint(3) unsigned NOT NULL default '100',
  `maker` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`tid`)
) ENGINE=MyISAM;

CREATE TABLE pre_threadlink_link (
  `lid` mediumint(8) unsigned NOT NULL auto_increment,
  `tid` mediumint(8) unsigned NOT NULL,
  `pid` int(10) unsigned NOT NULL,
  `authorid` mediumint(8) unsigned NOT NULL,
  `author` varchar(15) NOT NULL,
  `publisher` mediumint(8) unsigned NOT NULL,
  `fid` mediumint(8) unsigned NOT NULL,
  `subject` varchar(80) NOT NULL,
  `message` text NOT NULL,
  `url` varchar(255) NOT NULL,
  `pic` text NOT NULL,
  `aid` varchar(255) NOT NULL,
  `displayorder` smallint(4) unsigned NOT NULL,
  PRIMARY KEY  (`lid`),
  UNIQUE KEY `tid` (`tid`,`pid`)
) ENGINE=MyISAM;
CREATE TABLE pre_threadlink_stat (
  `daytime` char(10) NOT NULL,
  `create_base` int(10) unsigned NOT NULL,
  `modify_link` int(10) unsigned NOT NULL,
  `create_link` int(10) unsigned NOT NULL,
  `clicks` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`daytime`)
) ENGINE=MyISAM;
SQL;

runquery($sql);
$finish = 1;
?>
