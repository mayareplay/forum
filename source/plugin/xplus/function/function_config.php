<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_config.php 16840 2011-09-05 08:19:59Z Niexinyuan $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function showusergroup($title, $varname, $value, $description) {
    $type = '<select name="'.$varname.'[]" size="10" multiple="multiple"><option value=""'.(@in_array('', $value) ? ' selected' : '').'>'.cplang('plugins_empty').'</option>';
    $value = is_array($value) ? $value : array($value);
    $query = DB::query("SELECT type, groupid, grouptitle, radminid FROM ".DB::table('common_usergroup')." ORDER BY (creditshigher<>'0' || creditslower<>'0'), creditslower, groupid");
    $groupselect = array();
    while($group = DB::fetch($query)) {
        $group['type'] = $group['type'] == 'special' && $group['radminid'] ? 'specialadmin' : $group['type'];
        $groupselect[$group['type']] .= '<option value="'.$group['groupid'].'"'.(@in_array($group['groupid'], $value) ? ' selected' : '').'>'.$group['grouptitle'].'</option>';
    }
    $type .= '<optgroup label="��Ա�û���">'.$groupselect['member'].'</optgroup>'.
            ($groupselect['special'] ? '<optgroup label="�����û���">'.$groupselect['special'].'</optgroup>' : '').
            ($groupselect['specialadmin'] ? '<optgroup label="�Զ��������">'.$groupselect['specialadmin'].'</optgroup>' : '').
            '<optgroup label="ϵͳ�û���">'.$groupselect['system'].'</optgroup></select>';
    showsetting($title, '', '', $type, '', 0, nl2br($description));    
}

?>