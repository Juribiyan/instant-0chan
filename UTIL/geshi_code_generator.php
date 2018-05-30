<?php
/*
	This script lets you choose what languages will be displayed in post box dropdown list.
	It generates code for /dwoo/templates/board_post_box.tpl (.code_markup_select) and also parse.class.php ($geshi_langs)

	Usage: copy to root directory, select languages you want, press 'GET CODE', see generated code in the console.
*/
require 'config.php';
include_once KU_ROOTDIR . '/lib/geshi.php';
$geshi = new GeSHi();
?>
<style>
	label {
		display: block;
		padding: 4px;
		margin: 4px;
		background: #eee;
		border-radius: 6px;
	}
</style>
<?php
foreach($geshi->get_supported_languages(true) as $lang=>$desc) {
	echo "
		<label for='$lang' title='$lang'>
			<input type='checkbox' value='$lang' id='$lang'>
			$desc
		</label>
	";
}
?>
<button id="process">GET CODE</button>
<script>
	document.querySelector('#process').onclick = () => {
		console.log('board_post_box.tpl:\n',
			[].map.call(document.querySelectorAll('input[type=checkbox]:checked'), label => 
				`<option value="${label.value}">${label.parentElement.innerText.trim()}</option>`
			).join(''),
			'\n\nparse.class.php:\n',
			[].map.call(document.querySelectorAll('input[type=checkbox]'), label => `"${label.value}"`).join(', ')
		)
		document.querySelector('#process').innerText = "SEE CONSOLE"
	}
	
</script>