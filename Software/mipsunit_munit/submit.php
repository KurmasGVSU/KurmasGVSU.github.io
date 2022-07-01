<?php
/**
 * Created by IntelliJ IDEA.
 * User: kurmasz
 * Date: 7/22/13
 * Time: 12:54 PM
 * To change this template use File | Settings | File Templates.
 */
// Function to get the client ip address
function get_client_ip()
{
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if (getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if (getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if (getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if (getenv('HTTP_FORWARDED'))
        $ipaddress = getenv('HTTP_FORWARDED');
    else if (getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';

    return $ipaddress;
}

$date = date('m/d/Y H:i:s', time());

$_POST['CLIENT_IP'] = get_client_ip();
$_POST['CLIENT_PLATFORM'] = $_SERVER['HTTP_USER_AGENT'];
$_POST['time'] = $date;

$user_info = serialize($_POST);
file_put_contents("/home/kurmasz/mspec_private/munit_download_log.txt", $user_info . "\n\n", FILE_APPEND);

if (isset($_POST['jar']))
   $file_name = 'dist/munit.jar';
else
    $file_name = 'dist/munit.jar';


header("Content-Type: application/force-download");
header('Content-Disposition: attachment; filename="'. basename($file_name) . '"');
header("Content-Length: " . filesize($file_name));

$fp = fopen($file_name, "r");
fpassthru($fp);
fclose($fp);

?>
