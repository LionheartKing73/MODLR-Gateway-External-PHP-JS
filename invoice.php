<?
include_once("lib/lib.php");

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
	
	$stripe_amount_total = round($amount_total,2)*100;
	
	$sql = "SELECT client_name,client_address1, client_address2, city, country,stripe_id FROM modlr.clients WHERE client_id='%s';";
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
		$stripe_id = $r['stripe_id'];
	}
} else {
	header("Location: /manage/");
	die();
}


$msg = "";
if( form("stripeToken") != "" && $invoice_paid == 0 ) { 
	$stripeToken = form("stripeToken");
	$stripeEmail = form("stripeEmail");

	include_once("lib/Stripe/Stripe.php");
	
	// Set your secret key: remember to change this to your live secret key in production
	// See your keys here https://dashboard.stripe.com/account
	Stripe::setApiKey(STRIPE_SECRET_KEY);

	// Get the credit card details submitted by the form
	$token = $_POST['stripeToken'];

	// Create the charge on Stripe's servers - this will charge the user's card
	try {
		
		$customer = null;
		if( $stripe_id == "" ) {
		
			$customer = Stripe_Customer::create(array(
				'email' => session('username'),
				'card'  => $token
			));
			
			$stripe_id = $customer->id;
			$db = new db_helper();
			$db->CommandText("UPDATE clients SET stripe_id='%s' WHERE client_id='%s';");
			$db->Parameters($stripe_id);
			$db->Parameters(session("client_id"));
			$db->Execute();
			
			$charge = Stripe_Charge::create(array(
				'customer' => $stripe_id,
				"amount" => $stripe_amount_total, // amount in cents, again
				"currency" => "aud",
				"card" => $customer->default_card,
				"description" => session("client_id") . "," . session('username') 
			));
			
		} else {
			
			
			$charge = Stripe_Charge::create(array(
				'customer' => $stripe_id,
				"amount" => $stripe_amount_total, // amount in cents, again
				"currency" => "aud",
				"description" => session("client_id") . "," . session('username') 
			));
			
		}
		

		
		
		
		
		
 		
		
		$db = new db_helper();
		$db->CommandText("UPDATE clients_invoices SET invoice_paid='1' WHERE invoice_id='%s';");
		$db->Parameters($invoice_id);
		$db->Execute();
		
		$msg = '<div class="alert alert-success clearfix">
                <span class="alert-icon"><i class="fa fa-file-o"></i></span>
                <div class="notification-info">
                    <ul class="clearfix notification-meta">
                        <li class="pull-left notification-sender"><span><a href="/invoice/?id='.$invoice_id.'">INV'.$invoice_id.'</a></span></li>
                    </ul>
                    <p style="padding-bottom:0px;">
                    	<b>Credit Card Payment Suceeded.</b><br/>
                        Thank you for your business.
                    </p>
                </div>
            </div>';
            
        $invoice_paid = 1;
	} catch(Stripe_CardError $e) {
	  // The card has been declined
	  $msg = '<div class="alert alert-danger clearfix">
                <span class="alert-icon"><i class="fa fa-file-o"></i></span>
                <div class="notification-info">
                    <ul class="clearfix notification-meta">
                        <li class="pull-left notification-sender"><span><a href="/invoice/?id='.$invoice_id.'">INV'.$invoice_id.'</a></span></li>
                    </ul>
                    <p style="padding-bottom:0px;">
                    	<b>Credit Card Payment Failed.</b><br/>
                        Please check your details and account balance and try again.
                    </p>
                </div>
            </div>';
	} catch (Stripe_InvalidRequestError $e) {
    // You screwed up in your programming. Shouldn't happen!
    	$msg = '<div class="alert alert-danger clearfix">
                <span class="alert-icon"><i class="fa fa-file-o"></i></span>
                <div class="notification-info">
                    <ul class="clearfix notification-meta">
                        <li class="pull-left notification-sender"><span><a href="/invoice/?id='.$invoice_id.'">INV'.$invoice_id.'</a></span></li>
                    </ul>
                    <p style="padding-bottom:0px;">
                    	<b>Credit Card Payment Failed.</b><br/>
                        Please press the "Pay with Card" button at the bottom to rectify the situation.
                    </p>
                </div>
            </div>';
	}
	

}

//stripeToken
//stripeEmail



include_once("lib/header.php");

?>
    	<!--external css-->
    	<link rel="stylesheet" type="text/css" href="/js/fuelux/css/tree-style.css" />
    	
		<title>MODLR » Invoice</title>
		
<?
include_once("lib/body_start.php");
?>

 		<!-- page start-->

		<div class="row">
			<div class="col-md-12">
				<ul class="breadcrumbs-alt">
					<li>
						<a href="/home/">Home</a>
					</li>
					<li>
						<a href="/manage/">Manage Account</a>
					</li>
					<li>
						<a class="active-trail active" href="/invoice/?id=<? echo $invoice_id;?>">Invoice: INV<? echo $invoice_id;?></a>
					</li>
				</ul>
			</div>
		</div>


        <div class="row">
            <div class="col-md-12">
                <section class="panel">
                    <div class="panel-body invoice">
                    <? echo $msg;?>
                    
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
                                        <h1 class="amnt-value">$ <? 
                                        
                                        if( $invoice_paid == 1 ) {
                                        	echo "0.00 - PAID";
                                        } else {
                                        	echo $amount_total_str;
                                        }
                                        
                                        
                                        ?></h1>
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
                                <p>1. Pay by Credit Card now using the "Pay with Card" button below.</p>
                                <p>2. Direct Deposit: Bank details<br/>
									<div style='margin-left:40px;'>MODLR Pty Limited<br/>
									Commonwealth Bank<br/>
									BSB: 062-692<br/>
									Account: 2319 4035</div></p>
                                
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

                        <div class="text-center invoice-btn">
                            <?
                            	if( $invoice_paid == 0 ) {
                            ?>
                            
                            <!-- <a class="btn btn-success btn-lg"><i class="fa fa-check"></i> Pay Invoice </a> -->
                            <form action="" method="POST">
							  <script
								src="https://checkout.stripe.com/checkout.js" class="stripe-button"
								data-key="<? echo STRIPE_PUBLISHABLE_KEY;?>"
								data-amount="<? echo $stripe_amount_total;?>"
								data-name="Modlr Invoice"
								data-description="INV<? echo $invoice_id;?> ($<? echo $amount_total_str;?>)"
								data-image="/images/logo_square-300x300.png"
								data-currency="aud"
								data-email="<? echo session('username');?>"
								>
							  </script>
							</form>
							<br/>
							<?
                            	}
                            ?>
							
							
                            <div style='float:right;'>
                            <a href="/invoice/print/?id=<? echo $invoice_id;?>" target="_blank" class="btn btn-primary btn-lg"><i class="fa fa-print"></i> Print </a>
                            </div>
                        </div>

                    </div>
                </section>
            </div>
        </div>
        <!-- page end-->

<?
include_once("lib/body_end.php");
?>
<!--tree-->
<script src="/js/fuelux/js/tree.min.js"></script>
<!--script for this page-->
<script src="/js/tree.js"></script>
<?
include_once("lib/footer.php");
?>
