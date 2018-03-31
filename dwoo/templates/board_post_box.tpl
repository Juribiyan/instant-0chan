<div class="postarea"><p></p>
<a id="postbox"></a>
<form name="postform" id="postform" action="{%KU_CGIPATH}/board.php" method="post" enctype="multipart/form-data"
{if $board.enablecaptcha eq 1}
	onsubmit="return checkcaptcha('postform');"
{/if}>
<input type="hidden" name="ffdata_savedon" value="" />
<input type="hidden" name="board" value="{$board.name}" />
<input type="hidden" name="replythread" value="<!sm_threadid>" />
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
		<td><nobr>
			<input type="text" onfocus="return Captcha.refreshOnce()" name="captcha" placeholder="{t}Captcha{/t}" size="28" accesskey="c" style="vertical-align: middle">
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
			<div class="markupbtns"><nobr style="font-size: 2px;">
				<a title="{t}Bold{/t}" href="#" class="uibutton uib-mup" data-mups="**" data-mupe="**"><b>{t}B{/t}</b></a>
				<a title="{t}Italic{/t}" href="#" class="uibutton uib-mup" data-mups="*" data-mupe="*"><i>{t}I{/t}</i></a>
				<a title="{t}Undeline{/t}" href="#" class="uibutton uib-mup" data-mups="[u]" data-mupe="[/u]"><u>{t}U{/t}</u></a>
				<a title="{t}Strike{/t}" href="#" class="uibutton uib-mup" data-mups="~~" data-mupe="~~"><s>{t}S{/t}</s></a>
				<a title="{t}Spoiler{/t}" href="#" class="uibutton uib-mup uib-spoiler" data-mups="%%" data-mupe="%%">{t}Sp{/t}</a>
				<a title="{t}Code{/t}" style="position: relative;" href="#" class="uibutton {if %KU_USE_GESHI}opt-exp{else}uib-mup{/if}" data-mups="[code]" data-mupe="[/code]" data-imups="`" data-imupe="`"><span class="uib-code">();{if %KU_USE_GESHI} ▼{/if}</span>{if %KU_USE_GESHI}<div class="expandee code_markup"><select size="8" class="code_markup_select" ><option value="text">Простой текст</option><option value="actionscript">ActionScript</option><option value="actionscript3">ActionScript 3</option><option value="ada">Ada</option><option value="applescript">AppleScript</option><option value="asm">ASM</option><option value="asp">ASP</option><option value="avisynth">AviSynth</option><option value="bash">Bash</option><option value="bf">Brainfuck</option><option value="blitzbasic">BlitzBasic</option><option value="c">C</option><option value="cobol">COBOL</option><option value="cpp">C++</option><option value="csharp">C#</option><option value="css">CSS</option><option value="d">D</option><option value="delphi">Delphi</option><option value="diff">Diff</option><option value="dos">DOS</option><option value="fortran">Fortran</option><option value="haskell">Haskell</option><option value="html4strict">HTML</option><option value="ini">INI</option><option value="java">Java</option><option value="javascript">Javascript</option><option value="latex">LaTeX</option><option value="lisp">Lisp</option><option value="lua">Lua</option><option value="make">GNU make</option><option value="matlab">Matlab M</option><option value="mirc">mIRC Scripting</option><option value="mpasm">Microchip Assembler</option><option value="mxml">MXML</option><option value="mysql">MySQL</option><option value="objc">Objective-C</option><option value="ocaml">OCaml</option><option value="pascal">Pascal</option><option value="perl">Perl</option><option value="php">PHP</option><option value="pic16">PIC16</option><option value="powershell">posh</option><option value="prolog">Prolog</option><option value="python">Python</option><option value="qbasic">QBasic/QuickBASIC</option><option value="rails">Rails</option><option value="ruby">Ruby</option><option value="scala">Scala</option><option value="smalltalk">Smalltalk</option><option value="smarty">Smarty</option><option value="sql">SQL</option><option value="tcl">TCL</option><option value="tsql">T-SQL</option><option value="vb">Visual Basic</option><option value="vbnet">vb.net</option><option value="xml">XML</option></select></div>{/if}</a>
				<a title="{t}Greenquoting{/t}" href="#" class="uibutton uib-bul" data-bul="&gt;" data-imups="&gt;" data-imupe="&lt;"><span class="uib-imply">&gt;</span></a>
				<a title="{t}Bullet list{/t}" href="#" class="uibutton uib-bul" data-bul="* "><span>•</span></a>
				<a title="{t}Numbered list{/t}" href="#" class="uibutton uib-bul" data-bul="# "><span>#</span></a>
				<a title="{t}Open LaTex editor{/t}" href="#" class="uibutton uib-tx">TeX</a>
			</nobr></div>
			<textarea name="message" cols="48" rows="4" accesskey="m"></textarea>
		</td>
	</tr>
	{* if $board.enablecaptcha eq 1}
		<tr>
			<td class="postblock">{t}Captcha{/t}</td>
			<td colspan="2">{$recaptcha}</td>
		</tr>
	{/if *}
	{if $board.uploadtype eq 0 || $board.uploadtype eq 1}
		<tr>
			<td class="postblock">
				{t}File{/t}<a href="#" onclick="togglePassword(); return false;" style="text-decoration: none;" accesskey="x">&nbsp;</a>
			</td>
			<td>
			<input type="file" name="imagefile" size="35" accesskey="f" />
			{if $replythread eq 0 && $board.enablenofile eq 1 }
				[<input type="checkbox" name="nofile" id="nofile" accesskey="q" /><label for="nofile"> {t}No File{/t}</label>]
			{/if}
			</td>
		</tr>
	{/if}
	{if ($board.uploadtype eq 1 || $board.uploadtype eq 2) && $board.embeds_allowed neq ''}
		<tr>
			<td class="postblock">
				{t}Embed{/t}
			</td>
			<td>
				<input type="text" name="embed" placeholder="{t}Embed{/t}" size="28" maxlength="75" accesskey="e" />&nbsp;<select name="embedtype">
				{foreach name=embed from=$embeds item=embed}
					{if in_array($embed.filetype,explode(',' $board.embeds_allowed))}
						<option value="{$embed.name|lower}">{$embed.name}</option>
					{/if}
				{/foreach}
				</select>
			</td>
		</tr>
	{/if}
		<tr>
			<td class="postblock">
				{t}Password{/t}
			</td>
			<td>
				<input readonly onfocus="this.removeAttribute('readonly');" type="password" name="postpassword" size="8" accesskey="p" />&nbsp;{t}(for post and file deletion){/t}
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
					<li>{t}Supported file types are{/t}:
					{if $board.filetypes_allowed neq ''}
						{foreach name=files item=filetype from=$board.filetypes_allowed}
							{$filetype.filetype|upper}{if $.foreach.files.last}{else}, {/if}
						{/foreach}
					{else}
						{t}None{/t}
					{/if}
					</li>
					<li>{t}Maximum file size allowed is{/t} {math "round(x/1024)" x=$board.maximagesize} KB.</li>
					</li>
				</ul>
			<script type="text/javascript"><!--
				if (getCookie('ku_showblotter') != '1') {
					hideblotter();
				}
				--></script>
			</td>
		</tr>
	</tbody>

