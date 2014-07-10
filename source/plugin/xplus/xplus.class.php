<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: xplus.class.php 16840 2011-09-06 08:19:59Z Niexinyuan $
 */
 
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_xplus
{
    function plugin_xplus() {
        return;
    }
    
    function global_usernav_extra2() {
        define('XPLUS_ROOT', DISCUZ_ROOT.'./source/plugin/xplus/');
        define('XPLUS_FUNCTION_ROOT', XPLUS_ROOT.'./function/');
        require_once XPLUS_FUNCTION_ROOT.'./function_core.php';
        if(checkadmincr()) {
            return '<span class="pipe">|</span><a target="_blank" href="plugin.php?id=xplus:admincr">'.lang('plugin/xplus', 'admincp').'</a> ';
        } else {
            return '';
        }
    }
    
}
