<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>{$smarty.const.KU_NAME}</title>
<link rel="shortcut icon" href="{$smarty.const.KU_WEBPATH}/favicon.ico" />
<link rel="stylesheet" type="text/css" href="{$smarty.const.KU_BOARDSPATH}/css/menu_global.css" />
{foreach $styles}
	<link rel="{if $style neq $smarty.const.KU_DEFAULTMENUSTYLE}alternate {/if}stylesheet" type="text/css" href="{$smarty.const.KU_WEBFOLDER}css/site_{$style}.css" title="{$style|capitalize}" />
	<link rel="{if $style neq $smarty.const.KU_DEFAULTMENUSTYLE}alternate {/if}stylesheet" type="text/css" href="{$smarty.const.KU_WEBFOLDER}css/sitemenu_{$style}.css" title="{$style|capitalize}" />
{/foreach}

<style type="text/css">{literal}
body {
	width: 100% !important;
}
{/literal}</style>
</head>
<body>
<h1 style="font-size: 3em;">{t}Error{/t}</h1>
<br />
<h2 style="font-size: 2em;font-weight: bold;text-align: center;">
{$errormsg}
</h2>
{$errormsgext}
<div style="text-align: center;width: 100%;position: absolute;bottom: 10px;">
<br />
<div class="footer" style="clear: both;">
	{* I'd really appreciate it if you left the link to kusabax.org in the footer, if you decide to modify this. That being said, you are not bound by license or any other terms to keep it there *}
	<div class="legal">	- <a href="http://kusabax.cultnet.net/" target="_top">kusaba x {$smarty.const.KU_VERSION}</a> -
</div>
</div>
</body>
</html>