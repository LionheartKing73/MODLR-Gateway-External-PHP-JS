<?php

function getFooter() {
    $t_message = "<p>Kind Regards,<br/>";
    $t_message .= "Developers<br/>";
    $t_message .= "(Note: Please do not reply to this message. It comes from a notification-only address that cannot accept replies.)</p>";
    return $t_message;
}

function sendPasswordResetMail($to, $token, $uid) {
    $t_subject = "MODLR: Password Reset Mail";

    $reset_link = "http://go.MODLR.co/password/?u=".$uid."&k=".$token;

    $t_message = "<html>";
    $t_message .= "<head>";
    $t_message .= "<title>".$t_subject."</title>";
    $t_message .= "</head>";
    $t_message .= "<body>";
    $t_message .= "<h4>(Please do not forget to mark this message as not spam/junk)</h4>";
    $t_message .= "<p>You have requested for a password reset:</p>";
    $t_message .= "<p>Please follow this link to set your new password:<br/><a href='".$reset_link."' target='_blank'>".$reset_link."</a></p>";
    $t_message .= "<p>Copy and Paste the link if clicking is not possible</p>";
    $t_message .= getFooter();
    $t_message .= "</body>";
    $t_message .= "</html>";

    $message = "Please follow this link to set your new password ".$reset_link;

    return sendTransactionalMail($to, $t_subject, $message, $t_message);
}


function sendRegisterAlert($full_name, $email, $phone, $server_name, $memory, $region) {
	$to = "ben.hill@modlr.co";
	
    $t_subject = "MODLR Account Registration: ".$email;

    $t_message = "<html>";
    $t_message .= "<head>";
    $t_message .= "<title>".$t_subject."</title>";
    $t_message .= "</head>";
    $t_message .= "<body>";
   
    $t_message .= "<p>Hello,</p>";
    $t_message .= "<p>This email is just to let you know that a new account has been registered on MODLR</p>";
    
    $t_message .= "<table>";
    $t_message .= "<tr><td>Name:</td><td>".$full_name."</td></tr>";
    $t_message .= "<tr><td>Email:</td><td>".$email."</td></tr>";
    $t_message .= "<tr><td>Phone:</td><td>".$phone."</td></tr>";
    $t_message .= "<tr><td>Server Name:</td><td>".$server_name."</td></tr>";
    $t_message .= "<tr><td>Memory:</td><td>".$memory."</td></tr>";
    $t_message .= "<tr><td>Region:</td><td>".$region."</td></tr>";
    $t_message .= "</table>";

    $t_message .= getFooter();
    $t_message .= "</body>";
    $t_message .= "</html>";

    $message = "This email is just to let you know that a new account has been created. \r\nName: " . $full_name . "\r\nEmail: " . $email . "\r\nPhone: " . $phone;

    return sendTransactionalMail($to, $t_subject, $message, $t_message);
}


function sendProvisionNotification($to, $server_name, $memory, $region) {
    $t_subject = "MODLR Server Provision Notice";

    $t_message = "<html>";
    $t_message .= "<head>";
    $t_message .= "<title>".$t_subject."</title>";
    $t_message .= "</head>";
    $t_message .= "<body>";
   
    $t_message .= "<p>Hello,</p>";
    $t_message .= "<p>This email is just to let you know that a server has been provisioned in your account.</p>";
    
    $t_message .= "<table>";
    $t_message .= "<tr><td>Name:</td><td>".$server_name."</td></tr>";
    $t_message .= "<tr><td>Memory:</td><td>".$memory."</td></tr>";
    $t_message .= "<tr><td>Region:</td><td>".$region."</td></tr>";
    $t_message .= "</table>";
    
    $t_message .= "<p>If this was not you please contact support immediately. You can access this server via the \"Manage Account\" page after logging in <a href='http://go.MODLR.co/'>here</a>.</p>";

    $t_message .= getFooter();
    $t_message .= "</body>";
    $t_message .= "</html>";

    $message = "This email is just to let you know that a server has been provisioned in your account. If this was not you please contact support immediately. You can access this server via the \"Manage Account\" page.";

    return sendTransactionalMail($to, $t_subject, $message, $t_message);
}



