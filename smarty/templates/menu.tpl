<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>{$smarty.const.KU_NAME} Navigation</title>
{if $smarty.const.KU_MENUTYPE eq 'normal'}
	<link rel="stylesheet" type="text/css" href="{$boardpath}css/menu_global.css" />
	{foreach $styles as $style}
		<link rel="{if $style neq $smarty.const.KU_DEFAULTMENUSTYLE}alternate {/if}stylesheet" type="text/css" href="{$smarty.const.KU_WEBFOLDER}css/site_{$style}.css" title="{$style|capitalize}" />
		<link rel="{if $style neq $smarty.const.KU_DEFAULTMENUSTYLE}alternate {/if}stylesheet" type="text/css" href="{$smarty.const.KU_WEBFOLDER}css/sitemenu_{$style}.css" title="{$style|capitalize}" />
	{/foreach}
{else}
	{literal}<style type="text/css">body { margin: 0px; } h1 { font-size: 1.25em; } h2 { font-size: 0.8em; font-weight: bold; color: #CC3300; } ul { list-style-type: none; padding: 0px; margin: 0px; } li { font-size: 0.8em; padding: 0px; margin: 0px; }</style>{/literal}
{/if}

<script type="text/javascript"><!--
			// var style_cookie_site = "kustyle_site";
			var style_cookie = "kustyle";
		//--></script>
<link rel="shortcut icon" href="{$smarty.const.KU_WEBFOLDER}favicon.ico" />
<script type="text/javascript" src="{$smarty.const.KU_WEBFOLDER}lib/javascript/gettext.js"></script>
<!-- <script type="text/javascript" src="{$smarty.const.KU_WEBFOLDER}inc/javascript/menu.js"></script> -->
<script type="text/javascript" src="{$smarty.const.KU_WEBFOLDER}lib/javascript/jquery-3.6.3.min.js"></script>
<script type="text/javascript" src="{$smarty.const.KU_WEBFOLDER}lib/javascript/lodash.min.js"></script>
<script type="text/javascript" src="{$smarty.const.KU_WEBFOLDER}inc/javascript/kusaba.new.js"></script>
<script type="text/javascript"><!--
var ku_boardspath = '{$smarty.const.KU_BOARDSPATH}';
{if $showdirs eq 0 && $files.0 neq $files.1 }
	if (getCookie(tcshowdirs) == yes) {
		window.location = '{$smarty.const.KU_BOARDSPATH}/{$files.1}';
	}
{/if}

function showstyleswitcher() {
		var switcher = document.getElementById('sitestyles');
		switcher.innerHTML = '{strip}
		{if $smarty.const.KU_MENUSTYLESWITCHER && $smarty.const.KU_MENUTYPE eq 'normal'}
			{t}Styles{/t}:
			{foreach $styles as $style}
				[<a href="#" onclick="javascript:Styles.change(\'{$style|capitalize}\', false, true);reloadmain();" style="display: inline;" target="_self">{$style|upper|truncate:1}</a>]{if !$style@last} {/if}
			{/foreach}
		{/if}
		{/strip}';

}
{literal}
function toggle(button, area) {
	var tog=document.getElementById(area);
	if(tog.style.display)	{
		tog.style.display="";
	} else {
		tog.style.display="none";
	}
	button.innerHTML=(tog.style.display)?'+':'&minus;';
	set_cookie('nav_show_'+area, tog.style.display?'0':'1', 30);
}

function removeframes() {
	var boardlinks = document.getElementsByTagName("a");
	for(var i=0;i<boardlinks.length;i++) if(boardlinks[i].className == "boardlink") boardlinks[i].target = "_top";

	document.getElementById("removeframes").innerHTML = '{/literal}{t}Frames removed{/t}{literal}.';

	return false;
}
function reloadmain() {
	if (parent.main) {
		parent.main.location.reload();
	}
}
{/literal}
function hidedirs() {
	set_cookie('tcshowdirs', '', 30);
	{if $files.0 eq $files.1}
		location.reload(true)
	{else}
		window.location = '{$smarty.const.KU_WEBFOLDER}{$files.0}';
	{/if}
}
function showdirs() {
	set_cookie('tcshowdirs', 'yes', 30);
	{if $files.0 eq $files.1}
		location.reload(true)
	{else}
		window.location = '{$smarty.const.KU_WEBFOLDER}{$files.1}';
	{/if}
}

function updatenewpostscount() {
    $.ajax({
        url: '/newpostscount.php',
        data: localStorage['lastvisits'] ? (JSON.parse(localStorage['lastvisits']) || { }) : { },
        dataType: 'json',
        success: function(data) {
            iter_obj(data, function(brd, val) {
            	if(val != 0 || $('#newposts_'+brd).text() !== '') {
            		var newtext = (val == 0) ? '' : ' ('+val+')';
            		$('#newposts_'+brd).text(newtext);
            	}
            });
        },
        error: function() {
            alert(_.oops);
        }
    });
}
function iter_obj(object, callback) {
    for (var property in object) {
        if (object.hasOwnProperty(property)) {
            callback(property, object[property]);
        }
    }
}
//--></script>
<base target="main" />
</head>
<body>
<h1><a href="{$smarty.const.KU_WEBFOLDER}" target="_top" title="{t}Front Page{/t}">{$smarty.const.KU_NAME}</a></h1>
<ul>
{if $showdirs eq 0}
	<li><a onclick="javascript:showdirs();" href="{$files.1}" target="_self">[{t}Show Directories{/t}]</a></li>
{else}
	<li><a onclick="javascript:hidedirs();" href="{$files.0}" target="_self">[{t}Hide Directories{/t}]</a></li>
{/if}
{if $smarty.const.KU_MENUSTYLESWITCHER && $smarty.const.KU_MENUTYPE eq 'normal'}
	<li id="sitestyles"><a onclick="javascript:showstyleswitcher();" href="#" target="_self">[{t}Site Styles{/t}]</a></li>
{/if}
{* if $smarty.const.KU_MENUTYPE eq 'normal'}
	<li id="removeframes"><a href="#" onclick="javascript:return removeframes();" target="_self">[{t}Remove Frames{/t}]</a></li>
{/if *}
<li id="refreshnewposts"><a href="#" onclick="javascript:updatenewpostscount();return false" target="_self">Обновить</a></li>
</ul>
{if empty($boards)}
	<ul>
		<li>{t}No visible boards{/t}</li>
	</ul>
{else}

	{foreach $boards as $sect}
	
		{if $smarty.const.KU_MENUTYPE eq 'normal'}
			<h2>
		{else}
			<h2 style="display: inline;"><br />
		{/if}
		{if $smarty.const.KU_MENUTYPE eq 'normal'}
			<span class="plus" onclick="toggle(this, '{$sect.abbreviation}');" title="{t}Click to show/hide{/t}">{if $sect.hidden eq 1}+{else}&minus;{/if}</span>&nbsp;
		{/if}
		{$sect.name}</h2>
		{if $smarty.const.KU_MENUTYPE eq 'normal'}
			<div id="{$sect.abbreviation}"{if $sect.hidden eq 1} style="display: none;"{/if}>
		{/if}
		<ul>
		{if count($sect.boards) > 0}
			{foreach $sect.boards as $brd}
				<li><a href="{$smarty.const.KU_BOARDSPATH}/{$brd.name}/" class="boardlink{if $brd.trial eq 1} trial{/if}{if $brd.popular eq 1} pop{/if}">
				{if $showdirs eq 1}
					/{$brd.name}/ - 
				{/if}
				{$brd.desc}
				{if $brd.locked eq 1}
					<img src="{$smarty.const.KU_BOARDSPATH}/css/locked.gif" border="0" alt="{t}Locked{/t}">
				{/if}
				<span id="newposts_{$brd.name}"></span>
				</a></li>
			{/foreach}
		{else}
			<li>{t}No visible boards{/t}</li>
		{/if}
		</ul>
		{if $smarty.const.KU_MENUTYPE eq 'normal'}
			</div>
		{/if}
	{/foreach}
{/if}
{if $smarty.const.KU_IRC}
	{if $smarty.const.KU_MENUTYPE eq 'normal'}
		<h2>
	{else}
		<h2 style="display: inline;"><br />
	{/if}
	&nbsp;IRC</h2>
	<ul>
		<li>{$smarty.const.KU_IRC}</li>
	</ul>
{/if}
<script type="text/javascript">
$(document).ready(function() {
	updatenewpostscount();
	$('#refreshnewposts').click(updatenewpostscount);
})</script>
</body>
</html>


