<?php

/**
 *      [Discuz!] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: vote_index.php 3 2011-12-21 Z Niexinyuan $
 */
 
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require xplus_libfile('function/vote');
loadcache('xplus_common_config');
$vote = array();
$metakeywords = $metadescription = $title = '';
$voteid = !empty($_G['gp_vid']) ? intval($_G['gp_vid']) : 0;
$lang = $_G['cache']['xplus_common_config'] ? $_G['cache']['xplus_common_config'] : '';
$voteurl = $moduleurl."&vid=$voteid";
$iniframe = !empty($_G['gp_iniframe']) ? 1 : 0;
if($iniframe) {
    $width = !empty($_G['gp_width']) ? intval($_G['gp_width']) : 500;
    $height = !empty($_G['gp_height']) ? intval($_G['gp_height']) : 500;
    $voteurl .= $voteurl."&iniframe=1&width=$width&height=$height";
}
if(!$voteid) {
    showmessage('xplus:vote_inexistence');
} else {
    $vote = DB::fetch_first("SELECT * FROM ".DB::table('xplus_vote')." xv 
        LEFT JOIN ".DB::table('xplus_vote_field')." xvf ON xv.voteid=xvf.voteid
        WHERE xv.`voteid`='$voteid'"
    );
    if(!$vote['available'] && ($_G['adminid'] != 1 || $_G['username'] != $vote['username'])) {
        showmessage('xplus:vote_inexistence');
    }
    $vote['repeattype_username'] = getstatus($vote['repeattype'], 1);
    $vote['repeattype_ip'] = getstatus($vote['repeattype'], 2);
    $vote['repeattype_so'] = getstatus($vote['repeattype'], 3);
    
    if($vote['repeatlimit'] && ($vote['repeattype_username'] || $vote['repeattype_ip'])) {
        $condition = ($vote['repeattype_username'] ? " AND uid='$_G[uid]'" : '').($vote['repeattype_ip'] ? " AND ip='$_G[clientip]'" : '');
        $hasvoted = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xplus_vote_value')." WHERE 1 $condition");
    } else {
        $hasvoted = true;
    }

    $isshowresult = $vote['resultview_time'] 
        ? ($hasvoted ? ($vote['resultview_mod'] ? true : ($_G['uid'] ? true : false)) : false) 
        : ($vote['resultview_mod'] ? true : ($_G['uid'] ? true : false)); 
    
    $ismultipute = $vote['type'] ? true : false;
    $ismagevote = $vote['contenttype'] ? true : false;
}

