<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
{if $replythread > 0}<!-- i0:thrno={$replythread} -->{/if}
<meta property="i0:buildtime" content="{time()}"/>
<script type="text/javascript">
	var captchaTimeout = {$smarty.const.KU_CAPTCHALIFE} - 0.5;
	var liveupd_api = '{$smarty.const.KU_CLI_LIVEUPD_API}';
	var liveupd_ena = '{$smarty.const.KU_LIVEUPD_ENA}';
	var liveupd_sitename = {if $smarty.const.KU_LIVEUPD_SITENAME}'{$smarty.const.KU_LIVEUPD_SITENAME}:'{else}''{/if};
	var this_board_dir = '{$board.name}';
	var ku_boardspath = '{$smarty.const.KU_BOARDSPATH}';
	var ku_boardsfolder = '{$smarty.const.KU_BOARDSFOLDER}';
	var ku_cgipath = '{$smarty.const.KU_CGIPATH}';
	var style_cookie = "kustyle";
	var locale = '{$locale}';
	{if not $for_overboard}
		var this_board_defaultName = '{$board.anonymous}';
		var boardid = '{$board.id}';
		var opmod = '{$board.opmod}';
	{/if}
	{if $smarty.const.I0_OVERBOARD_ENABLED}
		var is_overboard = {if $for_overboard}true{else}false{/if};
		var overboard_dir = "{$smarty.const.I0_OVERBOARD_DIR}";
		var overboard_desc = "{$smarty.const.I0_OVERBOARD_DESCRIPTION}";
		{* var over_board_info = new Object; *}
	{/if}
	var force_html_nocache = !!'{$smarty.const.I0_FORCE_HTML_NOCACHE}';
	var ispage = {if $replythread > 0} false {else} true {/if};
