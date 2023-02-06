<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">
<channel>
<title>{$smarty.const.KU_NAME} - Modlog</title>
<link>{$smarty.const.KU_WEBPATH}</link>
<description>Live view of all moderative actions on {$smarty.const.KU_WEBPATH}</description>
<language>{$smarty.const.KU_LOCALE}</language>
{foreach $entries sd $entry}
	<item>
	<title>{$entry.timestamp|date_format:"D m/d H:i"}</title>
	<description><![CDATA[{$entry.user} - {$entry.entry}]]></description>
	</item>
{/foreach}
</channel>
</rss>
