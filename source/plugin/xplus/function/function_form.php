<?php

/**
 *      [Discuz! XPlus] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_form.php 881 2011-09-29 07:24:40Z xujiakun $
 */


function formfieldsearch($itemfieldarray) {
	global $_G;
	if(is_array($itemfieldarray)) foreach($itemfieldarray as $fieldid => $field) {
		$fieldshow = '';
		if($field['search']) {
			if(in_array($field['type'], array('radio', 'checkbox', 'select'))){
				if($field['type'] == 'select') {
					$fieldshow .= '<select name="searchfield['.$fieldid.'][value]"><option value="0">'.$lang['unlimited'].'</option>';
					foreach($field['choices'] as $id => $value) {
						$fieldshow .= '<option value="'.$id.'" '.($_G['gp_searchfield'][$fieldid]['value'] == $id ? 'selected="selected"' : '').'>'.$value.'</option>';
					}
					$fieldshow .= '</select><input type="hidden" name="searchfield['.$fieldid.'][type]" value="select">';
				} elseif($field['type'] == 'radio') {
					$fieldshow .= '<input type="radio" class="radio" name="searchfield['.$fieldid.'][value]" value="0" checked="checked"]>'.$lang['unlimited'].'&nbsp;';
					foreach($field['choices'] as $id => $value) {
						$fieldshow .= '<input type="radio" class="radio" name="searchfield['.$fieldid.'][value]" value="'.$id.'" '.($_G['gp_searchfield'][$fieldid]['value'] == $id ? 'checked="checked"' : '').'> '.$value.' &nbsp;';
					}
					$fieldshow .= '<input type="hidden" name="searchfield['.$fieldid.'][type]" value="radio">';
				} elseif($field['type'] == 'checkbox') {
					foreach($field['choices'] as $id => $value) {
						$fieldshow .= '<input type="checkbox" class="checkbox" name="searchfield['.$fieldid.'][value]['.$id.']" value="'.$id.'" '.($_G['gp_searchfield'][$fieldid]['value'] == $id ? 'checked="checked"' : '').'> '.$value.'';
					}
					$fieldshow .= '<input type="hidden" name="searchfield['.$fieldid.'][type]" value="checkbox">';
				}
			} elseif(in_array($field['type'], array('number', 'text', 'email', 'calendar', 'image', 'url', 'textarea', 'upload', 'range', 'mobile', 'telephone'))) {
				if ($field['type'] == 'calendar') {
					$fieldshow .= '<script type="text/javascript" src="static/js/calendar.js"></script><input type="text" name="searchfield['.$fieldid.'][value]" class="txt" value="'.$_G['gp_searchfield'][$fieldid]['value'].'" onclick="showcalendar(event, this, false)" />';
				} elseif($field['type'] == 'number') {
					$fieldshow .= '<select name="searchfield['.$fieldid.'][condition]">
						<option value="0" '.($_G['gp_searchfield'][$fieldid]['condition'] == 0 ? 'selected="selected"' : '').'>'.cplang('equal_to').'</option>
						<option value="1" '.($_G['gp_searchfield'][$fieldid]['condition'] == 1 ? 'selected="selected"' : '').'>'.cplang('more_than').'</option>
						<option value="2" '.($_G['gp_searchfield'][$fieldid]['condition'] == 2 ? 'selected="selected"' : '').'>'.cplang('lower_than').'</option>
					</select>&nbsp;&nbsp;
					<input type="text" class="txt" name="searchfield['.$fieldid.'][value]" value="'.$_G['gp_searchfield'][$fieldid]['value'].'" />
					<input type="hidden" name="searchfield['.$fieldid.'][type]" value="number">';
				} elseif($field['type'] == 'range') {
					$fieldshow .= '<input type="text" name="searchfield['.$fieldid.'][value][min]" size="16" value="'.$_G['gp_searchfield'][$fieldid]['value']['min'].'" /> -
					<input type="text" name="searchfield['.$fieldid.'][value][max]" size="16" value="'.$_G['gp_searchfield'][$fieldid]['value']['max'].'" />
					<input type="hidden" name="searchfield['.$fieldid.'][type]" value="range">';
				} else {
					$fieldshow .= '<input type="text" name="searchfield['.$fieldid.'][value]" class="txt" value="'.$_G['gp_searchfield'][$fieldid]['value'].'" />';
				}
			}
			$fieldshow .=  '&nbsp;'.$field['unit'];
			$html .= showsetting($field['title'], '', '', $fieldshow);
		}
	}
	return  $html;
}

