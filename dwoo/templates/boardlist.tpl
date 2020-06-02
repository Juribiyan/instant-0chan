{if %KU_GENERATEBOARDLIST}
	{foreach name=sections item=sect from=$boardlist}
		{if not $sect.isempty}
		[<label class="collapsible-menu">
			<input type="checkbox" {if not $sect.is_20} checked{/if}>
			<span class="collapsible-menu-name">{$sect.nick}</span>
			<span class="collapsed-menu-contents">
				{foreach name=brds item=brd from=$sect}
					{if isset($brd.desc) and is_array($brd)}
					<a title="{$brd.desc}" href="{%KU_BOARDSFOLDER}{$brd.name}/">{$brd.name}</a>{if $.foreach.brds.last}{else} / {/if}
					{/if}
				{/foreach}
			</span>
		</label>]
		{/if}
	{/foreach}
{else}
	{if is_file($boardlist)}
		{include $boardlist}
	{/if}
{/if}
[<label class="collapsible-menu">
	<input type="checkbox">
	<span class="collapsible-menu-name"><i>{t}Style{/t}</i></span>
	<span class="collapsed-menu-contents">
		{foreach name=syles item=style from=$ku_styles}
			<a href='{%KU_WEBPATH}/setstylesheet.php?style={$style|capitalize}' onclick="javascript:Styles.change('{$style|capitalize}');return false;">{$style|capitalize}</a>
			{if not $.foreach.syles.last} / {/if}
		{/foreach}
	</span>
</label>]