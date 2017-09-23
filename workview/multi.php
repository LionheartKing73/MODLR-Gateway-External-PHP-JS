<?
include_once("../lib/lib.php");
$id = querystring("id");
$w1 = querystring("w1");
$w2 = querystring("w2");
$w3 = querystring("w3");
$w4 = querystring("w4");
$position = querystring("position");
if( trim($position) == "" ){
	$position = "landscape";
}

?><!DOCTYPE html>
<!--[if lt IE 7]><html lang="en-us" class="ie6"><![endif]-->
<!--[if IE 7]><html lang="en-us" class="ie7"><![endif]-->
<!--[if IE 8]><html lang="en-us" class="ie8"><![endif]-->
<!--[if gt IE 8]><!--><html lang="en-us"><!--<![endif]-->
	<head>
		<title>MODLR » Split Screen</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
		<link rel="shortcut icon" href="http://www.modlr.co/wp-content/uploads/2014/06/favicon.ico">
		
		<!-- jQuery Framework -->
		<script src="/js/jquery-1.10.2.min.js"></script>
		<script src="/js/jquery-ui-1.10.3.custom.min.js"></script>
    	<script src="/js/jquery.ui.touch-punch.min.js" type="text/javascript"></script>
		<script src="/js/jquery.layout-latest.min.js"></script>
    	
		
		<link rel="apple-touch-icon" href="/img/apple-touch-icon-precomposed.png"/>
		<link rel="apple-touch-icon" sizes="72x72" href="/img/apple-touch-icon-72x72-precomposed.png" />
		<link rel="apple-touch-icon" sizes="114x114" href="/img/apple-touch-icon-114x114-precomposed.png" />
		<link rel="apple-touch-icon" sizes="144x144" href="/img/apple-touch-icon-144x144-precomposed.png" />
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-status-bar-style" content="black">
	
		<link href="/font-awesome/css/font-awesome.css" rel="stylesheet">
		<style type="text/css">
/**
 *	Basic Layout Theme
 */
.ui-layout-pane { /* all 'panes' */ 
	border: 1px solid #BBB; 
} 
.ui-layout-pane-center { /* IFRAME pane */ 
	padding: 0;
	margin:  0;
} 
.ui-layout-pane-west { /* west pane */ 
	padding: 0;
	margin:  0;
} 

.ui-layout-resizer { /* all 'resizer-bars' */ 
	background: #DDD; 
	} 
.ui-layout-resizer-open:hover { /* mouse-over */
		background: #9D9; 
	}

.ui-layout-toggler {  /* all 'toggler-buttons' */ 
	background: #AAA; 
	} 
	
.ui-layout-toggler-closed {  /* closed toggler-button */ 
	background: #CCC; 
	border-bottom: 1px solid #BBB; 
} 
.ui-layout-toggler .content { /* toggler-text */ 
	font: 14px bold Verdana, Verdana, Arial, Helvetica, sans-serif;
}
.ui-layout-toggler:hover { /* mouse-over */ 
	background: #DCA; 
} 
.ui-layout-toggler:hover .content { /* mouse-over */ 
	color: #009; 
}

/* masks are usually transparent - make them visible (must 'override' default) */
.ui-layout-mask {
	background:	#C00 !important;
	opacity:	.20 !important;
	filter:		alpha(opacity=20) !important;
}

		</style>
		
		<script type="text/javascript">

		var myLayout; // a var is required because this page utilizes: myLayout.allowOverflow() method

		$(document).ready(function () {

<?
$secondaryDirection = "west";
if( $w4 != "" ) {
?>
			myLayout = $('body').layout({
				west__size:					'50%'
				southwest__size:					'50%'
				south__size:					'50%'
			,	west__maskContents:		true // IMPORTANT - enable iframe masking
			,	center__maskContents:		true // IMPORTANT - enable iframe masking
			,	south__maskContents:		true // IMPORTANT - enable iframe masking
			,	southwest__maskContents:		true // IMPORTANT - enable iframe masking
			});
<?
} else if( $position == "landscape" ) {
?>
			myLayout = $('body').layout({
				west__size:					'50%'
			,	west__maskContents:		true // IMPORTANT - enable iframe masking
			,	center__maskContents:		true // IMPORTANT - enable iframe masking
			});
<?
} else {
	$secondaryDirection = "north";
?>
			myLayout = $('body').layout({
				north__size:					'50%'
			,	north__maskContents:		true // IMPORTANT - enable iframe masking
			,	center__maskContents:		true // IMPORTANT - enable iframe masking
			});
<?
}
?>			

		});

		</script>

	</head>
	<body>

<?
if( $w4 != "" ) {
?>
	<iframe id="frame1" name="frame1" class="ui-layout-center"
	width="100%" height="600" frameborder="0" scrolling="auto"
	src="/workview/editor/?id=<? echo $id;?>&workview=<? echo $w2;?>"></iframe>
	
	<iframe id="frame2" name="frame2" class="ui-layout-west"
	width="100%" height="600" frameborder="0" scrolling="auto"
	src="/workview/editor/?id=<? echo $id;?>&workview=<? echo $w1;?>"></iframe>
	
	<iframe id="frame3" name="frame3" class="ui-layout-southwest"
	width="100%" height="600" frameborder="0" scrolling="auto"
	src="/workview/editor/?id=<? echo $id;?>&workview=<? echo $w3;?>"></iframe>
	
	<iframe id="frame4" name="frame4" class="ui-layout-south"
	width="100%" height="600" frameborder="0" scrolling="auto"
	src="/workview/editor/?id=<? echo $id;?>&workview=<? echo $w4;?>"></iframe>
<?
} else {
?>
	<iframe id="frame1" name="frame1" class="ui-layout-center"
	width="100%" height="600" frameborder="0" scrolling="auto"
	src="/workview/editor/?id=<? echo $id;?>&workview=<? echo $w2;?>"></iframe>
	
	<iframe id="frame2" name="frame2" class="ui-layout-<? echo $secondaryDirection;?>"
	width="100%" height="600" frameborder="0" scrolling="auto"
	src="/workview/editor/?id=<? echo $id;?>&workview=<? echo $w1;?>"></iframe>
<?
}
?>


	
	</body>
</html>