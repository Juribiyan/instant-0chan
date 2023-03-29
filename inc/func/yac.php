<?php
function clearBlotterCache() {
	global $yac;
	if (I0_YAC) {
		$yac->delete('blotter|all');
		$yac->delete('blotter|last4');
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
	global $tc_db, $yac;
	if (I0_YAC) {
		$yac->delete('post|' . $board . '|' . $id);
	}
  if (!$skipdb)
	 $tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "reports` WHERE `postid` = " . $id . " AND `board` = " . $tc_db->qstr($board));
}

function cache_get($key) {
	global $yac;
	return I0_YAC ? $yac->get($key) : null;
}

function cache_set($key, $val) {
	global $yac;
	return I0_YAC ? $yac->set($key, $val) : false;
}

?>
