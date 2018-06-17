<div class="postarea"><p></p>
<a id="postbox"></a>
<form name="postform" id="postform" action="{%KU_CGIPATH}/board.php" method="post" enctype="multipart/form-data">
<!-- formbody -->
</form>
<form name="postform" id="postclone" action="{%KU_CGIPATH}/board.php" method="post" enctype="multipart/form-data"
class="reflinkpreview content-background qreplyform" style="display:none">
<!-- formbody -->
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
