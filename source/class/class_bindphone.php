<?php

class bindphone{
    
    public $uid;
    private $flagfield='field8';
    public $interval=120;
    public $errors = array(
        '-1'=>'该号码已被占用',
        '-2'=>'验证码为空',
        '-3'=>'验证码错误,或验证码已失效',
        '-4'=>'电话号码格式不正确',
        '-5'=>'一天内错误次数过多,请明天再试',
        '-6'=>'验证码输入错误过多请稍后重试',
        '-7'=>'原电话号码不符',
        '-8'=>'原电话号码输入错误过多,请稍后再试',
        '-9'=>'验证码已发送'    
    );
    public $error='';
    
    public function get_bind_info($uid){
        $detial = C::t('common_member_profile')->fetch($uid);
        if(!$detial || !$detial['mobile'] || !$detial[$this->flagfield]){
            return;
        }
        
        $data = array(
            'uid'=>$uid,
            'mobile'=>$detial['mobile'],
            'flag'=>intval($detial[$this->flagfield])
        );
        return $data;
    }
    public function ckBind($uid,$mobile)
    {
        //$had = C::t('common_member_profile')->count_by_field(array('uid',$flagfield),array('!='.$uid,$mobile));
        $mobile = addslashes($mobile);
        $had = DB::result_first('select count(*) from '.DB::table('common_member_profile')." where uid!='$uid' and mobile='$mobile' and field8=1");
        if($had){
            $this->error = -1;
            return false;
        }
        return true;
    }
    
    public function ckCode($uid,$mobile,$code){
        
        $code = addslashes($code);
        if(!$code){
            $this->error='-2';
            return false;
        }
        
        if(!$this->isTel($mobile)){
            return false;
        }
       
        
        $timelimit = time()-$this->interval;
        $errno = DB::result_first("select count(*) from wj_phonelog where uid='".$uid."' and  dateline >$timelimit");
 
        
        if($errno>5){
            $this->error = -6;
            return false;
        }
    
        
        // 1分钟内
        $timelimit = time()-$this->interval;
        $c = DB::result_first("select code from wj_phoneckcode where uid='".$uid."' and tel='$mobile' and code='$code' and sendtime >$timelimit");
        if($c && $c == $code){
            return true;
        }
        
        $this->log($uid,0,$mobile);
        $this->error = '-3';
        return false;
        

    }

    public function bind_phone($uid,$mobile,$code)
    {
        if(!$this->ckBind($uid,$mobile)){
            return false;
        }
        
        
        if(!$this->ckCode($uid,$mobile,$code)){
            return false;
        }
        
        return $this->_bind_phone($uid,$mobile);
    }
    
    public function unbind_phone($uid,$oldmobile)
    {
        $info = $this->get_bind_info($uid);

        if($info['mobile'] != $oldmobile){
            $this->error = -7;
            return false;
        }
        
        return $this->_unbind_phone($uid);
    }
 
    public function modBind($uid,$mobile,$code,$oldmobile){
        $info = $this->get_bind_info($uid);
        if($info['mobile'] != $oldmobile){
            $this->error = -7;
            return false;
        }
        
        return $this->bind_phone($uid,$mobile,$code);
    }
 

    private function _bind_phone($uid,$moblie){
        C::t('common_member_profile')->update($uid, array(
            'mobile'=>$moblie,$this->flagfield=>'1',   
        ));
        return true;
    }
      
    private function _unbind_phone($uid)
    {
   	    C::t('common_member_profile')->update($uid, array(
            'mobile'=>'',$this->flagfield=>'',
        ));
        return true;
    }
     
     
     public function sendCode($uid,$tel){
        
        if(!$this->isTel($tel)){
            return false;
        }
        
        if(!$this->ckBind($uid,$tel)){
            return false;
        }
        
        $code = rand(0,10000);
        $timestamp = time();
       
        $timelimt = $timestamp-3600*24;
        $cnt = DB::result_first("select count(*) from wj_phoneckcode where uid='".$uid."' and sendtime>$timelimt");
        if($cnt>3){
            // 超过次数限制
            $this->error=-5;
            return false;
        }
        
        $timelimt = time()-$this->interval;
        $cnt = DB::result_first("select count(*) from wj_phoneckcode where uid='".$uid."' and sendtime>$timelimt");
        if($cnt){
            $this->error = -9;
            return false;
        }      
      
        DB::query("insert into wj_phoneckcode (uid,code,tel,sendtime) values('".$uid."','$code','$tel','$timestamp')");
        return $code;
    }
 
     public function isTel($tel){
        if(preg_match("/^1\d{10}$/",$tel)){
            return true;
        }
        $this->error = '-4';
        return false;
    }
    	public function log($uid,$type,$tel=''){
		// $this->uid,$this->tel,$type
      
        if(!$tel){
            if($this->binddetial){
                $tel = $this->binddetial['tel'];
            }
        }
        
        DB::query("insert into wj_phonelog (uid,tel,`type`,dateline) values('".$this->uid."','$tel','$type','".time()."')");
        
	}   
}

?>