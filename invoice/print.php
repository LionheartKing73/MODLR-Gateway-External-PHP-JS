<?
include_once("../lib/lib.php");


$invoice_id = querystring("id");

$sql = "SELECT invoice_id,billing_period,date_added,amount_tax,amount_services,amount_total,invoice_paid,DATEDIFF(CURDATE(),date_added) as daysSinceBilled FROM modlr.clients_invoices WHERE invoice_id='%s' and client_id='%s';";
$db = new db_helper();
$db->CommandText($sql);
$db->Parameters($invoice_id);
$db->Parameters(session("client_id"));
$db->Execute();
if ($db->Rows_Count() > 0) {
	$r = $db->Rows();
	
	$invoice_id = $r['invoice_id'];
	$billing_period = $r['billing_period'];
	
	$amount_tax = $r['amount_tax'];
	$amount_services = $r['amount_services'];
	$amount_total = $r['amount_total'];
	
	$invoice_paid = $r['invoice_paid'];
	$date_added = $r['date_added'];
	$daysSinceBilled = $r['daysSinceBilled'];
	
	$month_days = days_in_month(strtotime($billing_period));
	
	$billing_period_long = date("F Y", strtotime(str_replace('-', '/', $billing_period))  );
	$date_invoice_created = date("d F Y", strtotime(str_replace('-', '/', $date_added))  );
	$date_invoice_due = date("d F Y", strtotime("+1 month",strtotime(str_replace('-', '/', $date_added)))  );
	
	
	$time_str = date("Y-m-d", strtotime(str_replace('-', '/', $billing_period))  );
	$time_generated_str = time2str( strtotime(str_replace('-', '/', $date_added))  ); 
	
	$amount_total_str = number_format ( $amount_total , 2 );
	$amount_services_str = number_format ( $amount_services , 2 );
	$amount_tax_str = number_format ( $amount_tax , 2 );
	
	$sql = "SELECT client_name,client_address1, client_address2, city, country FROM modlr.clients WHERE client_id='%s';";
	$db = new db_helper();
	$db->CommandText($sql);
	$db->Parameters(session("client_id"));
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		$r = $db->Rows();
		
		$client_name = $r['client_name'];
		$client_address1 = $r['client_address1'];
		$client_address2 = $r['client_address2'];
		$client_city = $r['city'];
		$client_country = $r['country'];
		
	}
} else {
	header("Location: /manage/");
	die();
}



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
	<link rel="shortcut icon" href="http://www.modlr.co/wp-content/uploads/2014/06/favicon.ico">
    
    
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
	
    <!--Core CSS -->
    <link href="/bs3/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/bootstrap-reset.css" rel="stylesheet">
    <link href="/font-awesome/css/font-awesome.css" rel="stylesheet" />

    <!-- Custom styles for this template -->
    <link href="/css/style.css" rel="stylesheet">
    <link href="/css/style-responsive.css" rel="stylesheet" />
	<link href="/css/modeler_theme/jquery-ui-1.10.3.custom.css" rel="stylesheet">
	<link href="/js/contextMenu/jquery.contextMenu.css" rel="stylesheet" type="text/css" />

	<link rel="apple-touch-icon" href="/img/apple-touch-icon-precomposed.png"/>
	<link rel="apple-touch-icon" sizes="72x72" href="/img/apple-touch-icon-72x72-precomposed.png" />
	<link rel="apple-touch-icon" sizes="114x114" href="/img/apple-touch-icon-114x114-precomposed.png" />
	<link rel="apple-touch-icon" sizes="144x144" href="/img/apple-touch-icon-144x144-precomposed.png" />
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	
    <!-- Just for debugging purposes. Don't actually copy this line! -->
    <!--[if lt IE 9]>
    <script src="js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
    
    <link href="/css/invoice-print.css" rel="stylesheet" media="all">
</head>

<body>

