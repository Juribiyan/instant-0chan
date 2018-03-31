<?php
header('Content-type: text/html; charset=utf-8');

require 'config.php';
if (!isset($_GET['b']) || !isset($_GET['t']) || !isset($_GET['p'])) {
	if (!isset($_SERVER['PATH_INFO'])) {
		die();
	}

	$pairs = explode('/', $_SERVER['PATH_INFO']);
	if (count($pairs) < 4) {
		die();
	}

	$board  = $pairs[1];
	$thread = $pairs[2];
	$posts  = $pairs[3];
} else {
	$board  = $_GET['b'];
	$thread = $_GET['t'];
	$posts  = $_GET['p'];
}

if ($board == '' || $thread == '' || $posts == '') {
	die();
}

$singlepost = (isset($_GET['single'])) ? true : false;

require KU_ROOTDIR . 'inc/functions.php';
require KU_ROOTDIR . 'inc/classes/board-post.class.php';

$executiontime_start = microtime_float();

$results = $tc_db->GetOne("SELECT COUNT(*) FROM `".KU_DBPREFIX."boards` WHERE `name` = ".$tc_db->qstr($board)." LIMIT 1");
if ($results == 0) {
	die('Invalid board.');
}
$board_class = new Board($board);

$replies = false;

$postids = getQuoteIds($posts, $replies);
if (count($postids) == 0) {
	die('No valid posts specified.');
}

	$noboardlist = false;
	$hide_extra = false;
	$replies = false;

	$postidquery = '';
	if (count($postids) > 1 && (!empty($postids[1]) || !empty($postids['BETWEEN']))) {
		$i = 0;
		foreach($postids as $key=>$postid) {
			if (is_numeric($key)) {
				if($i != 0)
					$postidquery .= " OR ";
				$postidquery .= "(`id` = ".intval($postid)." AND ";
				if ($postids[$key] == $thread) {
					$postidquery .= "(`id` = ".$tc_db->qstr($thread)." AND `parentid` = 0))";
				} else {
					$postidquery .= "`parentid` = " . $tc_db->qstr($thread) . " ) ";
				}
			}
			elseif($key == 'BETWEEN') {
				if (count($postids['BETWEEN'] > 0)) {
					foreach($postids['BETWEEN'] as $key2=>$pid) {
						if ($key2 !=0 || $i != 0)
							$postidquery .= " OR ";
						if ($pid[0] == $thread) {
							$postidquery .= "(`id` = ".$tc_db->qstr($thread)." AND `parentid` = 0) OR (";
						} else {
							$postidquery .= "(`parentid` = " . $tc_db->qstr($thread) . " AND ";
						}
						$end = intval(array_pop($pid));
						if ($pid[0] < $end) {
							$postidquery .= "`id` BETWEEN ".(intval($pid[0]))." AND ".$end."";
						} else {
							$postidquery .= "`id` BETWEEN ".$end." AND ".(intval($pid[0]))."";
						}
						if ($pid[0] == $thread) {
							$postidquery .= " AND `parentid` = " . $tc_db->qstr($thread) . ")";
						} else {
							$postidquery .= ")";
						}
					}
				}
			}
			$i++;
		}
	}
	else {
		$postidquery .= "`id` = ".intval($postids[0]);
	}

}

$board_class->InitializeDwoo();
$board_class->dwoo_data->assign('board', $board_class->board);
$board_class->dwoo_data->assign('isread', true);
$board_class->dwoo_data->assign('file_path', getCLBoardPath($board_class->board['name'], $board_class->board['loadbalanceurl_formatted'], ''));

$page ='';

if (!$singlepost) {
	$page .= $board_class->PageHeader($thread, 0, -1, -1);
	$board_class->dwoo_data->assign('replythrad', $thread);
 	$page .= $board_class->dwoo->get(KU_TEMPLATEDIR . '/board_reply_header.tpl', $board_class->dwoo_data);
} else {
	$tpl['title'] = '';
	$tpl['head'] = '';
	// #snivystuff
	// $page .= '<link rel="stylesheet" href="' . getCLBoardPath() . 'css/img_global.css" />';
}

	if (!$singlepost) {
		$page .= '<br />' . "\n";
	}

	$results = $tc_db->GetAll("SELECT * FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $board_class->board['id'] ." AND (" . $postidquery . ") AND `IS_DELETED` = 0 ORDER BY `id` ASC");

	$embeds = $tc_db->GetAll("SELECT filetype FROM `" . KU_DBPREFIX . "embeds`");
	foreach ($embeds as $embed) {
		$board_class->board['filetypes'][] .= $embed['filetype'];
	}
	$board_class->dwoo_data->assign('filetypes', $board_class->board['filetypes']);

	foreach ($results as $key=>$post) {
		$results[$key] = $board_class->BuildPost($post, false);
	}
	$board_class->dwoo_data->assign('posts', $results);

	$board_class->dwoo_data->assign('replink', $_GET['replink']);

	$page .= $board_class->dwoo->get(KU_TEMPLATEDIR . '/board_thread.tpl', $board_class->dwoo_data);

	if (!$singlepost) {
		$page .= '<br clear="left">' . "\n";
	}

if (!$singlepost) {
	$page .= '<hr />' . "\n" .
	$board_class->Footer($noboardlist, (microtime_float() - $executiontime_start), $hide_extra);
}

$board_class->PrintPage('', $page, true);
?>
