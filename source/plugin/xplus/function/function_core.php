<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_core.php 16840 2011-09-02 08:19:59Z Niexinyuan $
 */
 
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function xplus_libfile($name) {
    $name = explode('/', $name);
    if($name[1]) {
        $filename = realpath(XPLUS_ROOT."./{$name[0]}/{$name[0]}_{$name[1]}.php");        
    } elseif($name[0]) {
        $filename = realpath(XPLUS_ROOT."./{$name[0]}.php");
    } else {
        return;
    }
    if(file_exists($filename)) {
        return $filename;
    }
    return;
}

function xplus_save_syscache($cachename, $tablename, $key = '') {
    $query = DB::query("SELECT * FROM ".DB::table($tablename));
    while($temp = DB::fetch($query)) {
        if($key) {
           $data[$temp[$key]] = $temp; 
        } else {
            $data[] = $temp;
        }
    }
    save_syscache($cachename, $data);
}


function customlang($langvar = '', $vars = array(), $customlang = array(), $defaultlang = 'plugin/xplus') {
	$ret = '';
	$langvar = (string) $langvar;
	if(!empty($langvar)) {
		$ret = !empty($customlang[$langvar]) ? $customlang[$langvar] : lang($defaultlang, $langvar);
	}
	if($vars && is_array($vars)) {
		$searchs = $replaces = array();
		foreach($vars as $k => $v) {
			$searchs[] = '{'.$k.'}';
			$replaces[] = $v;
		}
		$ret = str_replace($searchs, $replaces, $ret);
	}
	return $ret;
}

function upload_images($file, $module, $makethumb=true, $thumbwidth = 176, $thumbheight = 176, $maxfilesize = 0, $silent = false, $maxwidth = 0, $maxheight = 0, $minwidth = 0, $minheight = 0) {
	global $_G;
    
	$_G['setting']['thumbquality'] = 100;
	require_once xplus_libfile('class/voteupload');
	$upload = new discuz_upload();

	if(!$upload->init($file, $module)) {
		return false;
	}
    
	$isimage = $upload->attach['isimage'] ? 1 : 0;
	if(!$isimage) {
		if($silent) {
			return -1;
		} elseif(!defined('IN_ADMINCP')) {
			showmessage('common_attachment_error_-1');
		} else {
			cpmsg('common_attachment_error_-1', '', 'error');
		}
	}

	$maxfilesize = intval($maxfilesize);
	if($maxfilesize>0 && $upload->attach['size'] > $maxfilesize * 1024) {
		if($silent) {
			return -2;
		} elseif(!defined('IN_ADMINCP')) {
			showmessage('common_attachment_error_-2', '', array('maxfilesize' => $maxfilesize));
		} else {
			cpmsg('common_attachment_error_-2', '', 'error', array('maxfilesize' => $maxfilesize));
		}
	}
    
	if(!$upload->save()) {
		if($silent) {
			return -3;
		} elseif(!defined('IN_ADMINCP')) {
			showmessage($upload->errormessage());
		} else {
			cpmsg($upload->errormessage(), '', 'error');
		}
	}
    
	$check = check_images($upload->attach, $maxwidth, $maxheight, $minwidth, $minheight);
    
	if(!$check) {
		@unlink($upload->attach['target']);
		if($silent) {
			return -4;
		} elseif(!defined('IN_ADMINCP')) {
			showmessage($upload->errormessage());
		} else {
			cpmsg($upload->errormessage(), '', 'error');
		}
	}
    
	$data = array(
		'authorid' => $_G['uid'],
		'filesize' => $upload->attach['size'],
		'type' => $isimage,
		'filename' => $upload->attach['name'],
		'url' => $upload->attach['attachment'],
        'dateline' => TIMESTAMP
	);
	$return = $upload->attach;
	DB::insert('xplus_common_attachment', $data, true);
	$return['aid'] = DB::insert_id();
    
	if(!$return['aid']) {
		if($silent) {
			return -5;
		} elseif(!defined('IN_ADMINCP')) {
			showmessage('common_attachment_error_-3');
		} else {
			cpmsg('common_attachment_error_-3', '', 'error');
		}
	}
    
	if($isimage && $makethumb) {
		require_once libfile('class/image');
		$image = new image;
		$source = $_G['setting']['attachdir'].$module.'/'.$upload->attach['attachment'];

		$setting_thumbwidth = $thumbwidth > 10 && $thumbwidth < 1000 ? intval($thumbwidth) : 176;
		$setting_thumbheight = $thumbheight > 10 && $thumbheight < 1000 ? intval($thumbheight) : 176;

		$isthumb = $image->Thumb($source, '', $setting_thumbwidth, $setting_thumbheight);
		if(!$isthumb) {
            
			$thumbtarget = $_G['setting']['attachdir'].$module.'/'.$upload->attach['attachment'].'.thumb.jpg';
			$isthumb = @copy($source, $thumbtarget);
		}
        
		if(!$isthumb) {
            
			if($silent) {
				return -6;
			} elseif(!defined('IN_ADMINCP')) {
				showmessage('common_attachment_error_-4');
			} else {
				cpmsg('common_attachment_error_-4', '', 'error');
			}
		}
        
	}

	return $return;
}

