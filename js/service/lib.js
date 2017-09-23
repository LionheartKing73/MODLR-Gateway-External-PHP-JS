

var keys = {};
var has_used_speech_recog = false;

 var entityMap = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': '&quot;',
    "'": '&#39;',
    "/": '&#x2F;'
  };

function escapeHtml(string) {
    return String(string).replace(/[&<>"'\/]/g, function (s) {
		return entityMap[s];
    });
}

function goToScreen(title) {
	var serverid =  getParameterByName("serverid");
	var activityid =  getParameterByName("activityid");
	var moddelid =  getParameterByName("id");

	var url = "/activities/view/?id="+moddelid+"&activityid="+activityid+"&serverid="+serverid+"&title="+title;
	if( window.parent ) {
		window.parent.location = url;
	} else {
		window.location = url;
	}
}

$(document).keydown(function (e) {
    keys[e.which] = true;
});

$(document).keyup(function (e) {
    delete keys[e.which];
});

$(document).ready(function() {
	//authenticate the user within the current page
	$("#dialog-loading").dialog({
		autoOpen: false
	});
});

function listValue(list) {
	if( list.selectedIndex > -1 ) {
		return list.options[list.selectedIndex].value;
	}
	return "";
}

// proper case string prptotype (JScript 5.5+)
String.prototype.toProperCase = function()
{
  return this.toLowerCase().replace(/^(.)|\s(.)/g, 
      function($1) { return $1.toUpperCase(); });
}

var bLoading = false;

function cleanStr(str) {
	return str.toLowerCase().replace(/ /gi, "").replace(/\t/gi, "").replace(/\./gi, "").replace(/\,/gi, "").replace(/\-/gi, "").replace(/\r/gi, "").replace(/\n/gi, "").replace(/\:/gi, "").toLowerCase();
}
	
function isTouchDevice() {
	
	var bool;
    if(('ontouchstart' in window) || window.DocumentTouch && document instanceof DocumentTouch) {
      bool = true;
    } else {
      return false;
    }
    return bool;
}


function showLoadingReally(message) {
	if( !bLoading ) 
		return;
	
	
	if( !message ) 
		message = "Communicating with the analytics server.";
	
	if( document.getElementById('txtLoading') ) {
		document.getElementById('txtLoading').innerHTML = message;
	}
	
	
	$( "#dialog-loading" ).dialog({
		open: function(event, ui) { 
				document.getElementById('dialog-loading').parentNode.childNodes[0].childNodes[1].style.display = 'none';
			},
		autoOpen: true,
		modal: true,
		buttons: {},
		zindex: 100001
	});
}



//show loading dialog if we are still loading after half a second
function showLoading(message) {
	
		

	if( !bLoading ) {
		
		bLoading = true;
		setTimeout(function(){ showLoadingReally(message); }, 500);
	}
}

function showAsk() {
	$("#divAskResults").html("");
	$("#divAskResults").css("margin-top","0px");
	
	$("#dialog-ask-modlr").dialog({
		autoOpen: false
	});
	
	if( !bLoading ) {
		$( "#dialog-ask-modlr" ).dialog({
			autoOpen: true,
			modal: true,
			buttons: {},
			width: 537,
			height: 88,
			zindex: 100001
		});
	}
}

function createWorkflowPrompt() {
	
	$( "#dialog-create-workflow" ).dialog({
		autoOpen: true,
		modal: true,
		buttons: {},
		width: 537,
		height: 88,
		zindex: 100001
	});
}

function workflowCreate() {
	
	var name = $("#workflowName").val();
	
	window.location = "/workflow/?action=create&id=" + model_detail.id + "&name="+name;
	
}

