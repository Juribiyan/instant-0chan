<?
class Cloud20 {
	function rebuild() {
	  global $tc_db;

	  $sections = $tc_db->GetAll('SELECT * FROM `'. KU_DBPREFIX .'sections`');

	  $allboards = $tc_db->GetAll('SELECT `id`, `name`, `desc`, `section` FROM `'. KU_DBPREFIX .'boards` ORDER BY `name` ASC');
	  $section20 = $tc_db->GetOne('SELECT `id` FROM `'. KU_DBPREFIX .'sections` WHERE `abbreviation`="20"');
	  foreach($sections as &$sect) {
	    $sect_map[$sect['id']] = $sect['abbreviation'];
	  }unset($sect);

	  foreach($allboards as &$board) {
	    if($sect_map[$board['section']] === "20") {
	      $postcount = $tc_db->GetOne('SELECT COUNT(1) FROM `'. KU_DBPREFIX .'posts` WHERE `boardid`='.$board['id'].' AND `timestamp` >= '.strtotime(KU_20_CLOUDTIME));
	      $boards20 []= array('name'=>$board['name'], 'desc'=>$board['desc'], 'postcount'=>$postcount);
	    }
	    elseif(isset($sect_map[$board['section']])) {
	      $boards10[$sect_map[$board['section']]] []= array('name'=>$board['name'], 'desc'=>$board['desc']);
	    }
	  }unset($board);

	  file_put_contents(KU_ROOTDIR . '/boards10.json', json_encode($boards10), LOCK_EX);
	  file_put_contents(KU_ROOTDIR . '/boards20.json', json_encode($boards20), LOCK_EX);
	}
}
