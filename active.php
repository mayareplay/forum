<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: member.php 20112 2011-02-15 07:10:53Z monkey $
 */
//@header("location: home.php?mod=spacecp&ac=profile&op=phone");
//exit;


define('APPTYPEID', 0);
define('CURSCRIPT', 'active');

require './source/class/class_core.php';

$discuz = & discuz_core::instance();



$modarray = array('activate', 'clearcookies', 'emailverify', 'getpasswd',
	'groupexpiry', 'logging', 'lostpasswd',
	'register', 'regverify', 'switchstatus', 'connect');


$mod = !in_array($discuz->var['mod'], $modarray) ? 'register' : $discuz->var['mod'];

define('CURMODULE', $mod);

$discuz->init();


if(!$_GET['by']||!in_array($_GET['by'],array('email','mobile'))){
    $_GET['by'] = 'mobile';
}

$uid = $_G['uid'];

if(!$uid || $_G['groupid']!=8){
    /*
    include template('common/header_ajax');
    echo '你不需要验证';
    include template('common/footer_ajax');
    */
    header("location:index.php");
    exit;
}    

if($_GET['action'] == 'ckcode'){
    // 检查激活码是否正确
    
    $vcode = intval($_GET['vcode']);
   
    $bind = new bindphone;
    $binddetial =$bind->get_bind_info($_G['uid']);
    //$bind->unbind_phone($_G['uid']);
     $res = $bind->bind_phone($_G['uid'],$_GET['mobile'],$vcode);

       if($res){

            $addmsg = '';
            if($_G['groupid']==8){
                // 需要激活
                $uid = $_G['uid'];
                //DB::query("update pre_common_member set groupid=10 where uid='$uid' and groupid=8");
                C::t('common_member')->update($uid, array('groupid'=>10));
                $addmsg = '您的账号已激活<script>gotoReferer()</script>';
            }
            
           $result = $addmsg;
       }else{
        if($bind->error == -1 || $bind->error == -4){
            $result = $bind->errors[$bind->error];
            }else{
                 $result = $bind->errors[$bind->error];
            }
       }

include template('common/header_ajax');
    echo $result;
    include template('common/footer_ajax');
    
  /*  
    include template('common/header_ajax');
    echo $result;
    include template('common/footer_ajax');
    exit;
   */ 
    
}

//$tel = DB::result_first("select tel from wj_usertel where uid='$uid'");

$referer = dreferer();

include(template('member/active'));
/*
require libfile('function/member');
require libfile('class/member');
runhooks();


require DISCUZ_ROOT.'./source/module/member/member_'.$mod.'.php';
*/
?>