<?php
require './source/class/class_core.php';
C::app()->init();
//error_reporting(ALL);

// �����Ƽ��������
if($_GET['action'] == 'updatecredit'){
    // ���ó����������ݿ�
    DB::$db->connect(100);

    $posstr = dimplode(array(1,2,6,7,8,9));
    $query = DB::query("select q.id,q.authorid,q.url from wj_quotes q where posid in($posstr) and q.flag=0 and q.authorid>0 and q.dateline>1327896000");
    
    $members = array();
    $updateids = $allurl = array();
    while($row = DB::fetch($query)){
        //$username =trim($row['username']);
        
        $members[$row['authorid']]++;
        $updateids[] = $row['id'];
        $allurl[$row['authorid']][]=$row['url'];
    }
    


    
    if($updateids && $posstr){
        $idstr = dimplode($updateids);
        $sql = "update wj_quotes set flag=1 where id in($idstr)";
        DB::query($sql);
    }else{
        echo "none";
    }
    /*
    echo "<pre>";
    print_r($members);
    print_r($updateids);
    print_r($allurl);
    */
  
    // ���ó�������̳���ݿ�����
    DB::$db->connect(1);
    if($updateids && $members){
        foreach($members as $uid=>$val){
			$uid = intval($uid);
			$val = intval($val);
			if($uid>0 && $val>0){
                // ��������
                $usecredit = array();
                $usecredit[2] = '+'.$val;
                //print_r($usecredit);
                updatemembercount($uid, $usecredit, true, 'ACC', 0);
				$urls = $allurl[$uid];
				$urlstr  = "";
				for($i=0;$i<count($urls);$i++){
					$urlstr .=	"<br>".'<a href="'.$urls[$i].'">'.$urls[$i].'</a>';
                }
				$message = "���������������ݱ���վ�Ƽ�: ".$urlstr."\n ��л���ľ��ʷ��������� $val ������ ";
				helper_notification::notification_add($uid,'system',$message);
			}
        }
    }

}else if($_GET['action'] == 'auto'){
    $token = $_GET['token'];
    
    if($_G['groupid']==1){
        
        ?>
        <html>
        <meta http-equiv="Content-Type" content="text/html; charset=gbk" />
        <script src="static/js/mobile/jquery-1.8.3.min.js"></script>
        <body>
        <script>
        $(function(){
            $('#submit').click(function(){
                if(!$('#tid').val() || isNaN($('#tid').val())){
                    alert('����ȷ����tid');
                     $('#tid').val('');
                    $('#tid').focus();
                    return false;
                }
                var tid = $('#tid').val();
                
                if(!$('#message').val()){
                    alert('������ظ�����');
                    $('#message').focus();
                    return false;
                }
                
                var msg = $('#message').val();
                var rows = msg.split('\n');
                $('#cnt').html('(��: '+rows.length+'�� ���ڴ����<span id="cu"></span>)');
                
                for(var i=0;i<rows.length;i++){
                    var row = $.trim(rows[i]);
                    if(row){
                        
                        var _row = row.split('@');
                        if(_row.length=2){
                            $('#cu').html((i+1)+'��');
                            var name = _row[0];
                            var msg = _row[1];
                            
                            $('#result').html('���ڻظ�:'+name+":"+"msg");
                            $.post('tools.php?action=dopost',{name:name,msg:msg,tid:tid},function(html){
                               $('#result').html('');
                            });
                        }
                    }
                }
                
            })
        });
        </script>
            
            Tid:<input type="text" id="tid" name="tid" value="" /><br />
      
            (�û���@����) һ��һ��<br/>
            <textarea name="message" id="message" style="width: 500px;height: 300px;"></textarea>
            <br />
            <input  type="button" id="submit" name="submit" value="��ʼ�ظ�"/><span id="cnt"></span> <span id="result"></span>
     
        </body>
        </html>
        <?
        /*
        $p = intval($_GET['p']);
        $P = max(1,$p);
        
        $tid= 1548078;
        $message= array('�ȸ��й�1','΢�ſͻ���2','С���ֻ�3');
        
        if(!$msg = $message[$p]){
            echo "over";
            exit;
        }
        
        $msg = $message[$p];
        autoPost($tid,$msg,'replay');
        header('location: tools.php?action=auto&p='.($p+1));
        exit;
        */
    }
}else if($_GET['action'] == 'dopost'){
    if($_G['groupid']!=1){
        exit;
    }
    $username = mb_convert_encoding( $_GET['name'],'GB2312','UTF-8');
    $msg = addslashes(mb_convert_encoding( $_GET['msg'],'GB2312','UTF-8'));
    $tid = intval($_GET['tid']);
    if($username && $msg && $tid){
        autoPost($tid,$msg,$username);
    }
    
}

function autoPost($tid,$_message,$username){
       	$params = array(
    		'subject' => '',
    		'message' => $_message,
    		'special' => 0,
    		'extramessage' => '',
    		'bbcodeoff' => 1,
    		'smileyoff' => 1,
    		'htmlon' => 0,
    		'parseurloff' => 1,
    		'usesig' => 0,
    		'isanonymous' => 0,
    		'noticetrimstr' => '',
    		'noticeauthor' => '',
    		'from' => '',
    		'sechash' => $_GET['sechash'],
    		'geoloc' => diconv($_GET['geoloc'], 'UTF-8'),
    	);
        $username = addslashes($username);
        if(!$username){
            return;
        }
        $modpost = C::m('forum_post', $tid);
        $modpost->member = DB::fetch_first("select * from ".DB::table('common_member')." where username='$username'");
        $return = $modpost->newreply($params);

}


?>