<?php
include_once("../../lib/lib.php");
require 'lib/XeroOAuth.php';

if( querystring("wipe") != "" ) {
	session_destroy();
  	header("Location: /connectors/xero/public.php");
}


/**
 * Define for file includes
 */
define ( 'BASE_PATH', dirname(__FILE__) );

/**
 * Define which app type you are using:
 * Private - private app method
 * Public - standard public app method
 * Public - partner app method
 */
define ( "XRO_APP_TYPE", "Public" );

/**
 * Set a user agent string that matches your application name as set in the Xero developer centre
 */
$useragent = "Xero-OAuth-PHP Public";

/**
 * Set your callback url or set 'oob' if none required
 * Make sure you've set the callback URL in the Xero Dashboard
 * Go to https://api.xero.com/Application/List and select your application
 * Under OAuth callback domain enter localhost or whatever domain you are using.
 */
define ( "OAUTH_CALLBACK", 'http://go.modlr.co/connectors/xero/public.php' );

/**
 * Application specific settings
 * Not all are required for given application types
 * consumer_key: required for all applications
 * consumer_secret: for partner applications, set to: s (cannot be blank)
 * rsa_private_key: application certificate private key - not needed for public applications
 * rsa_public_key: application certificate public cert - not needed for public applications
 */

/**
 * Persist the OAuth access token and session handle somewhere
 * In my example I am just using the session, but in real world, this is should be a storage engine
 *
 * @param array $params the response parameters as an array of key=value pairs
 */
function persistSession($response)
{
    if (isset($response)) {
        $_SESSION['access_token']       = $response['oauth_token'];
        $_SESSION['oauth_token_secret'] = $response['oauth_token_secret'];
      	if(isset($response['oauth_session_handle']))  
      		$_SESSION['session_handle']     = $response['oauth_session_handle'];
    } else {
        return false;
    }

}

/**
 * Retrieve the OAuth access token and session handle
 * In my example I am just using the session, but in real world, this is should be a storage engine
 *
 */
function retrieveSession()
{
    if (isset($_SESSION['access_token'])) {
        $response['oauth_token']            =    $_SESSION['access_token'];
        $response['oauth_token_secret']     =    $_SESSION['oauth_token_secret'];
        
        if( isset($_SESSION['session_handle']) )
        	$response['oauth_session_handle']   =    $_SESSION['session_handle'];
        else
        	$response['oauth_session_handle']   =    null;
        	
        return $response;
    } else {
        return false;
    }

}

function outputError($XeroOAuth)
{
    echo 'Error: ' . $XeroOAuth->response['response'] . PHP_EOL;
    pr($XeroOAuth);
}

/**
 * Debug function for printing the content of an object
 *
 * @param mixes $obj
 */
function pr($obj)
{

    if (!is_cli())
        echo '<pre style="word-wrap: break-word">';
    if (is_object($obj))
        print_r($obj);
    elseif (is_array($obj))
        print_r($obj);
    else
        echo $obj;
    if (!is_cli())
        echo '</pre>';
}

function is_cli()
{
    return (PHP_SAPI == 'cli' && empty($_SERVER['REMOTE_ADDR']));
}



$signatures = array (
		'consumer_key' => '1W6AM0UMBTDSGLFSXSIHRGNKHL9DRT',
		'shared_secret' => 'XCECCLDTSHRQFSI2J0OO8P5EMSYWIM',
		// API versions
		'core_version' => '2.0',
		'payroll_version' => '1.0' 
);

if (XRO_APP_TYPE == "Private" || XRO_APP_TYPE == "Partner") {
	$signatures ['rsa_private_key'] = BASE_PATH . '/certs/privatekey.pem';
	$signatures ['rsa_public_key'] = BASE_PATH . '/certs/publickey.cer';
}
if (XRO_APP_TYPE == "Partner") {
	$signatures ['curl_ssl_cert'] = BASE_PATH . '/certs/entrust-cert-RQ3.pem';
	$signatures ['curl_ssl_password'] = '1234';
	$signatures ['curl_ssl_key'] = BASE_PATH . '/certs/entrust-private-RQ3.pem';
}

