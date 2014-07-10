<?
header('HTTP/1.1 301 Moved Permanently');
$tid = intval($_GET['tid']);
$page = intval($_GET['page']);

$page = max(1,$page);
header('Location: http://bbs.wangjing.cn/thread-'.$tid.'-'.$page.'-1.html');
exit;
?>