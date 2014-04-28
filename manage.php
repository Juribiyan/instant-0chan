<?php
/*
 * This file is part of kusaba.
 *
 * kusaba is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * kusaba is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * kusaba; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
/**
 * Manage panel frameset
 *
 * Tells the browser to load the menu and main page
 *
 * @package kusaba
 */
$preconfig_db_unnecessary = true;
require 'config.php';
header("Expires: Mon, 1 Jan 2030 05:00:00 GMT");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<script src="/lib/javascript/jquery.min.js"></script>
<title>Manage Boards</title>
<link rel="shortcut icon" href="<?php echo KU_WEBPATH . '/'; ?>favicon.ico" />
<style type="text/css">
body, html {
	width: 100%;
	height: 100%;
	margin: 0;
	padding: 0;
	overflow: auto;
}
#menu {
	position: absolute;
	left: 0px;
	top: 0px;
	margin: 0;
	padding: 0;
	border: 0px;
	height: 100%;
	width: 15%;
}
#manage_main {
	position: absolute;
	left: 15%;
	top: 0px;
	width: 85%;
	height: 100%;
	border: 0px;
}
body {
	background: -moz-linear-gradient(top, #bebebe 0px, #ffffff 253px); 
background: -webkit-linear-gradient(top, #bebebe 0px,#ffffff 253px); 
background: -o-linear-gradient(top, #bebebe 0px,#ffffff 253px);
background: -ms-linear-gradient(top, #bebebe 0px,#ffffff 253px);
}
</style>
</head>
<body>
<iframe src="manage_menu.php" name="menu" id="menu">
</iframe>
<iframe src="manage_page.php" name="manage_main" id="manage_main">
</iframe>
<script type="text/javascript">
	var $frames = {
		fired: false,
		readyset: function() {
			this.fired = true;
			this.over = false;
			this.menu.find('.magic-link').on('mouseenter', function(ev) {
				if(halp.hasOwnProperty($(this).data('help'))) {
					$frames.over = true;
					var helpEntry = halp[$(this).data('help')];
					var offset = ev.pageY - $frames.menu.scrollTop();
					var newhtml = '<h2>'+helpEntry.title+'</h2><br /><div class="halp">'+helpEntry.contents+'</div>';
					var helpWindow = $frames.page.find('#help');
					helpWindow.html(newhtml);
					offset = offset - (helpWindow.height() / 2);
					helpWindow.css({'top': offset+'px'});
					if($frames.off) helpWindow.fadeTo('fast',1);
				}
			})
			.on('mouseleave', function() {
				$frames.over = false;
				setTimeout(function() {
					if(!$frames.over) {
						$frames.page.find('#help').fadeTo('fast',0).promise().done(function() {
							$frames.off=true;
						});
					}
				}, 600);
			});
		}
	};
	$('#menu').load(function() {
		$frames.menu = $(frames['menu'].document);
		if(typeof $frames.page !== 'undefined' && !$frames.fired) $frames.readyset();
	});
	$('#manage_main').load(function() {
		$frames.page = $(frames['manage_main'].document);
		if(typeof $frames.menu !== 'undefined' && !$frames.fired) {
			$frames.readyset();
		}
		$frames.page.find('.toedit').click(function() {
			$form = $(this).parents('form');
			$form.find('.normal-buttons, .oldbanner, .custom-link-a').hide();
			$form.find('.editconfirm, .newbanner, .custom-link-entry').show();
			$form.parent().addClass('editing');
		});
		$frames.page.find('.todelete').click(function() {
			$form = $(this).parents('form');
			$form.find('.normal-buttons').hide();
			$form.find('.deleteconfirm').show();
			$form.parent().addClass('deleting');
		});
		$frames.page.find('.unedit, .undelete').click(function() {
			$form = $(this).parents('form');
			$form.find('.transformed, .newbanner, .custom-link-entry').hide();
			$form.find('.normal-buttons, .oldbanner, .custom-link-a').show();
			$form.parent().removeClass('deleting editing');
		});
	});
	var halp = {
		'showpwd': {
			title: 'Пароль для отправки',
			contents: 'Используется для модпостинга. <br><b>PROTIP:</b> Кликните справа от слова "Файл" чтобы активировать мод-панель.'
		},
		'styles': {
			title: 'Менеджер стилей',
			contents: 'Загружайте и редактируйте пользовательские CSS.'
		},
		'spam': {
			title: 'Спамлисты',
			contents: 'Создавайте списки слов, за которые будет наступать получасовой бан.'
		},
		'posting_rates': {
			title: 'Здесь',
			contents: 'Какие-то таблицы.'
		},
		'statistics': {
			title: 'А здесь',
			contents: 'Няшные графики.'
		},
		'adddelboard': {
			title: 'Добавить/Удалить доски',
			contents: 'Вы можете одновременно иметь до 5 досок.'
		},
		'boardopts': {
			title: 'Опции доски',
			contents: 'Изменяйте название доски, типы файлов, стандартное имя, стиль по умолчанию, добавляйте кантриболы и прочее'
		},
		'stickypost': {
			title: 'Прикрепленные треды',
			contents: 'Прикрепленные треды будут висеть вверху на нулевой станице доски.<br>Треды можно прикреплять прямо на доске по нажатию соответствующей кнопки.'
		},
		'lockpost': {
			title: 'Заблокированные треды',
			contents: 'Запретить оставлять посты в определенном треде.<br>Треды можно блокировать прямо на доске по нажатию соответствующей кнопки.'
		},
		'banners': {
			title: 'Баннеры',
			contents: 'Лучший способ пропиарить свою доску!'
		},
	};

</script>
</body>
</html>