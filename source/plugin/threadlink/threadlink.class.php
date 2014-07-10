<?php
/*
 *	Author: IAN - zhouxingming
 *	Last modified: 2011-09-06 10:09
 *	Filename: threadlink.class.php
 *	Description: 
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_threadlink{

}
class plugin_threadlink_forum extends plugin_threadlink {
	var $postfooter = array();	//
	var $allowgroups = array();	//允许使用此功能的用户组
	var $member = array();
	var $addbasehtml = '';
	var $tid = 0;

	function plugin_threadlink_forum() {
		global $_G;

		$this->tid = $_G['tid'];
		$this->member = $_G['member'];
		$this->allowgroups = unserialize($_G['cache']['plugin']['threadlink']['allowgroups']);
	}

	function viewthread() {
		global $_G;
		if($_G['gp_from'] == 'threadlink') {
			include DISCUZ_ROOT.'./source/plugin/threadlink/stat.func.php';
			update_clicks(dgmdate($_G['timestamp'], 'Y-m-d'));
		}
	}
	function viewthread_title_extra_output($p) {
		if(in_array($this->member['groupid'], $this->allowgroups)) {
		  return '';
		} else {
			return '';
		}
	}
    
    function viewthread_posttop_output(){
        global $postlist;
        $a = array();

        foreach($postlist as $k=>$v){
            if($v['first'] == 1 && !IS_ROBOT){
                $sql = "select b.subject,b.tid from ".DB::table('threadlink_link')." l left join ".DB::table('threadlink_base')."  b on l.tid=b.tid where l.pid='$v[pid]' order by l.lid desc limit 1";
                $detial = DB::fetch_first($sql);
                if($detial){
                    $a[]="<a href=\"forum.php?mod=viewthread&tid=".$detial[tid]."\" target=\"_blank\" style=\"color:#ff6d00;font-weight: bold; font-size:14px\">[".pl('from_thread').": ".$detial[subject]."]</a>";
                }
                break;
            }
        }
        return $a;   
    }
    
	function viewthread_postfooter_output() {
		global $postlist;
		if(in_array($this->member['groupid'], $this->allowgroups)) {
			$this->addbasehtml = '<a href="plugin.php?id=threadlink:mod&action=addbase&tid='.$this->tid.'" style="background:url(source/plugin/threadlink/image/addbase.png) no-repeat 4px 50%;" onclick="showWindow(\'threadlink\', this.href, \'get\', 1);doane(e);">'.pl('addbase').'</a>';
			foreach($postlist as $k => $v) {
				$a[] = ($v['first'] ? $this->addbasehtml : '').'<a href="plugin.php?id=threadlink:mod&action=addlink&pid='.$v['pid'].'" style="background:url(source/plugin/threadlink/image/addlink.png) no-repeat 4px 50%;" onclick="showWindow(\'threadlink\', this.href, \'get\', 1);doane(e);">'.pl('addlink').'</a>';
			}
			$this->postfooter = $a;
		}

		return $this->postfooter;
	}


	function viewthread_postbottom_output() {
		global $postlist;
		reset($postlist);
		$post = current($postlist);
		$return = '';
		$links = array();
		if($this->tid && $post['first'] && !IS_ROBOT) {
	
			if($base = DB::fetch_first("SELECT * FROM ".DB::table('threadlink_base')." WHERE tid='{$this->tid}'")) {
				$line = 1;
				$query = DB::query("SELECT * FROM ".DB::table('threadlink_link')." WHERE tid='{$this->tid}' ORDER BY displayorder ASC LIMIT {$base[maxrow]}");
				while($link = DB::fetch($query)) {
					$pic = $link['pic'] ? (array)explode(chr(1), $link['pic']) : null;
					unset($link['pic']);
					if($pic) {
						$link['pic'] = $pic;
						foreach($link['pic'] as $key => $p) {
							$link['pic'][$key] = array();
							$link['pic'][$key]['url'] = $p;
							$link['pic'][$key]['md5'] = md5($p);
						}
					}
					if($link['aid']) {
						$link['aid'] = explode(',', $link['aid']);
						$i = $link['pic'][0] ? 4 : 0;
						foreach($link['aid'] as $aid) {
							$link['pic'][$i]['url'] = getforumimg($aid, 0, $base['picwidth'], $base['picheight']);
							$link['pic'][$i]['aid'] = $aid;
							$i++;
						}
					}
					$link['line'] = $line;
					$links[] = $link;
					$line++;
				}
				if($links) {
					$left = $base['picwidth'] - 25;
					$top = $base['picheight'] - 17;
					include template('threadlink:'.$base['tltpl']);
				}
			}
		} else {
			$return = '';
		}

		return array($return);
	}
}


function pl($str) {
	return lang('plugin/threadlink', $str);
}

?>
