<?php
/*
* This file is part of kusaba.
*
*/

class Parse {
	var $parentid;
	var $id;
	var $boardid;


	function urlcallback($matches) {
		return '<a target="_blank" rel="nofollow" href="'.$matches[1].$matches[2].'">'.$matches[1].urldecode($matches[2]).'</a>';
	}

	function exturlcallback($matches) {
		$text = strtr(urldecode($matches[1]), array('/' => '&#47;'));
		return '<a target="_blank" rel="nofollow" href="'.$matches[2].$matches[3].'">'.$text.'</a>';
	}

	function MakeClickable($txt) {
		$txt = preg_replace_callback('#«([^«»]*)»:(http://|https://|ftp://)([^(\s<|\[)]+(?:\([\w\d]+\)|([^[:punct:]«»\s]|/)))#u',array(&$this, 'exturlcallback'),$txt);
		$txt = preg_replace_callback('#(?<!href=")((?:http:|https:|ftp:)\/\/)([^(\s<|\[)]+(?:\([\w\d]+\)|([^[:punct:]«»\s]|\/)))#u',array(&$this, 'urlcallback'),$txt);
		return $txt;
	}

	function BBCode($string){
	if(!KU_USE_GESHI) {
		$string = preg_replace_callback('`\[code\](.+?)\[/code\]`is', array(&$this, 'code_callback'), $string);
		$string = preg_replace_callback('/```\s+?\R(.+?)\R```/is', array(&$this, 'code_callback'), $string);
	}
	$string = preg_replace_callback('#`(.+?)`#i', array(&$this, 'inline_code_callback'), $string);
	$string = preg_replace_callback('`\[tex\](.+?)\[/tex\]`is', array(&$this, 'latex_callback'), $string);
	$string = preg_replace_callback('`((?:(?:(?:^[\-\*] )(?:[^\r\n]+))[\r\n]*)+)`m', array(&$this, 'bullet_list'), $string);
	$string = preg_replace_callback('`((?:(?:(?:[+\#] )(?:[^\r\n]+))[\r\n]*)?(?:(?:(?:^[+\#] )(?:[^\r\n]+))[\r\n]*)+)`m', array(&$this, 'number_list'), $string);

		$patterns = array(
      '`\*\*(.+?)\*\*`is',
      '`\*(.+?)\*`is',
      '`%%(.+?)%%`is',
      '`\[b\](.+?)\[/b\]`is',
      '`\[i\](.+?)\[/i\]`is',
      '`\[u\](.+?)\[/u\]`is',
      '`\[s\](.+?)\[/s\]`is',
      '`~~(.+?)~~`is',
      '`\[aa\](.+?)\[/aa\]`is',
      '`\[spoiler\](.+?)\[/spoiler\]`is',
      '`\[lination\](.+?)\[/lination\]`is',
      '`\[caps\](.+?)\[/caps\]`is',
      '`&quot;(.+?)&quot;`is',
      '`&gt;(.+?)&lt;`'
      );
    $replaces =  array(
      '<b>\\1</b>',
      '<i>\\1</i>',
      '<span class="spoiler">\\1</span>',
      '<b>\\1</b>',
      '<i>\\1</i>',
      '<span style="border-bottom: 1px solid">\\1</span>',
      '<strike>\\1</strike>',
      '<strike>\\1</strike>',
      '<span style="font-family: Mona,\'MS PGothic\' !important;">\\1</span>',
      '<span class="spoiler">\\1</span>',
      '<table class="lination"><tr><td><img src="/images/lina.png"></td><td><div class="bubble">\\1</div></td></tr></table>',
      '<span style="text-transform: uppercase;">\\1</span>',
      '«\\1»',
      '<span class="unkfunc">&gt;\\1</span>'
      );
		$string = preg_replace($patterns, $replaces , $string);
		return $string;
	}

