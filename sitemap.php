<?php
if($_SERVER['HTTP_HOST']!='localhost' && $_SERVER['HTTP_HOST']!='bbs.wangjing.cn'){
	 $url='http://bbs.wangjing.cn'.str_replace('/www/forum',"",$_SERVER['REQUEST_URI']);
	 header('HTTP/1.1 301 Moved Permanently');
	 @header("location: $url");
}
define('APPTYPEID', 2);
define('CURSCRIPT', 'forum');
require './source/class/class_core.php';


C::app()->init();

$ppp = 50000;
$page = intval(max(1,$_GET['page']));
$start = ($page-1)*$ppp;
$query = DB::query("select tid,subject from pre_forum_thread where displayorder>-1 order by lastpost desc limit $start,$ppp");
while($row = DB::fetch($query)){
    echo 'http://bbs.wangjing.cn/thread-'.$row['tid'].'-1-1.html'."\n";
}

?>