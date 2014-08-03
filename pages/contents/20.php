<div id="boards">
<style>
.i20switcher {
	display: inline-block;
	font-size: 0px;
	border: 1px solid #59c44d;
	border-radius: 8px;
}
.i20switcher .switch, .i2-sprite {
	display: inline-block;
	width: 43px;
	height: 30px;
	cursor: pointer;
}
.switch {
	background: none;
}
.switch.i2s-selected {
	background: #59c44d;
}
.i2-sprite {
	background-image: url(../images/icons.png);
}
.i2s-cloud {background-position: -43px 0px } .i2s-selected .i2s-cloud {background-position: -43px -30px }
.i2s-list {background-position: 0px 0px } .i2s-selected .i2s-list {background-position: 0px -30px }
.i2s-az {background-position: -86px 0px } .i2s-selected .i2s-az {background-position: -86px -30px }
.i2s-comment {background-position: -129px 0px } .i2s-selected .i2s-comment {background-position: -129px -30px }

.switch-left {
	border-radius: 6px 0 0 6px;
}
.switch-right {
	border-radius: 0 6px 6px 0 ;
}

.is-left {float: left}	.is-right {float: right}
#inputs20 {  
	text-align: center;
	border-top: 1px #AAA solid;
	text-align: center;
	margin: 0px auto;
	width: auto;
	max-width: 636px;
}

.cloud-view {
	padding: 0 16%;
}

.list-view {
	text-align: left;
	max-width: 636px;
margin: 0px auto;
}

.submenu20 {
	line-height: 60px;
	font-size: 25px;
}
#boardselect { 
	background: #818181;
border: 1px solid #D8D8D8;
border-radius: 9px;
text-align: center;
}
#boardselect:focus {
	background: #D8D8D8;
	outline: none;
}
.postcount20 { opacity: 0.7;}
.link20 {padding: 0 0.2em;}
</style>
    <center>
    <a href="/?p=boards" title="Доски">Доски</a> | <b>2.0chan<sup>beta</sup></b> | <a href="/?p=faq" title="FAQ">FAQ</a>
    </center>
    <div id="container20">
    	<div id="inputs20">
    		<div class="submenu20">
    			<a href="/?p=register" onclick="toggleregister();return false;" id="register">$регистрация</a> |
    			<a href="../manage.php" id="register">Админка</a>
    		</div><br />
    		<div class="i20switcher is-left">
    			<div title="Все в куче" data-action="cloud" class="switch switch-left i2s-selected"><div class="i2-sprite i2s-cloud"></div></div>
    			<div title="Список" data-action="list" class="switch switch-right"><div class="i2-sprite i2s-list"></div></div>
    		</div>
    		<input type="text" id="boardselect" placeholder="Фильтр" />
    		<div class="i20switcher is-right">
    			<div title="Алфавитный список" data-action="name" class="switch  switch-left i2s-selected"><div class="i2-sprite i2s-az"></div></div>
    			<div title="По числу постов за сутки" data-action="postcount" class="switch switch-right"><div class="i2-sprite i2s-comment"></div></div>
    		</div>
    	</div><br />
    	<div id="data20"></div>
    </div>
<script>
$(document).ready(function() {
    console.log('rdy!');
    cloud20.getboards();
    $('#boardselect').on('input', function() {
        cloud20.filter($(this).val());
    });
    $('.i20switcher .switch').click(function() {
        if($(this).hasClass('i2s-selected')) return;
        $(this).parents('.i20switcher').find('.switch').removeClass('i2s-selected');
        $(this).addClass('i2s-selected');
        switch($(this).data('action')) {
            case 'cloud': cloud20.displaytype = 'cloud'; break
            case 'list': cloud20.displaytype = 'list'; break
            case 'name': cloud20.order = 'name'; break
            case 'postcount': cloud20.order = 'postcount'; break
            default: return
        }
        cloud20.filter($('#boardselect').val());
    })
});
</script>
</div>