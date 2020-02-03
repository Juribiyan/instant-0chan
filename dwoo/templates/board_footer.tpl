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
	<input name="deletepost" value="{t}Delete post{/t}" type="submit" class="styled-button bad-button" />{if $board.enablereporting eq 1}&nbsp;<input name="reportpost" value="{t}Report{/t}" type="submit" class="styled-button" />{/if}
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
{if $boardlist_prebuilt}
	<div id="boardlist_footer" class="navbar boardlist">{$boardlist_prebuilt}</div>
{/if}
</div><!-- /.content -->