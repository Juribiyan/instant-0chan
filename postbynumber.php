<?php
require 'config.php';

if (!isset($_GET['b']) || !isset($_GET['p'])) {
	http_response_code(404);
} 
else {
	$board  = $_GET['b'];
	$post  = $_GET['p'];
}

$result = $tc_db->GetOne("SELECT `parentid` FROM `".KU_DBPREFIX."posts` WHERE `id`=? AND `boardid`=(SELECT `id` FROM `boards` WHERE `name`=?)", array($post, $board));
if (!!$result || $result === '0') {
	$thread_id = ($result == 0) ? $post : $result;
	redirect(KU_WEBPATH.KU_BOARDSFOLDER.$board.'/res/'.$thread_id.'.html#'.$post, 301);
}
else {
	header('X-PHP-Response-Code: 404', true, 404);
}

function redirect($url, $statusCode = 303) {
	header('Location: ' . $url, true, $statusCode);
	die();
}
?>