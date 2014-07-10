<?php
	
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: update.php 2 2011-12-21 Z Niexinyuan $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$tablepre = !empty($_G['config']['db'][1]['tablepre']) ? $_G['config']['db'][1]['tablepre'] : 'pre_';

if(version_compare($_G['gp_fromversion'], '1.0.1', '<=')) {
    $filenames = array('bb_vote.png', 'bb_poll.png', 'bb_form.png');
    foreach($filenames as $filename) {
        if(file_exists(DISCUZ_ROOT.'./static/image/common/'.$filename)) {
            unlink(DISCUZ_ROOT.'./static/image/common/'.$filename);
        }
    }
    $filenames = array('xplus_common.js', 'xplus_admincr.js', 'script_city.js');
    foreach($filenames as $filename) {
        if(file_exists(DISCUZ_ROOT.'./static/js/'.$filename)) {
            unlink(DISCUZ_ROOT.'./static/js/'.$filename);
        }
    }
    $sql = <<<EOF
UPDATE `{$tablepre}forum_bbcode` SET icon='../../../source/plugin/xplus/static/image/bb_vote.png' WHERE tag='vote';
UPDATE `{$tablepre}forum_bbcode` SET icon='../../../source/plugin/xplus/static/image/bb_poll.png' WHERE tag='poll';
UPDATE `{$tablepre}forum_bbcode` SET icon='../../../source/plugin/xplus/static/image/bb_form.png' WHERE tag='form';
ALTER TABLE  `{$tablepre}xplus_vote` CHANGE  `available`  `available` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE  `{$tablepre}xplus_form_item` CHANGE  `available`  `available` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE  `{$tablepre}xplus_vote_choice` CHANGE  `basicnum`  `basicnum` MEDIUMINT( 8 ) UNSIGNED NOT NULL;
ALTER TABLE  `{$tablepre}xplus_vote_choice` ADD  `description` TEXT NOT NULL AFTER  `detailurl`;
EOF;
    runquery($sql);
}

$finish = TRUE;

?>