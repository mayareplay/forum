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
    // ����Ƶ��
    
}else if($tpl == 'yp'){
    // ��ҳƵ��
}else if($tpl == 'esf'){
    
}else{
    // ��׼��ʽ    
    if($_G['uid']){
    	$newpm = $_G[member][newpm]?'color:red':'';
    	$newprompt =  $_G[member][newprompt]?'color:red':'';
    ?>
    document.write('<div class="loginsuccess"> ����!<a class="username" href="<? echo $bbs_url?>space.php?uid=<? echo $_G['uid']?>"><? echo $_G['username']?></a>|<? echo $_G[groupid]==8?'<a href="'.$bbs_url.'active.php" style="color:red">[�����˺�]</a>':''?><a  href="<? echo $bbs_url?>home.php?mod=space&do=pm" target="_blank">����Ϣ</a>| <a href="<? echo $bbs_url?>home.php?mod=spacecp" target="_blank">����</a>|<a style="<? echo $newprompt?> " href="<? echo $bbs_url?>home.php?mod=space&do=notice" target="_blank">����</a>|<a href="<? echo $bbs_url?>member.php?mod=logging&action=logout&formhash=<? echo $_G['formhash']?>&referer='+encodeURIComponent(document.location.href)+'">�˳�</a></div>');
    <?
    }else{
    ?>

	function gotoqqlogin(){
		var url ="<? echo $bbs_url?>connect.php?mod=login&op=init&referer="+encodeURIComponent(document.location.href)+"&statfrom=login_simple";
		location = url;
	}
	
    document.write('<div class="loginsuccess"><a  href="<? echo $bbs_url?>search.php">����</a> | <a  href="<? echo $bbs_url?>member.php?mod=logging&action=login">��¼</a> | <a  href="<? echo $bbs_url?>member.php?mod=register">ע��</a></div>');
    
    
    <?
    }
    

}

?>