$XeroOAuth = new XeroOAuth ( array_merge ( array (
		'application_type' => XRO_APP_TYPE,
		'oauth_callback' => OAUTH_CALLBACK,
		'user_agent' => $useragent 
), $signatures ) );


include_once("../../lib/header.php");
?>
    	<!--external css-->
    	<link rel="stylesheet" type="text/css" href="/js/fuelux/css/tree-style.css" />
    	<style type='text/css'>
    		.import-row {
    			height: 35px;
    			vertical-align: middle;
    		}
    		.import-cell {
    			padding-left: 10px;
    		}
    		.import-row.odd {
    			background-color:#EEE;
    		}
    	</style>
		<title>MODLR » Xero Connector</title>
		
<?
include_once("../../lib/body_start.php");
?>
        <div class="row">
 
			<div class="col-sm-12">
                <section class="panel">
                    <header class="panel-heading">
                        Xero Connector
                        <span class="tools pull-right">
                            <a href="javascript:;" class="fa fa-chevron-down"></a>
                         </span>
                    </header>
                    <div class="panel-body">
<?

$ready_to_rock = false;

$initialCheck = $XeroOAuth->diagnostics ();
$checkErrors = count ( $initialCheck );
if ($checkErrors > 0) {
	// you could handle any config errors here, or keep on truckin if you like to live dangerously
	
	foreach ( $initialCheck as $check ) {
		echo 'Error: ' . $check . PHP_EOL;
	}
	
	//
} else {
	
	$here = XeroOAuth::php_self ();
	$oauthSession = retrieveSession ();
	
	if( $oauthSession === false && !isset ( $_REQUEST ['authenticate'] ) &&  !isset ( $_REQUEST ['oauth_token'] )  ) {
	
	?>
					<h3>Authenticate with Xero</h3>
					<p>
						To import your financials from Xero into a model, we need to first authenticate your account with Xero. Below are the options available for the various modules of Xero.
					</p>
					<center>
						<button type="button" class="btn btn-success" onclick="window.location='?authenticate=1';">Xero</button>&nbsp;
						<button type="button" class="btn btn-success" onclick="window.location='?authenticate=2';">Xero with Payroll</button>
					</center>
	<?
	
	} else {
		
		if (isset ( $_REQUEST ['oauth_verifier'] )) {
			$XeroOAuth->config ['access_token'] = $_SESSION ['oauth'] ['oauth_token'];
			$XeroOAuth->config ['access_token_secret'] = $_SESSION ['oauth'] ['oauth_token_secret'];
		
			$code = $XeroOAuth->request ( 'GET', $XeroOAuth->url ( 'AccessToken', '' ), array (
					'oauth_verifier' => $_REQUEST ['oauth_verifier'],
					'oauth_token' => $_REQUEST ['oauth_token'] 
			) );
		
			if ($XeroOAuth->response ['code'] == 200) {
			
				$response = $XeroOAuth->extract_params ( $XeroOAuth->response ['response'] );
				$session = persistSession ( $response );
			
				unset ( $_SESSION ['oauth'] );
				$ready_to_rock = true;
			} else {
				outputError ( $XeroOAuth );
			}
			// start the OAuth dance
		} elseif (isset ( $_REQUEST ['authenticate'] ) || isset ( $_REQUEST ['authorize'] )) {
			$params = array (
					'oauth_callback' => OAUTH_CALLBACK 
			);
		
			$response = $XeroOAuth->request ( 'GET', $XeroOAuth->url ( 'RequestToken', '' ), $params );
		
			if ($XeroOAuth->response ['code'] == 200) {
			
				$scope = "";
				// $scope = 'payroll.payrollcalendars,payroll.superfunds,payroll.payruns,payroll.payslip,payroll.employees,payroll.TaxDeclaration';
				if ($_REQUEST ['authenticate'] > 1)
					$scope = 'payroll.employees,payroll.payruns';
			
				//print_r ( $XeroOAuth->extract_params ( $XeroOAuth->response ['response'] ) );
				$_SESSION ['oauth'] = $XeroOAuth->extract_params ( $XeroOAuth->response ['response'] );
			
				$authurl = $XeroOAuth->url ( "Authorize", '' ) . "?oauth_token={$_SESSION['oauth']['oauth_token']}&scope=" . $scope;
				
				?>
					<h3>Authenticating with Xero</h3>
					<p>
						To complete the authentication with Xero please click the button below to visit the Xero Authentication page.
					</p>
					<center>
						<button type="button" class="btn btn-success" onclick="window.location='<? echo $authurl;?>';">Proceed to Xero</button>
					</center>
				<?
				
			} else {
				outputError ( $XeroOAuth );
			}
		} else { 
			$ready_to_rock = true;
		}
	
	}
}