function askModlr() {
	var statement = document.getElementById("ask").value;
	var tasks = {"tasks": [
		{"task": "language.query", "statement": statement }
	]};
	query("collaborator.service",tasks,askModlrCallback);
}
function askModlrCallback(data) {
	var results = JSON.parse(data);
	var result = results['results'][0]['result'];
	var intersection = results['results'][0]['intersection'];
	
	$( "#dialog-ask-modlr" ).dialog({
		autoOpen: true,
		modal: true,
		buttons: {},
		width: 535,
		height: 260,
		zindex: 100001
	});
	$("#divAskResults").css("margin-top","10px");
	$("#divAskResults").css("font-size","14px");
	
	if( result == 1 ) {
		var html = "";
		
		var value = "";
		if( intersection.type == "number" ) {
			value = intersection.number;
		} else if( intersection.type == "consolidation" ) {
			value = intersection.number;
		} else {
			value = intersection.string;
		}		
		
		var elements = intersection.elements;
		var cube = intersection.cube;
		var model = intersection.model;
		
		
		
		
		html = "<div class='askHeadline'>" + value + " " + elements[elements.length-1] + "</div>Model: \"<b>" + model + "</b>\"<br/>Cube: \"<b>" + cube + "</b>\"<br/>Reference: [";
		
		for(var i=0;i<elements.length;i++) {
			if( i != 0 )
				html += ", ";
			html += elements[i];
		}
		
		html += "]";
		
		html = "<center>" + html + "</center>";
		$("#divAskResults").html(html);
		
		
		if( has_used_speech_recog ) {
			speak(value + " " + elements[elements.length-1]);
		}
		
	} else {
		var html = "";
		
		if( intersection != null ) {
			var message = results['results'][0]['message'];
		
			var elements = intersection.elements;
			var dimensions = intersection.dimensions;
			var cube = intersection.cube;
			var model = intersection.model;
			
			html = "<div class='askHeadline'>" + message + "</div>Model: \"<b>" + model + "</b>\"<br/>Cube: \"<b>" + cube + "</b>\"<br/>Reference: [";
			
			for(var i=0;i<elements.length;i++) {
				if( i != 0 )
					html += ", ";
				if( elements[i] == null )
					elements[i] = "Unknown";
				html += elements[i];
			}
			
			html += "]";
			
			html = "<center>" + html + "</center>";
			
			if( has_used_speech_recog ) {
				speak(message);
			}
			
			$("#divAskResults").html(html);
		} else {
		
			var message = results['results'][0]['message'];
			
			
			if( has_used_speech_recog ) {
				speak(message);
			}
			
			$("#divAskResults").html("<div class='askHeadline'>" + message + "</div>");
		}
		
	}
}

function hideLoading() {
	if( bLoading ) {
		if( $( "#dialog-loading" ).dialog( "isOpen" ) == true ) {
			$( "#dialog-loading" ).dialog( "close" );
		}
	}
	bLoading = false;
	
}



function checkSession() {
	var tasks = {"tasks": [
        {"task": "session.check" },
    ]};
    query("server.service","json",tasks,checkSession_callback);
}
function checkSession_callback(data) {
	if( parseInt(data['results'][0]['result']) != 1 ) {
		window.location = '/login.html';
	}
}


function createLoginTask(username, password, callback_func) {
	var tasks = {"tasks": [
        {"task": "login", "username": username, "password": password},
        {"task": "config", "key": "name"}
    ]};
    query("server.service","json",tasks,callback_func);
}

function addTask(tasks, task, parameters) {
	var task = { 
		 "function"    :   "something", 
		 "username"    :   "Springfield", 
		 "password" 	:   ""
	};
}

function queryObj(serviceName,data,callback_func) { 
	query(serviceName,data,function(data) {
		var results = JSON.parse(data);
		callback_func(results);
	});
}

