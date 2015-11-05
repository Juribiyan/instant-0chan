<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
<script type="text/javascript"><!--
		var captchaTimeout = ( {%KU_CAPTCHALIFE} * 1000 ) - 500;
		var react_api = '{%KU_CLI_REACT_API}';
		var react_ena = '{%KU_REACT_ENA}';
		var react_sitename = {if %KU_REACT_SITENAME}'{%KU_REACT_SITENAME}:'{else}''{/if};
		var this_board_dir = '{$board.name}';
		var ku_boardspath = '{%KU_BOARDSPATH}';
		var ku_cgipath = '{%KU_CGIPATH}';
		var style_cookie = "kustyle";
		var locale = '{$locale}';
		var ku_youtube_apikey = '{%KU_YOUTUBE_APIKEY}';
{if $replythread > 0}
		var ispage = false;
{else}
		var ispage = true;
{/if}
//--></script>
<link href='http://fonts.googleapis.com/css?family=Roboto:400,700,400italic,700italic&subset=latin,cyrillic' rel='stylesheet' type='text/css'>
<link rel="stylesheet" type="text/css" href="{$cwebpath}css/img_global.css?v={%KU_CSSVER}" />
{loop $ku_styles}
	<link rel="{if $ neq $__.ku_defaultstyle}alternate {/if}stylesheet" type="text/css" href="{$__.cwebpath}css/{if $__.customstyle eq $}custom/{/if}{$}.css?v={if $__.customstyle eq $}{$__.csver}{else}{%KU_CSSVER}{/if}" title="{$|capitalize}" {if $__.customstyle eq $}data-custom="true"{/if}/>
{/loop}
<link href="{$cwebpath}css/prettify.css" type="text/css" rel="stylesheet" />
{if $locale eq 'ja'}
	{literal}
	<style type="text/css">
		*{
			font-family: IPAMonaPGothic, Mona, 'MS PGothic', YOzFontAA97 !important;
			font-size: 1em;
		}
	</style>
	{/literal}
{/if}
{if %KU_RSS neq ''}
	<link rel="alternate" type="application/rss+xml" title="RSS" href="{%KU_BOARDSPATH}/{$board.name}/rss.xml" />
{/if}
<script type="text/javascript" src="{$cwebpath}/lib/javascript/gettext.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/headjs/1.0.3/head.load.min.js"></script>
<script>
if (!window.head) {
    document.write('<script src="{$cwebpath}/lib/javascript/head.load.min.js"><\/script>');
}
</script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script>
if (!window.jQuery) {
    document.write('<script src="{$cwebpath}/lib/javascript/jquery-1.11.1.min.js"><\/script>');
}
</script>
<script src="//cdnjs.cloudflare.com/ajax/libs/prettify/r298/prettify.min.js"></script>
<script>
if (!window.prettyPrint) {
    document.write('<script src="{$cwebpath}/lib/javascript/prettify/prettify.js"><\/script>');
}
</script>
<script src="{%KU_WEBPATH}/lib/javascript/kusaba.new.js?v={%KU_JSVER}"></script>
<script><!--
	var hiddenthreads = getCookie('hiddenthreads').split('!');
//--></script>
{if %KU_REACT_ENA}
<script src="{%KU_CLI_REACT_API}/socket.io/socket.io.js"></script>
{/if}
<script src="//cdnjs.cloudflare.com/ajax/libs/prettify/r298/prettify.min.js"></script>
</head>
<body>
<table width="98%" border="0" align="center" cellpadding="0" cellspacing="0" class="maintable">
  <tbody><tr>
    <td width="13" height="38" class="topbar-left"></td>
    <td height="38" class="topbar-center"></td>
    <td width="13" height="38" class="topbar-right"></td>
  </tr>
  <tr>
    <td width="13" class="border-left"></td>
    <td class="content-background" style="padding:9px">
    {include file='disclaimer.tpl'}
