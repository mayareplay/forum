<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: install.php 8889 2011-11-15 12:48:22Z Niexinyuan $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$tablepre = !empty($_G['config']['db'][1]['tablepre']) ? $_G['config']['db'][1]['tablepre'] : 'pre_';

$sql = <<<EOF

CREATE TABLE `{$tablepre}xplus_common_attachment` (
  `aid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `authorid` mediumint(8) unsigned NOT NULL,
  `filesize` int(10) unsigned NOT NULL,
  `type` varchar(255) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `dateline` int(10) NOT NULL,
  PRIMARY KEY (`aid`),
  KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

CREATE TABLE `{$tablepre}xplus_common_config` (
  `ckey` varchar(255) NOT NULL,
  `cvalue` text NOT NULL,
  PRIMARY KEY (`ckey`)
) ENGINE=MyISAM;

CREATE TABLE `pre_xplus_common_template` (
  `templateid` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL DEFAULT '',
  `directory` varchar(100) NOT NULL DEFAULT '',
  `available` tinyint(1) NOT NULL,
  `mid` smallint(6) unsigned NOT NULL,
  `copyright` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`templateid`),
  KEY `mid` (`mid`,`available`),
  KEY `available` (`available`),
  KEY `templateid` (`templateid`)
) ENGINE=MyISAM;

CREATE TABLE `{$tablepre}xplus_common_module` (
  `mid` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `version` varchar(255) NOT NULL,
  `apikey` varchar(255) NOT NULL,
  `available` tinyint(3) NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`mid`),
  UNIQUE KEY `identifier` (`identifier`)
) ENGINE=MyISAM;

CREATE TABLE `{$tablepre}xplus_form_attachment` (
  `aid` mediumint(8) unsigned NOT NULL,
  `formid` mediumint(8) unsigned NOT NULL,
  `fieldid` mediumint(8) unsigned NOT NULL,
  `valueid` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`aid`),
  KEY `formid` (`formid`,`fieldid`,`valueid`),
  KEY `fieldid` (`fieldid`)
) ENGINE=MyISAM;

CREATE TABLE `{$tablepre}xplus_form_field` (
  `fieldid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `classid` smallint(6) unsigned NOT NULL DEFAULT '0',
  `displayorder` tinyint(3) NOT NULL DEFAULT '0',
  `expiration` tinyint(1) NOT NULL,
  `protect` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `identifier` varchar(255) NOT NULL DEFAULT '',
  `type` varchar(255) NOT NULL DEFAULT '',
  `unit` varchar(255) NOT NULL,
  `rules` mediumtext NOT NULL,
  PRIMARY KEY (`fieldid`),
  UNIQUE KEY `identifier` (`identifier`),
  KEY `displayorder` (`displayorder`),
  KEY `classid` (`classid`,`displayorder`)
) ENGINE=MyISAM;

CREATE TABLE `{$tablepre}xplus_form_field_class` (
  `classid` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `displayorder` tinyint(3) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`classid`),
  KEY `displayorder` (`displayorder`)
) ENGINE=MyISAM;

CREATE TABLE `{$tablepre}xplus_form_item` (
  `formid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `valuenum` smallint(6) unsigned NOT NULL DEFAULT '1',
  `templateid` smallint(6) unsigned NOT NULL DEFAULT '1',
  `available` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `allowguest` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `seccode` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `dateline` int(10) unsigned NOT NULL,
  `starttime` int(10) unsigned NOT NULL,
  `endtime` int(10) unsigned NOT NULL,
  `title` char(80) NOT NULL DEFAULT '',
  `username` char(15) NOT NULL DEFAULT '',
  PRIMARY KEY (`formid`),
  KEY `dateline` (`dateline`),
  KEY `username` (`username`,`dateline`)
) ENGINE=MyISAM;

CREATE TABLE `{$tablepre}xplus_form_item_count` (
  `formid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `count` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`formid`)
) ENGINE=MyISAM;