function queryDownload(serviceName,data,callback_func) {
	$.ajax({
    type: "POST",
	dataType: "text",
	contentType: "application/text; charset=utf-8",
	processData: false,
	url: "/api/?service=" + serviceName + "&server_id=" + server_id,
	data: JSON.stringify( data ),
    success: function(response, status, xhr) {
        // check for a filename
        var filename = "";
        var disposition = xhr.getResponseHeader('Content-Disposition');
        if (disposition && disposition.indexOf('attachment') !== -1) {
            var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
            var matches = filenameRegex.exec(disposition);
            if (matches != null && matches[1]) filename = matches[1].replace(/['"]/g, '');
        }

        var type = xhr.getResponseHeader('Content-Type');
        var blob = new Blob([response], { type: type });

        if (typeof window.navigator.msSaveBlob !== 'undefined') {
            // IE workaround for "HTML7007: One or more blob URLs were revoked by closing the blob for which they were created. These URLs will no longer resolve as the data backing the URL has been freed."
            window.navigator.msSaveBlob(blob, filename);
        } else {
            var URL = window.URL || window.webkitURL;
            var downloadUrl = URL.createObjectURL(blob);

            if (filename) {
                // use HTML5 a[download] attribute to specify filename
                var a = document.createElement("a");
                // safari doesn't support this yet
                if (typeof a.download === 'undefined') {
                    window.location = downloadUrl;
                } else {
                    a.href = downloadUrl;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                }
            } else {
                window.location = downloadUrl;
            }

            setTimeout(function () { URL.revokeObjectURL(downloadUrl); }, 100); // cleanup
        }
    }
});
}

function query(serviceName,data,callback_func) {
	showLoading();
	
	$.ajax({
		type: "POST",
		dataType: "text",
        contentType: "application/text; charset=utf-8",
		processData: false,
		url: "/api/?service=" + serviceName + "&server_id=" + server_id,
		data: JSON.stringify( data )
	}).done(function() {
	
	}).fail(function() {
		
		
	}).always(function(result) {
		hideLoading();
		if( callback_func != null ) {
			callback_func(result);
		}
	});
}

function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

Array.prototype.remove = function(value) {
  var idx = this.indexOf(value);
  if (idx != -1) {
      return this.splice(idx, 1); // The second parameter is the number of elements to remove.
  }
  return false;
}


function firstChildNodeNamed(name, node) {
    for (var i = 0; i < node.childNodes.length; i++) {
        if (node.childNodes[i].nodeName == name)
            return node.childNodes[i];
    }
    return null;
}

function isNumber(n) {
    return !isNaN(parseFloat(n)) && isFinite(n);
}

function nodeValue(node) {
    var str = node.nodeValue;
    if (str == null)
        if (node.childNodes.length > 0)
            str = node.childNodes[0].nodeValue;
            
    return str;
}


function generalNumberFormat(num, fmt) {
    var rValue = num;
	var isDollars = false;
	
	if( fmt.substring(0,1) == "$") {
		fmt = fmt.substring(1,fmt.length-1);
		isDollars = true;
	}
    
    if (fmt.indexOf(';') > 0) {
        fmt = fmt.split(';')[0];
    }

    if (fmt == "#,##0.00") {
        rValue = formatNumber(num, 2);
    } else if (fmt == "#,##0.0") {
        rValue = formatNumber(num, 1);
    } else if (fmt == "#,##0") {
        rValue = formatNumber(num, 0);
    } else if (fmt == "0.000%") {
        rValue = formatNumber(parseFloat(num) * 100, 3) + "%";
		if( parseFloat(num) < 0 )
			rValue = rValue.replace(/\)/gi,"") + ")";
    } else if (fmt == "0.00%") {
        rValue = formatNumber(parseFloat(num) * 100, 2) + "%";
		if( parseFloat(num) < 0 )
			rValue = rValue.replace(/\)/gi,"") + ")";
    } else if (fmt == "0.0%") {
        rValue = formatNumber(parseFloat(num) * 100, 1) + "%";
		if( parseFloat(num) < 0 )
			rValue = rValue.replace(/\)/gi,"") + ")";
    } else if (fmt == "0%") {
        rValue = formatNumber(parseFloat(num) * 100, 0) + "%";
		if( parseFloat(num) < 0 )
			rValue = rValue.replace(/\)/gi,"") + ")";
    } else {
        rValue = formatNumber(num, 0); 
    }
    /*
    if( num < 0 ) {
    	rValue = "(" + rValue + ")";
    }
*/
    if (rValue.length > 0)
        if (rValue.substr(rValue.length - 1, 1) == ".")
            rValue = rValue.substr(0, rValue.length - 1);

	if( isDollars ) {
		rValue = "$" + rValue;
	}
		
    if (num == 0)
        rValue = "-";

	
	
    return rValue;
}

function getDataset(obj, dataTag) {
    if (obj) {
        if (obj.dataset) {
            return obj.dataset[dataTag];
        } else {
            return obj.getAttribute("data-" + dataTag);
        }
    }
}

