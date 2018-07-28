
<?php
require 'config.php';

$posts = $tc_db->getAll("SELECT `id`, `boardid`, `message` FROM `".KU_DBPREFIX."posts` WHERE `message` LIKE '%<summary%'");

foreach($posts as $post) {
  $re = '/<summary[^>]+>([\s\S]+?)<\/summary>(?:<br>)?/m';
  $subst = '<summary class="read-more"><span class="xlink">$1</span></summary>';
  $msg_new = preg_replace($re, $subst, $post['message']);
  $tc_db->Execute("UPDATE `".KU_DBPREFIX."posts` SET `message`=? WHERE `id`=? AND `boardid`=?", array($msg_new, $post['id'], $post['boardid']));
}