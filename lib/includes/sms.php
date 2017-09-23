<?php

/*
C_TWILIO_ACCOUNT
C_TWILIO_TOKEN

*/


function send_sms( $to, $body ) {
	
    // resource url & authentication
    $uri = 'https://api.twilio.com/2010-04-01/Accounts/' . C_TWILIO_ACCOUNT . '/Messages.json';
    $auth = C_TWILIO_ACCOUNT . ':' . C_TWILIO_TOKEN;
	echo $auth;
 
    // post string (phone number format= +15554443333 ), case matters
    $fields = 
        '&To=' .  urlencode( $to ) . 
        '&From=' . urlencode( "MODLR" ) . 
        //'&From=' . urlencode( "+61451266357" ) . 
        '&Body=' . urlencode( $body );
 
    // start cURL
    $res = curl_init();
     
    // set cURL options
    curl_setopt( $res, CURLOPT_URL, $uri );
    curl_setopt( $res, CURLOPT_POST, 3 ); // number of fields
    curl_setopt( $res, CURLOPT_POSTFIELDS, $fields );
    curl_setopt( $res, CURLOPT_USERPWD, $auth ); // authenticate
    curl_setopt( $res, CURLOPT_RETURNTRANSFER, true ); // don't echo
	curl_setopt( $res, CURLOPT_SSL_VERIFYPEER, false);
     
    // send cURL
    $result = curl_exec( $res );
	curl_close( $res );
	
	
    return $result;
}


?>