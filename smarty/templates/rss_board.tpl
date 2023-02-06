<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">
<channel>
<title>{$smarty.const.KU_NAME} - {$boardname}</title>
<link>{$smarty.const.KU_BOARDSPATH}/{$boardname}</link>
<description>Live RSS feed for {$smarty.const.KU_BOARDSPATH}/{$boardname}</description>
<language>{$smarty.const.KU_LOCALE}</language>';
{foreach $posts as $item}
	<item>
	<title>{$item.id}</title>
	<link>
	{if $item.parentid neq 0}
		{$smarty.const.KU_BOARDSPATH}/{$boardname}/res/{$item.parentid}.html#{$item.id}</link>
	{else}
		{$smarty.const.KU_BOARDSPATH}/{$boardname}/res/{$item.id}.html</link>
	{/if}
	<description><![CDATA[
	{if $item.file neq ''}
		{if $item.file_type eq 'jpg' || $item.file_type eq 'png' || $item.file_type eq 'gif'}
			<a href="{$smarty.const.KU_BOARDSPATH}/{$boardname}/src/{$item.file}.{$item.file_type}"><img src="{$smarty.const.KU_BOARDSPATH}/{$boardname}/thumb/{$item.file}s.{$item.file_type}" /></a><br /><br />
		{else}
			[{$smarty.const.KU_BOARDSPATH}/{$boardname}/src/{$item.file}.{$item.file_type}] <br /><br />
		{/if}
	{/if}
	{if trim($item.message) neq ''}
		{stripslashes($item.message)}<br />
	{/if}
	]]></description>
	</item>
{/foreach}
</channel>
</rss>
