<?php
/*
 *	Author: IAN - zhouxingming
 *	Last modified: 2011-10-25 17:05
 *	Filename: list.inc.php
 *	Description: 
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$page = max(1, intval($_G['gp_page']));
$each = 50;
$start = ($page - 1) * $each;

include libfile('function/forum');
$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('threadlink_base'));
$query = DB::query("SELECT tid FROM ".DB::table('threadlink_base')." LIMIT $start,$each");
while($thread = DB::fetch($query)) {
	$tids[] = $thread['tid'];
}

$fields = 'tid,subject,author,authorid,lastpost,lastposter,dateline,replies,views';
if(!$_G['cache']['threadtableids']) {
	$query = DB::query("SELECT $fields FROM ".DB::table('forum_thread')." WHERE tid IN (".implode(',', $tids).") LIMIT $each");
	while($thread = DB::fetch($query)) {
		$thread['dateline'] = dgmdate($thread['dateline']);
		$thread['lastpost'] = dgmdate($thread['lastpost']);
		$threads[] = $thread;
	}
} else {
	foreach($threadtableids as $tableid) {
		$table = $tableid > 0 ? "forum_thread_{$tableid}" : 'forum_thread';
		$query = DB::query("SELECT $fields FROM ".DB::table($table)." WHERE tid IN (".implode(',', $tids).") LIMIT $each");
		while($thread = DB::fetch($query)) {
			$thread['dateline'] = dgmdate($thread['dateline']);
			$thread['lastpost'] = dgmdate($thread['lastpost']);
			$threads[] = $thread;
		}
	}
}
$multipage = multi($count, $each, $page, 'plugin.php?id=threadlink:list');

include template('threadlink:list');
?>