function form_field_init($formid = 0) {
	global $_G;

	$formid = intval($formid);
	loadcache(array("form_itemfield_{$formid}"));
	$_G['form_fieldlist'] = $_G['cache']["form_itemfield_{$formid}"];
	$_G['form_checkfield'] = array();
	$_G['form_fieldimage'] = array();
	if(is_array($_G['form_fieldlist'])) {
		foreach($_G['form_fieldlist'] as $fieldid => $field) {
			$_G['form_checkfield'][$field['identifier']]['fieldid'] = $fieldid;
			$_G['form_checkfield'][$field['identifier']]['type'] = $field['type'];
			$_G['form_checkfield'][$field['identifier']]['required'] = $field['required'] ? 1 : 0;
			$_G['form_checkfield'][$field['identifier']]['maxnum'] = $field['maxnum'] ? intval($field['maxnum']) : '';
			$_G['form_checkfield'][$field['identifier']]['minnum'] = $field['minnum'] ? intval($field['minnum']) : '';
			$_G['form_checkfield'][$field['identifier']]['maxlength'] = $field['maxlength'] ? intval($field['maxlength']) : '';
			$_G['form_checkfield'][$field['identifier']]['regular'] = $field['regular'] ? stripslashes($field['regular']) : '';
			if($field['type'] == 'image') {
				$_G['form_fieldimage'][$field['identifier']] = $_G['form_fieldlist'][$fieldid];
			}
		}
	}
}

function form_field_validator($fieldvalue) {
	global $_G;
	$_G['form_fieldvalue'] = array();
	foreach($_G['form_checkfield'] as $identifier => $check) {
		$value = $fieldvalue[$identifier];
		$fieldtitle = array('fieldtitle'=>$_G['form_fieldlist'][$check['fieldid']]['title']);
		if($check['required']) {
			if($check['type'] == 'image' && empty($_FILES["fieldvalue_{$identifier}"]['size'])) {
				showmessage('xplus:formfield_value_required', '', $fieldtitle);
			} elseif($check['type'] != 'image' && empty($value)) {
				showmessage('xplus:formfield_value_required', '', $fieldtitle);
			}
		}
		if(!empty($value)) {
			if(in_array($check['type'], array('number', 'range', 'mobile')) && !is_numeric($value)) {
				showmessage('xplus:formfield_value_num_invalid', '', $fieldtitle);
			} elseif($check['type'] == 'qq' && !preg_match("/^\d{5,12}$/i", $value)) {
				showmessage('xplus:formfield_value_qq_invalid', '', $fieldtitle);
			} elseif($check['type'] == 'mobile' && !preg_match("/^((13|15|18)\d{9})$/i", $value)) {
				showmessage('xplus:formfield_value_mobile_invalid', '', $fieldtitle);
			} elseif($check['type'] == 'telephone' && !preg_match("/^(((0\d{2,3}-)?\d{7,8}(-\d{1,4})?))$/i", $value)) {
				showmessage('xplus:formfield_value_telephone_invalid', '', $fieldtitle);
			} elseif($check['type'] == 'custom' && !empty($check['regular']) && !preg_match($check['regular'], stripslashes($value))) {
				showmessage('xplus:formfield_value_custom_invalid', '', $fieldtitle);
			} elseif($check['type'] == 'email' && !isemail($value)) {
				showmessage('xplus:formfield_value_email_invalid', '', $fieldtitle);
			} elseif($check['maxlength'] && strlen($value) > $check['maxlength']) {
				showmessage('xplus:formfield_value_toolong_invalid', '', $fieldtitle);
			} elseif($check['maxnum'] && $value > $check['maxnum']) {
				showmessage('xplus:formfield_value_maxnum_invalid', '', $fieldtitle);
			} elseif($check['minnum'] && $value < $check['minnum']) {
				showmessage('xplus:formfield_value_minnum_invalid', '', $fieldtitle);
			}
			//选择有效值确认
			if($_G['form_fieldlist'][$check['fieldid']]['choices']) {
				if(is_array($value)) {
					foreach($value as $val) {
						if(!array_key_exists($val, $_G['form_fieldlist'][$check['fieldid']]['choices'])) {
							showmessage('xplus:formfield_value_choices_invalid', '', $fieldtitle);
						}
					}
				} else {
					if(!array_key_exists($value, $_G['form_fieldlist'][$check['fieldid']]['choices'])) {
						showmessage('xplus:formfield_value_choices_invalid', '', $fieldtitle);
					}
				}
			}
			//日历有效值
			if($check['type'] == 'calendar') {
				if(!(preg_match("/[12]\d{3}-\d{1,2}-\d{1,2}/i", $value) && strtotime($value))) {
					showmessage('xplus:formfield_value_calendar_invalid', '', $fieldtitle);
				}
			} elseif($check['type'] == 'checkbox') {
				$value = implode("\t", $value);
			} elseif($check['type'] == 'url') {
				$value = substr(strtolower($value), 0, 4) == 'www.' ? 'http://'.$value : $value;
			}
		}

		$value = $check['type'] != 'image' ? dhtmlspecialchars(censor(trim($value))) : addslashes(serialize($value));
		$_G['form_fieldvalue'][$check['fieldid']] = $value;
	}
	return $_G['form_fieldvalue'];
}

