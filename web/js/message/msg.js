//IMathAS (c) 2007 David Lippman
//Flag toggles and pic rotation for message list
var imasroot = jQuery('.home-path').val();
function togglecolor(threadid,tagged) {
	var trchg = document.getElementById("tr"+threadid);
	var imgchg = document.getElementById("tag"+threadid);
	if (tagged==1) {
		trchg.className = "tagged";
		imgchg.src = imasroot+"img/flagfilled.gif";
	} else {
		trchg.className = "";
		imgchg.src = imasroot+"img/flagempty.gif";
	}
}

function toggletagged(threadid) {
	var trchg = document.getElementById("tr"+threadid);
	if (trchg.className.includes("tagged")) {
		submitTagged(threadid,0);
	} else {
		submitTagged(threadid,1);
	}
	return false;
}

function submitTagged(thread,tagged) { 
  url = AHAHsaveurl + '&threadid='+thread+'&tagged='+tagged;
  if (window.XMLHttpRequest) {
    req = new XMLHttpRequest(); 
  } else if (window.ActiveXObject) { 
    req = new ActiveXObject("Microsoft.XMLHTTP"); 
  } 
  if (typeof req != 'undefined') { 
    req.onreadystatechange = function() {ahahDone(url, thread, tagged);}; 
    req.open("GET", url, true); 
    req.send(""); 
  } 
}  

function ahahDone(url, threadid, tagged) {
    if (req.readyState == 4) { // only if req is "loaded"
    if (req.status == 200) { // only if "OK"
	    if ($.trim(req.responseText=='OK')) {
            togglecolor(threadid, tagged);
	    } else {
		    alert("Oops, error toggling the tag");
	    }
    } else { 
	   alert(" Couldn't save changes:\n"+ req.status + "\n" +req.statusText); 
    } 
  } 
}


var picsize = 0;
function rotatepics() {
	picsize = (picsize+1)%3;
	picshow(picsize);
}
function picshow(size) {
	if (size==0) {
		els = document.getElementById("myTable").getElementsByTagName("img");
		for (var i=0; i<els.length; i++) {
			els[i].style.display = "none";
		}
	} else {
		els = document.getElementById("myTable").getElementsByTagName("img");
		for (var i=0; i<els.length; i++) {
			els[i].style.display = "inline";
			if (els[i].getAttribute("src").match("userimg_sm")) {
				if (size==2) {
					els[i].setAttribute("src",els[i].getAttribute("src").replace("_sm","_"));
				}
			} else if (size==1) {
				els[i].setAttribute("src",els[i].getAttribute("src").replace("_","_sm"));
			}
		}
	}
}
