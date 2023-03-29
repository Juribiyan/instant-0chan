{if $smarty.const.I0_OVERBOARD_ENABLED}
	[<a title="{$smarty.const.I0_OVERBOARD_DESCRIPTION}" href="{$smarty.const.KU_BOARDSFOLDER}{$smarty.const.I0_OVERBOARD_DIR}/">{$smarty.const.I0_OVERBOARD_DIR}</a>]
{/if}
{if $smarty.const.KU_GENERATEBOARDLIST}
	{foreach $boardlist as $sect}
		{if not $sect.isempty}
		[<label class="collapsible-menu">
			<input type="checkbox" {if not $sect.is_20} checked{/if}>
			<span class="collapsible-menu-name">{$sect.nick}</span>
			<span class="collapsed-menu-contents">
				{foreach $sect as $brd}
					{if isset($brd.desc) and is_array($brd)}
					<a title="{$brd.desc}" href="{$smarty.const.KU_BOARDSFOLDER}{$brd.name}/">{$brd.name}</a>{if $brd@last}{else} / {/if}
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
		{foreach $ku_styles as $style}
			<a href='{$smarty.const.KU_WEBPATH}/setstylesheet.php?style={$style|capitalize}' onclick="javascript:Styles.change('{$style|capitalize}');return false;">{$style|capitalize}</a>
			{if not $style@last} / {/if}
		{/foreach}
	</span>
</label>]