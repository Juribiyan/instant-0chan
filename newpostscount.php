<?php 

require 'config.php';

$brds = $tc_db->GetAll('SELECT `id`,`name` FROM `boards`;');

foreach ($brds as &$brd) {
    if(isset($_GET[$brd["id"]])) $last_ts = $_GET[$brd["id"]];
    else $last_ts = 0;
    $result[$brd["name"]] = $tc_db->GetOne('SELECT COUNT(1) FROM `posts` WHERE `boardid` = '.$brd["id"].' AND`timestamp` > '.$last_ts);
}
unset($brd);

exit(json_encode($result));