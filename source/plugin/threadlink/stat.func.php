<?php
/*
 *	Author: IAN - zhouxingming
 *	Last modified: 2011-10-25 11:02
 *	Filename: stat.func.php
 *	Description: 统计信息函数库
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function check_row($daytime) {
	global $threadlink_exists;
	$exsits = DB::result_first("SELECT COUNT(*) FROM ".DB::table('threadlink_stat')." WHERE daytime='$daytime'");
	return $threadlink_exists = $exsits ? 1 : -1;
}

function table_update($field, $value, $exsits, $daytime) {
	if($exsits > 0) {
		$sql = "UPDATE ".DB::table('threadlink_stat')." SET $field=".($value ? "'$value'" : "$field+1")." WHERE daytime='$daytime'";
	} else {
		$sql = "INSERT INTO ".DB::table('threadlink_stat')." (daytime,$field) VALUES ('$daytime',".($value ? "'$value'" : "'1'").")";
	}
	DB::query($sql);
}

//更新当日发帖数
function update_posts($daytime, $posts) {
	global $threadlink_exists;
	if(!$threadlink_exists) {
		check_row($daytime);
	}
	table_update('posts', $posts, $threadlink_exists, $daytime);
}

//更新当日创建的base数
function update_create_base($daytime) {
	global $threadlink_exists;
	if(!$threadlink_exists) {
		check_row($daytime);
	}
	table_update('create_base', 0, $threadlink_exists, $daytime);
}

//更新当日修改的link数
function update_modify_link($daytime) {
	global $threadlink_exists;
	if(!$threadlink_exists) {
		check_row($daytime);
	}
	table_update('modify_link', 0, $threadlink_exists, $daytime);
}

//更新当然创建的link数
function update_create_link($daytime) {
	global $threadlink_exists;
	if(!$threadlink_exists) {
		check_row($daytime);
	}
	table_update('create_link', 0, $threadlink_exists, $daytime);
}

//更新当日的link点击中from=threadlink的数量
function update_clicks($daytime) {
	global $threadlink_exists;
	if(!$threadlink_exists) {
		check_row($daytime);
	}
	table_update('clicks', 0, $threadlink_exists, $daytime);
}

?>
