var mobile = null;
var send = 0;
var timer = null;
var m = 120;

if(typeof ajaxget!=="function"){
	
	ajaxget = function (url,elemid,elemid,_target,o,callBack){
		jQuery.get(url,function(d){
			var txt = $(d).find('root').text();
			jQuery('#'+elemid).html(jQuery.trim(txt));
			if(callBack && typeof callBack == 'function') {
				callBack();
			}else if(callBack) {
				eval(callBack);
			}

		});
	}
}

function sendCode(){
    
    if(mobile == null){
        var mobile = document.getElementById("mobile");
    }
    
    var tel = mobile.value;
    if(!checkTel(tel)){
        alert('����ȷ�����ֻ���');
        mobile.focus();
        return false;
    }
    
    //�жϵ绰�Ƿ�Ψһ
    ajaxget('home.php?mod=spacecp&ac=profile&op=phone&d=sendcode&mobile='+tel, 'ckmessage','ckmessage','black','',function(){
    var res = document.getElementById('ckmessage').innerHTML;
        if(res==''){
            runTimer();
        }
    });
    return false;
}


                        
                        
function getVcode(){
     
    if(send == 1){
       alert(send);
       return false;
    }
        var tel = document.getElementById('tel_1').value.replace(/(^\s*)|(\s*$)/g, "");
    
    document.getElementById('bindmessage').innerHTML='';
    if(!checkTel(tel)){
        alert('����ȷ�����ֻ���');
        return false;
    }
    
    //�жϵ绰�Ƿ�Ψһ
    ajaxget('active.php?action=ckphone&tel='+tel, 'ckmessage','ckmessage','black');
}

function runTimer(){
 
    send =1;
    timer = setInterval('setTimer()',1000);
}

function setTimer(){
     m=m-1;
     if(m<=0){
        clearInterval(timer);
        timer = '';
        resetBtn();
        return ;
     }
    
     document.getElementById('registerformsubmit').innerHTML='����'+m+'������д��֤��';

    
}


function resetBtn(){
     send = 0;
   
     document.getElementById('registerformsubmit').innerHTML='<a class="pn pnc sendtextcolor"  href="#" onclick="return sendCode()">���»�ȡ��֤��</a>';
  
     document.getElementById('ckmessage').innerHTML='';
}

function bindTel(){
    var mobile =  document.getElementById('mobile').value;
    var vcode = document.getElementById('vcode_1').value.replace(/(^\s*)|(\s*$)/g, "");
    if(vcode == ''){
    alert('����д������');
        document.getElementById('vcode_1').focus();
        return ;
    }
    ajaxget('active.php?action=ckcode&vcode='+vcode+'&mobile='+mobile, 'bindmessage','bindmessage','black');
}

function checkTel(tel){
    var pat = /^1\d{10}$/;
    if(tel == ''){
        return false;
    }
    if(pat.test(tel)){
        return true;
    }else{
        return false;
    }
}
