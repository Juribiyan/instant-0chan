<?php
// On-demand page rendering
require 'config.php';
require KU_ROOTDIR . 'inc/functions.php';
require KU_ROOTDIR . 'inc/classes/board-post.class.php';

if (!isset($_GET['board']))
	http_response_code(404);
if (!isset($_GET['page']))
	http_response_code(404);
$page = (int)$_GET['page'];
if ($page <= 0)
	http_response_code(404);
if (file_exists(KU_ROOTDIR . $_GET['board'] . '/' . $page . '.html'))
	do_redirect(KU_BOARDSPATH . '/' . $_GET['board'] . '/' . $page . '.html', true);
$board_class = new Board($_GET['board']);
if (!isset($board_class->board['name']))
	http_response_code(404);
if ($board_class->RegeneratePages($page, $page, array(), true))
	do_redirect(KU_BOARDSPATH . '/' . $_GET['board'] . '/' . $page . '.html', true);
else
	http_response_code(404);