function setDataset(obj, dataTag, value) {
    if (obj) {
        if (obj.dataset) {
            obj.dataset[dataTag] = value;
        } else {
            obj.setAttribute("data-" + dataTag, value);
        }
    }
}


function IsNumeric(val) {
    if (isNaN(parseFloat(val))) {
          return false;
     }
     return true
}




function copy(inElement) {
  if (inElement.createTextRange) {
    var range = inElement.createTextRange();
    if (range && BodyLoaded==1)
      range.execCommand('Copy');
  } else {
    var flashcopier = 'flashcopier';
    if(!document.getElementById(flashcopier)) {
      var divholder = document.createElement('div');
      divholder.id = flashcopier;
      document.body.appendChild(divholder);
    }
    document.getElementById(flashcopier).innerHTML = '';
    var divinfo = '<embed src="/js/_clipboard.swf" FlashVars="clipboard='+encodeURIComponent(inElement.value)+'" width="0" height="0" type="application/x-shockwave-flash"></embed>';
    document.getElementById(flashcopier).innerHTML = divinfo;
  }
}

function insertAtCaret(areaId,text) {
    var txtarea = document.getElementById(areaId);
    var scrollPos = txtarea.scrollTop;
    var strPos = 0;
    var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ? 
        "ff" : (document.selection ? "ie" : false ) );
    if (br == "ie") { 
        txtarea.focus();
        var range = document.selection.createRange();
        range.moveStart ('character', -txtarea.value.length);
        strPos = range.text.length;
    }
    else if (br == "ff") strPos = txtarea.selectionStart;

    var front = (txtarea.value).substring(0,strPos);  
    var back = (txtarea.value).substring(strPos,txtarea.value.length); 
    txtarea.value=front+text+back;
    strPos = strPos + text.length;
    if (br == "ie") { 
        txtarea.focus();
        var range = document.selection.createRange();
        range.moveStart ('character', -txtarea.value.length);
        range.moveStart ('character', strPos);
        range.moveEnd ('character', 0);
        range.select();
    }
    else if (br == "ff") {
        txtarea.selectionStart = strPos;
        txtarea.selectionEnd = strPos;
        txtarea.focus();
    }
    txtarea.scrollTop = scrollPos;
}

function insertTextAtCursor(text) {
    var sel, range, html;
    if (window.getSelection) {
        sel = window.getSelection();
        if (sel.getRangeAt && sel.rangeCount) {
            range = sel.getRangeAt(0);
            range.deleteContents();
            var node = document.createTextNode(text);
            range.insertNode( node );
            
            
            range.setStartAfter(node);
            range.collapse(true);
            sel.removeAllRanges();
            sel.addRange(range);
            
            
        }
    } else if (document.selection && document.selection.createRange) {
        document.selection.createRange().text = text;
    }
}

String.prototype.replaceHtmlEntites = function() {
	var s = this;
	var translate_re = /&(nbsp|amp|quot|lt|gt);/g;
	var translate = {"nbsp": " ","amp" : "&","quot": "\"","lt"  : "<","gt"  : ">"};
	return ( s.replace(translate_re, function(match, entity) {
	  return translate[entity];
	}) );
};

function saveSelection() {
    if (window.getSelection) {
        sel = window.getSelection();
        if (sel.getRangeAt && sel.rangeCount) {
            return sel.getRangeAt(0);
        }
    } else if (document.selection && document.selection.createRange) {
        return document.selection.createRange();
    }
    return null;
}

function restoreSelection(range) {
    if (range) {
        if (window.getSelection) {
            sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
        } else if (document.selection && range.select) {
            range.select();
        }
    }
}

function insertAtCursor(myField, myValue) {
	//IE support
	if (document.selection) {
		myField.focus();
		sel = document.selection.createRange();
		sel.text = myValue;
	}
	//MOZILLA/NETSCAPE support
	else if (myField.selectionStart || myField.selectionStart == '0') {
		var startPos = myField.selectionStart;
		var endPos = myField.selectionEnd;
		myField.value = myField.value.substring(0, startPos)
		+ myValue
		+ myField.value.substring(endPos, myField.value.length);
	} else {
		myField.value += myValue;
	}
}



function isiOS() {
 	var isiOS = false;
    var agent = navigator.userAgent.toLowerCase();
    if(agent.indexOf('iphone') >= 0 || agent.indexOf('ipad') >= 0){
           return true;
    }
    return false;
}


