
var workview_definition = null;
var workview_execute_options = {"disable-set-instructions" : "0", "expand" : []};
var titlesList = null;

var cell_prefix = "";

function toggle(header) {
	var expandOptions = workview_execute_options['expand'];
	
	var dimension = getDataset(header,"id");
	var element = getDataset(header,"element");
	var hierarchy = getDataset(header,"hierarchy");
	var principal = getDataset(header,"principal");
	
	if( principal == "" )
		principal = element;
	
	var expanding = getDataset(header,"expand");
	
	var set = getDataset(header,"set");
	//search for a match
	
	var bFound = false;
	for(var i=0;i<expandOptions.length;i++) {
		var expand = expandOptions[i];
		if( expand.element == principal && expand.set == set && expand.hierarchy == hierarchy) {
			if( expand.action == "expand" ) {
				expand.action = "collapse";
			} else {
				expand.action = "expand";
			}
			bFound = true;
		}
	}
	
	if( !bFound ) {
		var action = "expand";
		if( expanding == "1" ) {
			action = "collapse";
		}
		expandOptions[expandOptions.length] = {"element" : principal,"hierarchy" : hierarchy, "action" : action, "set" : set,"dimension" : dimension};
	}
	
	if( typeof workviewSave === "function" ) {
		workviewSave();
	} else {
		refresh();
	}
	
}

if( typeof workview_definition_loaded !== 'undefined' ) {
	if( workview_definition_loaded != null ) {
		workview_definition = workview_definition_loaded;
	}
}

function updateDefinition(key, value) {
	workview_definition[key] = value;
}
function getDefinition(key) {
	return workview_definition[key];
}

function updateDefinitionColumn(area, field, key, value) {
	if( ! workview_definition[area] )
		workview_definition[area] = new Object();
		
	if( ! workview_definition[area][field] )
		workview_definition[area][field] = new Object();
		
	workview_definition[area][field][key] = value;
}


function getDefinitionPosition(area, field) {
	if( workview_definition[area] ) {
		if( workview_definition[area][field] ) {
			return workview_definition[area][field];
		}
	}
	return null;
}


function dimByName(dimName) {
	for(var i=0;i<workview_metadata['dimensions'].length;i++) {
		var dim = workview_metadata['dimensions'][i];
		if( dim.name == dimName ) 
			return dim;
	}
	return null;
}

function dimById(dimId) {
	for(var i=0;i<workview_metadata['dimensions'].length;i++) {
		var dim = workview_metadata['dimensions'][i];
		if( dim.id == dimId ) 
			return dim;
	}
	return null;
}

function getDefinitionColumn(area, field, key) {
	if( workview_definition[area] ) {
		if( workview_definition[area][field] ) {
			if( workview_definition[area][field][key] ) {
				return workview_definition[area][field][key];
			}
		}
	}
	return null;
}


function getDimensionAt(index) {
	return workview_metadata['dimensions'][index];
}

function getDimensionsOfType(dimType) {
	var dims = [];
	for(var i=0;i<workview_metadata['dimensions'].length;i++) {
		var dim = workview_metadata['dimensions'][i];
		if( dim.type == dimType ) 
			dims[dims.length]=dim;
	}
	
	return dims;
}
function itemWithMatchingField(arrayObj,fieldName,fieldValue) {
	for(var i=0;i<arrayObj.length;i++) {
		if( arrayObj[i][fieldName] == fieldValue ) {
			return  arrayObj[i];
		}
	}
	return null;
}



//the html dom object for the workview div.
var wv = null;
var wvTbl = null;
var wvTblTitles = null;

function loadWorkview() {
	var workview_name = getDefinition('name');
	
	wv = document.getElementById('workview');
	
	var initialHTML = "<table id='wvTblTitles' class='grid'></table><table id='wvTbl' class='grid'></table>";
	if( is_editor ) {
		initialHTML = "<table id='wvTblTitles' class='grid'></table><table id='wvTbl' class='grid'></table>";
	}
	wv.innerHTML = initialHTML;
	
	wvTblTitles = document.getElementById('wvTblTitles');
	wvTbl = document.getElementById('wvTbl');
	
	
	var row_sets = workview_definition['row_sets'];
	var column_sets = workview_definition['column_sets'];
	
	var row_count = workview_definition['rows'];
	var column_count = workview_definition['columns'];
	
	if( !row_sets ) {
		row_sets = 1;
		row_count = 1;
		workview_definition['row_sets'] = row_sets;
		workview_definition['row_start'] = 0;
	}
	if( !column_sets ) {
		column_sets = 1;
		column_count = 1;
		workview_definition['column_sets'] = column_sets;
		workview_definition['column_start'] = 0;
	}
	
	
	
	if( getDefinitionPosition('positioning','rows') == null ) {
		//bring up the manage dimensions dialog.
		
		displayManageDimensions();
		updateManageDimensions();
	} else {
		//render the table
		//there aren't any row and column types, it is simply worked that combinations which have elements for all required dimensions return amounts.
		
	}
	
	updateGridWithDefinition();
	workviewSave();
}

function standardDimensionChecks(dimId) { 
	//standard checks 
	if( !workview_definition['dimensions'][dimId] )
		workview_definition['dimensions'][dimId] = {};

	if( !workview_definition['dimensions'][dimId]['set'] )
		workview_definition['dimensions'][dimId]['set'] = [];
}

function removeSet(axisStr,at) {
	var axisSets = workview_definition['row_sets'];
	var axisStart = workview_definition['row_start'];
	if( !axisStart) {
		axisStart = 0;
		workview_definition['row_start'] = 0;
	}
	if( axisStr == "columns" ) {
		axisSets = workview_definition['column_sets'];
		axisStart = workview_definition['column_start'];
		if( !axisStart) {
			axisStart = 0;
			workview_definition['column_start'] = 0;
		}
	}
	
	//add the set to the dimension set list.
	var axis = workview_definition['positioning'][axisStr];
	for(var i=0;i<axis.length;i++) {	//loop through dimensions on this axis.
		if( axis[i].id ) {
			var dim = dimById(axis[i].id);
			
			standardDimensionChecks(dim.id);
		
			var len = workview_definition['dimensions'][dim.id]['set'].length;
			
			var insert = at;// + Math.abs(axisStart);
			workview_definition['dimensions'][dim.id]['set'].splice(insert,1);
		}
	}
	
	if( axisStr == "columns" ) {
		workview_definition['column_sets']--;
		if( at < Math.abs(workview_definition['column_start']) )
			workview_definition['column_start']++;
	} else {
		workview_definition['row_sets']--;
		if( at < Math.abs(workview_definition['row_start']) )
			workview_definition['row_start']++;
	}
	
	workviewSave();
} 

function dimIsOnAxis(dimid, axisStr) {
	var axis = workview_definition['positioning'][axisStr];
	for(var i=0;i<axis.length;i++) {	//loop through dimensions on this axis.
		if( axis[i].id == dimid ) {
			return true;
		}
	}
	return false;
}

