{if $standalone}
<link rel="stylesheet" type="text/css" href="{%KU_BOARDSPATH}/css/site_{%KU_DEFAULTSTYLE}.css" title="Futaba">
</head>
<h1>{%KU_NAME}</h1>
<h3>{%KU_SLOGAN}</h3>
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
<h4>{t}Your ID is{/t} <span class="mentions-me">{$my_id}</span></h4>
<table cellspacing="2" cellpadding="1" border="1" width="100%"><tbody>
	<tr>
	  <th>{t}Time{/t}</th>
	  <th>{t}User{/t}</th>
	  <th>ID</th>
	  <th>{t}Board{/t}</th>
	  <th width="100%">{t}Action{/t}</th>
	</tr>
	{foreach name=modlog_entries item=entry from=$modlog_entries}
		<tr{if $entry.id != '' && $entry.id == $my_id} class="mentions-me"{/if}>
		  <td>{$entry.timestamp|date_format:"y/m/d(D)h:i"}</td>
		  <td>{$entry.user}</td>
		  <td class="id-col" title="{$entry.id}">{$entry.id}</td>
		  <td>
		  	{if $entry.boards != ''}
		  		{assign var="brds" value=explode(',', $entry.boards)}
		  		{foreach from=$brds item=board name=boards}
		  			<a href="{%KU_WEBPATH}{$board}/">/{$board}/</a>{if $.foreach.boards.last}{else},{/if}
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