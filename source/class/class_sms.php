<?
class DCurl{
    public static function doPost($url,$vars=array()){


   	    $ch = curl_init ();
		$array [CURLOPT_URL] = $url;
		$array [CURLOPT_HEADER] = false;
        //$array [CURLOPT_USERAGENT]='Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0; BOIE9;ZHCN)';
        //$array [CURLOPT_HTTP_X_REQUESTED_WITH] = 'XMLHttpRequest';
		$array [CURLOPT_RETURNTRANSFER] = 1;
		$array [CURLOPT_FOLLOWLOCATION] = 0;
        //$array[CURLOPT_POSTFIELDS]='domain='.$dn.$ex;

/*
		$array [CURLOPT_HTTPHEADER] = array(
            //'X-Requested-With:XMLHttpRequest',
            'CLIENT-IP:61.49.23.20',
            
        );
*/
        
 
		if (!empty ( $vars )){
			$postfields = self::parseVar ( $vars ); //生成urlcode的url
			$array [CURLOPT_POST] = true;
			$array [CURLOPT_POSTFIELDS] = $postfields;
		}
		
        /*
        HTTP_X_REQUESTED_WITH
        
		//判断是否有cookie,有的话直接使用
		if ($_COOKIE ['cookie_jar'] || is_file ( $_COOKIE ['cookie_jar'] )) {
			$array [CURLOPT_COOKIEFILE] = $_COOKIE ['cookie_jar']; //这里判断cookie
		} else {
			$cookie_jar = tempnam ( '/tmp', 'cookie' ); //产生一个cookie文件
			$array [CURLOPT_COOKIEJAR] = $cookie_jar; //写入cookie信息
			setcookie ( 'cookie_jar', $cookie_jar ); //保存cookie路径
		}
        */
        
		curl_setopt_array ( $ch, $array ); //传入curl3
		$content = curl_exec ( $ch ); //执行
		curl_close ( $ch );//关闭
		return $content; //返回
    }
    

    
    public static function parseVar($var = array()){
        if(!$var){
            return '';
        }
        $postdata = '';
        foreach($var as $key=>$values){
            $postdata .= urlencode ( $key ) . "=" . urlencode ( $values ) . "&";
        }

        return $postdata;
    }
    
    
}


class sms{
    
    public static function verifiyMsg($phone,$message=null){
        
        $vars = array(
            'usermobile'=>$phone,
            'verificationcode'=>$message,
            'communityId'=>2,
        );
        
        if($message === null){
            $message = rand(1,9999);
        }
        $api = 'http://221.179.219.53/community/verification.do';
        $xml = DCurl::doPost($api,$vars);

        $result = ($xml->resCode[0]);
     
        if($result == 900){
            return true;
        }else{
            return false;
        }
    }
    
    public static function orderMessage($message,$phone){
            
    }
    
    public static function sendMsg($phone,$message){
        return DCurl::doPost('http://221.179.219.53/community/verification.do',array(
            'usermobile'=>$phone,
    		'verificationcode'=> iconv("GB2312","UTF-8",'望京网'),
             'verificationcode1'=>iconv("GB2312","UTF-8",$message),
    		'communityId'=>'13'
        ));
    }
    
}

?>