	function SaysThinks($string) {
		$string = preg_replace_callback('/\[says(?:=(?:(\:)?(.+?)\:?))?\](.+?)\[\/(?:says)\]/is', array(&$this, 'caption_callback'), $string);
		$string = preg_replace_callback('/\[thinks(?:=(?:(\:)?(.+?)\:?))?\](.+?)\[\/(?:thinks)\]/is', array(&$this, 'caption_callback'), $string);
		return $string;
	}

	function caption_callback($matches) {
		$sayer = strtolower($matches[2]);
		$thought_bubble = strtolower($matches[4])=='thinks' ? ' thought-bubble' : '';
		$is_emoji = ($matches[1]==':');
		$content = $matches[3];

		$sayer_exists = false;
		if ($sayer) {
			if ($is_emoji) {
				$src = null;
				foreach(array('.gif','.png') as $extension) {
					if(file_exists(KU_ROOTDIR.I0_SMILEDIR.$sayer.$extension)) {
						$src = KU_WEBPATH.'/'.I0_SMILEDIR.$sayer.$extension;
						$sayer_exists = true;
						break;
					}
				}
			}
			else {
				$src = '/images/sayers/'.$sayer.'.png';
				$sayer_exists = file_exists(KU_ROOTDIR.$src);
			}
		}
		
		$colon = $is_emoji ? '&colon;' : '';
		return ($sayer_exists ? '<table class="caption"><tr><td><img src="'.$src.'" title="'.$colon.$sayer.$colon.'"></td><td>' : '') .
			'<div class="bubble'.$thought_bubble.'">'.$content.'</div>' . 
			($sayer_exists ? '</td></tr></table>' : '');
	}

	function bullet_list($matches) {
		$output = '<ul>';
		$lines = explode(PHP_EOL,$matches[1]);
		foreach($lines as $line) {
			if(strlen($line))
			$output .= '<li>'.substr($line, 2).'</li>';
		}
		$output .= '</ul>';
		return $output;
	}

	function number_list($matches) {
		$output = '<ol>';
		$lines = explode(PHP_EOL,$matches[1]);
		foreach($lines as $line) {
			if(strlen($line))
			$output .= '<li>'.substr($line, 2).'</li>';
		}
		$output .= '</ol>';
		return $output;
	}

	function bydlo_callback($matches) {
		$rn1 = rand(0, 255);	$rn2 = rand(0, 255);	$rn3 = rand(0, 255);
		$return = '<span style="font-weight: bold; text-transform: uppercase; background-color:rgb('
		. $rn1 . ',' . $rn2 . ',' . $rn3 . '); color:rgb('
		. (255 - $rn1) . ',' . (255 - $rn2) . ',' . (255 - $rn3) . ');">'
		. $matches[1] .
		'</span>';

		return $return;
	}

	function code_callback($matches) {
		$tr = array("\t"=>"&#9;", "["=>"&#91;", "]"=>"&#93;", "*"=>"&#42;", "%"=>"&#37;", "/"=>"&#47;", "&quot;"=>"&#34;", "-"=>"&#45;", ":"=>"&#58;", " "=>"&nbsp;", "#"=>"&#35;", "~"=>"&#126;",  "&#039;"=>"'", "&apos;"=>"'", "`"=>'&#96;', "&gt;"=>"&#62;", "&lt;"=>"&#60;" );
		$return = '<pre class="prettyprint">'.  strtr($matches[1],$tr) . '</pre>';
		return $return;
	}

	function inline_code_callback($matches) {
		$tr = array("\t"=>"&#9;", "["=>"&#91;", "]"=>"&#93;", "*"=>"&#42;", "%"=>"&#37;", "/"=>"&#47;", "&quot;"=>"&#34;", "-"=>"&#45;", ":"=>"&#58;", " "=>"&nbsp;", "#"=>"&#35;", "~"=>"&#126;",  "&#039;"=>"'", "&apos;"=>"'", "&gt;"=>"&#62;", "&lt;"=>"&#60;" );
		$return = '<pre class="inline-pp">' . strtr($matches[1],$tr) . '</pre>';
		return $return;
	}

