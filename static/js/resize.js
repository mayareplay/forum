
(function(){
    var fn = function(){
        var w = document.documentElement ? document.documentElement.clientWidth : document.body.clientWidth
            ,r = 1255;
                
           	if($('css_widthauto')) {
                CSSLOADED['widthauto'] = 1;
	        }
            if(w>1255){
                /*
        		if(hasClass(HTMLNODE,'widthauto')){
   		    	  HTMLNODE.className = HTMLNODE.className.replace(' widthauto', '');
                }
                */
                if(!CSSLOADED['widthauto']) {
			         loadcss('widthauto');
                }
                $('css_widthauto').disabled = false;
            }else{
            
                if(!CSSLOADED['widthauto']) {
			         loadcss('widthauto');
                }
                /*
                if(!hasClass(HTMLNODE,'widthauto')){
              		    HTMLNODE.className += ' widthauto';
                }
                */
                $('css_widthauto').disabled = false;
            }
    }
    
    if(window.addEventListener){
        window.addEventListener('resize', function(){ fn(); });
    }else if(window.attachEvent){
        window.attachEvent('onresize', function(){ fn(); });
    }
    fn();
})();