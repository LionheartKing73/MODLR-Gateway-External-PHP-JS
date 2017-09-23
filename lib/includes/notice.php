<?php

function pushDeviceNotice($message = "", $app_id = 1, $app_name = "''", $notification_id = 0, $user_id = 0) {
    DBopen();

    $tmpCert = "certs/spark_dev_final.pem";
    $tmpKey = "Rock11Rock11";

    $useCert = $tmpCert;
    $useKey = $tmpKey;
    //$useURL = "gateway.sandbox.push.apple.com" //Dev
    $useURL = "gateway.sandbox.push.apple.com"; //Prod
    $userPort = "2195";

    /* End of Configurable Items */

    $ctx = stream_context_create();
    stream_context_set_option($ctx, 'ssl', 'local_cert', $useCert);
    // assume the private key passphase was removed.
    stream_context_set_option($ctx, 'ssl', 'passphrase', $useKey);

    $fp = stream_socket_client('ssl://'.$useURL.':'.$userPort, $err, $errstr, 60,STREAM_CLIENT_CONNECT, $ctx);
    if (!$fp) {
        print "Failed to connect $err\r\n";
        return;
    }
    else print "Connection OK\r\n\r\n";

    $errortype = "''";
    if ($notification_id > 0) {
        // take notification template
    }

    $payload['aps'] = array (
        'alert' => $message,
        'badge' => 1,
        'sound' => 'default'
    );
    $payload['channel'] = array (
        'app_name' => $app_name,
        'app_id' => $app_id,
        'error_type' => $errortype
    );
    $payloadStr = json_encode($payload);

    $deviceToken = "";
    $db = new db_helper();
    $db->CommandText("SELECT * FROM users_devices".($user_id > 0 ? " WHERE user_id = ".$user_id : "").";");
    $db->Execute();
    if ($db->Rows_Count() > 0) {
        while (($r = $db->Rows()) != FALSE) {
            $deviceToken = $r['device_id'];
            if ($deviceToken != "(null)" && trim($deviceToken) != "") {
                $deviceToken = str_replace("<", "", $deviceToken);
                $deviceToken = str_replace(">", "", $deviceToken);
                $deviceToken = str_replace(" ", "", $deviceToken);

                $apnsMessage .= chr(0) . chr(0) . chr(32) . pack('H*', trim($deviceToken)) . chr(0) . chr(strlen($payloadStr)) . $payloadStr;
                print "sending message :" . $payloadStr . " to {" . trim($deviceToken) . "} \r\n\r\n";
                $description[] = "Sending Message: " . $payloadStr . " to {" . trim($deviceToken) . "}";
            }
        }
    }

    if ($apnsMessage != "") {
        fwrite($fp, $apnsMessage);
        echo "Message Sent: ". $apnsMessage;
        $description[] = "Message Sent: ". $apnsMessage;
    }
    print_f($description);

    print "Closing Connection\r\n";
    fclose($fp);
}

?>