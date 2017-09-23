
var updates_definition = {"updates" : []};
	//update:
		//{"elements" : [], "value" : ""}




function updateBindings() { 
/*
	$("input.dataEntry").bind({
		paste: function(event) {
			pasteIntoCell();
		}
	});
	$("input.dataEntryValidation").bind({
		paste: function(event) {
			pasteIntoCell();
		}
	});*/
	
	
	$('input.dataEntry').change(function() {
		setDataset(this, "updated", "true");
	});
	$('input.dataEntry').keydown(function(event) {
		if (event.keyCode == '13' || event.keyCode == 13) {
			var entry = this.value;
			saveDataEntry();
			/*
			if (entry.length > 2) {
				if (entry.toLowerCase().substr(0, 2) == "r>" || entry.toLowerCase().substr(0, 2) == "r<" ||
				 entry.toLowerCase().substr(0, 2) == "r|" || entry.toLowerCase().substr(0, 2) == "r^") {

				} else {
					selectNextCell();
				}
			}
			
			else
				selectNextCell();
			*/
		} else if (event.keyCode == '27' || event.keyCode == 27) {
			cleanUpDataEntry(); 
		} else if (event.keyCode == '40' || event.keyCode == 40) {	//down
			saveChangeToBatch();
			selectRelativeCell(0,1);
			return false;
		} else if (event.keyCode == '38' || event.keyCode == 38) {	//up
			saveChangeToBatch();
			selectRelativeCell(0,-1);
			return false;
		} else if (event.keyCode == '39' || event.keyCode == 39) {
			saveChangeToBatch();
			selectRelativeCell(1,0);
			return false;
		} else if (event.keyCode == '37' || event.keyCode == 39) {
			saveChangeToBatch();
			selectRelativeCell(-1,0);
			return false;
		}
		
	});
	
	
	$('select.dataEntryValidation').keydown(function(event) {
		if (event.keyCode == '13' || event.keyCode == 13) {
			var entry = this.value;
			saveDataEntry();
		} else if (event.keyCode == '27' || event.keyCode == 27) {
			cleanUpDataEntry(); 
		} else if (event.keyCode == '40' || event.keyCode == 40) {	//down
			return true;
		} else if (event.keyCode == '38' || event.keyCode == 38) {	//up
			return true;
		} else if (event.keyCode == '39' || event.keyCode == 39) {
			saveChangeToBatch();
			selectRelativeCell(1,0);
			return false;
		} else if (event.keyCode == '37' || event.keyCode == 39) {
			saveChangeToBatch();
			selectRelativeCell(-1,0);
			return false;
		}
		
	});
/*
	$('select.dataEntryValidation').keydown(function(event) {
		if (event.keyCode == '13' || event.keyCode == 13) {
			saveDataEntry();
		} else if (event.keyCode == '27' || event.keyCode == 27) {
			cleanUpDataEntry();
		}
	});
*/

	$('select.dataEntryValidation').change(function() {
		setDataset(this, "updated", "true");
		saveChangeToBatch();
		selectRelativeCell(1,0);
	});

}

var selCellAddress = null;