if($_G['gp_action'] == 'votesubmit') {

    if($vote['starttime'] > TIMESTAMP) {
		showmessage('xplus:vote_unstart');
	}
    
    if(!empty($vote['endtime']) && $vote['endtime'] < TIMESTAMP) {
		showmessage('xplus:vote_expired');
	}
    
	$processname = 'V'.substr(md5("{$itemid}_{$_G['strictip']}"), 8, 8);
	
    if(discuz_process::islocked($processname, 5)) {
        showmessage('xplus:vote_refresh');
	}

	$choice_arr = $choicelist = array();

	if(is_array($_G['gp_choose_value'])) {
		$choicelist = dhtmlspecialchars($_G['gp_choose_value']);
	} else {
		$choicelist[] = dhtmlspecialchars($_G['gp_choose_value']);
	}

	foreach($choicelist as $value) {
		$value = intval($value);
		if($value > 0) {
			$choice_arr[$value] = $value;
		}
	}

	if(empty($choice_arr)) {
		showmessage('xplus:vote_choice_null');
	} elseif($ismultipute && $vote['choicenum'] > 0 && count($choice_arr) > $vote['choicenum']) {
		showmessage($lang['admincp_vote_choicenum'], '', array('num' => $vote['choicenum'] ));
	} elseif(!$ismultipute && count($choice_arr) > 1) {
		showmessage($lang['admincp_vote_choicenum'], '', array('num' => $vote['choicenum'] ));
	}
    
    if(!empty($vote['repeatlimit'])) {
        if(!empty($vote['repeattype'])) {
            $check = checkrepeat($vote['repeattype']);  
            if($check['username']) {
                if(empty($_G['uid'])) {
                    $url_forward = $iniframe ? '' : 'member.php?mod=logging&action=login';
    				showmessage('xplus:vote_guest_unallowed', $url_forward, '', array('showmsg' => true, 'login' => 1));
    			}
                
                if(in_array($_G['groupid'],array(8,6,5,4))){
                    showmessage('您所在的用户组不允许投票');
                }
                
                $lastvoteinfo['username'] = getvotelastdate($voteid, 'username', $_G['uid']);  
            }   
            if($check['ip']) {
                $lastvoteinfo['ip'] = getvotelastdate($voteid, 'ip', $_G['clientip']);
            }
        }
        $vote['limittime'] = $vote['limittime']?$vote['limittime']:1;
        foreach($lastvoteinfo as $key => $voteinfo) {
            
            
      
            if($vote['maxnum'] > 0){
               
                if(count($voteinfo) >= $vote['maxnum']){
                    showmessage($lang['vote_repeat_vote'], '', array('maxnum' => $vote['maxnum']));
                }
                $cknum = 0;
                foreach($voteinfo as $dateline =>$choices){
                    $cknum +=  count($choices);
                }
                
                if( $cknum>= $vote['maxnum']) {
                    showmessage($lang['vote_repeat_vote'], '', array('maxnum' => $vote['maxnum']));
                }  
                    
            } 
            
            foreach($voteinfo as $dateline => $choices) {
                if($vote['limittime']) {
                    if($dateline + $vote['limittime'] > TIMESTAMP) {
                       
                        showmessage($vote['limittime'].'秒钟只能投一次');
                    }
                }  
                if(!$vote['choicerepeat']) {
                    foreach($choices as $choiceid) {
                        if(!in_array($choiceid, $choice_arr)) {
                            continue;
                        }
                        showmessage($lang['vote_repeat_item'], '');
                    }
                }           
            }
        }
    }
       
	$votenum = array();
	foreach($choice_arr as $value) {
		empty($votenum[$value]) && $votenum[$value] = 1;
		$data = array(
			'uid' => $_G['uid'],
			'ip' => $_G['clientip'],
			'dateline' => TIMESTAMP,
			'voteid' => $voteid,
			'choiceid' => $value,
			'soid' => $_G['cookie']['soid']
		);
        DB::insert('xplus_vote_value', $data);
	}

	$tnum = 0;
	$returnjs_num = array();
	foreach($votenum as $choiceid => $value) { 
        DB::query("UPDATE ".DB::table('xplus_vote_choice')." SET votenum = (votenum + $value) WHERE `choiceid`='$choiceid'");
		if(DB::affected_rows()) {
			$tnum += $value;
			$returnjs_num[] = $choiceid;
		}
	}
	if($tnum > 0) {
        DB::query("UPDATE ".DB::table('xplus_vote')." SET totalnum = (totalnum + $tnum) WHERE `voteid`='$voteid'");
	}
    	
	discuz_process::unlock($processname);

    showmessage($lang['vote_success'], $voteurl, $returnjs_num);
 
} else {
    
    $allowvote = (!$vote['starttime'] || $vote['starttime'] <= TIMESTAMP) && (!$vote['endtime'] || $vote['endtime'] >= TIMESTAMP);

    $template = DB::fetch_first("SELECT * FROM ".DB::table('xplus_common_template')." WHERE templateid='$vote[templateid]' AND available='1' AND mid='$module[mid]'");
    if(!$template) {
        showmessage('xplus:admincr_notemplate');
    }
    $_G['showmessage']['cssurl'] = 'source/plugin/xplus/template/'.$module['identifier'].'/'.$template['directory'].'/'.$module['identifier'].'.css';
    $_G['showmessage']['jspath'] = 'source/plugin/xplus/static/js/';
    $_G['showmessage']['imgpath'] = 'source/plugin/xplus/template/'.$module['identifier'].'/'.$template['directory'].'/image/';
  
    $metakeywords = $vote['seokeywords'];
   	$metadescription = $vote['seodesc'];

	$title = $vote['title'].' - '.$_G['setting']['bbname'];
    $voteurl = "plugin.php?id=xplus:vote&vid=$voteid";
    
    if($ismagevote) {
        $count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xplus_vote_choice')." WHERE voteid='$voteid'");
        if(!$count) {
            showmessage('xplus:vote_incomplete');
        }
        $numperpage = $vote['numperpage'] > 0 && $vote['numperpage'] < 200 ? $vote['numperpage'] : 200;
        $pagenum = ceil($count / $numperpage);
        $page = !empty($_G['gp_page']) ? intval($_G['gp_page']) : 1;
        $curpage = min($pagenum, $page);
        $start = ($curpage - 1) * $numperpage;
        $limit = " LIMIT $start, $numperpage";
        $multi = multi($count, $numperpage, $curpage, $voteurl);
    }
    
    $query = DB::query("SELECT * FROM ".DB::table('xplus_vote_choice')." WHERE voteid='$voteid' ORDER BY displayorder $limit");
    $totalcount = 0;
    while($choice = DB::fetch($query)) {
        $choice['imagethumb'] = $ismagevote && $choice['imageurl'] ? $_G['setting']['attachurl'].'common/'.$choice['imageurl'].'.thumb.jpg' : 'static/image/common/default.jpg';
		$choice['image'] = $ismagevote && $choice['imageurl'] ? $_G['setting']['attachurl'].'common/'.$choice['imageurl'] : 'static/image/common/default.jpg';
		$choice['votenum'] += $choice['basicnum'];
        $totalcount += $choice['votenum'];
		$choice['detailurl'] = dhtmlspecialchars($choice['detailurl']);
		$choices[] = $choice;
    }
    while (list($key) = each($choices)) {
		$prevkey = $key - 1;
		$nextkey = $key + 1;
		$choices[$key]['prevchoiceid'] = !empty($choices[$prevkey]['choiceid']) ? $choices[$prevkey]['choiceid'] : NULL;
		$choices[$key]['nextchoiceid'] = !empty($choices[$nextkey]['choiceid']) ? $choices[$nextkey]['choiceid'] : NULL;
	}
    
    $so = showsoflash($voteid);

    $tplfile = $iniframe ? 'index_iframe' : 'index';
    include template('xplus:vote/'.$template['directory'].'/'.$tplfile);       
}

?>
