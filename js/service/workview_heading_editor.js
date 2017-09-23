
var heading_edited = null;

function displayHeadingEditor() {

	var headers = getHeaderSelected();
	if( headers.length != 1) 
		return;
		
	heading_edited = headers[0];
	
	
	$("#btnHeadingSetOff").prop('checked', true);
	$("#btnHeadingSetOn").prop('checked', false);
	
	var bLoaded = false;
	
	//attempt to load existing header formula
	var defIndex = getDataset(heading_edited,"definitionposition");
	var set = parseInt(getDataset(heading_edited,"set"));
	var position = getDataset(heading_edited,"position");
	var index = parseInt(getDataset(heading_edited,"index"));
	var headerDef = workview_definition['positioning'][position + "s"][parseInt(defIndex)];
	if( headerDef['members'] ) {
		if( headerDef['members'][set] ) {
			if( headerDef['members'][set]["set-formula"]["formula"] ) {
				//set formula
				$( "#divHeadingContent" )[0].value = headerDef['members'][set]["set-formula"]["formula"];
				
				$("#btnHeadingSetOff").prop('checked', false);
				$("#btnHeadingSetOn").prop('checked', true);
				bLoaded = true;
			} else {
				//individual formula
				if( headerDef['members'][set]["index-formula"][index] ) {
					$( "#divHeadingContent" )[0].value = headerDef['members'][set]["index-formula"][index]["formula"];
					bLoaded = true;
				}
			}
			
		}
	}
	
	if( !bLoaded ) {
		$( "#divHeadingContent" )[0].value = "";
	}
	
	
	var size_width = 660;
	if (navigator.appVersion.indexOf("Win")!=-1) {
		size_width = 700;
	}
	
	var dialog = $( "#dlgHeaderEditor" ).dialog({
      resizable: true,
      height:280,
      width:size_width,
      autoOpen: true,
      modal: false,
      position: { my: "left top" , at: "right bottom", of: headers },
      buttons: {
        "Save Formula": function() {
        	headerSave();
			
          	$( this ).dialog( "close" );
        },
        Cancel: function() {
          $( this ).dialog( "close" );
        }
      }
     });
     
	updateRadioButtons();
}


function getHeadingAppliesToSet() {
	var radio = $(".iradio_flat-grey.checked")[0].childNodes[0];
	if( radio.id == "btnHeadingSetOn" ) {
		return true;
	} else {
		return false;
	}
	return false;
}

function headerSave() {

	var defIndex = getDataset(heading_edited,"definitionposition");
	var set = parseInt(getDataset(heading_edited,"set"));
	var position = getDataset(heading_edited,"position");
	var index = parseInt(getDataset(heading_edited,"index"));
	
	var headerDef = workview_definition['positioning'][position + "s"][parseInt(defIndex)];

	var widthSetting = null;
	if( !headerDef['members'] )
		headerDef['members'] = [];
	if( !headerDef['members'][set] ) 
		headerDef['members'][set] = {"index-formula" : [], "set-formula" : {}};
		
	var def = {"set" : set, "index" : index,"formula" : $( "#divHeadingContent" )[0].value };
	
	if( getHeadingAppliesToSet() ) {
		//reset the set array as the formula is a set formula
		headerDef['members'][set]["set-formula"] = def;
	} else {
		headerDef['members'][set]["set-formula"] = {};
		
		
		if( index < headerDef['members'][set]["index-formula"].length ) {
			if( headerDef['members'][set]["index-formula"][index] ) {
				if( headerDef['members'][set]["index-formula"][index]['width'] ) {
					def['width'] = headerDef['members'][set]["index-formula"][index]['width'];
				}
			}
		}
		
		
		headerDef['members'][set]["index-formula"][index] = def;
	}
	
	workview_definition['positioning'][position + "s"][parseInt(defIndex)] = headerDef;
	heading_edited = null;
	
	workviewSave();
}

function getHeaderSelected() {
	var sel = $(".ui-selected");
	var headers = [];
	
	for(var i=0;i<sel.length;i++) {
		var selection = sel[i];
		
		if( getDataset(selection, "id") == "undefined" ) {
			headers[headers.length] = selection;
		} else {
			
		}
	}
	
	var uniqueDims = [];
	$.each(headers, function(i, el){
    	if($.inArray(el, uniqueDims) === -1) uniqueDims.push(el);
	});
	
	return uniqueDims;
}
