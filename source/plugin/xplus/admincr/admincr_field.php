<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: mod_vote.php 16840 2011-09-07 08:19:59Z Niexinyuan $
 */
 
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require xplus_libfile('function/form');

$action = in_array($_G['gp_action'], array('formdetail')) ? $_G['gp_action'] : 'formdetail';

$lang = lang('plugin/xplus');

switch($action) {	
    case 'formdetail' :
		$do = !empty($_G['gp_do']) ? $_G['gp_do'] : 'ajaxclasslist';
		$formid = !empty($_G['gp_formid']) ? $_G['gp_formid'] : 1;
		$inguide = !empty($_G['gp_inguide']) ? $_G['gp_inguide'] : 0;
		switch($do){
			case 'ajaxclasslist' :
				$classlist = '';
				$classid = !empty($_G['gp_classid']) ? intval($_G['gp_classid']) : 0;
				$query = DB::query("SELECT classid, title FROM ".DB::table('xplus_form_field_class')." ORDER BY displayorder");
				while($class = DB::fetch($query)) {
					$classlist .= "<a href=\"plugin.php?id=xplus:admincr&mod=form&action=guide&step=3&formid={$formid}&inguide={$inguide}##\" onclick=\"ajaxget('plugin.php?id=xplus:admincr&mod=field&action=formdetail&do=ajaxfieldlist&classid={$class['classid']}&formid={$formid}', 'fieldlist', 'fieldlist', 'Loading...', '', checkedbox)\">$class[title]</a>";
				}
                include template('common/header_ajax');
				echo $classlist;
                include template('common/footer_ajax');
				break;
			case 'ajaxfieldlist' :
				$fieldlist = '';
				$classid = !empty($_G['gp_classid']) ? intval($_G['gp_classid']) : 0;
				$query = DB::query("SELECT * FROM ".DB::table('xplus_form_field')." WHERE classid='$classid' ORDER BY displayorder");
				while($field = DB::fetch($query)) {
					$fieldlist .= "<input class=\"pc\" type=\"checkbox\" name=\"typeselect[]\" id=\"fieldselect_{$field['fieldid']}\" value=\"{$field['fieldid']}\" onclick=\"insertfield(this.value);\" /><label for=\"fieldselect_{$field['fieldid']}\">".dhtmlspecialchars($field['title'])."</label>";
				}
				include template('common/header_ajax');
                echo $fieldlist;
                include template('common/footer_ajax');
				break;
			case 'ajaxfield' :
				$fieldid = intval($_G['gp_fieldid']);
				$field = DB::fetch_first("SELECT * FROM ".DB::table('xplus_form_field')." WHERE fieldid='$fieldid' LIMIT 1");
				$field['type'] = $lang['formfield_type_'. $field['type']];
				$field['available'] = 1;
                include template('common/header_ajax');
				echo xplus_showtablerow('', array('', ''), array(
					"<input class=\"pc\" type=\"checkbox\" name=\"delete[]\" value=\"{$field['fieldid']}\" />",
					"<input type=\"text\" class=\"px\" size=\"2\" name=\"displayorder[{$field['fieldid']}]\" value=\"{$field['displayorder']}\" />",
					"<input class=\"pc\" type=\"checkbox\" name=\"available[{$field['fieldid']}]\" value=\"1\" ".($field['available'] ? 'checked' : '')." />",
					dhtmlspecialchars($field['title']),
					$field['type'],
					"<input class=\"pc\" type=\"checkbox\" name=\"required[{$field['fieldid']}]\" value=\"1\" ".($field['required'] ? 'checked' : '')." />",
					"<input class=\"pc\" type=\"checkbox\" name=\"search[{$field['fieldid']}]\" value=\"1\" ".($field['search'] ? 'checked' : '')." />",
					"<input class=\"pc\" type=\"checkbox\" name=\"listshow[{$field['fieldid']}]\" value=\"1\" ".($field['subjectshow'] ? 'checked' : '')." />",
					"<a href=\"plugin.php?id=xplus:admincr&mod=form&action=fielddetail&fieldid={$field['fieldid']}\">$lang[form_edit]</a>"
				));
                include template('common/footer_ajax');
                exit;
				break;
			default :
				break;
		}
    	case 'rlist' :
			$do = !empty($_G['gp_do']) ? $_G['gp_do'] : 'exportcsv';
			$formid = !empty($_G['gp_formid']) ? $_G['gp_formid'] : 1;
			switch($do){
				case 'exportcsv' :
				@set_time_limit(0);
				$query = DB::query("SELECT i.formid, f.fieldid, f.title, f.type, f.unit, f.rules, f.identifier, f.description, iv.required, iv.search, iv.listshow, f.expiration, f.protect
					FROM ".DB::table('xplus_form_item')." i
					LEFT JOIN ".DB::table('xplus_form_item_var')." iv ON i.formid=iv.formid
					LEFT JOIN ".DB::table('xplus_form_field')." f ON iv.fieldid=f.fieldid
					WHERE iv.formid = '$formid'
					ORDER BY iv.displayorder");
				while($data = DB::fetch($query)) {
					$data['rules'] = unserialize($data['rules']);
					$formid = $data['formid'];
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
					if($data['listshow']) {
						$listshowarray[$fieldid] = array(
							'title' => dhtmlspecialchars($data['title']),
							'identifier' => dhtmlspecialchars($data['identifier']),
							'unit' => dhtmlspecialchars($data['unit'])
						);
					}
				}
				$separator = $echo_detail = '';
				$sqlfield = 'valueid, uid, username, ip, dateline';
				$export_title = array("$lang[csv_valueid](valueid)", "$lang[csv_uid](uid)", "$lang[csv_username](username)", "$lang[csv_ip](ip)", "$lang[csv_dateline](dateline)");
				foreach($itemfieldarray as $fieldid=>$field) {
					$export_title[] = "$field[title]($field[identifier])";
					$sqlfield .= ", `{$field['identifier']}`";
				}
				$echo_detail = implode(",", $export_title)."\r\n";
				$query = DB::query("SELECT {$sqlfield} FROM ".DB::table("xplus_form_value_{$formid}"));  
                         
				while($row = DB::fetch($query)) {
					$row['dateline'] = dgmdate($row['dateline']);
					$row = str_replace(',', $lang['csv_comma'], $row);
					foreach($itemfieldarray as $fieldid=>$field) {
						$identifier = $field['identifier'];
						if(in_array($field['type'], array('radio', 'select'))) {
							$row[$identifier] = $field['choices'][$row[$identifier]];
						} elseif($field['type'] == 'checkbox') {
							$value_arr = explode("\t", $row[$identifier]);
							foreach($value_arr as $key=>$val) {
								$value_arr[$key] = $field['choices'][$val];
							}
							$row[$identifier] = implode("\t|\t", $value_arr);
						}
					}
					$echo_detail .= implode(",", $row)."\r\n";
				}

				$filename = 'formdata_'.$formid.'_'.date('Ymd', TIMESTAMP).'.csv';
				ob_end_clean();
				header('Content-Encoding: GBK');
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename='.$filename);
				header('Pragma: no-cache');
				header('Expires: 0');
				echo diconv($echo_detail, CHARSET, 'GBK');
				break;
			default :
				break;
			}
		break;
    default :
        break;        
}

?>