CREATE TABLE `{$tablepre}xplus_form_item_field` (
  `formid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `description` text NOT NULL,
  `seokeywords` varchar(255) NOT NULL,
  `seodesc` varchar(255) NOT NULL,
  PRIMARY KEY (`formid`)
) ENGINE=MyISAM;

CREATE TABLE `{$tablepre}xplus_form_item_var` (
  `formid` mediumint(8) NOT NULL DEFAULT '0',
  `fieldid` mediumint(8) NOT NULL DEFAULT '0',
  `available` tinyint(1) NOT NULL DEFAULT '0',
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `search` tinyint(1) NOT NULL DEFAULT '0',
  `displayorder` tinyint(3) NOT NULL DEFAULT '0',
  `listshow` tinyint(1) NOT NULL DEFAULT '0',
  UNIQUE KEY `formid_fieldid` (`formid`,`fieldid`),
  KEY `formid` (`formid`,`displayorder`)
) ENGINE=MyISAM;

CREATE TABLE `{$tablepre}xplus_form_setting` (
  `skey` varchar(255) NOT NULL DEFAULT '',
  `stype` varchar(255) NOT NULL DEFAULT '',
  `svalue` text NOT NULL,
  PRIMARY KEY (`skey`)
) ENGINE=MyISAM;

CREATE TABLE `{$tablepre}xplus_poll` (
  `pollid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `templateid` smallint(6) NOT NULL,
  `title` char(80) NOT NULL,
  `username` char(15) NOT NULL,
  `dateline` int(10) NOT NULL,
  `introduce` text NOT NULL,
  `keywords` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `count` mediumint(8) NOT NULL,
  `available` tinyint(1) NOT NULL,
  `starttime` int(10) NOT NULL,
  `endtime` int(10) NOT NULL,
  `allowalluser` tinyint(1) NOT NULL,
  `forwardurl` varchar(255) NOT NULL,
  PRIMARY KEY (`pollid`),
  KEY `username` (`username`,`dateline`),
  KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

CREATE TABLE `{$tablepre}xplus_poll_category` (
  `categoryid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `pollid` mediumint(8) NOT NULL,
  `caption` varchar(255) NOT NULL,
  `displayorder` tinyint(1) NOT NULL,
  PRIMARY KEY (`categoryid`),
  KEY `pollid` (`pollid`,`displayorder`)
) ENGINE=MyISAM;

CREATE TABLE `{$tablepre}xplus_poll_item` (
  `itemid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `pollid` mediumint(8) NOT NULL,
  `categoryid` mediumint(8) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `type` enum('radio','checkbox','select','text','textarea') NOT NULL,
  `must` tinyint(1) NOT NULL,
  `enable` tinyint(1) NOT NULL,
  `displayorder` tinyint(1) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`itemid`),
  KEY `pollid` (`pollid`,`displayorder`)
) ENGINE=MyISAM;

CREATE TABLE `{$tablepre}xplus_poll_option` (
  `optionid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `itemid` mediumint(8) NOT NULL,
  `caption` varchar(255) NOT NULL,
  `displayorder` tinyint(3) NOT NULL,
  `count` mediumint(8) NOT NULL,
  PRIMARY KEY (`optionid`),
  KEY `itemid` (`itemid`,`displayorder`)
) ENGINE=MyISAM;

CREATE TABLE `{$tablepre}xplus_poll_profile` (
  `profileid` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `fieldid` varchar(255) NOT NULL,
  `pollid` smallint(6) NOT NULL,
  `label` varchar(255) NOT NULL,
  `must` tinyint(1) NOT NULL,
  `displayorder` tinyint(1) NOT NULL,
  PRIMARY KEY (`profileid`),
  KEY `pollid` (`pollid`,`displayorder`)
) ENGINE=MyISAM;

CREATE TABLE `{$tablepre}xplus_poll_result` (
  `resultid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `pollid` smallint(6) NOT NULL,
  `uid` mediumint(8) NOT NULL,
  `dateline` int(8) NOT NULL,
  `detail` text NOT NULL,
  `profile` text NOT NULL,
  PRIMARY KEY (`resultid`)
) ENGINE=MyISAM;

