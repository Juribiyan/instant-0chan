<?php
define('JSONFOLDER', realpath(dirname(__FILE__)).'/coub_json/');
define('URLPREFIX', 'http://coub.com/api/oembed.json?url=http://coub.com/view/');

if(!isset($_GET['code'])) exit('Nigga what are yo doin');

function remote_file_exists($url) {
	$file_headers = @get_headers($url);
	if($file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return false;
	}
	else {
	    return true;
	}
}

function serve_file($location) {
	header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
    header("Cache-Control: public");
    header("Content-Type: application/json");
    header("Content-Transfer-Encoding: Binary");
    header("Content-Length:".filesize($location));
    readfile($location);
    die();        
}

function download_file($from, $to) {
	$fp = fopen($to, 'w');
    $ch = curl_init($from);
    curl_setopt($ch, CURLOPT_FILE, $fp);
    $data = curl_exec($ch);
    curl_close($ch);
    fclose($fp);
}

$local_link = JSONFOLDER.$_GET['code'].'.json';
$remote_link = URLPREFIX.$_GET['code'];

if(file_exists($local_link)) serve_file($local_link);
elseif(remote_file_exists($remote_link)) {
	download_file($remote_link, $local_link);
	serve_file($local_link);
}
else serve_file(JSONFOLDER.'__404__.json'); ?>