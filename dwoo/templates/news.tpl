{if !$partial}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"> 
<head>
	<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script>
	if (!window.jQuery) {
	    document.write('<script src="{$cwebpath}/lib/javascript/jquery-1.11.1.min.js"><\/script>');
	}
	</script>
	<title>{$dwoo.const.KU_NAME}</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	
	{for style $styles}
				<link rel="{if $styles[$style] neq $dwoo.const.KU_DEFAULTMENUSTYLE}alternate {/if}stylesheet" type="text/css" href="{$dwoo.const.KU_WEBFOLDER}css/site_{$styles[$style]}.css" title="{$styles[$style]|capitalize}" />
	{/for}
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
<link rel="shortcut icon" href="{$dwoo.const.KU_WEBFOLDER}favicon.ico" />
<script type="text/javascript" src="{%KU_WEBFOLDER}lib/javascript/gettext.js"></script>
<script type="text/javascript" src="{$dwoo.const.KU_WEBFOLDER}lib/javascript/kusaba.js"></script></head>
<body>
	<center><div class="sitelogo"></div></center>
	{if $dwoo.const.KU_SLOGAN neq ''}<center><h3>{$dwoo.const.KU_SLOGAN}</h3></center>{/if}
	
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
{foreach item=entry from=$entries}
	<div class="content">
		<h2><span class="newssub">{$entry.subject|stripslashes}{if $dwoo.get.p eq ''} by {if $entry.email neq ''}<a href="mailto:{$entry.email|stripslashes}">{/if}{$entry.poster|stripslashes}{if $entry.email neq ''}</a>{/if} - {$entry.timestamp|date_format:"%D @ %I:%M %p %Z"}{/if}</span>
		<span class="permalink"><a href="#{$entry.id}">#</a></span></h2>
		<div style="margin:5px; padding-bottom: 5px;">{$entry.message|stripslashes}</div>
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