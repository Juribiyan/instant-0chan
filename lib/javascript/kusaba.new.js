var style_cookie;
var style_cookie_txt;
var style_cookie_site;
var kumod_set=false;
var ispage;
var is_entering=false;
var injectDestination = "img_global.css";

var _messages = {
    en: {
        noLocalStorage: "Your browser does not support LocalStorage",
        loading: "Загрузка",
        oops: "Something went wrong...",
        blankResponse: "blank response",
        watchlistAdd: "Тред добавлен в список избранных.",
        expandingThread: "Expanding thread...",
        newThread: "new thread",
        NewThread: "New thread",
        replyTo: "reply to",
        cancel: "Cancel",
        update: "Update", 
        updatingCounts: "Updating...",
        couldntFetch: "Cold not fetch this post",
        noNewPosts: "No new posts",
        replies: "Replies",
        settings_fxEnabled: "Animation effects",
        settings_showReplies: "Show replies inside posts",
        settings_sfwMode: "NSFW mode",
        settings_expandImgFull: "Expand images to full size",
        deletePost: "Delete post", 
        deleteAndBan: "Delete post and ban poster",
        enterCaptcha: "Please enter captcha.",
        selectText: "Select some text",
        dcls: "Double click to show source",
        watchOn: "Watch on",
        captcharot: "Captcha has rotted",
        threadUpdationAutomatically: "Tread is being updated automatically.",
        stopFuckingDolls: "<b>Отключите AJAX-отправку постов и AJAX-обновление треда.</b><br />(Кликните, чтобы закрыть)",
        delete: "Delete",
        delandban: "Delete and ban",
        ban: "Ban",
        stickthread: "Stick thread",
        unstickthread: "Unstick thread",
        lockthread: "Lock thread",
        unlockthread: "Unlock thread",
        returnDesktop: "Switch to desktop interface",
        returnTouch: "Switch to touch interface",
        impliedTouch: "Your device is recognized as touch device.",
        impliedDesktop: "Your device is recognized as desktop device.",
        forceDesktop: "Force Desktop interface",
        okay: "Okay",
        captchalang: "Captcha language",
        reply: "Reply",
        imageDownscaledBy: "Image was downscaled by",
        videoDownscaledBy: "Video was downscaled by",
        toFit: "to feet screen"
    },
    ru: {
        noLocalStorage: "localStorage не поддерживается браузером",
        loading: "Загрузка",
        oops: "Что-то пошло не так...",
        blankResponse: "пустой ответ",
        watchlistAdd: "Тред добавлен в список избранных.",
        watchlistRemove: "Тред удален из списка избранных.",
        expandingThread: "Разворачиваем тред...",
        newThread: "новый тред",
        NewThread: "Создать тред",
        replyTo: "ответ на",
        cancel: "Отмена",
        update: "Обновить", 
        updatingCounts: "Ищем новые посты...",
        couldntFetch: "Не удалось загрузить этот пост",
        noNewPosts: "Нет новых постов",
        replies: "Ответы",
        settings_fxEnabled: "Анимированные эффекты",
        settings_showReplies: "Показывать ответы внутри поста",
        settings_sfwMode: "Мамка в комнате",
        settings_expandImgFull: "Разворачивать картинки до исходного размера",
        deletePost: "Удалить пост", 
        deleteAndBan: "Удалить пост и забанить постера",
        enterCaptcha: "Пожалуйста, введите капчу.",
        selectText: "Текст не выделен",
        dcls: "Double click to show source",
        watchOn: "Смотреть на",
        odc: "javascript:LatexIT.replaceWithSrc(this);",  
        captcharot: "Капча протухла",
        threadUpdationAutomatically: "Тред обновляется автоматически",
        stopFuckingDolls: "<b>Отключите AJAX-отправку постов и AJAX-обновление треда.</b><br />(Кликните, чтобы закрыть)",
        delete: "Удалить",
        delandban: "Удалить и забанить",
        ban: "Забанить",
        stickthread: "Прикрепить тред",
        unstickthread: "Отлепить тред",
        lockthread: "Закрыть тред",
        unlockthread: "Открыть тред",
        returnDesktop: "Switch to desktop interface",
        returnTouch: "Switch to touch interface",
        impliedTouch: "Your device is recognized as touch device.",
        impliedDesktop: "Your device is recognized as desktop device.",
        forceDesktop: "Force Desktop interface",
        okay: "Ясно",
        captchalang: "Язык капчи",
        reply: "Ответить",
        imageDownscaledBy: "Картинка ужата на",
        videoDownscaledBy: "Видео ужато на",
        toFit: "по размеру окна"
    }
}

var _ = (typeof locale !== 'undefined' && _messages.hasOwnProperty(locale)) ? _messages[locale] : _messages.ru;

