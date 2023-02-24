<?php
require 'config.php';
require 'inc/func/yac.php';

if (!isset($_GET['b']) || !isset($_GET['p'])) {
	http_response_code(404);
}
else {
	$board  = $_GET['b'];
	$post  = $_GET['p'];
}
$key = implode(':', [$board, $post]);

if ($result = cache_get($key)) return $result;

$result = $tc_db->GetOne("SELECT `parentid` FROM `".KU_DBPREFIX."posts` WHERE `id`=? AND `boardid`=(SELECT `id` FROM `".KU_DBPREFIX."boards` WHERE `name`=?)", array($post, $board));
cache_set($key, $result);

if ($result === 0) {
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