//axisStr = "rows" or "columns"
function addSet(axisStr,at) {
	
	var axisSets = workview_definition['row_sets'];
	var axisStart = workview_definition['row_start'];
	if( typeof axisStart === 'undefined' ) {
		axisStart = 0;
		workview_definition['row_start'] = 0;
	}
	if( axisStr == "columns" ) {
		axisSets = workview_definition['column_sets'];
		axisStart = workview_definition['column_start'];
		if( typeof axisStart === 'undefined' ) {
			axisStart = 0;
			workview_definition['column_start'] = 0;
		}
	}
	
	//add the set to the dimension set list.
	var axis = workview_definition['positioning'][axisStr];
	for(var i=0;i<axis.length;i++) {	//loop through dimensions on this axis.
		if( axis[i].id ) {
			var dim = dimById(axis[i].id);
			
			standardDimensionChecks(dim.id);
		
			var len = workview_definition['dimensions'][dim.id]['set'].length;
			if( len == 0 )
				len++;
			var insert = at + Math.abs(axisStart);
			workview_definition['dimensions'][dim.id]['set'].splice(insert,0,{"instructions":[],"element":null});
			
			if( len + 1 > workview_definition['dimensions'][dim.id]['set'].length) {
				workview_definition['dimensions'][dim.id]['set'].splice(insert,0,{"instructions":[],"element":null});
			}
		} 
	}
	
	if( axisStr == "columns" ) {
		workview_definition['column_sets']++;
		if( at < 0 )
			workview_definition['column_start']--;
	} else {
		workview_definition['row_sets']++;
		if( at < 0 )
			workview_definition['row_start']--;
	}
	
	workviewSave();
}

function changeReportSize(xAdd,yAdd) {
	var row_sets = workview_definition['row_sets'];
	var column_sets = workview_definition['column_sets'];
	
	if( row_sets + yAdd < 1 )
		return;
	if( column_sets + xAdd < 1 )
		return;
	
	workview_definition['row_sets'] = row_sets + yAdd;
	workview_definition['column_sets'] = column_sets + xAdd;
	
	//add the set to the dimension set list.
	var axisStr = "rows";
	var axisSetSize = row_sets + yAdd;
	if( xAdd != 0 ) {
		axisStr = "columns";
		axisSetSize = column_sets + xAdd;
	}
	
	
	
	var axis = workview_definition['positioning'][axisStr];
	
	//check for dimensions on titles which have since been removed from the cube.
	for(var i=axis.length-1;i>=0;i--) {
		if( axis[i].id ) {
			var dim = dimById(axis[i].id);
			if( dim == null ) {
				axis.splice(i,1);
			}
		}
	}
	
	
	for(var i=0;i<axis.length;i++) {
		if( axis[i].id ) {
			var dim = dimById(axis[i].id);
		
			if( !workview_definition['dimensions'][dim.id] )
				workview_definition['dimensions'][dim.id] = {};
		
			if( !workview_definition['dimensions'][dim.id]['set'] )
				workview_definition['dimensions'][dim.id]['set'] = [];
		
			var len = workview_definition['dimensions'][dim.id]['set'].length;
			for(var k=len;k<axisSetSize;k++) { 
				workview_definition['dimensions'][dim.id]['set'][k] = {"instructions":[],"element":null};
			}
			for(var k=len-1;k>axisSetSize-1;k--) { 
				workview_definition['dimensions'][dim.id]['set'].splice(k,1);
			}
		}
	}
	
	workviewSave();
}

function styleById(styleId) {
	for(var k=0;k<workview_metadata['styles'].length;k++) {
		var item = workview_metadata['styles'][k];
		if( styleId == item['id'] ) {
			return item;
		}
	}
	return null;
}

function updateWorkviewData(results) {
	
	//return the position collections
	var titles = workview_definition['positioning']['titles'];
	var rows = workview_definition['positioning']['rows'];
	var columns = workview_definition['positioning']['columns'];
	var hidden = workview_definition['positioning']['hidden'];
	
	if( model_detail['styles'] ) {
		//var c = $(".c.h");
		for(var o=0;o<model_detail['styles'].length;o++) {
			$("." + model_detail['styles'][o]["name"]).find(".c").removeClass(model_detail['styles'][o]["name"]);
			//c.removeClass(model_detail['styles'][o]["name"]);
		}
	}
	
	//return the number of set rows and columns in the grid.
	
	var row_count = 0;
	if( results['rows'][0] )
		row_count = results['rows'][0]['members'].length;
		
	var column_count = 0;
	if( results['columns'][0] )
		column_count = results['columns'][0]['members'].length;
	
	for(var yy=0;yy<row_count;yy++) {
		var y = yy + columns.length;
		
		var rowHasValues = false;
		
		for(var xx=0;xx<column_count;xx++) {
			var x = xx + rows.length;
			
			
			var col = results['columns'][0]['members'][xx];
			var row = results['rows'][0]['members'][yy];
			var cellAddress = 'c' + col['column'] + 'r' + row['row'];
			
			var cell = results[cellAddress];
			
			if( cell ) {
				rowHasValues = true;
				var cellObj = document.getElementById(cell_prefix + cellAddress);
				if( cellObj ) {
					var c = $(cellObj);
					c.removeClass("consolidation");
					c.removeClass("formula");
					
					
					
					
					if( cell.value ) {
						if( typeof cell.value.number != 'undefined' ) {
							setDataset(cellObj, "type", "number");
							setDataset(cellObj, "value", cell.value.number);
							setDataset(cellObj, "format", cell.format);
							setDataset(cellObj, "validation", "");
						} else if( typeof cell.value.string != 'undefined') {
							setDataset(cellObj, "type", "string");
							setDataset(cellObj, "value", cell.value.string);
							setDataset(cellObj, "validation", "");
						} else if( typeof cell.value.blank != 'undefined') {
							$(cellObj).removeClass("c");
							
						}
						
						if( cell['styles'] ) {
							var styles = cell['styles'].split("|");
							for(var k=0;k<styles.length;k++) {
								var styleObject = styleById(styles[k]);
								if( styleObject == null ) {
									$(cellObj).addClass(styles[k]);
								} else {
									$(cellObj).addClass(styleObject['name']);
								}
								
							}
						}	
						
						if( cell.value.formula ) {
							if( cell.value.formula ) {
								setDataset(cellObj, "formula", cell.value.formula );
							}
						}
						
						if( cell.value.status ) {
							var status = cell.value.status;
							$(cellObj).addClass(status);
						}
						
						setDataset(cellObj, "elements", cell.elements.join("|"));
						resetContentsOfCell(cellObj);
					} else {
						$(cellObj).addClass("s");		
					} 
				}
			}
			
		}
		
		//remove row. for suppression.
	}
	
	
	
}

