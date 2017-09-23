<?
function IsTestMode() {
	return false;
}

include_once("lib/includes/email.php");


$to = "ben.hill@modlr.co";
$subject = "MODLR 500 Internal Datastore Error";
$plainMessage = "MODLR 500 Internal Datastore Error\r\nPlease check the configuration of server id ".$_GET["serverid"].".";
$htmlMessage = "<b>MODLR 500 Internal Datastore Error\r\nPlease check the configuration of server id ".$_GET["serverid"].".</b>";

sendTransactionalMail($to,$subject,$plainMessage,$htmlMessage);


?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="keyword" content="">
    <link rel="shortcut icon" href="img/favicon.png">

    <title>500</title>

    <!-- Bootstrap core CSS -->
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/bootstrap-reset.css" rel="stylesheet">
    <!--external css-->
    <link href="/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <!-- Custom styles for this template -->
    <link href="/css/style.css" rel="stylesheet">
    <link href="/css/style-responsive.css" rel="stylesheet" />

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 tooltipss and media queries -->
    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->
</head>




  <body class="body-500">

    <div class="error-head"> </div>

    <div class="container ">

      <section class="error-wrapper text-center">
          <h1><img src="/images/500.png" alt=""></h1>
          <div class="error-desk">
              <h2>OOOPS!!!</h2>
              <p class="nrml-txt-alt">Something went wrong.</p>
              <p>It looks like the internal datastore for this MODLR instance is temporarily down, We have contacted our support staff about this. Please click the link below to return to MODLR.</p>
          </div>
          <a href="/home/" class="back-btn"><i class="fa fa-home"></i> Back To Home</a>
      </section>

    </div>


  </body>
</html>
