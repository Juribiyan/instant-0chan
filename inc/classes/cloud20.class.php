<?php
class Cloud20 {
	function rebuild() {
		global $tc_db;

		$all = $tc_db->GetAll('SELECT `b`.`name`, `desc`, `b`.`order`, `pc`.`postcount`, `sect`.`order` as `sectorder`, `sect`.`name` as `sect`, `is_20` FROM
			(SELECT * FROM `'.KU_DBPREFIX.'boards`) `b`
			JOIN (SELECT *, (`abbreviation`="20") AS `is_20` FROM `'.KU_DBPREFIX.'sections`) `sect`
			ON `b`.`section` = `sect`.`id`
			LEFT JOIN(SELECT `boardid`, COUNT(1) AS `postcount` FROM `'.KU_DBPREFIX.'posts` GROUP BY `boardid`) `pc`
			ON `b`.`id` = `pc`.`boardid`
			WHERE `b`.`hidden` != 1');

		foreach($all as &$board) {
			if ($board['is_20']) {
				$boards20 []= array('name'=>$board['name'], 'desc'=>$board['desc'], 'postcount'=>($board['postcount'] ? (int)$board['postcount'] : 0));
			}
			else {
				if (!isset($boards10_wrap[$board['sect']]['name'])) {
					$boards10_wrap[$board['sect']]['name'] = $board['sect'];
					$boards10_wrap[$board['sect']]['order'] = $board['sectorder'];
				}
				$boards10_wrap[$board['sect']]['boards'] []= array('dir'=>$board['name'], 'desc'=>$board['desc'], 'order'=>$board['order']);
			}
		}unset($board);

		foreach ($boards10_wrap as $key => $value) {
			$boards10 []= $value;
		}

		if (!isset($boards10))
			$boards10 = array();
		file_put_contents(KU_ROOTDIR . '/boards10.json', json_encode($boards10), LOCK_EX);
		if (!isset($boards20))
			$boards20 = array();
		file_put_contents(KU_ROOTDIR . '/boards20.json', json_encode($boards20), LOCK_EX);
	}
}
