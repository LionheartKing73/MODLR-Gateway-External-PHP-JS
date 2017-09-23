
var formula_selection = null;
var formula_editing = false;

var formula_definition = {};


function getFormulaById(formulaId) {
	var formulae = workview_metadata['formula'];
    for(var i=0;i<formulae.length;i++) {
     	var formula = formulae[i];
     	if( formula['id'] == formulaId ) {
			return formula;
		}
    }
	return null;
}

var formula_name = "";
function displayFormulaEditor(selection, newFormula, loadFormula) {
	formula_definition = {};
	formula_name = "";
	functionCount++;

	if( !newFormula ) {
		newFormula = false;
	}
	
	$("#btnLevelOff").prop('checked', true);
	$("#btnLevelOn").prop('checked', false);
	
	
	
	if( loadFormula ) {
		formula_definition = loadFormula;
	} else if( newFormula ) {
		formula_definition = {};
		
	} else {
		var existing_formula = getDataset(selection,"formula");
		if( existing_formula ) {
			if( existing_formula != "" ) {
				var f = getFormulaById(existing_formula);
				if( f != null ) {
					formula_definition = f;
					
					
					
					
				}
			}
		}
	}
	
	formula_selection = selection;
	$(formula_selection).addClass("ui-editing");
	unselect();

	if( !newFormula ) {
		var applyToCLevel = formula_definition['consolidation_rule'];
		if( applyToCLevel != null ) {
			if( applyToCLevel == "true" ) {
				$("#btnLevelOff").prop('checked', false);
				$("#btnLevelOn").prop('checked', true);
			} else {
				$("#btnLevelOff").prop('checked', true);
				$("#btnLevelOn").prop('checked', false);
			}
		}
	}
	
	var size_width = 660;
	if (navigator.appVersion.indexOf("Win")!=-1) {
		size_width = 700;
	}
	
	document.getElementById('divScopeMembers').innerHTML = '';
	
	
	var elements = getDataset(selection,"elements").split("|");
	for(var i=0;i<elements.length;i++) {
		scopeButtonForElement(elements[i],i);
	}
	
	
	var dialog = $( "#dlgFormulaEditor" ).dialog({
      resizable: true,
      height:420,
      width:size_width,
      autoOpen: true,
      modal: false,
      close: closeFormulaEditor,
      position: { my: "left top" , at: "right bottom", of: selection },
      buttons: {
        "Save Formula": function() {
        	
			var formulaText = document.getElementById('txtFormula').value;
			
			if( formulaText.trim() == "" ) {
				alert("You have not specified a formula.");
				return;
			}
			
        	formula_definition['name'] = formulaText;
        	formula_definition['formula'] = $( "#divFormulaContent" )[0].value;
        	updateFormulaSetConsolidation();
			
        	scopes = [];
        	for(var i=0;i<workview_metadata['dimensions'].length;i++) {
        		//for each dimension store the restrictions which have been set.
        		var restriction = $( "#scope_" + i + "_" + functionCount ).data( "restriction");
        		if( restriction == "element" ) {
        			var element = $( "#scope_" + i + "_" + functionCount ).data( "element");
        			element = element.replaceHtmlEntites();
        			scopes[scopes.length] = {"dimension":"","instructions" : [{"action" : "include", "set" : element}]};
        		} else if( restriction == "hierarchy" ) {
        			//none - add an empty array
        			var hierarchy = $( "#scope_" + i + "_" + functionCount ).data( "hierarchy");
        			scopes[scopes.length] = {"dimension":"","instructions" : [{"action" : "include", "hierarchy" : hierarchy}]};
        			
        		} else {
        			//none - add an empty array
        			scopes[scopes.length] = {"dimension":"","instructions" : []};
        		}
        	}
        	formula_definition['scope'] = scopes;
        	
        	addFormula(formula_definition);
        	
          	
        },
        "Delete Formula": function() {
        	
        	removeFormula(formula_definition);
        	
          	$( this ).dialog( "close" );
        },
        Cancel: function() {
          $( this ).dialog( "close" );
        }
      }
     });
     
     
     var html = '<table id="tableFormula" width="100%"><tr><td width="50%"><h3>Formula Order:</h3>';
     
     html += '<ul id="sortableFormula">';
  	 var formulae = workview_metadata['formula'];
  	 
     for(var i=0;i<formulae.length;i++) {
     	var formula = formulae[i];
     	html += '<li class="ui-state-default" data-id="' + formula['id'] + '" ondblclick="editFormula(\'' + formula['id'] + '\')"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>' + formula['name'] + '</li>';
     }
     html += '</ul>';
     
     html += '</td><td><h3>Formula Actions:</h3><center>';
     
     html += "<button style='width:200px;' id='btnNewFormula' onclick='displayFormulaEditor(formula_selection,true);'>Add New Formula</button>";
     html += "<button style='width:200px;' id='btnUpdateOrder' onclick='reorderFormula();'>Update the Formula Order</button><br/><br/>";
     
     html += '</center></td></tr></table>';
     
     $( "#tabs-2-formula" ).html(html);
     
     $( "#sortableFormula" ).sortable();
     $( "#sortableFormula" ).disableSelection();
     
     
     $( "#btnNewFormula" ).button({ icons: { primary: "ui-icon-plus" } });
     $( "#btnUpdateOrder" ).button({ icons: { primary: "ui-icon-check" } });
     
     $( "#tabsFormula" ).tabs();
     $("#tabsFormula").tabs("option", "active", 0);
     
     $( "#divScopeMembers,#divFormula,#dlgFormulaEditor,#txtFormula" ).bind( "touchstart click", function() {
		editorDeselect();
		return false;
	 });
     $( "#divFormulaContent" ).bind( "touchstart click", function() {
		editorSelect();
		return false;
	 });

	 $( "#divFormulaContent" ).focus(function() {
		editorSelect();
	 });

	 $( "#divFormulaContent" )[0].value = '';
	 if( formula_definition['formula'] ) {
	 	$( "#divFormulaContent" )[0].value = formula_definition['formula'].replace(/&nbsp;/gi," ");
	 }
	 
	formula_name = formula_name.substr(0,formula_name.length-2).replaceHtmlEntites();
	 
	if( formula_definition['name'] ) {
		formula_name = formula_definition['name'];
	}
	 
	document.getElementById('txtFormula').value = formula_name;
	 
	
	
	
	updateRadioButtons();
	formula_editing = true;
	
}