function pasteSelectedCellsFromClipboard(data) {
	var lines = data.split("\r");
	
	if( data.indexOf("\n") == 0 )
		data = data.substring(1,data.length);
	if( data.indexOf("\r") == 0 )
		data = data.substring(1,data.length);
	
	if( data.indexOf("\n") > -1 )
		lines = data.split("\n");
	
	for(var y=0;y<lines.length;y++) {
		var cells = lines[y].split("\t");
		for(var x=0;x<cells.length;x++) { 
			var value = cells[x].trim();
			var relativeCell = getRelativeCell(x,y);
			
			if( relativeCell ) {
				if( getDataset(relativeCell,"type") == "number" ) {
					value = value.replace(/,/gi,"");
				}
				var elements = getDataset(relativeCell,"elements").split("|");
				updates_definition['updates'][updates_definition['updates'].length] = {"elements" : elements, "value" : value}; 
			
			
				setDataset(relativeCell,"value",value);
				//relativeCell.style.color = colorForUnsavedChanges;
			
				relativeCell.innerHTML = value;
			}
		}
	}
	workviewChangeData();
}
function copySelectedCellsToClipboard() {
	var data = "<table>";
	for(var y=0;y<wvTbl.rows.length;y++) {
		var row = wvTbl.rows[y];
		var rowHadData = false;
		for(var x=0;x<row.cells.length;x++) {
			var cell = row.cells[x];
			if( $(cell).hasClass("ui-selected") ) {
				rowHadData = true;
				if( getDataset(cell,"element") ) {
					data += "<td>" + getDataset(cell,"element") + "</td>";
				} else {
					if( getDataset(cell,"value") ) {
						data += "<td>" + getDataset(cell,"value") + "</td>";
					} else {
						data += "<td></td>";
					}
				}
			}
		}
		if( rowHadData ) {
			data = "<tr>" + data + "</tr>";
		}
	}
	data += "</table>";
	
	var myWindow = window.open("", "Clipboard Friendly Data", "width=300, height=150");
	if( myWindow ) {
		myWindow.document.body.innerHTML = "";
		myWindow.document.write("<html><head><title>Clipboard Friendly Data</title></head><body>" + data + "</body></html>");
	} else {
		alert("Tried to open a new window with your data so that you could copy it from there but the browser prevented the popup window.");
	}
}


function getRelativeCell(xChange, yChange) {
	var sel = $(".ui-selected");
	if( sel.length == 1 ) {
		var newAddress = addressFromPosition(getDataset(sel[0],"address"),xChange,yChange);
		return $('.' + newAddress)[0];
	}
	return null;
}

function selectRelativeCellNonEntry(xChange, yChange) {
    var sel = $(".ui-selected");
	if( sel.length == 1 ) {
		var newAddress = addressFromPosition(getDataset(sel[0],"address"),xChange,yChange);
		$(".ui-selected").removeClass("ui-selected");
		$('.' + newAddress).click();
	}
    
    
}

function selectRelativeCell(xChange, yChange) {
    if (selCellAddress == null)
        return;
    
    var newAddress = addressFromPosition(selCellAddress,xChange,yChange);
    var newCell = $('.' + newAddress)[0];
    if( newCell ) {
    	setTimeout(function(){startDataEntry(newCell);}, 50);
        
    }
}


var colorForUnsavedChanges = "#6dba89";

function saveChangeToBatch() {
	var elm = null;
	var valueStr = "";
	
	if( $('input.dataEntry').length != 0 ) {
		elm = $('input.dataEntry')[0];
		valueStr = elm.value;
		
		if( valueStr.indexOf('%') > -1 ) {
			var strReplaced = valueStr.replace(/%/gi,"");
			if( isNumber(strReplaced) ) {
				valueStr = parseFloat(strReplaced)/100;
				valueStr = "" + valueStr;
			}
		}
		
	} else {
		elm = $('select.dataEntryValidation')[0];
		valueStr = $('.dataEntryValidation option:selected')[0].value;
	}
	
	
	var elements = getDataset(elm.parentNode,"elements").split("|");
	
	
	
	updates_definition['updates'][updates_definition['updates'].length] = {"elements" : elements, "value" : valueStr}; 
	
	setDataset(elm.parentNode,"value",valueStr);
	//elm.parentNode.style.color = colorForUnsavedChanges;
}

function saveDataEntry() {
	var elm = null;
	var valueStr = "";
	
	if( $('input.dataEntry').length != 0 ) {
		elm = $('input.dataEntry')[0];
		valueStr = elm.value;
	} else {
		elm = $('select.dataEntryValidation')[0];
		valueStr = $('.dataEntryValidation option:selected')[0].value;
	}
	
    if (getDataset(elm, "EntryStarted") == "true") {
	
		saveChangeToBatch();
		workviewChangeData();
		stopDataEntry();
		
	
    } else {
    	cleanUpDataEntry();
    }
}

