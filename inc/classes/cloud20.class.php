<?
class Cloud20 {
	function rebuild() {
	  global $tc_db;
	  $sections = $tc_db->GetAll('SELECT * FROM `'. KU_DBPREFIX .'sections` ORDER BY `order`');

	  $allboards = $tc_db->GetAll('SELECT `id`, `name`, `desc`, `section`, `order` FROM `'. KU_DBPREFIX .'boards`');
	  $section20 = $tc_db->GetOne('SELECT `id` FROM `'. KU_DBPREFIX .'sections` WHERE `abbreviation`="20"');
	  foreach($sections as &$sect) {
	    $sect_map[$sect['id']] = $sect['name'];
	    if($sect['abbreviation'] !== '20')
	    	$boards10_wrap[$sect['id']] = array(order=>$sect['order'], name=>$sect['name']);
	  }unset($sect);

	  foreach($allboards as &$board) {
	    if($sect_map[$board['section']] === "20") {
	      $postcount = $tc_db->GetOne('SELECT COUNT(1) FROM `'. KU_DBPREFIX .'posts` WHERE `boardid`='.$board['id'].' AND `timestamp` >= '.strtotime(KU_20_CLOUDTIME));
	      $boards20 []= array('name'=>$board['name'], 'desc'=>$board['desc'], 'postcount'=>$postcount);
	    }
	    elseif(isset($sect_map[$board['section']])) {
	      $boards10_wrap[$board['section']]['boards'] []= array('dir'=>$board['name'], 'desc'=>$board['desc'], 'order'=>$board['order']);
	    }
	  }unset($board);

	  foreach ($boards10_wrap as $key => $value) {
	  	$boards10 []= $value;
	  }

	  file_put_contents(KU_ROOTDIR . '/boards10.json', json_encode($boards10), LOCK_EX);
		file_put_contents(KU_ROOTDIR . '/boards20.json', json_encode($boards20), LOCK_EX);
	}
}
