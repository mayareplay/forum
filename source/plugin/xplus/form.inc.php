<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: vote.inc.php 16840 2011-09-23 08:19:59Z Niexinyuan $
 */
 
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('XPLUS_ROOT', DISCUZ_ROOT.'./source/plugin/xplus/');
define('XPLUS_FUNCTION_ROOT', XPLUS_ROOT.'./function/');
define('XPLUS_MODULE_ROOT', XPLUS_ROOT.'./module/');

require_once XPLUS_FUNCTION_ROOT.'./function_core.php';

$module = get_moduleinfo('form', true);
if(!$module) {
    showmessage('xplus:admincr_module_disable', dreferer());
}

$moduleurl = "plugin.php?id=xplus:form";

$mod = in_array($_G['gp_mod'], array('index' , 'post')) ? $_G['gp_mod'] : 'index';
require XPLUS_MODULE_ROOT.'/form/form_'.$mod.'.php';

?>