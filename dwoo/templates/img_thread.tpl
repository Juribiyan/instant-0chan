{if not $isexpand and not $isread}
	<form id="delform" action="{%KU_CGIPATH}/board.php" method="post">
	<input type="hidden" name="board" value="{$board.name}" />
{/if}
	{foreach key=postkey item=post from=$posts name=postsloop}
	
		{if $post.parentid eq 0}
		<div id="thread{$post.id}{$board.name}" class="replies">
		<div class="postnode op">
			<a name="s{$.foreach.thread.iteration}"></a>
			
			{if ($post.file neq '' || $post.file_type neq '' ) && (( $post.videobox eq '' && $post.file neq '') && $post.file neq 'removed')}
				<span class="filesize">
				{if $post.file_type eq 'mp3' or $post.file_type eq 'ogg'}
					{t}Audio{/t}
				{else}
					{t}File{/t}
				{/if}
				<a {if %KU_NEWWINDOW}target="_blank"{/if}	href="{$file_path}/src/{$post.file}.{$post.file_type}">
				{if isset($post.id3.comments_html)}
					{if $post.id3.comments_html.artist.0 neq ''}
					{$post.id3.comments_html.artist.0} -
					{/if}
					{if $post.id3.comments_html.title.0 neq ''}
						{$post.id3.comments_html.title.0}{else}{$post.file_original}{if $post.id3.comments_html.artist.0 eq ''}.{$post.file_type}{/if}{/if}</a>
				{else}
					{if $post.file_type eq 'mp3' or $post.file_type eq 'ogg' or $post.file_type eq 'swf'}{$post.file_original}{else}{$post.file}{/if}.{$post.file_type}</a>
				{/if}
				- ({$post.file_size_formatted}
				{if $post.id3.comments_html.bitrate neq 0 || $post.id3.audio.sample_rate neq 0}
					{if $post.id3.audio.bitrate neq 0}
						- {round($post.id3.audio.bitrate / 1000)} kbps
						{if $post.id3.audio.sample_rate neq 0}
							- 
						{/if}
					{/if}
					{if $post.id3.audio.sample_rate neq 0}
						{$post.id3.audio.sample_rate / 1000} kHz
					{/if}
				{/if}
				{if $post.image_w > 0 && $post.image_h > 0}
					, {$post.image_w}x{$post.image_h}
				{/if}
				)
				{if $post.id3.playtime_string neq ''}
					{t}Length{/t}: {$post.id3.playtime_string}
				{/if}
				</span>
				{if %KU_THUMBMSG}
					<span class="thumbnailmsg"> 
					{if $post.file_type neq 'jpg' && $post.file_type neq 'gif' && $post.file_type neq 'png' && $post.videobox eq ''}
						{t}Extension icon displayed, click image to open file.{/t}
					{else}
						{t}Thumbnail displayed, click image for full size.{/t}
					{/if}
					</span>
				{/if}
				<br />
			{/if}
			{if $post.videobox eq '' && $post.file neq '' && ( $post.file_type eq 'jpg' || $post.file_type eq 'gif' || $post.file_type eq 'png')}
				{if $post.file eq 'removed'}
					<div class="nothumb">
						{t}File<br />Removed{/t}
					</div>
				{else}
					<a 
					{if %KU_NEWWINDOW}
						target="_blank" 
					{/if}
					onclick="javascript:return expandimg('{$post.id}', '{$file_path}/src/{$post.file}.{$post.file_type}', '{$file_path}/thumb/{$post.file}s.{$post.file_type}', '{$post.image_w}', '{$post.image_h}', '{$post.thumb_w}', '{$post.thumb_h}');" 
					href="{$file_path}/src/{$post.file}.{$post.file_type}">
					<span id="thumb{$post.id}"><img src="{$file_path}/thumb/{$post.file}s.{$post.file_type}" alt="{$post.id}" class="thumb" height="{$post.thumb_h}" width="{$post.thumb_w}" /></span>
					</a>
				{/if}
			{elseif $post.nonstandard_file neq ''}
				{if $post.file eq 'removed'}
					<div class="nothumb">
						{t}File<br />Removed{/t}
					</div>
				{else}
					<a
					{if $post.file_type eq 'webm'} class="movie" data-id="{$post.id}" data-thumb="{$post.nonstandard_file}" data-width="{$post.image_w}" data-height="{$post.image_h}"{/if}
					{if $post.file_type eq 'mp3' or $post.file_type eq 'ogg'} class="audiowrap" {/if}
					{if $post.file_type eq 'css'} class="csswrap" {/if}
					{if %KU_NEWWINDOW}
						target="_blank" 
					{/if}								
					href="{$file_path}/src/{$post.file}.{$post.file_type}">
					<span id="thumb{$post.id}"><img src="{$post.nonstandard_file}" alt="{$post.id}" class="thumb" height="{$post.thumb_h}" width="{$post.thumb_w}" /></span>
					</a>
				{/if}
			{/if}
			<a name="{$post.id}"></a>
			<label class="postinfo">
			<input type="checkbox" name="post[]" value="{$post.id}" />
			{if $post.subject neq ''}
				<span class="filetitle">
					{$post.subject}
				</span>
			{/if}
			{strip}
				<span class="postername">
				{if $post.name eq '' && $post.tripcode eq ''}
					{$board.anonymous}
				{elseif $post.name eq '' && $post.tripcode neq ''}
				{else}
					{$post.name}
				{/if}

				</span>

				{if $post.tripcode neq ''}
					<span class="postertrip">!{$post.tripcode}</span>
				{/if}
			{/strip}
			{if $post.posterauthority eq 1}
				<span class="admin">
					&#35;&#35;&nbsp;{t}Admin{/t}&nbsp;&#35;&#35;
				</span>
			{elseif $post.posterauthority eq 4}
				<span class="mod">
					&#35;&#35;&nbsp;{t}Super Mod{/t}&nbsp;&#35;&#35;
				</span>
			{elseif $post.posterauthority eq 2}
				<span class="mod">
					&#35;&#35;&nbsp;{t}Mod{/t}&nbsp;&#35;&#35;
				</span>
			{/if}
			{$post.timestamp_formatted}
			</label>
			<span class="reflink">
				{$post.reflink}
			</span>
			{if %KU_QUICKREPLY}
			<span class="extrabtns">
				<a href="#" data-parent="{$post.id}" data-boardname="{$board.name}" data-postnum="{$post.id}" class="qrl" title="{t}Quick Reply{/t}  в тред {$post.id}">
          <svg class="icon b-icon"><use xlink:href="#i-qr"></use></svg>
				</a>
			{/if}
			{if $board.balls}
			<img class="_country_" src="{%KU_WEBPATH}/images/flags/{$post.country}.png">
			{/if}
			{if $board.showid}<img src="data:image/png;base64,{rainbow($post.ipmd5, $post.id);}" />{/if}
			<span id="dnb-{$board.name}-{$post.id}-y"></span>
			</span>
			{if $replink eq 1} [<a href="{%KU_BOARDSFOLDER}{$board.name}/res/{if $post.parentid eq 0}{$post.id}{else}{$post.parentid}{/if}.html">{t}Reply{/t}</a>]{/if}
			<br />
		{else}
		{* if $numimages > 0 && $isexpand && $.foreach.postsloop.first}
				<a class="xlink" href="#top" onclick="javascript:
				{foreach key=postkey2 item=post2 from=$posts}
					{if $post2.parentid neq 0}
						{if $post2.file_type eq 'jpg' || $post2.file_type eq 'gif' || $post2.file_type eq 'png'}
							expandimg('{$post2.id}', '{$file_path}/src/{$post2.file}.{$post2.file_type}', '{$file_path}/thumb/{$post2.file}s.{$post2.file_type}', '{$post2.image_w}', '{$post2.image_h}', '{$post2.thumb_w}', '{$post2.thumb_h}');
						{/if}
					{/if}
				{/foreach}
				return false;">{t}Expand all images{/t}</a>
			{/if *}

			<table class="postnode">
				<tbody>
				<tr>
					<td class="doubledash">
						&gt;&gt;
					</td>
					<td class="reply" id="reply{$post.id}">
						<a name="{$post.id}"></a>
						<label class="postinfo">
						<input type="checkbox" name="post[]" value="{$post.id}" />
						
						
						{if $post.subject neq ''}
							<span class="filetitle">
								{$post.subject}
							</span>
						{/if}
						{strip}
							<span class="postername">
							
							{if $post.name eq '' && $post.tripcode eq ''}
								{$board.anonymous}
							{elseif $post.name eq '' && $post.tripcode neq ''}
							{else}
								{$post.name}
							{/if}

							</span>

							{if $post.tripcode neq ''}
								<span class="postertrip">!{$post.tripcode}</span>
							{/if}
						{/strip}
						{if $post.posterauthority eq 1}
							<span class="admin">
								&#35;&#35;&nbsp;{t}Admin{/t}&nbsp;&#35;&#35;
							</span>
						{elseif $post.posterauthority eq 4}
							<span class="mod">
								&#35;&#35;&nbsp;{t}Super Mod{/t}&nbsp;&#35;&#35;
							</span>
						{elseif $post.posterauthority eq 2}
							<span class="mod">
								&#35;&#35;&nbsp;{t}Mod{/t}&nbsp;&#35;&#35;
							</span>
						{/if}
						{$post.timestamp_formatted}
						</label>

						<span class="reflink">
							{$post.reflink}
						</span>
						{if $board.showid}<img src="data:image/png;base64,{rainbow($post.ipmd5, $post.parentid);}" />{/if}
						<span class="extrabtns">
						{if %KU_QUICKREPLY}
						<a href="#" data-parent="{$post.parentid}" data-boardname="{$board.name}" data-postnum="{$post.id}" class="qrl" title="{t}Quick Reply{/t} в тред {$post.parentid}">
							<svg class="icon b-icon"><use xlink:href="#i-qr"></use></svg>
						</a>
						{/if}
						{if $board.balls}
						<img class="_country_" src="{%KU_WEBPATH}/images/flags/{$post.country}.png">
						{/if}
						{if $post.locked eq 1}
							<img style="border: 0;" src="{$boardpath}css/locked.gif" alt="{t}Locked{/t}" />
						{/if}
						{if $post.stickied eq 1}
							<img style="border: 0;" src="{$boardpath}css/sticky.gif" alt="{t}Stickied{/t}" />
						{/if}
						</span>
						<span id="dnb-{$board.name}-{$post.id}-n"></span>
						{if ($post.file neq '' || $post.file_type neq '' ) && (( $post.videobox eq '' && $post.file neq '') && $post.file neq 'removed')}
							<br /><span class="filesize">
							{if $post.file_type eq 'mp3' or $post.file_type eq 'ogg'}
								{t}Audio{/t}
							{else}
								{t}File{/t}
							{/if}
							<a {if %KU_NEWWINDOW}target="_blank"{/if}	href="{$file_path}/src/{$post.file}.{$post.file_type}">
							{if isset($post.id3.comments_html)}
								{if $post.id3.comments_html.artist.0 neq ''}
								{$post.id3.comments_html.artist.0} -
								{/if}
								{if $post.id3.comments_html.title.0 neq ''}
									{$post.id3.comments_html.title.0}{else}{$post.file_original}{if $post.id3.comments_html.artist.0 eq ''}.{$post.file_type}{/if}{/if}</a>
							{else}
								{if $post.file_type eq 'mp3' or $post.file_type eq 'ogg' or $post.file_type eq 'swf'}{$post.file_original}{else}{$post.file}{/if}.{$post.file_type}</a>
							{/if}
							- ({$post.file_size_formatted}
							{if $post.id3.comments_html.bitrate neq 0 || $post.id3.audio.sample_rate neq 0}
								{if $post.id3.audio.bitrate neq 0}
									- {round($post.id3.audio.bitrate / 1000)} kbps
									{if $post.id3.audio.sample_rate neq 0}
										- 
									{/if}
								{/if}
								{if $post.id3.audio.sample_rate neq 0}
									{$post.id3.audio.sample_rate / 1000} kHz
								{/if}
							{/if}
							{if $post.image_w > 0 && $post.image_h > 0}
								, {$post.image_w}x{$post.image_h}
							{/if}
							)
							{if $post.id3.playtime_string neq ''}
								{t}Length{/t}: {$post.id3.playtime_string}
							{/if}
							</span>
							{if %KU_THUMBMSG}
								<span class="thumbnailmsg"> 
								{if $post.file_type neq 'jpg' && $post.file_type neq 'gif' && $post.file_type neq 'png' && $post.videobox eq ''}
									{t}Extension icon displayed, click image to open file.{/t}
								{else}
									{t}Thumbnail displayed, click image for full size.{/t}
								{/if}
								</span>
							{/if}

						{/if}
						{if $post.videobox eq '' && $post.file neq '' && ( $post.file_type eq 'jpg' || $post.file_type eq 'gif' || $post.file_type eq 'png')}
							<br />
							{if $post.file eq 'removed'}
								<div class="nothumb">
									{t}File<br />Removed{/t}
								</div>
							{else}
								<a 
					{if %KU_NEWWINDOW}
						target="_blank" 
					{/if}
					onclick="javascript:return expandimg('{$post.id}', '{$file_path}/src/{$post.file}.{$post.file_type}', '{$file_path}/thumb/{$post.file}s.{$post.file_type}', '{$post.image_w}', '{$post.image_h}', '{$post.thumb_w}', '{$post.thumb_h}');" 
					href="{$file_path}/src/{$post.file}.{$post.file_type}">
					<span id="thumb{$post.id}"><img src="{$file_path}/thumb/{$post.file}s.{$post.file_type}" alt="{$post.id}" class="thumb" height="{$post.thumb_h}" width="{$post.thumb_w}" /></span>
					</a>
							{/if}
						{elseif $post.nonstandard_file neq ''}
							<br />
							{if $post.file eq 'removed'}
								<div class="nothumb">
									{t}File<br />Removed{/t}
								</div>
							{else}
								<a
					{if $post.file_type eq 'webm'} class="movie" data-id="{$post.id}" data-thumb="{$post.nonstandard_file}" data-width="{$post.image_w}" data-height="{$post.image_h}"{/if}
					{if $post.file_type eq 'mp3' or $post.file_type eq 'ogg'} class="audiowrap" {/if}
					{if $post.file_type eq 'css'} class="csswrap" {/if}
					{if %KU_NEWWINDOW}
						target="_blank" 
					{/if}								
					href="{$file_path}/src/{$post.file}.{$post.file_type}">
					<span id="thumb{$post.id}"><img src="{$post.nonstandard_file}" alt="{$post.id}" class="thumb" height="{$post.thumb_h}" width="{$post.thumb_w}" /></span>
					</a>
							{/if}
						{/if}

		{/if}
		<blockquote class="postmessage">
		{if $post.videobox}
			{$post.videobox}
		{/if}
		{$post.message}
		<div class="replieslist"></div>
		</blockquote>
		{if not $post.stickied && $post.parentid eq 0 && (($board.maxage > 0 && ($post.timestamp + ($board.maxage * 3600)) < (time() + 7200 ) ) || ($post.deleted_timestamp > 0 && $post.deleted_timestamp <= (time() + 7200)))}
			<span class="oldpost">
				{t}Marked for deletion (old){/t}
			</span>
			<br />
		{/if}
		{if $post.parentid eq 0}
		</div>
			{if $modifier eq 'last50'}
					<span class="omittedposts">
							{$replycount-50}
							{if $replycount-50 eq 1}
									{t lower="yes"}Post{/t} 
							{else}
									{t lower="yes"}Posts{/t} 
							{/if}
					{t}omitted{/t}. {t}Last 50 shown{/t}.
					</span>
			{/if}
			{* if $numimages > 0}
				<a href="#top" onclick="javascript:
				{foreach key=postkey2 item=post2 from=$posts}
					{if $post2.parentid neq 0}
						{if $post2.file_type eq 'jpg' || $post2.file_type eq 'gif' || $post2.file_type eq 'png'}
							expandimg('{$post2.id}', '{$file_path}/src/{$post2.file}.{$post2.file_type}', '{$file_path}/thumb/{$post2.file}s.{$post2.file_type}', '{$post2.image_w}', '{$post2.image_h}', '{$post2.thumb_w}', '{$post2.thumb_h}');
						{/if}
					{/if}
				{/foreach}
				return false;">{t}Expand all images{/t}</a>
			{/if *}
		{else}
				</td>
			</tr>
		</tbody>
		</table>
		{/if}
	{/foreach}
	{if $modifier eq 'first100'}
		<span class="omittedposts" style="float: left">
			{$replycount-100}
			{if $replycount-100 eq 1}
				{t lower="yes"}Post{/t} 
			{else}
				{t lower="yes"}Posts{/t} 
			{/if}
			{t}omitted{/t}. {t}First 100 shown{/t}.
		</span>
	{/if}
	{if not $isread}
		{if $replycount > 2}
			<span style="float:right">
				&#91;<a href="/{$board.name}/">{t}Return{/t}</a>&#93;
				{if %KU_FIRSTLAST && ( count($posts) > 50 || $replycount > 50)}
					&#91;<a href="/{$board.name}/res/{$posts.0.id}.html">{t}Entire Thread{/t}</a>&#93; 
					&#91;<a href="/{$board.name}/res/{$posts.0.id}+50.html">{t}Last 50 posts{/t}</a>&#93;
					{if ( count($posts) > 100 || $replycount > 100) }
						&#91;<a href="/{$board.name}/res/{$posts.0.id}-100.html">{t}First 100 posts{/t}</a>&#93;
					{/if}
				{/if}
			</span>
		{/if}
	</div>
	{if not $isexpand}
		<br clear="left" />
		<div id="newposts_get"><a href="#" onclick="return getnewposts()">
      <svg class="icon b-icon"><use xlink:href="#i-refresh"></use></svg> Получить новые посты (если есть)</a>
		</div>
		<div id="newposts_load" style="display:none;">
			<img src="{%KU_WEBPATH}/images/loading.gif"> Загрузка...
		</div>
		<hr />
	{/if}
{/if}