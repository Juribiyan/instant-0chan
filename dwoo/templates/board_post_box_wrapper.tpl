<div class="postarea">
<a id="postbox"></a>
<form name="postform" id="postform" action="{%KU_CGIPATH}/board.php" method="post" enctype="multipart/form-data" class="main-reply-form">
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
<script type="text/javascript">set_inputs("postform");</script>