function updateFormulaSetConsolidation() {
	var radio = $(".iradio_flat-green.checked")[0].childNodes[0];
	if( radio.id == "btnLevelOn" ) {
		formula_definition['consolidation_rule'] = "true";
	} else {
		formula_definition['consolidation_rule'] = "false";
	}
}

function editFormula(formulaid) {
	var formulae = workview_metadata['formula'];
  	 
     for(var i=0;i<formulae.length;i++) {
     	var formula = formulae[i];
     	if( formula['id'] == formulaid ) {
     		displayFormulaEditor(formula_selection, false, formula);
     	}
     }
	
	
}

function reorderFormula() {
	var list = $("#sortableFormula")[0];
	var listFormulae = [];
	
	for(var i=0;i<list.childNodes.length;i++) {
		var li = list.childNodes[i];
		listFormulae[listFormulae.length] = getDataset(li,"id");
	}
	var tasks = {};
	tasks = {"tasks": [
		{"task": "cube.formula.reorder", "id" : model_detail.id, "cubeid" : workview_definition['cube'], "formulalist" : listFormulae }
	]};
	
	query("model.service",tasks,reorderFormulaCallback);
}

function reorderFormulaCallback(data) {
	var results = JSON.parse(data);
	if( results['results'][0]['result'] == 1 ) {
		$( "#dlgFormulaEditor" ).dialog( "close" );
	} else {
		alert(results['results'][0]['error']);
	}
	updateMetadata(workviewSave);
}

function removeFormula(formula_definition) {
	var tasks = {};
	tasks = {"tasks": [
		{"task": "cube.formula.remove", "id" : model_detail.id, "cubeid" : workview_definition['cube'], "formulaid" : formula_definition['id'] }
	]};
	
	query("model.service",tasks,addFormulaCallback);	
}


function addFormula(formula_definition) {
	var tasks = {};
	if( formula_definition['id'] ) {
		tasks = {"tasks": [
			{"task": "cube.formula.update", "id" : model_detail.id, "cubeid" : workview_definition['cube'], "formulaid" : formula_definition['id'], "definition" : formula_definition }
		]};
	} else {
		tasks = {"tasks": [
			{"task": "cube.formula.add", "id" : model_detail.id, "cubeid" : workview_definition['cube'], "definition" : formula_definition }
		]};
	}
	
	query("model.service",tasks,addFormulaCallback);	
}
function addFormulaCallback(data) {
	var results = JSON.parse(data);
	if( results['results'][0]['result'] == 1 ) {
		$( "#dlgFormulaEditor" ).dialog( "close" );
	} else {
		alert(results['results'][0]['message'] + "\r\n" + results['results'][0]['error']);
	}
	updateMetadata(workviewSave);
}


function flashAppliedCells() {
	$('.c').animate({color: '#D66', 'delay' : 0});
	setTimeout(
	function(){
		$('.c').animate({color: '#000', 'delay' : 100})
	}
	, 2000);
	
}

