<?php
/*
 *	Author: IAN - zhouxingming
 *	Last modified: 2011-09-06 17:09
 *	Filename: mod.inc.php
 *	Description: 
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

//可以使用的模版名,添加模版请修改此变量
//'{文件名}' => '中文名称'
$tltpls = array(
	'tl_pic' => pl('default_tpl'),
	'tl_mpic' => pl('image_tpl'),
);


$allowgroups = unserialize($_G['cache']['plugin']['threadlink']['allowgroups']);
$allowaction = array(
	'addbase',
	'editbase',
	'addlink',
	'getthreadlist',
	'delete',
	'edit',
	'editimage',
	'getimages',
	'threads',
	'sort',
);
$action = in_array($_G['gp_action'], $allowaction) ? $_G['gp_action'] : '';
$tid = intval($_G['gp_tid']);
$daytime = dgmdate($_G['timestamp'], 'Y-m-d');

//权限判断
if(!in_array($_G['groupid'], $allowgroups) || empty($action)) {
	showmessage(pl('no_perm'));
}



include libfile('function/forum');
include DISCUZ_ROOT.'./source/plugin/threadlink/stat.func.php';

//设置为聚合贴
if($action == 'addbase') {
	if(empty($tid)) {
		showmessage(pl('no_tid'));
	} else {
		$thread = get_thread_by_tid($tid);
	}

	if(empty($thread)) {
		showmessage(pl('no_tid'));
	}
	if(!submitcheck('addbasesubmit')) {
		$is_base = DB::result_first("SELECT COUNT(*) FROM ".DB::table('threadlink_base')." WHERE tid='$tid'");
		if($is_base) {
			$base = DB::fetch_first("SELECT * FROM ".DB::table('threadlink_base')." WHERE tid='$tid' LIMIT 1");
			$action = 'editbase';
		}
		include template('threadlink:addbase');
		exit;
	} else {
		$tltpl = trim($_G['gp_tltpl']);
		if(!in_array($tltpl, array_keys($tltpls))) {
			showmessage(pl('no_template'));
		}

		$_G['gp_maxrow'] = intval($_G['gp_maxrow']);
		$maxrow = $_G['gp_maxrow'] ? $_G['gp_maxrow'] : 20;
		$summarylength = intval($summarylength);
		$summarylength = $summarylength > 0 ? $summarylength : 300;
		$picwidth = intval($_G['gp_picwidth']);
		$picwidth = $picwidth > 0 ? $picwidth : 100;
		$picheight = intval($_G['gp_picheight']);
		$picheight = $picheight > 0 ? $picheight : 100;
		if(DB::result_first("SELECT COUNT(*) FROM ".DB::table('threadlink_base')." WHERE tid='$tid'")) {
			showmessage(pl('is_base'));
		}
		//入库
		DB::query("INSERT INTO ".DB::table('threadlink_base')." (tid,pid,tltpl,maxrow,subject,dateline,summarylength,picwidth,picheight,maker) VALUES ('$tid', '{$thread[pid]}', '$tltpl', '$maxrow', '".daddslashes($thread['subject'])."','{$_G[timestamp]}','{$summarylength}','$picwidth','$picheight','{$_G[uid]}')");
		$msg = pl('addbase_success');
		update_create_base($daytime);
		$alert_info = pl('tip');
		$extrajs = <<<JS
<script type="text/javascript" reload="1">hideWindow('threadlink');showDialog('$msg', 'right', '$alert_info', 'relload()', true, null, '', '', '', 3);function relload(){window.location.reload();}</script>
JS;
		showmessage('', '', '', array('extrajs' => $extrajs));
	}

//编辑聚合帖
} elseif($action == 'editbase') {
	if(submitcheck('editbasesubmit')) {
		$tltpl = trim($_G['gp_tltpl']);
		if(!in_array($tltpl, array_keys($tltpls))) {
			showmessage(pl('no_template'));
		}

		$_G['gp_maxrow'] = intval($_G['gp_maxrow']);
		$maxrow = $_G['gp_maxrow'] ? $_G['gp_maxrow'] : 20;
		$summarylength = intval($summarylength);
		$summarylength = $summarylength > 0 ? $summarylength : 300;
		$picwidth = intval($_G['gp_picwidth']);
		$picwidth = $picwidth > 0 ? $picwidth : 100;
		$picheight = intval($_G['gp_picheight']);
		$picheight = $picheight > 0 ? $picheight : 100;
		if(!DB::result_first("SELECT COUNT(*) FROM ".DB::table('threadlink_base')." WHERE tid='$tid'")) {
			showmessage(pl('not_base'));
		}
		//入库
		DB::query("UPDATE ".DB::table('threadlink_base')." SET 
			tltpl='$tltpl',
			maxrow='$maxrow',
			dateline='$_G[timestamp]',
			summarylength='$summarylength',
			picwidth='$picwidth',
			picheight='$picheight'
			WHERE tid='$tid'");
		ajax_alert(pl('editbase_success'));
	}
//聚合到帖子内
} elseif($action == 'addlink') {
	if(!submitcheck('addlinksubmit')) {
		include_once libfile('function/post');
		$thread_count = DB::fetch_first("SELECT * FROM ".DB::table('threadlink_base'));
		$thread_count['summarylength'] = $thread_count['summarylength'] ? $thread_count['summarylength'] : 300;
		$threads = array();
		$pid = intval($_G['gp_pid']);
		if($pid) {
			$linkthread = get_post_by_pid($pid);
			if(empty($linkthread)) {
				showmessage(pl('no_pid'));
			}
			$linkthread_src = get_thread_by_tid($linkthread['tid']);
			$linkthread['message'] = messagecutstr($linkthread['message'], $thread_count['summarylength']);
			//获取图片
			if($linkthread['attachment'] == 2) {
				$attach = DB::fetch_first("SELECT * FROM ".DB::table('forum_attachment')." WHERE pid='{$linkthread[pid]}'");
				$attach_num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_attachment_'.$attach['tableid'])." WHERE pid='{$linkthread[pid]}'");
				$attach_query = DB::query("SELECT * FROM ".DB::table('forum_attachment_'.$attach['tableid'])." WHERE pid='{$linkthread[pid]}' LIMIT 4");
				while($attach = DB::fetch($attach_query)) {
					$attach['attachment'] = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'forum/'.$attach['attachment'];
					$attachs[] = $attach;
				}
				if($attach_num < 4) {
					for($i = 4 - $attach_num; $i > 0; $i--) {
						$add_attach[] = 100000000+$i;
					}
				}
			}
		} else {
			showmessage(pl('no_pid'));
		}
		if($thread_count) {
			$thread_query = DB::query("SELECT * FROM ".DB::table('threadlink_base')." ORDER BY dateline DESC LIMIT 20");
			while($thread = DB::fetch($thread_query)) {
				//可以聚合进去的帖子列表
				$threads[] = $thread;
			}

			include template('threadlink:addlink');
		} else {
			showmessage(pl('no_tid'));
		}
	} else {
		$tid = intval($_G['gp_tid_hand']);
		$tid = $tid ? $tid : intval($_G['gp_tid']);
		empty($tid) && showmessage(pl('no_tid'));
		$thread_base = DB::fetch_first("SELECT * FROM ".DB::table('threadlink_base')." WHERE tid='$tid'");///////////////////////////
		$thread_base['summarylength'] = $thread_base['summarylength'] ? $thread_base['summarylength']  : 300;
		$pid = intval($_G['gp_pid']);
		$subject = daddslashes(cutstr(trim($_G['gp_subject']), 80));
		$message = daddslashes(cutstr(trim($_G['gp_message']), $thread_base['summarylength']));
		$url = daddslashes(trim($_G['gp_url']));

		empty($pid) && showmessage(pl('no_pid'));
		empty($subject) && showmessage(pl('no_subject'));
		empty($message) && showmessage(pl('no_message'));
		$aid = empty($pic) ? $aid : 0;

		$attach_hand = (array)explode(',', trim($_G['gp_attach_hand']));
		if(!empty($attach_hand)) {
			$i = 0;
			foreach($attach_hand as $inputname) {
				$i++;
				if(empty($inputname) || empty($_G['gp_pic'][$inputname])) {
					continue;
				}
				if(substr($_G['gp_pic'][$inputname], 0, 4) == 'aid:'){
					$aid_tmp[] = intval(str_replace('aid:', '', $_G['gp_pic'][$inputname]));
				}
				if($i >= 4) {
					break;
				}
			}
		}
		$aid = implode(',', $aid_tmp);
		if(DB::result_first("SELECT COUNT(*) FROM ".DB::table('threadlink_link')." WHERE tid='{$tid}' AND pid='{$pid}'")) {
			showmessage(pl('exists_already'));
		} else {
			$post = get_post_by_pid($pid);
			$linkid = DB::insert('threadlink_link',
				array('tid' => $tid, 'pid' => $pid, 'subject' => $subject, 'message' => $message, 'url' => $url, 'pic' => $pic, 'aid' => $aid, 'displayorder' => 0, 'author' => $post['author'], 'authorid' => $post['authorid'], 'fid' => $post['fid'], 'publisher' => $_G['uid']),
				true
			);
			if($linkid) {
				update_create_link($daytime);
				ajax_alert(pl('addlink_success').'&nbsp;<em><a href="forum.php?mod=viewthread&tid='.$tid.'" target="_blank" style="color:red;font-weight:700;">'.pl('click_to_view').'</a></em>', 5);
			} else {
				showmessage(pl('unknown_error'));
			}

		}
	}
} elseif($action == 'delete') {
	$lid = intval($_G['gp_lid']);
	$link = DB::fetch_first("SELECT * FROM ".DB::table('threadlink_link')." WHERE lid='{$lid}'");

	if($link) {
		if(!submitcheck('deletesubmit')) {
			include template('threadlink:addbase');
			exit;
		} else {
			DB::query("DELETE FROM ".DB::table('threadlink_link')." WHERE lid='{$lid}'");
			ajax_alert(pl('delete_success'));
		}
	} else {
		showmessage(pl('no_pid'));
	}
} elseif($action == 'edit') {
	$lid = intval($_G['gp_lid']);
	$link = DB::fetch_first("SELECT * FROM ".DB::table('threadlink_link')." WHERE lid='{$lid}'");
	if($link) {
		if(!submitcheck('editsubmit')) {
			$thread_count = DB::fetch_first("SELECT * FROM ".DB::table('threadlink_base'));
			if($thread_count) {
				$thread_query = DB::query("SELECT * FROM ".DB::table('threadlink_base')." ORDER BY dateline DESC LIMIT 20");
				while($thread = DB::fetch($thread_query)) {
					//可以聚合进去的帖子列表
					$threads[] = $thread;
				}

			}

			include template('threadlink:addlink');
			exit;
		} else {
			$tid = !$_G['gp_change_base'] ? $link['tid'] : intval($_G['gp_tid_hand']);
			$thread_base = DB::fetch_first("SELECT * FROM ".DB::table('threadlink_base')." WHERE tid='$tid'");
			$subject = daddslashes(cutstr(trim($_G['gp_subject']), 80));
			$message = daddslashes(cutstr(trim($_G['gp_message']), 200));
			$url = daddslashes(trim($_G['gp_url']));
			$pic = daddslashes(trim($_G['gp_pic']));

			empty($tid) && showmessage(pl('no_tid'));
			empty($subject) && showmessage(pl('no_subject'));
			empty($message) && showmessage(pl('no_message'));

			if(!$thread_base) {
				showmessage(pl('no_tid'));
			} else {
				DB::query("UPDATE ".DB::table('threadlink_link')." SET
					tid='$tid',
					subject='$subject',
					message='$message',
					url='$url'
					".(!empty($pic) ? ",pic='$pic'" : '')." WHERE lid='$lid'
					");
				update_modify_link($daytime);
				ajax_alert(pl('edit_success'));
			}
		}
	} else {
		showmessage(pl('no_pid'));
	}
} elseif($action == 'getimages') {
	$message = '';
	$post = get_post_by_pid($_G['gp_pid'], 'pid,tid,attachment');
	$page = intval($_G['gp_page']);
	if($page < 2 || $page > 5) {
		$message = pl('wrong_page');
	}
	$start = ($page - 1) * 4;
	$attach = null;
	if($post['attachment'] == 2) {
		$attach = DB::fetch_first("SELECT * FROM ".DB::table('forum_attachment')." WHERE pid='{$post[pid]}'");
	} else {
		$message = pl('no_image');
	}
	if($attach) {
		$attach_num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_attachment_'.$attach['tableid'])." WHERE pid='{$post[pid]}'");
		if($attach_num >= $start + 1) {
			$attach_query = DB::query("SELECT aid,remote,attachment FROM ".DB::table('forum_attachment_'.$attach['tableid'])." WHERE pid='{$post[pid]}' LIMIT $start,4");
			while($attach = DB::fetch($attach_query)) {
				$attach['attachment'] = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'forum/'.$attach['attachment'];
				$attachs[] = $attach;
			}
		} else {
			$message = pl('no_image');
		}
	} else {
		$message = pl('no_image');
	}

	$page++;
	include template('threadlink:addlink_getimages');
	exit;
} elseif($action == 'editimage') { //编辑被聚合帖中的图片
	$lid = intval($_G['gp_lid']);
	$aid = intval($_G['gp_aid']);
	$md5 = trim($_G['gp_md5']);
	if(empty($lid)) {
		showmessage(pl('no_such_image'));
	} else {
		if(!submitcheck('editimagesubmit')) {
			$result = DB::result_first("SELECT ".($aid ? 'aid' : 'pic')." FROM ".DB::table('threadlink_link')." WHERE lid='$lid'");
			if($result) {
				include template('threadlink:link_editimage');
				exit;
			} else {
				showmessage(pl('no_such_image'));
			}
		} else {
			$picurl = daddslashes(trim($_G['gp_picurl']));
			if(empty($picurl)) {
				showmessage(pl('picurl_empty'));
			} else {
				$link = DB::fetch_first("SELECT pic,aid FROM ".DB::table('threadlink_link')." WHERE lid='{$lid}'");
				$found = 0;
				if(!$aid && $link['pic']) { //图片是地址
					$link['pics'] = (array)explode(chr(1), $link['pic']);
					foreach($link['pics'] as $key => $picurl_value) {
						if(md5($picurl_value) == $md5) {
							$link['pics'][$key] = $picurl;
							$found = 1;
							DB::query("UPDATE ".DB::table('threadlink_link')." SET pic='".implode(chr(1), daddslashes($link['pics']))."' WHERE lid='{$lid}'");
							break;
						}
					}
				} elseif($aid && $link['aid']) { //图片是帖内图片附件

					$link['aids'] = (array)explode(',', $link['aid']);
					if(in_array($aid, $link['aids'])) {
						$key = array_keys($link['aids'], $aid);
						unset($link['aids'][$key[0]]);
						$found = 1;
						DB::query("UPDATE ".DB::table('threadlink_link')." SET pic='".($link['pic'] ? (daddslashes($link['pic']).chr(1)) : '').$picurl."',aid='".implode(',', $link['aids'])."' WHERE lid='{$lid}'");
					}
				}
				if(!$found) {
					showmessage(pl('no_such_image'));
				} else {
					update_modify_link($daytime);
					ajax_alert(pl('edit_image_success'));
				}
			}
		}
	}
} elseif($action == 'threads') {
	$page = max(1, intval($_G['gp_page']));
	$start = ($page - 1)*20;
	$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('threadlink_base'));
	$start = $start % $count;
	if($count <= 20) {
		$start = 0;
	}
	$query = DB::query("SELECT * FROM ".DB::table('threadlink_base')." ORDER BY dateline DESC LIMIT $start,20");
	while($thread = DB::fetch($query)) {
		$threads[] = $thread;
	}
	$page++;
	include template('threadlink:addlink_change_thread');
	exit;
} elseif($action == 'sort') {
	if(empty($tid)) {
		showmessage(pl('no_tid'));
	} else {
		if(DB::result_first("SELECT COUNT(*) FROM ".DB::table('threadlink_base')." WHERE tid='$tid'")) {
			if(!submitcheck('sortsubmit')) {
				$thread = DB::fetch_first("SELECT * FROM ".DB::table('threadlink_base')." WHERE tid='$tid'");
				$query = DB::query("SELECT * FROM ".DB::table('threadlink_link')." WHERE tid='$tid' ORDER BY displayorder ASC LIMIT {$thread[maxrow]}");
				while($result = DB::fetch($query)) {
					$threadlinks[] = $result;
				}
				include template('threadlink:link_sort');
				exit;
			} else {
				if(empty($_G['gp_displayorder']) || !is_array($_G['gp_displayorder'])) {
					showmessage(pl('displayorder_wrong'));
				} else {
					$sql = '';
					foreach($_G['gp_displayorder'] as $lid => $order) {
						$lid = intval($lid);
						$order = max(0, intval($order));
						if($lid > 0) {
							DB::query("UPDATE ".DB::table('threadlink_link')." SET displayorder='$order' WHERE tid=$tid AND lid=$lid");
						}
					}
					ajax_alert(pl('sort_success'));
				}
			}
		} else {
			showmessage(pl('no_tid'));
		}
	}
	
}


/**
 * 插件语言包
 */
function pl($str) {
	return lang('plugin/threadlink', $str);
}
function ajax_alert($msg, $sec = 3) {
	$alert_info = pl('tip');
	$extrajs = <<<JS
<script type="text/javascript" reload="1">hideWindow('threadlink');showDialog('$msg', 'right', '$alert_info', 'relload()', true, null, '', '', '', {$sec});function relload(){window.location.reload();}</script>
JS;
	showmessage('', '', '', array('extrajs' => $extrajs));
	exit;
}
?>
