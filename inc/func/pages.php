<?php
/**
 * Generate the list of pages, linking to each
 *
 * @param integer $boardpage Current board page
 * @param integer $pages Number of pages
 * @param string $board Board directory
 * @return string Generated page list
 */

/* <3 coda for this wonderful snippet
print $contents to $filename by using a temporary file and renaming it */
function print_page($filename, $contents, $board) {
	global $tc_db;

	$tempfile = tempnam(KU_BOARDSDIR . $board . '/res', 'tmp'); /* Create the temporary file */
	$fp = fopen($tempfile, 'w');
	fwrite($fp, $contents);
	fclose($fp);
	/* If we aren't able to use the rename function, try the alternate method */
	if (!@rename($tempfile, $filename)) {
		copy($tempfile, $filename);
		unlink($tempfile);
	}

	chmod($filename, 0664); /* it was created 0600 */
}

function RegenerateOverboard($boardlist=null) {
	global $tc_db;
	$tc_db->SetFetchMode(ADODB_FETCH_ASSOC);

	$maxpages = I0_OVERBOARD_NUMPAGES;
	$total_threads = $maxpages * I0_OVERBOARD_THREADS;

	$over_board_class = new Board('', false, true);
	$over_board_class->is_overboard = true;
	$over_board_class->InitializeDwoo();
	
	$over_board_class->board = array(
		"name" => I0_OVERBOARD_DIR,
		"enablecatalog" => false, // Only for the big donation I will implement this
		"desc" => I0_OVERBOARD_DESCRIPTION
	);
	$over_board_class->board['boardlist'] = $boardlist; // Save some time by skipping boardlist building
	$over_board_class->dwoo_data->assign('for_overboard', 1);
	$header = $over_board_class->PageHeader(0,0,-1,0, true);

	$threads = $tc_db->GetAll("SELECT `latest_threads`.`id`, `visible_boards`.`boardname` FROM
		(SELECT `id`,`boardid`,`bumped` FROM `".KU_DBPREFIX."posts` WHERE `parentid`='0' AND `IS_DELETED`!='1') `latest_threads`
		INNER JOIN(SELECT `name` AS `boardname`, `id` AS `boardid` FROM `".KU_DBPREFIX."boards` WHERE `hidden`!='1' AND `section`!='0') `visible_boards` 
		ON `latest_threads`.`boardid` = `visible_boards`.`boardid`
		ORDER BY `latest_threads`.`bumped` DESC
		LIMIT $total_threads");

	$form_start = '<form id="delform" action="'.KU_CGIPATH.'/board.php" method="post"><input type="hidden" name="board" value="'.I0_OVERBOARD_DIR.'">';

	if (count($threads)) {
		$previous_page = -1;
		$i = 0; foreach($threads as &$thread) {
			$current_page = floor($i / I0_OVERBOARD_THREADS);
			if ($current_page != $previous_page) {
				$execution_times[$current_page] = microtime_float();
				$pages[$current_page] .= $form_start;
				$previous_page = $current_page;
			}
			// For all the boards involved create a board_class instance
			if (!isset($boards[$thread['boardname']])) { // (only if it's not already created)
				$boards[$thread['boardname']] = new Board($thread['boardname'], true, true);
				$boards[$thread['boardname']]->InitializeDwoo();
				$boards[$thread['boardname']]->is_overboard = true;
				$boards[$thread['boardname']]->dwoo_data->assign('for_overboard', 1);
				$boards[$thread['boardname']]->dwoo_data->assign('board', $boards[$thread['boardname']]->board);
				// $pages[$current_page] .= $boards[$thread['boardname']]->Postbox(); // Add postboxes for every board with specific rules
				// $pages[$current_page] .= "<script>over_board_info['".$thread['boardname']."'] = ".json_encode($boards[$thread['boardname']]->board)."</script>";
			}
			// Generate thread piece
			$threadling = $boards[$thread['boardname']]->GenerateOverboardThreadFragment($thread['id']);
			$pages[$current_page] .= $threadling;
		$i++; }unset($thread);
	}
	else {
		$pages = [$form_start];
	}
	
	$totalpages = count($pages);
	$over_board_class->dwoo_data->assign('numpages', $totalpages-1);

	$page = 0; foreach($pages as &$contents) {
		$over_board_class->dwoo_data->assign('thispage', $page);
		$footer = $over_board_class->Footer(false, (microtime_float()-$execution_times[$page]));
		$contents = $header.$contents.$footer;
		print_page(KU_BOARDSDIR.I0_OVERBOARD_DIR.'/'.($page==0 ? KU_FIRSTPAGE : '/'.$page.'.html'), $contents, I0_OVERBOARD_DIR);
	$page++; }unset($contents);

	$over_board_class->DeleteOldPages($totalpages-1);
}
?>