function updateGridWithDefinitionProduceHeader(results, headerObject,x,y,elmNumber,dimIndex, axis) {

	var optionalArgs = "";
	var optionalStyle = "";
	var optionalClass = "h ";
	var detaultElm = "&nbsp;";
	var setNumber = 0;
	var levelNumber = 0;
	var minWidth = null;
	var hierarchy = "";
	var principal = null;
	
	var title = dimHeader;
	if( headerObject.type == 'dimension' ) {
		var dim = dimById(headerObject.id);
		title = 'Dimension: ' + dim.name;
		
		if( !workview_definition['dimensions'][dim.id] )
			workview_definition['dimensions'][dim.id] = {};
		
		if( !workview_definition['dimensions'][dim.id]['set'] )
			workview_definition['dimensions'][dim.id]['set'] = [];
		
		if( axis == "rows" ) {
			minWidth = workview_definition['dimensions'][dim.id]['width'];
		}
		
		
		//dimIndex
		var dimPosition = 0;
		for(var i=0;i<dimIndex;i++) {
			if( results[axis][dimPosition].type == 'dimension' )
				dimPosition++;
		}
		
		if( elmNumber < headerObject['members'].length ) {
		
			detaultElm = headerObject['members'][elmNumber]['name'];
			principal =  headerObject['members'][elmNumber]['principal'];
			if( typeof principal === "undefined" ) {
				principal = name;
			}
			
			hierarchy = headerObject['members'][elmNumber]['hierarchy'];
			if( !detaultElm ) {
				detaultElm = "&nbsp;";
				
			} else {
				optionalArgs = " data-element='" + detaultElm + "'";
			}
			
			if( !hierarchy )
				hierarchy = "";
			
			
			setNumber = headerObject['members'][elmNumber]['set'];
			levelNumber = headerObject['members'][elmNumber]['level'];
			
			//column width for spacers
			if( axis == "columns" ) {
				if( headerObject['members'][elmNumber]['spacer'] ) {
					if( headerObject['members'][elmNumber]['spacer'] == "1" ) {
						if( workview_definition['dimensions'][dim.id]['set'][setNumber] ) {
							if( workview_definition['dimensions'][dim.id]['set'][setNumber]['styles'] ) {
								if( workview_definition['dimensions'][dim.id]['set'][setNumber]['styles']["blank"] ) {
									minWidth = workview_definition['dimensions'][dim.id]['set'][setNumber]['styles']["blank"]['Width'];
								}
							}
						}
					}
				} else {
					if( workview_definition['dimensions'][dim.id]['set'][setNumber] ) {
						if( workview_definition['dimensions'][dim.id]['set'][setNumber]['styles'] ) {
							if( workview_definition['dimensions'][dim.id]['set'][setNumber]['styles'][cleanStr(detaultElm)] ) {
								minWidth = workview_definition['dimensions'][dim.id]['set'][setNumber]['styles'][cleanStr(detaultElm)]['Width'];
							}
						}
					}
				}
			}
			
		}
		title += " (Set " + (parseInt(setNumber)+1) + ")";
	} else {
		if( elmNumber < headerObject['members'].length ) {
			detaultElm = headerObject['members'][elmNumber]['name'];
		}
		
		if( headerObject['members'][elmNumber]['index'] ) {
			dimIndex = headerObject['members'][elmNumber]['index'];
		}
		
		if( headerObject['members'][elmNumber]['set'] ) {
			setNumber = headerObject['members'][elmNumber]['set'];
		}
		if( headerObject['members'][elmNumber]['column'] ) {
			optionalArgs += " data-column='" + headerObject['members'][elmNumber]['column'] + "'";
		}
		if( headerObject['members'][elmNumber]['row'] ) {
			optionalArgs += " data-row='" + headerObject['members'][elmNumber]['row'] + "'";
		}
		
		if( axis == "rows" ) {
			//check for col width 
			if( headerObject['width'] ) {
				minWidth = headerObject['width'];
			}	
		} else {
			if( headerObject['members'][elmNumber]['width'] ) {
				minWidth = headerObject['members'][elmNumber]['width'];
			}
		}
		
		
		optionalArgs += " data-definitionPosition='" + headerObject['index'] + "'";
	}

	if( axis == "rows" ) {
		optionalArgs += " data-position='row'";
		optionalStyle += "text-indent:" + (parseInt(levelNumber) * 15) + "px;"
		if( minWidth ) {
			optionalStyle += "min-width:" + minWidth + "px;";
		}
		
	} else if( axis == "columns" ) {
		optionalArgs += " data-position='column' ";
		optionalStyle += "padding-top:" + (parseInt(levelNumber) * 20 + 4) + "px;"
		
		if( minWidth ) {
			optionalStyle += "min-width:" + minWidth + "px;";
		}
	}
	
	//if( dimPosition !== undefined ) {
		if( elmNumber < headerObject['members'].length ) {
			if( headerObject['members'][elmNumber]['spacer'] ) {
				if( headerObject['members'][elmNumber]['spacer'] == "1" ) {
					optionalClass = "spacer_" + axis;
					detaultElm = "";
				}
			} else {
				//look for styles to apply.
				if( headerObject['members'][elmNumber]['style-heading'] ) {
					optionalClass = headerObject['members'][elmNumber]['style-heading'] + " " + optionalClass;
				}
			}
		}
		/*
	} else {
		if( dimIndex-1 >= 0 && dimIndex-1 < results[axis].length ) {
			if( results[axis][dimIndex-1]['members'][elmNumber]['spacer'] ) {
				if( results[axis][dimIndex-1]['members'][elmNumber]['spacer'] == "1" ) {
					optionalClass = "spacer_" + axis;
				}
			}
		} else if( dimIndex+1 >= 0 && dimIndex+1 < results[axis].length ) {
			if( results[axis][dimIndex+1]['members'][elmNumber]['spacer'] ) {
				if( results[axis][dimIndex+1]['members'][elmNumber]['spacer'] == "1" ) {
					optionalClass = "spacer_" + axis;
				}
			}
		}
	}*/
	
	if( principal == null )
		principal = name;
		
	if( headerObject['members'][elmNumber]["drill"] ) {
		var expandIcon = "fa-plus";
		var expandText = "Expand";
		var expandOptional = "";
		if( headerObject['members'][elmNumber]["expanded"] ) {
			expandIcon = "fa-minus";
			expandText = "Collapse";
			optionalArgs += " data-expand='1' ";
		}
		
		if( parseInt(headerObject['members'][elmNumber]["children"]) == 0 ) {
			expandIcon = "fa-circle";
			expandText = "";
			expandOptional = " style='font-size:8px;' ";
		}
		
		detaultElm = '<i class="fa '+expandIcon+' toggle" '+expandOptional+' title="'+expandText+'"></i> ' + detaultElm;
	}

	return '<td class="' + optionalClass + ' hc' + x + 'r' + y + ' ui-selectee"  data-principal="'+principal+'" data-type="'+headerObject.type+'" data-id="'+headerObject.id+'" data-set="'+setNumber+'" data-hierarchy="'+hierarchy+'" data-index="'+dimIndex+'" title="' + title + '"' + optionalArgs + ' style=\'' + optionalStyle + '\'>' + detaultElm + '</td>';
	
}

