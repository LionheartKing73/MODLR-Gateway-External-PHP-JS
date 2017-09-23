<?

$contentType = $_POST["ct"];
$name = $_POST["nm"];
$data = $_POST["data"];

header('Content-Description: File Transfer');
header('Content-Type: '.$contentType);
header('Content-Disposition: attachment; filename='.$name);
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
//header('Content-Length: ' . strlen($data) );

print base64_decode($data);
flush();

?>