	function Process_geshi($string) {
		$geshi_langs = array("text", "4cs", "6502acme", "6502kickass", "6502tasm", "68000devpac", "abap", "actionscript", "actionscript3", "ada", "aimms", "algol68", "apache", "applescript", "apt_sources", "arm", "asm", "asp", "asymptote", "autoconf", "autohotkey", "autoit", "avisynth", "awk", "bascomavr", "bash", "basic4gl", "batch", "bf", "biblatex", "bibtex", "blitzbasic", "bnf", "boo", "c", "caddcl", "cadlisp", "ceylon", "cfdg", "cfm", "chaiscript", "chapel", "cil", "clojure", "cmake", "cobol", "coffeescript", "cpp-qt", "cpp-winapi", "cpp", "csharp", "css", "cuesheet", "c_loadrunner", "c_mac", "c_winapi", "d", "dart", "dcl", "dcpu16", "dcs", "delphi", "diff", "div", "dos", "dot", "e", "ecmascript", "eiffel", "email", "epc", "erlang", "euphoria", "ezt", "f1", "falcon", "fo", "fortran", "freebasic", "freeswitch", "fsharp", "gambas", "gdb", "genero", "genie", "gettext", "glsl", "gml", "gnuplot", "go", "groovy", "gwbasic", "haskell", "haxe", "hicest", "hq9plus", "html4strict", "html5", "icon", "idl", "ini", "inno", "intercal", "io", "ispfpanel", "j", "java", "java5", "javascript", "jcl", "jquery", "julia", "kixtart", "klonec", "klonecpp", "kotlin", "latex", "lb", "ldif", "lisp", "llvm", "locobasic", "logtalk", "lolcode", "lotusformulas", "lotusscript", "lscript", "lsl2", "lua", "m68k", "magiksf", "make", "mapbasic", "mathematica", "matlab", "mercury", "metapost", "mirc", "mk-61", "mmix", "modula2", "modula3", "mpasm", "mxml", "mysql", "nagios", "netrexx", "newlisp", "nginx", "nimrod", "nsis", "oberon2", "objc", "objeck", "ocaml-brief", "ocaml", "octave", "oobas", "oorexx", "oracle11", "oracle8", "oxygene", "oz", "parasail", "parigp", "pascal", "pcre", "per", "perl", "perl6", "pf", "phix", "php-brief", "php", "pic16", "pike", "pixelbender", "pli", "plsql", "postgresql", "postscript", "povray", "powerbuilder", "powershell", "proftpd", "progress", "prolog", "properties", "providex", "purebasic", "pys60", "python", "q", "qbasic", "qml", "racket", "rails", "rbs", "rebol", "reg", "rexx", "robots", "rpmspec", "rsplus", "ruby", "rust", "sas", "sass", "scala", "scheme", "scilab", "scl", "sdlbasic", "smalltalk", "smarty", "spark", "sparql", "sql", "standardml", "stonescript", "swift", "systemverilog", "tcl", "tclegg", "teraterm", "texgraph", "text", "thinbasic", "tsql", "twig", "typoscript", "unicon", "upc", "urbi", "uscript", "vala", "vb", "vbnet", "vbscript", "vedit", "verilog", "vhdl", "vim", "visualfoxpro", "visualprolog", "whitespace", "whois", "winbatch", "xbasic", "xml", "xojo", "xorg_conf", "xpp", "xyscript", "yaml", "z80", "zxbasic"); //all supported languages
		$langs = implode('|', $geshi_langs);
		$string = preg_replace_callback('`\[code(?:=('.$langs.'))?\](.+?)\[/code\]`is', array(&$this, 'geshi_callback'), $string);
		return preg_replace_callback('/```('.$langs.')?\s+?\R(.+?)\R```/is', array(&$this, 'geshi_callback'), $string);
	}