function form_upload() {
	global $_G;
	$upload_aids = array();
	foreach($_G['form_fieldimage'] as $identifier=>$field) {
		$attach = upload_images($_FILES["fieldvalue_$identifier"], 'common', $field['makethumb'], $field['thumbwidth'], $field['thumbheight'], $field['maxfilesize'], true, $field['maxwidth'], $field['maxheight'], $field['minwidth'], $field['minheight']);
		if($field['required'] && empty($attach['aid'])) {
			delete_images($upload_aids);
			$upload_aids = array();
			showmessage("common_attachment_error_{$attach}", '', array('maxfilesize' => $field['maxfilesize']));
		}
		$fieldid = $_G['form_checkfield'][$identifier]['fieldid'];
		if(!empty($attach['aid'])) {
			$upload_aids[] = $attach['aid'];
			$_G['form_fieldvalue'][$fieldid] = $attach;
		} else {
			$_G['form_fieldvalue'][$fieldid] = '';
		}
	}
}

function form_insertvalue($formid, $formurl) {
	global $_G;
	if($_G['form_fieldlist'] && !empty($_G['form_fieldvalue']) && is_array($_G['form_fieldvalue'])) {
		$filedname = $data_img = $comma = '';
		$data = array(
			'uid' => $_G['uid'],
			'username' => $_G['username'],
			'ip' => $_G['clientip'],
			'dateline' => TIMESTAMP
		);

		foreach($_G['form_fieldvalue'] as $fieldid => $value) {
			$fieldname = $_G['form_fieldlist'][$fieldid]['identifier'];
			if($_G['form_fieldlist'][$fieldid]['type'] == 'image') {
				if(is_array($value) && !empty($value['aid']) && !empty($value['attachment'])) {
					$data[$fieldname] = $value['attachment'];
					$data_img .= "$comma('{$value[aid]}', '$formid', '$fieldid', '".'{valueid}'."')";
					$comma = ', ';
				} else {
					$data[$fieldname] = $value;
				}
			} else {
				$data[$fieldname] = $value;
			}
		}

		$valueid = DB::insert("xplus_form_value_{$formid}", $data, 1);
		if($valueid) {
			DB::query('UPDATE '.DB::table('xplus_form_item_count')." SET count = count + 1 WHERE formid='{$formid}'");
			if($data_img) {
				$data_img = 'INSERT INTO '.DB::table('xplus_form_attachment').' (`aid`, `formid`, `fieldid`, `valueid`) VALUES '.str_replace('{valueid}', $valueid, $data_img);
				DB::query($data_img);
			}
			form_setparticipated($formid);
			showmessage('xplus:form_post_succeed', $formurl);
		}
	}
}

