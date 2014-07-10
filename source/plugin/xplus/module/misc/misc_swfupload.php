<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_swfupload.php 16840 2011-09-21 08:19:59Z Niexinyuan $
 */
 
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($_G['gp_operation'] == 'config' && checkadmincr()) {
    
	$swfhash = md5(substr(md5($_G['config']['security']['authkey']), 8).$_G['uid']);
	$xmllang = lang('forum/swfupload');
	$imageexts = array('jpg','jpeg','gif','png','bmp');
	$attachextensions = '*.'.implode(',*.', $imageexts);
    $max = 0;
	$max = @ini_get(upload_max_filesize);
	$unit = strtolower(substr($max, -1, 1));
	if($unit == 'k') {
		$max = intval($max)*1024;
	} elseif($unit == 'm') {
		$max = intval($max)*1024*1024;
	} elseif($unit == 'g') {
		$max = intval($max)*1024*1024*1024;
	}
	$depict = 'Image File ';
	
    ob_start();
    @header("Expires: -1");
    @header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
    @header("Pragma: no-cache");
    @header("Content-type: application/xml; charset=UTF-8");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><parameter><allowsExtend><extend depict=\"$depict\">{$attachextensions}</extend></allowsExtend><language>$xmllang</language><config><userid>$_G[uid]</userid><hash>$swfhash</hash><maxupload>{$maxattachsize}</maxupload></config></parameter>";
    
} elseif($_G['gp_operation'] == 'upload') {

	require xplus_libfile('class/voteupload');
	$_FILES['Filedata']['name'] = addslashes(diconv(urldecode($_FILES['Filedata']['name']), 'UTF-8', CHARSET));
	$upload = new vote_upload();

}

?>