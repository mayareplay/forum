<?php
/*
 *	Author: IAN - zhouxingming
 *	Last modified: 2011-09-08 17:39
 *	Filename: uninstall.php
 *	Description: 
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<SQL
DROP TABLE pre_threadlink_base ;
DROP TABLE pre_threadlink_link ;
DROP TABLE pre_threadlink_stat ;
SQL;
runquery($sql);
$finish = 1;
?>