function formulaAddReference(cell) {
	var elements1 = getDataset(cell,"elements").split('|');
	var elements2 = getDataset(formula_selection,"elements").split('|');
	
	var formula = "[";
	for(var i=0;i<elements1.length;i++ ) {
		if( removeHierString(elements1[i]) != removeHierString(elements2[i]) ) {
			if( formula.length > 1 ) {
				formula += ",";
			}
			formula += "\"" + removeHierString(elements1[i]) + "\"";
		}
	}
	formula += "]";
	
	if( document.activeElement.id == "divFormulaContent" ) {
		//insertTextAtCursor(formula);
		insertAtCaret('divFormulaContent',formula);
	}
	//insertAtCursor(document.getElementById('divFormulaContent') ,formula);
	
}

function editorSelect() {
	formula_editing = true;
	$( "#divFormulaContent" ).addClass('divFormulaSelected');
}

function editorDeselect() {
	formula_editing = false;
	$( "#divFormulaContent" ).removeClass('divFormulaSelected');
}

function closeFormulaEditor() {
	if( formula_selection ) 
		$(formula_selection).removeClass("ui-editing");
	editorDeselect();
}

var functionCount = 0;


function scopeButtonForElement(elementStr, elementPosition) {
	 
	var dim = getDimensionAt(elementPosition);
	var dimensionName = dim['name'];
	var hierarchyName = "Default";
	var elementName = elementStr;
	
	//need to work out what hierarchy is in use so we can offer hierarchal scopes.
	var hierStr = elementName.split("»");
	if( hierStr.length == 2 ) {
		hierarchyName = hierStr[0];
		elementName = hierStr[1];
	} else {
		//need to work out what the hierarchy selected is.
		//hierarchyName
		
	}
	var parentElement = "";
	var parentParentElement = "";
	
	
	elementName = elementName.replace(/ /gi,'&nbsp;');
	
	var div = document.createElement('div');
	
	var html = "<div>";
    html += "	<button id='scope_" + elementPosition  + "_" + functionCount + "'>" + dimensionName + " - Don't Restrict</button>";
  	html += "</div>";
  	
  	$( "#scope_" + elementPosition + "_" + functionCount ).data( "restriction", "none" );
  	
  	div.innerHTML = html;
  	div.style.display = 'inline-block';
  	
	document.getElementById('divScopeMembers').appendChild(div);
	
	
	$( "#scope_" + elementPosition + "_" + functionCount )
      .button({
      	icons: {
        primary: "ui-icon-triangle-1-s"
      	}
      })
      .click(function() {
    	$("#scope_" + elementPosition + "_" + functionCount).contextMenu();
      });
      
    if( dim['type'] == "measure" ) {
    	$( "#scope_" + elementPosition + "_" + functionCount ).button( "option", "label", dimensionName + " - Restrict to <b>" + elementName + "</b>" );
    	$( "#scope_" + elementPosition + "_" + functionCount ).data( "restriction", "element" );
        $( "#scope_" + elementPosition + "_" + functionCount ).data( "element", elementName );
        $( "#scope_" + elementPosition + "_" + functionCount ).data( "dimension", dim['name'] );
        formula_name += elementName + ", ";
    }
	
	//check if the user likely intends on writing a formula for the measure or a row dimension.
	var measuresUnlikely = false;
	var measuresDims = getDimensionsOfType("measure");
	for(var i=0;i<measuresDims.length;i++) {
		var dimItem = measuresDims[i];
		if( dimIsOnAxis(dimItem['id'],"titles") ) {
			measuresUnlikely = true;
		}
	}
	
	//if the measure is on titles then we need to restrict by the dimensions on rows.
	if( measuresUnlikely ) {
		if( dimIsOnAxis(dim['id'],"rows") ) {
			$( "#scope_" + elementPosition + "_" + functionCount ).button( "option", "label", dimensionName + " - Restrict to <b>" + elementName + "</b>" );
			$( "#scope_" + elementPosition + "_" + functionCount ).data( "restriction", "element" );
            $( "#scope_" + elementPosition + "_" + functionCount ).data( "element", elementName );
        	$( "#scope_" + elementPosition + "_" + functionCount ).data( "dimension", dim['name'] );
            formula_name += elementName + ", ";
		}
	}
	
	
	if( formula_definition['scope'] ) {
		var scope = formula_definition['scope'][elementPosition];
		if( scope ) {
			var instructions = scope['instructions'];
			if( instructions ) {
				if( instructions.length == 0 ) {
					setScopeOption(elementPosition, dimensionName, "", "none");
				} else {
					if( instructions.length == 1 ) {
						var instruction = instructions[0];
						if( instruction["action"] == "include" ) {
							if( instruction["set"] ) {
								setScopeOption(elementPosition, dimensionName, instruction["set"], "element");
							} else if( instruction["hierarchy"] ) {
								setScopeOption(elementPosition, dimensionName, instruction["hierarchy"], "hierarchy");
							}
						}
						
					} else {
						//TODO: Set selection
					}
				}
			}
		}
	}
	
	var buttonItems = {
			"no-restrictions": {name: dimensionName + " - Don't Restrict", icon: "lock-unlock"},
			"element-restriction": {name: dimensionName + " - Restrict to <b>" + elementName + "</b>", icon: "lock"}
		};
	
	var hiers = [];
	for(var i=0;i<dim['hierarchies'].length;i++) {
		var hier = dim['hierarchies'][i];
		hiers[hiers.length] = hier['name'];

	}
	hiers.sort();

	for(var i=0;i<hiers.length;i++) {
		var hier = hiers[i];
		buttonItems["hierarchy-restriction-" + hier] = {name: dimensionName + " - Restrict to the lowest elements in the <b>" + hier + "</b> hierarchy", icon: "filter", "hierarchy" : hier};
	}
	
	
	
	$.contextMenu({
		selector: "#scope_" + elementPosition + "_" + functionCount, 
		callback: function(key, options) {
			if( key == "no-restrictions" ) {
				setScopeOption(elementPosition, dimensionName, elementName, "none");
			} else if( key == "element-restriction" ) {
				setScopeOption(elementPosition, dimensionName, elementName, "element");
			} else if( key == "parent-restriction" ) {
				setScopeOption(elementPosition, dimensionName, elementName, "group");
			} else if( key.substr(0,("hierarchy-restriction").length) == "hierarchy-restriction" ) {
				setScopeOption(elementPosition, dimensionName, buttonItems[key]["hierarchy"], "hierarchy");
			}
		}, 
		items:buttonItems /* { 
			"no-restrictions": {name: dimensionName + " - Don't Restrict", icon: "lock-unlock"},
			"element-restriction": {name: dimensionName + " - Restrict to <b>" + elementName + "</b>", icon: "lock"},
			"hierarchy-restriction": {name: dimensionName + " - Restrict to the lowest elements in the <b>" + hierarchyName + "</b> hierarchy", icon: "filter"}
		} */
	}).data("type","scope");
	
	
	//	"parent-restriction": {name: dimensionName + " - Restrict to children of <b>" + elementName + "</b>", icon: "filter"},
	
	
	return;
}

