<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: form_index.php 2 2010-12-21 Z Niexinyuan $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$formid = intval($_G['gp_fid']);
$formitem = DB::fetch_first('SELECT * FROM '.DB::table('xplus_form_item').' i INNER JOIN '.DB::table('xplus_form_item_field')." f ON i.formid=f.formid WHERE i.formid={$formid} LIMIT 1");
if(!$formitem || (empty($formitem['available']) && empty($_G['adminid']))) {
	showmessage("xplus:form_inexistence","plugin.php?id=xplus:admincr&mod=form&action=guide&step=3&formid=$formid");
}

$formurl = $moduleurl."&fid=$formid";
$iniframe = !empty($_G['gp_iniframe']) ? 1 : 0;
if($iniframe) {
    $width = !empty($_G['gp_width']) ? intval($_G['gp_width']) : 500;
    $height = !empty($_G['gp_height']) ? intval($_G['gp_height']) : 500;
    $formurl .= $formurl."&iniframe=1&width=$width&height=$height";
}

$lang = lang('plugin/xplus');
require_once xplus_libfile('function/form');

if($_G['gp_action'] == 'formsubmit'){
	form_checkstart($formitem);
	form_checkneedlogin($formitem);
	form_checksec($formitem);
	form_checkparticipated($formitem);
	form_field_init($formid);
	
	$_G['showmessage']['cssurl'] = 'source/plugin/xplus/template/'.$module['identifier'].'/'.$template['directory'].'/'.$module['identifier'].'.css';
    $_G['showmessage']['jspath'] = 'source/plugin/xplus/static/js/';
    $_G['showmessage']['imgpath'] = 'source/plugin/xplus/template/'.$module['identifier'].'/'.$template['directory'].'/image/';
    
    $itemfieldarray = $listshowarray = array();
	$query = DB::query("SELECT i.formid, f.fieldid, f.title, f.type, f.unit, f.rules, f.identifier, f.description, iv.required, iv.search, iv.listshow, f.expiration, f.protect
			FROM ".DB::table('xplus_form_item')." i
			LEFT JOIN ".DB::table('xplus_form_item_var')." iv ON i.formid=iv.formid
			LEFT JOIN ".DB::table('xplus_form_field')." f ON iv.fieldid=f.fieldid
			WHERE iv.available='1' and iv.formid = '$formid' 
			ORDER BY iv.displayorder");
	while($data = DB::fetch($query)) {
		$data['rules'] = unserialize($data['rules']);
		$fieldid = $data['fieldid'];
		$itemfieldarray[$fieldid] = array(
			'title' => dhtmlspecialchars($data['title']),
			'type' => dhtmlspecialchars($data['type']),
			'unit' => dhtmlspecialchars($data['unit']),
			'identifier' => dhtmlspecialchars($data['identifier']),
			'description' => dhtmlspecialchars($data['description']),
			'required' => intval($data['required']),
			'search' => intval($data['search']),
			'listshow' => intval($data['listshow']),
			'expiration' => intval($data['expiration']),
			'protect' => unserialize($data['protect']),
		);

		if(in_array($data['type'], array('select', 'checkbox', 'radio'))) {
			if($data['rules']['choices']) {
				$choices = array();
				foreach(explode("\n", $data['rules']['choices']) as $item) {
					list($index, $choice) = explode('=', $item);
					$index = trim($index);
					$choice = trim($choice);
					if($index && $choice) {
						$choices[$index] = $choice;
					}
				}
				$itemfieldarray[$fieldid]['choices'] = $choices;
			} else {
				$itemfieldarray[$fieldid]['choices'] = array();
			}
			if($data['type'] == 'select') {
				$itemfieldarray[$fieldid]['inputsize'] = $data['rules']['inputsize'] ? intval($data['rules']['inputsize']) : 282;
			}
		} elseif(in_array($data['type'], array('text', 'textarea', 'calendar'))) {
			$itemfieldarray[$fieldid]['maxlength'] = intval($data['rules']['maxlength']) < 255 ? intval($data['rules']['maxlength']) : 255;
			if($data['type'] == 'textarea') {
				$itemfieldarray[$fieldid]['rowsize'] = $data['rules']['rowsize'] ? intval($data['rules']['rowsize']) : 5;
				$itemfieldarray[$fieldid]['colsize'] = $data['rules']['colsize'] ? intval($data['rules']['colsize']) : 50;
			} else {
				$itemfieldarray[$fieldid]['inputsize'] = $data['rules']['inputsize'] ? intval($data['rules']['inputsize']) : '';
			}
			if(in_array($data['type'], array('text', 'textarea'))) {
				$itemfieldarray[$fieldid]['defaultvalue'] = $data['rules']['defaultvalue'];
			}
		} elseif($data['type'] == 'image') {
			$itemfieldarray[$fieldid]['maxwidth'] = intval($data['rules']['maxwidth']);
			$itemfieldarray[$fieldid]['maxheight'] = intval($data['rules']['maxheight']);
			$itemfieldarray[$fieldid]['minwidth'] = intval($data['rules']['minwidth']);
			$itemfieldarray[$fieldid]['minheight'] = intval($data['rules']['minheight']);
			$itemfieldarray[$fieldid]['maxfilesize'] = intval($data['rules']['maxfilesize']);
			$itemfieldarray[$fieldid]['makethumb'] = intval($data['rules']['makethumb']);
			$itemfieldarray[$fieldid]['thumbwidth'] = intval($data['rules']['thumbwidth']);
			$itemfieldarray[$fieldid]['thumbheight'] = intval($data['rules']['thumbheight']);
			$itemfieldarray[$fieldid]['inputsize'] = $data['rules']['inputsize'] ? intval($data['rules']['inputsize']) : '';
		} elseif(in_array($data['type'], array('number', 'range'))) {
			$itemfieldarray[$fieldid]['inputsize'] = $data['rules']['inputsize'] ? intval($data['rules']['inputsize']) : '';
			$itemfieldarray[$fieldid]['maxnum'] = intval($data['rules']['maxnum']);
			$itemfieldarray[$fieldid]['minnum'] = intval($data['rules']['minnum']);
			if($data['rules']['searchtxt']) {
				$itemfieldarray[$fieldid]['searchtxt'] = explode(',', $data['rules']['searchtxt']);
			}
			if($data['type'] == 'number') {
				$itemfieldarray[$fieldid]['defaultvalue'] = $data['rules']['defaultvalue'];
			}
		} elseif(in_array($data['type'], array('qq', 'mobile', 'telephone', 'custom'))) {
			$itemfieldarray[$fieldid]['inputsize'] = $data['rules']['inputsize'] ? intval($data['rules']['inputsize']) : '';
			$itemfieldarray[$fieldid]['defaultvalue'] = $data['rules']['defaultvalue'];
			if($data['type'] == 'custom') {
				$itemfieldarray[$fieldid]['regular'] = $data['rules']['regular'];
			}
		}

		if($data['listshow']) {
			$listshowarray[$fieldid] = array(
				'title' => dhtmlspecialchars($data['title']),
				'identifier' => dhtmlspecialchars($data['identifier']),
				'unit' => dhtmlspecialchars($data['unit'])
			);
		}
	}
	$_G['form_fieldlist'] = $itemfieldarray;
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

	form_field_validator($_G['gp_fieldvalue']);
	form_upload();
	form_insertvalue($formid, $formurl);

}else{
	$metakeywords = $metadescription = $title = $fielddisable = '';
	$formvalue = array();
	$title = $formitem['title'];
	$metakeywords = $formitem['seokeywords'];
	$metadescription = $formitem['seodesc'];

	form_field_init($formid);
	$form_checkstart = form_checkstart($formitem, true);
	$resultnum = form_getresultnum($formid);
	$needlogin = form_checkneedlogin($formitem, true);
	$viewresult = $_G['gp_viewresult'] || form_checkparticipated($formitem, true);
	$resultnum_lang = lang('plugin/xplus', 'form_total', array('resultnum' => $resultnum));
	$seccodecheck = !empty($formitem['seccode']) ? 1 : 0;

	if($viewresult && $_G['uid']) {
		$fielddisabled = 'disabled="disabled"';
		$formvalue = DB::fetch_first('SELECT * FROM '.DB::table("xplus_form_value_{$formid}")." WHERE uid='{$_G[uid]}' ORDER BY valueid DESC LIMIT 0,1");
	}

	$itemfieldarray = $listshowarray = array();
	$query = DB::query("SELECT i.formid, f.fieldid, f.title, f.type, f.unit, f.rules, f.identifier, f.description, iv.required, iv.search, iv.listshow, f.expiration, f.protect
			FROM ".DB::table('xplus_form_item')." i
			LEFT JOIN ".DB::table('xplus_form_item_var')." iv ON i.formid=iv.formid
			LEFT JOIN ".DB::table('xplus_form_field')." f ON iv.fieldid=f.fieldid
			WHERE iv.available='1' and iv.formid = '$formid' 
			ORDER BY iv.displayorder");
	while($data = DB::fetch($query)) {
		$data['rules'] = unserialize($data['rules']);
		$fieldid = $data['fieldid'];
		$itemfieldarray[$fieldid] = array(
			'title' => dhtmlspecialchars($data['title']),
			'type' => dhtmlspecialchars($data['type']),
			'unit' => dhtmlspecialchars($data['unit']),
			'identifier' => dhtmlspecialchars($data['identifier']),
			'description' => dhtmlspecialchars($data['description']),
			'required' => intval($data['required']),
			'search' => intval($data['search']),
			'listshow' => intval($data['listshow']),
			'expiration' => intval($data['expiration']),
			'protect' => unserialize($data['protect']),
		);

		if(in_array($data['type'], array('select', 'checkbox', 'radio'))) {
			if($data['rules']['choices']) {
				$choices = array();
				foreach(explode("\n", $data['rules']['choices']) as $item) {
					list($index, $choice) = explode('=', $item);
					$index = trim($index);
					$choice = trim($choice);
					if($index && $choice) {
						$choices[$index] = $choice;
					}
				}
				$itemfieldarray[$fieldid]['choices'] = $choices;
			} else {
				$itemfieldarray[$fieldid]['choices'] = array();
			}
			if($data['type'] == 'select') {
				$itemfieldarray[$fieldid]['inputsize'] = $data['rules']['inputsize'] ? intval($data['rules']['inputsize']) : 282;
			}
		} elseif(in_array($data['type'], array('text', 'textarea', 'calendar'))) {
			$itemfieldarray[$fieldid]['maxlength'] = intval($data['rules']['maxlength']) < 255 ? intval($data['rules']['maxlength']) : 255;
			if($data['type'] == 'textarea') {
				$itemfieldarray[$fieldid]['rowsize'] = $data['rules']['rowsize'] ? intval($data['rules']['rowsize']) : 5;
				$itemfieldarray[$fieldid]['colsize'] = $data['rules']['colsize'] ? intval($data['rules']['colsize']) : 50;
			} else {
				$itemfieldarray[$fieldid]['inputsize'] = $data['rules']['inputsize'] ? intval($data['rules']['inputsize']) : '';
			}
			if(in_array($data['type'], array('text', 'textarea'))) {
				$itemfieldarray[$fieldid]['defaultvalue'] = $data['rules']['defaultvalue'];
			}
		} elseif($data['type'] == 'image') {
			$itemfieldarray[$fieldid]['maxwidth'] = intval($data['rules']['maxwidth']);
			$itemfieldarray[$fieldid]['maxheight'] = intval($data['rules']['maxheight']);
			$itemfieldarray[$fieldid]['minwidth'] = intval($data['rules']['minwidth']);
			$itemfieldarray[$fieldid]['minheight'] = intval($data['rules']['minheight']);
			$itemfieldarray[$fieldid]['maxfilesize'] = intval($data['rules']['maxfilesize']);
			$itemfieldarray[$fieldid]['makethumb'] = intval($data['rules']['makethumb']);
			$itemfieldarray[$fieldid]['thumbwidth'] = intval($data['rules']['thumbwidth']);
			$itemfieldarray[$fieldid]['thumbheight'] = intval($data['rules']['thumbheight']);
			$itemfieldarray[$fieldid]['inputsize'] = $data['rules']['inputsize'] ? intval($data['rules']['inputsize']) : '';
		} elseif(in_array($data['type'], array('number', 'range'))) {
			$itemfieldarray[$fieldid]['inputsize'] = $data['rules']['inputsize'] ? intval($data['rules']['inputsize']) : '';
			$itemfieldarray[$fieldid]['maxnum'] = intval($data['rules']['maxnum']);
			$itemfieldarray[$fieldid]['minnum'] = intval($data['rules']['minnum']);
			if($data['rules']['searchtxt']) {
				$itemfieldarray[$fieldid]['searchtxt'] = explode(',', $data['rules']['searchtxt']);
			}
			if($data['type'] == 'number') {
				$itemfieldarray[$fieldid]['defaultvalue'] = $data['rules']['defaultvalue'];
			}
		} elseif(in_array($data['type'], array('qq', 'mobile', 'telephone', 'custom'))) {
			$itemfieldarray[$fieldid]['inputsize'] = $data['rules']['inputsize'] ? intval($data['rules']['inputsize']) : '';
			$itemfieldarray[$fieldid]['defaultvalue'] = $data['rules']['defaultvalue'];
			if($data['type'] == 'custom') {
				$itemfieldarray[$fieldid]['regular'] = $data['rules']['regular'];
			}
		}

		if($data['listshow']) {
			$listshowarray[$fieldid] = array(
				'title' => dhtmlspecialchars($data['title']),
				'identifier' => dhtmlspecialchars($data['identifier']),
				'unit' => dhtmlspecialchars($data['unit'])
			);
		}
	}
	$_G['form_fieldlist'] = $itemfieldarray;
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
    $template = DB::fetch_first("SELECT * FROM ".DB::table('xplus_common_template')." WHERE templateid='$formitem[templateid]'");
	$_G['showmessage']['cssurl'] = 'source/plugin/xplus/template/'.$module['identifier'].'/'.$template['directory'].'/'.$module['identifier'].'.css';
    $_G['showmessage']['jspath'] = 'source/plugin/xplus/static/js/';
    $_G['showmessage']['imgpath'] = 'source/plugin/xplus/template/'.$module['identifier'].'/'.$template['directory'].'/image/';
    $tplfile = $iniframe ? 'index_iframe' : 'index';
	include template('xplus:form/'.$template['directory'].'/'.$tplfile);  
}
?>