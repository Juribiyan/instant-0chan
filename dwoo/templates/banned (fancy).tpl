<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>{t}YOU ARE BANNED{/t}!</title>
    <script type="text/javascript">
var defaultPattern = { pattern: 
    '000000dddddddd0000000000dddddddddddd0000000dddddddddddddd00000ddddddd555dddddd000ddddd5555555dddddd00dddd5555555ddddddd0dddd5555555ddddddddddddd555555ddddd5dddddddd55555ddddd555dddddd55555ddddd5555dddddd5555ddddd55555dddddd555ddddd55555dddddddd5ddddd555555ddddddddddddd5555555dddd0ddddddd5555555dddd00dddddd5555555ddddd000dddddd555ddddddd00000dddddddddddddd0000000dddddddddddd0000000000dddddddd000000', x: 20, y: 20 }; 
</script>
	<link href="../pages/index.css" media="all" rel="stylesheet" type="text/css">
	<script src="../lib/javascript/jquery.min.js"></script>
	<script src="../pages/index.js"></script>
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
            <a href="#" id="playSwitcher" class="switcher">вкл/выкл музыку</a>  
        </div>
    </div>
    <div id="boards">
    <center>
    <a href="/?p=boards" title="Доски">Доски</a> | <a href="/?p=2.0" title="2.0">Nullchorg 2.0<sup>beta</sup></a> | <a href="/?p=faq" title="FAQ">FAQ</a>
    </center>
    <table id="boardlist" border="0" align="center" cellpadding="0" cellspacing="0" style="border-top:1px #AAA solid">
    <tbody>
    <tr><td valign="top">
	<div id="boards">
            <center>
            <span class="big-shit">{t}YOU ARE BANNED{/t}!</span>
            </center>
    </div>
    {foreach name=bans item=ban from=$bans}
		{if not $.foreach.bans.first}
			{t}Additionally{/t},
		{/if}
		{if $ban.expired eq 1}
			{t}You were banned from posting on{/t}
		{else}
			{t}You have been banned from posting on{/t}
		{/if} 
		<strong>{if $ban.globalban eq 1}{t}All boards{/t}{else}/{implode('/</strong>, <strong>/', explode('|', $ban.boards))}/{/if}</strong> {t}for the following reason{/t}:<br /><br />
		<strong>{$ban.reason}</strong><br /><br />
		{t}Your ban was placed on{/t} <strong>{$ban.at|date_format:"%B %e, %Y, %I:%M %P %Z"}</strong>, {t}and{/t}
		{if $ban.expired eq 1}
			{t}expired on{/t} <strong>{$ban.until|date_format:"%B %e, %Y, %I:%M %P"}</strong><br  />
			<strong>{t}This ban has already expired, this message is for your information only and will not be displayed again{/t}</strong>
		{else}
			{if $ban.until > 0}{t}will expire on{/t} <strong>{$ban.until|date_format:"%B %e, %Y, %I:%M %P"}</strong>{else}{t}will not expire{/t}</strong>{/if}
		{/if}
		<br /><br />
		{if %KU_APPEAL neq '' && $ban.expired eq 0}
			{if $ban.appealat eq 0}
				{t}You may <strong>not</strong> appeal this ban.{/t}
			{elseif $ban.appealat eq -1}
				{t}Your appeal is currently pending review.{/t}
				{t}For reference, your appeal message is{/t}:<br />
				<strong>{$ban.appeal}</strong>
			{elseif $ban.appealat eq -2}
				{t}Your appeal was reviewed and denied. You may <strong>not</strong> appeal this ban again.{/t}
				{t}For reference, your appeal message was{/t}:<br />
				<strong>{$ban.appeal}</strong>
			{else}
				{if $ban.appealat < $.now}
					{t}You may now appeal this ban.{/t}
					<br /><br />
					<form action="{%KU_BOARDSPATH}/banned.php" method="post">
						<input type="hidden" name="banid" value="{$ban.id}" />
						<label for="appealmessage">{t}Appeal Message{/t}:</label>
						<br />
						<textarea name="appealmessage" rows="10" cols="50"></textarea>
						<br /><input type="submit" value="{t}Send Appeal{/t}" />
					</form>
				{else}
					{t}You may appeal this ban in{/t} <strong>{$ban.appealin}</strong>.
				{/if}
			{/if}
			<br />
		{/if}
		{if $.foreach.bans.last}
			<br />{t}Your IP address is{/t} <strong>{$.server.REMOTE_ADDR}</strong>.<br /><br />
		{/if}
		{if count($bans) > 1 && not $.foreach.bans.last}
			<hr />
		{/if}

	{/foreach}
    </td></tr>
    </tbody></table>
</div>
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