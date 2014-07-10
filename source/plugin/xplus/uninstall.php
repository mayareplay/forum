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

DROP TABLE {$tablepre}xplus_common_attachment;
DROP TABLE {$tablepre}xplus_common_template;
DROP TABLE {$tablepre}xplus_common_config;
DROP TABLE {$tablepre}xplus_common_module;
DROP TABLE {$tablepre}xplus_form_attachment;
DROP TABLE {$tablepre}xplus_form_field;
DROP TABLE {$tablepre}xplus_form_field_class;
DROP TABLE {$tablepre}xplus_form_item;
DROP TABLE {$tablepre}xplus_form_item_count;
DROP TABLE {$tablepre}xplus_form_item_field;
DROP TABLE {$tablepre}xplus_form_item_var;
DROP TABLE {$tablepre}xplus_form_setting;
DROP TABLE {$tablepre}xplus_poll;
DROP TABLE {$tablepre}xplus_poll_category;
DROP TABLE {$tablepre}xplus_poll_item;
DROP TABLE {$tablepre}xplus_poll_option;
DROP TABLE {$tablepre}xplus_poll_profile;
DROP TABLE {$tablepre}xplus_poll_result;
DROP TABLE {$tablepre}xplus_vote;
DROP TABLE {$tablepre}xplus_vote_choice;
DROP TABLE {$tablepre}xplus_vote_field;
DROP TABLE {$tablepre}xplus_vote_value;

DELETE FROM {$tablepre}forum_bbcode WHERE tag IN ('vote', 'poll', 'form');
DELETE FROM {$tablepre}common_syscache WHERE cname='xplus_common_config';
EOF;

runquery($sql);

$finish = TRUE;