function cleanUpDataEntry() {
	if( $('input.dataEntry')[0] ) { 
		var cellObj = $('input.dataEntry')[0].parentNode;
		resetContentsOfCell(cellObj);
		//cellObj.style.color = 'black';
		
	}
	if( $('select.dataEntryValidation')[0] ) { 
		var cellObj = $('select.dataEntryValidation')[0].parentNode;
		resetContentsOfCell(cellObj);
		//cellObj.style.color = 'black';
	}
	selCellAddress = null;
}

function resetContentsOfCell(cellObj) { 
	if( getDataset(cellObj,"type") == "number" ) {
		cellObj.innerHTML = generalNumberFormat(getDataset(cellObj,"value"), getDataset(cellObj, "format"));
		cellObj.style.textAlign = 'right';
	} else if( getDataset(cellObj,"type") == "string" ) {
		cellObj.innerHTML = getDataset(cellObj,"value");
		cellObj.style.textAlign = 'left';
	} else {
		cellObj.innerHTML = "";
	}
	cellObj.style.padding = '4px';
	//cellObj.style.color = 'black';
}

function stopDataEntry() {
    setDataset($('input.dataEntry')[0], "EntryStarted", false)
    
}


function validationHierarchyForValidation(validation) { 
	var validationDimensions = workview_metadata['validationDimensions'];
	for(var i=0;i<validationDimensions.length;i++) {
		var validationDim = validationDimensions[i];
		if( (validationDim['dimension'] + "»" + validationDim['name']).trim() == validation['dimension'].trim() ) {
			return validationDim;
		}
	}
	return null;
}

function findHierarchyLevelAndList(validation, elementList, currentLevel, parentNode) {
	var targetLevel = parseInt(validation['level']);
	var list = [];
	if( targetLevel == currentLevel ) {
		for(var i=0;i<elementList.length;i++) {
			var elm = elementList[i];
			list[list.length] = elm['name'];
		}
	} else {
		for(var i=0;i<elementList.length;i++) {
			var elm = elementList[i];
			
			var newItems = findHierarchyLevelAndList(validation, elm['children'], currentLevel+1);
			
			if( parentNode != "" ) {
				if( elm['name'].trim() != parentNode.trim() ) {
					newItems = [];
				}
			}
			
			list = list.concat(newItems);
			
			
		}
	}
	return list;
}


function validationPopulate(validation,listValidation, valueStr) {
	var hierarchy = validationHierarchyForValidation(validation);
	
	var parent = validation['parent'];
	if( parent != "" ) {
		var elements = getDataset(listValidation.parentNode,"elements");
		var elementsArray = elements.split("|");
		elements = elements.replace(elementsArray[elementsArray.length-1],parent);
		
		//check first if this has been changed in the next batch for the server.
		for(var k=updates_definition['updates'].length-1;k>=0;k--){
			var update = updates_definition['updates'][k];
			
			var targElements = update['elements'];
			var targMeasure = removeHierString(targElements[targElements.length-1]);
			
			var srcElements = elements.split("|");
			var srcMeasure = removeHierString(srcElements[srcElements.length-1]);
			
			
			
			if( targMeasure.toLowerCase().trim() == srcMeasure.toLowerCase().trim() ) {
				var opts = findHierarchyLevelAndList(validation,hierarchy['root'],0,update['value']);
				validationPopulateLoop(opts);
				return;
			}
		}
		
		
		validationForMeasureSelected = validation;
		var tasks = {"tasks": [
			{"task": "cube.value", "id":model_detail.id,  "cubeid": workview_definition['cube'], "elements" : elements }
		]};
		query("model.service",tasks,validationPopulateCallback);
		
	} else {
		var opts = findHierarchyLevelAndList(validation,hierarchy['root'],0,"");
		validationPopulateLoop(opts);
	}
}

function validationPopulateCallback(data) {
	var results = JSON.parse(data);
	var parentNode = "";
	if( results['results'][0]['result'] == 1 ) {
		parentNode = results['results'][0]["string"];
	}
	
	var hierarchy = validationHierarchyForValidation(validationForMeasureSelected);
	var opts = findHierarchyLevelAndList(validationForMeasureSelected,hierarchy['root'],0,parentNode);
	validationPopulateLoop(opts);
}

