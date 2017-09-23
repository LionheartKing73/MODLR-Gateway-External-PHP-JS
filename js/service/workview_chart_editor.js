


var bDisplayingRightPanel = false;
var displayWidth = 0;
function displayChartDeveloper() {
	var panelWidth = windowWidth/2.5;
	displayWidth = panelWidth;
	
	if( bDisplayingRightPanel ) {
		$('#right-sidebar').stop(true).animate({width:0});
		$('#workview').stop(true).animate({marginRight:0});
	} else {
		$('#right-sidebar').stop(true).animate({width:panelWidth});
		$('#workview').stop(true).animate({marginRight:panelWidth});
		
		//clear the current form
		$("#visualisation_form").html("");
		
		setTimeout(function() {
			loadVisualisation();
		}, 250);
		
	}
	bDisplayingRightPanel = !bDisplayingRightPanel;

}

function changeVisualisation() {
	var type = $('#inputVisualisationType option:selected')[0].value;
	workview_definition['visualisation']['type'] = type;
	workview_definition['visualisation']['title'] = $('#inputVisualisationTitle')[0].value;
	$("#chart svg").html("");
	workviewSaveChart();
}


function visualisationKeyChanged(field) {
	var key = getDataset(field,"key");
	var val = $(field).val();
	
	workview_definition['visualisation'][key] = val;
	workviewSaveChart();
}

function visualisationNameChanged() {
	workview_definition['visualisation']['title'] = $('#inputVisualisationTitle')[0].value;
	workviewSaveChart();
}


function loadVisualisation() {
	var html = '<table width="100%" style="margin-bottom:10px;"><tr><td>Visualisation:</td><td><select id="inputVisualisationType">';
    
	
	//load the visualisation list
	var type = "bubble";
	if( workview_definition['visualisation'] ) {
		type = workview_definition['visualisation']['type'];
	} else {
		workview_definition['visualisation'] = {"type" : "bubble", "title" : "New Visualisation"};
	}
	for(var i=0;i<visualisationTypes.length;i++) {
		var selected = "";
		if( visualisationTypes[i] == type ) 
			selected = " selected";
		html += "<option value='" + visualisationTypes[i] + "'" + selected + ">"+vizDefinitions[visualisationTypes[i]].title+"</option>";
	}
	
	var chartDefinition = vizDefinitions[type];
	var def = workview_definition['visualisation'];
	
	html += '</select></td></tr>';    
	html += '<tr><td>';    
 
	html += 'Title';    
	html += '</td>';    
	html += '<td>';    
	html += '<input type="text" class="chart-input" id="inputVisualisationTitle" value="' + workview_definition['visualisation']['title'] + '" onblur="visualisationNameChanged(this);"/>';  
	html += '</td></tr><tr><td colspan="2">';   	
	
	html += '<div id="chart"></div>';
	
	html += '</td></tr>';
	
	if( chartDefinition.options ) {
		for(var i=0;i<chartDefinition.options.length;i++) {
			var optGroup = chartDefinition.options[i];
			
			html += '<tr><td colspan="2"><b>'+optGroup.title+'</b></td></tr>';
			for(var o=0;o<optGroup.options.length;o++) {
				var opt = optGroup.options[o];
			
				var id = opt.key;
				var selected = opt.default;
				
				if( def[id] ) {
					selected = def[id];
				}
				
				html += '<tr><td>';
				html += opt.title;
				html += '</td><td>';
				
				if( opt.type == "list" ) {
					html+= '<select class="chart-select" id="'+id+'" data-key="'+id+'" onChange="visualisationKeyChanged(this);">';
					for(var k=0;k<opt.options.length;k++) {
						var val = opt.options[k];
						var addSelection = "";
						if( selected == val )
							addSelection = " selected";
						
						html+= '<option '+addSelection+' value="'+val+'">'+val+'</option>';
					}
					html += '</select>';
				} else if( opt.type == "numeric" ) {
					
					html += '<input type="text" class="chart-input" data-key="'+id+'" onBlur="visualisationKeyChanged(this);" id="'+id+'" placeholder="'+opt.title+'" value="'+selected+'">'
				} else if( opt.type == "string" ) {
					html += '<input type="text" class="chart-input" data-key="'+id+'" onBlur="visualisationKeyChanged(this);" id="'+id+'" placeholder="'+opt.title+'" value="'+selected+'">'
				}
				html += '</td></tr>';
			}
		}
	}
	
	html += '</table>';
	
	$("#visualisation_form").html(html);
	
	$('#inputVisualisationType').chosen({
    	width: '250px;',
    	height: '220px;'
    });
	
	$('#inputVisualisationType').change( function() { 
        changeVisualisation(); 
    }); 
	
	workviewSaveChart();

}



