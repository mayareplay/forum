<?php

/**
 *      [Discuz!] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: poll_index.php 867 2011-10-26 04:42:27Z Niexinyuan $
 */
 
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require xplus_libfile('function/poll');
loadcache('xplus_common_config');
$lang = $_G['cache']['xplus_common_config'] ? $_G['cache']['xplus_common_config'] : '';
$pollid = !empty($_G['gp_pid']) ? intval($_G['gp_pid']) : 0;
$pollurl = $moduleurl."&pid=$pollid";

$iniframe = !empty($_G['gp_iniframe']) ? 1 : 0;
if($iniframe) {
    $width = !empty($_G['gp_width']) ? intval($_G['gp_width']) : 500;
    $height = !empty($_G['gp_height']) ? intval($_G['gp_height']) : 500;
    $pollurl .= $pollurl."&iniframe=1&width=$width&height=$height";
}

if(!$pollid) {
    showmessage('xplus:poll_inexistence');
} else {
    $poll = DB::fetch_first("SELECT * FROM ".DB::table('xplus_poll')." WHERE pollid='$pollid'");
    
    if(!$poll['available'] && ($_G['adminid'] != 1 || $_G['username'] != $poll['username'])) {
        showmessage('xplus:vote_inexistence');
    }
    
    if(!$poll['allowalluser'] && !$_G['uid']) {
        dheader("Location: member.php?mod=logging&action=login");
    }
     
    $iscategoried = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xplus_poll_category')." WHERE pollid='$pollid'");
    if($iscategoried && !$_G['gp_finish']) {
        $step = $_G['gp_step'] ? intval($_G['gp_step']) : 1;
        $cid = $_G['gp_cid'] ? intval($_G['gp_cid']) : 0;
        $start = $step - 1;
        while($categoryid = DB::result_first("SELECT categoryid FROM ".DB::table('xplus_poll_category')." WHERE pollid='$pollid' ORDER BY displayorder LIMIT $start, 1")) {
            if(DB::result_first("SELECT COUNT(*) FROM ".DB::table('xplus_poll_item')." WHERE categoryid='$categoryid'") > 0) {
                break;
            }
            $start ++;
        }
    } 

    if(!$categoryid) {
        $query = DB::query("SELECT * FROM ".DB::table('xplus_poll_profile')." WHERE pollid='$pollid' ORDER BY displayorder");
        while($profile = DB::fetch($query)) {
            $profilelist[] = $profile;
            $selectprofile .= ", $profile[fieldid] ";
        }
        if($_G['uid']) {
            $memprofile = DB::fetch_first("SELECT uid $selectprofile FROM ".DB::table('common_member_profile')." WHERE uid='$_G[uid]'");
            $memprofile['gender'] = isset($memprofile['gender']) ? ($memprofile['gender'] == 0 ? lang('home/template', 'male') : lang('home/template', 'female')) : null;
        }
    }
}

