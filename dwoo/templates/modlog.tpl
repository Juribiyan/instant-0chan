{if $standalone}
<link rel="stylesheet" type="text/css" href="{$smarty.const.KU_BOARDSPATH}/css/site_{$smarty.const.KU_DEFAULTSTYLE}.css" title="Futaba">
</head>
<h1>{$smarty.const.KU_NAME}</h1>
<h3>{$smarty.const.KU_SLOGAN}</h3>
<div style="margin: 3em;">
{/if}
<style>
	.id-col {
		max-width: 8ch;
		overflow: hidden;
		text-overflow: ellipsis;
	}
	td:first-child {
		font-size: smaller;
	}
	{if $standalone}
	body {
		margin: 0 auto;
	}
	.mentions-me {
		color:  red;
	}
	{else}
	.id-col {
		min-width: 8ch;
		word-break: break-all;
		font-size: smaller;
	}
	{/if}
</style>
<h2>{t}ModLog{/t}</h2>
{if $standalone} <h4>{t}Your ID is{/t} <span class="mentions-me">{$my_id}</span> ({t}ID's are encrypted{/t})</h4>{/if}
<table cellspacing="2" cellpadding="1" border="1" width="100%"><tbody>
	<tr>
	  <th>{t}Time{/t}</th>
	  <th>{t}User{/t}</th>
	  <th{if $standalone} title="{t}ID's are encrypted{/t}"{/if}>ID{if $standalone}*{/if}</th>
	  <th>{t}Board{/t}</th>
	  <th width="100%">{t}Action{/t}</th>
	</tr>
	{foreach $modlog_entries as $entry}
		<tr{if $entry.id != '' && $entry.id == $my_id} class="mentions-me"{/if}>
		  <td>{$entry.timestamp|date_format:"y/m/d(D)h:i"}</td>
		  <td>{$entry.user}</td>
		  <td class="id-col" title="{$entry.id}">{$entry.id}</td>
		  <td>
		  	{if $entry.boards != ''}
		  		{assign var="brds" value=explode(',', $entry.boards)}
		  		{foreach $brds as $board}
		  			<a href="{$smarty.const.KU_WEBPATH}{$board}/">/{$board}/</a>{if $board@last}{else},{/if}
		  		{/foreach}
		  	{/if}
		  </td>
		  <td width="100%">{$entry.entry}</td>
		</tr>
	{/foreach}
</tbody></table>
{if $standalone}
</div>
<script>
	[].forEach.call(document.querySelectorAll('.id-col'), function(c) {
		c.addEventListener('mouseenter', function() {
			[].forEach.call(document.querySelectorAll('.id-col[title="'+this.title+'"]'), function(sc) {
				sc.style.color = 'red'
			})
		})
		c.addEventListener('mouseleave', function() {
			[].forEach.call(document.querySelectorAll('.id-col'), function(sc) {
				sc.style.color = null
			})
		})
	})
	
</script>
</body></html>
{/if}