function updateGridWithDefinition() {
	showLoading("Updating the workview layout.");
	
	
	
	//update the titles table first then the worksheet table.
	var titles = workview_definition['positioning']['titles'];
	var rows = workview_definition['positioning']['rows'];
	var columns = workview_definition['positioning']['columns'];
	var hidden = workview_definition['positioning']['hidden'];
	
	//return the number of set rows and columns in the grid.
	var row_sets = workview_definition['row_sets'];
	var column_sets = workview_definition['column_sets'];
	
	var row_count = workview_definition['rows'];
	var column_count = workview_definition['columns'];
	
	
	if( !workview_definition['dimensions'] )
		workview_definition['dimensions'] = {};
		
	
	
	//workview_definition['dimensions'][dim.id]['sets'][parseInt(getDataset(sel[i], "set"))]['element']
	
	
	
	//check for dimensions on titles which have since been removed from the cube.
	for(var i=titles.length-1;i>=0;i--) {
		var title = titles[i];
		if( title.type != 'header' ) {
			var dim = dimById(title.id);
			if( dim == null ) {
				titles.splice(i,1);
			}
		}
	}
	for(var i=rows.length-1;i>=0;i--) {
		var title = rows[i];
		if( title.type != 'header' ) {
			var dim = dimById(title.id);
			if( dim == null ) {
				rows.splice(i,1);
			}
		}
	}
	for(var i=columns.length-1;i>=0;i--) {
		var title = columns[i];
		if( title.type != 'header' ) {
			var dim = dimById(title.id);
			if( dim == null ) {
				columns.splice(i,1);
			}
		}
	}
	
	//build the titles table
	var html = '';
	for(var i=0;i<titles.length;i++) {
		var title = titles[i];
		
		if( title.type == 'header' ) {
		
			html += '<tr><td>';
			html += '&nbsp;';
			html += '</td><td>';
			html += '&nbsp;';
			html += '</td><td>';
			html += '&nbsp;';
			html += '</td></tr>';
			
		} else {
			var dim = dimById(title.id);
			
			if( !workview_definition['dimensions'][dim.id] )
				workview_definition['dimensions'][dim.id] = {};
				
			
			var element = title.element;
			if( element == null || element == '' ) {
				if( dim['elements'].length > 0 ) {
					var h = hierarchyMetaByName(dim, "Default");
					if( h != null ) {
						if( h.root.length > 0 ) {
							element = h.root[0]['name'];
						}
					}
				}
			}
			if( element == null || element == '' ) {
				if( dim['elements'].length > 0 ) {
					element = dim['elements'][0]['name'];
				} else {
					element = "";
				}
			}
			
			
			html += '<tr class="titleTr"><td class="titleTdHeader">';
			html += '<b>' + dim.name + ': </b>';
			html += '</td><td class="titleTdValue">';
			
			
			
			//default
			var elements = dim['elements'];
			
			var multiSelection = false;
			var timeRangeSelection = false;
			
			if( workview_definition['dimensions'][dim.id]['set'] ) {
				if( workview_definition['dimensions'][dim.id]['set'][0] ) {
					if( workview_definition['dimensions'][dim.id]['set'][0]['instructions'] ) {
						var instructions = workview_definition['dimensions'][dim.id]['set'][0]['instructions'];
						for(var k=0;k<instructions.length;k++) {
							if( instructions[k].action == "multiple-selection" ) {
								multiSelection = true;
							}
							if( instructions[k].action == "time-range" ) {
								timeRangeSelection = true;
							}
						}
						
						
						if( workview_definition['dimensions'][dim.id]['set'][0]['instructions'][0]['set'] ) {
							elements = workview_definition['dimensions'][dim.id]['set'][0]['instructions'][0]['set'];
						}
					}
				}
			}
			
			//ensure its one or the other, not both.
			if( timeRangeSelection && multiSelection) {
				multiSelection = false;
			}
			
			if( titlesList != null ) {
				if( titlesList[title.id] ) {
					if( titlesList[title.id].elements.length > 0 ) {
						elements = titlesList[title.id].elements;
					}
				}
			} else {
				if( workview_definition.titles[title.id] ) {
					if( workview_definition.titles[title.id].elements.length > 0 ) {
						elements = workview_definition.titles[title.id].elements;
					}
				}
			}
			
			if( timeRangeSelection ) {
				var elmSelectionFrom = element;
				var elmSelectionTo = element;
				
				if( element.indexOf("[+]") > -1 ) {
					var elmSelection = element.split("[+]");
					elmSelectionFrom = elmSelection[0];
					elmSelectionTo = elmSelection[elmSelection.length-1];
				}
				
				
				html += '<select class="halfTitleSelection" id="title' + title.id + '">';
				for(var k=0;k<elements.length;k++) {
					var elm = elements[k];
					var textAdd = '';
					if( elm['name'] == elmSelectionFrom )
						textAdd = ' selected';
					if( elm['hierarchy'] ) {
						textAdd += ' data-hierarchy="' + elm['hierarchy'] + '"';
					} else {
						textAdd += ' data-hierarchy=""';
					}
					html += '<option' + textAdd + '>' + elm['name'] + '</option>';
				}
				html += '</select>';
				html += ' to ';
				html += '<select class="halfTitleSelection" id="title' + title.id + '_secondary">';
				for(var k=0;k<elements.length;k++) {
					var elm = elements[k];
					var textAdd = '';
					if( elm['name'] == elmSelectionTo )
						textAdd = ' selected';
					if( elm['hierarchy'] ) {
						textAdd += ' data-hierarchy="' + elm['hierarchy'] + '"';
					} else {
						textAdd += ' data-hierarchy=""';
					}
					html += '<option' + textAdd + '>' + elm['name'] + '</option>';
				}
				html += '</select>';
			} else {
				html += '<select class="titleSelection" id="title' + title.id + '">';
				for(var k=0;k<elements.length;k++) {
					var elm = elements[k];
					var textAdd = '';
					if( elm['name'] == element )
						textAdd = ' selected';
					if( elm['hierarchy'] ) {
						textAdd += ' data-hierarchy="' + elm['hierarchy'] + '"';
					} else {
						textAdd += ' data-hierarchy=""';
					}
					html += '<option' + textAdd + '>' + elm['name'] + '</option>';
				}
				html += '</select>';
			}
			
			html += '</td><td class="titleTdIcon">';
			if( is_editor ) {
				html += '<img class="titleTdIcon" data-id="' + title.id + '" data-set="0" src="/img/icons/16/ui-scroll-pane-tree.png"/>';
			}
			html += '</td></tr>';
		}
	}
	
	wvTblTitles.innerHTML = html;
	
	$('select.titleSelection').chosen({
    	width: '250px;',
    	height: '180px;'
    });
	$('select.halfTitleSelection').chosen({
    	width: '115px;',
    	height: '180px;'
    });
	
    $('select.titleSelection').change( function() { 
        saveTitleSelectionsAndUpdate(); 
    }); 
    $('select.halfTitleSelection').change( function() { 
        saveTitleSelectionsAndUpdate(); 
    }); 
    
	//update the worksheet table
	html = '';
	
	
	
	hideLoading();
	
	
	$( "img.titleTdIcon" ).bind( "touchstart click", function(e) {
		e.stopPropagation();
		$(".ui-selected").removeClass("ui-selected");
		$(".titleTdIcon-selected").removeClass("titleTdIcon-selected");

		$(this).addClass("titleTdIcon-selected");

		if (typeof(displayDimensionElementPicker) === "function") {
			displayDimensionElementPicker(getDataset( $(this)[0] , "id" ));
			hideRibbonFileMenu();
			cleanUpDataEntry();
		}
		return false;
	});
}

function indexOfInOptions(options, val) {
	for(var i=0;i<options.length;i++) {
		if( options[i].value == val ) {
			return i;
		}
	}
	return -1;
}

function gatherRangeElement(options, elementFrom, elementTo) {
	var indexFrom = indexOfInOptions(options, elementFrom);
	var indexTo = indexOfInOptions(options, elementTo);
	
	//force earlier to later ordering (based on list order).
	if( indexFrom > indexTo ) {
		var indexFromOld = indexFrom;
		indexFrom = indexTo;
		indexTo = indexFromOld;
	}
	
	var elementString = "";
	for(var i=indexFrom;i<=indexTo;i++) {
		if( i != indexFrom)
			elementString += "[+]";
		elementString += options[i].value;
	}
	return elementString;
}

