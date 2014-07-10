/*
 	[Discuz!] (C)2001-2009 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: xplus_admincr.js 22639 2011-11-03 07:05:16Z Niexinyuan $
*/

function addpolloptions(id) {
    var optionlist = $(id);
    
    var attr = [];
    attr['name'] = 'options';
    attr['id'] = 'option';
    attr['classname'] = 'px';
    attr['size'] = 30;
    
    addoptions(optionlist, attr);
}

function addvoteoptions(id) {
    var optionlist = $(id);
    
    var attr = [];
    attr['name'] = 'choices';
    attr['id'] = 'choice';
    attr['classname'] = 'px';
    attr['size'] = 30;
    
    addoptions(optionlist, attr);
}

function addoptions(optionlist, attr) {
    
    i = optionlist.children.length + 1;
    
    var newoption = document.createElement('input');
    newoption.type = 'text';
    newoption.name = isUndefined(attr['name']) ? 'option[]' : attr['name']+'[]';
    newoption.id = isUndefined(attr['id']) ? 'option_'+i : attr['id']+'_'+i;;
    newoption.className = isUndefined(attr['classname']) ? 'px' : attr['classname'];
    newoption.size = isUndefined(attr['size']) ? '30' : attr['size'];
    
    var s = document.createElement('li');
    s.innerHTML = i+'.';
    s.appendChild(newoption);
    optionlist.appendChild(s);
}

function autofixtitle(fixid) {
    $('labeltitle').value = profile[fixid.value];
}

var addrowdirect = 0;
function addrow(obj, type) {
	var table = obj.parentNode.parentNode.parentNode.parentNode.parentNode;
	if(!addrowdirect) {
		var row = table.insertRow(obj.parentNode.parentNode.parentNode.rowIndex);
	} else {
		var row = table.insertRow(obj.parentNode.parentNode.parentNode.rowIndex + 1);
	}
	var typedata = rowtypedata[type];
	for(var i = 0; i <= typedata.length - 1; i++) {
		var cell = row.insertCell(i);
		cell.colSpan = typedata[i][0];
		var tmp = typedata[i][1];
		if(typedata[i][2]) {
			cell.className = typedata[i][2];
		}
		tmp = tmp.replace(/\{(\d+)\}/g, function($1, $2) {return addrow.arguments[parseInt($2) + 1];});
		cell.innerHTML = tmp;
	}
	addrowdirect = 0;
}

function dropmenu(obj){
	showMenu({'ctrlid':obj.id, 'menuid':obj.id + 'child', 'evt':'mouseover'});
	$(obj.id + 'child').style.top = (parseInt($(obj.id + 'child').style.top) - Math.max(document.body.scrollTop, document.documentElement.scrollTop)) + 'px';
	if(BROWSER.ie > 6 || !BROWSER.ie) {
		$(obj.id + 'child').style.left = (parseInt($(obj.id + 'child').style.left) - Math.max(document.body.scrollLeft, document.documentElement.scrollLeft)) + 'px';
	}
}

function changeselect(select) {
    var styles, key;
    styles = new Array('number', 'qq', 'mobile', 'telephone', 'text', 'custom', 'radio', 'checkbox', 'textarea', 'select', 'calendar', 'image', 'range'); 
    for(key in styles) {
        var obj=$('style_'+styles[key]); 
        obj.style.display=styles[key]==select.options[select.selectedIndex].value?'':'none';
    }
} 