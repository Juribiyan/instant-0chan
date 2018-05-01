{if not $isread}
	<table class="userdelete">
	<tbody>
	<tr>
	<td>
		{t}Password{/t}
		<input type="password" name="postpassword" size="8" class="make-me-readonly"/>
	</td>
	</tr>
	<tr>
	<td>
	<input name="deletepost" value="{t}Delete post{/t}" type="submit" />{if $board.enablereporting eq 1}&nbsp;<input name="reportpost" value="{t}Report{/t}" type="submit" />{/if}
	</td>
	</tr>
	</tbody>
	</table>

	</form>

	<script type="text/javascript"><!--
		set_delpass("delform");
	//--></script>
{/if}
{if $replythread eq 0}
	<table border="1">
	<tbody>
		<tr>
			<td>
				{if $thispage eq 0}
					{t}Previous{/t}
				{else}
					<form method="get" action="{%KU_BOARDSFOLDER}{$board.name}/{if ($thispage-1) neq 0}{$thispage-1}.html{/if}">
						<input value="{t}Previous{/t}" type="submit" /></form>
				{/if}
			</td>
			<td>
				&#91;{if $thispage neq 0}<a href="{%KU_BOARDSPATH}/{$board.name}/">{/if}0{if $thispage neq 0}</a>{/if}&#93;
				{section name=pages loop=$numpages}
				{strip}
					&#91;
					{if $.section.pages.iteration neq $thispage}<a href="{%KU_BOARDSFOLDER}{$board.name}/{$.section.pages.iteration}.html">
					{/if}
					
					{$.section.pages.iteration}
					
					{if $.section.pages.iteration neq $thispage}
					</a>
					{/if}
					&#93;
				{/strip}
				{/section}	
			</td>
			<td>
				{if $thispage eq $numpages}
					{t}Next{/t}
				{else}
					<form method="get" action="{%KU_BOARDSPATH}/{$board.name}/{$thispage+1}.html"><input value="{t}Next{/t}" type="submit" /></form>
				{/if}
	
			</td>
		</tr>
	</tbody>
	</table>
{/if}
<br />
{if $boardlist}
	<div id="boardlist_footer" class="navbar">
	{if %KU_GENERATEBOARDLIST}
		{foreach name=sections item=sect from=$boardlist}
			{if $sect.abbreviation neq '20'}
			[
			{foreach name=brds item=brd from=$sect}
				{if isset($brd.desc) and is_array($brd)}
				<a title="{$brd.desc}" href="{%KU_BOARDSFOLDER}{$brd.name}/">{$brd.name}</a>{if $.foreach.brds.last}{else} / {/if}
				{/if}
			{/foreach}
			]
			{else}
			<span style="float: right">[<select onchange="javascript:if(selectedIndex != 0) location.href='{%KU_WEBPATH}/' + this.options[this.selectedIndex].value;">
			<option>2.0</option>
			{foreach name=brds item=brd from=$sect}
				{if isset($brd.desc) and is_array($brd)}
				<option value="{$brd.name}">/{$brd.name}/ - {$brd.desc}</option>{if $.foreach.brds.last}{else} / {/if}
				{/if}
			{/foreach}
			</select>]</span>
			{/if}
		{/foreach}
	{else}
		{if is_file($boardlist)}
			{include $boardlist}
		{/if}
	{/if}
	</div>
{/if}
</td>
<td width="13" class="border-right"></td>
</tr></tbody></table>
<table width="98%" border="0" align="center" cellpadding="0" cellspacing="0" class="maintable">
	<tbody>
		<tr>
	    <td width="20" height="84" class="bottom-left"></td>
	    <td height="84" class="bottom-center">&nbsp;</td>
	    <td width="19" height="84" class="bottom-right"></td>
		</tr>
	</tbody>
</table>