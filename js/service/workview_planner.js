
var workview_definition = null;




if( workview_definition_loaded != null ) {
	workview_definition = workview_definition_loaded;
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
	wv.innerHTML = "<table id='wvTblTitles' class='grid'></table><table id='wvTbl' class='grid'></table>";
	wvTblTitles = document.getElementById('wvTblTitles');
	wvTbl = document.getElementById('wvTbl');
	
	if( getDefinitionPosition('positioning','rows') == null ) {
		//bring up the manage dimensions dialog.
		
		displayManageDimensions();
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
			
			var insert = at + Math.abs(axisStart);
			workview_definition['dimensions'][dim.id]['set'].splice(insert,0,{});
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
				workview_definition['dimensions'][dim.id]['set'][k] = {};
			}
			for(var k=len-1;k>axisSetSize-1;k--) { 
				workview_definition['dimensions'][dim.id]['set'].splice(k,1);
			}
		}
	}
	
	workviewSave();
}

function updateWorkviewData(results) {
	
	//return the position collections
	var titles = workview_definition['positioning']['titles'];
	var rows = workview_definition['positioning']['rows'];
	var columns = workview_definition['positioning']['columns'];
	
	//return the number of set rows and columns in the grid.
	
	var row_count = 0;
	if( results['rows'][0] )
		row_count = results['rows'][0].length;
		
	var column_count = 0;
	if( results['columns'][0] )
		column_count = results['columns'][0].length;
	
	for(var yy=0;yy<row_count;yy++) {
		var y = yy + columns.length;
		
		for(var xx=0;xx<column_count;xx++) {
			var x = xx + rows.length;
			
			var cellAddress = 'c' + (xx) + 'r' + (yy);
			var cell = results[cellAddress];
			
			if( cell ) {
				var cellObj = document.getElementById(cellAddress);
				if( cellObj ) {
					$(cellObj).removeClass("consolidation");
					$(cellObj).removeClass("formula");
					
					if( cell.value ) {
						if( typeof cell.value.number != 'undefined' ) {
							setDataset(cellObj, "type", "number");
							setDataset(cellObj, "value", cell.value.number);
							setDataset(cellObj, "format", "#,##0.00");
							setDataset(cellObj, "validation", "");
						} else if( typeof cell.value.string != 'undefined') {
							setDataset(cellObj, "type", "string");
							setDataset(cellObj, "value", cell.value.string);
							setDataset(cellObj, "validation", "");
						}
						
						if( cell.value.formula ) {
							if( cell.value.formula.length > 0 ) {
								setDataset(cellObj, "formula", JSON.stringify( cell.value.formula[0] ) );
							}
						}
						
						if( cell.value.status ) {
							var status = cell.value.status;
							$(cellObj).addClass(status);
						}
						
						setDataset(cellObj, "elements", cell.elements.join("|"));
						resetContentsOfCell(cellObj);
					} 
				}
			}
			
		}
		
	}
	
	
	
}

function updateGridWithDefinitionProduceHeader(results, headerObject,x,y,elmNumber,dimIndex, axis) {

	var optionalArgs = "";
	var optionalStyle = "";
	var detaultElm = "&nbsp;";
	var setNumber = 0;
	var levelNumber = 0;
	var minWidth = null;

	var title = dimHeader;
	if( headerObject[dimIndex].type == 'dimension' ) {
		var dim = dimById(headerObject[dimIndex].id);
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
			if( headerObject[i].type == 'dimension' )
				dimPosition++;
		}
		
		detaultElm = results[axis][dimPosition][elmNumber]['name'];
		if( !detaultElm ) {
			detaultElm = "&nbsp;";
		} else {
			optionalArgs = " data-element='" + detaultElm + "'";
		}
		
		setNumber = results[axis][dimPosition][elmNumber]['set'];
		levelNumber = results[axis][dimPosition][elmNumber]['level'];
		
		if( axis == "columns" ) {
			if( workview_definition['dimensions'][dim.id]['set'][setNumber] ) {
				if( workview_definition['dimensions'][dim.id]['set'][setNumber]['styles'] ) {
					if( workview_definition['dimensions'][dim.id]['set'][setNumber]['styles'][cleanStr(detaultElm)] ) {
						minWidth = workview_definition['dimensions'][dim.id]['set'][setNumber]['styles'][cleanStr(detaultElm)]['Width'];
					}
				}
			}
		}
		
		title += " (Set " + (parseInt(setNumber)+1) + ")";
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

	return '<td class="h hc' + x + 'r' + y + ' ui-selectee"  data-type="'+headerObject[dimIndex].type+'" data-id="'+headerObject[dimIndex].id+'" data-set="'+setNumber+'" data-index="'+dimIndex+'" title="' + title + '"' + optionalArgs + ' style=\'' + optionalStyle + '\'>' + detaultElm + '</td>';
	
}