function saveTitleSelectionsAndUpdate() {
	var titles = workview_definition['positioning']['titles'];
	for(var i=0;i<titles.length;i++) {
		var title = titles[i];
		var element = $('#title' + title.id + ' option:selected').text();
		
		if( $('#title' + title.id + '_secondary').length > 0 ) {
			var elementTimeRange = $('#title' + title.id + '_secondary option:selected').text();
			element=gatherRangeElement($('#title' + title.id + ' option'), element, elementTimeRange);
		}
		
		title.element = element;
	}
	
	workviewSave();
}


var tapped = null;
//build the rows and columns section of the table. 
//Recently shifted to run after workview.execute so we know how big the set expressions evaluated to.
function updateGridRowsAndColumns(results) {
	var html = '';
	//update the titles table first then the worksheet table.
	var titles = workview_definition['positioning']['titles'];
	var rows = workview_definition['positioning']['rows'];
	var columns = workview_definition['positioning']['columns'];
	
	//return the number of set rows and columns in the grid.
	var row_sets = workview_definition['row_sets'];
	var column_sets = workview_definition['column_sets'];
	var row_start = workview_definition['row_start'];
	var column_start = workview_definition['column_start'];
	
	if( !row_start)
		row_start = 0;
	if( !column_start)
		column_start = 0;
	
	var row_count = 0;
	var row_count_prior = 0;
	if( results['rows'][0] )
		row_count = results['rows'][0]['members'].length;
		
	var column_count = 0;
	var column_count_prior = 0;
	if( results['columns'][0] )
		column_count = results['columns'][0]['members'].length;
	
	//count the rows/columns in the sets prior to 0
	if( results['columns'].length > 0 ) {
		for(var i=0;i<results['columns'][0]['members'].length;i++) {
			if( parseInt(results['columns'][0]['members'][i]['set']) < Math.abs(column_start) ) {
				column_count_prior++;
			}
		}
		column_count -= column_count_prior;
	}
	
	if( results['rows'].length > 0 ) {
		for(var i=0;i<results['rows'][0]['members'].length;i++) {
			if( parseInt(results['rows'][0]['members'][i]['set']) < Math.abs(row_start) ) {
				row_count_prior++;
			}
		}
		row_count -= row_count_prior;
	}
	
	//for oncolumn loop (creating rows)
		//for onrow loop (creating columns)
		//for oncolumn sets (creating columns)
	
	
	for(var yy=0;yy<row_count_prior;yy++) {
		var y = yy + columns.length;
		var row = results['rows'][0]['members'][yy];
		
		html += '<tr>';
		
		for(var xx=0;xx<Math.abs(column_count_prior);xx++) {
			var x = xx + rows.length;
			
			var col = results['columns'][0]['members'][xx];
			
			html += '<td id="' + cell_prefix + 'c' + col['column'] + 'r' + row['row'] + '" class="c c' + x + ' r' + y + ' c' + xx + 'r' + yy + '" data-address="c' + xx + 'r' + yy + '" >&nbsp;</td>';
		}
		
		for(var x=0;x<results['rows'].length;x++) {
			
			html += updateGridWithDefinitionProduceHeader(results,results['rows'][x],x,y,yy,x,'rows');
			
			
		}
		for(var xx=Math.abs(column_count_prior);xx<column_count+column_count_prior;xx++) {
			var x = xx + rows.length;
			
			var col = results['columns'][0]['members'][xx];
			
			html += '<td id="' + cell_prefix + 'c' + col['column'] + 'r' + row['row'] + '" class="c c' + x + ' r' + y + ' c' + xx + 'r' + yy + '" data-address="c' + xx + 'r' + yy + '" >&nbsp;</td>';
		}
		
		html += '</tr>';
	}
	
	
	for(var y=0;y<results['columns'].length;y++) {
		//ensure the dimension is not hidden
		
		
		html += '<tr>';
		for(var xx=0;xx<Math.abs(column_count_prior);xx++) {
			var x = xx + rows.length;
			
			html += updateGridWithDefinitionProduceHeader(results,results['columns'][y],x,y,xx,y,'columns');
			
		}
		
		for(var x=0;x<results['rows'].length;x++) {
			html += '<td class="z hc' + x + 'r' + y + '">&nbsp;</td>';
		}
		
		for(var xx=Math.abs(column_count_prior);xx<column_count+column_count_prior;xx++) {
			var x = xx + rows.length;
			
			html += updateGridWithDefinitionProduceHeader(results,results['columns'][y],x,y,xx,y,'columns');
			
		}
		html += '</tr>';
	}
	
	//for onrow sets (creating rows)
		//for onrow loop (creating columns)
		//for oncolumn sets (creating columns)
		
	for(var yy=row_count_prior;yy<row_count+row_count_prior;yy++) {
		var y = yy + columns.length;
		
		var row = results['rows'][0]['members'][yy];
		
		html += '<tr>';
		
		for(var xx=0;xx<Math.abs(column_count_prior);xx++) {
			var x = xx + rows.length;
			
			var col = results['columns'][0]['members'][xx];
			
			html += '<td id="' + cell_prefix + 'c' + col['column'] + 'r' + row['row'] + '" class="c c' + x + ' r' + y + ' c' + xx + 'r' + yy + '" data-address="c' + xx + 'r' + yy + '" >&nbsp;</td>';
		}
		
		for(var x=0;x<results['rows'].length;x++) {
			
			html += updateGridWithDefinitionProduceHeader(results,results['rows'][x],x,y,yy,x,'rows');
			
			
		}
		for(var xx=Math.abs(column_count_prior);xx<column_count+column_count_prior;xx++) {
			var x = xx + rows.length;
			
			var col = results['columns'][0]['members'][xx];
			
			html += '<td id="' + cell_prefix + 'c' + col['column'] + 'r' + row['row'] + '" class="c c' + x + ' r' + y + ' c' + xx + 'r' + yy + '" data-address="c' + xx + 'r' + yy + '" >&nbsp;</td>';
		}
		
		html += '</tr>';
	}
	
	wvTbl.innerHTML = html;
	
	if( !isiOS()  ) {
		$( "#wvTbl" ).selectable({
		  filter: ".h,.c,.z,.spacer_rows,.spacer_columns",
		  distance: 1,
		  cancel: ".dataEntry,.dataEntryValidation",
		  selected: function( event, ui ) {
			var sel = $(".ui-selected");
			cleanUpDataEntry();
			if( sel.length == 1 ) {
				enableButtonsFor(getDataset(ui.selected, "type"));
			} else if( sel.length > 1 ) {
				enableButtonsFor('multiple');
			}
		  }
	  
		});
	}
	
	$( "img.titleTdIcon" ).unbind();
	$( "td.h,td.spacer_rows,td.spacer_columns" ).unbind();
	$( "td.c" ).unbind();
	$( "td.z" ).unbind();
	//$(document.body).unbind();
	
	
	$( "img.titleTdIcon" ).bind( "touchstart click", function(e) {
		e.stopPropagation();
		$(".ui-selected").removeClass("ui-selected");
		$(".titleTdIcon-selected").removeClass("titleTdIcon-selected");
		
		$(this).addClass("titleTdIcon-selected");
		
		if (typeof(displayDimensionElementPicker) === "function") {
			displayDimensionElementPicker(getDataset( $(this)[0] , "id" ));
			hideRibbonFileMenu();
			cleanUpDataEntry();
		}
		return false;
	});
	
	$( "i.toggle" ).unbind();
	$( "i.toggle" ).bind( "touchend click", function(e) {
		toggle(this.parentNode);
	});
	
	$( "td.h,td.spacer_rows,td.spacer_columns" ).bind( "touchend click", function(e) {
		
		if( !keys[91] && !keys[17] && !keys[16] ) {
			$(".ui-selected").removeClass("ui-selected");
		}
		
		$(this).addClass("ui-selected");
		hideRibbonFileMenu();
		cleanUpDataEntry();
		
		var sel = $(".ui-selected");
		if( sel.length == 1 ) {
			enableButtonsFor(getDataset(sel[0], "type"));
	  	} else if( sel.length > 1 ) {
	  		enableButtonsFor('multiple');
	  	}
		e.stopPropagation();
		return false;
	});
	$( "td.c" ).bind( "touchend click", function(e) {
		
		if( getDataset(this,"address") == selCellAddress ) {
			return true;
		}
		
		if( formula_editing ) {
			formulaAddReference(this);
		}
		
		if( !keys[91] && !keys[17] && !keys[16] ) {
			$(".ui-selected").removeClass("ui-selected");
		}
		
		$(this).addClass("ui-selected");
		hideRibbonFileMenu();
		cleanUpDataEntry();
		
		var sel = $(".ui-selected");
		if( sel.length == 1 ) {
			enableButtonsFor(getDataset(sel[0], "type"));
	  	} else if( sel.length > 1 ) {
	  		enableButtonsFor('multiple');
			
	  	}
		e.stopPropagation();
		return false;
	});
	
	
	
	$( "td.h,td.spacer_rows,td.spacer_columns" ).bind( "dblclick", function(e) {
		e.stopPropagation();
		hideRibbonFileMenu();
		if (typeof(displayDimensionElementPicker) === "function") {
			var dims = getDimensionsSelected();
			if( dims.length > 0 ) {
				displayDimensionElementPicker();
			} else {
				displayHeadingEditor();
			}
		}
		return false;
	});
	
	if( isiOS() ) {
		$( "td.h,td.spacer_rows,td.spacer_columns" ).doubletap(
			/** doubletap-dblclick callback */
			function(event){
				hideRibbonFileMenu();
				if (typeof(displayDimensionElementPicker) === "function") {
					var dims = getDimensionsSelected();
					if( dims.length > 0 ) {
						displayDimensionElementPicker();
					} else {
						displayHeadingEditor();
					}
				}
				event.stopPropagation();
				return false;
			},
			/** touch-click callback (touch) */
			function(event){
				//single
			},
			1
		);
	
		
		$( "td.c" ).doubletap(
			/** doubletap-dblclick callback */
			function(event){
				event.stopPropagation();
				startDataEntry(event.currentTarget);
			},
			/** touch-click callback (touch) */
			function(event){
				//single
			},
			1
		);
	} else {
		$( "td.c" ).bind( "dblclick", function(e) {
				startDataEntry(event.currentTarget);
				e.stopPropagation();
			}
		);
	}
	
	$( "td.z" ).bind( "click", function(e) {
		e.stopPropagation();
		unselect();
		cleanUpDataEntry();
		enableButtonsFor('');
		return false;
	});
    
    $( "div.workview" ).bind( "click", function() {
		hideRibbonFileMenu();
		
		if( selCellAddress == null )
			cleanUpDataEntry();
		else
			return true;
			
		enableButtonsFor('');
		return false;
	});
    
     //workviewBindings();
	/*
    
   
    
	
	*/
	/*
	if( is_editor ) {
		$( document ).tooltip({
			items : "td.h"
		});
	}
	*/
	
	$(document.body).bind( "keydown", function(event) {
		if( $("select.dataEntryValidation,input.dataEntry").length != 0 ) {
			return true;
		}
		
		if( $("#dlgColumnWidth").dialog( "isOpen" ) == true ) {
			return true;
		}		
		if( $("#dlgFormulaTracer").dialog( "isOpen" ) == true ) {
			return true;
		}		
		if( $("#dlgDimensionEditor").dialog( "isOpen" ) == true ) {
			return true;
		}		
		if( $("#dlgFormulaEditor").dialog( "isOpen" ) == true ) {
			return true;
		}		
		if( $("#dlgConditionalFormatEditor").dialog( "isOpen" ) == true ) {
			return true;
		}
		
		
		if( $("#dialog-dimension-element-select").dialog( "isOpen" ) == true ) {
			return true;
		}
		
		
		if (event.keyCode == '13' || event.keyCode == 13) {
			var sel = $(".ui-selected");
			if( sel.length == 1 ) {
				if( getDataset(sel[0],"elements" ) != "" ) {
					startDataEntry(sel[0]);
				}
			}
		} else if (event.keyCode == '27' || event.keyCode == 27) {
			
		} else if (event.keyCode == '40' || event.keyCode == 40) {	//down
			selectRelativeCellNonEntry(0,1);
			return false;
		} else if (event.keyCode == '38' || event.keyCode == 38) {	//up
			selectRelativeCellNonEntry(0,-1);
			return false;
		} else if (event.keyCode == '39' || event.keyCode == 39) {
			selectRelativeCellNonEntry(1,0);
			return false;
		} else if (event.keyCode == '37' || event.keyCode == 39) {
			selectRelativeCellNonEntry(-1,0);
			return false;
		} /*else if (event.keyCode == '86' || event.keyCode == 86) { 	//paste
			
		} else if (event.keyCode == '67' || event.keyCode == 67) {	//copy
			copySelectedCellsToClipboard();
			return false;
		} else if (event.keyCode == '17' || event.keyCode == 17 || event.keyCode == '91' || event.keyCode == 91) {	//ctrl or command
			ctrlDown = true;
		}*/
		event.stopPropagation();
	});
	
	$(document.body).bind( 'copy', function( e ) {
		if( $("#dlgColumnWidth").dialog( "isOpen" ) == true ) {
			return true;
		}		
		if( $("#dlgFormulaTracer").dialog( "isOpen" ) == true ) {
			return true;
		}		
		if( $("#dlgDimensionEditor").dialog( "isOpen" ) == true ) {
			return true;
		}		
		if( $("#dlgFormulaEditor").dialog( "isOpen" ) == true ) {
			return true;
		}		
		if( $("#dlgHeaderEditor").dialog( "isOpen" ) == true ) {
			return true;
		}		
		if( $("#dlgConditionalFormatEditor").dialog( "isOpen" ) == true ) {
			return true;
		}
		
		if( $("#dialog-dimension-element-select").dialog( "isOpen" ) == true ) {
			return true;
		}
		
		var sel = $(".ui-selected");
		if( sel.length > 0  ) {
			
			copySelectedCellsToClipboard();
	
		}
		e.stopPropagation();
	} );
	
	$(document.body).bind( 'paste', function( e ) {
		if( $("#dlgColumnWidth").dialog( "isOpen" ) == true ) {
			return true;
		}		
		if( $("#dlgFormulaTracer").dialog( "isOpen" ) == true ) {
			return true;
		}		
		if( $("#dlgDimensionEditor").dialog( "isOpen" ) == true ) {
			return true;
		}			
		if( $("#dlgConditionalFormatEditor").dialog( "isOpen" ) == true ) {
			return true;
		}
		if( $("#dlgFormulaEditor").dialog( "isOpen" ) == true ) {
			return true;
		}		
		if( $("#dialog-dimension-element-select").dialog( "isOpen" ) == true ) {
			return true;
		}		
		if( $("#dlgHeaderEditor").dialog( "isOpen" ) == true ) {
			return true;
		}	
		
		var sel = $(".ui-selected");
		if( sel.length == 1 ) {
			if( sel.hasClass("c") ) {
				var cd = e.originalEvent.clipboardData;
				pasteSelectedCellsFromClipboard(cd.getData("text/plain"));
			}
		}
		
		e.stopPropagation();
		return true;
	} );
}