function check_images($attach, $maxwidth=0, $maxheight=0, $minwidth=0, $minheight=0) {
	$maxwidth = intval($maxwidth);
	$maxheight = intval($minwidth);
	$minwidth = intval($minwidth);
	$minheight = intval($minheight);
	if(is_array($attach) && $attach) {
		if($maxwidth > 0 && $attach['imageinfo'][0] > $maxwidth) {
			return false;
		} elseif($maxheight > 0 && $attach['imageinfo'][1] > $maxheight) {
			return false;
		} elseif($minwidth > 0 && $attach['imageinfo'][0] < $minwidth) {
			return false;
		} elseif($minheight > 0 && $attach['imageinfo'][1] < $minheight) {
			return false;
		}
		return true;
	}
	return false;
}

function delete_images($aid) {
	global $_G;
	$condition = '';
	if(is_array($aid)) {
		$where = array();
		foreach($aid as $id) {
			$id = intval($id);
			if($id > 0) {
				$where[] = $id;
			}
		}
		if($where) {
			$condition = 'aid IN ('.implode(',', $where).')';
		}
	} else {
		$aid = intval($aid);
		if($aid > 0) {
			$condition = "aid='$aid'";
		}
	}
	if($condition) {
		$query = DB::query('SELECT type, url FROM '.DB::table('xplus_common_attachment').' WHERE '.$condition);
		while($attach = DB::fetch($query)) {
			if(!empty($attach['url'])) {
				$mtype = '';
				$source = $_G['setting']['attachdir'].($mtype ? $mtype.'/' : '').$attach['url'];
				@unlink($source);
				if(!empty($attach['type'])) {
					$thumb = $source.'.thumb.jpg';
					@unlink($thumb);
				}
			}
		}
		DB::delete('xplus_common_attachment', $condition);
	}
}

function get_moduleinfo($identifier, $isavailable = true) {
    $condition = " AND identifier='$identifier'". ($isavailable ? " AND available='1'" : '');
    $query = DB::query("SELECT * FROM ".DB::table('xplus_common_module')." WHERE 1 $condition");
    if($res = DB::fetch($query)) {
        return $res;
    } else {
        return false;
    }
}

function checkadmincr($cached = true) {
    global $_G;
    
    loadcache('xplus_common_config');
    if($cached && $_G['cache']['xplus_common_config']) {
        $usergroup = unserialize($_G['cache']['xplus_common_config']['common_usergroup']);
        $adminid = unserialize($_G['cache']['xplus_common_config']['common_adminid']);
    } 
    if(!$usergroup && !$adminid) {
        $query = DB::query("SELECT * FROM ".DB::table('xplus_common_config')." WHERE ckey IN ('common_usergroup', 'common_adminid')");
        while($config = DB::fetch($query)) {
            $configs[$config['ckey']] = $config['cvalue'];
        }
        $usergroup = unserialize($configs['common_usergroup']);
        $adminid = unserialize($configs['common_adminid']);
    }
    
    return in_array($_G['groupid'], $usergroup) || in_array($_G['uid'], $adminid);
}

function syntablestruct($sql, $version, $dbcharset) {
	if(strpos(trim(substr($sql, 0, 18)), 'CREATE TABLE') === FALSE) {
		return $sql;
	}
	$sqlversion = strpos($sql, 'ENGINE=') === FALSE ? FALSE : TRUE;
	if($sqlversion === $version) {
		return $sqlversion && $dbcharset ? preg_replace(array('/ character set \w+/i', '/ collate \w+/i', "/DEFAULT CHARSET=\w+/is"), array('', '', "DEFAULT CHARSET=$dbcharset"), $sql) : $sql;
	}
	if($version) {
		return preg_replace(array('/TYPE=HEAP/i', '/TYPE=(\w+)/is'), array("ENGINE=MEMORY DEFAULT CHARSET=$dbcharset", "ENGINE=\\1 DEFAULT CHARSET=$dbcharset"), $sql);
	} else {
		return preg_replace(array('/character set \w+/i', '/collate \w+/i', '/ENGINE=MEMORY/i', '/\s*DEFAULT CHARSET=\w+/is', '/\s*COLLATE=\w+/is', '/ENGINE=(\w+)(.*)/is'), array('', '', 'ENGINE=HEAP', '', '', 'TYPE=\\1\\2'), $sql);
	}
}

function xplus_showtablerow($trstyle = '', $tdstyle = array(), $tdtext = array(), $return = FALSE) {
	$rowswapclass = '';
	if(!preg_match('/class\s*=\s*[\'"]([^\'"<>]+)[\'"]/i', $trstyle, $matches)) {
		$rowswapclass = is_array($tdtext) && count($tdtext) > 2 ? ' class="hover"' : '';
	} else {
		if(is_array($tdtext) && count($tdtext) > 2) {
			$rowswapclass = " class=\"{$matches[1]} hover\"";
			$trstyle = preg_replace('/class\s*=\s*[\'"]([^\'"<>]+)[\'"]/i', '', $trstyle);
		}
	}
	$cells = "\n".'<tr'.($trstyle ? ' '.$trstyle : '').$rowswapclass.'>';
	if(isset($tdtext)) {
		if(is_array($tdtext)) {
			foreach($tdtext as $key => $td) {
				//note if($td !== NULL) {
					$cells .= '<td'.(is_array($tdstyle) && !empty($tdstyle[$key]) ? ' '.$tdstyle[$key] : '').'>'.$td.'</td>';
				//note }
			}
		} else {
			$cells .= '<td'.(!empty($tdstyle) && is_string($tdstyle) ? ' '.$tdstyle : '').'>'.$tdtext.'</td>';
		}
	}
	$cells .= '</tr>';
	if($return) {
		return $cells;
	}
	echo $cells;
}

?>