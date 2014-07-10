<?
require './source/class/class_core.php';
$discuz = & discuz_core::instance();
$discuz->init();

if($_SERVER['HTTP_HOST']=='localhost'){
    $bbs_url = 'http://localhost/wangjing_cn/www/forum/';
}else{
    $bbs_url = 'http://bbs.cibidi.cn/';
}

//print_r($_SERVER);

$tpl = trim($_GET['t']);
if($tpl == 'house'){
    // 房产频道
    
}else if($tpl == 'yp'){
    // 黄页频道
}else if($tpl == 'esf'){
    
}else{
    // 标准形式    
    if($_G['uid']){
    	$newpm = $_G[member][newpm]?'color:red':'';
    	$newprompt =  $_G[member][newprompt]?'color:red':'';
    ?>
    document.write('<div class="loginsuccess"> 您好!<a class="username" href="<? echo $bbs_url?>space.php?uid=<? echo $_G['uid']?>"><? echo $_G['username']?></a>|<? echo $_G[groupid]==8?'<a href="'.$bbs_url.'active.php" style="color:red">[激活账号]</a>':''?><a  href="<? echo $bbs_url?>home.php?mod=space&do=pm" target="_blank">短信息</a>| <a href="<? echo $bbs_url?>home.php?mod=spacecp" target="_blank">设置</a>|<a style="<? echo $newprompt?> " href="<? echo $bbs_url?>home.php?mod=space&do=notice" target="_blank">提醒</a>|<a href="<? echo $bbs_url?>member.php?mod=logging&action=logout&formhash=<? echo $_G['formhash']?>&referer='+encodeURIComponent(document.location.href)+'">退出</a></div>');
    <?
    }else{
    ?>

	function gotoqqlogin(){
		var url ="<? echo $bbs_url?>connect.php?mod=login&op=init&referer="+encodeURIComponent(document.location.href)+"&statfrom=login_simple";
		location = url;
	}
	
    document.write('<div class="loginsuccess"><a  href="<? echo $bbs_url?>search.php">搜索</a> | <a  href="<? echo $bbs_url?>member.php?mod=logging&action=login">登录</a> | <a  href="<? echo $bbs_url?>member.php?mod=register">注册</a></div>');
    
    
    <?
    }
    

}

?>