var ctrlDown = false;

function dismissTooltips() {
	/*
	$( document ).tooltip({
		items : "td.h"
	}).tooltip( "destroy" );
	*/
}



function unselect() {
	$('#wvTbl .ui-selected').removeClass('ui-selected');
	hideRibbonFileMenu();
}

function hideRibbonFileMenu() {
	if( window.myRibbon ) {
		$(".acidjs-ribbon-tool-file-dropdown")[0].setAttribute('class','acidjs-ribbon-tool-dropdown acidjs-ribbon-tool-file-dropdown acidjs-ribbon-hidden');
		$(".acidjs-ribbon-tool-dropdown").addClass('acidjs-ribbon-hidden');
	}
}

var followup = null;

function updateMetadata(follow_up_task) {
	var tasks = {"tasks": [
		{"task": "workview.metadata", "id" : model_detail.id, "workviewid" : workview_definition['id'] }
	]};
	followup = follow_up_task;
	query("model.service",tasks,updateMetadataCallback);	
}
function updateMetadataCallback(data) {
	var results = JSON.parse(data);
	workview_metadata = results['results'][0]['metadata'];
	
	if(followup != null ) 
		followup();
	followup = null;
}



function titleUpdate(dimId, value) {
	var titles = workview_definition['positioning']['titles'];
	for(var i=0;i<titles.length;i++) {
		var title = titles[i];
		
		if( title.type == 'header' ) {
			
			
		} else {
			var dimElmSelection = $('#title' + title.id + ' option:selected').text();
			
			if( dimId == title.id ) {
				$('#title' + title.id).val(value);
				$('#title' + title.id).trigger("chosen:updated");
				saveTitleSelectionsAndUpdate();
			}
			
		}
	}
	
	return "";
}



