/*
	[Discuz!] (C)2001-2009 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: common_xplus.js 22634 2011-09-16 05:52:08Z Niexinyuan $
*/

Array.prototype.indexOf = function(substr,start) {
	var ta,rt,d='\0';
	if(start!=null) {
		ta=this.slice(start); rt=start;
	} else {
		ta=this;rt=0;
	}
	var str=d+ta.join(d)+d,t=str.indexOf(d+substr+d);
	if(t==-1) return -1;
	rt+=str.slice(0,t).replace(/[^\0]/g,'').length;
	return rt;
}

Array.prototype.distinct = function() {
	var $ = this;
	var o1 = {}; //存放去重复值
	var o2 = {}; //存放重复值
	var o3 = []; //存放重复值

	var o; //数组单个变量
	for(var i=0;o = $[i];i++) {
		if(o in o1) {
			if(!(o in o2)) o2[o] = o;
			delete $[i];
		}else{
			o1[o] = o;
		}
	}

	$.length = 0; //清空原数组

	for(o in o1){
		$.push(o);
	}


	for(o in o2){
		o3.push(o);
	}

	return o3;
}

function checknum(box) {
	if(box.checked) {
		if(choice_num > 0 && (choice_arr.length + 1) > choice_num) {
			box.checked = false;
			showPrompt(null, null, "本投票最多只允许选择" + choice_num + "项！", 2000);
		} else {
			choice_arr.push(box.value);
		}
	} else {
		if(choice_arr.length > 0) {
			var indexofvalue = choice_arr.indexOf(box.value);
			if(indexofvalue != -1) {
				choice_arr.splice(indexofvalue, 1);
			}
		}
	}

	choice_arr.distinct();
	choice_arr.sort();
}

function clearpollchecked() {
	if(boxes) {
		for(var i=0; i<boxes.length; i++) {
			boxes[i].checked = false;
		}
		choice_arr = new Array();
	}
}

function ajaxsmallsubmit(choiceid) {
	choiceid = parseInt(choiceid);
	if(choiceid>0 && $('choose_value')) {
		$('choose_value').value = choiceid;
	}
}

function setmultiajaxtarget(id, ajaxtarget) {
	if($(id)) {
		var objs = $(id).getElementsByTagName('A');
		for(var i=0; i<objs.length; i++) {
			objs[i].setAttribute('ajaxtarget', ajaxtarget);
		}
	}
}

function succeedhandle_voteresult(url_forward, show_jsmessage, valuesjs) {
	var jumptime = 0;
	$('return_voteresult').innerHTML = '';
	$('return_voteresult').style.display = 'none';
	if(iniframe) {
		jumptime = 100;
		alert(show_jsmessage);
	} else {
		jumptime = 3500;
		showPrompt(null, null, show_jsmessage, 3000);
	}
	setTimeout(function() {
		if(with_img) {
			for(i in valuesjs) {
				if($('pollnum_' + valuesjs[i])) {
					$('pollnum_' +valuesjs[i]).innerHTML = parseInt($('pollnum_' + valuesjs[i]).innerHTML) + 1;
					if($('pollnum_zoom_' + valuesjs[i])) { $('pollnum_zoom_' +valuesjs[i]).innerHTML = $('pollnum_' + valuesjs[i]).innerHTML; }
				}
			}
		} else {
			url_forward = encodeURI(url_forward);
			location.href=url_forward;
		}
	}, jumptime);
}

function errorhandle_voteresult(show_jsmessage, valuesjs) {
	$('return_voteresult').innerHTML = '';
	$('return_voteresult').style.display = 'none';
	if(iniframe) {
		alert(show_jsmessage);
	} else {
		showPrompt(null, null, show_jsmessage, 2000);
	}
}

function succeedhandle_pollresult(url_forward, show_jsmessage, valuesjs) {
	var jumptime = 0;
	$('return_pollresult').innerHTML = '';
	$('return_pollresult').style.display = 'none';
	if(iniframe) {
		jumptime = 100;
		alert(show_jsmessage);
	} else {
		jumptime = 1000;
		showPrompt(null, null, show_jsmessage, 1500);
	}
	setTimeout(function() {
    	url_forward = encodeURI(url_forward);
    	location.href=url_forward;
	}, jumptime);
}

function errorhandle_pollresult(show_jsmessage, valuesjs) {
	$('return_pollresult').innerHTML = '';
	$('return_pollresult').style.display = 'none';
	if(iniframe) {
		alert(show_jsmessage);
	} else {
		showPrompt(null, null, show_jsmessage, 2000);
	}
}