var bSkipChartCallback = false;
function workviewSaveChartNoCallback() {
	bSkipChartCallback = true;
	workviewSaveChart();
}
function workviewSaveChart() {
	//specify the correct titles
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
		{"task": "workview.update", "id" : model_detail.id, "workviewid" : workview_definition['id'], "definition" : workview_definition },
		{"task": "workview.execute.visualisation", "id" : model_detail.id, "workviewid" : workview_definition['id'], "titles" : titles_collection, "options" : workview_execute_options }
		
	]};
	
	if( bSkipChartCallback ) {
		query("model.service",tasks,null);
	} else {
		query("model.service",tasks,workviewSaveChartCallback);
	}
}

function workviewSaveChartCallback(data) {
	var results = JSON.parse(data);
	
	if( results['results'][1]['result'] == 1 ) {
		//the workview executed successfully
		
		presentChartBasic(results['results'][1]);
		
	} else {
		//the workview failed to execute
		//the workview save failed, typically this is only happening if the session has expired.
		if( results['results'][0]['error'] ) {
			error = results['results'][0]['error'];
			alert(error);
		}
	}
}

function presentChartBasic(chartData) {
	presentChart(chartData, "chart" , true, (displayWidth-20) + 'px', "300px");
}

function SetChartLegend(chart,vizLegendPosition) {
	if( vizLegendPosition == "Hidden") {
		chart.showLegend(false);	
	} else {
		chart.showLegend(true);	
		chart.legendPosition(vizLegendPosition.toLowerCase());
	}

}

function SetAxisColoring(chartClass,vizGridXOpacity,vizGridYOpacity) {
	setTimeout(function() {
		if( vizGridXOpacity == "Lighter" ) {
			$("#" + chartClass + " svg").find(".nv-x").find(".tick").find("line").css("opacity","0.5");
			$("#" + chartClass + " svg").find(".nv-x").find(".tick").find("line").css("opacity","0.5");
		} else if( vizGridXOpacity == "Hidden" ) {
			$("#" + chartClass + " svg").find(".nv-x").find(".tick").find("line").css("opacity","0");
			$("#" + chartClass + " svg").find(".nv-x").find(".tick").find("line").css("opacity","0");
		}
		if( vizGridYOpacity == "Lighter" ) {
			$("#" + chartClass + " svg").find(".nv-y").find(".tick").find("line").css("opacity","0.5");
			$("#" + chartClass + " svg").find(".nv-y").find(".tick").find("line").css("opacity","0.5");
		} else if( vizGridYOpacity == "Hidden" ) {
			$("#" + chartClass + " svg").find(".nv-y").find(".tick").find("line").css("opacity","0");
			$("#" + chartClass + " svg").find(".nv-y").find(".tick").find("line").css("opacity","0");
		}
	},50);
}