function titleElementByDimensionId(dimId) {
	var titles = workview_definition['positioning']['titles'];
	for(var i=0;i<titles.length;i++) {
		var title = titles[i];
		
		if( title.type == 'header' ) {
			
			
		} else {
			var dimElmSelection = $('#title' + title.id + ' option:selected').text();
			
			if( dimId == title.id ) {
				return dimElmSelection;
			}
			
		}
	}
	
	return "";
}


function workviewChangeDataCallback(data) {
	var results = JSON.parse(data);
	if( results['results'][0]['result'] == 1 ) {
		//workview saved successfully
		
		if( results['results'][1]['result'] == 1 ) {
			//the workview executed successfully
			
			updateWorkviewData(results['results'][1]);
			
			
			if( parent ) {
				if (typeof( parent.refreshOthers ) === "function") {
					parent.refreshOthers(getParameterByName("page") + getParameterByName("workview"));
				}
			}	
			
			if( results['results'].length == 3 ) {
				presentChart(results['results'][2]);
			}
		} else {
			//the workview failed to execute
		}
	} else {
		//the workview save failed, typically this is only happening if the session has expired.
		var error = results['results'][0]['message'];
		if( !error )
			error = results['results'][0]['error'];
		
		alert(error);
	}
}


//dialog forms for dimensions
var dimAdd = "Add a New Dimension";
var dimAddExisting = "Add an Existing Dimension";
var dimHeader = "Custom Header";




var alwaysOn = ["manage-dimensions","refresh-workview"];
var headerFunctions = [];
var cellFunctions = ["data-validation"];

function enableButtonsFor(cellType) {
	if( !document.getElementById('btnValidation') )
		return;
	
	if( cellType == "header" ) {
	
	} else if( cellType == "number" || cellType == "string" ) {
		document.getElementById('btnValidation').style.color = '#fff';
	} 
	
}





function displayColumnWidthDialog(headerCell) {
	//if the header is on columns then save per "set-element" otherwise save for the z class item
	var width = parseInt($(headerCell).width())+1;
	document.getElementById('columnWidth').value = width;
	$( "#dlgColumnWidth" ).dialog({
      resizable: false,
      height:140,
      width:350,
      autoOpen: true,
      modal: true,
      buttons: {
			Save: function() {
				saveColumnWidth(headerCell);
				$( this ).dialog( "close" );
			},
			Cancel: function() {
				$( this ).dialog( "close" );
			}
		}
     });
}

function saveColumnWidth(headerCell) {
	var dimId = getDataset(headerCell,"id");
	var dimType = getDataset(headerCell,"type");
	var position = getDataset(headerCell,"position");
	var dim = dimById(dimId);
	
	if( dimType == "header" ) {
		var definitionposition = parseInt(getDataset(headerCell,"definitionposition"));
		if( position == "row" ) {
			//one column
			var header = workview_definition['positioning'][position + "s"][definitionposition];
			header['width'] = document.getElementById('columnWidth').value;
		} else {
			var column = parseInt(getDataset(headerCell,"column"));
			var set = parseInt(getDataset(headerCell,"set"));
			
			var headerDef = workview_definition['positioning'][position + "s"][definitionposition];
			if( !headerDef['members'] )
				headerDef['members'] = [];
			if( !headerDef['members'][set] ) 
				headerDef['members'][set] = {"index-formula" : [], "set-formula" : {}};
			
			if( column >= headerDef['members'][set]["index-formula"].length ) {
				headerDef['members'][set]["index-formula"][column] = {};
			}
			headerDef['members'][set]["index-formula"][column]['width'] = document.getElementById('columnWidth').value;
		}
	} else {
		if( position == "row" ) {
			workview_definition['dimensions'][dimId]['width'] = document.getElementById('columnWidth').value;
		} else {
			var setNo = getDataset(headerCell,"set");
			var setElement = getDataset(headerCell,"element");
			if( !setElement) 
				setElement = "blank";
			
			if( !workview_definition['dimensions'][dimId]['set'][setNo]['styles'] ) {
				workview_definition['dimensions'][dimId]['set'][setNo]['styles'] = {};
			}
		
			if( !workview_definition['dimensions'][dimId]['set'][setNo]['styles'][cleanStr(setElement)] ) {
				workview_definition['dimensions'][dimId]['set'][setNo]['styles'][cleanStr(setElement)] = {};
			}
			
			workview_definition['dimensions'][dimId]['set'][setNo]['styles'][cleanStr(setElement)]['Width'] = document.getElementById('columnWidth').value;
			
		}
	}
	
	workviewSave();
	
}

var traceCell = null;
function displayFormulaTrace(item) {
	traceCell = item;
	var elements = getDataset(traceCell,"elements");
	var tasks = {"tasks": [
			{"task": "cube.value.trace", "id" : model_detail.id, "cubeid" : workview_definition['cube'], "elements" : elements }
		]};
	
	query("model.service",tasks,displayFormulaTraceCallback);
}

