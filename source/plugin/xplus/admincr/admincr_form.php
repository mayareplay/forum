<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincr_form.php 2 2011-12-21 Z Niexinyuan $
 */
 
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require xplus_libfile('function/form');

$action = in_array($_G['gp_action'], array('list', 'guide', 'choiceed', 'rlist', 'fieldclass', 'fieldlist', 'fielddetail')) ? $_G['gp_action'] : 'list';
$isallowadmin = 1;

$lang = lang('plugin/xplus');

switch($action) {
    case 'list' :
        if(submitcheck('listsubmit')) {
			if(is_array($_G['gp_delete'])) {
					if($deleteids = dimplode($_G['gp_delete'])) {
						form_delete_images("formid IN ($deleteids)");
						DB::query("DELETE FROM ".DB::table('xplus_form_item')." WHERE formid IN ($deleteids)");
						DB::query("DELETE FROM ".DB::table('xplus_form_item_count')." WHERE formid IN ($deleteids)");
						DB::query("DELETE FROM ".DB::table('xplus_form_item_field')." WHERE formid IN ($deleteids)");
						DB::query("DELETE FROM ".DB::table('xplus_form_item_var')." WHERE formid IN ($deleteids)");
					}
					foreach($_G['gp_delete'] as $formid) {
						$formid = intval($formid);
						DB::query("DROP TABLE IF EXISTS ".DB::table("xplus_form_value_{$formid}"));
					}                
            } 
			if(is_array($_G['gp_titlenew']) && $_G['gp_titlenew']) {
				foreach($_G['gp_titlenew'] as $formid => $titlenew) {
					$titlenew = trim($titlenew);
						DB::update('xplus_form_item', array(
							'title' => trim($_G['gp_titlenew'][$formid]),
						), "formid='$formid'");
				}
			}
			showmessage('xplus:formlist_succeed', 'plugin.php?id=xplus:admincr&mod=form&action=list');
        }
        
        $condition = '';
        if(submitcheck('searchsubmit') && $key = $_G['gp_searchkey']) {
            switch($_G['gp_searchtype']) {
                case 'id' : $condition .= " AND `formid`='$key'"; break;
                case 'title' : $condition .= " AND `title` LIKE '%$key%'"; break;
                case 'username' : $condition .= " AND `username`='$key'"; break;
            }
        } 
		$type = $_G['gp_type'] == 'admin';
        $condition .= $type ? '' : " AND `username`='$_G[username]'";
        
        $limit = $perpage = 10;
        $start = ($curpage = is_numeric($_G['gp_page']) && $_G['gp_page'] > 0 ? $_G['gp_page'] : 1) - 1; 
		$start = $start*$limit;
        $tatolcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xplus_form_item')." WHERE 1 $condition");
		if($isadmin){
			$multipage = multi($tatolcount, $perpage, $curpage, 'plugin.php?id=xplus:admincr&mod=form&action=list&type=admin');
        }else{
			$multipage = multi($tatolcount, $perpage, $curpage, 'plugin.php?id=xplus:admincr&mod=form&action=list');
		}
        $query = DB::query("SELECT formid, title, username, dateline, endtime, available, starttime FROM ".DB::table('xplus_form_item')
            ." WHERE dateline>0 $condition ORDER BY dateline DESC LIMIT $start, $limit"
        );

        while($vote = DB::fetch($query)) {
            $vote['title'] = cutstr($vote['title'], 30);    
            $vote['dateline'] = dgmdate($vote['dateline'], 'd');
            $vote['status'] = $vote['available'] ? $lang['admincr_enable'] : $lang['admincr_disable'];
            $vote['starttime'] = $vote['starttime'] ? dgmdate($vote['starttime'], 'd') : $lang['admincr_timelimit'];
            $vote['endtime'] = $vote['endtime'] ? dgmdate($vote['endtime'], 'd') : $lang['admincr_timelimit'];
            $votes[] = $vote;
        }

		$classs = array();
		$query = DB::query("SELECT classid, title FROM ".DB::table('xplus_form_field_class')." WHERE displayorder>=0 ORDER BY displayorder");
		while($class = DB::fetch($query)) {
			$classs[] = $class;
		}

        break;
	
	 case 'fieldclass' :
		  if(submitcheck('fieldclasssubmit')) {
            if($ids = dimplode($_G['gp_delete'])) {
				if(array_search('1', $_G['gp_delete']) !== false) {
					showmessage('xplus:fieldclass_have_default', '', 'error');
				}
				$query = DB::query("SELECT fieldid FROM ".DB::table('xplus_form_field')." WHERE classid IN ($ids)");
				if(DB::num_rows($query)) {
					showmessage('xplus:fieldclass_have_field', '', 'error');;
				} else {
					DB::query("DELETE FROM ".DB::table('xplus_form_field_class')." WHERE classid IN ($ids)");
				}
			}

			if(is_array($_G['gp_newtitle'])) {
				foreach($_G['gp_newtitle'] as $key => $value) {
					$newtitle = dhtmlspecialchars(trim($value));
					if($newtitle) {
						$data = array(
							'displayorder' => intval($_G['gp_newdisplayorder'][$key]),
							'title' => $newtitle,
						);
						DB::insert('xplus_form_field_class', $data);
					}
				}
			}

			if(is_array($_G['gp_title'])) {
				foreach($_G['gp_title'] as $id => $val) {
					$title = trim($_G['gp_title'][$id]);
					$displayorder = intval($_G['gp_displayorder'][$id]);
					if($title && ($title != $fieldclass[$id]['title'] || $displayorder != $fieldclass[$id]['displayorder'])) {
						DB::update('xplus_form_field_class', array(
							'displayorder' => $displayorder,
							'title' => $title,
						), "classid='$id'");
					}
				}
			}

			if($inguide || $formid) {
				$jumpurl = "plugin.php?id=xplus:admincr&mod=form&action=fieldclass";
			} else {
				$jumpurl = "plugin.php?id=xplus:admincr&mod=form&action=fieldclass";
			}
			showmessage('xplus:fieldclass_succeed', $jumpurl, 'succeed');
		}else{
			$classs = array();
			$query = DB::query("SELECT classid, displayorder, title FROM ".DB::table('xplus_form_field_class')." WHERE displayorder>=0 ORDER BY displayorder");
			while($class = DB::fetch($query)) {
				$classs[] = $class;
			}
		}
        break;
    
	case 'fieldlist' :
		$classid = intval($_G['gp_classid']);
		if(!$classid) {
			$classid = 1;
		}

        if(submitcheck('fieldlistsubmit')) {
            if($ids = dimplode($_G['gp_delete'])) {
				form_delete_images("fieldid IN ($ids)");
				DB::query("DELETE FROM ".DB::table('xplus_form_item_var')." WHERE fieldid IN ($ids)");
				DB::query("DELETE FROM ".DB::table('xplus_form_field')." WHERE fieldid IN ($ids)");
			}

			if(is_array($_G['gp_title'])) {
				foreach($_G['gp_title'] as $id => $val) {
					$title = trim($_G['gp_title'][$id]);
					$displayorder = intval($_G['gp_displayorder'][$id]);
					if($title && ($title != $formfield[$id]['title'] || $displayorder != $formfield[$id]['displayorder'])) {
						DB::update('xplus_form_field', array(
							'displayorder' => $displayorder,
							'title' => $title,
						), "fieldid='$id'");
					}
				}
			}

			if(is_array($_G['gp_newtitle'])) {
				foreach($_G['gp_newtitle'] as $key => $value) {
					$newtitle = dhtmlspecialchars(trim($value));
					$newidentifier = strtolower(trim($_G['gp_newidentifier'][$key]));
					$createidentifier = '';
					if($newtitle) {
						if(empty($newidentifier) || is_numeric($newidentifier) || in_array(strtoupper($newidentifier), $mysql_keywords) || !preg_match("/^[a-z]+[a-z0-9_]+$/", $newidentifier)) {
							$createidentifier = '1';
						}
						$query = DB::query("SELECT fieldid FROM ".DB::table('xplus_form_field')." WHERE identifier='$newidentifier' LIMIT 1");
						if(DB::num_rows($query) || strlen($newidentifier) > 40) {
							$createidentifier = '1';
						}
						$createidentifier && $newidentifier = '';
						$data = array(
							'classid' => $classid,
							'displayorder' => intval($_G['gp_newdisplayorder'][$key]),
							'title' => $newtitle,
							'identifier' => $newidentifier,
							'type' => $_G['gp_newtype'][$key],
						);
						$newfieldid = DB::insert('xplus_form_field', $data, true);
						if($createidentifier) {
							$createidentifier = 'field_'.$newfieldid;
							DB::update('xplus_form_field', array('identifier' => $createidentifier), "fieldid='$newfieldid'");
						}
					}
				}
			}
			if($inguide || $formid) {
				$jumpurl = "plugin.php?id=xplus:admincr&mod=form&action=fieldlist&type=admin&classid={$classid}";
			} else {
				$jumpurl = "plugin.php?id=xplus:admincr&mod=form&action=fieldlist&type=admin&classid={$classid}";
			}
			showmessage('xplus:formfield_succeed', $jumpurl, 'succeed');
        }

		$classs = array();
		$query = DB::query("SELECT classid, title FROM ".DB::table('xplus_form_field_class')." WHERE displayorder>=0 ORDER BY displayorder");
		while($class = DB::fetch($query)) {
			$classs[$class[classid]] = $class;
		}

		$formfield = array();
		$query = DB::query("SELECT * FROM ".DB::table('xplus_form_field')." WHERE classid='$classid' ORDER BY displayorder");
		while($field = DB::fetch($query)) {
			$formfield[$field['fieldid']] = $field;
		}

		break;
		
	case 'fielddetail' :
		$fieldid = intval($_G['gp_fieldid']);
		$classid = intval($_G['gp_classid']);
		$formfield = DB::fetch_first("SELECT * FROM ".DB::table('xplus_form_field')." WHERE fieldid='$fieldid'");
		if(!$formfield) {
			showmessage('undefined_action', '', 'error');
		}

		if(!submitcheck('fielddetailsubmit')) {
			$classselect = array();
			$query = DB::query("SELECT classid, title FROM ".DB::table('xplus_form_field_class')." ORDER BY displayorder");
			while($class = DB::fetch($query)) {
				$classselect[] = array('classid' => $class['classid'], 'title' => $class['title']);
			}

			$formfield['rules'] = unserialize($formfield['rules']);
			$formfield['protect'] = unserialize($formfield['protect']);

		}else{
			$titlenew = trim($_G['gp_titlenew']);
			$classidnew = intval($_G['gp_classidnew']);
			$_G['gp_identifiernew'] = strtolower(trim($_G['gp_identifiernew']));
			if(!$titlenew || !$_G['gp_identifiernew']) {
				showmessage('xplus:formfield_field_invalid', '', 'error');
			}

			if(is_numeric($_G['gp_identifiernew']) || in_array(strtoupper($_G['gp_identifiernew']), $mysql_keywords) || !preg_match("/^[a-z]+[a-z0-9_]+$/", $_G['gp_identifiernew'])) {
				showmessage('xplus:formfield_fieldvariable_iskeyword', '', 'error');
			}

			$query = DB::query("SELECT type FROM ".DB::table('xplus_form_field')." WHERE identifier='{$_G['gp_identifiernew']}' AND fieldid<>'$fieldid' LIMIT 1");
			if(DB::num_rows($query) || strlen($_G['gp_identifiernew']) > 40) {
				showmessage('xplus:formfield_fieldvariable_invalid', '', 'error');
			}
			$oldfield = DB::fetch($query);

			DB::update('xplus_form_field', array(
				'classid' => $classidnew,
				'title' => $titlenew,
				'description' => $_G['gp_descriptionnew'],
				'identifier' => $_G['gp_identifiernew'],
				'type' => $_G['gp_typenew'],
				'unit' => $_G['gp_unitnew'],
				'expiration' => $_G['gp_expirationnew'],
				'protect' => addslashes(serialize($_G['gp_protectnew'])),
				'rules' => addslashes(serialize($_G['gp_rules'][$_G['gp_typenew']])),
			), "fieldid='$fieldid'");

			//处理form_value表数据
			if($oldfield['type'] != $_G['gp_typenew']) {
				$tables = array();
				$db = DB::object();
				$query = DB::query("SHOW TABLES LIKE '".DB::table('xplus_form_value_')."%'");
				while($table = DB::fetch($query)) {
					$table = array_values($table);
					$table = $table[0];
					if($db->version() > '4.1') {
						$query_column = DB::query("SHOW FULL COLUMNS FROM $table");
					} else {
						$query_column = DB::query("SHOW COLUMNS FROM $table", 'SILENT');
					}
					while($field = @DB::fetch($query_column)) {
						$identifier = $field['Field'];
						if($identifier == $_G['gp_identifiernew']) {
							$olddbtype = $field['Type'];
							$newdb = gettablecolumntype($_G['gp_typenew']);
							if(strtolower($olddbtype) != strtolower($newdb['type'])) {
								DB::query("ALTER TABLE $table CHANGE `$identifier` `$identifier` {$newdb['type']} NOT NULL {$newdb['default']}", 'SILENT');
							}
						}
					}
				}
			}
			showmessage('xplus:formfield_succeed', "plugin.php?id=xplus:admincr&mod=form&action=fieldlist&type=admin&classid={$classid}", 'succeed');
		}
		break;
    case 'guide' :
        $step = in_array($_G['gp_step'], array(1, 2, 3)) ? $_G['gp_step'] : 1;
		$inguide = !empty($_G['gp_inguide']) ? $_G['gp_inguide'] : 0;
        switch($step) {
            case 1 :
                if(submitcheck('step1_submit')) {
                    if(!$title = dhtmlspecialchars(trim($_G['gp_title']))) {
                        showmessage('xplus:formlist_tips_guide', 'plugin.php?id=xplus:admincr&mod=form&action=guide');
                    }
                    DB::insert('xplus_form_item', array('title' => $title, 'username' => $_G['username'], 'dateline' => TIMESTAMP)); 
					if($formid = DB::insert_id()) {
                        $query = DB::query("SELECT fieldid, type, identifier FROM ".DB::table('xplus_form_field')." WHERE fieldid IN (".dimplode($newfields).")");
						while($field = DB::fetch($query)) {
							$insertfieldid[$field['fieldid']]['type'] = $field['type'];
							$insertfieldid[$field['fieldid']]['identifier'] = $field['identifier'];
						}
						$query = DB::query("SHOW TABLES LIKE '".DB::table("xplus_form_value_{$formid}")."'");
						if(DB::num_rows($query) != 1) {
							$create_table_sql = "CREATE TABLE ".DB::table("xplus_form_value_{$formid}")." (
								valueid mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
								uid mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
								username char(15) NOT NULL DEFAULT '',
								ip char(15) NOT NULL DEFAULT '',
								dateline int(10) UNSIGNED NOT NULL DEFAULT '0',
								";
							foreach($addfield as $fieldid => $field) {
								$identifier = $insertfieldid[$fieldid]['identifier'];
								if($identifier) {
									$dbtype = gettablecolumntype($insertfieldid[$fieldid]['type']);
									$create_tablefield_sql .= "{$separator}{$identifier} {$dbtype['type']} NOT NULL {$dbtype['default']}\r\n";
									$separator = ', ';
									if(in_array($insertfieldid[$fieldid]['type'], array('radio', 'select', 'number')) || ($_G['gp_search'][$fieldid]['search'] && $insertfieldid[$fieldid]['type'] != 'textarea')) {
										$indexfield[$identifier] = $identifier;
									}
								}
							}
							$create_table_sql .= ($create_tablefield_sql ? $create_tablefield_sql.',' : '');
							$create_table_sql .= "PRIMARY KEY valueid (valueid), KEY uid (uid), KEY dateline (dateline)";
							if($indexfield) {
								foreach($indexfield as $index) {
									$create_table_sql .= "$separator KEY $index ($index)\r\n";
									$separator = ' ,';
								}
							}
							$create_table_sql .= ") TYPE=MyISAM;";
							$dbcharset = empty($dbcharset) ? str_replace('-','',CHARSET) : $dbcharset;
							$db = DB::object();
							$create_table_sql = syntablestruct($create_table_sql, $db->version() > '4.1', $dbcharset);
							DB::query($create_table_sql);
							$item_field = array(
								'description' => '',
								'seokeywords' => '',
								'seodesc' => ''
							);
							$query = DB::query("SELECT formid FROM ".DB::table('xplus_form_item_field')." WHERE formid='$formid'");
							if(DB::num_rows($query)) {
							} else {
								$item_field['formid'] = $formid;
								DB::insert('xplus_form_item_field', $item_field);
							}
                        }
                        dheader("Location: plugin.php?id=xplus:admincr&mod=form&action=guide&step=2&formid=$formid&inguide=1");
                    } else {
                        showmessage('xplus:formlist_tips_guide1', 'plugin.php?id=xplus:admincr&mod=form&action=guide');
                    }
                } 
                break;
            case 2 :
                if(!$formid = intval($_G['gp_formid'])) {
                    showmessage('xplus:admincr_unkownparams', 'plugin.php?id=xplus:admincr&mod=form&action=guide');
                } 
				

                if(submitcheck('formdetailsubmit')) {
                    $formnew = $_G['gp_formnew'];

					//表单基本表
					$starttime = strtotime($formnew['starttime']);
					$endtime = strtotime($formnew['endtime']);
					if($starttime > $endtime) {
						showmessage('xplus:admincr_date_error', 'plugin.php?id=xplus:admincr&mod=form&action=guide&step=2&formid=$formid&inguide=$inguide');
					}
					$form_item = array(
                        'title' => dhtmlspecialchars($formnew['title']),
						'valuenum' => intval($formnew['valuenum']),
						'templateid' => intval($formnew['templateid']),
						'available' => intval($formnew['available']),
						'allowguest' => intval($formnew['allowguest']),
						'seccode' => intval($formnew['seccode']),
						'starttime' => $starttime,
						'endtime' => $endtime,
					);
					DB::update('xplus_form_item', $form_item, "formid='{$formid}'");

					//表单扩展表
					$item_field = array(
						'description' => trim($formnew['description']),
						'seokeywords' => dhtmlspecialchars(trim($formnew['seokeywords'])),
						'seodesc' => dhtmlspecialchars(trim($formnew['seodesc']))
					);
					$query = DB::query("SELECT formid FROM ".DB::table('xplus_form_item_field')." WHERE formid='$formid'");
					if(DB::num_rows($query)) {
						DB::update('xplus_form_item_field', $item_field, "formid='{$formid}'");
					} else {
						$item_field['formid'] = $formid;
						DB::insert('xplus_form_item_field', $item_field);
					}

					if($inguide == 1) {
						dheader("Location: plugin.php?id=xplus:admincr&mod=form&action=guide&step=3&formid=$formid&inguide=1");
					} else {
						$query = DB::query("SELECT formid FROM ".DB::table('xplus_form_item_count')." WHERE formid='$formid'");
						if(!DB::num_rows($query)) {
							$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table("xplus_form_value_{$formid}"));
							DB::insert('xplus_form_item_count', array('formid' => $formid, 'count'=>$count));
						}
						showmessage('xplus:formdetail_update_succeed', 'plugin.php?id=xplus:admincr&mod=form');
					}
                }else{
					$form = DB::fetch_first("SELECT * FROM ".DB::table('xplus_form_item')." i LEFT JOIN ".DB::table('xplus_form_item_field')." f ON i.formid=f.formid WHERE i.formid='$formid'");
					$form['starttime'] = $form['starttime'] > 0 ? date('Y-m-d', $form['starttime']) : '';
					$form['endtime'] = $form['endtime'] > 0 ? date('Y-m-d', $form['endtime']) : '';

					if(!$form) {
						showmessage('undefined_action', '', 'error');
					}
					$query = DB::query("SELECT * FROM ".DB::table('xplus_common_template')." WHERE mid='$module[mid]' AND available='1'");
					while($template = DB::fetch($query)) {
						$templates[] = $template;
					} 
                }                
                break;
            case 3 :
                if(!$formid = intval($_G['gp_formid'])) {
                    showmessage('xplus:admincr_unkownparams', 'plugin.php?id=xplus:admincr&mod=form&action=guide');
                } 
				if(!submitcheck('formdetailsubmit')) {
					$formfields = $jsfieldids = '';
					$showfield = array();
					$query = DB::query("SELECT v.*, f.title, f.type, f.identifier
						FROM ".DB::table('xplus_form_item_var')." v, ".DB::table('xplus_form_field')." f
						WHERE v.formid='$formid' AND v.fieldid=f.fieldid ORDER BY v.displayorder ASC");
					while($field = DB::fetch($query)) {
						$jsfieldids .= "fieldids.push({$field['fieldid']});\r\n";
						$fieldtitle[$field['identifier']] = $field['title'];
						$showfield[$field['fieldid']] = $field;
						$showfield[$field['fieldid']]['type'] = $lang['formfield_type_'. $field['type']];
					}
					if(!$field){
						$field = DB::fetch_first("SELECT * FROM ".DB::table('xplus_form_item')." i LEFT JOIN ".DB::table('xplus_form_item_field')." f ON i.formid=f.formid WHERE i.formid='$formid'");
					}
				}else {
					$orgfield = $orgfields = $addfield = array();
					$query = DB::query("SELECT fieldid FROM ".DB::table('xplus_form_item_var')." WHERE formid='$formid'");
					while($orgfield = DB::fetch($query)) {
						$orgfields[] = $orgfield['fieldid'];
					}

					$addfield = $addfield ? (array)$addfield + (array)$_G['gp_displayorder'] : (array)$_G['gp_displayorder'];
					$jumpurl = "";
					if(empty($addfield)) {
						showmessage('xplus:formdetail_have_no_field', 'plugin.php?id=xplus:admincr&mod=form');
					}

					@$newfields = array_keys($addfield);
					@$delete = array_merge((array)$_G['gp_delete'], array_diff($orgfields, $newfields));

					if($delete) {
						if($ids = dimplode($delete)) {
							DB::query("DELETE FROM ".DB::table('xplus_form_item_var')." WHERE formid='$formid' AND fieldid IN ($ids)");
						}
						foreach($delete as $id) {
							unset($addfield[$id]);
						}
					}

					$insertfieldid = $indexfield = array();
					$create_table_sql = $separator = $create_tablefield_sql = '';

					if(is_array($newfields) && !empty($newfields)) {
						$query = DB::query("SELECT fieldid, type, identifier FROM ".DB::table('xplus_form_field')." WHERE fieldid IN (".dimplode($newfields).")");
						while($field = DB::fetch($query)) {
							$insertfieldid[$field['fieldid']]['type'] = $field['type'];
							$insertfieldid[$field['fieldid']]['identifier'] = $field['identifier'];
						}
						$query = DB::query("SHOW TABLES LIKE '".DB::table("xplus_form_value_{$formid}")."'");
						if(DB::num_rows($query) != 1) {
							$create_table_sql = "CREATE TABLE ".DB::table("xplus_form_value_{$formid}")." (
								valueid mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
								uid mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
								username char(15) NOT NULL DEFAULT '',
								ip char(15) NOT NULL DEFAULT '',
								dateline int(10) UNSIGNED NOT NULL DEFAULT '0',
								";
							foreach($addfield as $fieldid => $field) {
								$identifier = $insertfieldid[$fieldid]['identifier'];
								if($identifier) {
									$dbtype = gettablecolumntype($insertfieldid[$fieldid]['type']);
									$create_tablefield_sql .= "{$separator}{$identifier} {$dbtype['type']} NOT NULL {$dbtype['default']}\r\n";
									$separator = ', ';
									if(in_array($insertfieldid[$fieldid]['type'], array('radio', 'select', 'number')) || ($_G['gp_search'][$fieldid]['search'] && $insertfieldid[$fieldid]['type'] != 'textarea')) {
										$indexfield[$identifier] = $identifier;
									}
								}
							}
							$create_table_sql .= ($create_tablefield_sql ? $create_tablefield_sql.',' : '');
							$create_table_sql .= "PRIMARY KEY valueid (valueid), KEY uid (uid), KEY dateline (dateline)";
							if($indexfield) {
								foreach($indexfield as $index) {
									$create_table_sql .= "$separator KEY $index ($index)\r\n";
									$separator = ' ,';
								}
							}
							$create_table_sql .= ") TYPE=MyISAM;";
							$dbcharset = empty($dbcharset) ? str_replace('-','',CHARSET) : $dbcharset;
							$db = DB::object();
							$create_table_sql = syntablestruct($create_table_sql, $db->version() > '4.1', $dbcharset);
							DB::query($create_table_sql);
							$item_field = array(
								'description' => '',
								'seokeywords' => '',
								'seodesc' => ''
							);
							$query = DB::query("SELECT formid FROM ".DB::table('xplus_form_item_field')." WHERE formid='$formid'");
							if(DB::num_rows($query)) {
							} else {
								$item_field['formid'] = $formid;
								DB::insert('xplus_form_item_field', $item_field);
							}
					   	} else {
							$tables = array();
							$db = DB::object();
							if($db->version() > '4.1') {
								$query = DB::query("SHOW FULL COLUMNS FROM ".DB::table("xplus_form_value_{$formid}"), 'SILENT');
							} else {
								$query = DB::query("SHOW COLUMNS FROM ".DB::table("xplus_form_value_{$formid}"), 'SILENT');
							}
							while($field = @DB::fetch($query)) {
								$tables[$field['Field']] = 1;
							}
							foreach($addfield as $fieldid => $field) {
								$identifier = $insertfieldid[$fieldid]['identifier'];
								if(!$tables[$identifier]) {
									$fieldname = $identifier;
									$dbtype = gettablecolumntype($insertfieldid[$fieldid]['type']);
									$fieldtype = "{$dbtype['type']} NOT NULL {$dbtype['default']}";
									DB::query("ALTER TABLE ".DB::table("xplus_form_value_{$formid}")." ADD `$fieldname` $fieldtype");

									if(in_array($insertfieldid[$fieldid]['type'], array('radio', 'select', 'number'))) {
										$indexfield[$identifier] = $identifier;
									}
								}
							}
							foreach($_G['gp_search'] as $fieldid=>$issearch) {
								$issearch = intval($issearch);
								if($issearch && $insertfieldid[$fieldid]['type'] != 'textarea') {
									$identifier = $insertfieldid[$fieldid]['identifier'];
									$indexfield[$identifier] = $identifier;
								}
							}
							if($indexfield) {
								foreach($indexfield as $index) {
									DB::query("ALTER TABLE ".DB::table("xplus_form_value_{$formid}")." ADD INDEX $index ($index)", 'SILENT');
								}
							}
						}
						foreach($addfield as $id => $val) {
							$id = intval($id);
							$fieldid = DB::fetch_first("SELECT fieldid FROM ".DB::table('xplus_form_field')." WHERE fieldid='$id'");
							if($fieldid) {
								$data = array(
									'formid' => $formid,
									'fieldid' => $id,
									'displayorder' => intval($_G['gp_displayorder'][$id]),
									'available' => $_G['gp_available'][$id] ? 1 : 0,
									'required' => $_G['gp_required'][$id] ? 1 : 0,
									'search' => $_G['gp_search'][$id] ? 1 : 0,
									'listshow' => $_G['gp_listshow'][$id] ? 1 : 0,
								);
								DB::insert('xplus_form_item_var', $data, 0, 1);
							} else {
								DB::query("DELETE FROM ".DB::table('xplus_form_item_var')." WHERE formid='$formid' AND fieldid='$id'");
							}
						}
					}
					if($inguide == 1) {
						showmessage('xplus:formlist_tips_guide_success', 'plugin.php?id=xplus:admincr&mod=form');
					} else {
						showmessage('xplus:formdetail_update_succeed', ADMINCRSCRIPT."action=guide&step=3&formid={$formid}&inguide=0");
					}
                }
			default :
				break;
			}
        break;
    case 'rlist' :
		$do = !empty($_G['gp_do']) ? $_G['gp_do'] : '';
		if(!$formid = intval($_G['gp_formid'])) {
			showmessage('xplus:admincr_unkownparams', 'plugin.php?id=xplus:admincr&mod=form');
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
		if(empty($do)) {

			if(submitcheck('searchsubmit', 1) || submitcheck('delsubmit')) {

				if(submitcheck('searchsubmit', 1)) {
					$searchfield = $_G['gp_searchfield'];
					$mpurl = "plugin.php?id=xplus:admincr&mod=form&action=rlist&formid={$formid}&searchsubmit=true";
					if(!is_array($searchfield)) {
						$mpurl .= '&searchfield='.$searchfield;
						$searchfield = unserialize(base64_decode($searchfield));
					} else {
						$mpurl .= '&searchfield='.base64_encode(serialize($searchfield));
					}

					$wheresql = $and = '';
					if($searchfield) {
						foreach($searchfield as $fieldid => $field) {
							if(is_numeric($fieldid)) {
								$fieldname = !empty($itemfieldarray[$fieldid]['identifier']) ? $itemfieldarray[$fieldid]['identifier'] : '';
							} elseif(in_array($fieldid, array('uid', 'ip'))) {
								$fieldname = $fieldid;
								$field['condition'] = '';
							}
							if($field['value']) {
								if(in_array($field['type'], array('number', 'radio', 'select'))) {
									$field['value'] = intval($field['value']);
									$exp = '=';
									if($field['condition']) {
										$exp = $field['condition'] == 1 ? '>' : '<';
									}
									$sql = "$fieldname$exp'{$field[value]}'";
								} elseif($field['type'] == 'checkbox') {
									$sql = "$fieldname LIKE '%".(implode("%", $field['value']))."%'";
								} elseif($field['type'] == 'range') {
									$sql = $field['value']['min'] || $field['value']['max'] ? "$fieldname BETWEEN ".intval($field['value']['min'])." AND ".intval($field['value']['max'])."" : '';
								} else {
									$sql = "$fieldname LIKE '%{$field[value]}%'";
								}
								$wheresql .= $and."$sql ";
								$and = 'AND ';
							}
						}
						$wheresql = trim($wheresql);
						$valueids = array();
						if($wheresql) {
							$query = DB::query("SELECT valueid FROM ".DB::table("xplus_form_value_{$formid}").' WHERE '.$wheresql);
							while($value = DB::fetch($query)) {
								$valueids[] = $value['valueid'];
							}
						}
					}
					//获取搜索数据
					if($valueids || empty($wheresql)) {
						$selectsql = 'valueid, dateline, uid, username';
						$listshowtitle = array('', 'formcontent_username', 'formcontent_dateline');
						if(is_array($listshowarray)) foreach($listshowarray as $fieldid => $field) {
							$selectsql .= ', '.$field['identifier'];
							$listshowtitle[] = $field['title'];
						}
						$listshowtitle[] = 'poll_manage';
						$lpp = 20;
                        $page = !$page ? 1 : $page;
						$start_limit = ($page - 1) * $lpp;
						$where = '';
						$valueids && $where = ' WHERE valueid IN ('.dimplode($valueids).')';
						$valuecount = DB::result_first("SELECT count(*) FROM ".DB::table("xplus_form_value_{$formid}").$where);
						$query = DB::query("SELECT $selectsql FROM ".DB::table("xplus_form_value_{$formid}")."$where LIMIT $start_limit, $lpp");
						while($value = DB::fetch($query)) {
						    $value['dateline'] = dgmdate($value['dateline'], 'Y-m-d H:i');
                            $userlist[] = $value;
						}
						$multipage = multi($valuecount, $lpp, $page, $mpurl, 0, 3);
					}
				} elseif(submitcheck('delsubmit',1)) {
					if(is_array($_G['gp_delete'])) {
						if($deleteids = dimplode($_G['gp_delete'])) {
							form_delete_images("formid='{$formid}' AND valueid IN ($deleteids)");
							DB::query("DELETE FROM ".DB::table("xplus_form_value_{$formid}")." WHERE valueid IN ($deleteids)");
							$affected_rows = DB::affected_rows();
							if($affected_rows > 0) {
								DB::query('UPDATE '.DB::table('xplus_form_item_count')." SET `count`=`count`-'$affected_rows' WHERE formid='$formid'");
							}
						}
					}
					showmessage('xplus:formcontent_delete_succeed', "plugin.php?id=xplus:admincr&mod=form&action=rlist&formid={$formid}&searchsubmit=1", 'succeed');
				}
			}
		} elseif($do == 'valuedetail') {
			$formvalue = DB::fetch_first("SELECT * FROM ".DB::table("xplus_form_value_{$formid}")." WHERE valueid='{$_G[gp_valueid]}' LIMIT 1");
		} else{
			$html = "xplus:admincr_unkownparams";
		}
        break;
	default :
        break;        
}

function form_delete_images($condition) {
	if($condition) {
		$aids = array();
		$query = DB::query('SELECT aid FROM '.DB::table('xplus_form_attachment')." WHERE $condition");
		while($row = DB::fetch($query)) {
			$aids[] = $row['aid'];
		}
		if($aids) {
			delete_images($aids);
		}
		DB::query("DELETE FROM ".DB::table('xplus_form_attachment')." WHERE $condition");
	}
}

include template('xplus:admincr/form');

?>