<section id="container" class="print" >

    <!--main content start-->
    <section id="main-content">
        <section class="wrapper">
        
        <!-- page start-->

        <div class="row">
            <div class="col-md-12">
                <section class="panel">
                    <div class="panel-body invoice">
                        <div class="invoice-header">
                            <div class="invoice-title col-md-4 col-xs-2">
                                <h1>invoice</h1>
                                
                            </div>
                            <div class="invoice-info col-md-8 col-xs-10">

                                <div class="pull-right">
                                    <div class="col-md-6 col-sm-6 pull-left">
										<? echo COMPANY_ADDRESS;?></p>
                                    </div>
                                    <div class="col-md-6 col-sm-6 pull-right">
                                        <p>MODLR Pty Limited <br>
                                            ABN : 61 601 210 799<br>
                                            Email : sales@modlr.co</p>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="row invoice-to">
                            <div class="col-md-4 col-sm-4 pull-left">
                                <h4>Invoice To:</h4>
                                <h2><? echo $client_name;?></h2>
                                <p>
                                <?
                                	if( $client_address1 != "" )
                                		echo $client_address1 . "<br/>";
                                	if( $client_address2 != "" )
                                		echo $client_address2 . "<br/>";
                                	if( $client_city != "" )
                                		echo $client_city . "<br/>";
                                	if( $client_country != "" )
                                		echo $client_country . "<br/>";
                                ?>
                                </p>
                            </div>
                            <div class="col-md-4 col-sm-5 pull-right">
                                <div class="row">
                                    <div class="col-md-4 col-sm-5 inv-label">Invoice #</div>
                                    <div class="col-md-8 col-sm-7">INV<? echo $invoice_id;?></div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4 col-sm-5 inv-label">Usage Month:</div>
                                    <div class="col-md-8 col-sm-7"><? echo $billing_period_long;?></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 col-sm-5 inv-label">Invoice Created:</div>
                                    <div class="col-md-8 col-sm-7"><? echo $date_invoice_created;?></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 col-sm-5 inv-label">Invoice Due:</div>
                                    <div class="col-md-8 col-sm-7"><? echo $date_invoice_due;?></div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-12 inv-label">
                                        <h3>TOTAL DUE</h3>
                                    </div>
                                    <div class="col-md-12">
                                        <h1 class="amnt-value">$ <? echo $amount_total_str;?></h1>
                                    </div>
                                </div>


                            </div>
                        </div>
                        <table class="table table-invoice" >
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Item Description</th>
                                <th class="text-center">Monthly Cost</th>
                                <th class="text-center">Days Usage</th>
                                <th class="text-center">Total</th>
                            </tr>
                            </thead>
                            <tbody>
								<tr>
									<td>1</td>
									<td>
										<h4>Named User - Modeller (Single Free Modeller)</h4>
										<p>Named Users with the ability to create environments.</p>
									</td>
									<td class="text-center">$0.00</td>
									<td class="text-center"><? echo $month_days." of ".$month_days;?></td>
									<td class="text-center">$0.00</td>
								</tr>
<?

$sql = "SELECT clients_invoices_lines.product_id,units,clients_invoices_lines.price,revenue,product_pricing.product_name ,product_pricing.product_description  
			FROM modlr.clients_invoices_lines LEFT JOIN modlr.product_pricing ON product_pricing.product_id=clients_invoices_lines.product_id 
			WHERE clients_invoices_lines.invoice_id='%s';";
$db = new db_helper();
$db->CommandText($sql);
$db->Parameters($invoice_id);
$db->Execute();

$count = 2;
if ($db->Rows_Count() > 0) {
	while( $r = $db->Rows() ) { 
		
		$product_name = $r['product_name'];
		$product_description = $r['product_description'];
		
		$units = $r['units'];
		$price = $r['price'];
		$revenue = $r['revenue'];
		
		echo '<tr>
					<td>'.$count.'</td>
					<td>
						<h4>'.$product_name.'</h4>
						<p>'.$product_description.'</p>
					</td>
					<td class="text-center">$'.number_format($price,2).'</td>
					<td class="text-center">'.number_format($units,0).' of '.$month_days.'</td>
					<td class="text-center">$'.number_format($revenue,2).'</td>
				</tr>';	
		$count++;
	}
}
?>
                            
                            
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-md-8 col-xs-7 payment-method">
                                <h4>Payment Method</h4>
                                <p>1. Pay by Credit Card now using the Pay Now button below.</p>
                                <p>2. Direct Deposit: Bank details<br/>
									<div style='margin-left:40px;'>Empire Analytics Pty Ltd<br/>
									BSB: 633-000<br/>
									Account: 146294533</div></p>
                                
                                <br>
                                <h3 class="inv-label itatic">Thank you for your business</h3>
                            </div>
                            <div class="col-md-4 col-xs-5 invoice-block pull-right">
                                <ul class="unstyled amounts">
                                    <li>Sub - Total amount : $<? echo $amount_services_str;?></li>
                                    <!-- <li>Discount : 0% </li> -->
                                    <li>GST (10%): <? echo $amount_tax_str;?> </li>
                                    <li class="grand-total">Grand Total : $<? echo $amount_total_str;?></li>
                                </ul>
                            </div>
                        </div>


                    </div>
                </section>
            </div>
        </div>
        <!-- page end-->
        
        </section>
    </section>
    <!--main content end-->

</section>

<!-- Placed js at the end of the document so the pages load faster -->

<!--Core js-->
<script src="/js/jquery.js"></script>
<script src="/bs3/js/bootstrap.min.js"></script>
<script class="include" type="text/javascript" src="/js/jquery.dcjqaccordion.2.7.js"></script>
<script src="/js/jquery.scrollTo.min.js"></script>
<script src="/js/jQuery-slimScroll-1.3.0/jquery.slimscroll.js"></script>
<script src="/js/jquery.nicescroll.js"></script>

<!--common script init for all pages-->
<script src="/js/scripts.js"></script>

<script type="text/javascript">
    window.print();
</script>


</body>
</html>
