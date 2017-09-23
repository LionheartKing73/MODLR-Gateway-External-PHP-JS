
var defaultColorArguments = {title:"Colors", options:[
		{key:"viz-colors",title:"Colorset",type:"list",default:"MODLR",options:["MODLR","Default","Earthy","RAW","Excel 2010","Grayscale","Greens"]}
	]};
var defaultMarginArguments = {title:"Margins", options:[
		{key:"viz-margin-top",title:"Margin Top",default:25,type:"numeric"},
		{key:"viz-margin-right",title:"Margin Right",default:30,type:"numeric"},
		{key:"viz-margin-bottom",title:"Margin Bottom",default:30,type:"numeric"},
		{key:"viz-margin-left",title:"Margin Left",default:90,type:"numeric"}
]};
var defaultMarginArgumentsScatter = {title:"Margins", options:[
		{key:"viz-margin-top",title:"Margin Top",default:60,type:"numeric"},
		{key:"viz-margin-right",title:"Margin Right",default:60,type:"numeric"},
		{key:"viz-margin-bottom",title:"Margin Bottom",default:60,type:"numeric"},
		{key:"viz-margin-left",title:"Margin Left",default:90,type:"numeric"}
]};
var defaultSortingArguments = {title:"Top and Bottom Selection", options:[
		{key:"viz-sort",title:"Sort by Value",type:"list",default:"None",options:["None","Ascending","Descending"]},
		{key:"viz-limit",title:"Limit Series",default:0,type:"numeric"}
	]};
var defaultLegendOptions = {title:"Chart Legend", options:[
		{key:"viz-legend-position",title:"Position",type:"list",default:"Hidden",options:["Hidden","Top","Right","Bottom","Left"]}
	]};
    
var defaultLineLegendOptions = {title:"Chart Legend", options:[
		{key:"viz-legend-display",title:"Position",type:"list",default:"Hidden",options:["Hidden","Visible"]}
	]};
    

var defaultControlOptions = {title:"Chart Options", options:[
		{key:"viz-enable-controls",title:"Show Controls",type:"list",default:"No",options:["No","Yes"]}
	]};

var defaultGridOptions = {title:"Chart Grid", options:[
		{key:"viz-grid-x-opacity",title:"Grid X Axis",type:"list",default:"Normal",options:["Normal","Lighter","Hidden"]},
		{key:"viz-grid-y-opacity",title:"Grid Y Axis",type:"list",default:"Normal",options:["Normal","Lighter","Hidden"]}
	]};

var defaultNumberOptions = {title:"Number Format", options:[
		{key:"viz-numeric-decimals",title:"Decimal Places",type:"list",default:"2",options:["0","1","2","3","4","5","6"]}
	]};

var defaultAxisXOptions = {title:"Axis-X Options", options:[
		{key:"viz-numeric-decimals-x",title:"Decimal Places",type:"list",default:"2",options:["0","1","2","3","4","5","6"]},
		{key:"viz-numeric-symbol-x",title:"Display Symbol",type:"list",default:"",options:["","$","%","£","€"]},
		{key:"viz-display-markers-x",title:"Show Markers",type:"list",default:"No",options:["Yes","No"]}
	]};
var defaultAxisYOptions = {title:"Axis-Y Options", options:[
		{key:"viz-numeric-decimals-y",title:"Decimal Places",type:"list",default:"2",options:["0","1","2","3","4","5","6"]},
		{key:"viz-numeric-symbol-y",title:"Display Symbol",type:"list",default:"",options:["","$","%","£","€"]},
		{key:"viz-display-markers-y",title:"Show Markers",type:"list",default:"No",options:["Yes","No"]}
	]};
	
	//.legendPosition("right");

var visualisationTypes = ["custom","figure","figurevariance","bubble","bar","column","area","pie","donut","line","scatter"];
visualisationTypes.sort();

