<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_vote.php 16840 2011-09-06 08:19:59Z Niexinyuan $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function getcookiekey($voteid, $usecheck) {
	global $_G;
	if($usecheck == 'cookie') {
		return "repeat_cookie_{$voteid}";
	} elseif($usecheck == 'username') {
		return "repeat_username_{$_G['uid']}_{$voteid}";
	} elseif($usecheck == 'ip') {
		return "repeat_ip_{$_G['strictip']}_{$voteid}";
	} elseif($usecheck == 'so') {
		return "repeat_so_{$_G['cookie']['soid']}_{$voteid}";
	}
}

function checkrepeat($repeattype) {
	$check = array();
	$repeatarr = array(
		1 => 'username',
		2 => 'ip',
		3 => 'so',
	);
	$repeatcount = count($repeatarr);
	$repeattype = (string) intval($repeattype);
	$repeattype = sprintf('%0'.$repeatcount.'b', $repeattype);
	$i = 1;
	foreach($repeatarr as $key => $value) {
		$check[$value] = intval($repeattype{$repeatcount - $i});
		$i++;
	}
	return $check;
}

function showsoflash($pollid) {
	global $_G;
	return "
		<div id=\"soflash\">
			<script type=\"text/javascript\">
				$('soflash').innerHTML = AC_FL_RunContent(
					'width', '1', 'height', '1',
					'src', '".STATICURL."image/common/soflash.swf?site={$_G['siteurl']}plugin.php%3fid=xplus:so%26&module=poll&pollid={$pollid}&operation=config&random=".random(4)."',
					'quality', 'high',
					'id', 'soflash_swf',
					'menu', 'false',
					'allowScriptAccess', 'always',
					'wmode', 'transparent'
				);
			</script>
		</div>
	";
}

function showuploadflash($voteid) {
    global $_G; 
    return "
        <div class=\"fswf\" id=\"multiimg\">
			<script type=\"text/javascript\">
				$('multiimg').innerHTML = AC_FL_RunContent(
					'width', '470', 'height', '268',
					'src', '".STATICURL."image/common/upload.swf?site={$_G['siteurl']}plugin.php%3fid=xplus:misc%26mod=swfupload%26type=image%26voteid=$voteid%26random=".random(4)."',
					'quality', 'high',
					'id', 'swfupload',
					'menu', 'false',
					'allowScriptAccess', 'always',
					'wmode', 'transparent'
				);
				function swfHandler(action) {
				    if(action == 2) {
    				    url_forward = 'plugin.php?id=xplus:admincr&mod=vote&action=choiceed&voteid=$voteid';
    				    location.href=url_forward;
                    }
                }
			</script>
		</div>
    ";  
}

function getvotesetting($voteid) {
    $vote_setting = DB::fetch_first("SELECT * FROM ".DB::table('xplus_vote')." xv 
        LEFT JOIN ".DB::table('xplus_vote_field')." xvf ON xv.voteid=xvf.voteid
        WHERE xv.`voteid`='$voteid'"
    );
                
    if(!$vote_setting) {
        showmessage('xplus:admincr_unkownparams', dreferer());
    }
    $vote_setting['repeattype_username'] = getstatus($vote_setting['repeattype'], 1);
    $vote_setting['repeattype_ip'] = getstatus($vote_setting['repeattype'], 2);
    $vote_setting['repeattype_so'] = getstatus($vote_setting['repeattype'], 3);
        
    return $vote_setting;         
}

function savevotenewsetting($voteid, $newsetting) {
    if(!is_array($newsetting)) {
        showmessage('xplus:admincr_data_error', "plugin.php?id=xplus:admincr&mod=vote&action=guide&step=2&voteid=$voteid");
    }
    $repeattype = 0;
    if(is_array($newsetting['repeattype'])) {                        
        $repeattype = setstatus(1, $newsetting['repeattype']['username'] ? 1 : 0, $repeattype);
        $repeattype = setstatus(2, $newsetting['repeattype']['ip'] ? 1 : 0, $repeattype);
        $repeattype = setstatus(3, $newsetting['repeattype']['so'] ? 1 : 0, $repeattype);
    } 
    $newsetting['repeattype'] = $repeattype;
    $newsetting['starttime'] = strtotime($newsetting['starttime']);
    $newsetting['endtime'] = strtotime($newsetting['endtime']);
    if($newsetting['starttime'] && $newsetting['endtime'] && $newsetting['starttime'] > $newsetting['endtime']) {
        showmessage('xplus:admincr_date_error', "plugin.php?id=xplus:admincr&mod=vote&action=guide&step=2&voteid=$voteid");
    }
                    
    $data_filed = array(
        'description' => trim($newsetting['description']),
        'seokeywords' => dhtmlspecialchars(trim($newsetting['seokeywords'])),
        'seodesc' => dhtmlspecialchars(trim($newsetting['seodesc'])),
        'lazyload' => intval($newsetting['lazyload']),
    );
    $ifhasfield = DB::result_first("SELECT voteid FROM ".DB::table('xplus_vote_field')." WHERE `voteid`='$voteid'");
    if($ifhasfield) {
        DB::update('xplus_vote_field', $data_filed, "`voteid`='$voteid'");
    } else {
        $data_filed['voteid'] = $voteid;
        DB::insert('xplus_vote_field', $data_filed);
    }
                    
    $data = array(
        'title' => dhtmlspecialchars($newsetting['title']),
        'available' => intval($newsetting['available']),
        'numperpage' => intval($newsetting['numperpage']),
        'repeattype' => intval($newsetting['repeattype']),
        'limittime' => intval($newsetting['limittime']),
        'type' => intval($newsetting['type']),
        'templateid' => intval($newsetting['template']),
        'choicenum' => intval($newsetting['choicenum']),
        'contenttype' => intval($newsetting['contenttype']),
        'repeatlimit' => intval($newsetting['repeatlimit']),
        'maxnum' => intval($newsetting['maxnum']),
        'resultview_mod' => intval($newsetting['resultview_mod']),
        'resultview_time' => intval($newsetting['resultview_time']),
        'errordetail' => intval($newsetting['errordetail']),
        'starttime' => intval($newsetting['starttime']),
        'endtime' => intval($newsetting['endtime']),
        'choicerepeat' => intval($newsetting['choicerepeat'])
    );
    DB::update('xplus_vote', $data, "`voteid`='$voteid'");   
    
    return;                
}

function getvotelastdate($voteid, $type = 'username', $value = '') {
    $condition = "`voteid`=$voteid";
    switch($type) {
        case 'username' : $condition .= " AND `uid`='$value'"; break;
        case 'ip' : $condition .= " AND `ip`='$value'"; break;
    }
    $query = DB::query("SELECT choiceid, dateline FROM ".DB::table('xplus_vote_value')." WHERE $condition");
    while($value = DB::fetch($query)) {
        $valuelist[$value['dateline']][] = $value['choiceid'];
    }
    unset($datelist);
    return $valuelist;
}

?>