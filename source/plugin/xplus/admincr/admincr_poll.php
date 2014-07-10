<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincr_poll.php 2 2011-12-21 Z Niexinyuan $
 */
 
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require xplus_libfile('function/poll');

$actions = array('list', 'setting', 'meminfo', 'items', 'guide', 'export', 'statistics');
$action = in_array($_G['gp_action'], $actions) ? $_G['gp_action'] : 'list';
$inguide = $_G['gp_inguide'] ? 1 : 0;

$lang = lang('plugin/xplus');

switch($action) {
    case 'list' :   
        if(submitcheck('listsubmit')) {
            if($pollid = $_G['gp_delete']) {
                $condition = "pollid IN (".(is_array($pollid) ? dimplode($pollid) : '\'$pollid\'').")";
                                
                DB::delete('xplus_poll', $condition);          
                DB::delete('xplus_poll_profile', $condition);
                DB::delete('xplus_poll_item', $condition);
                DB::delete('xplus_poll_result', $condition);
                
                showmessage('xplus:admincr_delete_success', ADMINCRSCRIPT.'action=list');
            } else {
                showmessage('xplus:admincr_delete_noobject', ADMINCRSCRIPT.'action=list');
            }
        }
       
        if(submitcheck('searchsubmit') && $key = $_G['gp_searchkey']) {
            switch($_G['gp_searchtype']) {
                case 'id' : $condition .= " AND `pollid`='$key'"; break;
                case 'title' : $condition .= " AND `title` LIKE '%$key%'"; break;
                case 'username' : $condition .= " AND `username`='$key'"; break;
            }
        }
        $type = $_G['gp_type'] == 'admin';
        $condition .= $type ? '' : " AND `username`='$_G[username]'";
        
        $limit = $perpage = 10;
        $start = ($curpage = is_numeric($_G['gp_page']) && $_G['gp_page'] > 0 ? $_G['gp_page'] : 1) - 1;                 
        $tatolcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xplus_poll')." WHERE 1 $condition");
        $multipage = multi($tatolcount, $perpage, $curpage, ADMINCRSCRIPT.'action=list');
        
        $query = DB::query("SELECT pollid, title, username, dateline, endtime, available, starttime FROM ".DB::table('xplus_poll')
            ." WHERE dateline>0 $condition ORDER BY dateline DESC LIMIT $start, $limit"
        );

        while($poll = DB::fetch($query)) {
            $poll['title'] = cutstr($poll['title'], 30);    
            $poll['dateline'] = dgmdate($poll['dateline'], 'd');
            $poll['status'] = $poll['available'] ? $lang['admincr_enable'] : $lang['admincr_disable'];
            $poll['starttime'] = $poll['starttime'] ? dgmdate($poll['starttime'], 'd') : $lang['admincr_timelimit'];
            $poll['endtime'] = $poll['endtime'] ? dgmdate($poll['endtime'], 'd') : $lang['admincr_timelimit'];
            $polls[] = $poll;
        }
        break;
              
    case 'guide' :
        if(submitcheck('step1_submit')) {
            if(!$title = trim($_G['gp_title'])) {
                showmessage('xplus:admincr_notitle', ADMINCRSCRIPT.'action=guide');
            }
            DB::insert('xplus_poll', array(
                'title' => dhtmlspecialchars($title), 
                'username' => $_G['username'], 
                'dateline' => TIMESTAMP)
            );
            if($pollid = DB::insert_id()) {
                dheader("Location: ".ADMINCRSCRIPT."action=setting&inguide=1&pollid=$pollid");
            } else {
                showmessage('xplus:admincr_createvote_failed', ADMINCRSCRIPT.'action=guide');
            }
        } 
        break;
        
    case 'setting' :
        if(!$pollid = intval($_G['gp_pollid'])) {
            showmessage('xplus:admincr_unkownparams', ADMINCRSCRIPT.'action=list');
        }
        if(submitcheck('settingsubmit')) {
            $newsetting = $_G['gp_newsetting'];
            $newsetting['pollid'] = $pollid;       
            savepollnewsetting($pollid, $newsetting);
            if($inguide) {
                dheader('Location: '.ADMINCRSCRIPT."action=items&inguide=1&pollid=$pollid");
            } else {
                showmessage('xplus:admincr_edit_success', ADMINCRSCRIPT."action=setting&pollid=$pollid");
            }
        }
        $poll_setting = DB::fetch_first("SELECT * FROM ".DB::table('xplus_poll')." WHERE pollid='$pollid'"); 
        $poll_setting['starttime'] = $poll_setting['starttime'] > 0 ? date('Y-m-d', $poll_setting['starttime']) : '';
        $poll_setting['endtime'] = $poll_setting['endtime'] > 0 ? date('Y-m-d', $poll_setting['endtime']) : '';
        $title = $poll_setting['title'];
        $query = DB::query("SELECT * FROM ".DB::table('xplus_common_template')." WHERE mid='$module[mid]' AND available='1'");
        while($template = DB::fetch($query)) {
            $templates[] = $template;
        } 
           
        break;
        
    case 'meminfo' :
        if(!$pollid = intval($_G['gp_pollid'])) {
            showmessage('xplus:admincr_poll_null', ADMINCRSCRIPT.'&action=meminfo');
        }
        $fieldids = array('realname', 'gender', 'birthday', 'telephone', 'mobile', 'address', 'zipcode', 'company', 'occupation', 'qq', 'msn');
        if(submitcheck('addsubmit')) {
            $fieldid = dhtmlspecialchars($_G['gp_fieldid']);
            $label = dhtmlspecialchars($_G['gp_label']);
            if(in_array($fieldid, $fieldids) && !empty($label)) {
                $must = intval($_G['gp_must']) ? 1 : 0;
                $profileid = DB::result_first("SELECT profileid FROM ".DB::table('xplus_poll_profile')." WHERE fieldid='$fieldid' AND pollid='$pollid'");
                $data = array(
                    'fieldid' => $fieldid, 
                    'pollid' => $pollid, 
                    'label' => $label, 
                    'must' => $must
                );
                if(!$profileid) {
                    DB::insert('xplus_poll_profile', $data);
                } else {
                    DB::update('xplus_poll_profile', $data, "profileid='$profileid'");
                }
            }
            dheader("Location: ".ADMINCRSCRIPT."action=meminfo&pollid=$pollid".($inguide ? '&inguide=1' : ''));
        }
        
        if(submitcheck('modifysubmit')) {
            $delete = $_G['gp_delete'];
            if(!empty($delete) && is_array($delete)) {
                DB::delete('xplus_poll_profile', "profileid IN (".dimplode($delete).")");
            }
            $profile = $_G['gp_label'];
            if(!empty($profile) && is_array($profile)) {
                foreach($profile as $fieldid => $label) {
                    $fieldid = dhtmlspecialchars($fieldid);
                    $data = array(
                        'label' => dhtmlspecialchars($label),
                        'must' => $_G['gp_must'][$fieldid] ? 1 : 0,
                        'displayorder' => $_G['gp_displayorder'][$fieldid] ? intval($_G['gp_displayorder'][$fieldid]) : 0,
                    );
                    DB::update('xplus_poll_profile', $data, "fieldid='$fieldid' AND pollid='$pollid'");
                }
            }
            if($inguide) {
                showmessage('xplus:admincr_poll_create_success', ADMINCRSCRIPT."action=list");
            } else {
                showmessage('xplus:admincr_operate_success', ADMINCRSCRIPT."action=meminfo&pollid=$pollid");
            }
            
        }
        
        $query = DB::query("SELECT * FROM ".DB::table('common_member_profile_setting')." WHERE fieldid IN (".dimplode($fieldids).")");
        while($profile = DB::fetch($query)) {
            $profile['label'] = !empty($currentprofile[$profile['fieldid']]) ? $currentprofile[$profile['fieldid']]['label'] : $profile['title'];
            $profile['enable'] = !empty($currentprofile[$profile['fieldid']]) ? true : false;
            $profile['must'] = !empty($currentprofile[$profile['fieldid']]) ? $currentprofile[$profile['fieldid']]['must'] : 0;
            $profile['displayorder'] = !empty($currentprofile[$profile['fieldid']]) ? $currentprofile[$profile['fieldid']]['displayorder'] : 0;
            $profilelist[] = $profile;
        } 
        
        $query = DB::query("SELECT * FROM ".DB::table('xplus_poll_profile')." WHERE pollid='$pollid' ORDER BY displayorder");
        while($profile = DB::fetch($query)) {
           $currentprofile[$profile['fieldid']] = array(
               'profileid' => $profile['profileid'],
               'label' => $profile['label'],
               'must' => $profile['must'],
               'displayorder' => $profile['displayorder'],
           );  
        }

        break; 
    case 'items' :
        if(!$pollid = intval($_G['gp_pollid'])) {
            showmessage('xplus:admincr_poll_null', ADMINCRSCRIPT.'&action=items');
        }
        $manages = array('category', 'item', 'detail');
        $manage = in_array($_G['gp_manage'], $manages) ? $_G['gp_manage'] : 'item';
        if($manage == 'category') {
            if(submitcheck('addsubmit')) {
                $caption = trim($_G['gp_caption']);
                if(empty($caption)) {
                    showmessage('xplus:admincr_poll_nocaption', ADMINCRSCRIPT."action=items&manage=category&pollid=$pollid");
                }
                DB::insert('xplus_poll_category', array('pollid' => $pollid, 'caption' => dhtmlspecialchars($caption), 'displayorder' => 0));
                dheader("Location: ".ADMINCRSCRIPT."action=items&manage=category&pollid=$pollid".($inguide ? '&inguide=1' : ''));
            }
            if(submitcheck('editsubmit')) {
                $delete = $_G['gp_delete'];
                if(!empty($delete) && is_array($delete)) {
                    $categoryid = dimplode($delete);
                    DB::delete('xplus_poll_category', "categoryid IN ($categoryid)");
                }
                $displayorder = $_G['gp_displayorder'];
                $caption = $_G['gp_caption'];
                if($caption && is_array($caption)) {
                    foreach($caption as $categoryid => $value) {
                        if(empty($value)) {
                            continue;
                        }
                        $data = array(
                            'caption' => dhtmlspecialchars($value),
                            'displayorder' => $displayorder[$categoryid] ? intval($displayorder[$categoryid]) : 0,
                        );
                        DB::update('xplus_poll_category', $data, "categoryid='$categoryid'");
                    }
                }
                showmessage('xplus:admincr_success', ADMINCRSCRIPT."action=items&manage=category&pollid=$pollid".($inguide ? '&inguide=1' : ''));
                
            }
            $query = DB::query("SELECT * FROM ".DB::table('xplus_poll_category')." WHERE pollid='$pollid' ORDER BY displayorder");
            while($category = DB::fetch($query)) {
                $categorylist[] = $category;
            }
        } elseif($manage == 'item') {
            if(submitcheck('addsubmit')) {
                $subject = trim($_G['gp_subject']);
                if(empty($subject)) {
                    showmessage('xplus:admincr_poll_nosubject', ADMINCRSCRIPT."action=items&manage=item&pollid=$pollid".($inguide ? '&inguide=1' : ''));
                }
                $typelist = array('radio', 'checkbox', 'select', 'text', 'textarea');
                $type = trim($_G['gp_type']);
                $type = in_array($type, $typelist) ? $type : 'radio';
                $data = array(
                    'pollid' => $pollid,
                    'categoryid' => $_G['gp_category'] ? intval($_G['gp_category']) : 0,
                    'subject' => dhtmlspecialchars($subject),
                    'type' => $type,
                    'must' => $_G['gp_must'] ? 1 : 0,
                    'enable' => 1,
                );
                $itemid = DB::insert('xplus_poll_item', $data, true);
                if(in_array($type, array('radio', 'checkbox', 'select'))) {
                    $options = $_G['gp_options'];
                    if(!empty($options) && is_array($options)) {
                        foreach($options as $option) {
                            $option = trim($option);
                            if(!empty($option)) {
                                $data = array(
                                    'itemid' => $itemid,
                                    'caption' => dhtmlspecialchars($option),
                                    'count' => 0,
                                );
                                DB::insert('xplus_poll_option', $data);
                            }
                        }
                    }
                } 
                dheader("Location: ".ADMINCRSCRIPT."action=items&manage=item&pollid=$pollid".($inguide ? '&inguide=1' : ''));
            }
            if(submitcheck('editsubmit')) {
                $delete = $_G['gp_delete'];
                if(!empty($delete) && is_array($delete)) {
                    $itemid = dimplode($delete);
                    DB::delete('xplus_poll_item', "itemid IN ($itemid)");
                    DB::delete('xplus_poll_option', "itemid IN ($itemid)");
                }
                if($displayorder = $_G['gp_displayorder']) {
                    foreach($displayorder as $itemid => $value) {
                        $data = array(
                            'categoryid' => intval($_G['gp_category'][$itemid]),
                            'displayorder' => intval($value),
                            'enable' => in_array($itemid, $_G['gp_enable']) ? 1 : 0,
                            'must' => in_array($itemid, $_G['gp_must']) ? 1 : 0,
                        );
                        DB::update('xplus_poll_item', $data, "itemid='$itemid'");
                    }
                }
                if($inguide) {
                    dheader('Location: '.ADMINCRSCRIPT."action=meminfo&pollid=$pollid&inguide=1");
                } else {
                    showmessage('xplus:admincr_success', ADMINCRSCRIPT."action=items&manage=item&pollid=$pollid");
                }
            }
            $query = DB::query("SELECT * FROM ".DB::table('xplus_poll_category')." WHERE pollid='$pollid'");
            while($category = DB::fetch($query)) {
                $category['caption'] = cutstr($category['caption'], 14, '');
                $categorylist[] = $category;
            }
            $query = DB::query("SELECT * FROM ".DB::table('xplus_poll_item')." WHERE pollid='$pollid' ORDER BY displayorder");
            while($item = DB::fetch($query)) {
                $item['type'] = lang('plugin/xplus', 'admincr_poll_type_'.$item['type']);
                $itemlist[] = $item;
            }
        } elseif($manage == 'detail') {
            if(!$itemid = intval($_G['gp_itemid'])) {
                showmessage('xplus:admincr_poll_item_null', ADMINCRSCRIPT.'action=items&manage=item&pollid=$pollid');
            }
            if(submitcheck('editsubmit')) {
                $subject = trim($_G['gp_subject']);
                if(empty($subject)) {
                    showmessage('xplus:admincr_poll_nosubject', ADMINCRSCRIPT."action=items&manage=item&pollid=$pollid".($inguide ? '&inguide=1' : ''));
                }
                $typelist = array('radio', 'checkbox', 'select', 'text', 'textarea');
                $type = trim($_G['gp_type']);
                $type = in_array($type, $typelist) ? $type : 'radio';
                $data = array(
                    'pollid' => $pollid,
                    'categoryid' => $_G['gp_category'] ? intval($_G['gp_category']) : 0,
                    'subject' => dhtmlspecialchars($subject),
                    'type' => $type,
                    'must' => $_G['gp_must'] ? 1 : 0,
                    'enable' => 1,
                );
                DB::update('xplus_poll_item', $data, "itemid='$itemid'");
                if(!in_array($type, array('radio', 'checkbox', 'select'))) {
                    DB::delete('xplus_poll_option', "itemid='$itemid'");
                } else {
                    $options = $_G['gp_options'];
                    if(!empty($options) && is_array($options)) {
                        foreach($options as $caption) {
                            $caption = trim($caption);
                            if(empty($caption)) {
                                continue;
                            }
                            DB::insert('xplus_poll_option', array(
                                'caption' => dhtmlspecialchars($caption),
                                'itemid' => $itemid,
                                'displayorder' => 0,
                            ));
                        }
                    }
                }
                $delete = $_G['gp_delete'];
                if(!empty($delete) && is_array($delete)) {
                    DB::delete('xplus_poll_option', "optionid IN (".dimplode($delete).")");
                }
                $curoptions = $_G['gp_curoptions'];
                if(!empty($curoptions) && is_array($curoptions)) {
                    foreach($curoptions as $optionid => $caption) {
                        $caption = trim($caption);
                        if(empty($caption) || !$optionid) {
                            continue;
                        }
                        DB::update('xplus_poll_option', array(
                            'caption' => dhtmlspecialchars($caption),
                            'displayorder' => intval($_G['gp_displayorder'][$optionid])
                        ), "optionid='$optionid'");
                    }  
                }
                showmessage('xplus:admincr_success', ADMINCRSCRIPT."action=items&manage=detail&pollid=$pollid&itemid=$itemid".($inguide ? '&inguide=1' : ''));
            }
            $item = DB::fetch_first("SELECT * FROM ".DB::table('xplus_poll_item')." WHERE pollid='$pollid' AND itemid='$itemid'");
            if(!$item) {
                showmessage('xplus:admincr_poll_item_null', ADMINCRSCRIPT."action=items&manage=item&pollid=$pollid");
            } else {
                $query = DB::query("SELECT * FROM ".DB::table('xplus_poll_category')." WHERE pollid='$pollid'");
                while($category = DB::fetch($query)) {
                    $category['caption'] = cutstr($category['caption'], 14, '');
                    $categorylist[] = $category;
                }
                $query = DB::query("SELECT * FROM ".DB::table('xplus_poll_option')." WHERE itemid='$item[itemid]' ORDER BY displayorder");
                while($option = DB::fetch($query)) {
                    $optionlist[] = $option;
                }
            }
        }
        break;
        
    case 'statistics' :
        if(!$pollid = intval($_G['gp_pollid'])) {
            showmessage('xplus:admincr_poll_null', ADMINCRSCRIPT.'&action=meminfo');
        }
        
        $curpage = $page = intval($_G['gp_page']) > 0 ? intval($_G['gp_page']) : 1;
        $limit = $perpage = 5;
        $start = ($curpage - 1) * $perpage;
        
        $condition = "pollid='$pollid' AND type IN ('radio', 'checkbox', 'select')";
        $tatolcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xplus_poll_item')." WHERE $condition");
        if($tatolcount) {
            $pollcount = DB::result_first("SELECT count FROM ".DB::table('xplus_poll')." WHERE pollid='$pollid'");
            $multipage = multi($tatolcount, $perpage, $curpage, ADMINCRSCRIPT."action=statistics&pollid=$pollid");
            $query = DB::query("SELECT * FROM ".DB::table('xplus_poll_category')." WHERE pollid='$pollid'");
            while($category = DB::fetch($query)) {
                $categorylist[$category['categoryid']] = $category['caption'];
            }
            $query = DB::query("SELECT * FROM ".DB::table('xplus_poll_item')." WHERE $condition ORDER BY displayorder LIMIT $start, $limit");
            while($item = DB::fetch($query)) {
                $itemlist[$item['itemid']] = $item;
                $itemids[] = $item['itemid'];
            }
            if(count($itemids) > 0) {
                $query = DB::query("SELECT * FROM ".DB::table('xplus_poll_option')." WHERE itemid IN (".dimplode($itemids).")");
                while($option = DB::fetch($query)) {
                    $optionlist[$option['itemid']][$option['optionid']] = $option;
                    $optioncount[$option['itemid']] = isset($optioncount[$option['itemid']]) ? $optioncount[$option['itemid']] + $option['count'] : $option['count'];
                }
            }
        }
        $pollcount = $pollcount ? $pollcount : 0;
        break;
        
    case 'export' :
    
        if(!$pollid = intval($_G['gp_pollid'])) {
            showmessage('xplus:admincr_poll_null', ADMINCRSCRIPT."action=list");
        }
        
        set_time_limit(0);
		if(function_exists(ini_set)){
			ini_set('memory_limit','256M');
		}
        
        $filename = "poll_{$pollid}.csv";
        
        $csvstr = '';
        $csvstr = DB::result_first("SELECT title FROM ".DB::table('xplus_poll')." WHERE pollid='$pollid'");
        $csvstr .= "\n";
        
        $csvstr .= 'UID';
        $query = DB::query("SELECT * FROM ".DB::table('xplus_poll_profile')." WHERE pollid='$pollid'");
        while($profile = DB::fetch($query)) {
            $csvstr .= ','.$profile['label'];
            $profiles[] = $profile['fieldid'];;
        }
        
        $query = DB::query("SELECT * FROM ".DB::table('xplus_poll_item')." WHERE pollid='$pollid'");
        while($item = DB::fetch($query)) {
            $csvstr .= ','.$item['subject'];
            $items[] = $item['itemid'];
        }
        $csvstr .= "\n";
        
        $itemids = dimplode($items);
        $query = DB::query("SELECT * FROM ".DB::table('xplus_poll_option')." WHERE itemid IN ($itemids)");
        while($option = DB::fetch($query)) {
            $options[$option['itemid']][$option['optionid']] = str_replace(array('[text]', '[city]'), '', $option['caption']);
        }
        
        $query = DB::query("SELECT * FROM ".DB::table('xplus_poll_result')." WHERE pollid='$pollid'");
        while($result = DB::fetch($query)) {
            $result['detail'] = unserialize($result['detail']);
            $result['profile'] =  unserialize($result['profile']);
            
            $csvstr .= $result['uid'];
            foreach($profiles as $fieldid) {
                $csvstr .= ',"'.str_replace("\"", '', $result['profile'][$fieldid]).'"';
            }
            foreach($items as $itemid) {
                if(is_array($result['detail'][$itemid])) {
                    foreach($result['detail'][$itemid] as $optionid => $value) {
                        if($value == 1) {
                            $resultcsv .= $resultcsv ? ','.$options[$itemid][$optionid] : $options[$itemid][$optionid];
                        } elseif(!empty($value)) {
                            $resultcsv .= $resultcsv ? ','.$options[$itemid][$optionid].'|'.$value : $options[$itemid][$optionid].'|'.$value;
                        }   
                    }
                } else {
                    $resultcsv .= $result['detail'][$itemid];
                }
                $csvstr .= ',"'.str_replace("\"", '', $resultcsv).'"';
                unset($resultcsv);
            }
            $csvstr .= "\n";
        }
        
        ob_end_clean();
        
        header('Content-Encoding: none');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.$filename);
		header('Pragma: no-cache');
		header('Expires: 0');
		if($_G['charset'] != 'gbk') {
			$csvstr = diconv($csvstr, $_G['charset'], 'GBK');
		}
        echo $csvstr;
        exit();
        break;
}

include template('xplus:admincr/poll');

?>