function pollzoom(obj, fromobj) {
	var append_parent = 'voteform';
	zimg = !obj.getAttribute('bigimg') ? obj.src : obj.getAttribute('bigimg');
	if(!zoomstatus) {
		window.open(zimg, '', '');
		return;
	}
	if(!obj.id) obj.id = 'img_' + Math.random();
	var menuid = obj.id + '_zmenu';
	var menu = $(menuid);
	var imgid = menuid + '_img';
	var zoomid = menuid + '_zimg';
	var maxh = (document.documentElement.clientHeight ? document.documentElement.clientHeight : document.body.clientHeight) - 70;

	if(!menu) {
		menu = document.createElement('div');
		menu.id = menuid;
		var objpos = fetchOffset(obj);
		var obj_clientWidth = obj.clientWidth;
		var obj_clientHeight = obj.clientHeight;
		if(fromobj) {
			var frommenuobj = $(fromobj.id+ '_zmenu');
			if(frommenuobj && frommenuobj.style.display == '' ) {
				obj_clientWidth = frommenuobj.clientWidth;
				obj_clientHeight = frommenuobj.clientHeight;
				objpos = fetchOffset(frommenuobj);
			}
		}
		menu.innerHTML = '<div onclick="$(\'' + append_parent + '\').removeChild($(\'' + obj.id + '_zmenu\'))" style="z-index:600;filter:alpha(opacity=50);opacity:0.5;background:#FFF;position:absolute;width:' + obj_clientWidth + 'px;height:' + obj_clientHeight + 'px;left:' + objpos['left'] + 'px;top:' + objpos['top'] + 'px"><table width="100%" height="100%"><tr><td valign="middle" align="center"><img src="' + IMGDIR + '/loading.gif" /></td></tr></table></div>' +
			'<div style="position:absolute;top:-100000px;display:none"><img id="' + imgid + '" src="' + zimg + '"></div>';
		$(append_parent).appendChild(menu);
		$(imgid).onload = function() {
			$(imgid).parentNode.style.display = '';
			var imgw = $(imgid).width;
			var imgh = $(imgid).height;
			var r = imgw / imgh;
			var w = document.body.clientWidth * 0.95;
			w = imgw > w ? w : imgw;
			var h = w / r;
			if(h > maxh) {
				h = maxh;
				w = h * r;
			}
			var pollnum = 0;
			var choiceid = obj.getAttribute('choiceid');
			if(choiceid && $('pollnum_' + choiceid)) { pollnum = parseInt($('pollnum_' + choiceid).innerHTML); }
			var pollbuttonhtml = '<button type="submit" onclick="ajaxsmallsubmit(' + choiceid + ')">投票</button>';
			var althtml = '<strong>' + (obj.alt ? obj.alt : '') + '</strong><span><span id="pollnum_zoom_' + choiceid + '">' + pollnum + '</span> 票</span>';
			var prevhtml = '<li class="prev"><a href="javascript:;" title="上一项" ' + ($('option_image_' + obj.getAttribute('prevchoiceid')) ? 'onclick="pollzoomother(' + obj.getAttribute('prevchoiceid') + ', ' + obj.getAttribute('choiceid') + ')"' : 'class="disabled"') + ' >上一项</a></li>';
			var nexthtml = '<li class="next"><a href="javascript:;" title="下一项" ' + ($('option_image_' + obj.getAttribute('nextchoiceid')) ? 'onclick="pollzoomother(' + obj.getAttribute('nextchoiceid') + ', ' + obj.getAttribute('choiceid') + ')"' : 'class="disabled"') + ' >下一项</a></li>';
			var ctrlhtml = '<div class="zoomtool cl"><ul>' + prevhtml + '<li class="poll">' + pollbuttonhtml + '</li>' + nexthtml + '</ul></div>';

			$(append_parent).removeChild(menu);
			menu = document.createElement('div');
			menu.id = menuid;
			menu.style.overflow = 'visible';
			menu.style.width = (w < 300 ? 300 : w) + 20 + 'px';
			menu.style.height = h + 50 + 'px';
			menu.innerHTML = '<div class="zoominner">' + ctrlhtml + '<p class="cl" id="' + menuid + '_ctrl"><span class="y"><a href="' + zimg + '" class="imglink" target="_blank" title="在新窗口打开">在新窗口打开</a><a href="javascipt:;" id="' + menuid + '_adjust" class="imgadjust" title="实际大小">实际大小</a><a href="javascript:;" onclick="hideMenu()" class="imgclose" title="关闭">关闭</a></span>' + althtml + '</p><div align="center" onmousedown="zoomclick=1" onmousemove="zoomclick=2" onmouseup="if(zoomclick==1) hideMenu()"><img id="' + zoomid + '" src="' + zimg + '" width="' + w + '" height="' + h + '" w="' + imgw + '" h="' + imgh + '"></div></div>';
			$(append_parent).appendChild(menu);
			$(menuid + '_adjust').onclick = function(e) {adjust(e, 1)};
			if(BROWSER.ie){
				menu.onmousewheel = adjust;
			} else {
				menu.addEventListener('DOMMouseScroll', adjust, false);
			}
			showMenu({'menuid':menuid,'duration':3,'pos':'00','cover':1,'drag':menuid,'maxh':maxh+70,'zindex':5});
		};
	} else {
		showMenu({'menuid':menuid,'duration':3,'pos':'00','cover':1,'drag':menuid,'maxh':menu.clientHeight,'zindex':5});
	}
	if(BROWSER.ie) doane(event);
	var adjust = function(e, a) {
		var imgw = $(zoomid).getAttribute('w');
		var imgh = $(zoomid).getAttribute('h');
		var imgwstep = imgw / 10;
		var imghstep = imgh / 10;
		if(!a) {
			if(!e) e = window.event;
			if(e.altKey || e.shiftKey || e.ctrlKey) return;
			if(e.wheelDelta <= 0 || e.detail > 0) {
				if($(zoomid).width - imgwstep <= 200 || $(zoomid).height - imghstep <= 200) {
					doane(e);return;
				}
				$(zoomid).width -= imgwstep;
				$(zoomid).height -= imghstep;
			} else {
				if($(zoomid).width + imgwstep >= imgw) {
					doane(e);return;
				}
				$(zoomid).width += imgwstep;
				$(zoomid).height += imghstep;
			}
		} else {
			$(zoomid).width = imgw;
			$(zoomid).height = imgh;
		}
		menu.style.width = (parseInt($(zoomid).width < 300 ? 300 : parseInt($(zoomid).width)) + 20) + 'px';
		menu.style.height = (parseInt($(zoomid).height) + 50) + 'px';
		setMenuPosition('', menuid, '00');
		doane(e);
	};
}