function sendInvoiceNotification($to, $invoice_id) {
    $t_subject = "MODLR Invoice Notice";

    $t_message = "<html>";
    $t_message .= "<head>";
    $t_message .= "<title>".$t_subject."</title>";
    $t_message .= "</head>";
    $t_message .= "<body>";
   
    $t_message .= "<p>Hello,</p>";
    $t_message .= "<p>This email is just to let you know that invoice \"INV".$invoice_id."\" has been generated for your last months usage of the MODLR service.</p>";
    $t_message .= "<p>You can access this invoice via the \"Manage Account\" page after logging in <a href='http://go.MODLR.co/'>here</a>.</p>";

    $t_message .= getFooter();
    $t_message .= "</body>";
    $t_message .= "</html>";

    $message = "This email is just to let you know that invoice \"INV".$invoice_id."\" has been generated for your last months usage of the MODLR service.";

    return sendTransactionalMail($to, $t_subject, $message, $t_message);
}


function sendAccountActivation($to, $uid, $name, $password, $plan, $activation_code) {
    $t_subject = "MODLR Account Activation";

    $activation_link = "http://go.MODLR.co/?user=".$uid."&code=".$activation_code;

    $t_message = "<html>";
    $t_message .= "<head>";
    $t_message .= "<title>".$t_subject."</title>";
    $t_message .= "</head>";
    $t_message .= "<body>";
   
    $t_message .= "<p>Hello ".$name.",</p>";
    $t_message .= "<p>Your MODLR account has been created. Your login details are as follows:</p>";
    $t_message .= "<table>";
    $t_message .= "<tr><td>Login:</td><td>".$to."</td></tr>";
    $t_message .= "<tr><td>Password:</td><td>".$password."</td></tr>";
    $t_message .= "<tr><td colspan='2'>".$plan."</td></tr>";
    $t_message .= "</table>";
    
    $t_message .= "<p>Please follow this link to activate your account: <a href='".$activation_link."' target='_blank'>".$activation_link."</a></p>";
    $t_message .= "<p>Copy and Paste the link if clicking is not possible</p>";
    $t_message .= getFooter();
    $t_message .= "</body>";
    $t_message .= "</html>";

    $message = "Welcome to MODLR. Your login is \"".$to."\" and your password is \"".$password."\". Please follow this link to set your new password ".$activation_link;
	
    return sendTransactionalMail($to, $t_subject, $message, $t_message);
}

function sendTransactionalMailReply($to,$subject,$htmlMessage, $replyTo) {
    if (IsTestMode()) return;

	$post_data = array(
		'Username' => 'MODLR',
		'Password' => '723909EX',
		'FromEmail' => 'info@MODLR.co',
		'FromName' => 'MODLR [no-reply]',
		'ToEmailAddress' => $to,
		'Subject' => $subject,
		'MessageHTML' => $htmlMessage,
		'Options' => 'OpenTrack=True,ClickTrack=True,TransactionalGroupID=1466261,ReplyTo=' . $replyTo,
		'TransactionalGroupID' => '1466261'
	);
 

	try
	{
		//echo "<!-- Making Request -->";
		$response = post_request("http://api.jangomail.com/api.asmx/SendTransactionalEmail", $post_data);
		/*
		echo "<!-- ";
		print_r($response);
		echo "-->";*/
		return "";
	}
	catch(SoapFault $e)
	{
		echo "<!-- ".$e->getMessage()." -->";
		return $e->getMessage();
	}
}