function updateGridWithDefinition() {
	showLoading("Updating the workview layout.");
	
	//update the titles table first then the worksheet table.
	var titles = workview_definition['positioning']['titles'];
	var rows = workview_definition['positioning']['rows'];
	var columns = workview_definition['positioning']['columns'];
	
	//return the number of set rows and columns in the grid.
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
					element = dim['elements'][0]['name'];
				} else {
					element = "";
				}
			}
			
			html += '<tr class="titleTr"><td class="titleTdHeader">';
			html += '<b>' + dim.name + ': </b>';
			html += '</td><td class="titleTdValue">';
			
			html += '<select class="titleSelection" id="title' + title.id + '">';
			
			
			//default
			var elements = dim['elements'];
			if( workview_definition['dimensions'][dim.id]['set'] ) {
				if( workview_definition['dimensions'][dim.id]['set'][0] ) {
					if( workview_definition['dimensions'][dim.id]['set'][0]['instructions'] ) {
						if( workview_definition['dimensions'][dim.id]['set'][0]['instructions'][0]['set'] ) {
							elements = workview_definition['dimensions'][dim.id]['set'][0]['instructions'][0]['set'];
						}
					}
				}
			}
			
			
			
			for(var k=0;k<elements.length;k++) {
				var elm = elements[k];
				var textAdd = '';
				if( elm['name'] == element )
					textAdd = ' selected';
				
				html += '<option' + textAdd + '>' + elm['name'] + '</option>';
			}
				
			html += '</select>';
			
			html += '</td><td class="titleTdIcon">';
			html += '<img class="titleTdIcon" data-id="' + title.id + '" data-set="0" src="/img/icons/16/ui-scroll-pane-tree.png"/>';
			html += '</td></tr>';
		}
	}
	
	wvTblTitles.innerHTML = html;
	
	$('select.titleSelection').chosen({
    	width: '200px;',
    	height: '180px;'
    });
	
    $('select.titleSelection').change( function() { 
        saveTitleSelectionsAndUpdate(); 
		
    }); 
    
	//update the worksheet table
	html = '';
	
	
	
	hideLoading();
	
	
	$( "img.titleTdIcon" ).bind( "touchstart click", function() {
		$(".ui-selected").removeClass("ui-selected");
		$(".titleTdIcon-selected").removeClass("titleTdIcon-selected");

		$(this).addClass("titleTdIcon-selected");

		displayDimensionElementPicker(getDataset( $(this)[0] , "id" ));
		hideRibbonFileMenu();
		cleanUpDataEntry();
		return false;
	});
}