if( $ready_to_rock ) {

$json = "{\"tasks\": [";
$json .= "{\"task\": \"home.directory\"}";
$json .= "]}";

$results = api_short(SERVICE_SERVER, $json);
$contents = $results->results[0]->models;


?>
<h3>Xero Account Connected</h3>
<p>The model has been connected with your Xero account. </p>

<div class="row" style="margin-left: 0px;margin-right: 0px">
	<div class="col-md-6">
		<h4>Importing information from Xero</h4>
		<p>
			Modlr imports the financial and debtor information into a set of standard cubes. You can then create Reports (Workviews) of this data in the format you prefer.
			
			<form class="form-horizontal" action="public.php" method='post'>
				<div class="form-group">
					<label class="col-lg-2 col-sm-2 control-label">Model:</label>
					<div class="col-lg-6">
						<select class="form-control" style="width: 300px" id="source" name="model">
<?
			for($i=0;$i<count($contents);$i++) {
				$model = $contents[$i];
				$sel = "";
				if( form("model") == $model->id ) {
					$sel = " selected";
				}
				echo "<option value='".$model->id."'".$sel.">".$model->name."</option>";
			}
?>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-2 col-sm-2 control-label">Month:</label>
					<div class="col-lg-6">
						<select class="form-control" style="width: 300px" id="month" name="month">
<?
$months = array();
for ($i = 0; $i < 48; $i++) {
    $timestamp = mktime(0, 0, 0, date('n') - $i, 1);
    
    $sel = "";
	if( form("month") == date('Y-m-01', $timestamp) ) {
		$sel = " selected";
	}
    
    echo "<option value='".date('Y-m-01', $timestamp)."'".$sel.">".date('M Y', $timestamp)."</option>";
}

?>
						</select>
					</div>
				</div>
				
				<table width='100%'>
					<tr class='import-row'>
						<td class='import-cell' width='99%'>Profit and Loss Information</td>
						<td><button type="submit" name='btnImport' value='ProfitAndLoss' class="btn btn-success">Import</button></td>
					</tr>
					<tr class='import-row odd'>
						<td class='import-cell' >Balance Sheet Information</td>
						<td><button type="submit" name='btnImport' value='BalanceSheet' class="btn btn-success">Import</button></td>
					</tr>
					<tr class='import-row'>
						<td class='import-cell' >Debtor Information</td>
						<td><button type="submit" name='btnImport' value='Debtors' class="btn btn-success">Import</button></td>
					</tr>
					<tr class='import-row'>
						<td class='import-cell' >Payroll Information</td>
						<td><button type="submit" name='btnImport' value='Payroll' class="btn btn-success">Import</button></td>
					</tr>
				</table>
			</form>
			
		</p>
	</div>
	<div class="col-md-6">
		<h4>Build Logs</h4>
		<div class="timeline-messages">
<?
include_once("imports.php");
if( form("btnImport") != "" ) {
	if( form("model") != "" ) {
		
		importData(form("model"), form("btnImport"), form("month"));
	}
}


?>
			
		</div>
	</div>
</div>

<?



}

?>                      
                    </div>
                </section>
            </div>
        
        </div>
<?
include_once("../../lib/body_end.php");
include_once("../../lib/footer.php");
?>