function formatTracerItem(trace) {
	if( trace == null )
		return;
	
	var html = "";
	var event = trace['event'];		
	var location = trace['location'];
	var description = trace['description'];
	var result = trace['result'];
	
	html += "<tr>";
	if( event == "relative reference" ) {
		location = location.replace(/\|/g,",&nbsp;"); 
		html += "<td class='traceValue'>" + event + "</td>";
		html += "<td class='traceValue'>" + location + "</td>";
		html += "<td class='traceValue' style='text-align:right;'>" + result + "</td>";
	} else if( event == "remote reference" ) {
		location = location.replace(/\|/g,",&nbsp;"); 
		html += "<td class='traceValue' style='background-color: #EFE;'>" + event + "</td>";
		html += "<td class='traceValue' style='background-color: #EFE;'>" + location + "</td>";
		html += "<td class='traceValue' style='background-color: #EFE;text-align:right;'>" + result + "</td>";
	} else if( event.toLowerCase() == "aggregation" || event.toLowerCase() == "consolidation" ) {
		location = location.replace(/\|/g,",&nbsp;"); 
		html += "<td class='traceValue'>" + event + "</td>";
		html += "<td class='traceValue'>" + location + "</td>";
		html += "<td class='traceValue' style='text-align:right;'>" + result + "</td>";
	} else if( event == "calculation" ) {
		html += "<td class='traceValue'>" + event + "</td>";
		html += "<td class='traceValue'>" + description + "</td>";
		html += "<td class='traceValue' style='text-align:right;'>" + result + "</td>";
	} else {
		html += "<td class='traceValue'>" + event + "</td>";
		html += "<td class='traceValue'>" + description + "</td>";
		html += "<td class='traceValue' style='text-align:right;'>" + result + "</td>";
	}
	
	html += "</tr>";
	return html;
}

function itemsToTable(items) {
	var html = "<table><tr>";
	for(var i=0;i<items.length;i++) {
		html+="<td>" + items[i] + "</td>";
	}
	html+="</tr></table>";
	return html;
}

function displayFormulaTraceCallback(data) {
	var results = JSON.parse(data);
	var elements = getDataset(traceCell,"elements");
	var html = "";
	if( results['results'][0]['result'] == 1 ) {
		var type = results['results'][0]['type'];
		var elms = elements.replace(/\|/g,",&nbsp;"); 
		var value = "";

		html += "<ul>";
		if( type == "string" ) {
			value = results['results'][0]['string'];
			html += "<li>Value: \"" + results['results'][0]['string'] + "\"</li>";
		} else { 
			value = results['results'][0]['number'];
			html += "<li>Value: " + results['results'][0]['number'] + "</li>";
		}
		html += "<li>Location: " + elms + "</li>";
		html += "</ul>";
		
		html += "<table id='tracer' style='width: 100%;'>";
		html += "<tr><td class='traceHeader'>Event</td><td class='traceHeader'>Description</td><td class='traceHeader'>Outcome</td></tr>";
		var tracer = results['results'][0]['trace'];
		for(var i=0;i<tracer.length;i++ ) {
			var trace = tracer[i];
			html += formatTracerItem(trace);
		}
		html += "<tr><td class='traceHeader'></td><td class='traceHeader' style='text-align:right;'><b>Result: </b></td><td class='traceHeader' style='text-align:right;'><b>" + value + "</b></td></tr>";
		html += "</table>";
		
	} else {
    	html += "Failed to execute the trace. Please refresh the workview then try again.";
    }
	
	document.getElementById('divCellTraceResults').innerHTML = html;
	
	$( "#dlgFormulaTracer" ).dialog({
      resizable: true,
      height:300,
      width:640,
      autoOpen: true,
      modal: false,
      buttons: {
       	 	'Close': function(event) {
            	$( this ).dialog( "close" );
       	 	}
    	}
    });
    
}


function btnDisableSetExpressions() {
	if( workview_execute_options["disable-set-instructions"] == "1" ) {
		$("#btnDisableSetExp").removeClass('fa-pause');
		$("#btnDisableSetExp").addClass('fa-play');
		workview_execute_options["disable-set-instructions"] = "0";
	} else {
		workview_execute_options["disable-set-instructions"] = "1";
		$("#btnDisableSetExp").removeClass('fa-play');
		$("#btnDisableSetExp").addClass('fa-pause');
	}
	workviewSave();
	
}

//ensure all dimensions on similar axis have the same number of sets.
function checkDefinitionForErrors() {
	
	//sort dimensions by their axis
	var onrows = [];
	var oncolumns = [];
	for (dimension in workview_definition['dimensions']) {
		if( workview_definition['dimensions'][dimension]['positioning'] == "columns" ) {
			oncolumns[oncolumns.length] = workview_definition['dimensions'][dimension];
		} else if( workview_definition['dimensions'][dimension]['positioning'] == "rows" ) {
			onrows[onrows.length] = workview_definition['dimensions'][dimension];
		}
	}
	
	//call helper function
	balanceSetsInAxis(oncolumns);
	balanceSetsInAxis(onrows);
	
}

function hierarchyMetaByName(dim, hierarchyName) {
	for(var i=0;i<dim.hierarchies.length;i++) {
		var hier = dim.hierarchies[i];
		if( hier.name == hierarchyName ) {
			return hier;
		}
	}
	return null;
}

function balanceSetsInAxis(axis) {
	var maxSetCount = 0;
	for(var i=0;i<axis.length;i++) {
		var dim = axis[i];
		var setCount = 0;
		if( dim['set'] ) {
			setCount = dim['set'].length;
		}
		if( setCount > maxSetCount ) {
			maxSetCount = setCount;
		}		
	}
	
	for(var i=0;i<axis.length;i++) {
		var dim = axis[i];
		if( dim['set'] ) {
			var setCount = dim['set'].length;
			while( dim['set'].length < maxSetCount-1 ) {
				dim['set'][dim['set'].length] = {"instructions":[],"element":null};
			}	
	
		}
	}
	
}


function workviewExport(format) {
	var titles_collection = [];
	var titles = workview_definition['positioning']['titles'];
	
	if( typeof format === 'undefined' ) {
		format = "XLSX";
	}
	
	
	for(var i=0;i<titles.length;i++) {
		var title = titles[i];
		
		if( title.type == 'header' ) {
			
			titles_collection[titles_collection.length] = {
				'type' : 'header',
				'id' : '',
				'element' : ''
			};
		} else {
			var listItem = $('#title' + title.id + ' option:selected');
			var dimElmSelection = listItem.text();
			
			titles_collection[titles_collection.length] = {
				'type' : 'dimension',
				'id' : title.id,
				'element' : dimElmSelection,
				'hierarchy' : getDataset(listItem[0],"hierarchy") 
			};
		}
	}
	var tasks = {"tasks": [
		{"task": "workview.execute.export", "id" : model_detail.id, "workviewid" : workview_definition['id'], "titles" : titles_collection, "options" : workview_execute_options, "format" : format }
		
	]};
	workviewExportPerform(tasks);
}


function workviewExportCallback(data) {
	var results = JSON.parse(data);
	if( results['results'][0]['result'] == 1 ) {
		//the workview exported successfully
		var name = results['results'][0]['name'];
		var data = results['results'][0]['data'];
		var contenttype = results['results'][0]['content-type'];
		
		$("#ct").val(contenttype);
		$("#nm").val(name);
		$("#data").val(data);

		document.forms.filedownload.submit();
	}
}