var chartObject = null;
function presentChart(chartData, chartClass, bCleanUp, sWidth, sHeight) {
	var colors = colorsDefault;
	
	var vizColors = chartData.visualisation['viz-colors'];
	if( vizColors ) {
		colors = colorsets[vizColors]['colors'];
	}
	
	//chart properties.
	var vizMarginTop = chartData.visualisation['viz-margin-top'];
	var vizMarginRight = chartData.visualisation['viz-margin-right'];
	var vizMarginBottom = chartData.visualisation['viz-margin-bottom'];
	var vizMarginLeft = chartData.visualisation['viz-margin-left'];
	
	var vizLegendPosition = chartData.visualisation['viz-legend-position'];
	if( !vizLegendPosition )
		vizLegendPosition = "Hidden";
	
	var vizEnableControls = chartData.visualisation['viz-enable-controls'];
	if( !vizEnableControls )
		vizEnableControls = "No";
	
	var vizGridXOpacity = chartData.visualisation['viz-grid-x-opacity'];
	if( !vizGridXOpacity )
		vizGridXOpacity = "Normal";
	var vizGridYOpacity = chartData.visualisation['viz-grid-y-opacity'];
	if( !vizGridYOpacity )
		vizGridYOpacity = "Normal";
	
	var vizNumericDecimals = chartData.visualisation['viz-numeric-decimals'];
	if( !vizNumericDecimals )
		vizNumericDecimals = 2;
	else	
		vizNumericDecimals = parseInt(vizNumericDecimals,10);
	
	var numericFormat = ',.'+vizNumericDecimals+'f';
	
	
	if( bCleanUp )
		d3.selectAll("#"+chartClass+" > *").remove();

	var html = '<div id="' + chartClass + 'Block"></div><svg id="' + chartClass + 'Svg" style="height:' + sHeight + ';width:' + sWidth + '"> </svg>';
	
	$("#" + chartClass).html("");
	$("#" + chartClass).html(html);
	$("#" + chartClass + "Block").css("display","none");
	$("#" + chartClass + "Svg").css("display","none");	
	
	if( !chartData['charts'] )
		return;
	
	
	if( chartData['type'] == "pie" ) {
	 
		var chartObj = chartData['charts'][0];
		nv.addGraph(function() {
			var chart = nv.models.pieChart()
			  .x(function(d) { return d.label })
			  .y(function(d) { return d.value })
			  .showLabels(true);
			d3.select("#" + chartClass + " svg")
				.datum(chartObj['data'])
				.transition().duration(350)
				.call(chart)
				//.color(colors);

			SetChartLegend(chart, vizLegendPosition);
				
			chartObject =  chart;
			return chart;
		});
		
		$("#" + chartClass + "Svg").css("display","block");	
	} else if( chartData['type'] == "donut" ) {
	 
		var chartObj = chartData['charts'][0];
		nv.addGraph(function() {
			var chart = nv.models.pieChart()
			  .x(function(d) { return d.label })
			  .y(function(d) { return d.value })
			  .showLabels(true)
			  .donut(true)
			  .donutRatio(0.35)
			  .color(colors);
			  
			d3.select("#" + chartClass + " svg")
				.datum(chartObj['data'])
				.transition().duration(350)
				//.call(chart);

			SetChartLegend(chart, vizLegendPosition);
			
			chartObject =  chart;
			return chart;
		});
	
		$("#" + chartClass + "Svg").css("display","block");	
	} else if( chartData['type'] == "bar"  ) {
	 
		
		if( !vizMarginTop )
			vizMarginTop = 25;
	 
		if( !vizMarginRight )
			vizMarginRight = 30;
		
		if( !vizMarginBottom )
			vizMarginBottom = 30;
		
		if( !vizMarginLeft )
			vizMarginLeft = 90;
		
		var chartObj = chartData['charts'][0];
		nv.addGraph(function() {
			var chart = nv.models.multiBarChart()
			  .transitionDuration(350)
			  .margin({top: vizMarginTop, right: vizMarginRight, bottom: vizMarginBottom, left: vizMarginLeft})
			  .rotateLabels(0)      //Angle to rotate x-axis labels.
			  .groupSpacing(0.2);    //Distance between each group of bars.
			
			if( vizEnableControls == "Yes" ) {
				chart.showControls(true); //Allow user to switch between 'Grouped' and 'Stacked' mode.
			} else {
				chart.showControls(false);
			}
			 
			chart.color(colors);
			
			SetChartLegend(chart, vizLegendPosition);
			
			chart.yAxis
				.tickFormat(d3.format(numericFormat));
				
			if( chartData['visualisation']['stacked'] ) {
				chart.stacked(chartData['visualisation']['stacked']);
			}
			
			try {
				d3.select("#" + chartClass + " svg")
					.datum(chartObj['data'])
					.call(chart);
				}
			catch(e) {
				
			} 
			
			
				
			nv.utils.windowResize(chart.update);
			chartObject =  chart;
			
			SetAxisColoring(chartClass,vizGridXOpacity,vizGridYOpacity);
			
			return chart;
		});
	 
		$("#" + chartClass + "Svg").css("display","block");	
	} else if( chartData['type'] == "column" ) {
	 
		
		if( !vizMarginTop )
			vizMarginTop = 10;
	 
		if( !vizMarginRight )
			vizMarginRight = 30;
		
		if( !vizMarginBottom )
			vizMarginBottom = 30;
		
		if( !vizMarginLeft )
			vizMarginLeft = 90;
		
		var chartObj = chartData['charts'][0];
		nv.addGraph(function() {
			var chart = nv.models.multiBarHorizontalChart()
			  .transitionDuration(350)
			  .margin({top: vizMarginTop, right: vizMarginRight, bottom: vizMarginBottom, left: vizMarginLeft})
			  .color(colors);
			
			if( vizEnableControls == "Yes" ) {
				chart.showControls(true); //Allow user to switch between 'Grouped' and 'Stacked' mode.
			} else {
				chart.showControls(false);
			}
			
			SetChartLegend(chart, vizLegendPosition);
			
			chart.yAxis
				.tickFormat(d3.format(numericFormat));
				
			if( chartData['visualisation']['stacked'] ) {
				chart.stacked(chartData['visualisation']['stacked']);
			}
			
			
			d3.select("#" + chartClass + " svg")
				.datum(chartObj['data'])
				.call(chart);
			nv.utils.windowResize(chart.update);
			chartObject =  chart;
			
			SetAxisColoring(chartClass,vizGridXOpacity,vizGridYOpacity);
			
			return chart;
		});
	 
			
		$("#" + chartClass + " svg").css("display","block");
	} else if( chartData['type'] == "figure"  ) {
		
		createFigure("#" + chartClass + "Block", chartData['charts'][0]);
		$("#" + chartClass + "Block").css("display","block");
	 
	} else if( chartData['type'] == "figurevariance"  ) {
		
		createFigureVariance("#" + chartClass + "Block", chartData['charts'][0]);
		$("#" + chartClass + "Block").css("display","block");
	 
	} else if( chartData['type'] == "area"  ) {
	 
		var chartObj = chartData['charts'][0];
		nv.addGraph(function() {
			var chart = nv.models.stackedAreaChart()
			  .transitionDuration(350)
			  .color(colors);
			
			if( vizEnableControls == "Yes" ) {
				chart.showControls(true); //Allow user to switch between 'Grouped' and 'Stacked' mode.
			} else {
				chart.showControls(false);
			}
			 
			SetChartLegend(chart, vizLegendPosition);
			
			
			chart.yAxis
				.tickFormat(d3.format(numericFormat));
				
			chart.xAxis.tickFormat(function(d){
				return chartObj['data'][0]['values'][d]['label'];
			});
			
			
			chart.yAxis
				.tickFormat(d3.format(',.1f'));
			d3.select("#" + chartClass + " svg")
				.datum(chartObj['data'])
				.call(chart);
			nv.utils.windowResize(chart.update);
			chartObject =  chart;
			
			SetAxisColoring(chartClass,vizGridXOpacity,vizGridYOpacity);
			
			return chart;
		});
	 
		$("#" + chartClass + "Svg").css("display","block");	
	} else if( chartData['type'] == "line" ) {
        var vizLegendPosition = chartData.visualisation['viz-legend-display'];
        
     
		var chartObj = chartData['charts'][0];
		nv.addGraph(function() {
			var chart = nv.models.lineChart()
			  .transitionDuration(350)
			  .margin({top: 10, right: 30, bottom: 30, left: 90})
			  .useInteractiveGuideline(true)
			  .color(colors);
			
			//SetChartLegend(chart, vizLegendPosition);
            if( vizLegendPosition == "Visible") {
                chart.showLegend(true);
            } else {
                chart.showLegend(false);
            }
            
			
			chart.interactiveLayer.tooltip.fixedTop(100);
			
			
			chart.yAxis
				.tickFormat(d3.format(numericFormat));
				
			chart.xAxis.tickFormat(function(d){
				return chartObj['data'][0]['values'][d]['label'];
			});
			
			d3.select("#" + chartClass + " svg")
				.datum(chartObj['data'])
				.call(chart);
			nv.utils.windowResize(chart.update);
			chartObject =  chart;
			
			SetAxisColoring(chartClass,vizGridXOpacity,vizGridYOpacity);
			
			return chart;
		});
	 
		$("#" + chartClass + "Svg").css("display","block");	
	} else if( chartData['type'] == "scatter" ) {
		$("#" + chartClass + " svg").css("display","block");
        var vizLegendPosition = chartData.visualisation['viz-legend-display'];
		
        var vizMarkersX = chartData.visualisation['viz-display-markers-x'];
        var showMarkersX = vizMarkersX == "Yes" ? true : false;
        var vizMarkersY = chartData.visualisation['viz-display-markers-y'];
        var showMarkersY = vizMarkersY == "Yes" ? true : false;
        
        
        var vizNumericDecimalsX = chartData.visualisation['viz-numeric-decimals-x'];
        if( !vizNumericDecimalsX )
            vizNumericDecimalsX = 2;
        else	
            vizNumericDecimalsX = parseInt(vizNumericDecimalsX,10);
        var vizSymbolX = chartData.visualisation['viz-numeric-symbol-x'];
        var numericFormatX = ',.'+vizNumericDecimals+'f';
        if( vizSymbolX == "%" ) 
            numericFormatX += vizSymbolX;
        else 
            numericFormatX = vizSymbolX + numericFormatX;
        
        var vizNumericDecimalsY = chartData.visualisation['viz-numeric-decimals-y'];
        if( !vizNumericDecimalsY )
            vizNumericDecimalsY = 2;
        else	
            vizNumericDecimalsY = parseInt(vizNumericDecimalsY,10);
        var vizSymbolY = chartData.visualisation['viz-numeric-symbol-y'];
        var numericFormatY = ',.'+vizNumericDecimals+'f';
        if( vizSymbolY == "%" ) 
            numericFormatY += vizSymbolY;
        else 
            numericFormatY = vizSymbolY + numericFormatY;
        
        
		var width = parseInt(sWidth.replace(/px/gi,""));
		var height = parseInt(sHeight.replace(/px/gi,""));
		
		var chartObj = chartData['charts'][0].data;
        for(var i=0;i<chartObj[0].values.length;i++) {
            
            if( !chartObj[0].values[i].color ) {
                chartObj[0].values[i].color = colors[0];
            }
            if( !chartObj[0].values[i].shape ) {
                chartObj[0].values[i].shape = "circle";
            }
        }
		
		var chart = nv.models.scatterChart()
                .showDistX(showMarkersX)    //showDist, when true, will display those little distribution lines on the axis.
                .showDistY(showMarkersY)
                .transitionDuration(350)
                .margin({top: vizMarginTop, right: vizMarginRight, bottom: vizMarginBottom, left: vizMarginLeft})
                .color(colors);

            
            if( vizLegendPosition == "Visible") {
                chart.showLegend(true);
            } else {
                chart.showLegend(false);
            }
            
            chart.yAxis
                .axisLabel(chartData['charts'][0]["axis-y"])
                //.axisLabelDistance(40);
                
            chart.xAxis
                .axisLabel(chartData['charts'][0]["axis-x"])
                //.axisLabelDistance(40);
            
            
		  //Configure how the tooltip looks.
		  chart.tooltipContent(function(key) {
			  return '<h3>' + key + '</h3>';
		  });
          
		  //Axis settings
		  chart.xAxis.tickFormat(d3.format(numericFormatX));
		  chart.yAxis.tickFormat(d3.format(numericFormatY));

		  //We want to show shapes other than circles.
		  chart.scatter.onlyCircles(false);

		  d3.select("#" + chartClass + " svg")
			  .datum(chartObj)
			  .call(chart);

			  nv.utils.windowResize(chart.update);

		  return chart;
		
		
	} else if( chartData['type'] == "bubble" ) {
		$("#" + chartClass + " svg").css("display","block");
		
		var width = parseInt(sWidth.replace(/px/gi,""));
		var height = parseInt(sHeight.replace(/px/gi,""));
		
		var chartObj = chartData['charts'][0];
		var diameter = (width-20);
		
		var format = d3.format(",d"),
			color = d3.scale.category20c();

		var bubble = d3.layout.pack()
			.sort(null)
			.size([ width,  height])
			.padding(1.5);

		for(var i=chartObj['data'].length-1;i>=0;i--) {
			chartObj['data'][i]['size'] = chartObj['data'][i]['value'];
			if( chartObj['data'][i]['value'] == 0 ) {
				chartObj['data'].splice(i,1);
			}
		}
			
		if( chartObj['data'].length > 0 ) {
		
			var root = {"name" : "Bubble", "children" : chartObj['data']};
			
			var svg = d3.select("#" + chartClass + " svg")
				.attr("width", width)
				.attr("height", height)
				.attr("class", "bubble")
			
			  var node = svg.selectAll(".node")
				.data(bubble.nodes(root)
				.filter(function(d) { return !d.children; }))
				.enter().append("g")
				.attr("class", "node")
				.attr("transform", function(d) { return "translate(" + d.x + "," + d.y + ")"; });

			  node.append("title")
				  .text(function(d) { return d.row + ": " + format(d.value); })
				  .style("fill", "#FFF");

			 
			  node.append("circle")
				  .attr("r", function(d) { return d.r; })
				  .style("cursor", "pointer")
				  .style("fill", function(d) { return color(d.column); });

			  node.append("text")
				  .attr("dy", "-0.3em")
				  .style("text-anchor", "middle")
				  .style("fill", "#FFF")
				  .style("cursor", "pointer")
				  .style("font-size", "10px")
				  .text(function(d) { return d.row.substring(0, d.r / 3.5); });
			
			node.append("text")
				  .attr("dy", "0.7em")
				  .style("text-anchor", "middle")
				  .style("fill", "#FFF")
				  .style("cursor", "pointer")
				  .style("font-size", "10px")
				  .text(function(d) { return d.column.substring(0, d.r / 3.5); });

			d3.select(self.frameElement).style("height", sHeight);
			
			$("#" + chartClass + "Svg").css("display","block");	
		}
	}
	
	if( workview_definition ) {
		
		setTimeout(function(){
		$(".nv-series").on("click", function() {
				if( chartData['visualisation']['type'] == "bar" ||  chartData['visualisation']['type'] == "column" ) {
					var stacked = false;
					if( this.childNodes[1].innerHTML == "Stacked" ) {
						stacked = true;
						workview_definition['visualisation']['stacked'] = stacked;
						workviewSaveChartNoCallback();
					} else if( this.childNodes[1].innerHTML == "Grouped" ) {
						workview_definition['visualisation']['stacked'] = stacked;
						workviewSaveChartNoCallback();
					}
					
				}
			return true;
		});
		}, 500);
	
	}

}


function createFigure(parentSelector, chartObject) {
	
	var cell = chartObject['data'][0];
	var value = generalNumberFormat(cell['value'],cell['format']);
	var title = cell['column'] + ", " + cell['row'];
	
	var html = "<div class='visFigure'>" + value + "<span class='visFigureTitle'>" + title + "</span></div>";
	$(parentSelector).html(html);	

}
function createFigureVariance(parentSelector, chartObject) {
	
	if( chartObject['data'].length < 2 ) {
		createFigure(parentSelector, chartObject);
		return;
	}
	
	var cell = chartObject['data'][0];
	var cellSecond = chartObject['data'][1];
	
	var value = generalNumberFormat(cell['value'],cell['format']);
	var title = cell['column'] + ", " + cell['row'];
	
	var variance = (( parseFloat(cell['value']) / parseFloat(cellSecond['value']))-1) * 100;
	variance = variance.toFixed(2);
	
	var html = "<div class='visFigure'>" + value + "<span class='visFigureVariance'>" + variance + "%</span><span class='visFigureTitle'>" + title + "</span></div>";
	$(parentSelector).html(html);	
	
}

