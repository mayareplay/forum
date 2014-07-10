<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: template.inc.php 16840 2011-09-06 08:19:59Z Niexinyuan $
 */

if(!defined('IN_DISCUZ') && !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

define('XPLUS_ROOT', DISCUZ_ROOT.'./source/plugin/xplus/');
define('XPLUS_FUNCTION_ROOT', XPLUS_ROOT.'./function/');

require XPLUS_FUNCTION_ROOT.'./function_core.php';

$identifier = $plugin['identifier'];
$Plang = $scriptlang['xplus'];

$_G['gp_mid'] = intval($_G['gp_mid']);

$allselected = empty($_G['gp_mid']) ? 1 : 0;
$modulemenu[] = array($lang['all'], 'template&operation=list', $allselected);
$moduleselect = '';

$query = DB::query("SELECT * FROM ".DB::table('xplus_common_module')." WHERE available='1'");
$moduleselect = '<select name="newmodule[]"><option value="0">'.$Plang['admincp_template_all'].'</option>';
while($module = DB::fetch($query)) {
    if($module['type'] != 1) {
        $moduleselect .= '<option value="'.$module['mid'].'">'.$module['name'].'</option>';
        $selected = $_G['gp_mid'] == $module['mid'] ? 1 : 0;
        $modulemenu[] = array($module['name'], 'template&operation=list&mid='.$module['mid'], $selected);
        $modulelist[$module['mid']] = $module['name'];
    }    
}
$moduleselect .= '</select>';


if(submitcheck('listsubmit')) {
    
    $delete = is_array($_G['gp_delete']) ? $_G['gp_delete'] : '';
    if($delete) {
        $ids = $comma = '';
        foreach($delete as $id) {
			$ids .= "$comma'$id'";
			$comma = ',';
        }
        DB::query("DELETE FROM ".DB::table('xplus_common_template')." WHERE templateid IN ($ids)");			
    }
    
    if(is_array($_G['gp_newname'])) {
		$_G['gp_newdirectory'] = is_array($_G['gp_newdirectory']) ? $_G['gp_newdirectory'] : array();
		$_G['gp_newcopyright'] = is_array($_G['gp_newcopyright']) ? $_G['gp_newcopyright'] : array();
		foreach($_G['gp_newname'] as $id => $val) {
		    $newname = trim($_G['gp_newname'][$id]);
            $newdirectory = trim($_G['gp_newdirectory'][$id]);
            if(empty($newname) || empty($newdirectory)) {
                continue;
            }
			$data = array(
				'name' => dhtmlspecialchars($newname),
				'directory' => dhtmlspecialchars($newdirectory),
				'available' => intval($_G['gp_newavailable'][$id]),
				'copyright' => dhtmlspecialchars(trim($_G['gp_newcopyright'][$id])),
				'mid' => intval($_G['gp_newmodule'][$id]),
			);
            DB::insert('xplus_common_template', $data);
        }
	}
    
    if(is_array($_G['gp_templateid'])) {
		foreach($_G['gp_templateid'] as $id) {
            $avaliable = $_G['gp_available'][$id] ? 1 : 0;
            DB::query("UPDATE ".DB::table('xplus_common_template')." SET available='$avaliable' WHERE templateid='$id'");
		}
	}
    
    xplus_save_syscache('xplus_common_template', 'xplus_common_template', 'templateid');   
    cpmsg($Plang['admincp_template_modify_successed'], "action=plugins&operation=config&do=$pluginid&identifier=$identifier&pmod=template", 'succeed');
    
} else {
    
    echo <<<EOT
<script type="text/JavaScript">
var rowtypedata = [
	[
		[1,''],
		[1,'<input type="text" class="text" name="newname[]" value="">'],
		[1,'<input type="text" class="text" name="newdirectory[]" value="">'],
		[1,'$moduleselect'],
		[1,'<input type="text" class="text" name="newcopyright[]" size="35">'],
		[1,'<input type="checkbox" class="checkbox" name="newavailable[]" value="1" checked="checked">']
	]
];
</script>
EOT;
    showformheader("plugins&operation=config&do=$pluginid&identifier=$identifier&pmod=template");
    showtableheader($Plang['admincp_template_list'], 'fixpadding');
    showsubtitle(array('', $Plang['admincp_template_name'], $Plang['admincp_template_dir'], $Plang['admincp_template_module'], $Plang['admincp_template_copyright'], $Plang['admincp_template_enable']));
    
    $query = DB::query("SELECT * FROM ".DB::table('xplus_common_template')." WHERE templateid>0 ORDER BY templateid ASC");
    while($template = DB::fetch($query)) {
        $checked = $template['available'] ? 'checked="checked"' : '';
        showhiddenfields(array('templateid[]' => $template['templateid']));
        showtablerow('', array('class="td25"', 'width="22%"', 'width="22%"', 'width="15%"', 'width="28%"', ''), array(
			"<input type=\"checkbox\" class=\"checkbox\" name=\"delete[]\" value=\"$template[templateid]\">",
			$template['name'],
			$template['directory'],
			$modulelist[$template['mid']],
			$template['copyright'],
			"<input type=\"checkbox\" class=\"checkbox\" name=\"available[$template[templateid]]\" value=\"1\" $checked>",
        ));       
    }
    
    echo '<tr><td></td><td colspan="7"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$Plang['admincp_template_add'].'</a></div></td></tr>';
    showsubmit('listsubmit', 'submit', 'del');
    showtablefooter();
    showformfooter();

}

?>