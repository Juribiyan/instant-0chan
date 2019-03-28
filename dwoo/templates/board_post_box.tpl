<input type="hidden" name="ffdata_savedon" value="" />
<input type="hidden" name="board" value="{$board.name}" />
<input type="hidden" name="replythread" value="<!sm_threadid>" />
<input type="hidden" name="makepost" value="1" />
{if $board.maximagesize > 0}
	<input type="hidden" name="MAX_FILE_SIZE" value="{$board.maximagesize}" />
{/if}
<input type="text" name="email" size="28" maxlength="{%KU_MAXEMAILLENGTH}" value="" style="display: none;" />
<table class="postform">
	<tbody>
	{if $board.forcedanon neq 1}
		<tr>
			<td class="postblock">
				{t}Name{/t}</td>
			<td>
				<input type="text" name="name" placeholder="{t}Name{/t}#{t}tripcode{/t}" size="28" maxlength="{%KU_MAXNAMELENGTH}" accesskey="n"/>
				<label for="disable_name">
          <input id="disable_name" type="checkbox" name="disable_name" value="1" style="vertical-align: -2px;">&nbsp;{t}Anonymously{/t}
        </label>
			</td>
		</tr>
	{/if}
	<tr>
		<td class="postblock">
			Sage</td>
		<td>
			<label for="sage">
 				<input id="sage" type="checkbox" name="em" value="sage" style="vertical-align: middle;">({t}do not bump this thread{/t})
 			</label>
		</td>
	</tr>
	{if $board.enablecaptcha eq 1}
	<tr>
		<td class="postblock"></td>
		<td><nobr class="captcharow">
			<input type="text" onfocus="return Captcha.refreshOnce()" name="captcha" placeholder="{t}Captcha{/t}" size="28" accesskey="c" style="vertical-align: middle" autocomplete="off">
			<script>document.write('<div class="captchawrap cw-initial" title="{t}Refresh captcha{/t}"><div class="captcha-show msg">{t}Show captcha{/t}</div><img class="captchaimage" valign="middle" border="0" alt="Captcha image"><div class="rotting-indicator"></div><div class="rotten-msg msg">{t}Captcha has expired{t}.</div></div>')</script>
			<noscript><iframe class="captchawrap" src="{%KU_BOARDSFOLDER}nojscaptcha.php" frameborder="0" width="150" height="32" style="vertical-align: middle;"></iframe></noscript>
		</nobr></td>
	</tr>
	{/if}
	<tr>
		<td class="postblock">
			{t}Subject{/t}
		</td>
		<td class="subject-submit">
			{strip}<input type="text" name="subject" placeholder="{t}Subject{/t}" size="35" maxlength="{%KU_MAXSUBJLENGTH}" accesskey="s" />&nbsp;<br class="onsmall-show"><input type="submit" value="
			{if %KU_QUICKREPLY && $replythread eq 0}
				{t}Submit{/t}" accesskey="z" />&nbsp;<small class="posttypeindicator">({t}new thread{/t})</small>
			{elseif %KU_QUICKREPLY && $replythread neq 0}
				{t}Reply{/t}" accesskey="z" />&nbsp;<small class="posttypeindicator">({t}reply to{/t} <!sm_threadid>)</small>
			{else}
				{t}Submit{/t}" accesskey="z" />
			{/if}{/strip}
		</td>
	</tr>
	<tr>
		<td class="postblock">
			{t}Message{/t}
		</td>
		<td>
			<div class="markupbtns yesscript"><nobr style="font-size: 2px;">
				<a title="{t}Bold{/t}" href="#" class="uibutton uib-mup" data-mups="**" data-mupe="**"><b>{t}B{/t}</b></a>
				<a title="{t}Italic{/t}" href="#" class="uibutton uib-mup" data-mups="*" data-mupe="*"><i>{t}I{/t}</i></a>
				<a title="{t}Undeline{/t}" href="#" class="uibutton uib-mup" data-mups="[u]" data-mupe="[/u]"><u>{t}U{/t}</u></a>
				<a title="{t}Strike{/t}" href="#" class="uibutton uib-mup" data-mups="~~" data-mupe="~~"><s>{t}S{/t}</s></a>
				<a title="{t}Spoiler{/t}" href="#" class="uibutton uib-mup uib-spoiler" data-mups="%%" data-mupe="%%">{t}Sp{/t}</a>
				<a title="{t}Code{/t}" style="position: relative;" href="#" class="uibutton {if %KU_USE_GESHI}opt-exp{else}uib-mup{/if}" data-mups="[code]" data-mupe="[/code]" data-imups="`" data-imupe="`"><span class="uib-code">();{if %KU_USE_GESHI} ▼{/if}</span>{if %KU_USE_GESHI}<div class="expandee code_markup"><select size="8" class="code_markup_select" ><option value="text">Простой текст</option><option value="actionscript">ActionScript</option><option value="actionscript3">ActionScript 3</option><option value="apache">Apache configuration</option><option value="asm">ASM</option><option value="bash">Bash</option><option value="batch">Windows Batch file</option><option value="bf">Brainfuck</option><option value="c">C</option><option value="clojure">Clojure</option><option value="coffeescript">CoffeeScript</option><option value="cpp">C++</option><option value="csharp">C#</option><option value="css">CSS</option><option value="d">D</option><option value="dart">Dart</option><option value="delphi">Delphi</option><option value="erlang">Erlang</option><option value="fsharp">F#</option><option value="glsl">glSlang</option><option value="go">Go</option><option value="groovy">Groovy</option><option value="haskell">Haskell</option><option value="haxe">Haxe</option><option value="html5">HTML5</option><option value="ini">INI</option><option value="io">Io</option><option value="java">Java</option><option value="javascript">Javascript</option><option value="kotlin">Kotlin</option><option value="latex">LaTeX</option><option value="lisp">Lisp</option><option value="lolcode">LOLcode</option><option value="lua">Lua</option><option value="make">GNU make</option><option value="mathematica">Mathematica</option><option value="matlab">Matlab M</option><option value="nginx">nginx</option><option value="objc">Objective-C</option><option value="ocaml">OCaml</option><option value="pcre">PCRE</option><option value="perl">Perl</option><option value="php">PHP</option><option value="python">Python</option><option value="racket">Racket</option><option value="rails">Rails</option><option value="ruby">Ruby</option><option value="rust">Rust</option><option value="sass">Sass</option><option value="scala">Scala</option><option value="scheme">Scheme</option><option value="smalltalk">Smalltalk</option><option value="smarty">Smarty</option><option value="sql">SQL</option><option value="systemverilog">SystemVerilog</option><option value="vb">Visual Basic</option><option value="vbnet">vb.net</option><option value="vbscript">VBScript</option><option value="verilog">Verilog</option><option value="vhdl">VHDL</option><option value="whitespace">Whitespace</option><option value="xml">XML</option><option value="yaml">YAML</option></select></div>{/if}</a>
				<a title="{t}Greenquoting{/t}" href="#" class="uibutton uib-bul" data-bul="&gt;" data-imups="&gt;" data-imupe="&lt;"><span class="uib-imply">&gt;</span></a>
				<a title="{t}Bullet list{/t}" href="#" class="uibutton uib-bul" data-bul="* "><span>•</span></a>
				<a title="{t}Numbered list{/t}" href="#" class="uibutton uib-bul" data-bul="# "><span>#</span></a>
				<a title="{t}Open LaTex editor{/t}" href="#" class="uibutton uib-tx">TeX</a>
				<a title="Cut" href="#" class="uibutton uib-mup" data-mups="[cut]" data-mupe="">Cut</a>
			</nobr></div>
			<textarea name="message" cols="50" rows="4" accesskey="m"></textarea>
		</td>
	</tr>
	{* if $board.enablecaptcha eq 1}
		<tr>
			<td class="postblock">{t}Captcha{/t}</td>
			<td colspan="2">{$recaptcha}</td>
		</tr>
	{/if *}

	{* <noscript> *}
	<input type="hidden" name="legacy-posting" value="1" />
	{if sizeof($board.filetypes_allowed)}
		<tr>
			<td class="postblock">
				{t}File{/t}<a href="#" onclick="togglePassword(); return false;" style="text-decoration: none;" accesskey="x">&nbsp;</a>
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
						{* if $replythread eq 0 && $board.enablenofile eq 1 }
							[<input type="checkbox" name="nofile" id="nofile" accesskey="q" /><label for="nofile"> {t}No File{/t}</label>]
						{/if *}
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
	{if sizeof($board.embeds_allowed)}
		<tr>
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
	{* </noscript> *}
		<tr>
			<td class="postblock">
				{t}Password{/t}
			</td>
			<td>
				<input class="make-me-readonly" type="password" name="postpassword" size="8" accesskey="p" />&nbsp;{t}(for post and file deletion){/t}
			</td>
		</tr>
		<tr>
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
		<tr>
			<td class="postblock">
				{t}Go to thread{/t}
			</td>
			<td>
			<label for="gotothread">
				<input id="gotothread" type="checkbox" name="redirecttothread" value="1" style="vertical-align: middle;">&nbsp;({t}return to thread after posting a message{/t})
			</label>
			</td>
		</tr>
		<tr id="passwordbox" style="display: none;"><td></td><td></td></tr>

		<tr>
			<td colspan="2" class="blotter">
				<div class="blotterhead">[<a href="#" onclick="toggleblotter();return false;" class="xlink"><b>{t}Info{/t}</b></a>{if $board.enablecatalog eq 1}  | <a href="{%KU_BOARDSFOLDER}{$board.name}/catalog.html"><b>{t}Catalog{/t}</b></a>{/if}]</div>
				<ul style="margin-left: 0; margin-top: 0; margin-bottom: 0; padding-left: 0;" class="blotter-entries">
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
				</ul>
			<script>
				if (getCookie('ku_showblotter') != '1') {
					hideblotter();
				}
			</script>
			</td>
		</tr>
	</tbody>
</table>
<div class="formsending-overlay"><div class="form-spinner"></div></div>