	function geshi_callback($matches) {
		include_once KU_ROOTDIR . '/lib/geshi.php';
		$lang = $matches[1] ? $matches[1] : 'text';
		$geshi = new GeSHi(html_entity_decode($matches[2], ENT_QUOTES), $lang);
		$geshi->set_header_type(GESHI_HEADER_PRE);
		$tr = array("\t"=>"&#9;", "["=>"&#91;", "]"=>"&#93;", "*"=>"&#42;", "%"=>"&#37;", "&quot;"=>"&#34;", "-"=>"&#45;", ":"=>"&#58;", "# "=>"&#35; ", "~"=>"&#126;",  "&#039;"=>"'", "&apos;"=>"'", "&gt;"=>"&#62;", "&lt;"=>"&#60;", "`"=>'&#96;');
		return '<div class="code_part">'.strtr($geshi->parse_code(), $tr).'</div>';
	}

	function latex_callback($matches) {
	$tr = array( "["=>"&#91;", "]"=>"&#93;", "*"=>"&#42;", "%"=>"&#37;", "/"=>"&#47;", "&quot;"=>"&#34;", "-"=>"&#45;", ":"=>"&#58;");
		$return = '<span lang="latex">'
		. strtr($matches[1],$tr) .
		'</span>';
		return $return;
	}

	function yoba_callback($matches) {

	   $tr = array(
        "нули"=>"nooley","НУЛИ"=>"NOOLEY", "сули"=>"sooley","СУЛИ"=>"SOOLEY",
		"були"=>"booley","БУЛИ"=>"BOOLEY", "w"=>"ш", "W"=>"Ш",
		"очень"=>"oche","ОЧЕНЬ"=>"OCHE",
		"А"=>"A","Б"=>"B","В"=>"V","Г"=>"G",
        "Д"=>"D","Е"=>"E","Ж"=>"J","З"=>"Z","И"=>"I",
        "Й"=>"Y","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N",
        "О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T",
        "У"=>"U","Ф"=>"F","Х"=>"H","Ц"=>"TS","Ч"=>"CH",
        "Ш"=>"SH","Щ"=>"SCH","Ъ"=>"","Ы"=>"I", "ЫЙ"=>"I", "Ь"=>"&#39;",
        "Э"=>"E","Ю"=>"YU","Я"=>"YA","а"=>"a","б"=>"b",
        "в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"j",
        "з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
        "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
        "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
        "ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
        "ы"=>"i", "ый"=>"i", "ь"=>"&#39;","э"=>"e","ю"=>"yu","я"=>"ya"
    );

	$return = '<span style="text-transform: uppercase; font-weight: bold;">'
		. strtr($matches[1],$tr) .
		'</span>';

		return $return;
	}

	function ColoredQuote($buffer) {
		/* Add a \n to keep regular expressions happy */
		if (substr($buffer, -1, 1)!="\n") {
			$buffer .= "\n";
		}

		/* The css for imageboards use 'unkfunc' (???) as the class for quotes */
		$class = 'unkfunc';
		$linechar = "\n";

		$buffer = preg_replace('/^(&gt;[^>](.*))\n/m', '<span class="'.$class.'">\\1</span>' . $linechar, $buffer);

		return $buffer;
	}

	function ClickableQuote($buffer, $board, $parentid, $boardid, $ispage = false) {
		global $thread_board_return;
		$thread_board_return = $board;
		$thread_board_id = $boardid;

		/* Add html for links to posts in the board the post was made */
		$buffer = preg_replace_callback('/&gt;&gt;([0-9]+)/', array(&$this, 'InterthreadQuoteCheck'), $buffer);

		/* Add html for links to posts made in a different board */
		$buffer = preg_replace_callback('/&gt;&gt;\/(\S+)\/([0-9]+)/', array(&$this, 'InterboardQuoteCheck'), $buffer);

		/* Add html for links to posts in the board the post was made */
		$buffer = preg_replace_callback('/##([0-9]+|op|оп)##/i', array(&$this, 'InterthreadProofLabel'), $buffer);

		/* Add html for links to posts made in a different board */
		$buffer = preg_replace_callback('/##\/(\S+)\/([0-9]+)##/', array(&$this, 'InterboardProofLabel'), $buffer);

		return $buffer;
	}