var vizDefinitions = [];
vizDefinitions['bubble'] = {title:"Bubble Chart",options:[
	defaultColorArguments,
	defaultMarginArguments
]};
vizDefinitions['treemap'] = {title:"Tree Map Diagram",options:[
	defaultColorArguments,
	defaultMarginArguments
]};
vizDefinitions['heatmap'] = {title:"Heat Map Diagram",options:[
	defaultColorArguments,
	defaultMarginArguments
]};
vizDefinitions['bar'] = {title:"Bar Chart",options:[
	defaultColorArguments,
	defaultMarginArguments,
	defaultSortingArguments,
	defaultControlOptions,
	defaultGridOptions,
	defaultNumberOptions
]};
vizDefinitions['column'] = {title:"Column Chart",options:[
	defaultColorArguments,
	defaultMarginArguments,
	defaultSortingArguments,
	defaultControlOptions,
	defaultGridOptions,
	defaultNumberOptions
]};
vizDefinitions['area'] = {title:"Area Chart",options:[
	defaultColorArguments,
	defaultMarginArguments,
	defaultControlOptions,
	defaultGridOptions,
	defaultNumberOptions
]};
vizDefinitions['sunburst'] = {title:"Sunburst Diagram",options:[
	defaultColorArguments,
	defaultMarginArguments
]};
vizDefinitions['pie'] = {title:"Pie Chart",options:[
	defaultColorArguments,
	defaultMarginArguments,
	defaultLegendOptions
]};
vizDefinitions['donut'] = {title:"Donut Chart",options:[
	defaultColorArguments,
	defaultMarginArguments,
	defaultLegendOptions
]};
vizDefinitions['line'] = {title:"Line Chart",options:[
	defaultColorArguments,
	defaultMarginArguments,
	defaultControlOptions,
	defaultGridOptions,
    defaultLineLegendOptions,
	defaultNumberOptions
]};
vizDefinitions['waterfall'] = {title:"Waterfall Chart",options:[
	defaultColorArguments,
	defaultMarginArguments
]};
vizDefinitions['figure'] = {title:"Figure"};
vizDefinitions['figurevariance'] = {title:"Figure (with Variance)"};
vizDefinitions['custom'] = {title:"Custom Visualisation"};

vizDefinitions['scatter'] = {title:"Scatter Plot",options:[
	defaultColorArguments,
	defaultMarginArgumentsScatter,
	defaultLineLegendOptions,
	defaultAxisXOptions,
    defaultAxisYOptions
]};




var colorsets = 
{
	"MODLR":{
         "colors" : ["#104470","#00699d","#00699d","#0776b1","#118dc6","#057eaf","#65cbe3","#8dd6eb","#39c5ea","#5ecbec","#94d9f1","#aee1f0"]
        },
    "Default":{
         "colors" : ["#E5CF6C","#9DBE59","#5BBE94","#5884B3","#CC6686","#E68570","#F9EBAA","#D4E5B5","#B6E4D1","#B6CEE5","#E5B5C5","#F1C8C0"]
        },
    "Earthy":{
         "colors" :["#E96D63","#7FCA9F","#F4BA70","#85C1F5","#4A789C","#9C3B33","#427D5C","#A7783C"]
        },
   "RAW":{
         "colors" :["#bf9169","#6983bf","#bfb869","#bf6969","#69bf83","#7669bf","#69abbf","#9e69bf","#bf69b8","#9ebf69","#69bfab","#76bf69","#bf6991"]
        },
    "Excel 2010":{
         "colors" :[ "#4F81BD","#C0504D","#9BBB59","#8064A2","#4BACC6","#F79646","#2C4D75","#772C2A","#5F7530","#4D3B62","#276A7C","#B65708","#729ACA"]
        },
    "Grayscale":{
         "colors" :[ "#32323A","#666","#888","#777","#AAA","#CCC"]
        },
    "Greens":{
         "colors" :[ "#2F4F4F", "#008080", "#2E8B57",  "#3CB371", "#90EE90"]
        }
    };

var colorsDefault = colorsets['MODLR']['colors'];

