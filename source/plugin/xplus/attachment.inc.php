<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: attachment.inc.php 16840 2011-10-26 08:19:59Z Niexinyuan $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

define('XPLUS_ROOT', DISCUZ_ROOT.'./source/plugin/xplus/');
define('XPLUS_FUNCTION_ROOT', XPLUS_ROOT.'./function/');

require XPLUS_FUNCTION_ROOT.'./function_core.php';

$identifier = $plugin['identifier'];
$Plang = $scriptlang['xplus'];

if(submitcheck('attachmentsubmit')) {
    
    $delete = $_G['gp_delete'];
    if($delete && is_array($delete)) {
        DB::delete('xplus_common_attachment', 'aid IN ('.dimplode($delete).')');
        if($_G['gp_deleterelatedchoices']) {
            DB::delete('xplus_vote_choice', 'aid IN ('.dimplode($delete).')');
        }
    }
    
    cpmsg($Plang['admincp_operate_successed'], "action=plugins&operation=config&do=$pluginid&identifier=$identifier&pmod=attachment", 'succeed');    
    
} else {
    
    $curpage = $_G['gp_page'] && intval($_G['gp_page']) > 0 ? intval($_G['gp_page']) : 1;
    $limit = $numpage = 5;
    $start = ($curpage - 1) * $numpage;
    $totalcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xplus_common_attachment'));
    
    $query = DB::query("SELECT xca.*, cm.username FROM ".DB::table('xplus_common_attachment')." xca
        LEFT JOIN ".DB::table('common_member')." cm ON cm.uid=xca.authorid WHERE xca.dateline>0
        ORDER BY xca.dateline DESC LIMIT $start, $limit");
        
    $multipage = multi($totalcount, $numpage, $curpage, ADMINSCRIPT."?action=plugins&operation=config&do=$pluginid&identifier=$identifier&pmod=attachment");
    
    showformheader("plugins&operation=config&do=$pluginid&identifier=$identifier&pmod=attachment");
    showtableheader($Plang['admincp_attachment_list']);
    showsubtitle(array('', $Plang['admincp_attachment_image'], $Plang['admincp_attachment_url'], $Plang['admincp_attachment_size'], $Plang['admincp_attachment_author'], $Plang['admincp_attachment_dateline']));
    while($attchment = DB::fetch($query)) {
        $imageurl = $_G['setting']['attachurl'].'common/'.$attchment['url'].'.thumb.jpg';
        $imagehtml = '<img src="'.$imageurl.'" />';
        $authorhtml = '<a href="home.php?mod=space&uid='.$attchment['authorid'].'" target="_blank">'.$attchment['username'].'</a>';
        $dateline = dgmdate($attchment['dateline']);
        showtablerow('', array('', '', '', '', '', ''),
            array("<input type=\"checkbox\" class=\"checkbox\" name=\"delete[]\" value=\"$attchment[aid]\">", 
            $imagehtml, $attchment['url'], $attchment['filesize'], $authorhtml, $dateline)
        );
    }
    showsubmit('attachmentsubmit', 'submit', 'del', '<label><input name="deleterelatedchoices" class="checkbox" type="checkbox" value="true" checked="checked" />'.$Plang['admincp_attachment_delete'].'</label>', $multipage);
    showtablefooter();
    showformfooter();
    
}