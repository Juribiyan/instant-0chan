{if !$partial}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"> 
<head>
	<script src="{$smarty.const.KU_WEBFOLDER}lib/javascript/jquery-3.6.3.min.js"></script>
	<title>{$smarty.const.KU_NAME}</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	
	{foreach $styles as $style}
		<link rel="{if $style neq $smarty.const.KU_DEFAULTMENUSTYLE}alternate {/if}stylesheet" type="text/css" 
		href="{$cwebpath}css/{$style}.css?v={$smarty.const.KU_CSSVER}" 
		title="{$style|capitalize}" />
	{/foreach}
<script type="text/javascript">
	var style_cookie_site = "kustyle_site";
</script>
<style>
.news-cont {
	margin: 5px;
	padding-bottom: 5px;
}
#mfmenu {
	text-align: center;
height: 40px;
font-size: 1.5em;
}
#mfmenu a {
	display: inline-block;
min-width: 100px;
height: 40px;
vertical-align: middle;
line-height: 40px;
}
</style>
<link rel="shortcut icon" href="{$smarty.const.KU_WEBFOLDER}favicon.ico" />
<script type="text/javascript" src="{$smarty.const.KU_WEBFOLDER}lib/javascript/gettext.js"></script>
<script type="text/javascript" src="{$smarty.const.KU_WEBFOLDER}inc/javascript/kusaba.js"></script></head>
<body>
	<center><div class="sitelogo"></div></center>
	{if $smarty.const.KU_SLOGAN neq ''}<center><h3>{$smarty.const.KU_SLOGAN}</h3></center>{/if}
	
	<div class="menu" id="topmenu">
	{$topads}<br />
		<div id="mfmenu">
			<a href="/?p=news" data-page="news">{t}News{/t}</a>
			<a href="/?p=faq" data-page="faq">{t}FAQ{/t}</a>
			<a href="/?p=rules" data-page="rules">{t}Rules{/t}</a>
		</div>

	</div>
{/if}
<div id="entries">
{foreach $entries as $entry}
	<div class="content">
		<h2><span class="newssub">{stripslashes($entry.subject)}{if $smarty.get.p eq ''} by {if $entry.email neq ''}<a href="mailto:{stripslashes($entry.email)}">{/if}{stripslashes($entry.poster)}{if $entry.email neq ''}</a>{/if} - {$entry.timestamp|date_format:"m/d/y @ h:i A T"}{/if}</span>
		<span class="permalink"><a href="#{$entry.id}">#</a></span></h2>
		<div style="margin:5px; padding-bottom: 5px;">{stripslashes($entry.message)}</div>
	</div><br />
{/foreach}
</div>
{$botads}
{if !$partial}
<script>
function getPage(name) {
	$.get('?partial&p='+name, function(data) {
		// $('#entries').empty();
		// console.log($(data).html());
		$('#entries').html($(data).html());
		/*$($(data).filter('#main')[0]).find('.entry').each(function() {
			var $entry = $(this);
			$entry.addClass('content');
			$('#entries').append($entry).append('<br>');
		});*/
	})
}

$(document).ready(function() {

	// getPage('/news');

	$('#mfmenu a').click(function(event) {
		event.preventDefault();
		if($(this).hasClass('active-tab')) return;
		$('.active-tab').removeClass('active-tab');
		$(this).addClass('active-tab');
		getPage($(this).data('page'));
	});
});
</script>
</body>
</html>
{/if}