function saveTitleSelectionsAndUpdate() {
	var titles = workview_definition['positioning']['titles'];
	for(var i=0;i<titles.length;i++) {
		var title = titles[i];
		var element = $('#title' + title.id + ' option:selected').text();
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
		row_count = results['rows'][0].length;
		
	var column_count = 0;
	var column_count_prior = 0;
	if( results['columns'][0] )
		column_count = results['columns'][0].length;
	
	//count the rows/columns in the sets prior to 0
	if( results['columns'].length > 0 ) {
		for(var i=0;i<results['columns'][0].length;i++) {
			if( parseInt(results['columns'][0][i]['set']) < Math.abs(column_start) ) {
				column_count_prior++;
			}
		}
		column_count -= column_count_prior;
	}
	
	if( results['rows'].length > 0 ) {
		for(var i=0;i<results['rows'][0].length;i++) {
			if( parseInt(results['rows'][0][i]['set']) < Math.abs(row_start) ) {
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
		
		html += '<tr>';
		
		for(var xx=0;xx<Math.abs(column_count_prior);xx++) {
			var x = xx + rows.length;
			
			html += '<td id="c' + xx + 'r' + yy + '" class="c c' + x + ' r' + y + ' c' + xx + 'r' + yy + '" data-address="c' + xx + 'r' + yy + '" >&nbsp;</td>';
		}
		
		for(var x=0;x<rows.length;x++) {
			
			html += updateGridWithDefinitionProduceHeader(results,rows,x,y,yy,x,'rows');
			
			
		}
		for(var xx=Math.abs(column_count_prior);xx<column_count+column_count_prior;xx++) {
			var x = xx + rows.length;
			
			html += '<td id="c' + xx + 'r' + yy + '" class="c c' + x + ' r' + y + ' c' + xx + 'r' + yy + '" data-address="c' + xx + 'r' + yy + '" >&nbsp;</td>';
		}
		
		html += '</tr>';
	}
	
	
	for(var y=0;y<columns.length;y++) {
		html += '<tr>';
		for(var xx=0;xx<Math.abs(column_count_prior);xx++) {
			var x = xx + rows.length;
			
			html += updateGridWithDefinitionProduceHeader(results,columns,x,y,xx,y,'columns');
			
		}
		
		for(var x=0;x<rows.length;x++) {
			html += '<td class="z hc' + x + 'r' + y + '">&nbsp;</td>';
		}
		
		for(var xx=Math.abs(column_count_prior);xx<column_count+column_count_prior;xx++) {
			var x = xx + rows.length;
			
			html += updateGridWithDefinitionProduceHeader(results,columns,x,y,xx,y,'columns');
			
		}
		html += '</tr>';
	}
	
	//for onrow sets (creating rows)
		//for onrow loop (creating columns)
		//for oncolumn sets (creating columns)
		
	for(var yy=row_count_prior;yy<row_count+row_count_prior;yy++) {
		var y = yy + columns.length;
		
		html += '<tr>';
		
		for(var xx=0;xx<Math.abs(column_count_prior);xx++) {
			var x = xx + rows.length;
			
			html += '<td id="c' + xx + 'r' + yy + '" class="c c' + x + ' r' + y + ' c' + xx + 'r' + yy + '" data-address="c' + xx + 'r' + yy + '" >&nbsp;</td>';
		}
		
		for(var x=0;x<rows.length;x++) {
			
			html += updateGridWithDefinitionProduceHeader(results,rows,x,y,yy,x,'rows');
			
			
		}
		for(var xx=Math.abs(column_count_prior);xx<column_count+column_count_prior;xx++) {
			var x = xx + rows.length;
			
			html += '<td id="c' + xx + 'r' + yy + '" class="c c' + x + ' r' + y + ' c' + xx + 'r' + yy + '" data-address="c' + xx + 'r' + yy + '" >&nbsp;</td>';
		}
		
		html += '</tr>';
	}
	
	
	wvTbl.innerHTML = html;
	
	if( !isiOS() ) {
		$( "#wvTbl" ).selectable({
		  filter: ".h,.c,.z",
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
	
	$( "img.titleTdIcon" ).bind( "touchstart click", function() {
		$(".ui-selected").removeClass("ui-selected");
		$(".titleTdIcon-selected").removeClass("titleTdIcon-selected");
		
		$(this).addClass("titleTdIcon-selected");
		
		displayDimensionElementPicker(getDataset( $(this)[0] , "id" ));
		hideRibbonFileMenu();
		cleanUpDataEntry();
		return false;
	});
	
	$( "td.h" ).bind( "touchend click", function() {
		
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
		
		return false;
	});
	$( "td.c" ).bind( "touchend click", function() {
		
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
		
		return false;
	});
	
	
	
	$( "td.h" ).bind( "dblclick", function() {
		hideRibbonFileMenu();
		displayDimensionElementPicker();
		return false;
	});
	
	$( "td.h" ).doubletap(
		/** doubletap-dblclick callback */
		function(event){
			hideRibbonFileMenu();
			displayDimensionElementPicker();
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
			startDataEntry(event.currentTarget);
		},
		/** touch-click callback (touch) */
		function(event){
			//single
		},
		1
	);
	
	$( "td.z" ).bind( "click", function() {
		unselect();
		cleanUpDataEntry();
		enableButtonsFor('');
		return false;
	});
	
	$( "body" ).bind( "click", function() {
		hideRibbonFileMenu();
		
		if( selCellAddress == null )
			cleanUpDataEntry();
		else
			return true;
			
		enableButtonsFor('');
		return false;
	});
	
	$( document ).tooltip({
		items : "td.h"
	});
	
	/*
	$(document.body).bind( "keyup", function(event) {
		if (event.keyCode == '17' || event.keyCode == 17 || event.keyCode == '91' || event.keyCode == 91) { 	//ctrl or command
			ctrlDown = false;
		}
	});
	*/
	$(document.body).bind( "keydown", function(event) {
		if( $("select.dataEntryValidation,input.dataEntry").length != 0 ) {
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
	});
	
	$(document.body).bind( 'copy', function( e ) {
		
		var sel = $(".ui-selected");
		if( sel.length > 0  ) {
			
			copySelectedCellsToClipboard();
	
		}
	} );
	
	$(document.body).bind( 'paste', function( e ) {
		var sel = $(".ui-selected");
		if( sel.length == 1 ) {
			if( sel.hasClass("c") ) {
				var cd = e.originalEvent.clipboardData;
				pasteSelectedCellsFromClipboard(cd.getData("text/plain"));
			}
		}
		return true;
	} );
}



var ctrlDown = false;

function dismissTooltips() {
	$( document ).tooltip({
		items : "td.h"
	}).tooltip( "destroy" );
}



function unselect() {
	$('#wvTbl .ui-selected').removeClass('ui-selected');
	hideRibbonFileMenu();
}

function hideRibbonFileMenu() {
	$(".acidjs-ribbon-tool-file-dropdown")[0].setAttribute('class','acidjs-ribbon-tool-dropdown acidjs-ribbon-tool-file-dropdown acidjs-ribbon-hidden');
	$(".acidjs-ribbon-tool-dropdown").addClass('acidjs-ribbon-hidden');
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



function workviewChangeData() {
	var titles_collection = [];
	var titles = workview_definition['positioning']['titles'];
	
	for(var i=0;i<titles.length;i++) {
		var title = titles[i];
		
		if( title.type == 'header' ) {
			
			titles_collection[titles_collection.length] = {
				'type' : 'header',
				'id' : '',
				'element' : ''
			};
			
		} else {
			var dimElmSelection = $('#title' + title.id + ' option:selected').text();
			
			titles_collection[titles_collection.length] = {
				'type' : 'dimension',
				'id' : title.id,
				'element' : dimElmSelection
			};
			
		}
	}
	
	var tasks = {"tasks": [
		{"task": "cube.update", "id" : model_detail.id, "cubeid" : workview_definition['cube'], "definition" : updates_definition },
		{"task": "workview.execute", "id" : model_detail.id, "workviewid" : workview_definition['id'], "titles" : titles_collection }
		
	]};
	query("model.service",tasks,workviewChangeDataCallback);	
	 updates_definition = {"updates" : []};
}

function workviewChangeDataCallback(data) {
	var results = JSON.parse(data);
	if( results['results'][0]['result'] == 1 ) {
		//workview saved successfully
		
		if( results['results'][1]['result'] == 1 ) {
			//the workview executed successfully
			
			updateWorkviewData(results['results'][1]);
			
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
var dimAddExisting = "Add a Existing Dimension";
var dimHeader = "Header / Spacer";


function workviewSave() {
	var titles_collection = [];
	var titles = workview_definition['positioning']['titles'];
	
	for(var i=0;i<titles.length;i++) {
		var title = titles[i];
		
		if( title.type == 'header' ) {
			
			titles_collection[titles_collection.length] = {
				'type' : 'header',
				'id' : '',
				'element' : ''
			};
			
		} else {
			var dimElmSelection = $('#title' + title.id + ' option:selected').text();
			
			titles_collection[titles_collection.length] = {
				'type' : 'dimension',
				'id' : title.id,
				'element' : dimElmSelection
			};
			
		}
	}
	
	
	var tasks = {"tasks": [
		{"task": "workview.update", "id" : model_detail.id, "workviewid" : workview_definition['id'], "definition" : workview_definition },
		{"task": "workview.execute", "id" : model_detail.id, "workviewid" : workview_definition['id'], "titles" : titles_collection }
		
	]};
	query("model.service",tasks,workviewSaveCallback);	
}

function workviewSaveCallback(data) {
	var results = JSON.parse(data);
	if( results['results'][0]['result'] == 1 ) {
		//workview saved successfully
		
		if( results['results'][1]['result'] == 1 ) {
			//the workview executed successfully
			
			updateGridWithDefinition();
			updateGridRowsAndColumns(results['results'][1]);
			updateWorkviewData(results['results'][1]);
			
			enableButtonsFor('');
	
			
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

var alwaysOn = ["manage-dimensions","refresh-workview"];
var headerFunctions = [];
var cellFunctions = ["data-validation"];

function enableButtonsFor(cellType) {
	
	window.myRibbon.disable(headerFunctions);
	window.myRibbon.disable(cellFunctions);
	
	if( cellType == "header" ) {
		window.myRibbon.enable(headerFunctions);
	} else if( cellType == "number" || cellType == "string" ) {
		window.myRibbon.enable(cellFunctions);
	} 
	
	window.myRibbon.enable(alwaysOn);
	
}


function displayColumnWidthDialog(headerCell) {
	//if the header is on columns then save per "set-element" otherwise save for the z class item
	var width = parseInt($(headerCell).width())+1;
	document.getElementById('columnWidth').value = width;
	$( "#dlgColumnWidth" ).dialog({
      resizable: false,
      height:140,
      width:350,
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
	var position = getDataset(headerCell,"position");
	var dim = dimById(dimId);
	
	if( position == "row" ) {
		workview_definition['dimensions'][dimId]['width'] = document.getElementById('columnWidth').value;
	} else {
		var setNo = getDataset(headerCell,"set");
		var setElement = getDataset(headerCell,"element");
		if( !workview_definition['dimensions'][dimId]['set'][setNo]['styles'] ) {
			workview_definition['dimensions'][dimId]['set'][setNo]['styles'] = {};
		}
	
		if( !workview_definition['dimensions'][dimId]['set'][setNo]['styles'][cleanStr(setElement)] ) {
			workview_definition['dimensions'][dimId]['set'][setNo]['styles'][cleanStr(setElement)] = {};
		}
		
		workview_definition['dimensions'][dimId]['set'][setNo]['styles'][cleanStr(setElement)]['Width'] = document.getElementById('columnWidth').value;
		
		
		
		//workview_definition['dimensions'][dimId]['set'][setNo] = document.getElementById('columnWidth').value;
	}
	
	workviewSave();
	
}