(function($){
	
    // Determine if we on iPhone or iPad
    var isiOS = false;
    var agent = navigator.userAgent.toLowerCase();
    if(agent.indexOf('iphone') >= 0 || agent.indexOf('ipad') >= 0){
           isiOS = true;
    }
	if( isiOS ) {
		$.fn.doubletap = function(onDoubleTapCallback, onTapCallback, delay){
			var eventName, action;
			delay = delay == null? 500 : delay;
			eventName = isiOS == true? 'touchend' : 'click';

			$(this).bind(eventName, function(event){
				var now = new Date().getTime();
				var lastTouch = $(this).data('lastTouch') || now + 1 /** the first time this will make delta a negative number */;
				var delta = now - lastTouch;
				clearTimeout(action);
				if(delta<500 && delta>0){
					if(onDoubleTapCallback != null && typeof onDoubleTapCallback == 'function'){
						onDoubleTapCallback(event);
					}
				}else{
					$(this).data('lastTouch', now);
					action = setTimeout(function(evt){
						if(onTapCallback != null && typeof onTapCallback == 'function'){
							onTapCallback(evt);
						}
						clearTimeout(action);   // clear the timeout
					}, delay, [event]);
				}
				$(this).data('lastTouch', now);
			});
		};
	}
    
    //replace targets in a elements with webapp for iOS.
    if( isiOS ) {
    	//$('a').prop("target","webapp");
    	$("a").click(function (event) {
			event.preventDefault();
			window.location = $(this).attr("href");
		});
    }
    
    
})(jQuery);



var resultsHistoric = null;
var strprocessid = null;
var processModelId = null;

function executeProcess(modelid,processid) {
	strprocessid = processid;
	processModelId = modelid;
	if( processid != null && processid != "" ) {
		var tasks = {"tasks": [
			{"task": "process.execute", "id": modelid, "processid": processid }
		]};

		query("model.service",tasks,executeProcessCallback);
	}
	
}

function executeProcessCallback(data) {
	var results = JSON.parse(data);
	resultsHistoric = results;
	var result = results['results'][0]['result'];
	var feedback = results['results'][0]['feedback'];
	
	if( result == 0 ) {
		alert("This process is no longer valid. Please correct the process before proceeding.");
		
		return;
	}
	
	
	var process_parameters = document.getElementById('process_parameters');
	var process_actions = document.getElementById('process_actions');
	
	var paramStr = "";
	var actionStr = "";
	
	if( feedback ) {
	
		for(var i=0;i<feedback.length;i++ ) {
			var item = feedback[i];
		
			if( item['type'] == "build" ) {
				var heading = "";
				var text = "";
				if( item['dimension'] ) {
					heading = "Dimension: " + item['dimension'];
				} else if( item['cube'] ) {
					heading = "Cube: " + item['cube'];
				} else if( item['alias'] ) {
					heading = "Alias: " + item['alias'] + " on " + item['parent'];
				} else if( item['attribute'] ) {
					heading = "Attribute: " + item['attribute'] + " on " + item['parent'];
				} else if( item['hierarchy'] ) {
					heading = "Hierarchy: " + item['hierarchy'] + " on " + item['hierarchy_dimension'];
				}
				text = "This needs to be " + item['mode'] + ".";
			
				actionStr += "<a href='#' class='list-group-item'>";
				actionStr += "<h4 class='list-group-item-heading' style='font-size: 14px;'>" + heading + "</h4>";
				actionStr += "<p class='list-group-item-text'>" + text + "</p>";
				actionStr += "</a>";
			
			
			} else if( item['type'] == "filter" ) { 
				var heading = "Please provide a " + item['mode'];
				var text = "<input type='input' class='form-control' id='filter" + item['filter'] + "' value='" + item['default'] + "' />";
			
				if( item['mode'] == "time" ) {
					text += "<p class='text-muted' style='font-size:11px;'>Note: You can enter multiple time values into this field:";
					text += "<ul>";
					text += "<li>2013</li>";
					text += "<li>Jan 2013</li>";
					text += "<li>Q1 2013</li>";
					text += "<li>This Month</li>";
					text += "<li>Last Month</li>";
					text += "</ul>";
					text += "</p>";
				}
						
				paramStr += "<a href='#' class='list-group-item'>";
				paramStr += "<h4 class='list-group-item-heading' style='font-size: 14px;'>" + heading + "</h4>";
				paramStr += "<p class='list-group-item-text'>" + text + "</p>";
				paramStr += "</a>";
			}
		
		}
	
	} else {
		
		paramStr += "<a href='#' class='list-group-item'>";
		paramStr += "<h4 class='list-group-item-heading' style='font-size: 14px;'>No Input Required</h4>";
		paramStr += "<p class='list-group-item-text'>This process does not need any input from the user to execute.</p>";
		paramStr += "</a>";
		
	}
	
	process_parameters.innerHTML = paramStr;
	process_actions.innerHTML = actionStr;
	
	process_actions.style.display = 'block';
	if( actionStr == "" )
		process_actions.style.display = 'none';
	
	$( "#dialog-process" ).dialog({
      resizable: true,
      height:400,
      width:320,
      modal: true,
      buttons: {
        "Confirm Execution": function() {
        	
        	var filters = new Array();
        	
        	var feedback = resultsHistoric['results'][0]['feedback'];
        	if( feedback ) {
				for(var i=0;i<feedback.length;i++ ) {
					var item = feedback[i];
					if( item['type'] == "filter" ) { 
						var textInput = "filter" + item['filter'];
						var value = document.getElementById(textInput).value;
					
						var filter = new Object();
						filter.name = item['filter'];
						filter.value = value;
						filters.push(filter);
					}
				}
			}
			
			executeProcessConfirmed(strprocessid,filters );
        	
          $( this ).dialog( "close" );
        },
        Cancel: function() {
          $( this ).dialog( "close" );
        }
      }
    });
}