</script>
<noscript><style>.yesscript { display: none!important; }</style></noscript>
<link rel="stylesheet" type="text/css" href="{$cwebpath}css/img_global.css?v={$smarty.const.KU_CSSVER}" />
<script>document.write('{strip}
{foreach $ku_styles as $style}
	<link rel="{if $style neq $ku_defaultstyle}alternate {/if}stylesheet" type="text/css" 
	href="{$cwebpath}css/{if $customstyle eq $style}custom/{/if}{$style}.css?v={if $customstyle eq $style}{$csver}{else}{$smarty.const.KU_CSSVER}{/if}" 
	title="{$style|capitalize}" 
	{if $customstyle eq $style}data-custom="true"{/if}/>
{/foreach}{/strip}')</script>
<noscript><link rel="stylesheet" href="{$cwebpath}getpreferredstylesheet.php?allowed={implode(',', $ku_styles)}&default={$ku_defaultstyle}&v={$smarty.const.KU_CSSVER}{if $customstyle}&custom={$customstyle}&cv={$csver}{/if}"></noscript>
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
{if $smarty.const.KU_RSS neq ''}
	<link rel="alternate" type="application/rss+xml" title="RSS" href="{$smarty.const.KU_BOARDSPATH}/{$board.name}/rss.xml" />
{/if}
<script src="{$cwebpath}lib/javascript/gettext.js"></script>
<script src="{$cwebpath}lib/javascript/head.load.min.js"></script>
<script src="{$cwebpath}lib/javascript/jquery-3.6.3.min.js"></script>
<script src="{$cwebpath}lib/javascript/lodash.min.js"></script>
<script src="{$cwebpath}lib/javascript/prettify/prettify.js"></script>
<script src="{$smarty.const.KU_WEBPATH}/lib/javascript/Sortable.min.js"></script>
<script src="{$smarty.const.KU_WEBPATH}/inc/javascript/kusaba.new.js?v={$smarty.const.KU_JSVER}"></script>
{if $smarty.const.KU_LIVEUPD_ENA}
<script src="{$smarty.const.KU_CLI_LIVEUPD_API}/socket.io/socket.io.js"></script>
{/if}
{literal}<script>if(localStorage['constrainWidth']=='true') document.write('<style id="injector:constrainWidth">body\{max-width:960px;margin:0px auto\}</style>')</script>{/literal}
</head>
<body{if $replythread neq 0} class="isthread"{/if}>
<script>
	document.body.classList.add('js-supported')
	$.get("{$cwebpath}css/icons/icons.svg?v={$smarty.const.KU_SVGVER}", function(data) {
	  var div = document.createElement("div");
	  div.innerHTML = new XMLSerializer().serializeToString(data.documentElement);
	  document.body.insertBefore(div, document.body.childNodes[0]);
	});
</script>
<div class="content-background content">
<header class="decor-header" style="display: none"></header>
{if $smarty.const.KU_DISCLAIMER}
	{include file='disclaimer.tpl'}
{/if}
<div class="adminbar">
{if $board.enablecatalog eq 1}[<a href="{$smarty.const.KU_BOARDSFOLDER}{$board.name}/catalog.html"><b>{t}Catalog{/t}</b></a>]&nbsp;{/if}
{if $smarty.const.I0_ENABLE_PUBLIC_MODLOG}[<a href="{$smarty.const.KU_WEBPATH}/modlog.php"><b>{t}ModLog{/t}</b></a>]&nbsp;{/if}
[<a href="{$smarty.const.KU_WEBPATH}/kusaba.php" target="_top">{t}Frames{/t}</a>]&nbsp;[<a href="{$smarty.const.KU_CGIPATH}/manage.php" target="_top">{t}Manage{/t}</a>]
<svg class="icon b-icon history-toggle yesscript" onclick="pups.historyToggle()"><use title="{t}Show alert history{/t}" xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#i-bell"></use></svg>
</div>
<div id="boardlist_header">
	{if $boardlist_prebuilt}
		<noscript id="ns_oldmenu">
			{$boardlist_prebuilt}
		</noscript>
	{/if}
	<script>
		if (getCookie('ku_oldmenu') == 'yes') toggle_oldmenu(true)
		var isTouch = (('ontouchstart' in window) || window.DocumentTouch && document instanceof DocumentTouch) || false;
		if(localStorage['interfaceType'] == 'desktop')
		  isTouch = false;
		if(localStorage['interfaceType'] == 'touch')
		  isTouch = true;
		if (isTouch) {
			injector.inject('mb', `
				#overlay_menu {
					display: none;
				}
			  body {
			    margin-bottom: 350px;
			  }
			  #postclone {
			    bottom: 0px;
			    left: 0px;
			    position: fixed;
			    opacity: 1
			  }
			  .logo {
			    margin-left: 40px;
			  }
			  @media only screen and (max-width: 768px) {
			    .logo {
			      margin-left: 60px;
			    }
			  }`)
		}
	</script>
	<div id="overlay_menu" class="content-background overlay-menu yesscript">
		<span style="display: none" class="olm-link mgoback">[<a href="{$smarty.const.KU_CGIPATH}/{$board.name}/"> &lt; </a>]</span>
		<span class="olm-link">[<a href="{$smarty.const.KU_BOARDSFOLDER}">home</a>]</span>
		{if $smarty.const.I0_OVERBOARD_ENABLED}<span class="olm-link">[<a title="{$smarty.const.I0_OVERBOARD_DESCRIPTION}" href="{$smarty.const.KU_BOARDSFOLDER}{$smarty.const.I0_OVERBOARD_DIR}/">{$smarty.const.I0_OVERBOARD_DIR}</a>]</span>{/if}
		{foreach $boardlist as $sect}
		<b  class="olm-link">[<a href="{$smarty.const.I0_20_LINK}" class="sect-exr" data-toexpand="{$sect.abbreviation}">{$sect.nick}</a>]</b>
		{/foreach}
		<span class="olm-link">[<a href="#" class="sect-exr" data-toexpand="_options">options</a>]</span>
		{foreach $boardlist as $sect}
		<div class="menu-sect" id="ms-{$sect.abbreviation}">
			{if $sect.abbreviation neq '20'}
				{foreach $sect as $brd}
					{if isset($brd.desc) and is_array($brd)}
					<a class="menu-item" title="{$brd.desc}" href="{$smarty.const.KU_BOARDSFOLDER}{$brd.name}/">/{$brd.name}/ - {$brd.desc}</a>
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
			{foreach $ku_styles as $style}
			[<a href="#" onclick="javascript:Styles.change('{$style|capitalize}');return false;">{if $customstyle eq $style}Custom{else}{$style|capitalize}{/if}</a>]
			{/foreach}<br />
			<a href="#" onclick="javascript:menu_pin();return false;">{t}Pin/Unpin{/t}</a>  |
			<a href="#" onclick="javascript:toggle_oldmenu(true);return false;">{t}Simple list{/t}</a>
			<div id="js_settings"></div>
		</div>
	</div>
</div>
<table style="width:100%" border="0">
<tbody><tr><td valign="center">
	<{if $replythread > 0 || $is_catalog}a href="{$smarty.const.KU_WEBFOLDER}{$board.name}/"{else}div{/if} class="logo">{if $smarty.const.KU_DIRTITLE}/{$board.name}/ - {/if}{$board.desc}</{if $replythread > 0}a{else}div{/if}>
</td><td align="right"></td></tr></tbody></table>
<hr />