function setScopeOption(elementPosition, dimensionName, elementName, restriction) {
	if( restriction == "none" ) {
		$( "#scope_" + elementPosition + "_" + functionCount ).button( "option", "label", dimensionName + " - Don't Restrict" );
        $( "#scope_" + elementPosition + "_" + functionCount ).data( "restriction", restriction );
	} else if( restriction == "element" ) {
		$( "#scope_" + elementPosition + "_" + functionCount ).button( "option", "label", dimensionName + " - Restrict to <b>" + elementName + "</b>" );
        $( "#scope_" + elementPosition + "_" + functionCount ).data( "restriction", restriction );
        $( "#scope_" + elementPosition + "_" + functionCount ).data( "element", elementName );
	} else if( restriction == "group" ) {
		$( "#scope_" + elementPosition + "_" + functionCount ).button( "option", "label", dimensionName + " - Restrict to children of <b>" + elementName + "</b>" );
        $( "#scope_" + elementPosition + "_" + functionCount ).data( "restriction", restriction );
        $( "#scope_" + elementPosition + "_" + functionCount ).data( "element", elementName );
	}  else if( restriction == "hierarchy" ) {
		$( "#scope_" + elementPosition + "_" + functionCount ).button( "option", "label", dimensionName + " - Restrict to the lowest elements in the <b>" + elementName + "</b> hierarchy" );
        $( "#scope_" + elementPosition + "_" + functionCount ).data( "restriction", restriction );
        $( "#scope_" + elementPosition + "_" + functionCount ).data( "hierarchy", elementName );
	} 
    $( "#scope_" + elementPosition + "_" + functionCount ).data( "dimension", dimensionName );
}


function updateRadioButtons() {

	$(function(){
		"use strict";
		$('.minimal input').iCheck({
			checkboxClass: 'icheckbox_minimal',
			radioClass: 'iradio_minimal',
			increaseArea: '20%' // optional
		});
	});

	$(function(){
		$('.flat-red input').iCheck({
			checkboxClass: 'icheckbox_flat-red',
			radioClass: 'iradio_flat-red'
		});
	});

	$(function(){
		$('.flat-green input').iCheck({
			checkboxClass: 'icheckbox_flat-green',
			radioClass: 'iradio_flat-green'
		});
	});

	$(function(){
		$('.flat-grey input').iCheck({
			checkboxClass: 'icheckbox_flat-grey',
			radioClass: 'iradio_flat-grey'
		});
	});

}
