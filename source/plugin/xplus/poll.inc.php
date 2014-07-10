<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc.inc.php 16840 2011-09-26 08:19:59Z Niexinyuan $
 */
 
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('XPLUS_ROOT', DISCUZ_ROOT.'./source/plugin/xplus/');
define('XPLUS_FUNCTION_ROOT', XPLUS_ROOT.'./function/');
define('XPLUS_MODULE_ROOT', XPLUS_ROOT.'./module/');

require_once XPLUS_FUNCTION_ROOT.'./function_core.php';

$module = get_moduleinfo('poll', true);
if(!$module) {
    showmessage('xplus:admincr_module_disable', dreferer());
}

$moduleurl = "plugin.php?id=xplus:poll";

$mod = in_array($_G['gp_mod'], array('index')) ? $_G['gp_mod'] : 'index';

require XPLUS_MODULE_ROOT.'/poll/poll_'.$mod.'.php';

?>