function pollzoomother(choiceid, fromid) {
	if($('option_image_' + choiceid) && $('option_image_' + fromid)) {
		//hideMenu();
		pollzoom($('option_image_' + choiceid), $('option_image_' + fromid));
	}
}

function setiframeheight(classname) {
	var ifms = parent.document.getElementsByTagName('iframe');
	if(ifms) {
		for (var i=0; i<ifms.length; i++) {
			if(ifms[i].className == classname) {
				ifms[i].setAttribute('height', document.body.scrollHeight);
			}
		}
	}
}

/**
* 显示提示信息
* @param ctrlid		弹出提示信息的对象
* @param evt		点击弹出还是鼠标移上去弹出
* @param msg		提示信息内容
* @param timeout	提示信息持续时间
*/
function showPrompt(ctrlid, evt, msg, timeout) {
	var menuid = ctrlid ? ctrlid + '_pmenu' : 'ntcwin';
	var duration = timeout ? 0 : 3;
	if($(menuid)) {
		$(menuid).parentNode.removeChild($(menuid));
	}
	var div = document.createElement('div');
	div.id = menuid;
	div.className = ctrlid ? 'prmm up' : 'ntcwin';
	div.style.display = 'none';
	$('append_parent').appendChild(div);
	if(ctrlid) {
		msg = '<div id="' + ctrlid + '_prompt" class="prmc"><ul><li>' + msg + '</li></ul></div>';
	} else {
		msg = '<table cellspacing="0" cellpadding="0" class="popupcredit"><tr><td class="pc_l">&nbsp;</td><td class="pc_c"><div class="pc_inner wx">' + msg +
			'</td><td class="pc_r">&nbsp;</td></tr></table>';
	}
	div.innerHTML = msg;
	if(ctrlid) {
		if($(ctrlid).evt !== false) {
			var prompting = function() {
				showMenu({'mtype':'prompt','ctrlid':ctrlid,'evt':evt,'menuid':menuid,'pos':'210'});
			};
			if(evt == 'click') {
				$(ctrlid).onclick = prompting;
			} else {
				$(ctrlid).onmouseover = prompting;
			}
		}
		showMenu({'mtype':'prompt','ctrlid':ctrlid,'evt':evt,'menuid':menuid,'pos':'210','duration':duration,'timeout':timeout,'fade':1,'zindex':JSMENU['zIndex']['prompt']});
		$(ctrlid).unselectable = false;
	}else {
		showMenu({'mtype':'prompt','pos':'00','menuid':menuid,'duration':duration,'timeout':timeout,'fade':1,'zindex':JSMENU['zIndex']['prompt']});
	}
}