</table>
</form>

<form name="postform" id="postclone" action="{%KU_CGIPATH}/board.php" method="post" enctype="multipart/form-data" class="reflinkpreview content-background qreplyform" style="display:none"
{if $board.enablecaptcha eq 1}
	onsubmit="return ffdata.save();"
{/if}>
<input type="hidden" name="ffdata_savedon" value="" />
<input type="hidden" name="board" value="{$board.name}" />
<input type="hidden" name="replythread" value="<!sm_threadid>" />
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
				<label for="disable_name_clone">
          <input id="disable_name_clone" type="checkbox" name="disable_name" value="1" style="vertical-align: -2px;">&nbsp;{t}Anonymously{/t}
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
		<td><nobr>
			<input type="text" onfocus="return Captcha.refreshOnce()" name="captcha" placeholder="{t}Captcha{/t}" size="28" accesskey="c" style="vertical-align: middle">
			<script>document.write('<div class="captchawrap cw-initial" title="{t}Refresh captcha{/t}"><div class="captcha-show msg">{t}Show captcha{/t}</div><img class="captchaimage" valign="middle" border="0" alt="Captcha image"><div class="rotting-indicator"></div><div class="rotten-msg msg">{t}Captcha has expired{t}.</div></div>')</script>
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
			<div class="markupbtns"><nobr style="font-size: 2px;">
				<a title="{t}Bold{/t}" href="#" class="uibutton uib-mup" data-mups="**" data-mupe="**"><b>{t}B{/t}</b></a>
				<a title="{t}Italic{/t}" href="#" class="uibutton uib-mup" data-mups="*" data-mupe="*"><i>{t}I{/t}</i></a>
				<a title="{t}Undeline{/t}" href="#" class="uibutton uib-mup" data-mups="[u]" data-mupe="[/u]"><u>{t}U{/t}</u></a>
				<a title="{t}Strike{/t}" href="#" class="uibutton uib-mup" data-mups="[s]" data-mupe="[/s]"><s>{t}S{/t}</s></a>
				<a title="{t}Spoiler{/t}" href="#" class="uibutton uib-mup uib-spoiler" data-mups="%%" data-mupe="%%">{t}Sp{/t}</a>
				<a title="{t}Code{/t}" style="position: relative;" href="#" class="uibutton {if %KU_USE_GESHI}opt-exp{else}uib-mup{/if}" data-mups="[code]" data-mupe="[/code]" data-imups="`" data-imupe="`"><span class="uib-code">();{if %KU_USE_GESHI} ▼{/if}</span>{if %KU_USE_GESHI}<div class="expandee code_markup"><select size="8" class="code_markup_select"><option value="text">Простой текст</option><option value="actionscript">ActionScript</option><option value="actionscript3">ActionScript 3</option><option value="ada">Ada</option><option value="applescript">AppleScript</option><option value="asm">ASM</option><option value="asp">ASP</option><option value="avisynth">AviSynth</option><option value="bash">Bash</option><option value="bf">Brainfuck</option><option value="blitzbasic">BlitzBasic</option><option value="c">C</option><option value="cobol">COBOL</option><option value="cpp">C++</option><option value="csharp">C#</option><option value="css">CSS</option><option value="d">D</option><option value="delphi">Delphi</option><option value="diff">Diff</option><option value="dos">DOS</option><option value="fortran">Fortran</option><option value="haskell">Haskell</option><option value="html4strict">HTML</option><option value="ini">INI</option><option value="java">Java</option><option value="javascript">Javascript</option><option value="latex">LaTeX</option><option value="lisp">Lisp</option><option value="lua">Lua</option><option value="make">GNU make</option><option value="matlab">Matlab M</option><option value="mirc">mIRC Scripting</option><option value="mpasm">Microchip Assembler</option><option value="mxml">MXML</option><option value="mysql">MySQL</option><option value="objc">Objective-C</option><option value="ocaml">OCaml</option><option value="pascal">Pascal</option><option value="perl">Perl</option><option value="php">PHP</option><option value="pic16">PIC16</option><option value="powershell">posh</option><option value="prolog">Prolog</option><option value="python">Python</option><option value="qbasic">QBasic/QuickBASIC</option><option value="rails">Rails</option><option value="ruby">Ruby</option><option value="scala">Scala</option><option value="smalltalk">Smalltalk</option><option value="smarty">Smarty</option><option value="sql">SQL</option><option value="tcl">TCL</option><option value="tsql">T-SQL</option><option value="vb">Visual Basic</option><option value="vbnet">vb.net</option><option value="xml">XML</option></select></div>{/if}</a>
				<a title="{t}Greenquoting{/t}" href="#" class="uibutton uib-bul" data-bul=">"><span class="uib-imply">&gt;</span></a>
				<a title="{t}Bullet list{/t}" href="#" class="uibutton uib-bul" data-bul="* "><span>•</span></a>
				<a title="{t}Numbered list{/t}" href="#" class="uibutton uib-bul" data-bul="# "><span>#</span></a>
				<a title="{t}Open LaTex editor{/t}" href="#" class="uibutton uib-tx">TeX</a>
			</nobr></div>
			<textarea name="message" cols="48" rows="4" accesskey="m"></textarea>
		</td>
	</tr>
	{* if $board.enablecaptcha eq 1}
		<tr>
			<td class="postblock">{t}Captcha{/t}</td>
			<td colspan="2">{$recaptcha}</td>
		</tr>
	{/if *}
	{if $board.uploadtype eq 0 || $board.uploadtype eq 1}
		<tr>
			<td class="postblock">
				{t}File{/t}<a href="#" onclick="togglePassword(); return false;" style="text-decoration: none;" accesskey="x">&nbsp;</a>
			</td>
			<td>
			<input type="file" name="imagefile" size="35" accesskey="f" />
			{if $replythread eq 0 && $board.enablenofile eq 1 }
				[<input type="checkbox" name="nofile" id="nofile" accesskey="q" /><label for="nofile"> {t}No File{/t}</label>]
			{/if}
			</td>
		</tr>
	{/if}
	{if ($board.uploadtype eq 1 || $board.uploadtype eq 2) && $board.embeds_allowed neq ''}
		<tr>
			<td class="postblock">
				{t}Embed{/t}
			</td>
			<td>
				<input type="text" name="embed" placeholder="{t}Embed{/t}" size="28" maxlength="75" accesskey="e" />&nbsp;<select name="embedtype">
				{foreach name=embed from=$embeds item=embed}
					{if in_array($embed.filetype,explode(',' $board.embeds_allowed))}
						<option value="{$embed.name|lower}">{$embed.name}</option>
					{/if}
				{/foreach}
				</select>
			</td>
		</tr>
	{/if}
		<tr>
			<td class="postblock">
				{t}Password{/t}
			</td>
			<td>
				<input readonly onfocus="this.removeAttribute('readonly');" type="password" name="postpassword" size="8" accesskey="p" />&nbsp;{t}(for post and file deletion){/t}
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
	</tbody>

</table>
</form>

<hr />
{if $topads neq ''}
	<div class="content ads">
		<center> 
			{$topads}
		</center>
	</div>
	<hr />
{/if}
</div>
<script type="text/javascript">
set_inputs("postform");
set_inputs("postclone");
</script>
