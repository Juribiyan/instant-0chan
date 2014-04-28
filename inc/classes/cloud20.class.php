<?
class Cloud20 {
	function rebuild() {
		global $tc_db;

		$bjson = KU_ROOTDIR . '/boards20.json';

		$boardid20 = $tc_db->GetOne('SELECT `id` FROM `'. KU_DBPREFIX .'sections` WHERE `abbreviation`="20"');
		$boards20 = $tc_db->GetAll('SELECT `id`, `name`, `desc` FROM `'. KU_DBPREFIX .'boards` WHERE `section` = '.$boardid20.' ORDER BY `name` ASC');
		foreach($boards20 as &$b20) {
			$postcount = $tc_db->GetOne('SELECT COUNT(1) FROM `'. KU_DBPREFIX .'posts` WHERE `boardid`='.$b20['id'].' AND `timestamp` >= '.strtotime(KU_20_CLOUDTIME));
			$results[] = array('name'=>$b20['name'], 'desc'=>$b20['desc'], 'postcount'=>$postcount);
		}unset($b20);

		file_put_contents($bjson, json_encode($results), LOCK_EX);
	}
}
