<?php
require 'config.php';
$pages = array(
    "404" => array(
        'title' => '404 Not Found',
        'pattern' => 'error',
        'body' => '404'
    ),
    "403" => array(
        'title' => '403 Forbidden',
        'pattern' => 'ban',
        'body' => '403'
    ),
    "faq" => array(
        'title' => KU_NAME.' FAQ',
        'pattern' => 'main',
        'body' => 'faq'
    ),
    "boards" => array(
        'title' => KU_NAME,
        'pattern' => 'main',
        'body' => 'boards'
    ),
    "donate" => array(
        'title' => 'Donate '.KU_NAME,
        'pattern' => 'coin',
        'body' => 'donate'
    ),
    "2.0" => array(
        'title' => 'Chan 2.0',
        'pattern' => 'main',
        'body' => '20'
    ),
    "register" => array(
        'title' => '$регистрация',
        'pattern' => 'main',
        'body' => 'register'
    ),
);
$page = isset($_GET['p']) && array_key_exists($_GET['p'], $pages) ? $pages[$_GET['p']] : $pages['boards'];
$title = $page['title'];
$pattern = 'pages/patterns/'.$page['pattern'].'.php';
$body = 'pages/contents/'.$page['body'].'.php';
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title><?php echo $title;?></title>
    <?php include($pattern);?>
	<link href="/pages/index.css?v=<?php echo KU_CSSVER?>" media="all" rel="stylesheet" type="text/css">
	<script src="/lib/javascript/jquery-1.11.1.min.js"></script>
    <script src="/lib/javascript/lodash.min.js"></script>
	<script src="/pages/index.js?v=<?php echo KU_JSVER?>"></script>
</head>
<body>
    <a href="kusaba.php" id="toframes" title="Oldfag mode" class="switcher" style="">фреймы тут</a>
    <div id="canvas-wrap" class="audiostuff as-hidable">
	    <canvas id="bars" height="240" width="850"></canvas>
	</div>
	<div id="shadow-wrap" class="audiostuff as-hidable">
	    <div id="shadow"></div>
	</div>
    <div id="gridwrap">
	    <div id="nullgrid"></div>
	</div>
    <div id="clicker-wrap" class="audiostuff">
        <div id="clicker-wrap-wrap">
            <a href="#" id="playSwitcher" class="switcher">Loading...</a>  
        </div>
    </div>
	<?php include($body);?>
    </div>
    <div id="palette" style="display:none">
        <div class="palette-block" id="colors">
            <div data-color="9" class="brush lit green"></div>
            <div data-color="1" class="brush dim green"></div>
            <div data-color="a" class="brush lit yellow"></div>
            <div data-color="2" class="brush dim yellow"></div>
            <div data-color="b" class="brush lit orange"></div>
            <div data-color="3" class="brush dim orange"></div>
            <div data-color="f" class="brush lit mono"></div>
            <div data-color="7" class="brush dim mono"></div><br
            
           ><div data-color="d" class="brush lit crimson"></div>
            <div data-color="5" class="brush dim crimson"></div>
            <div data-color="e" class="brush lit violet"></div>
            <div data-color="6" class="brush dim violet"></div>
            <div data-color="c" class="brush lit blue"></div>
            <div data-color="4" class="brush dim blue"></div>
            <div class="pal-btn brush" id="eraser" data-color="0"></div>
        </div>
        <form action="#" class="palette-block" id="resampler">
        	<input type="number" min="1" value="14" id="width"><span id="multiplier">×</span><input type="number" min="1" value="18" id="height">
        	<input type="submit" class="pal-btn" value="Рисовать">
        	<input type="button" id="reset" class="pal-btn" value="Сброс">
        	<input type="button" id="clearGrid" class="pal-btn" value="Очистить"><br
        	
           ><input type="text" id="pattern">
        	<input type="button" id="getPattern" class="pal-btn" value="Получить паттерн">
        	<input type="button" id="closePalette" class="pal-btn" value="Закрыть">
        </form>
    </div>
</body>
</html>