function validationPopulateLoop(opts) {
	var listValidation = $('select.dataEntryValidation')[0];
	var valueStr = getDataset(listValidation.parentNode,"value");
	var option = null;
	
	option = new Option("", " ");
	if( valueStr.trim() == "" )
		option.selected = true;
	listValidation.options[i] = option;
	
	for (var i = 0; i < opts.length; i++) {
		option = new Option(opts[i], opts[i]);
		if( opts[i] == valueStr )
			option.selected = true;
		listValidation.options[i+1] = option;
	}
	$('select.dataEntryValidation')[0].focus();
}

function removeHierString(value) {
	var mes = value.split("»");
	if(mes.length == 2 )
		value = mes[1];
	return value;
}

var validationForMeasureSelected = null;
function validationForMeasure(measure) { 
	var validations = workview_metadata['validation'];
	for(var i=0;i<validations.length;i++) {
		var validation = validations[i];
		
		var measureStr = validation['measure'];
		var mes = validation['measure'].split("»");
        if(mes.length == 2 )
        	measureStr = mes[1];
		
		if( measureStr.trim() == measure.trim() ) {
			return validation;
		}
	}
	return null;
}

function startDataEntry(cellDiv) {

    cleanUpDataEntry();
    if ( getDataset(cellDiv, "type") == "number" || getDataset(cellDiv, "type") == "string" ) {
        var elements = getDataset(cellDiv, "elements").split("|");
        var element = elements[elements.length-1];
        
        var hierStr = element.split("»");
        if(hierStr.length == 2 )
        	element = hierStr[1];
        
        
        var validation = validationForMeasure(element);
        
        if ( validation == null ) {
        	cellDiv.innerHTML = "<input type='text' value='' class='dataEntry'/>";
        	
            $('input.dataEntry')[0].style.display = 'block';
            $('input.dataEntry')[0].style.height = cellDiv.style.height;
            $('input.dataEntry')[0].style.width = cellDiv.style.width;
            $('input.dataEntry')[0].style.textAlign = cellDiv.style.textAlign;
            $('input.dataEntry')[0].value = getDataset(cellDiv, "value");
            
            //Data Entry Flags
            setDataset($('input.dataEntry')[0], "EntryStarted", "true");
            setDataset($('input.dataEntry')[0], "EntryAddress", cellDiv.id);

			
			cellDiv.style.padding = '0px';
			$('input.dataEntry')[0].style.width = ($(cellDiv).innerWidth()-4) + 'px';
			
			if( getDataset(cellDiv, "type") == "string" ) { 
				$('input.dataEntry')[0].style.textAlign = 'left';
			} else {
				$('input.dataEntry')[0].style.textAlign = 'right';
			}
			
			selCellAddress =  getDataset(cellDiv, "address");
    		$('input.dataEntry')[0].select();
			$('input.dataEntry')[0].focus();
			
        } else {
        	cellDiv.innerHTML = "<select id='validation-select' class='dataEntryValidation'/></select>";
        	
			cellDiv.style.padding = '0px';
        	var listValidation = $('select.dataEntryValidation')[0];
			listValidation.style.width = ($(cellDiv).innerWidth()) + 'px';
			listValidation.style.height = ($(cellDiv).innerHeight()) + 'px';
			
            listValidation.style.display = 'block';
            listValidation.style.textAlign = cellDiv.style.textAlign;
			
			validationPopulate(validation,listValidation,getDataset(cellDiv, "value"));
            
            //Data Entry Flags
            setDataset($('select.dataEntryValidation')[0], "EntryStarted", "true");
            setDataset($('select.dataEntryValidation')[0], "EntryAddress", cellDiv.id);
            
            selCellAddress =  getDataset(cellDiv, "address");
            $('select.dataEntryValidation')[0].focus();
        }
    }
    updateBindings();
    unselect();
    
}


function cellFromAddress(cellAddress)
{
	return cellAddress.substr(1,cellAddress.length-1).split("r");
}