function sendMail($email_to,$email_reply_to,$email_subject,$email_message) {
	$styleAdd = '<style>.anchor { border:0px; font-size:12px;color: #999;text-decoration:none;font-family: "Open Sans", "Segoe UI", Arial, sans-serif; }</style>';
	$footerAdd = '<div style="padding:20px;text-align:right;"><a href="http://www.modlr.co" class="anchor"><br/><img width="140" src="https://go.modlr.co/images/poweredByMODLRh100px.png" style="padding-top:4px;"/></a></div>';
	
    $headers = 'MIME-Version: 1.0' . "\r\n" .
				'From: MODLR [no-reply] <info@MODLR.co>'."\r\n" .
				'Reply-To: '.$email_reply_to."\r\n" .
				'Subject: '.$email_subject."\r\n" .
				'Content-type: text/html; charset=UTF-8'."\r\n" .
				'MIME-Version: 1.0' . "\r\n" . 
				'X-Mailer: PHP/' . phpversion();
	
	if( strpos(strtolower($email_message),"<head>") !== false ) {
		$email_message = str_replace("<head>","<head>".$styleAdd,$email_message);
	} else {
		$email_message = $styleAdd.$email_message;
	}
	
	if( strpos(strtolower($email_message),"</body>") !== false ) {
		$email_message = str_replace("</body>",$footerAdd."</body>",$email_message);
	} else {
		$email_message = $email_message.$footerAdd;
	}
	//$email_message .= $footerAdd;
    $result = mail($email_to, $email_subject, $email_message, $headers); 
	
	
	return $result;
}

function sendTransactionalMail($to,$subject,$plainMessage,$htmlMessage) {
    if (IsTestMode()) return;
	
	//sendMail($to,"info@MODLR.co",$subject,$htmlMessage);
	//return;
	
	
	$post_data = array(
		'Username' => 'MODLR',
		'Password' => '723909EX',
		'FromEmail' => 'no-reply@MODLR.co',
		'FromName' => 'MODLR [no-reply]',
		'ToEmailAddress' => $to,
		'Subject' => $subject,
		'MessagePlain' => $plainMessage,
		'MessageHTML' => $htmlMessage,
		'Options' => 'OpenTrack=True,ClickTrack=True,TransactionalGroupID=1466261',
		'TransactionalGroupID' => '1466261'
	);


	try
	{
		//echo "<!-- Making Request -->";
		$response = post_request("http://api.jangomail.com/api.asmx/SendTransactionalEmail", $post_data);
		/*
		echo "<!-- ";
		print_r($response);
		echo "-->";*/
		return "";
	}
	catch(SoapFault $e)
	{
		echo "<!-- ".$e->getMessage()." -->";
		return $e->getMessage();
	}
}


function post_request($url, $data, $referer='') {

    // Convert the data array into URL Parameters like a=b&foo=bar etc.
    $data = http_build_query($data);

    // parse the given URL
    $url = parse_url($url);

    if ($url['scheme'] != 'http') {
        die('Error: Only HTTP request are supported !');
    }

    // extract host and path:
    $host = $url['host'];
    $path = $url['path'];

    // open a socket connection on port 80 - timeout: 30 sec
    $fp = fsockopen($host, 80, $errno, $errstr, 30);

    if ($fp){

        // send the request headers:
        fputs($fp, "POST $path HTTP/1.1\r\n");
        fputs($fp, "Host: $host\r\n");

        if ($referer != '')
            fputs($fp, "Referer: $referer\r\n");

        fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
        fputs($fp, "Content-length: ". strlen($data) ."\r\n");
        fputs($fp, "Connection: close\r\n\r\n");
        fputs($fp, $data);

        $result = '';
        while(!feof($fp)) {
            // receive the results of the request
            $result .= fgets($fp, 128);
        }
    }
    else {
        return array(
            'status' => 'err',
            'error' => "$errstr ($errno)"
        );
    }

    // close the socket connection:
    fclose($fp);

    // split the result header from the content
    $result = explode("\r\n\r\n", $result, 2);

    $header = isset($result[0]) ? $result[0] : '';
    $content = isset($result[1]) ? $result[1] : '';

    // return as structured array:
    return array(
        'status' => 'ok',
        'header' => $header,
        'content' => $content
    );
}


?>
