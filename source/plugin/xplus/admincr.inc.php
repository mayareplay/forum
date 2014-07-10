<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: xplus.inc.php 16840 2011-09-06 08:19:59Z Niexinyuan $
 */
 
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('XPLUS_ROOT', DISCUZ_ROOT.'./source/plugin/xplus/');
define('XPLUS_FUNCTION_ROOT', XPLUS_ROOT.'./function/');
define('XPLUS_ADMINCR_ROOT', XPLUS_ROOT.'./admincr/');
define('XPLUS_PANEL', 99);

require XPLUS_FUNCTION_ROOT.'./function_core.php';

//error_reporting(E_ERROR | E_WARNING | E_PARSE);

if(!checkadmincr()) {
    showmessage('xplus:admincr_noauthority', dreferer());
}

//require_once libfile('class/panel');
$modsession = new discuz_panel(XPLUS_PANEL);

if(getgpc('xplus_login_panel') && getgpc('xplus_cppwd') && submitcheck('submit')) {
    $modsession->dologin($_G['uid'], getgpc('xplus_cppwd'), true);
}

if(!$modsession->islogin) {
    include template('xplus:admincr/login');
    dexit();
}

$mod = in_array($_G['gp_mod'], array('vote', 'poll', 'form', 'field')) ? $_G['gp_mod'] : 'vote';
define('ADMINCRSCRIPT', "plugin.php?id=xplus:admincr&mod=$mod&");
$module = get_moduleinfo($mod);

$_G['showmessage']['cssurl'] = 'source/plugin/xplus/template/common/';
$_G['showmessage']['jspath'] = 'source/plugin/xplus/static/js/';

require XPLUS_ADMINCR_ROOT.'admincr_'.$mod.'.php';

?>