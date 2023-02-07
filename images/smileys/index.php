<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
	<title>Sayers</title>
	<link rel="stylesheet" href="../../css/img_gallery.css">
	<script src="../../lib/javascript/img_gallery.js"></script>
</head>
<body>
<?php
$dir = opendir('.');
$files = array();
while (false != ($file = readdir($dir))) {
	if (
		($file != '.') && ($file != '..') && ($file != 'index.php')
		&&
		preg_match("/\.(png|gif)$/", $file)
	) {
		$files []= $file;
	}
}
natsort($files);
foreach($files as $file) {
	$name = preg_replace("/(.+)\..+$/", '$1', $file);
	echo "<a href='$file'><img src='$file' alt='$name'><div class='filename'>$name</div></a>";
}
?>
<script>main('sayers')</script></body>
</html>