function addressFromPosition(cellAddress, col, row)
{
    var pos = cellFromAddress(cellAddress);
    var newCol = parseInt(pos[0]) + parseInt(col);
    var newRow = parseInt(pos[1]) + parseInt(row);
    return "c" + newCol + "r" + newRow;
}

var validationMeasure = "";
function showDataValidation() {
	
	var sel = $(".ui-selected");
	if( sel.length != 1 ) {
		return;
	}
	var elements = getDataset(sel[0],"elements").split("|");
	validationMeasure = elements[elements.length-1];
	
	var existingValidation = validationForMeasure(validationMeasure);
	
	document.getElementById('data-validation-measure').innerHTML = validationMeasure;
	
	$('#validation-dimension-select option').remove();
	
	
	var dims = [];
	for(var i=0;i<model_detail['dimensions'].length;i++) {
		var dim = model_detail['dimensions'][i];
		for(var k=0;k<dim['hierarchies'].length;k++) {
			var hierarchy = dim['hierarchies'][k];
			var hier = hierarchy['name'];
			dims[dims.length] = dim['name'] + '»' + hier;
		}
		
	}
	dims.sort();
	
	$('#validation-dimension-select').append('<option value="">No Validation</option>');
	
	for(var i=0;i<dims.length;i++) {
		var dim = dims[i];
		var strAdd = "";
		if( existingValidation != null ) {
			if( existingValidation['dimension'].trim() == dim.trim() ) {
				strAdd = " selected"
			}
		}
		
		$('#validation-dimension-select').append('<option value="' + dim + '"' + strAdd + '>Dimension: ' + dim + '</option>');
	}

	//dimension selector
    $('#validation-dimension-select').chosen({
    	width: '280px;',
    	height: '100px;'
    });
    $('#validation-dimension-select-level').chosen({
    	width: '280px;',
    	height: '100px;'
    });
    
    if( existingValidation != null ) {
		//$("#validation-dimension-select-level").val(existingValidation['level']).trigger("liszt:updated");
		//$("#validation-dimension-select-level").chosen().change();
		
		//$('#validation-dimension-select-level')[0].selectedIndex = parseInt(existingValidation['level']);
		$('#validation-dimension-select-level').prop('selectedIndex', parseInt(existingValidation['level']));
		$("#validation-dimension-select-level").trigger("chosen:updated");
		$('#validation-parent')[0].value = existingValidation['parent'];
	} else {
		$('#validation-dimension-select').prop('selectedIndex', 0);
		$("#validation-dimension-select").trigger("chosen:updated");
		
		$('#validation-dimension-select-level').prop('selectedIndex', 0);
		$("#validation-dimension-select-level").trigger("chosen:updated");
		
		$('#validation-parent')[0].value = "";
	}
    
	
	var dialog = $( "#dialog-data-validation" ).dialog({
      resizable: true,
      height:290,
      width:450,
      modal: true,
      close: closeFormulaEditor,
      //position: { my: "left top" , at: "right bottom", of: selection },
      buttons: {
        "Save Validations": function() {
        	
        	var dim =  $('#validation-dimension-select option:selected')[0].value;
        	var level =  $('#validation-dimension-select-level option:selected')[0].value;
        	var parent = $('#validation-parent')[0].value;
        	
        	var tasks = {"tasks": [
				{"task": "cube.validation.update", "id":model_detail.id,  "cubeid": workview_definition['cube'], "measure" : validationMeasure,"dimension" : dim ,"level" : level, "parent" : parent }
			]};
			query("model.service",tasks,showDataValidationCallback);
        	//validationUpdate
        	
        },
        Cancel: function() {
          $( this ).dialog( "close" );
        }
      }
     });
}

function showDataValidationCallback(data) {
	var results = JSON.parse(data);
	if( results['results'][0]['result'] == 1 ) {
        $( "#dialog-data-validation" ).dialog( "close" );
		updateMetadata(workviewSave);
	} else {
		alert(results['results'][0]['error']);
	}
}