	function InterthreadProofLabel($matches) {
		global $tc_db, $ispage, $thread_board_return, $thread_board_id;

		if (in_array(strtoupper($matches[1]), array('OP', 'ОП'))) 
			$matches[1] = $this->parentid;
		if (is_numeric($matches[1])) {
			$result = $tc_db->GetAll("SELECT `parentid`, `ipmd5` FROM `".KU_DBPREFIX."posts` WHERE `boardid` = " . $this->boardid . " AND `id` = ".$tc_db->qstr($matches[1]));
			if(count($result) > 0) {
				$result = $result[0];
				$proven = ($result['ipmd5'] == $this->ipmd5) ? 'proven' : 'disproven';
				if ($result['parentid'] == 0) {
					$realid = $matches[1];
				} else {
					$realid = $result['parentid'];
				}
				return '<a href="'.KU_BOARDSFOLDER.$thread_board_return.'/res/'.$realid.'.html#'.$matches[1].'" onclick="return highlight(\'' . $matches[1] . '\', true);" class="ref|' . $thread_board_return . '|' .$realid . '|' . $matches[1] . ' prooflabel '.$proven.'">'.$matches[0].'</a>';
			}
			else return $matches[0];
		}
		return $matches[0];
	}

	function InterboardProofLabel($matches) {
		global $tc_db;

		$result = $tc_db->GetAll("SELECT `id`, `type` FROM `".KU_DBPREFIX."boards` WHERE `name` = ".$tc_db->qstr($matches[1])."");
		if ($result[0]["type"] != '') {
			$result2 = $tc_db->GetAll("SELECT `parentid`, `ipmd5` FROM `".KU_DBPREFIX."posts` WHERE `boardid` = " . $result[0]['id'] . " AND `id` = ".$tc_db->qstr($matches[2])."");
			if(count($result2) > 0) {
				$result2 = $result2[0];
				$proven = ($result2['ipmd5'] == $this->ipmd5) ? 'proven' : 'disproven';

				if ($result2['parentid'] == 0) {
					$realid = $matches[2];
				}
				else $realid = $result2['parentid'];

				if ($result[0]["type"] != 1) {
					return '<a href="'.KU_BOARDSFOLDER.$matches[1].'/res/'.$realid.'.html#'.$matches[2].'" class="ref|' . $matches[1] . '|' . $realid . '|' . $matches[2] . ' prooflabel '.$proven.'">'.$matches[0].'</a>';
				} else {
					return '<a href="'.KU_BOARDSFOLDER.$matches[1].'/res/'.$realid.'.html" class="ref|' . $matches[1] . '|' . $realid . '|' . $realid . ' prooflabel '.$proven.'">'.$matches[0].'</a>';
				}
			}
			else return $matches[0];

		}
		return $matches[0];
	}

	function InterthreadQuoteCheck($matches) {
		global $tc_db;

		$result = $tc_db->GetOne("SELECT `parentid` FROM `".KU_DBPREFIX."posts` WHERE `id`=? AND `boardid`=?", array($matches[1], $this->boardid));
		if (!!$result || $result === '0') {
			$realid = ($result === '0') ? $matches[1] : $result;
			$path = $this->boardname.'/res/'.$realid.'.html#'.$matches[1];
		}
		else {
			$realid = $matches[1];
			$path = 'postbynumber.php?b='.$this->boardname.'&p='.$matches[1];
		}
		return '<a href="'.KU_BOARDSFOLDER.$path.'" class="ref|' . $this->boardname . '|' . $realid . '|' . $matches[1] . '">'.$matches[0].'</a>';
	}

