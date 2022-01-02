<?php
// On-demand page rendering
require 'config.php';
require KU_ROOTDIR . 'inc/functions.php';
require KU_ROOTDIR . 'inc/classes/board-post.class.php';

if (!isset($_GET['board']))
	http_response_code(404);
$board = $_GET['board'];
if (!isset($_GET['page']))
	http_response_code(404);
$res = $_GET['res'];
$ext = $_GET['ext'];

// determine the page
$page = $_GET['page'];
$is_index = ($page.'.'.$ext == KU_FIRSTPAGE);
if (
	(!$is_index && $page != 'catalog' && (int)$page < 0) 
	|| 
	($ext == 'json' && $page != 'catalog')
) {
	http_response_code(404);
}
// go to target file if it exists
if (file_exists(KU_ROOTDIR . $board . $res . '/' . $page . '.' . $ext))
	do_redirect(KU_BOARDSPATH . '/' . $board . $res . '/' . ($is_index ? '' : $page . '.html'), true);

if ($is_index)
	$page = 0;

if ($board == I0_OVERBOARD_DIR) {
	if ($page == 'catalog')
		http_response_code(404);
	$rendered_page = RegenerateOverboard(null, $page);
	do_redirect(KU_BOARDSPATH . '/' . I0_OVERBOARD_DIR . '/' . ((int)$rendered_page===0 ? '' : $rendered_page.'.html'), true);
}

$board_class = new Board($board);
if (!isset($board_class->board['name']))
	http_response_code(404);
if ($res == '/res') {
	if ($page!=0 && $board_class->RegenerateThreads($page, true))
		do_redirect(KU_BOARDSPATH . '/' . $board . $res . '/' . $page . '.html', true);
	else
		http_response_code(404);
}
else {
	if ($page === 'catalog') {
		if ($board_class->RegeneratePages(-1, INF, array(), 'catalog'))
			do_redirect(KU_BOARDSPATH . '/' . $board . '/catalog.' . $ext, true);
		else
			http_response_code(404);
	}
	else {
		if ($board_class->RegeneratePages($page, $page, array(), 'page'))
			do_redirect(KU_BOARDSPATH . '/' . $board . '/' . ($page==0 ? '' : $page. '.html') , true);
		else
			http_response_code(404);
	}
}