function form_checkstart($formitem, $returnjs = false) {
	if($formitem['starttime'] > TIMESTAMP) {
		if($returnjs) {
			return "showPrompt(null, null, '".lang('form/message', 'form_unstart')."', 1500); return false; ";
		} else {
			showmessage('xplus:form_unstart');
		}
	}
	if(!empty($formitem['endtime']) && $formitem['endtime'] < TIMESTAMP) {
		if($returnjs) {
			return "showPrompt(null, null, '".lang('form/message', 'form_expired')."', 1500); return false; ";
		} else {
			showmessage('xplus:form_expired');
		}
	}
	return true;
}

function form_checkneedlogin($formitem, $returnmsg = false) {

	global $_G;
	if($returnmsg){
		if($formitem['allowguest'] || $_G['uid']){
			return false;
		}else {
			return true;
		}
	}else{
		if($formitem['allowguest'] || $_G['uid']){
			return true;
		}else {
			showmessage('xplus:form_needlogin');
		}	
	}
}

function form_checksec($formitem) {
	global $_G;
	if(empty($formitem['seccode'])) {
		return true;
	} else {
		if(!check_seccode($_G['gp_seccodeverify'], $_G['gp_sechash'])) {
			showmessage('xplus:form_seccode_invalid');
		}
	}
}

function form_checkparticipated($formitem, $returnmsg = false) {
	global $_G;
	$formid = intval($formitem['formid']);
	$cookiekey = form_getparticipated_cookiekey($formid);

	if(empty($formitem['valuenum'])) {
		return false;
	}

	$participated_cookie = !empty($_G['cookie'][$cookiekey]) ? intval($_G['cookie'][$cookiekey]) : 0;
	if($participated_cookie >= $formitem['valuenum']) {
		if($returnmsg) {
			return true;
		} else {
			showmessage('xplus:form_participated');
		}
	}
	if($_G['uid']) {
		$query = DB::query("SELECT valueid FROM ".DB::table("xplus_form_value_{$formid}")." WHERE uid='{$_G['uid']}'");
		$participated_db = DB::num_rows($query);
		if($participated_db >= $formitem['valuenum']) {
			if($returnmsg) {
				return true;
			} else {
				showmessage('xplus:form_participated');
			}
		}
	}

	return false;

}

function form_setparticipated($formid) {
	global $_G;
	$formid = intval($formid);
	$cookiekey = form_getparticipated_cookiekey($formid);
	$participated_cookie = !empty($_G['cookie'][$cookiekey]) ? intval($_G['cookie'][$cookiekey]) + 1 : 1;
	dsetcookie($cookiekey, $participated_cookie, 1800);
}

function form_getparticipated_cookiekey($formid) {
	global $_G;
	return "form_participated_{$formid}_uid_{$_G['uid']}";
}

function form_getresultnum($formid) {
	$count = DB::result_first("SELECT count FROM ".DB::table('xplus_form_item_count')." WHERE formid='$formid'");
	return $count;
}

function form_getresultlist($formid) {
	global $_G;
	$resultlist = array();
	$query = DB::query("SELECT valueid, uid, username, dateline FROM ".DB::table("xplus_form_value_{$formid}")." ORDER BY valueid DESC LIMIT 0, 100");
	while($data = DB::fetch($query)) {
		$data['utime'] = dgmdate($data['dateline'], 'u');
		$resultlist[] = $data;
	}
	return $resultlist;
}


function gettablecolumntype($fieldtype) {
	$return = array();
	if($fieldtype == 'number') {
		$return['type'] = 'int(10)';
		$return['default'] = 'DEFAULT \'0\'';
	} elseif(in_array($fieldtype, array('mobile', 'qq'))) {
		$return['type'] = 'bigint(19)';
		$return['default'] = 'DEFAULT \'0\'';
	} elseif(in_array($fieldtype, array('radio', 'select'))) {
		$return['type'] = 'smallint(6) UNSIGNED';
		$return['default'] = 'DEFAULT \'0\'';
	} elseif($fieldtype == 'textarea') {
		$return['type'] = 'mediumtext';
		$return['default'] = '';
	} else {
		$return['type'] = 'char(255)';
		$return['default'] = 'DEFAULT \'\'';
	}
	return $return;
}




?>