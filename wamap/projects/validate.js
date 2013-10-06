//By Martin Honnen
//taken from http://www.faqts.com/knowledge_base/view.phtml/aid/1756/fid/129
function validateForm (form) {
  for (var e = 0; e < form.elements.length; e++) {
    var el = form.elements[e];
    if (el.className.indexOf('req')==-1){ continue;}
    if (typeof el.type == "undefined") {
	    continue;
    }
    if (el.type == 'text' || el.type == 'textarea' ||
        el.type == 'password' || el.type == 'file' ) { 
      if (el.value == '') {
        //alert('Please fill out all text field ' + el.name);
        alert('Please complete field for ' + el.title);
	el.focus();
        return false;
      }
    }
    else if (el.type.indexOf('select') != -1) {
      if (el.value=='') {
        //alert('Please select a value of the select field ' + el.name);
         alert('Please complete field for ' + el.title);
	el.focus();
        return false;
      }
    }
    else if (el.type == 'radio') {
      var group = form[el.name];
      var checked = false;
      if (!group.length)
        checked = el.checked;
      else
        for (var r = 0; r < group.length; r++)
          if ((checked = group[r].checked))
            break;
      if (!checked) {
        //alert('Please check one of the radio buttons ' + el.name);
        alert('Please complete field for ' + el.title);
	el.focus();
        return false;
      }
    }
    else if (el.type == 'checkbox') {
      var group = form[el.name];
      if (group.length) {
        var checked = false;
        for (var r = 0; r < group.length; r++)
          if ((checked = group[r].checked))
            break;
        if (!checked) {
          //alert('Please check one of the checkboxes ' + el.name);
           alert('Please complete field for ' + el.title);
	  el.focus();
          return false;
        }
      }
    }
  }
  return true;
}

var filecnt = 1;
function addnewfile(t) {
	var s = document.createElement("span");
	s.innerHTML = 'Description: <input type="text" size="40" name="newfiledesc-'+filecnt+'" /> File: <input type="file" name="newfile-'+filecnt+'" /> or Web link: <input type="text" name="newweblink-'+filecnt+'" /><br/>';
	t.parentNode.insertBefore(s,t);
	filecnt++;
}

function recordrating(n) {
	document.getElementById("rating").value = n;
	document.getElementById("current-rating").style.width = (n*20) + "%";
	return false;
}

function donesavingratings(noticetgt) {
	if (req.readyState == 4) { // only if req is "loaded" 
		if (req.status == 200) { // only if "OK" 
			 var resptxt = req.responseText;
			 document.getElementById("ratingholder").innerHTML = resptxt;
		} else { 
			if (noticetgt != null) {
			 document.getElementById(noticetgt).innerHTML = "Submission Error:\n"+ req.status + "\n" +req.statusText; 
			}
		}
	 } 
}
function saverating() {
	if (document.getElementById("rating").value==0) {alert("Please give a star rating"); return false;}
	backgroundpostsubmit(ratingssaveurl,document.getElementById("ratingentry"), function() {donesavingratings('ratingsavenotice');}, 'ratingsavenotice');
}


var req;
function backgroundpostsubmit(url,el,respfunc,noticetgt) {
	if (noticetgt != null && document.getElementById(noticetgt).innerHTML == "Submitting...") {return false;}
	if (window.XMLHttpRequest) { 
		req = new XMLHttpRequest(); 
	} else if (window.ActiveXObject) { 
		req = new ActiveXObject("Microsoft.XMLHTTP"); 
	} 
	if (el == null) {el = document;}
	if (req != undefined) { 
		var params = '';
		var els = new Array();
		var tags = el.getElementsByTagName("input");
		for (var i=0;i<tags.length;i++) {
			els.push(tags[i]);
		}
		var tags = el.getElementsByTagName("select");
		for (var i=0;i<tags.length;i++) {
			els.push(tags[i]);
		}
		var tags = el.getElementsByTagName("textarea");
		for (var i=0;i<tags.length;i++) {
			els.push(tags[i]);
		}
		for (var i=0;i<els.length;i++) {
			if ((els[i].type!='radio' && els[i].type!='checkbox') || els[i].checked) {
				params += ('&'+els[i].name+'='+encodeURIComponent(els[i].value));
			}
		}
		
		if (noticetgt != null) {
			document.getElementById(noticetgt).innerHTML = "Submitting...";
		}
		req.open("POST", url, true);
		req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		req.setRequestHeader("Content-length", params.length);
		req.setRequestHeader("Connection", "close");
		req.onreadystatechange = respfunc; 
		req.send(params);  
	} else {
		if (noticetgt != null) {
			document.getElementById(noticetgt).innerHTML = "Error Submitting.";
		}
	}
}  

function commentshowhide(o,n) {
	var el = document.getElementById("hiddencomment"+n);
	if (el.style.display=="none") {
		el.style.display = "inline";
		o.innerHTML = "[less...]";
	} else {
		el.style.display = "none";
		o.innerHTML = "[more...]";
	}
}
	