CREATE TABLE `{$tablepre}xplus_vote` (
  `voteid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `templateid` smallint(6) unsigned NOT NULL DEFAULT '1',
  `choicenum` mediumint(8) unsigned NOT NULL DEFAULT '1',
  `numperpage` smallint(6) unsigned NOT NULL DEFAULT '0',
  `limittime` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `totalnum` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `title` char(80) NOT NULL,
  `available` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `repeattype` smallint(6) unsigned NOT NULL DEFAULT '0',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `contenttype` tinyint(3) NOT NULL DEFAULT '0',
  `repeatlimit` tinyint(1) NOT NULL,
  `maxnum` smallint(6) NOT NULL,
  `resultview_mod` tinyint(1) NOT NULL DEFAULT '1',
  `resultview_time` tinyint(1) NOT NULL DEFAULT '0',
  `errordetail` tinyint(1) NOT NULL DEFAULT '0',
  `username` char(15) NOT NULL,
  `dateline` int(10) unsigned NOT NULL,
  `starttime` int(10) unsigned NOT NULL,
  `endtime` int(10) unsigned NOT NULL,
  `choicerepeat` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`voteid`),
  KEY `available` (`available`),
  KEY `endtime` (`endtime`),
  KEY `username` (`username`,`dateline`),
  KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

CREATE TABLE `{$tablepre}xplus_vote_choice` (
  `choiceid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `voteid` mediumint(8) unsigned NOT NULL,
  `caption` varchar(255) NOT NULL,
  `displayorder` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `imageurl` varchar(255) NOT NULL,
  `detailurl` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `votenum` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `aid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `basicnum` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`choiceid`),
  KEY `itemid` (`voteid`),
  KEY `itemid_displayorder` (`voteid`,`displayorder`),
  KEY `itemid_pollnum` (`voteid`,`votenum`)
) ENGINE=MyISAM;

CREATE TABLE `{$tablepre}xplus_vote_field` (
  `voteid` mediumint(8) unsigned NOT NULL,
  `description` text NOT NULL,
  `seokeywords` varchar(255) NOT NULL,
  `seodesc` varchar(255) NOT NULL,
  `lazyload` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`voteid`)
) ENGINE=MyISAM;

CREATE TABLE `{$tablepre}xplus_vote_value` (
  `voteid` mediumint(8) unsigned NOT NULL,
  `choiceid` mediumint(8) unsigned NOT NULL,
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(15) NOT NULL,
  `soid` varchar(8) NOT NULL DEFAULT '',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `itemid` (`voteid`),
  KEY `choiceid` (`choiceid`),
  KEY `uid` (`uid`,`voteid`,`dateline`),
  KEY `ip` (`ip`,`voteid`,`dateline`),
  KEY `soid` (`soid`,`voteid`,`dateline`),
  KEY `voteid` (`voteid`,`choiceid`,`dateline`)
) ENGINE=MyISAM;

INSERT INTO `{$tablepre}xplus_common_config` VALUES ('vote_choicenum', '{$installlang['vote_choicenum']}');
INSERT INTO `{$tablepre}xplus_common_config` VALUES ('vote_repeat_vote', '{$installlang['vote_repeat_vote']}');
INSERT INTO `{$tablepre}xplus_common_config` VALUES ('vote_repeat_item', '{$installlang['vote_repeat_item']}');
INSERT INTO `{$tablepre}xplus_common_config` VALUES ('vote_interval', '{$installlang['vote_interval']}');
INSERT INTO `{$tablepre}xplus_common_config` VALUES ('vote_success', '{$installlang['vote_success']}');
INSERT INTO `{$tablepre}xplus_common_config` VALUES ('common_usergroup', '');
INSERT INTO `{$tablepre}xplus_common_config` VALUES ('common_adminid', 'a:1:{i:0;i:1;}');
INSERT INTO `{$tablepre}xplus_common_config` VALUES ('poll_repeat', '{$installlang['poll_repeat']}');
INSERT INTO `{$tablepre}xplus_common_config` VALUES ('poll_must', '{$installlang['poll_must']}');
INSERT INTO `{$tablepre}xplus_common_config` VALUES ('poll_unfinished', '{$installlang['poll_unfinished']}');
INSERT INTO `{$tablepre}xplus_common_config` VALUES ('poll_meminfo', '{$installlang['poll_meminfo']}');
INSERT INTO `{$tablepre}xplus_common_config` VALUES ('poll_location', '{$installlang['poll_location']}');
INSERT INTO `{$tablepre}xplus_common_config` VALUES ('poll_success', '{$installlang['poll_success']}');
INSERT INTO `{$tablepre}xplus_common_module` VALUES ('1', '{$installlang['vote']}', 'vote', '', '', '1', '0');
INSERT INTO `{$tablepre}xplus_common_module` VALUES ('2', '{$installlang['form']}', 'form', '', '', '1', '0');
INSERT INTO `{$tablepre}xplus_common_module` VALUES ('3', '{$installlang['poll']}', 'poll', ' ', ' ', '1', '0');
INSERT INTO `{$tablepre}xplus_common_template` VALUES ('1', '{$installlang['default_template']}', 'default', '1', '1', 'Comsenz.inc');
INSERT INTO `{$tablepre}xplus_common_template` VALUES ('2', '{$installlang['default_template']}', 'default', '1', '2', 'Comsenz.inc');
INSERT INTO `{$tablepre}xplus_common_template` VALUES ('3', '{$installlang['default_template']}', 'default', '1', '3', 'Comsenz.inc');
INSERT INTO `{$tablepre}xplus_form_field_class` VALUES ('1', '1', '{$installlang['default_category']}');

INSERT INTO `{$tablepre}forum_bbcode` (available, tag, icon, replacement, example, explanation, params, prompt, nest, displayorder, perm) VALUES ('2', 'vote', '../../../source/plugin/xplus/static/image/bb_vote.png', '<iframe class=\"xplus_iframes\" src=\"plugin.php?id=xplus:vote&vid={3}&iniframe=1&width={1}&height={2}\" width=\"{1}\" height=\"{2}\" scrolling=\"no\" frameborder=\"0\" border=\"0\"></iframe>', '', '{$installlang['xplus_vote']}', '3', '{$installlang['info']}', '1', '1', '1');
INSERT INTO `{$tablepre}forum_bbcode` (available, tag, icon, replacement, example, explanation, params, prompt, nest, displayorder, perm) VALUES ('2', 'poll', '../../../source/plugin/xplus/static/image/bb_poll.png', '<iframe class=\"xplus_iframes\" src=\"plugin.php?id=xplus:poll&pid={3}&iniframe=1&width={1}&height={2}\" width=\"{1}\" height=\"{2}\" scrolling=\"no\" frameborder=\"0\" border=\"0\"></iframe>', '', '{$installlang['xplus_poll']}', '3', '{$installlang['info']}', '1', '2', '1');
INSERT INTO `{$tablepre}forum_bbcode` (available, tag, icon, replacement, example, explanation, params, prompt, nest, displayorder, perm) VALUES ('2', 'form', '../../../source/plugin/xplus/static/image/bb_form.png', '<iframe class=\"xplus_iframes\" src=\"plugin.php?id=xplus:form&fid={3}&iniframe=1&width={1}&height={2}\" width=\"{1}\" height=\"{2}\" scrolling=\"no\" frameborder=\"0\" border=\"0\"></iframe>', '', '{$installlang['xplus_form']}', '3', '{$installlang['info']}', '1', '3', '1');
INSERT INTO `{$tablepre}common_syscache` VALUES ('xplus_common_config', '1', '1322556386', 0x613A31353A7B733A31363A22636F6D6D6F6E5F7573657267726F7570223B733A303A22223B733A31343A22636F6D6D6F6E5F61646D696E6964223B733A31343A22613A313A7B693A303B693A313B7D223B733A343A22766F7465223B693A313B733A343A22706F6C6C223B693A313B733A31343A22766F74655F63686F6963656E756D223B733A33353A22B6D4B2BBC6F0A3ACB1BECDB6C6B1CFDED6C6D7EEB6E0D6BBC4DCD1A17B6E756D7DCFEE223B733A31363A22766F74655F7265706561745F766F7465223B733A33363A22B6D4B2BBC6F0A3ACB1BECDB6C6B1C9E8B6A8C3BFC8CBCFDECDB67B6D61786E756D7DB4CE223B733A31363A22766F74655F7265706561745F6974656D223B733A33383A22B6D4B2BBC6F0A3ACC4FAB2BBC4DCB6D4D2D1BEADCDB6B9FDB5C4D1A1CFEED6D8B8B4CDB6C6B1223B733A31333A22766F74655F696E74657276616C223B733A34353A22B6D4B2BBC6F0A3ACB1BECDB6C6B1C9E8B6A87B6C696D697474696D657DB7D6D6D3D6BBC4DCCDB6D2BBB4CEC6B1223B733A31323A22766F74655F73756363657373223B733A32353A22CDB6C6B1B3C9B9A620A3ACB8D0D0BBC4FAB5C4B2CED3EBA3A1223B733A31313A22706F6C6C5F726570656174223B733A33303A22C4FAD2D1BEADCCE1BDBBB9FDCECABEEDA3ACB8D0D0BBC4FAB5C4B2CED3EB223B733A393A22706F6C6C5F6D757374223B733A33323A22B6D4B2BBC6F0A3ACB5DA7B69647DCCE2CEAAB1D8CCEECFEEA3ACC7EBCCEED0B4223B733A31353A22706F6C6C5F756E66696E6973686564223B733A33303A22B6D4B2BBC6F0A3ACC4FACEB4CDEAB3C9CBF9D3D0B7D6C0E0B5C4CCEED0B4223B733A31323A22706F6C6C5F6D656D696E666F223B733A34313A22B6D4B2BBC6F0A3AC7B6669656C646E616D657DCCEED0B4B2BBD5FDC8B7A3ACC7EBD5FDC8B7CCEED0B4223B733A31333A22706F6C6C5F6C6F636174696F6E223B733A33383A22CCE1BDBBB3C9B9A6A3ACCFB5CDB3B4A6C0EDD6D0A3ACB5C8B4FDD2B3C3E6CCF8D7AAA1ADA1AD223B733A31323A22706F6C6C5F73756363657373223B733A33383A22B9A7CFB2C4FAA3ACCECABEEDCCEED0B4CDEAB3C9A3ACB5C8B4FDD2B3C3E6CCF8D7AAA1ADA1AD223B7D);

EOF;

runquery($sql);

$finish = TRUE;

?>