var CHECKALLFIELD = false;
function checkfield(identifier, required, checktype, checkmaxnum, checkminnum, checkmaxlength) {
	if(checktype != 'image' && !$('fieldvalue_' + identifier) || !$('checkresult_' + identifier)) {
		return true;
	}
	var ce = $('checkresult_' + identifier);
	var tobj = $('fieldtitle_' + identifier);
	ce.innerHTML = '';
	if(checktype == 'image') {

	} else {
		var checkvalue = $('fieldvalue_' + identifier).value;
	}
	if(required != '0') {
		if(checktype == 'radio' || checktype == 'checkbox') {
			if(checktype == 'radio') {
				var ces = document.getElementsByName("fieldvalue["+identifier+"]");
			} else if(checktype == 'checkbox') {
				var ces = document.getElementsByName("fieldvalue["+identifier+"][]");
			}
			for(var i=0; i<ces.length; i++) {
				if(ces[i].checked) {
					checkdone(ce);
					return true;
				}
			}
			warning(ce, '必填项', tobj);
			return false;
		} else if(checkvalue == '') {
			warning(ce, '必填项', tobj);
			return false;
		} else {
			checkdone(ce);
		}
	}

	if(checktype == 'radio' || checktype == 'checkbox') {
		return true;
	}

	if(checkvalue) {
		if((checktype == 'number' || checktype == 'range' || checktype == 'mobile') && isNaN(checkvalue)) {
			warning(ce, '必须为数字', tobj);
			return false;
		} else if(checktype == 'qq' && !(/^\d{5,12}$/.test(checkvalue))) {
			warning(ce, '必须为QQ数字帐号', tobj);
			return false;
		} else if(checktype == 'mobile' && !(/^((13|15|18)\d{9})$/.test(checkvalue))) {
			warning(ce, '必须为手机号码', tobj);
			return false;
		} else if(checktype == 'telephone' && !(/^(((0\d{2,3}-)?\d{7,8}(-\d{1,4})?))$/.test(checkvalue))) {
			warning(ce, '必须为固定电话格式', tobj);
			return false;
		} else if(checktype == 'email' && !(/^[\-\.\w]+@[\.\-\w]+(\.\w+)+$/.test(checkvalue))) {
			warning(ce, '必须为E-mail格式', tobj);
			return false;
		} else if((checktype == 'text' || checktype == 'textarea') && checkmaxlength != '0' && mb_strlen(checkvalue) > checkmaxlength) {
			warning(ce, '文字过长', tobj);
			return false;
		} else if((checktype == 'number' || checktype == 'range')) {
			if(checkmaxnum != '0' && parseInt(checkvalue) > parseInt(checkmaxnum)) {
				warning(ce, '数值超过允许的最大值', tobj);
				return false;
			} else if(checkminnum != '0' && parseInt(checkvalue) < parseInt(checkminnum)) {
				warning(ce, '数值小于允许的最小值', tobj);
				return false;
			} else {
				checkdone(ce);
			}
		} else {
			checkdone(ce);
		}
	}
	return true;
}

function checkdone(obj) {
	obj.style.display = '';
	obj.innerHTML = '<img src="' + IMGDIR + '/check_right.gif" width="16" height="16" class="vm" />';
}

function warning(obj, msg, titleobj) {
	obj.style.display = '';
	obj.innerHTML = '<img src="' + IMGDIR + '/check_error.gif" width="16" height="16" class="vm" /> ' + msg;
	obj.className = "warning";
	if(CHECKALLFIELD) {
		msg = titleobj.innerHTML + ' ' + msg;
		if(iniframe) {
			alert(msg);
		} else {
			showPrompt(null, null, msg, 1500);
		}
		return false;
	}
}

function valida_ajaxpost(obj) {
	var validate = validateextra(obj)
	if(validate) {
		ajaxpost('form', 'return_formresult');
	} else {
		return validate;
	}
}

function succeedhandle_formresult(url_forward, show_jsmessage, valuesjs) {
	var jumptime = 0;
	$('return_formresult').innerHTML = '';
	$('return_formresult').style.display = 'none';
	if(iniframe) {
		jumptime = 100;
		alert(show_jsmessage);
	} else {
		jumptime = 3500;
		showPrompt(null, null, show_jsmessage, 3000);
	}
	setTimeout(function() {
		url_forward = encodeURI(url_forward);
		location.href=url_forward;
	}, jumptime);
}

function errorhandle_formresult(show_jsmessage, valuesjs) {
	$('return_formresult').innerHTML = '';
	$('return_formresult').style.display = 'none';
	if(iniframe) {
		alert(show_jsmessage);
	} else {
		showPrompt(null, null, show_jsmessage, 1500);
	}
}
