<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_poll.php 2 2011-12-21 Z Niexinyuan $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function savepollnewsetting($pollid, $newsetting) {
    if(!is_array($newsetting)) {
        showmessage('xplus:admincr_data_error', "plugin.php?id=xplus:admincr&mod=poll&action=setting&pollid=$pollid");
    }
    $newsetting['starttime'] = strtotime($newsetting['starttime']);
    $newsetting['endtime'] = strtotime($newsetting['endtime']);
    if($newsetting['starttime'] > $newsetting['endtime']) {
        showmessage('xplus:admincr_date_error', "plugin.php?id=xplus:admincr&mod=poll&action=setting&pollid=$pollid");
    }
    $title = dhtmlspecialchars(trim($newsetting['title']));
    if(empty($title)) {
        showmessage('xplus:admincr_poll_notitle', "plugin.php?id=xplus:admincr&mod=poll&action=setting&pollid=$pollid");
    }
    $data = array(
        'templateid' => intval($newsetting['template']),
        'title' => $title,
        'introduce' => dhtmlspecialchars(trim($newsetting['introduce'])),
        'keywords' => dhtmlspecialchars(cutstr(trim($newsetting['keywords']), 252)),
        'description' => dhtmlspecialchars(cutstr(trim($newsetting['description']), 252)),
        'allowalluser' => $newsetting['allowalluser'] ? 1 : 0,
        'forwardurl' => dhtmlspecialchars(trim($newsetting['forwardurl'])),
        'available' => $newsetting['available'] ? 1 : 0,
        'starttime' => intval($newsetting['starttime']),
        'endtime' => intval($newsetting['endtime']),
    );
    DB::update('xplus_poll', $data, "`pollid`='$pollid'");       
}

function mergeresult($source, $object = array(), $cover = false) {
    foreach($object as $key => $value) {
        $source[$key] =  isset($source[$key]) && !$cover ? $source[$key] : $value;
    }
    return $source;
}


?>
