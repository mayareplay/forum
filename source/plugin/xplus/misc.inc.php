<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc.inc.php 16840 2011-09-21 08:19:59Z Niexinyuan $
 */
 
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('XPLUS_ROOT', DISCUZ_ROOT.'./source/plugin/xplus/');
define('XPLUS_FUNCTION_ROOT', XPLUS_ROOT.'./function/');
define('XPLUS_MODULE_ROOT', XPLUS_ROOT.'./module/');

require_once XPLUS_FUNCTION_ROOT.'./function_core.php';

$mod = in_array($_G['gp_mod'], array('so', 'swfupload')) ? $_G['gp_mod'] : 'so';

require XPLUS_MODULE_ROOT.'/misc/misc_'.$mod.'.php';


?>