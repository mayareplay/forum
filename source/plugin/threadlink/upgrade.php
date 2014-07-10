<?php
/*
 *	Author: IAN - zhouxingming
 *	Last modified: 2011-10-17 16:09
 *	Filename: upgrade.php
 *	Description: 
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<SQL

ALTER TABLE pre_threadlink_link CHANGE pic pic TEXT NOT NULL,
CHANGE aid aid VARCHAR(255) NOT NULL;

ALTER TABLE pre_threadlink_link ADD displayorder SMALLINT(4) UNSIGNED NOT NULL;

ALTER TABLE pre_threadlink_link DROP INDEX tid,
ADD UNIQUE tid (tid, pid);

ALTER TABLE  pre_threadlink_link ADD authorid MEDIUMINT(8) UNSIGNED NOT NULL AFTER pid,
ADD author VARCHAR(15) NOT NULL AFTER authorid,
ADD fid MEDIUMINT(8) UNSIGNED NOT NULL AFTER author,
ADD publisher MEDIUMINT(8) UNSIGNED NOT NULL AFTER author;

ALTER TABLE pre_threadlink_base ADD maker MEDIUMINT(8) UNSIGNED NOT NULL;

CREATE TABLE pre_threadlink_stat (
  daytime char(10) NOT NULL,
  create_base int(10) unsigned NOT NULL,
  modify_link int(10) unsigned NOT NULL,
  create_link int(10) unsigned NOT NULL,
  clicks int(10) unsigned NOT NULL,
  PRIMARY KEY  (`daytime`)
) ENGINE=MyISAM;
SQL;

runquery($sql);
$finish = 1;
?>