	function InterboardQuoteCheck($matches) {
		global $tc_db;

		$result = $tc_db->GetOne("SELECT `parentid` FROM `".KU_DBPREFIX."posts` WHERE `id`=? AND `boardid`=(SELECT `id` FROM `boards` WHERE `name`=?)", array($matches[2], $matches[1]));
		if (!!$result || $result === '0') {
			$realid = ($result == 0) ? $matches[2] : $result;
			$path = $matches[1].'/res/'.$realid.'.html#'.$matches[2];
		}
		else {
			$realid = $matches[2];
			$path = 'postbynumber.php?b='.$matches[1].'&p='.$matches[2];
		}
		return '<a href="'.KU_BOARDSFOLDER.$path.'" class="ref|' . $matches[1] . '|' . $realid . '|' . $matches[2] . '">'.$matches[0].'</a>';
	}

	function Wordfilter($buffer, $board) {
		global $tc_db;

		$query = "SELECT * FROM `".KU_DBPREFIX."wordfilter`";
		$results = $tc_db->GetAll($query);
		foreach($results AS $line) {
			$array_boards = explode('|', $line['boards']);
			if (in_array($board, $array_boards)) {
				$replace_word = $line['word'];
				$replace_replacedby = $line['replacedby'];

				$buffer = ($line['regex'] == 1) ? preg_replace($replace_word, $replace_replacedby, $buffer) : str_ireplace($replace_word, $replace_replacedby, $buffer);
			}
		}

		return $buffer;
	}

	function CheckNotEmpty($buffer) {
		$buffer_temp = str_replace("\n", "", $buffer);
		$buffer_temp = str_replace("<br>", "", $buffer_temp);
		$buffer_temp = str_replace("<br/>", "", $buffer_temp);
		$buffer_temp = str_replace("<br />", "", $buffer_temp);

		$buffer_temp = str_replace(" ", "", $buffer_temp);

		if ($buffer_temp=="") {
			return "";
		} else {
			return $buffer;
		}
	}
	function CutWord($txt, $where) {
		$txt_split_primary = preg_split('/\n/', $txt);
		$txt_processed = '';
		$usemb = (function_exists('mb_substr') && function_exists('mb_strlen')) ? true : false;

		foreach ($txt_split_primary as $txt_split) {
			$txt_split_secondary = preg_split('/ /', $txt_split);

			foreach ($txt_split_secondary as $txt_segment) {
				$segment_length = ($usemb) ? mb_strlen($txt_segment) : strlen($txt_segment);
				while ($segment_length > $where) {
					if ($usemb) {
						$txt_processed .= mb_substr($txt_segment, 0, $where) . "\n";
						$txt_segment = mb_substr($txt_segment, $where);

						$segment_length = mb_strlen($txt_segment);
					} else {
						$txt_processed .= substr($txt_segment, 0, $where) . "\n";
						$txt_segment = substr($txt_segment, $where);

						$segment_length = strlen($txt_segment);
					}
				}

				$txt_processed .= $txt_segment . ' ';
			}

			$txt_processed = ($usemb) ? mb_substr($txt_processed, 0, -1) : substr($txt_processed, 0, -1);
			$txt_processed .= "\n";
		}

		return $txt_processed;
	}

	function Smileys($string){
		$string = preg_replace_callback('`:([0-9a-z_]{3,20}):`is', array(&$this, 'smiley_callback'), $string);
		return $string;
	}

