<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincr_vote.php 3 2011-12-21 Z Niexinyuan $
 */
 
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require xplus_libfile('function/vote');

$action = in_array($_G['gp_action'], array('list', 'guide', 'setting', 'choiceed')) ? $_G['gp_action'] : 'list';
$inguide = $_G['gp_inguide'] ? 1 : 0;

$lang = lang('plugin/xplus');

switch($action) {
    case 'list' :  
        if(submitcheck('listsubmit')) {
            if($voteid = $_G['gp_delete']) {
                $condition = "voteid IN (".(is_array($voteid) ? dimplode($voteid) : '\'$voteid\'').")";
                $query = DB::query('SELECT aid FROM '.DB::table('xplus_vote_choice')." WHERE $condition AND aid>'0'");
    			while($choicerow = DB::fetch($query)) {
    				$aids[] = $choicerow['aid'];
    			}
    			if($aids) {
    				delete_images($aids);
    			} 
                     
                DB::delete('xplus_vote', $condition);          
                DB::delete('xplus_vote_choice', $condition);
                DB::delete('xplus_vote_field', $condition);
                DB::delete('xplus_vote_value', $condition);
                
                showmessage('xplus:admincr_delete_success', ADMINCRSCRIPT.'action=list');
            } else {
                showmessage('xplus:admincr_delete_noobject', ADMINCRSCRIPT.'action=list');
            }
        }
        
        $condition = '';
        if(submitcheck('searchsubmit') && $key = $_G['gp_searchkey']) {
            switch($_G['gp_searchtype']) {
                case 'id' : $condition .= " AND `voteid`='$key'"; break;
                case 'title' : $condition .= " AND `title` LIKE '%$key%'"; break;
                case 'username' : $condition .= " AND `username`='$key'"; break;
            }
        } 
        
        $type = $_G['gp_type'] == 'admin';
        $condition .= $type ? '' : " AND `username`='$_G[username]'";
        
        $limit = $perpage = 10;
        $start = ($curpage = is_numeric($_G['gp_page']) && $_G['gp_page'] > 0 ? $_G['gp_page'] : 1) - 1;                 
        $tatolcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xplus_vote')." WHERE 1 $condition");
        $multipage = multi($tatolcount, $perpage, $curpage, ADMINCRSCRIPT.'action=list');
        
        $query = DB::query("SELECT voteid, title, username, dateline, endtime, available, starttime FROM ".DB::table('xplus_vote')
            ." WHERE dateline>0 $condition ORDER BY dateline DESC LIMIT $start, $limit"
        );

        while($vote = DB::fetch($query)) {
            $vote['cuttitle'] = cutstr($vote['title'], 35);    
            $vote['dateline'] = dgmdate($vote['dateline'], 'd');
            $vote['status'] = $vote['available'] ? $lang['admincr_enable'] : $lang['admincr_disable'];
            $vote['starttime'] = $vote['starttime'] ? dgmdate($vote['starttime'], 'd') : $lang['admincr_timelimit'];
            $vote['endtime'] = $vote['endtime'] ? dgmdate($vote['endtime'], 'd') : $lang['admincr_timelimit'];
            $votes[] = $vote;
        }
        break;
    
    case 'guide' :
        if(submitcheck('submit')) {
            if(!$title = dhtmlspecialchars(trim($_G['gp_title']))) {
                showmessage('xplus:admincr_notitle', ADMINCRSCRIPT.'action=guide');
            }
            DB::insert('xplus_vote', 
                array(
                    'title' => $title, 
                    'username' => $_G['username'], 
                    'dateline' => TIMESTAMP, 
                    'repeattype' => 1, 
                    'repeatlimit' => 1,
                    'maxnum' => 1,
                    
                )
            );
            if($voteid = DB::insert_id()) {
                dheader("Location: ".ADMINCRSCRIPT."action=setting&voteid=$voteid&inguide=1");
            } else {
                showmessage('xplus:admincr_createvote_failed', ADMINCRSCRIPT.'action=guide');
            }
        } 
        break;
        
    case 'setting' :
        if(!$voteid = intval($_G['gp_voteid'])) {
            showmessage('xplus:admincr_unkownparams', ADMINCRSCRIPT.'action=list');
        }      
        if(submitcheck('settingsubmit')) {
            $newsetting = $_G['gp_newsetting'];
            $newsetting['voteid'] = $voteid;
            savevotenewsetting($voteid, $newsetting);
            if($inguide) {
                dheader("Location: ".ADMINCRSCRIPT."action=choiceed&inguide=1&voteid=$voteid");
            } else {
                showmessage('xplus:admincr_edit_success', ADMINCRSCRIPT."action=setting&voteid=$voteid");
            }
        }
        $vote_setting = getvotesetting($voteid);
        $vote_setting['starttime'] = $vote_setting['starttime'] > 0 ? date('Y-m-d H:i', $vote_setting['starttime']) : '';
        $vote_setting['endtime'] = $vote_setting['endtime'] > 0 ? date('Y-m-d H:i', $vote_setting['endtime']) : '';      
        $title = $vote_setting['title'];
        $query = DB::query("SELECT * FROM ".DB::table('xplus_common_template')." WHERE mid='$module[mid]' AND available='1'");
        while($template = DB::fetch($query)) {
            $templates[] = $template;
        }
        break;
        
    case 'choiceed' :
        if(!$voteid = intval($_G['gp_voteid'])) {
            showmessage('xplus:admincr_unkownparams', ADMINCRSCRIPT.'action=list');
        }  
        
        if(submitcheck('addsubmit')) {
            $choices = $_G['gp_choices'];
            if($choices && is_array($choices)) {
                foreach($choices as $caption) {
                    $caption = trim($caption);
                    if(empty($caption)) {
                        continue;
                    }
                    $data_choice = array(
                        'voteid' => $voteid,
                        'caption' => dhtmlspecialchars($caption),
                    );
                    DB::insert('xplus_vote_choice', $data_choice);
                }
            }
            if($inguide) {
                dheader("Location: ".ADMINCRSCRIPT."action=choiceed&inguide=1&voteid=$voteid");
            } else {
                dheader("Location: ".ADMINCRSCRIPT."action=choiceed&voteid=$voteid");
            }
        }
        
        $uploadimage = DB::result_first("SELECT contenttype FROM ".DB::table('xplus_vote')." WHERE `voteid`='$voteid'") ? true : false; 
        
        if(submitcheck('listsubmit')) {
            if($choiceid = dimplode($_G['gp_delete'])) {
                $query = DB::query('SELECT aid FROM '.DB::table('xplus_vote_choice')." WHERE choiceid IN ($choiceid)");
                while($aid = DB::fetch($query)) {
                    $aids[] = $aid['aid'];
                }
                if($aids) {
					delete_images($aids);
				}
                
                DB::delete('xplus_vote_choice', "choiceid IN ($choiceid)");
    			DB::delete('xplus_vote_value', "choiceid IN ($choiceid)");
            }
            if($choicecaption = $_G['gp_caption']) {
                foreach($choicecaption as $choiceid => $caption) {
                    if(!trim($caption) === '') {
                        continue;
                    }
                    $data_choice = array(
                        'caption' => dhtmlspecialchars(trim($caption)),
                        'detailurl' => trim($_G['gp_detailurl'][$choiceid]),
                        'displayorder' => intval($_G['gp_displayorder'][$choiceid]),
                        'description' => $_G['gp_description'][$choiceid],
                        'basicnum' => intval($_G['gp_basicnum'][$choiceid]),
                    );
                    if($_FILES['uploadfile']['name'][$choiceid]) {
                        foreach($_FILES['uploadfile'] as $key => $val) {
                            $file[$key] = $val[$choiceid];
                        }
                        $attach = upload_images($file, 'common', true, 176, 176);
                        $data_choice['imageurl'] = $attach['attachment'];
                        $aid = DB::result_first('SELECT aid FROM '.DB::table('xplus_vote_choice')." WHERE choiceid='$choiceid'");
                        delete_images($aids);
                    }  
                    DB::update('xplus_vote_choice', $data_choice, "`choiceid`='$choiceid'");              
                }
            }    
            if($inguide) {
                showmessage('xplus:admincr_vote_create_success', ADMINCRSCRIPT."action=list");
            } else {
                showmessage('xplus:admincr_operate_success', ADMINCRSCRIPT."action=choiceed&voteid=$voteid");
            }     
        } 
        
        if($uploadimage) {
            $uploadflash = showuploadflash($voteid);
        } 
        
        $totalcount = 0;
        $query = DB::query("SELECT * FROM ".DB::table('xplus_vote_choice')." WHERE `voteid`='$voteid' ORDER BY displayorder");
        while($choice = DB::fetch($query)) {
            $choices[] = array(
                'choiceid' => $choice['choiceid'],
                'caption' => $choice['caption'],
				'displayorder' => $choice['displayorder'],
				'imageurl' => $choice['imageurl'],
				'detailurl' => $choice['detailurl'],
                'thumburl' => $choice['imageurl'] ? $_G['setting']['attachurl'].'common/'.$choice['imageurl'].'.thumb.jpg' : '',
                'basicnum' => $choice['basicnum'] ? $choice['basicnum'] : 0,
                'votenum' => $choice['votenum'] ? $choice['votenum'] : 0,
                'description' => $choice['description'],
                'totalnum' => $choice['basicnum'] + $choice['votenum'],
            );
            $totalcount += $choice['basicnum'] + $choice['votenum'];
        }
        break;  
    default :
        break;        
}

include template('xplus:admincr/vote');

?>