function trace() {
    if (!console.log) return;
    
    var f = arguments.callee.caller;
    var path = arguments[0];
    if (path == '') path += "trace()";
    
    while (f != null) {
        var re = /function ([^\(]+)/;
        var fname = re.exec(f.toString());
        if (fname == null) fname = ''; else fname = fname[1];
        var args = [];
        for (var i = 0; i < f.arguments.length; i++) args.push(f.arguments[i]);
        fname += "(" + args.join(', ') + ")"; 
        path += ' <- ' + fname;
        f = f.caller;
    }
    console.log(path);
}

/* IE/Opera fix, because they need to go learn a book on how to use indexOf with arrays */
if (!Array.prototype.indexOf) {
  Array.prototype.indexOf = function(elt /*, from*/) {
    var len = this.length;

    var from = Number(arguments[1]) || 0;
    from = (from < 0)
         ? Math.ceil(from)
         : Math.floor(from);
    if (from < 0)
      from += len;

    for (; from < len; from++) {
      if (from in this &&
          this[from] === elt)
        return from;
    }
    return -1;
  };
}
    
/* Utf8 strings de-/encoder */
var Utf8 = {
    // public method for url encoding
    encode : function (string) {
        string = string.replace(/\r\n/g,"\n");
        var utftext = "";
        for (var n = 0; n < string.length; n++) {
            var c = string.charCodeAt(n);
            if (c < 128) {
                utftext += String.fromCharCode(c);
            }
            else if((c > 127) && (c < 2048)) {
                utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            }
            else {
                utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }
        }
        return utftext;
    },
    // public method for url decoding
    decode : function (utftext) {
        var string = "";
        var i = 0;
        var c = c1 = c2 = 0;
        while ( i < utftext.length ) {
            c = utftext.charCodeAt(i);
            if (c < 128) {
                string += String.fromCharCode(c);
                i++;
            }
            else if((c > 191) && (c < 224)) {
                c2 = utftext.charCodeAt(i+1);
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                i += 2;
            }
            else {
                c2 = utftext.charCodeAt(i+1);
                c3 = utftext.charCodeAt(i+2);
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            }
        }
        return string;
    }
}

function Cookie(name) {
    if (arguments.length == 1) {
        with(document.cookie) {
            var regexp=new RegExp("(^|;\\s+)"+name+"=(.*?)(;|$)");
            var hit=regexp.exec(document.cookie);
            if(hit&&hit.length>2) return Utf8.decode(unescape(replaceAll(hit[2],'+','%20')));
            else return '';
        }
    } else {
        var value = arguments[1];
        var days = arguments[2];
        if(days) {
            var date=new Date();
            date.setTime(date.getTime()+(days*24*60*60*1000));
            var expires="; expires="+date.toGMTString();
        } else expires="";
        document.cookie=name+"="+value+expires+"; path=/";
    }
}
   

function replaceAll(str, from, to) {
    var idx = str.indexOf( from );
    while ( idx > -1 ) {
        str = str.replace( from, to );
        idx = str.indexOf( from );
    }
    return str;
}


function insert(text) {
    var textarea = ($('#postclone').length && $('#postclone').css('display') !== 'none') ? document.forms.postclone.message : document.forms.postform.message;
    if(textarea) {
        if(textarea.createTextRange && textarea.caretPos) { // IE 
            var caretPos=textarea.caretPos;
            caretPos.text=caretPos.text.charAt(caretPos.text.length-1)==" "?text+" ":text;
        } else if(textarea.setSelectionRange) { // Firefox 
            var start=textarea.selectionStart;
            var end=textarea.selectionEnd;
            textarea.value=textarea.value.substr(0,start)+text+textarea.value.substr(end);
            textarea.setSelectionRange(start+text.length,start+text.length);
        } else {
            textarea.value+=text+" ";
        }
        textarea.focus();
    }
    if($('#postclone').css('display') !== 'none') return false;
}

function markup($target, start, end) {
    element = $target.find('textarea').get(0);
    if (document.selection) {
        element.focus();
        sel = document.selection.createRange();
        sel.text = start + sel.text + end;
    } else if (element.selectionStart || element.selectionStart == '0') {
        element.focus();
        var startPos = element.selectionStart;
        var endPos = element.selectionEnd;
        element.value = element.value.substring(0, startPos) + start + element.value.substring(startPos, endPos) + end + element.value.substring(endPos, element.value.length);
    } else {
        element.value += start + end;
    }
}

function bullets($target, bullet) {
    var $area = $target.find('textarea');
    var selected = $area.val().substring($area[0].selectionStart, $area[0].selectionEnd).split('\n');
    var newtxt = "";
    for(var i=0; i<selected.length; i++) {
        newtxt += (bullet + selected[i]);
        if(i < (selected.length - 1)) newtxt += '\n';
    }
    $area.val(
        $area.val().substring(0, $area[0].selectionStart) 
        + newtxt + 
        $area.val().substring($area[0].selectionEnd)
    );  
} 
    
function quote(b, a) { 
    var v = eval("document." + a + ".message");
    v.value += ">>" + b + "\n";
    v.focus();
}

function checkhighlight() {
    var match;
    if(match=/#i([0-9]+)/.exec(document.location.toString()))
        if(!document.forms.postform.message.value)
            insert(">>"+match[1]);
    if(match=/#([0-9]+)/.exec(document.location.toString()))
        highlight(match[1]);
}

function highlight(post, checknopage) {
    if (checknopage && ispage)  return;
    $('.highlight').removeClass('highlight').addClass('reply');
    $("#reply" + post).removeClass('reply').addClass('highlight');
    var match = /^([^#]*)/.exec(document.location.toString());
    document.location = match[1] + "#" + post;
}    
    
function get_password(name) {
    var pass = getCookie(name);
    if(pass) return pass;

    var chars="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    var pass='';

    for(var i=0;i<8;i++) {
        var rnd = Math.floor(Math.random()*chars.length);
        pass += chars.substring(rnd, rnd+1);
    }
    Cookie(name, pass, 365);
    return(pass);
}

function togglePassword() {
    var passwordbox_html = $("#passwordbox").html().toLowerCase();
    var newhtml = '<td></td><td></td>';
    if (passwordbox_html == newhtml) {
        var newhtml = '<td class="postblock">Mod</td><td><input type="text" name="modpassword" size="28" maxlength="75">&nbsp;<acronym title="Display staff status (Mod/Admin)">D</acronym>:&nbsp;<input type="checkbox" name="displaystaffstatus" checked>&nbsp;<acronym title="Lock">L</acronym>:&nbsp;<input type="checkbox" name="lockonpost">&nbsp;&nbsp;<acronym title="Sticky">S</acronym>:&nbsp;<input type="checkbox" name="stickyonpost">&nbsp;&nbsp;<acronym title="Raw HTML">RH</acronym>:&nbsp;<input type="checkbox" name="rawhtml">&nbsp;&nbsp;<acronym title="Name">N</acronym>:&nbsp;<input type="checkbox" name="usestaffname"></td>';
    }
    $("#passwordbox").html(newhtml);
    return false;
}

/* used for textboards only, deleted, src in clean */
function toggleOptions(D,C,B){ trace('deprecated!') }

// proxied functions
function getCookie(name)                {   return Cookie(name)                     }  
function set_cookie(name,value,days)    {   return Cookie(name,value,days)          }       

var Styles = {
    all: [], titles: [],
    init: function() {
        iter(document.getElementsByTagName("link"), function(link) {
            if(link.getAttribute("rel").indexOf("style")!=-1 && link.getAttribute("title")) {
                Styles.all.push(link);
                Styles.titles.push(link.getAttribute("title"));
                if(link.getAttribute("rel").indexOf("alternate")===-1) {
                    Styles.default = link.getAttribute("title");
                }
                if(link.hasAttribute("data-custom")) {
                    Styles.custom = link.getAttribute("title");
                }
            }
        });
        this.current = this.default;
        var customBypass = getCookie('bypasscustom');
        this.customBypass = (customBypass.length && typeof this_board_dir !== 'undefined' && in_array(this_board_dir, customBypass.split('|'))) ? true : false;
    },
    decide: function() {
        this.init();        
        if(this.hasOwnProperty('custom') && !this.customBypass) 
            return this.setCustom();
        var sc = getCookie(style_cookie);
        if(sc && in_array(sc, this.titles))
            this.setStyle(sc);
        else {
            this.setDefault();
            set_cookie("kustyle_site",this.default,365);
            set_cookie("kustyle",this.default,365); 
        }
    },
    change: function(stylename) {
        if(!in_array(stylename, this.titles) || this.current === stylename) return;
        this.setStyle(stylename);
        if(this.hasOwnProperty('custom') && this.custom === stylename) {
            this.removeBypass();
        }
        else {
            if(this.hasOwnProperty('custom'))
                this.addBypass();
            set_cookie("kustyle_site",stylename,365);
            set_cookie("kustyle",stylename,365);    
        }
    },
    removeBypass: function() {
        if(!this.customBypass || typeof this_board_dir === 'undefined') return;
        this.customBypass = false;
        var oldcookie = getCookie('bypasscustom').split('|'), newcookie = [];
        iter(oldcookie, function(brd) {
            if(brd !== this_board_dir) newcookie.push(brd);
        });
        newcookie = newcookie.length ? newcookie.join('|') : '';
        set_cookie("bypasscustom",newcookie,365);
    },
    addBypass: function() {
        if(this.customBypass || typeof this_board_dir === 'undefined') return;
        this.customBypass = true;
        var cookie = getCookie('bypasscustom').split('|');
        if(!in_array(this_board_dir, cookie)) {
            cookie.push(this_board_dir);
            set_cookie("bypasscustom",cookie.join('|'),365);
        }
    },
    setDefault: function() {
        if(this.hasOwnProperty('default') && this.current !== this.default)
            this.setStyle(this.default);
    },
    setCustom: function() {
        if(this.hasOwnProperty('custom'))
            this.setStyle(this.custom);
    },
    setStyle: function(stylename) {
        if(!in_array(stylename, this.titles)) return;
        iter(this.all, function(sheet) {
            sheet.disabled=true;    // Hello IE
            if(sheet.getAttribute("title") === stylename)
                sheet.disabled=false;
        });
        this.current = stylename;
    }
}

if(style_cookie) Styles.decide();

function delandbanlinks($scope, force) {
    if(typeof force === 'undefined') force = false;
    if ((!kumod_set && !force) || typeof $scope === 'undefined') return;
    $scope.find('span[id^=dnb]').each(function(index,element) {
        dnbinfo = $(this).attr('id').split('-');

        var newhtml = '&nbsp;<span class="btngroup">' + '<a href="' + ku_cgipath + '/manage_page.php?action=delposts&boarddir=' + dnbinfo[1] + '&del';
        if (dnbinfo[3] == 'y') {newhtml += 'thread';} else {newhtml += 'post';}
        newhtml += 'id=' + dnbinfo[2] + '" title="'+_.delete+'" onclick="return confirm(\''+_.deletePost+'?\');"><img src="'+ku_boardspath+'/css/icons/blank.gif" class="delete spritebtn sb-l"><\/a>';
        newhtml += '<a href="' + ku_cgipath + '/manage_page.php?action=delposts&boarddir=' + dnbinfo[1] + '&del';if (dnbinfo[3] == 'y') {newhtml += 'thread';} else {newhtml += 'post';}
        newhtml +='id=' + dnbinfo[2] + '&postid=' + dnbinfo[2] + '" title="'+_.delandban+'" onclick="return confirm(\''+_.deletePost+'?\');"><img src="'+ku_boardspath+'/css/icons/blank.gif" class="dandb spritebtn sb-c"><\/a>';
        newhtml +='<a href="' + ku_cgipath + '/manage_page.php?action=bans&banboard=' + dnbinfo[1] + '&banpost=' + dnbinfo[2] + '" title="'+_.ban+'"><img src="'+ku_boardspath+'/css/icons/blank.gif" class="ban spritebtn sb-r"><\/a></span>&nbsp;';

        if (dnbinfo[3] == 'y') {
            newhtml += '<span class="btngroup"><a href="' + ku_cgipath + '/manage_page.php?action=stickypost&board=' + dnbinfo[1] + '&postid=' + dnbinfo[2] + '" title="'+_.stickthread+'" ><img src="'+ku_boardspath+'/css/icons/blank.gif" class="stick spritebtn sb-l"><\/a><a href="' + ku_cgipath + '/manage_page.php?action=unstickypost&board=' + dnbinfo[1] + '&postid=' + dnbinfo[2] + '" title="'+_.unstickthread+'" ><img src="'+ku_boardspath+'/css/icons/blank.gif" class="unstick spritebtn sb-r"><\/a></span>&nbsp;';
            newhtml += '<span class="btngroup"><a href="' + ku_cgipath + '/manage_page.php?action=lockpost&board=' + dnbinfo[1] + '&postid=' + dnbinfo[2] + '" title="'+_.lockthread+'" ><img src="'+ku_boardspath+'/css/icons/blank.gif" class="lock spritebtn sb-l"><\/a><a href="' + ku_cgipath + '/manage_page.php?action=unlockpost&board=' + dnbinfo[1] + '&postid=' + dnbinfo[2] + '" title="'+_.unlockthread+'" ><img src="'+ku_boardspath+'/css/icons/blank.gif" class="unlock spritebtn sb-r"><\/a></span>';
        }

        $(this).html(newhtml);
    });
}

var HiddenThreads = {
    list: function() {
        if (localStorage == null) {
            trace(_.noLocalStorage);
            return [];
        }
        var list = localStorage.getItem('hiddenThreads.' + this_board_dir);
        if (list == null) return [];
        return list.split(',');
    },
    isHidden: function(threadid) { return HiddenThreads.list().indexOf(threadid) != -1 },
    hide: function(threadid) { 
        if (localStorage == null) alert(_.noLocalStorage);
        else {
            var newlist = HiddenThreads.list();
            newlist.push(threadid.toString());
            localStorage.setItem('hiddenThreads.' + this_board_dir, newlist.join(','));
        }
    },
    unhide: function(threadid) { 
        if (localStorage == null) alert(_.noLocalStorage);
        else {
            var list = HiddenThreads.list();
            var i = list.indexOf(threadid.toString());
            if (i == -1) return;
            var newlist = list.slice(0,i);
            newlist = newlist.concat(list.slice(i+1));
            localStorage.setItem('hiddenThreads.' + this_board_dir, newlist.join(','));
        }
    }
}
    
function togglethread(threadid) {
    if (HiddenThreads.isHidden(threadid)) {
        $('#unhidethread' + threadid + this_board_dir).slideUp();
        $('#thread' + threadid + this_board_dir).slideDown();
        HiddenThreads.unhide(threadid);
    } else {
        $('#unhidethread' + threadid + this_board_dir).slideDown();
        $('#thread' + threadid + this_board_dir).slideUp();
        HiddenThreads.hide(threadid);
    }
    return false;
}
  
function toggleblotter(save) {
    $('li.blotterentry').each(function(index,element) {
        if($(this).is(":hidden")) {
            $(this).slideDown();
            if (save) Cookie('ku_showblotter', '1', 365);
        } else {
            $(this).slideUp();
            if (save) Cookie('ku_showblotter', '0', 365);
        }
    });
}

function expandthread(threadid, board) {
    if ($('#replies' + threadid + board).get() != '') {
        $('#replies' + threadid + board).prepend("<img src=\"/images/loading.gif\" align=\"middle\" /> " + _.expandingThread + '<br />');
        $.ajax({
            url: ku_boardspath + '/expand.php?board=' + board + '&threadid=' + threadid,
            success: function(data) {
                var rep = $('#replies' + threadid + board);
                if (data) {
                    rep.html(data);
                    Settings.sfwMode(false);
                    rep.hide().fadeIn();
                } else {
                    $('#replies' + threadid + board).prepend(_.oops + " ("+_.blankResponse+")");
                }
            },
            error: function(xhr, status) {
                $('#replies' + threadid + board).prepend(_.oops + " (" + status + ")");
            }
        });
    }
    return false;
}   

function getnewposts(threadid) {
    if(typeof threadid === 'undefined') threadid = $('input[name=replythread]').val();
    var lastpost = ($('.postnode').last().find('td[id^=reply]').attr('id'));
    lastpost = lastpost ? lastpost.substring(5) : threadid;
    $.ajax({
        url: ku_boardspath + '/expand.php?after=' + lastpost + '&board=' + this_board_dir + '&threadid=' + threadid,
        success: function(data) {
            if (data) {
                var $target = $('.postnode').length ? $('.postnode').last() : $('.postmessage');
                $target.after('<div class="newposts">' + data + '</div>');
                Settings.sfwMode(false);
                showreplies();
                $('.newposts').last().hide().fadeIn();
            } else {
                popupMessage(_.noNewPosts);
            }
            $('#newposts_get').show();
            $('#newposts_load').hide();
        },
        error: function(xhr, status) {
            popupMessage(_.oops + " (" + status + ")");
            $('#newposts_get').show();
            $('#newposts_load').hide();
        }
    });
    $('#newposts_get').hide();
    $('#newposts_load').show();
    return false;
}

function showLinks(ev) {
    var href = $(this).attr('href');
    $('#directLink').val(ku_boardspath+href);
    $('#quoteLink').val('>>'+href.split('/res/')[0]+'/'+href.split('#')[1]);
    $('#viewlink').css({
        top: $(this).offset().top + $(this).height(),
        left: $(this).offset().left
    }).fadeIn('fast');
    return false;
}  

var goingExternal = false;
function quickreply(ev) {
    var externalBoard = $(this).data('boardname');
    if(externalBoard === this_board_dir) externalBoard = false;
    var parent = $(this).data('parent'), current = $(this).data('postnum') || parent;
    var preferUnpinned = (localStorage['pinPreference'] === 'unpinned');
    var appearsNew = ($('#postclone').css('display') === 'none');
    $('#postclone').show();
    if(isTouchDevice) {
        $('#postclone').css({
            bottom: '0px',
            left: '0px',
            position: 'fixed',
            opacity: 1
        });
    }
    else {
        if(preferUnpinned) {
            $('#postclone').css({
                top: $(this).offset().top + $(this).height(),
                left: Math.round(($(window).width() / 2) - ($('#postclone').width() / 2)),
                position: 'absolute'
            });
        }
        else if(appearsNew) {
            $('#postclone').css({
                top: $(this).offset().top + $(this).height() - $(document).scrollTop(),
                left: Math.round(($(window).width() / 2) - ($('#postclone').width() / 2)),
                position: 'fixed'
            });
        }
    }
    $('#postclone input[name="replythread"]').val(parent);
    if(externalBoard) {
        $('#postclone input[name="board"]').val(externalBoard);
        $('#postclone #posttypeindicator').html('<a href="../'+externalBoard+'/res/'+parent+'.html?i#'+current+'"> &gt;&gt;/'+externalBoard+'/'+parent+'</a>');
    }
    else {
        $('#postclone #posttypeindicator').html('<a class="xlink" href="#'+current+'"> &gt;&gt;'+parent+'</a>');
    }
    insert('>>'+current+'\n');
    return false;
} 
    
function getwatchedthreads(threadid, board) {
    if ($('#watchedthreadlist').get()!='') {
        $('#watchedthreadlist').html(_.loading + '...');
        $.ajax({
            url: ku_boardspath + '/threadwatch.php?board=' + board + '&threadid=' + threadid,
            success: function(data) {
                $('#watchedthreadlist').html(data || (_.oops + " ("+_.blankResponse+")"));
            },
            error: function(xhr, status) {
                $('#watchedthreadlist').html(_.oops + " (" + status + ")");
            }
        });
    }
}  

function popupMessage(text, delay) {
    if (delay == null) delay = 1000;
    if ($('#popupMessage').get() == '') {
        $('body').children().last().after('<div id="popupMessage" class="reply"></div>');
        $('#popupMessage').css('position', 'fixed');
        $('#popupMessage').css('top', '50px');
        $('#popupMessage').css('padding', '20px');
        $('#popupMessage').css('width', '40%');
        $('#popupMessage').css('left', '30%');
        $('#popupMessage').css('text-align', 'center');
        $('#popupMessage').css('-webkit-box-shadow', '#666 0px 0px 10px');
        $('#popupMessage').hide();
    }
    $('#popupMessage').html("<span class=\"postername\">" + text + "</span>");
    $('#popupMessage').fadeIn(150).delay(delay).fadeOut(300);
}


function addtowatchedthreads(threadid, board) {
    if ($('#watchedthreadlist').get()!='') {
        $.ajax({
            url: ku_boardspath + '/threadwatch.php?do=addthread&board=' + board + '&threadid=' + threadid,
            success: function(data) {
                popupMessage(_.watchlistAdd)
                getwatchedthreads('0', board);
            },
            error: function(xhr, status) {
                popupMessage(_.oops + " (" + status + ")");
            }
        });
    }
}
  
function removefromwatchedthreads(threadid, board) {
    if ($('#watchedthreadlist').get()!='') {
        $.ajax({
            url: ku_boardspath + '/threadwatch.php?do=removethread&board=' + board + '&threadid=' + threadid,
            success: function(data) {
                popupMessage(_.watchlistRemove)
                getwatchedthreads('0', board);
            },
            error: function(xhr, status) {
                popupMessage(_.oops + " (" + status + ")");
            }
        });
    }
}
    
function hidewatchedthreads() {
    Cookie('showwatchedthreads','0',30);
    $("#watchedthreads").fadeOut();
}    
    
function showwatchedthreads() {
    Cookie('showwatchedthreads','1',30);
    window.location.reload(true);
}

var captcha_shown = 0, rottencaptcha, captcha_rotten = 0;

function captcha_show() { 
    if (captcha_shown == 0 || captcha_rotten == 1) { 
        $(".captchaimage").attr("src", ku_boardspath+'/captcha.php?'+Math.random());
        $('.captchawrap').show();
        if(!dcxt.enabled) {
            captcha_rotten = 0;
            rottencaptcha = setTimeout(rotCaptcha, captchaTimeout);
            $('.captchawrap').animate({'width': 0}, captchaTimeout, "linear").css('overflow', 'visible');
        }
        else $('.captchawrap').css({'background': 'none'});
        $(".captcha_status").html("").click(); captcha_shown = 1;
    }
}

function rotCaptcha() {
    captcha_rotten = 1;
    $('[name="captcha"]').val('');
    $(".captchawrap").css({'opacity': '0'}); 
    $(".captcha_status").html('<a class="xlink" onclick="javascript:refreshCaptcha();">'+_.captcharot+'</a>').show();
}

function refreshCaptcha() {
    $('#'+$(this).parents('[name="postform"]').attr('id')).find('[name="captcha"]').val('').focus();
    $('.captchaimage').attr('src', ku_boardspath+'/captcha.php?'+Math.random());
    $('.captchawrap').stop().css({'opacity': '1', 'width': 150}).animate({'width': 0}, captchaTimeout, "linear").css('overflow', 'visible');; 
    $(".captcha_status").hide();
    clearTimeout(rottencaptcha);
    captcha_rotten = 0;
    rottencaptcha = setTimeout(rotCaptcha, captchaTimeout);
}

function checkcaptcha(formid) {
    if($('input[name=captcha]').length > 0) {
        if($('#'+formid+' input[name=captcha]').val() =='') {
            popupMessage(_.enterCaptcha);
            return false;
        }
        if(captcha_rotten) {
            popupMessage(_.captcharot);
            return false;
        }
    }
    return true;
}
    
function expandimg(postnum, imgurl, thumburl, imgw, imgh, thumbw, thumbh) {
    var element = document.getElementById("thumb" + postnum);
    if (element == null) return false;
    if (element.getElementsByTagName('img')[0].getAttribute('alt').substring(0,4)!='full') {
        $(element).html('<img src="'+imgurl+'" alt="full'+postnum+'" class="thumb" height="'+imgh+'" width="'+imgw+'">'); 
        if (Settings.expandImgFull()) return false;    
        var element = document.getElementById("thumb" + postnum);
        var img = element.getElementsByTagName('img')[0];
        var max_w = document.documentElement?document.documentElement.clientWidth : document.body.clientWidth;
        var offset = 50;
        var offset_el = img;

        while (offset_el != null) {
                offset += offset_el.offsetLeft;
                offset_el = offset_el.offsetParent;
        }
        var new_w = max_w - offset;
        if (img.width > new_w) {
                var ratio = img.width / img.height;
                var zoom = 1 - new_w / img.width;
                var new_h = new_w / ratio;
                var notice = document.createElement('div');
                notice.setAttribute('class', 'filesize');
                notice.style.textDecoration = 'underline'; 
                var textNode = document.createTextNode(_.imageDownscaledBy + " " + Math.round(zoom*100) + "% "+_.toFit);
                notice.appendChild(textNode);  
                element.insertBefore(notice, img);
                $(img).width(new_w);
                $(img).height(new_h);

        }     
    } else{
        element.innerHTML = '<img src="' + thumburl + '" alt="' + postnum + '" class="thumb" height="' + thumbh + '" width="' + thumbw + '">';
        if (Settings.sfwMode()) {
            $(element).find('img').stop(true, true).animate({opacity: 0.05}, {duration: 100});
        }
    }
    return false;
}

// YOBA previews w/#snivystuff
var PostPreviews = {
    zindex:  1,
    cached: {},
    parent: {},

    _mouseover: function(e) {
        e.stopPropagation();
        var href = this.getAttribute("href");
        var isCatalog = $(this).hasClass('catalog-entry');
        var postid = isCatalog ? href.split("/")[3].split('.')[0] : href.split("#")[1];
        var board = href.split("/")[1] || this_board_dir;
        var previewid = 'preview_'+board+'_'+postid;
        var preview = $('#' + previewid);
        if (preview.length == 0) {
            $('body').children().first().before('<div id="'+previewid+'"></div>');
            preview = $('#preview_'+board+'_'+postid);
            preview.addClass('reflinkpreview content-background');     
            preview.mouseleave(PostPreviews._mouseout);
            preview.mouseover(PostPreviews.onMouseOver);
        }
        var parent = $(this).parents('div[id^=preview]');
        if (parent.length > 0) {
            if (previewid == parent.attr('id')) { return; } // anti-recursion
            for(var id in PostPreviews.parent) { if (id == previewid || PostPreviews.parent[id] == previewid) return }
            PostPreviews.parent[previewid] = parent.attr('id');
        } else {
            for(var id in PostPreviews.parent) {
                $('#'+id).stop(true, true);
                $('#'+id).fadeOut(1);
                $('#'+PostPreviews.parent[id]).stop(true, true);
                $('#'+PostPreviews.parent[id]).fadeOut(1);
            }
            PostPreviews.parent = [];
        }
        if(e.clientY < ($(window).height() / 1.5)) { 
            preview.css({top:e.pageY+5}); 
        } else {
            preview.css({bottom:$(window).height()-e.pageY+5}); 
        }
        if(e.clientX < ($(window).width() / 1.5)) {
            preview.css({left:e.pageX+15}); 
        } else {
            preview.css({right:$(window).width()-e.pageX+15}); 
        }
        preview.css({zIndex: PostPreviews.zindex++});      
        if (PostPreviews.cached[previewid] != null) {  
            preview.html(PostPreviews.cached[previewid]);
            if(isCatalog) $(preview).find('.quickreply').remove();
            $(preview).fadeIn(100); 
        } else {
            preview.html("<img alt=\"...\" src=\"/images/loading.gif\" />");
            $.ajax({
                url: "/read.php?b="+board+"&t="+this_board_dir+"&p="+postid+"&single",
                success: function(data){
                    var text = data||(_.oops + " (" + _.blankResponse + ")");
                    preview.html(text);
                    if (data) {                        
                        PostPreviews.cached[previewid] = data;
                        Settings.sfwMode(false);
                    }
                    if(isCatalog) $(preview).find('.quickreply').remove();
                    $(preview).fadeIn(100);
                },
                error: function(){
                    preview.html(_.couldntFetch);
                    $(preview).fadeIn(100);
                }
            });
        }
        e.preventDefault();
    },

    onMouseOver: function() {
        var preview = $(this);
        if ($(this).is('a')) {
            var href = this.getAttribute("href");
            var postid = $(this).hasClass('catalog-entry') ? href.split("/")[3].split('.')[0] : href.split("#")[1];
            var board = href.split("/")[1] || this_board_dir;
            preview = $('#preview_'+board+"_"+postid).first();
        }
        while (preview.length > 0) {
            preview.stop(true,true);
            preview.fadeIn(1);
            preview = $('#' + PostPreviews.parent[preview.attr('id')]);
            
        }
    },
    
    _mouseout: function() {
        var preview = $(this);
        if ($(this).is('a')) {
            var href = this.getAttribute("href");
            var postid = $(this).hasClass('catalog-entry') ? href.split("/")[3].split('.')[0] : href.split("#")[1];
            var board = href.split("/")[1] || this_board_dir;
            preview = $('#preview_'+board+"_"+postid).first();
        }
        while (preview.length > 0) {
            preview.delay(50).fadeOut(250).queue('fx', function() { 
                delete PostPreviews.parent[$(this).attr('id')];
                $(this).remove();
            });
            preview = $('#' + PostPreviews.parent[preview.attr('id')]);   
        }
    }
}

/* txt only. deleted. src in clean */
function postpreview(D,A,C,B){}
    
function set_inputs(id) {
    if (document.getElementById(id)) {
        with(document.getElementById(id)) {
            if(!name.value) name.value = getCookie("name");
            if(!em.value) em.value = getCookie("email");
            if(!postpassword.value) postpassword.value = get_password("postpassword");
        }
    }
}
    
function set_delpass(id) {
    if (document.getElementById(id).postpassword) {
        with(document.getElementById(id)) {
            if(!postpassword.value) postpassword.value = get_password("postpassword");
        }
    }
}   

(function ($) {
    $.event.special.load = {
        add: function (callback) {
            if ( this.nodeType === 1 && this.tagName.toLowerCase() === 'img' && this.src !== '' ) {
                if ( this.complete || this.readyState === 4 ) {
                    callback.handler.apply(this);
                }
                else if ( this.readyState === 'uninitialized' && this.src.indexOf('data:') === 0 ) {
                    $(this).trigger('error');
                }

                else {
                    $(this).bind('load', callback.handler);
                }
            }
        }
    };
}(jQuery));

var Settings = {
    _checkbox: function(clicked, settingName, defaultValue) {
        if (localStorage == null) {
            trace(_.noLocalStorage);
            return;
        }
        if (localStorage[settingName] == null) {
            localStorage[settingName] = defaultValue;
        }
        if (clicked == true) {
            // save it
            localStorage[settingName] = $('#settings_' + settingName).is(":checked");
        } else {
            // update checkbox (called on load)
            if (localStorage[settingName] == 'true')
                $('#settings_' + settingName).attr("checked","checked");
            else 
                $('#settings_' + settingName).removeAttr("checked");
        }
        return (localStorage[settingName] == 'true') || (localStorage[settingName] == true) ;
    },
    
    fxEnabled: function(changed) { 
        var enabled = Settings._checkbox(changed, 'fxEnabled', true);
        if (changed != null) {
            $.fx.off = !enabled;
        }
        return enabled;
    },
    
    showReplies: function(changed) {
        var enabled = Settings._checkbox(changed, 'showReplies', false);
        if (changed != null) {
            enabled ? showreplies() : $('.replieslist').remove();
        }
        return enabled;
    },
    
    sfwMode: function(changed) {
        var enabled = Settings._checkbox(changed, 'sfwMode', false);
        if (changed != null) {
            var target = $('img.thumb');
            if(enabled) {
                injectCSS([[".thumb { opacity: 0.05;}", 1], [".thumb:hover { opacity: 1;}", 2]]);
            } else if(changed) { 
                removeCSS([1, 2]);
            }
        }
        return enabled;
    },
    
    expandImgFull: function(changed) {
        return Settings._checkbox(changed, 'expandImgFull', false);
    }
}

var rswap = {
    i: true,
    swap: function() {
        if(this.i) $('#delform').before($('#rswapper')).after($('.postarea'));
        else $('#delform').before($('.postarea')).after($('#rswapper'));
        this.i = !this.i;
    }
}

var isTouchDevice = (('ontouchstart' in window) || window.DocumentTouch && document instanceof DocumentTouch) ? true : false;
var interfaceType = localStorage['interfaceType'] || false;

var captchalang = getCookie('captchalang') || 'ru';
function setCaptchaLang(lang) {
    if(!in_array(lang, ['ru', 'en', 'num'])) return;
    captchalang = lang;
    set_cookie('captchalang', lang, 365);
    // document.cookie = 'captchalang='+lang+'; expires=0; path=/';
}

function readyset() {
    if(!ispage) $('.mgoback').show();

    if(interfaceType) {
        if(interfaceType == 'touch') {
            isTouchDevice = true;
            $('#js_settings').prepend('<a href="javascript: localStorage[\'interfaceType\']=\'desktop\'; location.reload(); return false">'+_.returnDesktop+'</a><br>');
        } 
        else if(interfaceType == 'desktop') {
            isTouchDevice = false;
            $('#js_settings').prepend('<a href="javascript: localStorage[\'interfaceType\']=\'touch\'; location.reload(); return false">'+_.returnTouch+'</a><br>');
        }
    }
    else if(isTouchDevice) {
        $('#boardlist_header').after('<div id="interface-notifier" class="content-background reflinkpreview"><div class="ifcn-text">'+_.impliedTouch+'</div><div class="incn-buttons">'+
            '<button class="ifcnbtn" onclick="javascript: localStorage[\'interfaceType\']=\'touch\'; $(\'#interface-notifier\').fadeOut(\'fast\'); return false">'+_.okay+'</button>&nbsp;'+
            '<button class="ifcnbtn" onclick="javascript: localStorage[\'interfaceType\']=\'desktop\'; location.reload(); return false">'+_.forceDesktop+'</button></div></div>');
    }
    else {
        $('#js_settings').prepend('<a title="'+_.impliedDesktop+'" href="javascript: localStorage[\'interfaceType\']=\'touch\'; location.reload(); return false">'+_.returnTouch+'</a><br>');
    }
    $('#js_settings').prepend(_.captchalang+': <a href="javascript:setCaptchaLang(\'ru\');">Cyrillic</a> | <a href="javascript:setCaptchaLang(\'en\');">Latin</a> | <a href="javascript:setCaptchaLang(\'num\');">Numeral</a><br />');

    LatexIT.init();
    checkhighlight();
    checkgotothread();
    checknamesave();

    bnrs.init();

    cloud20.init();
    $('#boardselect').on('input', function() {
        cloud20.filter($(this).val());
    });

    if(getCookie('ku_menutype')) {
        var c = Cookie('ku_menutype');
        if(c != 'default' && c != '')
            document.getElementById('overlay_menu').style.position = c;
    }

    //initial post-process
    processNodeInsertion();

    if(!isTouchDevice) {
        $('.mobile-nav').hide();
        $('.sect-exr').mouseenter(function() { menu_show('ms-'+$(this).data('toexpand')); });
        $('#overlay_menu').mouseleave(function() { menu_show('_off_'); });
        $('body').on('mouseenter', "a[class^='ref'], .catalog-entry", PostPreviews._mouseover);
        $('body').on('mouseleave', "a[class^='ref'], .catalog-entry", PostPreviews._mouseout);
    }
    else {
        $('.sect-exr:not([data-toexpand="_options"])').parent().hide();
        $('.sect-exr').click(function() {
            if($('#js_settings').is(':visible')) {
                menu_show('_off_');
            }
            else {
                menu_show('ms-_options');
            }
            return false;
        });
        $('body').click(function(event) {
            menu_show('_off_'); 
            $('[id^=preview]').remove();
        });
        $('.mobile-nav').show();
        $('body').on('click', "a[class^='ref']", PostPreviews._mouseover);
        injectCSS(".spritebtn { border-width: 0 4px;}", 3);
    }

    // new markup
    $('body').on('click', '.uib-mup', function() {
        markup($(this).parents('form'), $(this).data('mups'), $(this).data('mupe'));
        return false;
    });
    $('body').on('click', '.uib-bul', function() {
        bullets($(this).parents('form'), $(this).data('bul'));
        return false;
    });
    $('body').on('click', '.uib-tx', function() {
        var target = $(this).data('target');
        head.js('http://latex.codecogs.com/editor3.js', function() {
            OpenLatexEditor(target,'phpBB','en-us', false, '','full');
        });
        return false;
    });
    //Webm expanding
    $('body').on('click', '.movie', function(event) {expandwebm($(this), event)});
    //new quick reply
    $('body').on('click', '.qrl', quickreply);
    $('#postform').clone().attr({'id': 'postclone', 'onsubmit': "return checkcaptcha('postclone');"}).appendTo('body').hide().addClass('reflinkpreview content-background qreplyform').find('.rules').remove();
    $('#postclone label').each(function() {
        var newid = $(this).attr('for')+'_clone';
        $(this).attr('for', newid);
        $(this).find('input').attr('id', newid);
    });
    $('#postform textarea').attr('id', 'top-textarea'); $('#postform .uib-tx').data('target', 'top-textarea');
    $('#postclone textarea').attr('id', 'pop-textarea'); $('#postclone .uib-tx').data('target', 'pop-textarea');
    if(!isTouchDevice) {
        $('input, textarea, select, label').hover(function() {
            $('#postclone').dragsOff();
        }, function() {
            $('#postclone').drags();
        });
        var pinnerState = localStorage['pinPreference'] || 'pinned';
        var pinner = '<a href="#" onclick="javascript:$(\'#postclone\').pin();return false;" title="Прикрепить / Открепить"><img src="'+ku_boardspath+'/css/icons/blank.gif" border="0" class="spritebtn pinner '+pinnerState+'"></a>';
    }
    else {
        var pinner = '';
    }
    $('<span class="extrabtns postboxcontrol">'+ pinner +
    '&nbsp;<a href="#" onclick="javascript:$(\'#postclone\').hide();return false;" title="Закрыть"><img src="'+ku_boardspath+'/css/icons/blank.gif" border="0" class="closebox spritebtn"></a>'+
    '</span>').appendTo('#postclone');

    //Dollscript rape begins
    //Switch captcha language   
    dcxt.addTask(function() {
        dcxt.openSettings();
        //Switch to "form" tab
        $('.de-cfg-tab[info=form]')[0].click();
        //Switch language if it's set wrong
        if(captchalang == 'ru' && $('select[info=captchaLang] option:selected').val() !== $('select[info=captchaLang] option:contains(Rus)').val()) {
            $('select[info=captchaLang]').val($('select[info=captchaLang] option:contains(Rus)').val()).triggerNative('change'); 
        }
        if(captchalang == 'en' && $('select[info=captchaLang] option:selected').val() !== $('select[info=captchaLang] option:contains(Eng)').val()) {
            $('select[info=captchaLang]').val($('select[info=captchaLang] option:contains(Eng)').val()).triggerNative('change'); 
        }
        dcxt.closeSettings();
    });

    if(react_ena) {
        // Remove malfunctioning post counter
        injectCSS('.de-ppanel-cnt:after {display: none}', 8);
        head.load(react_api + '/socket.io/socket.io.js', function() {
            if(typeof io !== 'undefined') {
                var socket = io.connect(react_api);
                if(ispage) {
                    console.log('It is page!');
                }
                else {
                    dcxt.addTask(function() {
                        dcxt.openSettings();
                        $('.de-cfg-tab[info=form]')[0].click();
                        //Detect if AJAX posting enabled.
                        if(+$('select[info=ajaxReply]').val()) {
                            updater.ajaxPosting = true;
                        }
                        //Switch to "posts" tab
                        $('.de-cfg-tab[info=posts]')[0].click();
                        //Turn off AJAX thread update
                        if($('input[info=ajaxUpdThr]:checked').length) {
                            $('input[info=ajaxUpdThr]:checked')[0].click();
                            dcxt.changed++;
                        }
                        dcxt.closeSettings();
                    });

                    _.noNewPosts += ("<br>" + _.threadUpdationAutomatically);
                    
                    $("#postform, #postclone").each(function() { $(this).append($('<input type="hidden" name="token">').val(randomString())) } );
                    socket.emit('subscribe', $('input[name=board]').val()+':'+$('input[name=replythread]').val());
                    socket.on('update', function(data) {
                        updater.update(data);
                    });
                    $('body').on('submit', '#postform, #postclone', function(e) {
                        e.preventDefault();
                        if (!dcxt.enabled || checkcaptcha($(this).attr('id')))
                        updater.send($(this));
                    });
                }                
            }
        });
    }

    $('#delform').after('<div id="rswapper">[<a onclick="javascript:rswap.swap();return false;" href="#">'+(ispage ? _.NewThread : _.reply)+'</a>]<hr /></div>');


    dcxt.addTask(function() {
        $('#rswapper').remove();
    })

    Settings.sfwMode(false);
    if (localStorage) {
        for(var s in Settings) {
            if (s.substring(0,1) == "_") continue;
            $("#js_settings").append('<label><input type="checkbox" onchange="javascript:Settings.'+s+'(true)" name="settings_'+s+'" id="settings_'+s+'" value="true"> '+_['settings_'+s]+'</label><br />');
            Settings[s](false);
        }
    } else {
        $("#js_settings").append("<span style=\"color:#F00\">"+_.noLocalStorage+"</span><br />Твой браузер — говно. Скачай <a href=\"/web/20110329072959/http://google.com/chrome\" target=\"_blank\">Chome</a>, например.");
    }

    var textbox = document.getElementById('message');
    if(textbox)
    {
        textbox.onfocus=function(){is_entering = true;}
        textbox.onblur=function(){is_entering = false;}
    }

    $('body').on('click', '#posttypeindicator a', function() {
        var xl = $(this); 
        var offset = $('[name="' + xl.attr('href').substr(1) + '"]').offset() || $('[name="' + xl.text().split('>>')[1] + '"]').offset() || false;
        if(offset) {
            $('html, body').animate({
                scrollTop: offset.top - ( $('#overlay_menu').height() + 10 )
            }, 250);
        }
        return false;
    });

    $('body').on('click', '.dice', function() {
        if(typeof $(this).data('html') === 'undefined') $(this).data('html', $(this).html());
        var htm = strip($(this).html());
        $(this).html($(this).attr('title'));
        $(this).attr('title', htm);
    });

    //Permalinks and stuff
    $('<div id="viewlink"></div>').addClass('content-background reflinkpreview qreplyform').html(
        '<div style="display:inline-block"><input type="text" id="directLink"><br /><input type="text" id="quoteLink"></div>'+
        '<a href="#" onclick="javascript:$(\'#viewlink\').hide();return false;" title="'+_.Close+'"><img style="vertical-align: top;" src="'+ku_boardspath+'/css/icons/blank.gif" border="0" class="closebox spritebtn"></a>'
    ).hide().appendTo('body');
    $('body').on('click', '.shl', showLinks);
    $('#directLink, #quoteLink').on("click", function() { $(this).select(); });

    //Ultimate YOBA Youtube embeds
    $('body').on('click','.embed', function() {$(this).unwrap() });
    //detect node insertions and process them
    $(document).on('animationstart webkitAnimationStart MSAnimationStart oanimationstart', function(event) {
        var $target = $(event.target);
        if (event.originalEvent.animationName == "nodeInserted" && !$target.hasClass('_inserted_')) processNodeInsertion($target);
    });
    $('body').on('mouseenter', '._country_', function() {
        if(typeof $(this).attr('title') === "undefined") {
            $(this).attr('title', countries[$(this).attr('src').split('flags/')[1].split('.PNG')[0].toUpperCase()]);
        }
    });
    dcxt.performTasks();
}

// this will be applied to every new inserted node (post)
function processNodeInsertion($node) {
    if(typeof $node === 'undefined') $node = $('*');
    else {
        $node.addClass('_inserted_');
        $node = $node.parents(":eq(1)");
    }
    if($node.find('.prettyprint').length) prettyprint_mod(null, $node[0]);
    LatexIT.render($node);
    processEmbeds($node);
    delandbanlinks($node);
}

var dcxt = {
    tries: 10,
    enabled: false,
    tasks: [],
    changed: 0,
    openSettings: function() {
        if($('#de-content-cfg').length) return;
        injectCSS('#de-content-cfg {opacity: 0!important}', 7);
        $('#de-btn-settings')[0].click();
    },
    closeSettings: function() {
        if(!$('#de-content-cfg').length) return;
        $('#de-btn-settings')[0].click();
        removeCSS(7);
    },
    addTask: function(fn) {
        if(this.enabled) fn();
        else this.tasks.push(fn);
    },
    performTasks: function() {
        if(!this.enabled && $('#de-btn-settings').length) {
            this.enabled = true;
            $('#gotothread').prop('checked', true);
            iter(this.tasks, function(task) {
                task();
            });
            this.tasks = [];
            if(this.changed) location.reload();
        }
        else if(this.tries) {
            setTimeout(function() {
                dcxt.performTasks();
            }, 500);
            this.tries--;
        }
    }
}

var updater = {
    ajaxPosting: false,
    update: function(data) {
        if(typeof data.token !== undefined) {
            if($('input[name=token][value='+ data.token +']').length) {
                // This is my post
                clearfields($('input[name=token][value='+ data.token +']').parents('form'));
                if(!this.ajaxPosting) getnewposts();
            }
            else {
                getnewposts();
            }
        }
        var lastvisits = localStorage['lastvisits'] ? (JSON.parse(localStorage['lastvisits']) || { }) : { };
        if(typeof data.timestamp !== 'undefined') {
            lastvisits[boardid] = data.timestamp;
            localStorage.setItem('lastvisits', JSON.stringify(lastvisits));
        }
    },
    send: function($form) {
        if(!this.ajaxPosting) {
            xsend($form.attr('id'));
        }
        return false;
    }
}

function strip(html)
{
   var tmp = document.createElement("DIV");
   tmp.innerHTML = html;
   return tmp.textContent || tmp.innerText || "";
}

function processEmbeds($scope) {
    $scope.find('.embed:not(.title-given)').each(function() {
        var container = $(this);
        var vidID = container.data('id');
        if(container.data('site') === 'youtube') { 
            $.get('http://gdata.youtube.com/feeds/api/videos/'+vidID, function(res) {
                var videotitle = $($(res).find('title')[0]).text();
                container.append($('<a target="_blank" title="'+_.watchOn+' Youtube"></a>').addClass('yt-title-overlay').text(videotitle).attr('href', 'http://www.youtube.com/watch?v='+vidID)
                .click(function(ev) { ev.stopPropagation(); }));
            });
            container.addClass('title-given');
        }
        if(container.data('site') === 'vimeo') {
            $.get('http://vimeo.com/api/v2/video/'+vidID+'.json', function(res) {
                var videotitle = res[0].title, thumbnail = res[0].thumbnail_large;
                container.css({'background-image': 'url('+thumbnail+')'})
                .append($('<a target="_blank" title="'+_.watchOn+' Vimeo"></a>').addClass('vi-title-overlay').text(videotitle).attr('href', 'http://vimeo.com/'+vidID)
                .click(function(ev) { ev.stopPropagation(); }));
            });
            container.addClass('title-given');
        }
        if(container.data('site') === 'coub') {
            $.get(ku_boardspath+'/corpsy.php?code='+vidID, function(res) {
                var videotitle = res.title, thumbnail = res.thumbnail_url;
                container.css({'background-image': 'url('+thumbnail+')'})
                .append($('<a target="_blank" title="'+_.watchOn+' Coub"></a>').addClass('co-title-overlay').text(videotitle).attr('href', 'http://coub.com/view/'+vidID)
                .click(function(ev) { ev.stopPropagation(); }));
            });
            container.addClass('title-given');
        }
    });
}


var kumod = getCookie('kumod');
if (kumod !== '') {
    if(kumod === 'allboards') kumod_set = true;
    else kumod_set = in_array(this_board_dir, kumod.split('|'));
}

var mp3playerid = 0;
function expandmp3(id, path){
    if (mp3playerid == id)
    {
        document.getElementById('player'+id).innerHTML = '';
        document.getElementById('player'+id).style.display = 'none';
        mp3playerid = 0;
    } else {
        if(mp3playerid != 0)
        {
            document.getElementById('player'+mp3playerid).innerHTML = '';
            document.getElementById('player'+mp3playerid).style.display = 'none';
        }

        document.getElementById('player'+id).innerHTML = '<embed src="/web/20110329072959/http://www.0chan.ru/mediaplayer.swf?type=mp3&file='+path+'" width="320" height="20">';
        document.getElementById('player'+id).style.display = 'block';
        mp3playerid = id;
    }
}

var swfplayerid = 0;
function expandswf(id, path, w, h){
    if (swfplayerid == id)
    {
        document.getElementById('swfplayer'+id).innerHTML = '';
        document.getElementById('swfplayer'+id).style.display = 'none';
        swfplayerid = 0;
    } else {
        if(swfplayerid != 0)
        {
            document.getElementById('swfplayer'+swfplayerid).innerHTML = '';
            document.getElementById('swfplayer'+swfplayerid).style.display = 'none';
        }

        document.getElementById('swfplayer'+id).innerHTML = '<embed src="'+path+'" width="'+w+'" height="'+h+'">';
        document.getElementById('swfplayer'+id).style.display = 'block';
        swfplayerid = id;
    }
}

function expandwebm($mov, ev) {
    //good luck understanding this shitcode :^)
    if($mov.data('expanded') !== '1') {
        ev.preventDefault(); 
        var movieurl = $mov.attr('href'), imgh = $mov.data('height'), imgw = $mov.data('width'), dt = $mov.data('thumb'), postnum = $mov.data('id');
        var uid = '_vframe_'+makeid()+(new Date().getTime());
        $mov.replaceWith(function() {
            return '<span id="'+uid+'" data-thumb="'+dt+'" data-width="'+imgw+'"" data-height="'+imgh+'" data-href="'+movieurl+'">'+this.innerHTML + '</span>';
        });
        $mov = $("#"+uid);
        $mov.find('img').hide();
        var video = $mov.find('video').show(), notice = '';
        if(!video.length) {
            $mov.find('span').append('<video class="thumb" src="'+movieurl+'" controls loop autoplay height="'+imgh+'" width="'+imgw+'"></video>').promise().done(function() {
                video = $mov.find('video');
            });
        }
        else video.get(0).play();
        if(!Settings.expandImgFull()) {
            var offset = 50, offset_el = video[0];
            var max_w = document.documentElement?document.documentElement.clientWidth : document.body.clientWidth;
            while (offset_el != null) {
                offset += offset_el.offsetLeft;
                offset_el = offset_el.offsetParent;
            }
            var new_w = max_w - offset;
            if(imgw > new_w) {
                var ratio = imgw / imgh;
                var zoom = 1 - new_w / imgw;
                var new_h = new_w / ratio;
                video.width(new_w);
                video.height(new_h);
                notice = _.videoDownscaledBy + " " + Math.round(zoom*100) + "% "+_.toFit;
            }
        }
        $mov.parent().find('.filesize').append('<span class="videocloser"><b> [<a href="#">x</a>]</b> '+notice+'</span>');
        $mov.parent().find('.videocloser').click(function() {
            var uid = '_vframe_'+makeid()+(new Date().getTime());
            $mov.replaceWith(function() {
                return '<a class="movie" id="'+uid+'" data-thumb="'+dt+'" data-width="'+imgw+'"" data-height="'+imgh+'" href="'+movieurl+'">'+this.innerHTML + '</a>';
            }).data('expanded', '0');
            $mov = $("#"+uid);
            $mov.find('video').hide()[0].pause();
            $mov.find('img').show();
            $(this).remove();
            return false;
        });
    }
}

function makeid()
{
    var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    for( var i=0; i < 5; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));

    return text;
}

function checknamesave(){
    var checkd;
    if(getCookie('name') != '') {
        checkd = true;
    } else {
        checkd = false;
    }
    var doc = document.getElementById('save');
    if (doc != null) doc.checked = checkd;
}
function checkgotothread(){
    var checkd; 
    if(getCookie('tothread') == 'on') {
        checkd = true;
    } else {
        checkd = false;
    }
    $("#gotothread").attr('checked', checkd);
}

function navigatepages (event)
{
    if (!document.getElementById) return;
        if (is_entering) return;
    if (window.event) event = window.event;

    if (event.ctrlKey)
    {

        var link = null;
        var href = null;

                var docloc = document.location.toString();
                if (docloc.indexOf('/res/') != -1) {
                    if( (event.keyCode ? event.keyCode : event.which ? event.which : null) == 13 )
                        $('textarea[name="message"]:focus').parents('form').submit();
                }
                else {
                    if (docloc.indexOf('.html') == -1 || docloc.indexOf('board.html') != -1) {
                        var page = 0;
                        var docloc_trimmed = docloc.substr(0, docloc.lastIndexOf('/') + 1);
                    } else {
                        var page = docloc.substr((docloc.lastIndexOf('/') + 1));
                        page = (+page.substr(0, page.indexOf('.html')));
                        var docloc_trimmed = docloc.substr(0, docloc.lastIndexOf('/') + 1);
                    }
                    if (page == 0) {
                        var docloc_valid = docloc_trimmed;
                    } else {
                        var docloc_valid  = docloc_trimmed + page + '.html';
                    }
                    if(match=/#s([0-9]+)/.exec(docloc)) {
                        var relativepost = (+match[1]);
                    } else {
                        var relativepost = -1;
                    }
                    var maxthreads = 0;
                    while(document.getElementsByName('s'+(++maxthreads)).length>0){}
                    switch (event.keyCode ? event.keyCode : event.which ? event.which : null)
                    {
                        case 13: // ctrl+Enter
                            $('textarea[name="message"]:focus').parents('form').submit();
                            break;

                        case 0x25: // ctrl+left
                            link = document.getElementById('prevPage');
                            break;
                        case 0x27: // ctrl+right
                            link = document.getElementById('nextPage');
                            break;

                        case 0x28: // ctrl+down
                            if (relativepost == maxthreads - 1) {
                                break; //var newrelativepost = 0;
                            } else {
                                var newrelativepost = relativepost + 1;
                            }
                            href = docloc_valid + '#s' + newrelativepost;
                            break;

                        case 0x26: // ctrl+up
                            if (relativepost == -1 || relativepost == 0) {
                                break; //var newrelativepost = maxthreads - 1;
                            } else {
                                var newrelativepost = relativepost - 1;
                            }
                            href = docloc_valid + '#s' + newrelativepost;
                            break;

                        case 0x24: // ctrl+home
                            document.location = docloc_trimmed;
                            break;
                    }

                    if (link && link.action) document.location = link.action;
                    if (href) document.location.href = href;
                }
    }
}
 

if (window.document.addEventListener) {
   window.document.addEventListener("keydown", navigatepages, false);
} else {
   window.document.attachEvent("onkeydown", navigatepages);
}


// inline replies
function showreplies(root) {
    if (/*ispage || */!Settings.showReplies()) return;
    root = root ? root : 'body';
    $(root).find('.replieslist').remove();
    var repliesStrings = [];
    $(root).find('.postnode').each(function(index, element) {
        var postlink = $(this).find('span.reflink').find('a').first().attr('href');
        var postid = postlink.split('#')[1];
        var replies = [];
        $(root).find(".postmessage").find('a').each(function(index, element) {
            if ($(this).attr('href') == postlink)
                replies.push($(this).parents('.postnode').first().find('span.reflink').find('a').attr('href'));
        });
        replies = $.unique(replies);
        if (replies.length > 0) { 
            repliesStrings[postid] = '<div class="replieslist"><br />'+_.replies+': ';
            for (var i = 0; i < replies.length; i++) {
                var replypostlink = replies[i];
                var replypostid = replypostlink.split('#')[1];
                repliesStrings[postid] += '<a class="ref-reply" href="'+replypostlink+'" onclick="javascript:highlight(\''+replypostid+'\', true);">&gt;&gt;'+replypostid+"</a>";
                if (i != replies.length - 1) repliesStrings[postid] += ', ';
            }
        }
    });
    $(root).find('.postnode').each(function(index, element) {
        var postid = $(this).find('span.reflink').find('a').first().attr('href').split('#')[1];
        if (repliesStrings[postid]) {
            $(this).find('.postmessage').append(repliesStrings[postid]);
        }
    });
}

/// overlay menu
var menu_current = '';
var menu_last = '';
function menu_show(id)
{
    if(menu_current != '')
    {
        var dl = (id == '_off_') ? 125 : 0;
        $('#'+menu_current).delay(dl).slideUp(100);
        menu_last = menu_current;
    }
    if (id != '') {
        if (menu_last == id && typeof $('#' + id).queue() !== 'undefined' && $('#' + id).queue().length > 0) {
            $('#' + id).clearQueue();
        } else {
            $('#' + id).slideDown(150);
        }
    }
    menu_current = id;
}
function menu_pin(){
    if(document.getElementById('overlay_menu').style.position == 'absolute') {
        document.getElementById('overlay_menu').style.position = 'fixed';
        Cookie('ku_menutype', 'fixed', 365);
    } else { 
        document.getElementById('overlay_menu').style.position = 'absolute';
        Cookie('ku_menutype', 'absolute', 365);
    }
}

function set_oldmenu(cookie){
    if(cookie) {
        Cookie('ku_oldmenu', 'yes', 90);
    }
    var h = document.getElementById('boardlist_header');
    var f = document.getElementById('boardlist_footer');
    if (h && f) {
        h.innerHTML = f.innerHTML + ' <a href=\"#\" onclick=\"javascript:set_cookie(\'ku_oldmenu\', \'no\', 90);parent.document.location.reload(true);\">[overlay]</a>';
    }
}

var LatexIT = {
    mode : 'gif',
    init : function() {
    if(document.implementation.hasFeature("http://www.w3.org/TR/SVG11/feature#BasicStructure", "1.1"))
        this.mode='svg';
    },
    odc: "javascript:LatexIT.replaceWithSrc(this);",
    dcls: "Double click to show source", 

    pre : function(eqn) {
        var txt=eqn.innerHTML;
        if ( !txt.match(/<img.*?>/i) && !txt.match(/<object.*?>/i))
        {
            //Clean code
            txt=txt.replace(/<br>/gi,"");
            txt=txt.replace(/<br \/>/gi,"");
            var atxt = "[tex]"+txt+"[/tex]"; 
            txt=escape(txt.replace(/\\/mg,'\\'));
            // Add coloring according to style of text
            var c = eval("LatexIT.normalize"+$(eqn).parent().css('color'));
            var extxt = "{\\color[rgb]{"+c.r+','+c.g+','+c.b+"}"+txt+"}";
            txt = " <img src=\"http://latex.codecogs.com/"+this.mode+".latex?"+ extxt +"\" title=\""+this.dcls+"\" alt=\""+atxt+"\" ondblclick=\""+this.odc+"\" border=\"0\" class=\"latex\" /> ";
        }
        return txt;
    },
    
    replaceWithSrc: function(eqn) {
        var txt = $(eqn).attr('alt');
        $(eqn).parent().html(txt);
    },

    render : function($scope) {
        var scope = (typeof $scope === 'undefined') ? window.document : $scope[0];
        var eqn = scope.getElementsByTagName("*");
        for (var i=0; i<eqn.length; i++) {
            if(eqn[i].getAttribute("lang") == "latex" || eqn[i].getAttribute("xml:lang") == "latex")
            eqn[i].innerHTML = this.pre(eqn[i]);
        } 
    },

    normalizergb : function(r, g, b) {
        return {r: (r/255).toFixed(3), g: (g/255).toFixed(2), b: (b/255).toFixed(2) }
    },
    normalizergba : function(r, g, b, a) {
        return this.normalizergb(r, g, b);
    }
};

function iter(array, callback) {
    if(typeof array !== 'undefined' && array) {
        if(typeof array.length === 'undefined') return callback(array);
        var i=0, len = array.length;
        for ( ; i < len ; i++ ) {
            callback(array[i]);
        }
    }
}

function in_array(needle, haystack) {
    if(typeof haystack !== 'object') {
        if(needle === haystack) return true;
        else return false;
    }
    for(var key in haystack) {
        if(needle === haystack[key]) {
            return true;
        }
    }
    return false;
}

function prettyprint_mod() {
    prettyPrint.apply(this, arguments);
    $(".prettyprint").each(function() {
        $(this).parents().filter(".reply").addClass('prettyprint-container');
    });
    $('.replies table tbody tr').each(function() {
        $(this).css({ 'display': 'block' });
    });
    $('.prettyprint').next().each(function() {
        $(this).css({ 'display': 'none' });
    });
}

(function($) {
    $.fn.drags = function(opt) {
        opt = $.extend({handle:"",cursor:"move"}, opt);

        if(opt.handle === "") {
            var $el = this;
        } else {
            var $el = this.find(opt.handle);
        }

        return $el.css('cursor', opt.cursor).on("mousedown", function(e) {
            if(opt.handle === "") {
                var $drag = $(this).addClass('draggable');
            } else {
                var $drag = $(this).addClass('active-handle').parent().addClass('draggable');
            }
            var z_idx = $drag.css('z-index'),
                drg_h = $drag.outerHeight(),
                drg_w = $drag.outerWidth(),
                pos_y = $drag.offset().top + drg_h - e.pageY,
                pos_x = $drag.offset().left + drg_w - e.pageX;
            $drag.css('z-index', 1000).parents().on("mousemove", function(e) {
                $('.draggable').offset({
                    top:e.pageY + pos_y - drg_h,
                    left:e.pageX + pos_x - drg_w
                }).on("mouseup", function() {
                    $(this).removeClass('draggable').css('z-index', z_idx);
                });
            });
            e.preventDefault(); // disable selection
        }).on("mouseup", function() {
            if(opt.handle === "") {
                $(this).removeClass('draggable');
            } else {
                $(this).removeClass('active-handle').parent().removeClass('draggable');
            }
        });
    }
    $.fn.dragsOff = function(opt) {
        opt = $.extend({handle:"",cursor:"default"}, opt);

        if(opt.handle === "") {
         var $el = this;
         $(this).removeClass('draggable');
        } else {
         var $el = this.find(opt.handle);
         $(this).removeClass('active-handle')
                .parent()
                .removeClass('draggable');
        }
        return $el.css('cursor', "default")
            .off("mousedown")
            .off("mouseup")
            .off("mousemove");
    }
    $.fn.pin = function() {
        if(this.css('position') !== 'fixed') {
            var abs = {
                top: this.position().top - $(document).scrollTop(),
                left: this.position().left - $(document).scrollLeft()
            }
            this.css({
                position: 'fixed',
                left: abs.left,
                top: abs.top
            });
            this.find('.pinner').removeClass('unpinned').addClass('pinned');
            localStorage['pinPreference'] = 'pinned';
        }
        else {
            var abs = {
                top: this.position().top + $(document).scrollTop(),
                left: this.position().left + $(document).scrollLeft()
            }
            this.css({
                position: 'absolute',
                left: abs.left,
                top: abs.top
            });
            this.find('.pinner').removeClass('pinned').addClass('unpinned');
            localStorage['pinPreference'] = 'unpinned';
        }
    }
    $.fn.unwrap = function () {
        $(this).off().removeClass('wrapper').addClass('unwrapping').empty().css({'background-image': 'none'});
        var vid = $(this).data('id'), htm = '';
        var embedHTMLs = {
            youtube: '<iframe style="display:none" class="embedded-content" width="368" height="237" src="//www.youtube.com/embed/'+vid+'?wmode=transparent" frameborder="0" allowfullscreen></iframe>',
            vimeo: '<iframe style="display:none" src="//player.vimeo.com/video/'+vid+'?badge=0" width="368" height="210" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>',
            coub: '<iframe src="http://coub.com/embed/'+vid+'?muted=false&autostart=false&originalSize=false&hideTopBar=false&noSiteButtons=false&startWithHD=false" allowfullscreen="true" frameborder="0" width="368" height="207"></iframe>'
        }
        var htm = embedHTMLs[$(this).data('site')] || false;
        if(!htm) return false;
        var container = $(this);
        var content = $(htm)
        .appendTo(container)
        .load(function() {
            $(this).show();
            container.removeClass('unwrapping');
        });
    };
    $.fn.triggerNative = function(eventName) {
        return this.each(function() {
            var el = $(this).get(0);
            triggerNativeEvent(el, eventName);
        });
    };
    function triggerNativeEvent(el, eventName){
      if (el.fireEvent) { // < IE9
        (el.fireEvent('on' + eventName));
      } else {
        var evt = document.createEvent('Events');
        evt.initEvent(eventName, true, false);
        el.dispatchEvent(evt);
      }
    }
})(jQuery);

;!function(a){"use strict";"function"==typeof define&&define.amd?define(["jquery"],a):a("undefined"!=typeof jQuery?jQuery:window.Zepto)}(function(a){"use strict";function b(b){var c=b.data;b.isDefaultPrevented()||(b.preventDefault(),a(b.target).ajaxSubmit(c))}function c(b){var c=b.target,d=a(c);if(!d.is("[type=submit],[type=image]")){var e=d.closest("[type=submit]");if(0===e.length)return;c=e[0]}var f=this;if(f.clk=c,"image"==c.type)if(void 0!==b.offsetX)f.clk_x=b.offsetX,f.clk_y=b.offsetY;else if("function"==typeof a.fn.offset){var g=d.offset();f.clk_x=b.pageX-g.left,f.clk_y=b.pageY-g.top}else f.clk_x=b.pageX-c.offsetLeft,f.clk_y=b.pageY-c.offsetTop;setTimeout(function(){f.clk=f.clk_x=f.clk_y=null},100)}function d(){if(a.fn.ajaxSubmit.debug){var b="[jquery.form] "+Array.prototype.join.call(arguments,"");window.console&&window.console.log?window.console.log(b):window.opera&&window.opera.postError&&window.opera.postError(b)}}var e={};e.fileapi=void 0!==a("<input type='file'/>").get(0).files,e.formdata=void 0!==window.FormData;var f=!!a.fn.prop;a.fn.attr2=function(){if(!f)return this.attr.apply(this,arguments);var a=this.prop.apply(this,arguments);return a&&a.jquery||"string"==typeof a?a:this.attr.apply(this,arguments)},a.fn.ajaxSubmit=function(b){function c(c){var d,e,f=a.param(c,b.traditional).split("&"),g=f.length,h=[];for(d=0;g>d;d++)f[d]=f[d].replace(/\+/g," "),e=f[d].split("="),h.push([decodeURIComponent(e[0]),decodeURIComponent(e[1])]);return h}function g(d){for(var e=new FormData,f=0;f<d.length;f++)e.append(d[f].name,d[f].value);if(b.extraData){var g=c(b.extraData);for(f=0;f<g.length;f++)g[f]&&e.append(g[f][0],g[f][1])}b.data=null;var h=a.extend(!0,{},a.ajaxSettings,b,{contentType:!1,processData:!1,cache:!1,type:i||"POST"});b.uploadProgress&&(h.xhr=function(){var c=a.ajaxSettings.xhr();return c.upload&&c.upload.addEventListener("progress",function(a){var c=0,d=a.loaded||a.position,e=a.total;a.lengthComputable&&(c=Math.ceil(d/e*100)),b.uploadProgress(a,d,e,c)},!1),c}),h.data=null;var j=h.beforeSend;return h.beforeSend=function(a,c){c.data=b.formData?b.formData:e,j&&j.call(this,a,c)},a.ajax(h)}function h(c){function e(a){var b=null;try{a.contentWindow&&(b=a.contentWindow.document)}catch(c){d("cannot get iframe.contentWindow document: "+c)}if(b)return b;try{b=a.contentDocument?a.contentDocument:a.document}catch(c){d("cannot get iframe.contentDocument: "+c),b=a.document}return b}function g(){function b(){try{var a=e(r).readyState;d("state = "+a),a&&"uninitialized"==a.toLowerCase()&&setTimeout(b,50)}catch(c){d("Server abort: ",c," (",c.name,")"),h(A),w&&clearTimeout(w),w=void 0}}var c=l.attr2("target"),f=l.attr2("action"),g="multipart/form-data",j=l.attr("enctype")||l.attr("encoding")||g;x.setAttribute("target",o),(!i||/post/i.test(i))&&x.setAttribute("method","POST"),f!=m.url&&x.setAttribute("action",m.url),m.skipEncodingOverride||i&&!/post/i.test(i)||l.attr({encoding:"multipart/form-data",enctype:"multipart/form-data"}),m.timeout&&(w=setTimeout(function(){v=!0,h(z)},m.timeout));var k=[];try{if(m.extraData)for(var n in m.extraData)m.extraData.hasOwnProperty(n)&&(a.isPlainObject(m.extraData[n])&&m.extraData[n].hasOwnProperty("name")&&m.extraData[n].hasOwnProperty("value")?k.push(a('<input type="hidden" name="'+m.extraData[n].name+'">').val(m.extraData[n].value).appendTo(x)[0]):k.push(a('<input type="hidden" name="'+n+'">').val(m.extraData[n]).appendTo(x)[0]));m.iframeTarget||q.appendTo("body"),r.attachEvent?r.attachEvent("onload",h):r.addEventListener("load",h,!1),setTimeout(b,15);try{x.submit()}catch(p){var s=document.createElement("form").submit;s.apply(x)}}finally{x.setAttribute("action",f),x.setAttribute("enctype",j),c?x.setAttribute("target",c):l.removeAttr("target"),a(k).remove()}}function h(b){if(!s.aborted&&!F){if(E=e(r),E||(d("cannot access response document"),b=A),b===z&&s)return s.abort("timeout"),y.reject(s,"timeout"),void 0;if(b==A&&s)return s.abort("server abort"),y.reject(s,"error","server abort"),void 0;if(E&&E.location.href!=m.iframeSrc||v){r.detachEvent?r.detachEvent("onload",h):r.removeEventListener("load",h,!1);var c,f="success";try{if(v)throw"timeout";var g="xml"==m.dataType||E.XMLDocument||a.isXMLDoc(E);if(d("isXml="+g),!g&&window.opera&&(null===E.body||!E.body.innerHTML)&&--G)return d("requeing onLoad callback, DOM not available"),setTimeout(h,250),void 0;var i=E.body?E.body:E.documentElement;s.responseText=i?i.innerHTML:null,s.responseXML=E.XMLDocument?E.XMLDocument:E,g&&(m.dataType="xml"),s.getResponseHeader=function(a){var b={"content-type":m.dataType};return b[a.toLowerCase()]},i&&(s.status=Number(i.getAttribute("status"))||s.status,s.statusText=i.getAttribute("statusText")||s.statusText);var j=(m.dataType||"").toLowerCase(),k=/(json|script|text)/.test(j);if(k||m.textarea){var l=E.getElementsByTagName("textarea")[0];if(l)s.responseText=l.value,s.status=Number(l.getAttribute("status"))||s.status,s.statusText=l.getAttribute("statusText")||s.statusText;else if(k){var o=E.getElementsByTagName("pre")[0],p=E.getElementsByTagName("body")[0];o?s.responseText=o.textContent?o.textContent:o.innerText:p&&(s.responseText=p.textContent?p.textContent:p.innerText)}}else"xml"==j&&!s.responseXML&&s.responseText&&(s.responseXML=H(s.responseText));try{D=J(s,j,m)}catch(t){f="parsererror",s.error=c=t||f}}catch(t){d("error caught: ",t),f="error",s.error=c=t||f}s.aborted&&(d("upload aborted"),f=null),s.status&&(f=s.status>=200&&s.status<300||304===s.status?"success":"error"),"success"===f?(m.success&&m.success.call(m.context,D,"success",s),y.resolve(s.responseText,"success",s),n&&a.event.trigger("ajaxSuccess",[s,m])):f&&(void 0===c&&(c=s.statusText),m.error&&m.error.call(m.context,s,f,c),y.reject(s,"error",c),n&&a.event.trigger("ajaxError",[s,m,c])),n&&a.event.trigger("ajaxComplete",[s,m]),n&&!--a.active&&a.event.trigger("ajaxStop"),m.complete&&m.complete.call(m.context,s,f),F=!0,m.timeout&&clearTimeout(w),setTimeout(function(){m.iframeTarget?q.attr("src",m.iframeSrc):q.remove(),s.responseXML=null},100)}}}var j,k,m,n,o,q,r,s,t,u,v,w,x=l[0],y=a.Deferred();if(y.abort=function(a){s.abort(a)},c)for(k=0;k<p.length;k++)j=a(p[k]),f?j.prop("disabled",!1):j.removeAttr("disabled");if(m=a.extend(!0,{},a.ajaxSettings,b),m.context=m.context||m,o="jqFormIO"+(new Date).getTime(),m.iframeTarget?(q=a(m.iframeTarget),u=q.attr2("name"),u?o=u:q.attr2("name",o)):(q=a('<iframe name="'+o+'" src="'+m.iframeSrc+'" />'),q.css({position:"absolute",top:"-1000px",left:"-1000px"})),r=q[0],s={aborted:0,responseText:null,responseXML:null,status:0,statusText:"n/a",getAllResponseHeaders:function(){},getResponseHeader:function(){},setRequestHeader:function(){},abort:function(b){var c="timeout"===b?"timeout":"aborted";d("aborting upload... "+c),this.aborted=1;try{r.contentWindow.document.execCommand&&r.contentWindow.document.execCommand("Stop")}catch(e){}q.attr("src",m.iframeSrc),s.error=c,m.error&&m.error.call(m.context,s,c,b),n&&a.event.trigger("ajaxError",[s,m,c]),m.complete&&m.complete.call(m.context,s,c)}},n=m.global,n&&0===a.active++&&a.event.trigger("ajaxStart"),n&&a.event.trigger("ajaxSend",[s,m]),m.beforeSend&&m.beforeSend.call(m.context,s,m)===!1)return m.global&&a.active--,y.reject(),y;if(s.aborted)return y.reject(),y;t=x.clk,t&&(u=t.name,u&&!t.disabled&&(m.extraData=m.extraData||{},m.extraData[u]=t.value,"image"==t.type&&(m.extraData[u+".x"]=x.clk_x,m.extraData[u+".y"]=x.clk_y)));var z=1,A=2,B=a("meta[name=csrf-token]").attr("content"),C=a("meta[name=csrf-param]").attr("content");C&&B&&(m.extraData=m.extraData||{},m.extraData[C]=B),m.forceSync?g():setTimeout(g,10);var D,E,F,G=50,H=a.parseXML||function(a,b){return window.ActiveXObject?(b=new ActiveXObject("Microsoft.XMLDOM"),b.async="false",b.loadXML(a)):b=(new DOMParser).parseFromString(a,"text/xml"),b&&b.documentElement&&"parsererror"!=b.documentElement.nodeName?b:null},I=a.parseJSON||function(a){return window.eval("("+a+")")},J=function(b,c,d){var e=b.getResponseHeader("content-type")||"",f="xml"===c||!c&&e.indexOf("xml")>=0,g=f?b.responseXML:b.responseText;return f&&"parsererror"===g.documentElement.nodeName&&a.error&&a.error("parsererror"),d&&d.dataFilter&&(g=d.dataFilter(g,c)),"string"==typeof g&&("json"===c||!c&&e.indexOf("json")>=0?g=I(g):("script"===c||!c&&e.indexOf("javascript")>=0)&&a.globalEval(g)),g};return y}if(!this.length)return d("ajaxSubmit: skipping submit process - no element selected"),this;var i,j,k,l=this;"function"==typeof b?b={success:b}:void 0===b&&(b={}),i=b.type||this.attr2("method"),j=b.url||this.attr2("action"),k="string"==typeof j?a.trim(j):"",k=k||window.location.href||"",k&&(k=(k.match(/^([^#]+)/)||[])[1]),b=a.extend(!0,{url:k,success:a.ajaxSettings.success,type:i||a.ajaxSettings.type,iframeSrc:/^https/i.test(window.location.href||"")?"javascript:false":"about:blank"},b);var m={};if(this.trigger("form-pre-serialize",[this,b,m]),m.veto)return d("ajaxSubmit: submit vetoed via form-pre-serialize trigger"),this;if(b.beforeSerialize&&b.beforeSerialize(this,b)===!1)return d("ajaxSubmit: submit aborted via beforeSerialize callback"),this;var n=b.traditional;void 0===n&&(n=a.ajaxSettings.traditional);var o,p=[],q=this.formToArray(b.semantic,p);if(b.data&&(b.extraData=b.data,o=a.param(b.data,n)),b.beforeSubmit&&b.beforeSubmit(q,this,b)===!1)return d("ajaxSubmit: submit aborted via beforeSubmit callback"),this;if(this.trigger("form-submit-validate",[q,this,b,m]),m.veto)return d("ajaxSubmit: submit vetoed via form-submit-validate trigger"),this;var r=a.param(q,n);o&&(r=r?r+"&"+o:o),"GET"==b.type.toUpperCase()?(b.url+=(b.url.indexOf("?")>=0?"&":"?")+r,b.data=null):b.data=r;var s=[];if(b.resetForm&&s.push(function(){l.resetForm()}),b.clearForm&&s.push(function(){l.clearForm(b.includeHidden)}),!b.dataType&&b.target){var t=b.success||function(){};s.push(function(c){var d=b.replaceTarget?"replaceWith":"html";a(b.target)[d](c).each(t,arguments)})}else b.success&&s.push(b.success);if(b.success=function(a,c,d){for(var e=b.context||this,f=0,g=s.length;g>f;f++)s[f].apply(e,[a,c,d||l,l])},b.error){var u=b.error;b.error=function(a,c,d){var e=b.context||this;u.apply(e,[a,c,d,l])}}if(b.complete){var v=b.complete;b.complete=function(a,c){var d=b.context||this;v.apply(d,[a,c,l])}}var w=a("input[type=file]:enabled",this).filter(function(){return""!==a(this).val()}),x=w.length>0,y="multipart/form-data",z=l.attr("enctype")==y||l.attr("encoding")==y,A=e.fileapi&&e.formdata;d("fileAPI :"+A);var B,C=(x||z)&&!A;b.iframe!==!1&&(b.iframe||C)?b.closeKeepAlive?a.get(b.closeKeepAlive,function(){B=h(q)}):B=h(q):B=(x||z)&&A?g(q):a.ajax(b),l.removeData("jqxhr").data("jqxhr",B);for(var D=0;D<p.length;D++)p[D]=null;return this.trigger("form-submit-notify",[this,b]),this},a.fn.ajaxForm=function(e){if(e=e||{},e.delegation=e.delegation&&a.isFunction(a.fn.on),!e.delegation&&0===this.length){var f={s:this.selector,c:this.context};return!a.isReady&&f.s?(d("DOM not ready, queuing ajaxForm"),a(function(){a(f.s,f.c).ajaxForm(e)}),this):(d("terminating; zero elements found by selector"+(a.isReady?"":" (DOM not ready)")),this)}return e.delegation?(a(document).off("submit.form-plugin",this.selector,b).off("click.form-plugin",this.selector,c).on("submit.form-plugin",this.selector,e,b).on("click.form-plugin",this.selector,e,c),this):this.ajaxFormUnbind().bind("submit.form-plugin",e,b).bind("click.form-plugin",e,c)},a.fn.ajaxFormUnbind=function(){return this.unbind("submit.form-plugin click.form-plugin")},a.fn.formToArray=function(b,c){var d=[];if(0===this.length)return d;var f,g=this[0],h=this.attr("id"),i=b?g.getElementsByTagName("*"):g.elements;if(i&&!/MSIE 8/.test(navigator.userAgent)&&(i=a(i).get()),h&&(f=a(":input[form="+h+"]").get(),f.length&&(i=(i||[]).concat(f))),!i||!i.length)return d;var j,k,l,m,n,o,p;for(j=0,o=i.length;o>j;j++)if(n=i[j],l=n.name,l&&!n.disabled)if(b&&g.clk&&"image"==n.type)g.clk==n&&(d.push({name:l,value:a(n).val(),type:n.type}),d.push({name:l+".x",value:g.clk_x},{name:l+".y",value:g.clk_y}));else if(m=a.fieldValue(n,!0),m&&m.constructor==Array)for(c&&c.push(n),k=0,p=m.length;p>k;k++)d.push({name:l,value:m[k]});else if(e.fileapi&&"file"==n.type){c&&c.push(n);var q=n.files;if(q.length)for(k=0;k<q.length;k++)d.push({name:l,value:q[k],type:n.type});else d.push({name:l,value:"",type:n.type})}else null!==m&&"undefined"!=typeof m&&(c&&c.push(n),d.push({name:l,value:m,type:n.type,required:n.required}));if(!b&&g.clk){var r=a(g.clk),s=r[0];l=s.name,l&&!s.disabled&&"image"==s.type&&(d.push({name:l,value:r.val()}),d.push({name:l+".x",value:g.clk_x},{name:l+".y",value:g.clk_y}))}return d},a.fn.formSerialize=function(b){return a.param(this.formToArray(b))},a.fn.fieldSerialize=function(b){var c=[];return this.each(function(){var d=this.name;if(d){var e=a.fieldValue(this,b);if(e&&e.constructor==Array)for(var f=0,g=e.length;g>f;f++)c.push({name:d,value:e[f]});else null!==e&&"undefined"!=typeof e&&c.push({name:this.name,value:e})}}),a.param(c)},a.fn.fieldValue=function(b){for(var c=[],d=0,e=this.length;e>d;d++){var f=this[d],g=a.fieldValue(f,b);null===g||"undefined"==typeof g||g.constructor==Array&&!g.length||(g.constructor==Array?a.merge(c,g):c.push(g))}return c},a.fieldValue=function(b,c){var d=b.name,e=b.type,f=b.tagName.toLowerCase();if(void 0===c&&(c=!0),c&&(!d||b.disabled||"reset"==e||"button"==e||("checkbox"==e||"radio"==e)&&!b.checked||("submit"==e||"image"==e)&&b.form&&b.form.clk!=b||"select"==f&&-1==b.selectedIndex))return null;if("select"==f){var g=b.selectedIndex;if(0>g)return null;for(var h=[],i=b.options,j="select-one"==e,k=j?g+1:i.length,l=j?g:0;k>l;l++){var m=i[l];if(m.selected){var n=m.value;if(n||(n=m.attributes&&m.attributes.value&&!m.attributes.value.specified?m.text:m.value),j)return n;h.push(n)}}return h}return a(b).val()},a.fn.clearForm=function(b){return this.each(function(){a("input,select,textarea",this).clearFields(b)})},a.fn.clearFields=a.fn.clearInputs=function(b){var c=/^(?:color|date|datetime|email|month|number|password|range|search|tel|text|time|url|week)$/i;return this.each(function(){var d=this.type,e=this.tagName.toLowerCase();c.test(d)||"textarea"==e?this.value="":"checkbox"==d||"radio"==d?this.checked=!1:"select"==e?this.selectedIndex=-1:"file"==d?/MSIE/.test(navigator.userAgent)?a(this).replaceWith(a(this).clone(!0)):a(this).val(""):b&&(b===!0&&/hidden/.test(d)||"string"==typeof b&&a(this).is(b))&&(this.value="")})},a.fn.resetForm=function(){return this.each(function(){("function"==typeof this.reset||"object"==typeof this.reset&&!this.reset.nodeType)&&this.reset()})},a.fn.enable=function(a){return void 0===a&&(a=!0),this.each(function(){this.disabled=!a})},a.fn.selected=function(b){return void 0===b&&(b=!0),this.each(function(){var c=this.type;if("checkbox"==c||"radio"==c)this.checked=b;else if("option"==this.tagName.toLowerCase()){var d=a(this).parent("select");b&&d[0]&&"select-one"==d[0].type&&d.find("option").selected(!1),this.selected=b}})},a.fn.ajaxSubmit.debug=!1});
(function(n,t){"use strict";function w(){}function u(n,t){if(n){typeof n=="object"&&(n=[].slice.call(n));for(var i=0,r=n.length;i<r;i++)t.call(n,n[i],i)}}function it(n,i){var r=Object.prototype.toString.call(i).slice(8,-1);return i!==t&&i!==null&&r===n}function s(n){return it("Function",n)}function a(n){return it("Array",n)}function et(n){var i=n.split("/"),t=i[i.length-1],r=t.indexOf("?");return r!==-1?t.substring(0,r):t}function f(n){(n=n||w,n._done)||(n(),n._done=1)}function ot(n,t,r,u){var f=typeof n=="object"?n:{test:n,success:!t?!1:a(t)?t:[t],failure:!r?!1:a(r)?r:[r],callback:u||w},e=!!f.test;return e&&!!f.success?(f.success.push(f.callback),i.load.apply(null,f.success)):e||!f.failure?u():(f.failure.push(f.callback),i.load.apply(null,f.failure)),i}function v(n){var t={},i,r;if(typeof n=="object")for(i in n)!n[i]||(t={name:i,url:n[i]});else t={name:et(n),url:n};return(r=c[t.name],r&&r.url===t.url)?r:(c[t.name]=t,t)}function y(n){n=n||c;for(var t in n)if(n.hasOwnProperty(t)&&n[t].state!==l)return!1;return!0}function st(n){n.state=ft;u(n.onpreload,function(n){n.call()})}function ht(n){n.state===t&&(n.state=nt,n.onpreload=[],rt({url:n.url,type:"cache"},function(){st(n)}))}function ct(){var n=arguments,t=n[n.length-1],r=[].slice.call(n,1),f=r[0];return(s(t)||(t=null),a(n[0]))?(n[0].push(t),i.load.apply(null,n[0]),i):(f?(u(r,function(n){s(n)||!n||ht(v(n))}),b(v(n[0]),s(f)?f:function(){i.load.apply(null,r)})):b(v(n[0])),i)}function lt(){var n=arguments,t=n[n.length-1],r={};return(s(t)||(t=null),a(n[0]))?(n[0].push(t),i.load.apply(null,n[0]),i):(u(n,function(n){n!==t&&(n=v(n),r[n.name]=n)}),u(n,function(n){n!==t&&(n=v(n),b(n,function(){y(r)&&f(t)}))}),i)}function b(n,t){if(t=t||w,n.state===l){t();return}if(n.state===tt){i.ready(n.name,t);return}if(n.state===nt){n.onpreload.push(function(){b(n,t)});return}n.state=tt;rt(n,function(){n.state=l;t();u(h[n.name],function(n){f(n)});o&&y()&&u(h.ALL,function(n){f(n)})})}function at(n){n=n||"";var t=n.split("?")[0].split(".");return t[t.length-1].toLowerCase()}function rt(t,i){function e(t){t=t||n.event;u.onload=u.onreadystatechange=u.onerror=null;i()}function o(f){f=f||n.event;(f.type==="load"||/loaded|complete/.test(u.readyState)&&(!r.documentMode||r.documentMode<9))&&(n.clearTimeout(t.errorTimeout),n.clearTimeout(t.cssTimeout),u.onload=u.onreadystatechange=u.onerror=null,i())}function s(){if(t.state!==l&&t.cssRetries<=20){for(var i=0,f=r.styleSheets.length;i<f;i++)if(r.styleSheets[i].href===u.href){o({type:"load"});return}t.cssRetries++;t.cssTimeout=n.setTimeout(s,250)}}var u,h,f;i=i||w;h=at(t.url);h==="css"?(u=r.createElement("link"),u.type="text/"+(t.type||"css"),u.rel="stylesheet",u.href=t.url,t.cssRetries=0,t.cssTimeout=n.setTimeout(s,500)):(u=r.createElement("script"),u.type="text/"+(t.type||"javascript"),u.src=t.url);u.onload=u.onreadystatechange=o;u.onerror=e;u.async=!1;u.defer=!1;t.errorTimeout=n.setTimeout(function(){e({type:"timeout"})},7e3);f=r.head||r.getElementsByTagName("head")[0];f.insertBefore(u,f.lastChild)}function vt(){for(var t,u=r.getElementsByTagName("script"),n=0,f=u.length;n<f;n++)if(t=u[n].getAttribute("data-headjs-load"),!!t){i.load(t);return}}function yt(n,t){var v,p,e;return n===r?(o?f(t):d.push(t),i):(s(n)&&(t=n,n="ALL"),a(n))?(v={},u(n,function(n){v[n]=c[n];i.ready(n,function(){y(v)&&f(t)})}),i):typeof n!="string"||!s(t)?i:(p=c[n],p&&p.state===l||n==="ALL"&&y()&&o)?(f(t),i):(e=h[n],e?e.push(t):e=h[n]=[t],i)}function e(){if(!r.body){n.clearTimeout(i.readyTimeout);i.readyTimeout=n.setTimeout(e,50);return}o||(o=!0,vt(),u(d,function(n){f(n)}))}function k(){r.addEventListener?(r.removeEventListener("DOMContentLoaded",k,!1),e()):r.readyState==="complete"&&(r.detachEvent("onreadystatechange",k),e())}var r=n.document,d=[],h={},c={},ut="async"in r.createElement("script")||"MozAppearance"in r.documentElement.style||n.opera,o,g=n.head_conf&&n.head_conf.head||"head",i=n[g]=n[g]||function(){i.ready.apply(null,arguments)},nt=1,ft=2,tt=3,l=4,p;if(r.readyState==="complete")e();else if(r.addEventListener)r.addEventListener("DOMContentLoaded",k,!1),n.addEventListener("load",e,!1);else{r.attachEvent("onreadystatechange",k);n.attachEvent("onload",e);p=!1;try{p=!n.frameElement&&r.documentElement}catch(wt){}p&&p.doScroll&&function pt(){if(!o){try{p.doScroll("left")}catch(t){n.clearTimeout(i.readyTimeout);i.readyTimeout=n.setTimeout(pt,50);return}e()}}()}i.load=i.js=ut?lt:ct;i.test=ot;i.ready=yt;i.ready(r,function(){y()&&u(h.ALL,function(n){f(n)});i.feature&&i.feature("domloaded",!0)})})(window);

function xsend(formid) {
    if(typeof formid === 'undefined') formid = "postform";
    $('#'+formid).ajaxSubmit({
        success: function(responseText, statusText, xhr, $form) {
            var resp = $('<html></html>').append(responseText);
            if(resp.find('h1').text() !== '') {
                popupMessage(resp.find('h2').text());
                clearfields($(formid), true);
            }
            else if(resp.find('.big-shit').text() !== '') {
                if(resp.find('.big-shit').text() === 'Вы забанены!') {
                    window.location.href = ku_cgipath + "/banned.php";
                }
                popupMessage(resp.find('.big-shit').text());
                clearfields($(formid), true);
            }
        } 
    }); 
    return false;
}

function clearfields($form, onlycaptcha) {
    if(typeof onlycaptcha === 'undefined') onlycaptcha = false;
    if(!onlycaptcha) {
        $form.find('[name="message"]').val('');
        $form.find('[name="captcha"]').val('');
        $form.find('[name="subject"]').val('');
        $form.find('[name="name"]').val('');
        $form.find('[name="embed"]').val('');
        $form.find('[name="token"]').val(randomString());
    }
    if(!dcxt.enabled) {
        $('.captchawrap').stop();
        clearTimeout(rottencaptcha);
        rotCaptcha();
    }
}

function injectCSS(rule, order) {
    iter(document.styleSheets, function(sheet) {
        if(sheet.href && sheet.href.search(ku_boardspath+'/css/')===0 && sheet.href.split('/css/')[1].split('?')[0] === injectDestination) {
            if(rule instanceof Array) {
                iter(rule, function(ro) {
                    sheet.insertRule(ro[0], ro[1]);
                })
            }
            else { sheet.insertRule(rule, order); }
        }
    });
}

function removeCSS(order) {
    iter(document.styleSheets, function(sheet) {
        if(sheet.href && (new RegExp(injectDestination).test(sheet.href))) {
            if(order instanceof Array) {
                iter(order, function(o) {
                    sheet.deleteRule(o);
                })
            }
            else { sheet.deleteRule(order); } 
        }
    });
}

function randomString() {
    var result = '', chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', length = 10;
    for (var i = length; i > 0; --i) result += chars[Math.round(Math.random() * (chars.length - 1))];
    return result;
}

var cloud20 = {
    init: function() {
        $.getJSON(ku_cgipath + '/boards20.json', function(data) {
            cloud20.allboards = data;
            cloud20.filter('');
        });
    },
    filter: function(query) {
        var res = [];
        if(typeof this.allboards === "undefined") return;
        if(query == '') res = this.allboards;
        else {
            query = query.toLowerCase();
            iter(this.allboards, function(board) {
                if(board.name.toLowerCase().search(query) !== -1 || board.desc.toLowerCase().search(query) !== -1)
                    res.push(board);
            });
        }
        this.display(res);
    },
    display: function(list) {
        var newhtml = '', opts = '';
        iter(list, function(item) {
            newhtml += '<a class="menu-item" title="'+ item.desc +'" href="/'+item.name+'/">/'+item.name+'/ - '+ item.desc +'</a>';
            opts += '<option value="'+item.name+'">/'+item.name+'/ - '+ item.desc +'</option>';
        });
        $('#boards20').html(newhtml);
        $('.boardsel20').html(opts);
    }
}

var countries = {
    'A1': "Anonymous Proxy",
    'A2': "Satellite Provider",
    'O1': "Other Country",
    'AD': "Andorra",
    'AE': "United Arab Emirates",
    'AF': "Afghanistan",
    'AG': "Antigua and Barbuda",
    'AI': "Anguilla",
    'AL': "Albania",
    'AM': "Armenia",
    'AO': "Angola",
    'AP': "Asia/Pacific Region",
    'AQ': "Antarctica",
    'AR': "Argentina",
    'AS': "American Samoa",
    'AT': "Austria",
    'AU': "Australia",
    'AW': "Aruba",
    'AX': "Aland Islands",
    'AZ': "Azerbaijan",
    'BA': "Bosnia and Herzegovina",
    'BB': "Barbados",
    'BD': "Bangladesh",
    'BE': "Belgium",
    'BF': "Burkina Faso",
    'BG': "Bulgaria",
    'BH': "Bahrain",
    'BI': "Burundi",
    'BJ': "Benin",
    'BL': "Saint Bartelemey",
    'BM': "Bermuda",
    'BN': "Brunei Darussalam",
    'BO': "Bolivia",
    'BQ': "Bonaire, Saint Eustatius and Saba",
    'BR': "Brazil",
    'BS': "Bahamas",
    'BT': "Bhutan",
    'BV': "Bouvet Island",
    'BW': "Botswana",
    'BY': "Belarus",
    'BZ': "Belize",
    'CA': "Canada",
    'CC': "Cocos (Keeling) Islands",
    'CD': "Congo, The Democratic Republic of the",
    'CF': "Central African Republic",
    'CG': "Congo",
    'CH': "Switzerland",
    'CI': "Cote d'Ivoire",
    'CK': "Cook Islands",
    'CL': "Chile",
    'CM': "Cameroon",
    'CN': "China",
    'CO': "Colombia",
    'CR': "Costa Rica",
    'CU': "Cuba",
    'CV': "Cape Verde",
    'CW': "Curacao",
    'CX': "Christmas Island",
    'CY': "Cyprus",
    'CZ': "Czech Republic",
    'DE': "Germany",
    'DJ': "Djibouti",
    'DK': "Denmark",
    'DM': "Dominica",
    'DO': "Dominican Republic",
    'DZ': "Algeria",
    'EC': "Ecuador",
    'EE': "Estonia",
    'EG': "Egypt",
    'EH': "Western Sahara",
    'ER': "Eritrea",
    'ES': "Spain",
    'ET': "Ethiopia",
    'EU': "Europe",
    'FI': "Finland",
    'FJ': "Fiji",
    'FK': "Falkland Islands (Malvinas)",
    'FM': "Micronesia, Federated States of",
    'FO': "Faroe Islands",
    'FR': "France",
    'GA': "Gabon",
    'GB': "United Kingdom",
    'GD': "Grenada",
    'GE': "Georgia",
    'GF': "French Guiana",
    'GG': "Guernsey",
    'GH': "Ghana",
    'GI': "Gibraltar",
    'GL': "Greenland",
    'GM': "Gambia",
    'GN': "Guinea",
    'GP': "Guadeloupe",
    'GQ': "Equatorial Guinea",
    'GR': "Greece",
    'GS': "South Georgia and the South Sandwich Islands",
    'GT': "Guatemala",
    'GU': "Guam",
    'GW': "Guinea-Bissau",
    'GY': "Guyana",
    'HK': "Hong Kong",
    'HM': "Heard Island and McDonald Islands",
    'HN': "Honduras",
    'HR': "Croatia",
    'HT': "Haiti",
    'HU': "Hungary",
    'ID': "Indonesia",
    'IE': "Ireland",
    'IL': "Israel",
    'IM': "Isle of Man",
    'IN': "India",
    'IO': "British Indian Ocean Territory",
    'IQ': "Iraq",
    'IR': "Iran, Islamic Republic of",
    'IS': "Iceland",
    'IT': "Italy",
    'JE': "Jersey",
    'JM': "Jamaica",
    'JO': "Jordan",
    'JP': "Japan",
    'KE': "Kenya",
    'KG': "Kyrgyzstan",
    'KH': "Cambodia",
    'KI': "Kiribati",
    'KM': "Comoros",
    'KN': "Saint Kitts and Nevis",
    'KP': "Korea, Democratic People's Republic of",
    'KR': "Korea, Republic of",
    'KW': "Kuwait",
    'KY': "Cayman Islands",
    'KZ': "Kazakhstan",
    'LA': "Lao People's Democratic Republic",
    'LB': "Lebanon",
    'LC': "Saint Lucia",
    'LI': "Liechtenstein",
    'LK': "Sri Lanka",
    'LR': "Liberia",
    'LS': "Lesotho",
    'LT': "Lithuania",
    'LU': "Luxembourg",
    'LV': "Latvia",
    'LY': "Libyan Arab Jamahiriya",
    'MA': "Morocco",
    'MC': "Monaco",
    'MD': "Moldova, Republic of",
    'ME': "Montenegro",
    'MF': "Saint Martin",
    'MG': "Madagascar",
    'MH': "Marshall Islands",
    'MK': "Macedonia",
    'ML': "Mali",
    'MM': "Myanmar",
    'MN': "Mongolia",
    'MO': "Macao",
    'MP': "Northern Mariana Islands",
    'MQ': "Martinique",
    'MR': "Mauritania",
    'MS': "Montserrat",
    'MT': "Malta",
    'MU': "Mauritius",
    'MV': "Maldives",
    'MW': "Malawi",
    'MX': "Mexico",
    'MY': "Malaysia",
    'MZ': "Mozambique",
    'NA': "Namibia",
    'NC': "New Caledonia",
    'NE': "Niger",
    'NF': "Norfolk Island",
    'NG': "Nigeria",
    'NI': "Nicaragua",
    'NL': "Netherlands",
    'NO': "Norway",
    'NP': "Nepal",
    'NR': "Nauru",
    'NU': "Niue",
    'NZ': "New Zealand",
    'OM': "Oman",
    'PA': "Panama",
    'PE': "Peru",
    'PF': "French Polynesia",
    'PG': "Papua New Guinea",
    'PH': "Philippines",
    'PK': "Pakistan",
    'PL': "Poland",
    'PM': "Saint Pierre and Miquelon",
    'PN': "Pitcairn",
    'PR': "Puerto Rico",
    'PS': "Palestinian Territory",
    'PT': "Portugal",
    'PW': "Palau",
    'PY': "Paraguay",
    'QA': "Qatar",
    'RE': "Reunion",
    'RO': "Romania",
    'RS': "Serbia",
    'RU': "Russian Federation",
    'RW': "Rwanda",
    'SA': "Saudi Arabia",
    'SB': "Solomon Islands",
    'SC': "Seychelles",
    'SD': "Sudan",
    'SE': "Sweden",
    'SG': "Singapore",
    'SH': "Saint Helena",
    'SI': "Slovenia",
    'SJ': "Svalbard and Jan Mayen",
    'SK': "Slovakia",
    'SL': "Sierra Leone",
    'SM': "San Marino",
    'SN': "Senegal",
    'SO': "Somalia",
    'SR': "Suriname",
    'SS': "South Sudan",
    'ST': "Sao Tome and Principe",
    'SV': "El Salvador",
    'SX': "Sint Maarten",
    'SY': "Syrian Arab Republic",
    'SZ': "Swaziland",
    'TC': "Turks and Caicos Islands",
    'TD': "Chad",
    'TF': "French Southern Territories",
    'TG': "Togo",
    'TH': "Thailand",
    'TJ': "Tajikistan",
    'TK': "Tokelau",
    'TL': "Timor-Leste",
    'TM': "Turkmenistan",
    'TN': "Tunisia",
    'TO': "Tonga",
    'TR': "Turkey",
    'TT': "Trinidad and Tobago",
    'TV': "Tuvalu",
    'TW': "Taiwan",
    'TZ': "Tanzania, United Republic of",
    'UA': "Ukraine",
    'UG': "Uganda",
    'UM': "United States Minor Outlying Islands",
    'US': "United States",
    'UY': "Uruguay",
    'UZ': "Uzbekistan",
    'VA': "Holy See (Vatican City State)",
    'VC': "Saint Vincent and the Grenadines",
    'VE': "Venezuela",
    'VG': "Virgin Islands, British",
    'VI': "Virgin Islands, U.S.",
    'VN': "Vietnam",
    'VU': "Vanuatu",
    'WF': "Wallis and Futuna",
    'WS': "Samoa",
    'YE': "Yemen",
    'YT': "Mayotte",
    'ZA': "South Africa",
    'ZM': "Zambia",
    'ZW': "Zimbabwe",
    'XX': "OMCK"
}

var bnrs = {
    initiated: false,
    init: function() {
        $.getJSON(ku_boardspath+'/bnrs.json', function(data) { 
            var reduced = [];
            if(data.length > 1) {
                iter(data, function(bnr) {
                    if(bnr.link !== this_board_dir) reduced.push(bnr);
                });
            }
            else reduced = data;
            bnrs.data = reduced;
            bnrs.initiated = true;
            bnrs.display();
        });
    },
    display: function() {
        if(!this.initiated) return;
        if(!this.data.length) return;
        var reduced = [];
        if(typeof this.current !== 'undefined') {
            iter(this.data, function(item) {
                if(item.path !== bnrs.current) reduced.push(item)
            });
        }
        else reduced = this.data;
        var toDisplay = randomItem(reduced);
        this.current = toDisplay.path;
        var link = (toDisplay.link.indexOf('http') === (-1)) ? ku_boardspath+'/'+toDisplay.link : toDisplay.link;
        var newhtml = '<a class="bnrsupdate" href="#" onclick="javascript:bnrs.display();return false;"></a><a href="'+link+'"><img src="'+ku_boardspath+'/images/bnrs/'+toDisplay.path+'" /></a>';
        if($('.bnr').length) {
            $('.bnr').html(newhtml);
        }
        else $('.logo').before('<div class="bnr-wrap"><div class="bnr">'+newhtml+'</div></div>');
    },
}

function getRandomInt (min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

function randomItem(array) {
    return array[getRandomInt(0, array.length-1)];
}