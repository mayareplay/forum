<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: config.inc.php 16840 2011-09-02 08:19:59Z Niexinyuan $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

define('XPLUS_ROOT', DISCUZ_ROOT.'./source/plugin/xplus/');
define('XPLUS_FUNCTION_ROOT', XPLUS_ROOT.'./function/');

require XPLUS_FUNCTION_ROOT.'./function_core.php';
require xplus_libfile('function/config');

$identifier = $plugin['identifier'];
$Plang = $scriptlang['xplus'];

if(submitcheck('config_submit')) {
    
    if($newconfig = $_G['gp_newconfig']) {
        $newconfig['common_adminid'] = !empty($newconfig['common_adminid']) ? explode(',', $newconfig['common_adminid']) : '';
        foreach($newconfig['common_adminid'] as $id => &$adminid) {
            $adminid = intval($adminid);
            if(!$adminid) {
                unset($newconfig['common_adminid'][$id]);
            }
        }
        $data = array(
            'common_usergroup' => is_array($newconfig['common_usergroup']) ? serialize($newconfig['common_usergroup']) : '',
            'common_adminid' => serialize($newconfig['common_adminid']),
            'vote' => intval($newconfig['vote']) == 1 ? 1 : 0,
            'poll' => intval($newconfig['poll']) == 1 ? 1 : 0, 
            'vote_choicenum' => dhtmlspecialchars(trim($newconfig['vote_choicenum'])),
            'vote_repeat_vote' => dhtmlspecialchars(trim($newconfig['vote_repeat_vote'])),
            'vote_repeat_item' => dhtmlspecialchars(trim($newconfig['vote_repeat_item'])),
            'vote_interval' => dhtmlspecialchars(trim($newconfig['vote_interval'])),
            'vote_success' => dhtmlspecialchars(trim($newconfig['vote_success'])), 
            'poll_repeat' =>  dhtmlspecialchars(trim($newconfig['poll_repeat'])),  
            'poll_must' =>  dhtmlspecialchars(trim($newconfig['poll_must'])),  
            'poll_unfinished' =>  dhtmlspecialchars(trim($newconfig['poll_unfinished'])),  
            'poll_meminfo' =>  dhtmlspecialchars(trim($newconfig['poll_meminfo'])),  
            'poll_location' =>  dhtmlspecialchars(trim($newconfig['poll_location'])),  
            'poll_success' =>  dhtmlspecialchars(trim($newconfig['poll_success'])),  
        );
        foreach($data as $key => $value) {
            if(in_array($key, array('vote', 'poll', 'form'))) {
                DB::update('xplus_common_module', array('available' => $value), "identifier='$key'");
            } else {
                DB::update('xplus_common_config', array('cvalue' => $value), "ckey='$key'");
            }
        }
    }
    save_syscache('xplus_common_config', $data);
    cpmsg($Plang['admincp_config_modify_successed'], "action=plugins&operation=config&do=$pluginid&identifier=$identifier&pmod=config", 'succeed');
    
} else {
    
    $query = DB::query("SELECT ckey, cvalue FROM ".DB::table('xplus_common_config'));
    while($result = DB::fetch($query)) {
        $config[$result['ckey']] = $result['cvalue'];
    }
    
    $query = DB::query("SELECT available, identifier FROM ".DB::table('xplus_common_module')." WHERE 1");
    while($module = DB::fetch($query)) {
        $modulelist[$module['identifier']] = $module['available'];
    }
    
    showformheader("plugins&operation=config&do=$pluginid&identifier=$identifier&pmod=config", 'enctype');
    showtableheader();
    showtitle($Plang['admincp_common']);
    //showusergroup($Plang['admincp_common_usergroup'], 'newconfig[common_usergroup]', unserialize($config['common_usergroup']), $Plang['admincp_common_usergroup_des']);
    $config['common_adminid'] = implode(',', unserialize($config['common_adminid']));
    showsetting($Plang['admincp_common_adminid'], 'newconfig[common_adminid]', $config['common_adminid'], 'text', '', '', $Plang['admincp_common_adminid_des']);
    showtagfooter('tbody');
    showtitle($Plang['admincp_vote']);
    showsetting($Plang['admincp_vote_enable'], 'newconfig[vote]', $modulelist['vote'], 'radio', 0, 1, $Plang['admincp_vote_enable_des']);
    showsetting($Plang['admincp_vote_choicenum'], 'newconfig[vote_choicenum]', $config['vote_choicenum'], 'textarea', '', '', $Plang['admincp_vote_choicenum_des']);
    showsetting($Plang['admincp_vote_repeat_vote'], 'newconfig[vote_repeat_vote]', $config['vote_repeat_vote'], 'textarea', '', '', $Plang['admincp_vote_repeat_vote_des']);
    showsetting($Plang['admincp_vote_repeat_item'], 'newconfig[vote_repeat_item]', $config['vote_repeat_item'], 'textarea', '', '', $Plang['admincp_vote_repeat_item_des']); 
    showsetting($Plang['admincp_vote_interval'], 'newconfig[vote_interval]', $config['vote_interval'], 'textarea', '', '', $Plang['admincp_vote_interval_des']); 
    showsetting($Plang['admincp_vote_success'], 'newconfig[vote_success]', $config['vote_success'], 'textarea', '', '', $Plang['admincp_vote_success_des']);
    showtagfooter('tbody');
    showtitle($Plang['admincp_poll']);
    showsetting($Plang['admincp_poll_enable'], 'newconfig[poll]', $modulelist['poll'], 'radio', 0, 1, $Plang['admincp_vote_enable_des']);
    showsetting($Plang['admincp_poll_repeat'], 'newconfig[poll_repeat]', $config['poll_repeat'], 'textarea', '', '', $Plang['admincp_poll_repeat_des']);
    showsetting($Plang['admincp_poll_must'], 'newconfig[poll_must]', $config['poll_must'], 'textarea', '', '', $Plang['admincp_poll_must_des']);
    showsetting($Plang['admincp_poll_unfinished'], 'newconfig[poll_unfinished]', $config['poll_unfinished'], 'textarea', '', '', $Plang['admincp_poll_unfinished_des']); 
    showsetting($Plang['admincp_poll_meminfo'], 'newconfig[poll_meminfo]', $config['poll_meminfo'], 'textarea', '', '', $Plang['admincp_poll_meminfo_des']); 
    showsetting($Plang['admincp_poll_location'], 'newconfig[poll_location]', $config['poll_location'], 'textarea', '', '', $Plang['admincp_poll_location_des']);
    showsetting($Plang['admincp_poll_success'], 'newconfig[poll_success]', $config['poll_success'], 'textarea', '', '', $Plang['admincp_poll_success_des']);
    showtagfooter('tbody');
    showsubmit('config_submit', 'submit', '');
    showtablefooter();
    showformfooter();
}
?>