<div class="adminbar">
[<a href="{%KU_WEBPATH}/kusaba.php" target="_top">{t}Frames{/t}</a>]&nbsp;[<a href="{%KU_CGIPATH}/manage.php" target="_top">{t}Manage{/t}</a>]
</div>
<div id="boardlist_header">
	<div id="overlay_menu" class="content-background overlay-menu">
		<span style="display: none" class="olm-link mgoback">[<a href="{%KU_CGIPATH}/{$board.name}/"> &lt; </a>]</span>
		<span class="olm-link">[<a href="{%KU_BOARDSFOLDER}">home</a>]</span>
		<span class="mobile-nav" id="mn-normalboards" style="display:none"> 
			<select onchange="javascript:if(selectedIndex != 0) location.href='{%KU_WEBPATH}/' + this.options[this.selectedIndex].value;">
				<option><b>{t}Boards{/t}</b></option>
				{foreach name=sections item=sect from=$boardlist}
					{if $sect.abbreviation neq '20'}						
					{foreach name=brds item=brd from=$sect}							
						{if $brd.name neq $board.name}
						{if isset($brd.desc) and is_array($brd)}							
						<option value="{$brd.name}">/{$brd.name}/ - {$brd.desc}</option>
						{/if}
						{/if}						
					{/foreach}	
					{/if}				
				{/foreach}	
			</select>
			<select class="boardsel20" onchange="javascript:if(selectedIndex != 0) location.href='{%KU_WEBPATH}/' + this.options[this.selectedIndex].value;">
				<option><b>2.0 {t}Boards{/t}</b></option>
			</select>
		</span>
	{*	<div id="brdebug" style="display:none">{var_dump($boardlist);}<br>
		{foreach name=sections item=sect from=$boardlist}
			<div class="brdebug-sect">
			{var_dump($sect);}	
			{foreach name=brds item=brd from=$sect}
				{if is_array($brd)}
				<div class="brdebug-brd">
				{var_dump($brd);}<br>
				$brd.name = {$brd.name}, $brd.desc = {$brd.desc}, $brd.nick = {$brd.nick}
				</div>{/if}						
			{/foreach}
			</div>					
		{/foreach}
		</div>  *}
		{foreach name=sections item=sect from=$boardlist}
		<b  class="olm-link">[<a href="{if $sect.abbreviation eq '20'}{%KU_BOARDSPATH}/?p=2.0{else}#{/if}" class="sect-exr" data-toexpand="{$sect.abbreviation}">{$sect.nick}</a>]</b>
		{/foreach}
		<span class="olm-link">[<a href="#" class="sect-exr" data-toexpand="_options">options</a>]</span>
		{foreach name=sections item=sect from=$boardlist}
		<div class="menu-sect" id="ms-{$sect.abbreviation}">
			{if $sect.abbreviation neq '20'}
				{foreach name=brds item=brd from=$sect}
					{if isset($brd.desc) and is_array($brd)}
					<a class="menu-item" title="{$brd.desc}" href="{%KU_BOARDSFOLDER}{$brd.name}/">/{$brd.name}/ - {$brd.desc}</a>
					{/if}
				{/foreach}
			{else}
				<input type="text" id="boardselect" placeholder="{t}Filter{/t}" />
				<div id="boards20">
				</div>
			{/if}	
		</div>
		{/foreach}
		<div class="menu-sect" id="ms-_options">
			{t}Styles{/t}: 
			{loop $ku_styles}
			[<a href="#" onclick="javascript:Styles.change('{$|capitalize}');return false;">{if $__.customstyle eq $}Custom{else}{$|capitalize}{/if}</a>]
			{/loop}<br />
			<a href="#" onclick="javascript:menu_pin();return false;">{t}Pin/Unpin{/t}</a>  | 
			<a href="#" onclick="javascript:set_oldmenu(true);return false;">{t}Simple list{/t}</a>
			<div id="js_settings"></div>
		</div>
	</div>
</div>

<table style="width:100%" border="0">
<tbody><tr><td valign="center">
	<div class="logo">{if %KU_DIRTITLE}	/{$board.name}/ - {/if}{$board.desc}</div>
</td><td align="right"></td></tr></tbody></table>
<hr />