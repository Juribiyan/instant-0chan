<div class="postarea">
<a id="postbox"></a>
{if not $isthread}<div class="i0svcel">!i0-pb</div>{/if}<form name="postform" id="postform" action="{%KU_CGIPATH}/board.php" method="post" enctype="multipart/form-data" class="main-reply-form" data-maxfiles="{$board.maxfiles}" data-allowed-filetypes="{foreach name=files item=filetype from=$board.filetypes_allowed}{$filetype}{if $.foreach.files.last}{else},{/if}{/foreach}">
	<input type="hidden" name="board" value="{$board.name}" />
	<input type="hidden" name="replythread" value="<!sm_threadid>" />
	<input type="hidden" name="makepost" value="1" />
	<input type="text" name="email" size="28" maxlength="{%KU_MAXEMAILLENGTH}" value="" style="display: none;" />
	<table class="postform">
		<tbody>
		{if $board.forcedanon neq 1}
			<tr class="name-row">
				<td class="postblock">
					{t}Name{/t}</td>
				<td>
					<input type="text" name="name" placeholder="{t}Name{/t}#{t}tripcode{/t}" size="28" maxlength="{%KU_MAXNAMELENGTH}" accesskey="n" autocomplete="new-password"/>
					<label for="disable_name">
	          <input id="disable_name" type="checkbox" name="disable_name" value="1" style="vertical-align: -2px;">&nbsp;{t}Anonymously{/t}
	        </label>
				</td>
			</tr>
		{/if}
		<tr class="sage-row">
			<td class="postblock">
				Sage</td>
			<td>
				<label for="sage">
	 				<input id="sage" type="checkbox" name="em" value="sage" style="vertical-align: middle;">({t}do not bump this thread{/t})
	 			</label>
			</td>
		</tr>
		<tr class="subject-send-row">
			<td class="postblock">
				{t}Subject{/t}
			</td>
			<td class="subject-submit">
				{strip}<input type="text" name="subject" placeholder="{t}Subject{/t}" size="35" maxlength="{%KU_MAXSUBJLENGTH}" accesskey="s" />&nbsp;<input class="primary styled-button" type="submit" value="{if $replythread eq 0}{t}Submit{/t}{else}{t}Post reply{/t}{/if}" accesskey="z" />{/strip}
			</td>
		</tr>
		<tr class="message-row">
			<td class="postblock">
				{t}Message{/t}
			</td>
			<td>
				<div class="markupbtns yesscript">{* <nobr style="font-size: 2px;"> *}
					<a title="{t}Bold{/t}" href="#" class="uibutton uib-mup" data-mups="**" data-mupe="**"><b>{t}B{/t}</b></a>
					<a title="{t}Italic{/t}" href="#" class="uibutton uib-mup" data-mups="*" data-mupe="*"><i>{t}I{/t}</i></a>
					<a title="{t}Undeline{/t}" href="#" class="uibutton uib-mup" data-mups="[u]" data-mupe="[/u]"><u>{t}U{/t}</u></a>
					<a title="{t}Strike{/t}" href="#" class="uibutton uib-mup" data-mups="~~" data-mupe="~~"><s>{t}S{/t}</s></a>
					<a title="{t}Spoiler{/t}" href="#" class="uibutton uib-mup uib-spoiler" data-mups="%%" data-mupe="%%">{t}Sp{/t}</a>
					<a title="{t}Code{/t}" href="#" class="uibutton {if %KU_USE_GESHI}opt-exp{else}uib-mup{/if}" data-mups="[code]" data-mupe="[/code]" data-imups="`" data-imupe="`"><span class="uib-code">();{if %KU_USE_GESHI} ▼{/if}</span>{if %KU_USE_GESHI}<div class="expandee code_markup markup-popup"><select size="8" class="code_markup_select"><option value="text">Простой текст</option><option value="actionscript">ActionScript</option><option value="actionscript3">ActionScript 3</option><option value="apache">Apache configuration</option><option value="asm">ASM</option><option value="bash">Bash</option><option value="batch">Windows Batch file</option><option value="bf">Brainfuck</option><option value="c">C</option><option value="clojure">Clojure</option><option value="coffeescript">CoffeeScript</option><option value="cpp">C++</option><option value="csharp">C#</option><option value="css">CSS</option><option value="d">D</option><option value="dart">Dart</option><option value="delphi">Delphi</option><option value="erlang">Erlang</option><option value="fsharp">F#</option><option value="glsl">glSlang</option><option value="go">Go</option><option value="groovy">Groovy</option><option value="haskell">Haskell</option><option value="haxe">Haxe</option><option value="html5">HTML5</option><option value="ini">INI</option><option value="io">Io</option><option value="java">Java</option><option value="javascript">Javascript</option><option value="kotlin">Kotlin</option><option value="latex">LaTeX</option><option value="lisp">Lisp</option><option value="lolcode">LOLcode</option><option value="lua">Lua</option><option value="make">GNU make</option><option value="mathematica">Mathematica</option><option value="matlab">Matlab M</option><option value="nginx">nginx</option><option value="objc">Objective-C</option><option value="ocaml">OCaml</option><option value="pcre">PCRE</option><option value="perl">Perl</option><option value="php">PHP</option><option value="python">Python</option><option value="racket">Racket</option><option value="rails">Rails</option><option value="ruby">Ruby</option><option value="rust">Rust</option><option value="sass">Sass</option><option value="scala">Scala</option><option value="scheme">Scheme</option><option value="smalltalk">Smalltalk</option><option value="smarty">Smarty</option><option value="sql">SQL</option><option value="systemverilog">SystemVerilog</option><option value="vb">Visual Basic</option><option value="vbnet">vb.net</option><option value="vbscript">VBScript</option><option value="verilog">Verilog</option><option value="vhdl">VHDL</option><option value="whitespace">Whitespace</option><option value="xml">XML</option><option value="yaml">YAML</option></select></div>{/if}</a>
					<a title="{t}Greenquoting{/t}" href="#" class="uibutton opt-exp" data-bul="&gt;" data-imups="&gt;" data-imupe="&lt;"><span class="uib-imply">&gt; ▼</span>
						<div class="expandee quote_markup markup-popup">
							<select size="3" class="quote_select">
								<option value="g" title="{t}Green quotes{/t}">&gt;</option>
								<option value="r" title="{t}Red quotes{/t}">&lt;</option>
								<option value="x" title="{t}Alternating quotes{/t}">&gt;/&lt;</option>
							</select>
						</div>
					</a>
					<a title="{t}Bullet list{/t}" href="#" class="uibutton uib-bul" data-bul="• "><span>•</span></a>
					<a title="{t}Numbered list{/t}" href="#" class="uibutton uib-bul" data-bul="# "><span>#</span></a>
					<a title="{t}Open LaTex editor{/t}" href="#" class="uibutton uib-tx">TeX</a>
					<a title="Cut" href="#" class="uibutton uib-mup" data-mups="[cut]" data-mupe="">Cut</a>
				<!-- </nobr> --></div>
				<textarea name="message" cols="50" rows="4" accesskey="m"></textarea>
			</td>
		</tr>
		{if $board.enablecaptcha eq 1}
		<tr class="captcha-row">
			<td class="postblock"></td>
			<td><nobr class="captcharow">
				<input type="text" name="captcha" placeholder="{t}Captcha{/t}" size="28" accesskey="c" style="vertical-align: middle" autocomplete="off">
				<div class="captchawrap cw-initial yesscript" title="{t}Refresh captcha{/t}">
					<div class="captcha-show msg">{t}Show captcha{/t}</div>
					<img class="captchaimage" valign="middle" border="0" alt="{t}Captcha image{/t}">
					<div class="rotting-indicator"></div>
					<div class="rotten-msg msg">{t}Captcha has expired{t}.</div>
				</div>
				<noscript><iframe class="captchawrap" src="{%KU_BOARDSFOLDER}nojscaptcha.php" frameborder="0" width="150" height="32" style="vertical-align: middle;"></iframe></noscript>
			</nobr></td>
		</tr>
		{/if}
		<input type="hidden" name="legacy-posting" value="1" />
		{if $board.filetypes_allowed}
			<tr class="file-row">
				<td class="postblock">
					<span class="file-count">{t}File{/t}</span>
					<a href="#" onclick="togglePassword(); return false;" style="text-decoration: none;" accesskey="x">&nbsp;</a>
				</td>
				<td>
					<div class="noscript">
						{for embcnt 1 $board.maxfiles}
							<div class="multiembedwrap" data-pos="file-{$embcnt}">
								<input type="file" multiple name="imagefile[]" size="35" accesskey="f" />
								<label class="icon-checkbox-wrap" title="{t}Hide filename{/t}" for="hidename-{$embcnt-1}">
									<input type="checkbox" name="hidename-{$embcnt-1}" id="hidename-{$embcnt-1}" value="1">
									<span class="icon-with-fallback">
										<noscript class="b-icon"><strike>N</strike></noscript>
										<svg class="icon b-icon yesscript">
										  <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#i-hide-name"></use>
										</svg>
									</span>
								</label>
								<label class="icon-checkbox-wrap" title="{t}Spoiler{/t}" for="spoiler-{$embcnt-1}">
									<input type="checkbox" name="spoiler-{$embcnt-1}" id="spoiler-{$embcnt-1}" value="1">
									<span class="icon-with-fallback">
										<noscript class="b-icon">S</noscript>
										<svg class="icon b-icon yesscript">
										  <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#i-spoiler"></use>
										</svg>
									</span>
								</label>
								<button class="remove-file remove-file-legacy icon-wraping-button yesscript" title="{t}Remove file{/t}"><svg class="icon b-icon">
								  <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#i-x"></use>
								</svg></button>
							</div>
							{if $embcnt < $board.maxfiles}
								<input type="checkbox" name="addfile-{$embcnt}" id="addfile-{$embcnt}" class="add-embed">
								<label for="addfile-{$embcnt}" title="{t}Add file{/t}" class="add-embed-button b-icon">+</label>
								<br>
							{/if}
						{/for}
					</div class="noscript">
					<div class="yesscript drop-area">
						<div class="fe-sort-wrapper"></div>
						<button class="add-files" title="{t}Add files{/t}">
							<span class="add-files-text">{t}Add files{/t}...</span>
							<div class="add-files-plus"></div>
						</button>
					</div>
				</td>
			</tr>
		{/if}
		{if $board.embeds_allowed}
			<tr class="embed-row">
				<td class="postblock">
					{t}Embed{/t}
				</td>
				<td>
					{for embcnt 1 $board.maxfiles}
						<div class="multiembedwrap" data-pos="embed-{$embcnt}">
							<input type="text" name="embed[]" placeholder="{t}Embed{/t}" size="28" maxlength="75" accesskey="e" />
							<label class="icon-checkbox-wrap" title="{t}Spoiler{/t}" for="embed-spoiler-{$embcnt-1}">
								<input type="checkbox" name="embed-spoiler-{$embcnt-1}" id="embed-spoiler-{$embcnt-1}" value="1">
								<span class="icon-with-fallback">
									<noscript class="b-icon">S</noscript>
									<svg class="icon b-icon yesscript">
									  <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#i-spoiler"></use>
									</svg>
								</span>
							</label>
						</div>
						{if $embcnt < $board.maxfiles}
							<input type="checkbox" name="addembed-{$embcnt}" id="addembed-{$embcnt}" class="add-embed">
							<label for="addembed-{$embcnt}" title="{t}Add embed{/t}" class="add-embed-button b-icon">+</label>
							<br>
						{/if}
					{/for}
				</td>
			</tr>
		{/if}
			<tr class="password-row">
				<td class="postblock">
					{t}Password{/t}
				</td>
				<td>
					<input class="make-me-readonly" type="password" placeholder="{t}Password{/t}" name="postpassword" size="28" accesskey="p" /><div><span>{t}(for post and file deletion){/t}</span></div>
				</td>
			</tr>
			<tr class="ttl-row">
				<td class="postblock">
					<abbr title="{t}Time to live{/t}">TTL</abbr>
				</td>
				<td>
					<label for="ttl-enable">
						<input type="checkbox" id="ttl-enable" name="ttl-enable" value="1">
						{t}Delete after{/t}
					</label>
					<input type="number" value="0" name="ttl" min="0" style="width: 7ch">&nbsp;{t}hours{/t}
				</td>
			</tr>
			<tr class="noko-row">
				<td class="postblock">
					{t}Go to thread{/t}
				</td>
				<td>
				<label for="gotothread">
					<input id="gotothread" type="checkbox" name="redirecttothread" value="1" style="vertical-align: middle;" checked>&nbsp;({t}return to thread after posting a message{/t})
				</label>
				</td>
			</tr>
			<tr class="fake-password-row" id="passwordbox" style="display: none;"><td></td><td></td></tr>
			<tr class="simplified-send-row">
			  <td colspan="2">
			  	{if $board.filetypes_allowed}
			  	<button class="styled-button s-file" title="{t}Add files{/t}...">
			  	  <svg class="icon next-to-text"><use xlink:href="#i-clip"></use></svg>
			  	</button>
			  	{/if}
			    <button class="styled-button primary" type="submit">
			      <svg class="icon next-to-text"><use xlink:href="#i-send"></use></svg>
			      <span>{if $replythread eq 0}{t}Submit{/t}{else}{t}Post reply{/t}{/if}</span>
			    </button>
			    <button class="styled-button" type="submit" title="Sage" name="sagebtn" value="1">
			    	<noscript>Sage</noscript>
			      <svg class="icon sage-icon"><use xlink:href="#i-send"></use></svg>
			    </button>
			  </td>
			</tr>
			<tr class="blotter-row">
				<td colspan="2" class="blotter">
					<details>
						<summary style="text-align: center;">[<b class="xlink">{t}Info{/t}</b>]</summary>
						<ul class="blotter-entries">
							<li>{t}Supported file types are{/t}: {strip}
							{if $board.filetypes_allowed neq ''}
								{foreach name=files item=filetype from=$board.filetypes_allowed}
									{$filetype|upper}{if $.foreach.files.last}{else}, {/if}
								{/foreach}
							{else}
								{t}None{/t}
							{/if}.{/strip}
							</li>
							<li>{t}Supported embed types are{/t}: {strip}
							{if $board.embeds_allowed neq ''}
								{foreach name=embs item=emb from=$board.embeds_allowed}
									{$emb.name}{if $.foreach.embs.last}{else}, {/if}
								{/foreach}
							{else}
								{t}None{/t}
							{/if}.{/strip}
							</li>
							{if not $board.enablenofile}
							<li>{t}A file or embed ID is required for a new thread.{/t}</li>
							{/if}
							{if %KU_FILESIZE_METHOD eq 'sum'}
							<li>{t}Maximum total size for all files allowed is{/t} {math "round(x/1024)" x=$board.maximagesize} {t}KB{/t}.</li>
							{else}
							<li>{t}Maximum file size allowed is{/t} {math "round(x/1024)" x=$board.maximagesize} {t}KB{/t}.</li>
							{/if}
							<li>{t}Maximum number of files + embeds per post is{/t} {$board.maxfiles}.</li>
							{if $board.enablenofile}
								<li>{t}No file or embed required for a new thread on this board{/t}.</li>
							{else}
								<li>{t}A file or embed ID is required for a new thread.{/t}</li>
							{/if}
							{if $board.duplication}
								<li>{t}File end embed duplication is allowed{/t}.</li>
							{/if}
							{if $board.opmod}
								<li>{t}OP moderation is enabled{/t}.</li>
							{/if}
							{if $board.enablecaptcha eq 2}
							<li>This site is protected by hCaptcha and its
								<a href="https://hcaptcha.com/privacy">Privacy Policy</a> and
								<a href="https://hcaptcha.com/terms">Terms of Service</a> apply.
							</li>
							{/if}
						</ul>
					</details>
				</td>
			</tr>
		</tbody>
	</table>
	{if $board.enablecaptcha eq 2}
	<div class="h-captcha" data-sitekey="{%I0_HCAPTCHA_SITEKEY}" data-size="invisible"></div>
	<script src="https://js.hcaptcha.com/1/api.js&recaptchacompat=false" async defer></script>
	{/if}
	<div class="formsending-overlay"><div class="form-spinner"></div></div>
</form>{if not $isthread}<div class="i0svcel">!i0-pb-end</div>{/if}
<hr />
{if $topads neq ''}
	<div class="content ads">
		<center>
			{$topads}
		</center>
	</div>
	<hr />
{/if}
</div>{* /postarea *}
<script type="text/javascript">set_inputs("postform");</script>