	function smiley_callback($matches) {
		$src = FALSE;
		foreach(array('.gif','.png') as $extension) {
			if(file_exists(KU_ROOTDIR.I0_SMILEDIR.$matches[1].$extension))	 {
				$src = KU_WEBPATH.'/'.I0_SMILEDIR.$matches[1].$extension;
				break;
			}
		}
		$return = ($src) ? '<img title="&colon;'.$matches[1].'&colon;" class="emoji" src="'.$src.'">': ':'.$matches[1].':';
		return $return;
	}

	function dice($matches) {
		$expr = $matches[0];
		$times = $matches[1];
		$sides = $matches[2];
		$pm = $matches[3][0];
		if($pm) $add=(int)substr($matches[3], 1);
		$res = 0;
		$i = 0;
		$desc = '[';
		while($i < $times) {
			if($i>0) $desc .= ', ';
			$rollresult = rand(1, $sides);
			$desc .= $rollresult;
			$res += $rollresult;
			$i++;
		}
		$desc .= ']';
		if($pm) {
			if($pm == '+') $res += $add;
			else $res -= $add;
			$desc .= ' '.$pm.' '.$add;
		}
		$ret .= '<span class="dice" title="'.$desc.'">'.$expr.' &rarr; <b>'.$res.'</b></span>';
		return $ret;
	}

	function ParsePost($message, $board, $parentid, $boardid, $ispage = false, $useragent, $dice, $ipmd5) {
		$this->parentid = $parentid;
		$this->boardid = $boardid;
		$this->boardname = $board;
		$this->ipmd5 = $ipmd5;
		$message = trim($message);

		$cut_split = preg_split('/\s*\[cut(?:=?[^\[\]]+?)?\]\s*/m', $message);
		$before_cut = $cut_split[0];
		$after_cut = implode("\n[cut]\n", array_slice($cut_split, 1));

		preg_match('/\[cut(?:=([^\[\]\n\r]+)?)?\]/m', $message, $matches);
		if ($matches && $matches[1])
			$summary = htmlspecialchars($matches[1]);

		$message = $this->ParsePostFragment($before_cut, $board, $parentid, $boardid, $ispage = false, $useragent, $dice, $ipmd5);
		if ($after_cut) {
			if (!$summary)
				$summary = _gettext('Read more').'...';
			$message .= '<details><summary class="read-more"><span class="xlink">'.$summary.'</span></summary>'.$this->ParsePostFragment($after_cut, $board, $parentid, $boardid, $ispage = false, $useragent, $dice, $ipmd5).'</details>';
		}

		return $message;
	}

	function ParsePostFragment($message, $board, $parentid, $boardid, $ispage = false, $useragent, $dice, $ipmd5) {
		if(KU_CUTPOSTS) {
			$message = $this->CutWord($message, (KU_LINELENGTH / 15));
		}
		$message = htmlspecialchars($message, ENT_QUOTES);
		if(KU_USE_GESHI) {
			$message = $this->Process_geshi($message);
		}
		$message = $this->BBCode($message);

		if (I0_SAYERS_ENABLED) {
			$message = $this->SaysThinks($message);
		}

		$message = $this->ClickableQuote($message, $board, $parentid, $boardid, $ispage);
		$message = $this->ColoredQuote($message);

		$message = str_replace("\n", '<br />', $message);
		$message = preg_replace('#(<br(?: \/)?>\s*){3,}#i', '<br /><br />', $message);

		$message = $this->CheckNotEmpty($message);
		$message = $this->Wordfilter($message, $board);

		if (KU_MAKELINKS) {
			$message = $this->MakeClickable($message);
		}
		$message = preg_replace('# - #is', '&nbsp;— ', $message);

		if($useragent) $message = preg_replace('`##[uU]seragent##`im', '<span style="color:blue">'.$useragent.'</span>', $message);

		if($dice) $message = preg_replace_callback('`##(\d{1})d(\d{1,3})([+-]\d{1,3})?##`m', array(&$this, 'dice'), $message);

		if (I0_SMILES_ENABLED) $message = $this->Smileys($message);

		return $message;
	}
}
?>
