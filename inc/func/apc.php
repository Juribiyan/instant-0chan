<?php
function clearBlotterCache() {
	if (KU_APC) {
		apc_delete('blotter|all');
		apc_delete('blotter|last4');
	}
}

/**
 * Clear cache for the supplied post ID of the supplied board
 *
 * @param integer $id Post ID
 * @param string $board Board name
 * @param boolean $skipdb Whether or not deleting from reports table should be skipped
 */
function clearPostCache($id, $board, $skipdb=false) {
	global $tc_db;
	if (KU_APC) {
		apc_delete('post|' . $board . '|' . $id);
	}
  if (!$skipdb)
	 $tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "reports` WHERE `postid` = " . $id . " AND `board` = " . $tc_db->qstr($board));
}
?>
