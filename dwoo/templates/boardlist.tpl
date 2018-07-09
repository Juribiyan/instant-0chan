{if %KU_GENERATEBOARDLIST}
	{foreach name=sections item=sect from=$boardlist}
		{if not $sect.is_20}
		[
		{foreach name=brds item=brd from=$sect}
			{if isset($brd.desc) and is_array($brd)}
			<a title="{$brd.desc}" href="{%KU_BOARDSFOLDER}{$brd.name}/">{$brd.name}</a>{if $.foreach.brds.last}{else} / {/if}
			{/if}
		{/foreach}
		]
		{else}[<select onchange="javascript:if(selectedIndex != 0) location.href='{%KU_WEBPATH}/' + this.options[this.selectedIndex].value;">
		<option>2.0</option>
		{foreach name=brds item=brd from=$sect}
			{if isset($brd.desc) and is_array($brd)}
			<option value="{$brd.name}">/{$brd.name}/ - {$brd.desc}</option>{if $.foreach.brds.last}{else} / {/if}
			{/if}
		{/foreach}
		</select>]
		{/if}
	{/foreach}
{else}
	{if is_file($boardlist)}
		{include $boardlist}
	{/if}
{/if}
[ {t}Style{/t}: {foreach name=syles item=style from=$ku_styles}
	<a href='{%KU_WEBPATH}/setstylesheet.php?style={$style}'>{$style|capitalize}</a>
	{if not $.foreach.syles.last} /{/if}
{/foreach}]