$(document).ready(function() {

/* =================================
   Show menu button on mobile
   ================================= */
$("#show-menu").click(function(e) {
	e.preventDefault();
	$("#wrapper").toggleClass("active");
});

/* =================================
   Custom file dialog
   ================================= */
$('.show-file-dialog').click(function(e) {
	e.preventDefault();
	var targetDialog = $(this).attr('href');
	$(targetDialog).click();
});

/* =================================
   Mini charts
   ================================= */
   /*
$(".sparkline-bar").sparkline('html', {
	type: 'bar',
	height: '50px',
	barWidth: 10,
	chartRangeMin: 10,
	zeroAxis: false,
	disableInteraction: true,
	barColor: '#f9df9e',
	negBarColor: '#e74c3c',
	stackedBarColor: [ '#f9df9e','#338fbe','#109618','#66aa00','#dd4477','#0099c6','#990099' ]
});

$(".sparkline-pie").sparkline('html', {
    type: 'pie',
    disableInteraction: true,
    offset: '-90',
    sliceColors: ['#5bb2d3','#ffce55','#C2454E']
});
*/
/* =================================
   Equalize columns
   ================================= */
var maxHeight = 0;

$(".activity-feed-wrapper").each(function(){
   if ($(this).height() > maxHeight) { maxHeight = $(this).height(); }
});

$(".activity-feed-wrapper").height(maxHeight);

/* =================================
   Knobs
   ================================= */
   /*
$(".dial").knob({
	bgColor: '#e8e8e8',
	inputColor: '#2b3035',
	thickness: '.2',
	width: 75,
	height: 75,
	readOnly: true
});

*/
/* End scripts */
});