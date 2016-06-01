<?
if(!isset($_GET['trip']) || strlen($_GET['trip']) != 10)
	exit(json_encode(array(error=>"wrong_code")));

$trip = $_GET['trip'];

require 'config.php';
$tc_db->SetFetchMode(ADODB_FETCH_ASSOC);

$posts = $tc_db->GetAll('SELECT MIN(`timestamp`) first, MAX(`TIMESTAMP`) last, COUNT(1) posts FROM `'.KU_DBPREFIX.'posts` WHERE LEFT(`tripcode`, 10)=?', array($trip))[0];

$ops = $tc_db->GetOne('SELECT COUNT(1) ops FROM `'.KU_DBPREFIX.'posts` WHERE LEFT(`tripcode`, 10)=? AND `parentid`=0', array($trip));

$active_on = $tc_db->GetAll('SELECT `name` FROM `'.KU_DBPREFIX.'boards` b JOIN ( SELECT count(`boardid`) AS cnt, `boardid` FROM `'.KU_DBPREFIX.'posts` WHERE LEFT(`tripcode`, 10)=? GROUP BY `boardid`) p ON p.`boardid` = b.`id` ORDER BY p.cnt DESC', array($trip));
function pluck_name($n) {	return $n['name']; }
$active_on = array_map('pluck_name', $active_on);

$result = array(
	active_since => +$posts['first'],
	last_active => +$posts['last'],
	threads => +$ops,
	comments => $posts['posts'] - $ops,
	active_on => $active_on
);
exit(json_encode($result));