if($_G['gp_action'] == 'pollsubmit') {
    
    if($poll['starttime'] > TIMESTAMP) {
		showmessage('xplus:poll_unstart');
	}
    
    if(!empty($poll['endtime']) && $poll['endtime'] < TIMESTAMP) {
		showmessage('xplus:poll_expired');
	}
    
    $result = $_G['gp_res'];
    $text = $_G['gp_text'];
    $city = $_G['gp_city'];
    $cityc = $_G['gp_cityc'];
    
    if($_G['uid']) {
         DB::result_first("SELECT COUNT(*) FROM ".DB::table('xplus_poll_result')." WHERE pollid='$pollid' AND uid='$_G[uid]'") && showmessage($lang['poll_repeat'], $pollurl);
    }
    
    if($iscategoried) {
        $condition = " AND pollid='$pollid' AND enable='1'".($cid ? " AND categoryid='$cid'" : ' AND 0');
    } else {
        $condition = " AND pollid='$pollid' AND enable='1'";
    }
    
    $id = 1;
    $query = DB::query("SELECT * FROM ".DB::table('xplus_poll_item')." WHERE 1 $condition ORDER BY displayorder");
    while($item = DB::fetch($query)) {
        $itemid = $item['itemid'];
        if($item['must'] && empty($result[$itemid])) {
            showmessage($lang['poll_must'], '', array('id' => $id));
        }
        if($item['type'] == 'radio' || $item['type'] == 'checkbox') {
            if(!is_array($result[$itemid])) {
                $results[$itemid] = array(
                    intval($result[$itemid]) => !empty($text[$itemid][$result[$itemid]]) ? $text[$itemid][$result[$itemid]] : ($city[$itemid][$result[$itemid]] ? dhtmlspecialchars($city[$itemid][$result[$itemid]]).dhtmlspecialchars($cityc[$itemid][$result[$itemid]]) : 1),
                );
            } else {
                foreach($result[$itemid] as $optionid) {
                    $results[$itemid][intval($optionid)] = !empty($text[$itemid][$optionid]) ? $text[$itemid][$optionid] : ($city[$itemid][$optionid] ? dhtmlspecialchars($city[$itemid][$optionid]).dhtmlspecialchars($cityc[$itemid][$optionid]) : 1);
                }
            }
        } elseif($item['type'] == 'select') {
            $results[$itemid] = array(
                intval($result[$itemid]) => 1,
            );
            $options[] = intval($result[$itemid]);
        } else {
            $results[$itemid] = dhtmlspecialchars(trim($result[$itemid]));
        }
        $id ++;
    }
    
    if($cid) {
        $results = empty($results) ? array() : serialize($results);
        $cookiekey = "xplus_poll_{$pollid}_category_{$cid}".($_G['uid'] ? '_'.$_G['uid'] : '');
        dsetcookie($cookiekey, $results, 3600);
    }
    
    
    if($_G['gp_finish']) {
        $query = DB::query("SELECT categoryid FROM ".DB::table('xplus_poll_category')." WHERE pollid='$pollid'");
        if(DB::num_rows($query) > 0) {
            $results = array();
            while($category = DB::fetch($query)) {
                if(DB::result_first("SELECT COUNT(*) FROM ".DB::table('xplus_poll_item')." WHERE categoryid='$category[categoryid]'") == 0) {
                    continue;
                }
                $cookiekey = "xplus_poll_{$pollid}_category_{$category[categoryid]}".($_G['uid'] ? '_'.$_G['uid'] : '');
                if(!$_G['cookie'][$cookiekey]) { 
                    showmessage($lang['poll_unfinished']);
                }
                $result = unserialize(stripcslashes($_G['cookie'][$cookiekey]));
                $results = mergeresult($results, $result, true);
                dsetcookie($cookiekey, '', 0);
            }
        }
         
        $myprofile = $_G['gp_profile'];
    
        $query = DB::query("SELECT * FROM ".DB::table('xplus_poll_profile')." WHERE pollid='$pollid' ORDER BY displayorder");
        while($profile = DB::fetch($query)) {
            $fieldid = $profile['fieldid'];
            $myprofile[$fieldid] = !empty($myprofile[$fieldid]) ? dhtmlspecialchars($myprofile[$fieldid]) : '';
            if(!$myprofile[$fieldid] && $profile['must']) {
                showmessage($lang['poll_meminfo'], '', array('fieldname' => $profile['label']));
            }
        }
        if($results) {
            
            foreach($results as $itemid => $result) {
                if(!is_array($result)) {
                    continue;
                }
                foreach($result as $optionid => $value) {
                   $options[] = $optionid;    
                }
            }
            DB::query("UPDATE ".DB::table('xplus_poll_option')." SET count=count+1 WHERE optionid IN (".dimplode($options).")");
            DB::query("UPDATE ".DB::table('xplus_poll')." SET count=count+1 WHERE pollid='$pollid'");
            
            $data = array(
                'pollid' => $pollid,
                'uid' => $_G['uid'],
                'dateline' => TIMESTAMP,
                'detail' => serialize($results),
                'profile' => serialize($myprofile),
            );
            DB::insert('xplus_poll_result', $data);
            
        }
        
        showmessage($lang['poll_success'], $poll['forwardurl'] ? $poll['forwardurl'] : $pollurl);
        exit;
    }
    
    if($categoryid) {
        $step ++;
        showmessage($lang['poll_location'], $pollurl."&step=$step");
    }
    
    
} else {
    
    $template = DB::fetch_first("SELECT * FROM ".DB::table('xplus_common_template')." WHERE templateid='$poll[templateid]' AND available='1' AND mid='$module[mid]'");
    if(!$template) {
        showmessage('xplus:admincr_notemplate');
    }
    $_G['showmessage']['cssurl'] = 'source/plugin/xplus/template/'.$module['identifier'].'/'.$template['directory'].'/'.$module['identifier'].'.css';
    $_G['showmessage']['jspath'] = 'source/plugin/xplus/static/js/';
    $_G['showmessage']['imgpath'] = 'source/plugin/xplus/template/'.$module['identifier'].'/'.$template['directory'].'/image/';

    $metakeywords = $poll['keywords'];
    $metadescription = $poll['description'];
    $title = $poll['title'].' - '.$_G['setting']['bbname'];
    
    if($iscategoried) {
        $condition = " AND pollid='$pollid' AND enable='1'".($categoryid ? " AND categoryid='$categoryid'" : ' AND 0');
    } else {
        $condition = " AND pollid='$pollid' AND enable='1'";
    }
    
    
    $count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xplus_poll_item')." WHERE 1 $condition");
    
    if($count) {
        $query = DB::query("SELECT * FROM ".DB::table('xplus_poll_item')." WHERE 1 $condition ORDER BY displayorder");
        while($item = DB::fetch($query)) {
            if(in_array($item['type'], array('checkbox', 'radio'))) {
                $optionquery = DB::query("SELECT * FROM ".DB::table('xplus_poll_option')." WHERE itemid='$item[itemid]' ORDER BY displayorder");
                while($option = DB::fetch($optionquery)) {
                    $option['istextfield'] = strpos($option['caption'], '[text]') !== false ? true : false;
                    $option['iscityfield'] = strpos($option['caption'], '[city]') !== false ? true : false;
                    $options[$item['itemid']][] = $option;
                }
            } elseif(in_array($item['type'], array('select'))) {
                $optionquery = DB::query("SELECT * FROM ".DB::table('xplus_poll_option')." WHERE itemid='$item[itemid]' ORDER BY displayorder");
                while($option = DB::fetch($optionquery)) {
                    $options[$item['itemid']][] = $option;
                }
            }
            $items[] = $item;
        }
    }
    
    $tplfile = $iniframe ? 'index_iframe' : 'index';
    include template('xplus:poll/'.$template['directory']."/$tplfile");
}

?>