// Find the right method, call on correct element
function launchIntoFullscreen(element) {
  if(element.requestFullscreen) {
    element.requestFullscreen();
  } else if(element.mozRequestFullScreen) {
    element.mozRequestFullScreen();
  } else if(element.webkitRequestFullscreen) {
    element.webkitRequestFullscreen();
  } else if(element.msRequestFullscreen) {
    element.msRequestFullscreen();
  }
}
// Whack fullscreen
function exitFullscreen() {
  if(document.exitFullscreen) {
    document.exitFullscreen();
  } else if(document.mozCancelFullScreen) {
    document.mozCancelFullScreen();
  } else if(document.webkitExitFullscreen) {
    document.webkitExitFullscreen();
  }
}


var bFullScreen = false;
function fullscreenToggle() {
	if( !bFullScreen ) {
		// Launch fullscreen for browsers that support it!
		launchIntoFullscreen(document.documentElement); // the whole page
	} else {
		// Cancel fullscreen for browsers that support it!
		exitFullscreen();
	}
	bFullScreen = !bFullScreen;
}


function executeProcessConfirmed(processid,filters) {
	
	if( processid != null && processid != "" ) {
		var tasks = {"tasks": [
			{"task": "process.execute", "id": processModelId, "processid": processid, "confirm": 1 ,"filters" : filters  }
		]};

		query("model.service",tasks,executeProcessConfirmedCallback);
	}
	
}
function executeProcessConfirmedCallback(data) { 
	window.location = "/logs/";
	return;
	
	if( window.location.pathname.indexOf("process") > 0 ) {
		window.location = "/process/create/?id=" + processModelId + "&processid=" + strprocessid;
	} else {
		window.location.reload(true);
	}
	
	
}


function alertBox(message, title) {
	$("<div></div>").dialog( {
	buttons: { "Ok": function () { $(this).dialog("close"); } },
	close: function (event, ui) { $(this).remove(); },
	resizable: false,
	title: title,
	modal: true
  }).text(message);
}

	
function confimBox(message, title, okButton, callbackFunc) {
	$("<div></div>").dialog( {
	buttons: { "Confirm": function () { $(this).dialog("close"); callbackFunc() } , "Cancel": function () { $(this).dialog("close"); } },
	close: function (event, ui) { $(this).remove(); },
	resizable: false,
	title: title,
	height: 200,
	width: 400,
	modal: true
  }).html(message);
}


