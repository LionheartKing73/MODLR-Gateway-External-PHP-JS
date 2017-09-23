<?php

$config_server = "localhost"; //Database host
$config_database = "modlr"; //Database name
$config_username = "root"; //Database username
$config_password = "DevelopmentPa55w0rd"; //Database password

$config_app_name = "Modlr";
$config_app_name_short = "Modlr";
$config_app_version = "1.3";

/*
API Key
$twilioAccountSID = "SK31e8738653fbd5af7cf471487e6093ee";
$twilioToken = "bx6KYJtRX80HkEQ0q6acqoc3CIU550pU";

Account Key
*/
$twilioAccountSID = "ACc1dd937ddae0c7cda9a3d8efc06c2346";
$twilioToken = "522386bcd10bd22dd139ee27e75a90da";

define("C_TWILIO_ACCOUNT",$twilioAccountSID);
define("C_TWILIO_TOKEN",$twilioToken);

define("SERVICE_SERVER","server.service");
define("SERVICE_MODEL","model.service");
define("SERVICE_DATASOURCE","datasource.service");
define("SERVICE_COLLABORATOR","collaborator.service");

define("C_CERT_ROOT","/var/go/cron/id_rsa");

define("C_DB_HOST",$config_server);
define("C_DB_NAME",$config_database);
define("C_DB_USER",$config_username);
define("C_DB_PASS",$config_password);

define("C_APP_NAME",$config_app_name);
define("C_APP_NAME_SHORT",$config_app_name_short);
define("C_APP_VERSION",$config_app_version);
define("C_APP_VERSION_FORMAT","v" . $config_app_version);

define("COMPANY_ADDRESS","8/44a Bayswater Rd, Sydney <br>NSW 2011, Australia");

define("STRIPE_SECRET_KEY","sk_live_4Mx8qA4XT8AK1lZaL73PVLlm");
define("STRIPE_PUBLISHABLE_KEY","pk_live_4Mx81dJ0W9R4812GlDU1AiJh");


define("C_DB_NAME_HOST","datastore");
define("C_DB_USER_HOST","root");
define("C_DB_PASS_HOST","DevelopmentPa55w0rd");





//Do not edit below this line
$version = C_APP_VERSION_FORMAT;
$maintenance = false;

?>