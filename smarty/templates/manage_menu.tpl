<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>{t}Manage Boards{/t}</title>
	<link rel="stylesheet" type="text/css" href="{$smarty.const.KU_WEBPATH}/css/manage_page.css?v={$smarty.const.KU_CSSVER}" />
	<link rel="stylesheet" type="text/css" href="{$smarty.const.KU_WEBPATH}/css/manage_menu.css?v={$smarty.const.KU_CSSVER}" />
<link rel="shortcut icon" href="{$smarty.const.KU_WEBPATH}/favicon.ico" />
{literal}
<script type="text/javascript">
function toggle(button, area) {
	var tog=document.getElementById(area);
	if(tog.style.display)	{
		tog.style.display="";
	} else {
		tog.style.display="none";
	}
	createCookie('nav_show_'+area, tog.style.display?'0':'1', 365);
}
function createCookie(name,value,days) {
  if (days) {
    var date = new Date();
    date.setTime(date.getTime()+(days*24*60*60*1000));
    var expires = "; expires="+date.toGMTString();
  }
  else var expires = "";
  document.cookie = name+"="+value+expires+"; path=/";
}
</script>
{/literal}
<base target="manage_main" />
</head>
<body>
<div id="navbar-container" style="left: 0px; ">
{if $logo20 eq '0'}
<h1 id="title">{t}Manage Boards{/t}</h1>
{else}
<a href="/?p=2.0">
<img src="images/2-logo.png" style="max-width:100%"></img>
</a>
{/if}
<ul>
	{$links}
</ul>
</div>
{literal}
<script>
	document.querySelectorAll('#navbar-container div').forEach(sect => {
		if (!!~document.cookie.indexOf('nav_show_' + sect.id + '=0'))
			sect.style.display = 'none'
	})
</script>
{/literal}
</body>
</html>
