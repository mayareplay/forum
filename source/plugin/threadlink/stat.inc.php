<?php
/*
 *	Author: IAN - zhouxingming
 *	Last modified: 2011-10-24 18:10
 *	Filename: stat.inc.php
 *	Description: 
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$subop = $_G['gp_subop'];
if($subop == 'xml') {
	$siteuniqueid = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='siteuniqueid'");
	$stat_hash = md5($siteuniqueid."\t".substr($_G['timestamp'], 0, 6));
	$hash = md5($_G['uid']."\t".substr($_G['timestamp'], 0, 6));
	if($_G['gp_hash'] != $hash && $stat_hash != $_G['gp_hash']) {
		showmessage('no_privilege_statdata');
	}
	@header("Expires: -1");
	@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
	@header("Pragma: no-cache");
	@header("Content-type: application/xml; charset=utf-8");

	$_G['gp_begin'] = dgmdate(dmktime($_G['gp_begin']), 'Y-m-d');
	$_G['gp_end'] = dgmdate(dmktime($_G['gp_end']), 'Y-m-d');
	$begin = !empty($_G['gp_begin']) ? $_G['gp_begin'] : '';
	$end = !empty($_G['gp_end']) ? $_G['gp_end'] : '';
	$x = $g0 = $g1 = $g2 = $g3 = $g4 = '';
	$option = intval($_G['gp_option']);
	$option = !$option ? (1+2+4+8) : $option;
	if($begin && $end) {
		$query = DB::query("SELECT * FROM ".DB::table('threadlink_stat')." WHERE daytime<='$end' AND daytime>='$begin' ORDER BY daytime ASC LIMIT 50");
		$i = 0;
		while($data = DB::fetch($query)) {
			$i++;
			$x .= '<value xid="'.$i.'">'.$data['daytime'].'</value>';
			$g0 .= '<value xid="'.$i.'">'.$data['create_base'].'</value>';
			$g1 .= '<value xid="'.$i.'">'.($data['modify_link'] + $data['create_link']).'</value>';
			$g2 .= '<value xid="'.$i.'">'.$data['create_link'].'</value>';
			$g3 .= '<value xid="'.$i.'">'.$data['clicks'].'</value>';
		}
		
	}
	$xml .= '<chart><xaxis>';
	$xml .= $x;
	$xml .= '</xaxis><graphs>';
	($option & 1) && $xml .= '<graph gid="1" title="'.diconv(lang('plugin/threadlink', "stat_create_base"), CHARSET, 'utf8').'">'.$g0.'</graph>';
	($option & 2) && $xml .= '<graph gid="2" title="'.diconv(lang('plugin/threadlink', "stat_create_link"), CHARSET, 'utf8').'">'.$g2.'</graph>';
	($option & 4) && $xml .= '<graph gid="3" title="'.diconv(lang('plugin/threadlink', "stat_modify_link"), CHARSET, 'utf8').'">'.$g1.'</graph>';
	($option & 8) && $xml .= '<graph gid="4" title="'.diconv(lang('plugin/threadlink', "stat_clicks"), CHARSET, 'utf8').'">'.$g3.'</graph>';
	$xml .= '</graphs></chart>';
	echo $xml;
	exit();
} else {
	if(!defined('IN_ADMINCP')) {
		exit('Access Denied');
	}
	if(submitcheck('submit')) {
		$_G['gp_date_after'] = dgmdate(dmktime($_G['gp_date_after']), 'Y-m-d');
		$_G['gp_date_before'] = dgmdate(dmktime($_G['gp_date_before']), 'Y-m-d');
		$begin = !empty($_G['gp_date_after']) ? $_G['gp_date_after'] : dgmdate($_G['timestamp']-604800, 'Y-m-d');
		$end = !empty($_G['gp_date_before']) ? $_G['gp_date_before'] : dgmdate($_G['timestamp'], 'Y-m-d');
		if($begin > $end) {
			cpmsg(lang('plugin/threadlink', 'stat_time_error'), 'action=plugins&operation=config&do='.$pluginid.'&identifier=threadlink&pmod=stat', 'error');
		}
	}
	$option = 0;
	if($_G['gp_create_base']) $option += 1;
	if($_G['gp_create_link']) $option += 2;
	if($_G['gp_modify_link']) $option += 4;
	if($_G['gp_clicks']) $option += 8;
	showoption();
	if(submitcheck('submit')) {
		showtableheader(lang('plugin/threadlink', 'stat_view'), '');

		$hash = md5($_G['uid']."\t".substr($_G['timestamp'], 0, 6));


		$flash = '';
		$xmlurl_number = $_G['siteurl'].'plugin.php?id=threadlink:stat&subop=xml&option='.$option.'&begin='.$begin.'&end='.$end.'&hash='.$hash;
		$xmlurl_number = urlencode($xmlurl_number);
		$flash = <<<ttt
			<script type="text/javascript">
				document.write(AC_FL_RunContent(
					'width', '80%', 'height', '300',
					'src', './static/image/common/stat.swf?path=&settings_file=source/plugin/threadlink/setting.xml&data_file=$xmlurl_number',
					'quality', 'high', 'wmode', 'transparent'
				));
			</script>
ttt;
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('threadlink_stat')." WHERE daytime >= '$begin' AND daytime <= '$end'");
		if(empty($count) || $count < 1) {
			showtablerow('', array(''), array(lang('plugin/threadlink', 'no_data')));
		} else {
			showtablerow('', array(''), array($flash));
		}
		showtablefooter();
		echo '<ul id="tipslis">'.lang('plugin/threadlink', 'stat_tips').'</ul>';
	}

	showformfooter();
}

function showoption() {
	global $begin,$end,$_G,$pluginid,$option;
	$begin = !empty($begin) ? $begin : dgmdate(TIMESTAMP-604800, 'Y-m-d');
	$end = !empty($end) ? $end : dgmdate(TIMESTAMP, 'Y-m-d');
	echo '<script src="static/js/calendar.js" type="text/javascript"></script>';
	showformheader('plugins&operation=config&do='.$pluginid.'&identifier=threadlink&pmod=stat');
	showtableheader(lang('plugin/threadlink', 'stat'), '');
	
	showtablerow('', array('class="td23"', ''), array(lang('plugin/threadlink', 'stat_options'), 
		'<input type="checkbox" class="checkbox"'.(($option & 1) || ($option == 0) ? 'checked="checked"' : '').' name="create_base" id="create_base" /><label for="create_base">'.lang('plugin/threadlink', 'stat_create_base').'</label>'.
		'<input type="checkbox" class="checkbox"'.(($option & 2) || ($option == 0) ? 'checked="checked"' : '').' name="create_link" id="create_link" /><label for="create_link">'.lang('plugin/threadlink', 'stat_create_link').'</label>'.
		'<input type="checkbox" class="checkbox"'.(($option & 4) || ($option == 0) ? 'checked="checked"' : '').' name="modify_link" id="modify_link" /><label for="modify_link">'.lang('plugin/threadlink', 'stat_modify_link').'</label>'.
		'<input type="checkbox" class="checkbox"'.(($option & 8) || ($option == 0) ? 'checked="checked"' : '').' name="clicks" id="clicks" /><label for="clicks">'.lang('plugin/threadlink', 'stat_clicks').'</label>'));
	showtablerow('', array('class="td23"', ''), array(lang('plugin/threadlink', 'stat_date'), '<input type="text" class="txt" name="date_after" value="'.$begin.'" style="width: 108px; margin-right: 5px;" onclick="showcalendar(event, this)"> -- <input type="text" class="txt" name="date_before" value="'.$end.'" style="width: 108px; margin-left: 5px;" onclick="showcalendar(event, this)"><input type="submit" class="btn" id="submit_submit" name="submit" title="" value="'.lang('plugin/threadlink', 'view').'">'));
	showtablefooter();
}
?>
