'use strict';

var style_cookie;
var style_cookie_txt;
var style_cookie_site;
var kumod_set=false;
var ispage;
var is_entering=false;

var _messages = {
  en: {
    noLocalStorage: "Your browser does not support LocalStorage",
    oops: "Something went wrong...",
    blankResponse: "blank response",
    expandingThread: "Expanding thread...",
    newThread: "new thread",
    NewThread: "New thread",
    replyTo: "reply to",
    cancel: "Cancel",
    update: "Update",
    updatingCounts: "Updating...",
    couldntFetch: "Could not fetch this post",
    noNewPosts: "No new posts",
    replies: "Replies",
    settings_fxEnabled: "Animation effects",
    settings_showReplies: "Show replies inside posts",
    settings_sfwMode: "NSFW mode",
    settings_expandImgFull: "Expand images to full size",
    settings_constrainWidth: "Constrain content width",
    enterCaptcha: "Please enter captcha.",
    selectText: "Select some text",
    dcls: "Double click to show source",
    watchOn: "Watch on",
    captcharot: "Captcha has rotted",
    threadUpdationAutomatically: "Tread is being updated automatically.",
    stopFuckingDolls: "<b>Отключите AJAX-отправку постов и AJAX-обновление треда.</b><br />(Кликните, чтобы закрыть)",
    del: "Delete",
    delandban: "Delete and ban",
    ban: "Ban",
    stickthread: "Stick thread",
    unstickthread: "Unstick thread",
    lockthread: "Lock thread",
    unlockthread: "Unlock thread",
    returnDesktop: "Switch to desktop interface",
    returnTouch: "Switch to touch interface",
    forceDesktop: "Force Desktop interface",
    okay: "Okay",
    captchalang: "Captcha language",
    reply: "Reply",
    imageDownscaledBy: "Image was downscaled by",
    videoDownscaledBy: "Video was downscaled by",
    toFit: "to feet screen",
    newReplies: "New replies",
    newThreadsAvailable: "New threads available.",
    loading: "Loading",
    anonymous: "Anonymous",
    sortBy: "Sort by",
    bumpOrder: "Bump order",
    lastReply: "Last reply",
    creationDate: "Creation date",
    replyCount: "Reply count",
    doStick: "Respect stickied",
    showHidden: "Show hidden",
    doNotStick: "Ignore stickied",
    hideHidden: "Hide hidden",
    search: "Search",
    threadOnPage: "Thread is on page",
    goToThread: "Go to thread",
    smallPics: "Small pictures",
    largePics: "Large pictures",
    legacyMode: "Legacy mode",
    threads: "Threads",
    comments: "Replies",
    active_since: "Active since",
    last_active: "Last seen",
    active_on: "Active on",
    tryAgain: "Try again",
    xhrError: "XHR error",
    details: "details",
    thread: "Thread",
    post: 'Post',
    posts: "Posts",
    deleted:'has been deleted',
    deletedMulti: 'have been deleted',
    report: 'Report',
    reported:'has been reported',
    reportedMulti: 'have been reported',
    fileRemoved: 'File<br>removed.',
    file: 'File',
    files: 'Files',
    collapse: 'Collapse',
    asMod: 'as Mod',
    asOP: 'as OP',
    historyEmpty: 'History is empty',
    noDataLoaded: 'No data was loaded',
    unable_load_20: 'Unable to load 2.0 boards list',
    captchaLangChanged: 'Captcha laguage changed',
    found: 'Found',
    links: 'Links',
    selectMultiple: 'Select multiple',
    selected: 'Selected',
    directLink: 'Direct link',
    quoteLink: 'Quote link',
    maxAttNumReached: 'Attachments number limit reached.',
    unsupportedFileType: 'unsupported file type',
    hideName: 'Hide file name',
    removeFile: 'Remove file',
    spoilerOnOff: 'Spoier On/Off',
    hidePost: 'Hide post',
    unhidePost: 'Unhide post',
    settings_hideCompletely: 'Hide items completely',
    pinUnpin: 'Pin/Unpin',
    hideForm: 'Hide form',
    collapseExpand: 'Collapse/Expand',
    notEmpty: 'Not empty',
    showName: 'Show/hide name/trip field',
    showSubject: 'Show/hide subject field',
    showEmbeds: 'Show/hide embeds',
    showPassword: 'Show/hide password field',
    showTTL: 'Show/hide time-to-live field',
    attachFile: 'Attach file...',
    classicSimplifiedForm: 'Classic/simplified form',
    showMarkup: 'Show/hide markup',
    refreshCatalog: 'Refresh catalog',
    cancelTimer: 'Cancel timer',
    saved:'saved from deletion',
    savedMulti: 'saved from deletion',
    password: 'Password',
    captcha: 'Captcha',
    captchaImage: 'Captcha image',
    refreshCaptcha: 'Refresh captcha',
    showCaptcha: 'Show captcha',
    captchaExpired: 'Captcha has expired.',
    mod_: 'Mod.'
  },
  ru: {
    noLocalStorage: "localStorage не поддерживается браузером",
    oops: "Что-то пошло не так...",
    blankResponse: "пустой ответ",
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
    settings_constrainWidth: "Ограничивать ширину контента",
    enterCaptcha: "Пожалуйста, введите капчу.",
    selectText: "Текст не выделен",
    dcls: "Double click to show source",
    watchOn: "Смотреть на",
    odc: "javascript:LatexIT.replaceWithSrc(this);",
    captcharot: "Капча протухла",
    threadUpdationAutomatically: "Тред обновляется автоматически",
    stopFuckingDolls: "<b>Отключите AJAX-отправку постов и AJAX-обновление треда.</b><br />(Кликните, чтобы закрыть)",
    del: "Удалить",
    delandban: "Удалить и забанить",
    ban: "Забанить",
    stickthread: "Прикрепить тред",
    unstickthread: "Отлепить тред",
    lockthread: "Закрыть тред",
    unlockthread: "Открыть тред",
    returnDesktop: "Переключиться на десктопный интерфейс",
    returnTouch: "Переключиться на тач-интерфейс",
    forceDesktop: "Force Desktop interface",
    okay: "Ясно",
    captchalang: "Язык капчи",
    reply: "Ответить",
    imageDownscaledBy: "Картинка ужата на",
    videoDownscaledBy: "Видео ужато на",
    toFit: "по размеру окна",
    newReplies: "Новых ответов",
    newThreadsAvailable: "Доступны новые треды.",
    loading: "Загружаем",
    anonymous: "Аноним",
    sortBy: "Сортировать по",
    bumpOrder: "бампам",
    lastReply: "дате ответов",
    creationDate: "дате создания",
    replyCount: "числу ответов",
    doStick: "Прикреплять",
    showHidden: "Показывать скрытые",
    doNotStick: "Не прикреплять",
    hideHidden: "Скрывать скрытые",
    search: "Поиск",
    threadOnPage: "Тред располагается на странице",
    goToThread: "Перейти в тред",
    smallPics: "Мелкие картинки",
    largePics: "Большие картинки",
    legacyMode: "Олдфажный режим",
    threads: "Тредов",
    comments: "Ответов",
    active_since: "Присоединился",
    last_active: "Был активен",
    active_on: "Активен на",
    tryAgain: "Попробовать снова",
    xhrError: "Ошибка XHR",
    details: "подробности",
    thread: "Тред",
    post: 'Пост',
    posts: 'Посты',
    deleted:'удален',
    deletedMulti: 'удалены',
    report: 'Пожаловаться',
    reported:': жалоба отправлена',
    reportedMulti: ': жалобы отправлены',
    fileRemoved: 'Файл<br>удален.',
    file: 'Файл',
    files: 'Файлы',
    collapse: 'Свернуть',
    asMod: 'от лица модератора',
    asOP: 'от лица ОПа',
    historyEmpty: 'История пуста',
    noDataLoaded: 'Данные не загружены',
    unable_load_20: 'Unable to load 2.0 boards list',
    captchaLangChanged: 'Язык капчи изменен',
    found: 'Найдено',
    links: 'Ссылки',
    selectMultiple: 'Выбрать несколько',
    selected: 'Выбрано',
    directLink: 'Прямая ссылка на пост',
    quoteLink: 'Ссылка для цитирования',
    maxAttNumReached: 'Превышено допустимое число вложений.',
    unsupportedFileType: 'недопустимый тип файла',
    hideName: 'Скрыть имя файла',
    removeFile: 'Удалить файл',
    spoilerOnOff: 'Спойлер Вкл/Выкл',
    hidePost: 'Скрыть пост',
    unhidePost: 'Раскрыть пост',
    settings_hideCompletely: 'Скрывать треды и посты полностью',
    pinUnpin: 'Прикрепить/Открепить',
    hideForm: 'Скрыть форму',
    collapseExpand: 'Свернуть/Развернуть',
    notEmpty: 'Содержит данные',
    showName: 'Показать/скрыть поле имени',
    showSubject: 'Показать/скрыть поле темы',
    showEmbeds: 'Показать/скрыть вложения',
    showPassword: 'Показать/скрыть поле ввода пароля',
    showTTL: 'Показать/скрыть время жизни',
    attachFile: 'Прикрепить файл...',
    classicSimplifiedForm: 'Упрощенная/классическая форма',
    showMarkup: 'Показать/скрыть разметку',
    refreshCatalog: 'Обновить каталог',
    cancelTimer: 'Отменить таймер',
    saved:'спасен от удаления',
    savedMulti: 'спасены от удаления',
    password: 'Пароль',
    captcha: 'Captcha',
    captchaImage: 'Captcha image',
    refreshCaptcha: 'Обновить капчу',
    showCaptcha: 'Показать капчу',
    captchaExpired: 'Капча протухла.',
    mod_: 'Мод.'
  }
}
var _l = (typeof locale !== 'undefined' && _messages.hasOwnProperty(locale)) ? _messages[locale] : _messages.ru;

var onReady = {
  pushTask: function(fn) {
    if (this.isReady)
      fn()
    else
      this.tasks.push(fn)
  },
  tasks: [],
  ready: function() {
    this.isReady = true
    this.tasks.forEach(fn=>fn())
  }
}

/* IE/Opera fix, because they need to go learn a book on how to use indexOf with arrays */ // Is this still relevant tho?
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
    var string = "", i = 0, c = 0, c1 = 0, c2 = 0, c3 = 0
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
    // with(document.cookie) {
      var regexp=new RegExp("(^|;\\s+)"+name+"=(.*?)(;|$)");
      var hit=regexp.exec(document.cookie);
      if(hit&&hit.length>2) return Utf8.decode(unescape(replaceAll(hit[2],'+','%20')));
      else return '';
    // }
  } else {
    var value = arguments[1]
    , days = arguments[2]
    , expires=""
    if(days) {
      var date=new Date();
      date.setTime(date.getTime()+(days*24*60*60*1000));
      expires="; expires="+date.toGMTString();
    }
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

var _lastActiveForm = false
Object.defineProperty(window, 'lastActiveForm', {
  get: function() {
    if (!_lastActiveForm || !_lastActiveForm.getClientRects().length) {
      updateActiveForm()
    }
    return _lastActiveForm
  },
  set: function(el) {
    document.querySelectorAll('.qrf-active').forEach(a => a.classList.remove('qrf-active'))
    if (el) {
      let form = el._findParent('form') // _findParent retuns element itself if in matches the selector
      form.classList.add('qrf-active')
      if (form.classList.contains('collapsed')) {
        toggleFormCollapse(form, false/*← uncollapse*/, false/*← no focus*/)
      }
      _lastActiveForm  = form
    }
    else
      _lastActiveForm = false
  }
})
function updateActiveForm() {
  let floating = document.querySelector('.qrf-floating:not(.collapsed)')
  if (floating) {
    lastActiveForm = floating
    return
  }
  lastActiveForm = findClosest('.qrf-floating.collapsed, .quick-reply-form textarea')
  return
}

function findClosest(selector) {
  let height = window.innerHeight
  , hh = height / 2
  , list = document.querySelectorAll(selector)
  if (!list) return false
  let closest = [].map.call(list, el => {
    let rect = el.getClientRects()
    , offset = rect.length
      ? Math.abs(hh - (rect[0].y + rect[0].height/2))
      : null
    return { el: el, offset: offset }
  })
  .filter(a => a.offset !== null)
  .sort((e1, e2) => e1.offset < e2.offset ? -1 : 1)
  return closest.length ? closest[0].el : false
}

function insert(text, breakBefore=true, breakAfter=true) {
  let form = lastActiveForm || document.querySelector('#postform')
  , textarea = form && form.querySelector('textarea')
  if(textarea) {
    if (form.classList.contains('collapsed')) {
      toggleFormCollapse(form, false/*← uncollapse*/, false/*← no focus*/)
    }
    let start = textarea.selectionStart
    , end = textarea.selectionEnd
    , pre = textarea.value.substr(0,start)
    , post = textarea.value.substr(end)
    , charBefore = pre[pre.length-1]
    , charAfter = post[0]
    if (breakBefore && text[0] !== "\n" && charBefore && charBefore !== "\n")
      text = "\n"+text
    if (breakAfter && text[text.length-1] !== "\n" && charAfter !== "\n")
      text += "\n"
    let selectionRange = start + text.length
    textarea.value = pre + text + post
    textarea.setSelectionRange(selectionRange, selectionRange)
    textarea.focus()
  }
}

function markup($target, start, end, istart, iend) {
  let element = $target.find('textarea').get(0);
  if (element.selectionStart || element.selectionStart == '0') {
    element.focus();
    let startPos = element.selectionStart
    , endPos = element.selectionEnd
    , selected = element.value.substring(startPos, endPos);
    if(selected.indexOf('\n') === (-1) && typeof istart !== "undefined" && typeof iend !== "undefined") {
      start = istart; end = iend;
    }
    element.value = element.value.substring(0, startPos) + start + element.value.substring(startPos, endPos) + end + element.value.substring(endPos, element.value.length);
  } else {
    element.value += start + end;
  }
}

function bullets($target, bullet, istart, iend) {
  let $area = $target.find('textarea')
  , element = $area[0]
  , startPos = element.selectionStart
  , endPos = element.selectionEnd
  , selected = $area.val().substring(startPos, endPos)
  if(!~selected.indexOf('\n') && typeof istart !== "undefined" && typeof iend !== "undefined") {
    element.value = element.value.substring(0, startPos) + istart + element.value.substring(startPos, endPos) + iend + element.value.substring(endPos, element.value.length);
  }
  else {
    selected = $area.val().substring(startPos, endPos).split('\n');
    var newtxt = "";
    for(var i=0; i<selected.length; i++) {
      newtxt += (bullet + selected[i]);
      if(i < (selected.length - 1)) newtxt += '\n';
    }
    $area.val(
      $area.val().substring(0, startPos)
      + newtxt +
      $area.val().substring(endPos)
    );
  }
}

function quotes($target, type) {
  let $area = $target.find('textarea')
  , areaVal = $area.val()
  , element = $area[0]
  , startPos = element.selectionStart
  , endPos = element.selectionEnd
  , selected = areaVal.substring(startPos, endPos)
  , newtxt = ""
  if (~selected.indexOf('\n')) { // multiline quoting
    selected = areaVal.substring(startPos, endPos).split('\n')
    for(var i=0; i<selected.length; i++) {
      let bullet = (type=='g') ? '>'
        : (type=='r') ? '<'
          : (i%2) ? '<'
            : '>'
      newtxt += (bullet + ' ' + selected[i])
      if(i < (selected.length - 1)) newtxt += '\n'
    }
  }
  else { // inline quoting
    let tag = type=='r' ? 'rq' : 'q'
    newtxt = `[${tag}]${selected}[/${tag}]`
  }
  element.value =
    areaVal.substring(0, startPos)
    + newtxt +
    areaVal.substring(endPos)
}

function quote(b, a) {
  var v = eval("document." + a + ".message");
  v.value += ">>" + b + "\n";
  v.focus();
}

function checkhighlight() {
  var match;
  if(match=/#i([0-9]+)/.exec(document.location.toString()))
    if(!$('#postform textarea').val())
      insert(">>"+match[1]);
  if(match=/#([0-9]+)/.exec(document.location.toString()))
    highlight(match[1]);
}

function highlight(id, offTimeout=5000) {
  $('.highlight').removeClass('highlight');
  if (!(''+id).split('-')[1])
    id += '-'+this_board_dir
  let $post = $(`#postbody${id}`).parents('.reply')
  if (!$post.length) return false;
  let post = $post[0]
  $post.addClass('highlight');
  if (offTimeout) {
    setTimeout(() => $post.removeClass('highlight'), offTimeout)
  }
  // Scroll to post
  let bcr = post.getBoundingClientRect()
  , docHeight = document.documentElement.clientHeight
  if (bcr.bottom > docHeight || bcr.top < 0) {
    let postX = bcr.top + document.documentElement.scrollTop
    , spaceAround = docHeight - bcr.height
    window.scrollTo(0, postX - (spaceAround ? Math.ceil(spaceAround/2) : 0))
  }
  return true
}

const password_length = 20;

function get_password(name) {
  let pass = getCookie(name);
  if(pass) return pass;
  pass = randomString(password_length)
  Cookie(name, pass, 365);
  return(pass);
}

function togglePassword() {
  var passwordbox_html = $("#passwordbox").html().toLowerCase();
  var newhtml = '<td></td><td></td>';
  if (passwordbox_html == newhtml) {
    newhtml = '<td class="postblock">Mod</td><td><input type="text" name="modpassword" size="28" maxlength="75">&nbsp;<acronym title="Display staff status (Mod/Admin)">D</acronym>:&nbsp;<input type="checkbox" name="displaystaffstatus" checked>&nbsp;<acronym title="Lock">L</acronym>:&nbsp;<input type="checkbox" name="lockonpost">&nbsp;&nbsp;<acronym title="Sticky">S</acronym>:&nbsp;<input type="checkbox" name="stickyonpost">&nbsp;&nbsp;<acronym title="Raw HTML">RH</acronym>:&nbsp;<input type="checkbox" name="rawhtml">&nbsp;&nbsp;<acronym title="Name">N</acronym>:&nbsp;<input type="checkbox" name="usestaffname"></td>';
  }
  $("#passwordbox").html(newhtml).show();
  return false;
}

// proxied functions
function getCookie(name)                {   return Cookie(name)                     }
function set_cookie(name,value,days)    {   return Cookie(name,value,days)          }

var Styles = {
  all: [], titles: [],
  init: function() {
    _.each(document.getElementsByTagName("link"), function(link) {
      if(link.getAttribute("rel").indexOf("style")!=-1 && link.getAttribute("title")) {
        this.all.push(link);
        this.titles.push(link.getAttribute("title"));
        if(link.getAttribute("rel").indexOf("alternate")===-1) {
          this._default = link.getAttribute("title");
        }
        if(link.hasAttribute("data-custom")) {
          this.custom = link.getAttribute("title");
        }
      }
    }, this);
    this.current = this._default;
    var customBypass = getCookie('bypasscustom');
    this.customBypass = (customBypass.length && typeof this_board_dir !== 'undefined' && in_array(this_board_dir, customBypass.split('|'))) ? true : false;
    this.initiated = true
  },
  decide: function() {
    this.init();
    var testingCSS = LSfetchJSON('testing-css')
    if(testingCSS) {
      let title = this.addStyle(testingCSS.url, testingCSS.title)
      this.setStyle(title)
      var $clink = $('<a href="#">Отключить тестирование '+title+'.css</a>')
      $clink.click(function(ev) {
        ev.preventDefault()
        Styles.quitTest()
        $(this).parent().slideUp()
      })
      this.$cancelLink = $('<div style="font-weight: bold"></div>').append($clink)
      return
    }
    if(this.hasOwnProperty('custom') && !this.customBypass)
      return this.setCustom();
    var sc = getCookie(style_cookie);
    if(sc && in_array(sc, this.titles))
      this.setStyle(sc);
    else {
      this.setDefault();
      set_cookie("kustyle_site",this._default,365);
      set_cookie("kustyle",this._default,365);
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
    _.each(oldcookie, function(brd) {
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
    if(this.hasOwnProperty('_default') && this.current !== this._default)
      this.setStyle(this._default);
  },
  setCustom: function() {
    if(this.hasOwnProperty('custom'))
      this.setStyle(this.custom);
  },
  setStyle: function(stylename) {
    if(!in_array(stylename, this.titles)) return;
    if (scrollAnchor && scrollAnchor.save)
      scrollAnchor.save('setstyle', '.postnode')
    _.each(this.all, function(sheet) {
      sheet.disabled=true;    // Hello IE
      if(sheet.getAttribute("title") === stylename)
        sheet.disabled=false;
    });
    if (scrollAnchor && scrollAnchor.restore)
      scrollAnchor.restore('setstyle')
    this.current = stylename;
  },
  onTest: null,
  addStyle: function(url, title='') {
    if (! title) {
      let m = /(?:.+\/)?(.+)\.css/i.exec(url)
      if(!m) return;
      title = m[1]
    }
    title = _.capitalize(_.escape(title))
    if(!in_array(title, this.titles)) {
      var $link = $('<link rel="stylesheet" type="text/css" href="'+url+'" title="'+title+'" disabled>')
      $('head').append($link);
      this.titles.push(title)
      this.all.push($link[0])
    }
    return title
  },
  testStyle: function(url, title) {
    title = this.addStyle(url, title)
    this.setStyle(title)
    this.onTest = {
      url: url,
      title: title
    }
    pups.succ(`Установлен стиль ${title}.
      <div class="styletest-form">
        <button onclick="Styles.confirmLongTermTest()">OK</button>
        <button onclick="Styles.quitTest()">${_l.cancel}</button>
      </div>`, {time: 0, save: true})
  },
  confirmLongTermTest: function() {
    if(this.onTest)
      localStorage.setItem('testing-css', JSON.stringify(this.onTest))
  },
  quitTest: function() {
    localStorage.removeItem('testing-css')
    this.decide()
  }
}
if(style_cookie) Styles.decide();

var HiddenItems = {
  init: function() {
    this.hideCompletely = localStorage['hideCompletely.'+this_board_dir]=='true'
    ;['posts', 'threads'].forEach(kind => {
      let ls = localStorage[`hidden${_.capitalize(kind)}`]
      , list = ls ? ls.split(',').filter(a => a) : []
      this.lists[kind] = list
    })
    document.write(`<style id="injector:hideitems">${this.buildCSS()}</style>`)
  },
  buildCSS: function() {
    let unhide = [], hide = [], hiddenPosts = []
    ;['posts', 'threads'].forEach(kind => {
      this.lists[kind].forEach(id => {
        if (kind=="posts") {
          let node = `#postnode${id}`
          hiddenPosts.push(node)
          if (this.hideCompletely)
            hide.push(node)
          else
            hide.push(`#postbody${id}`)
        }
        else if(ispage) {
          hide.push(`#thread${id}`)
          if (this.hideCompletely) {
            hide.push(`#unhidethread${id}`)
            hide.push(`#thread${id} + br`)
            hide.push(`#thread${id} + br + hr`)
          }
          else {
            unhide.push(`#unhidethread${id}`)
          }
        }
      })
    })
    if (hiddenPosts.length) {
      onReady.pushTask(() => {
        $(hiddenPosts.join(',')).addClass('post-hidden')
      })
    }
    return (hide.length ? hide.join(',') + '{ display: none }' : '') +
      (unhide.length ? unhide.join(',') + '{ display: inline-block }' : '')
  },
  updateCSS: function() {
    injector.inject('hideitems', this.buildCSS())
  },
  hideCompletely: false,
  lists: {},
  isHidden: function(kind, id) {
    return this.lists[kind+'s'].indexOf(id.toString()) != -1 
  },
  hideItem: function(kind, id) {
    if (this.isHidden(kind, id)) return;
    this.lists[kind+'s'].push(id)
    this.saveList(kind)
  },
  unhideItem: function(kind, id) {
    if (!this.isHidden(kind, id)) return;
    this.lists[kind+'s'] = this.lists[kind+'s'].filter(a => a!=id)
    this.saveList(kind)
  },
  hideThread: function(id, inCatalog=false) {
    this.hideItem('thread', id)
    $('#unhidethread' + id).slideDown()
    let $thread = $('#thread' + id)
    , $form = $thread.find('.qrf-floating')
    $thread.slideUp().promise().done(updateActiveForm)
    if ($form.length)
      toggleFormFloating($form[0], false /* ← float=off*/, false /* ← no focus*/)
  },
  unhideThread: function(id, inCatalog=false) {
    this.unhideItem('thread', id)
    $('#thread' + id).slideDown()
    $('#unhidethread' + id).slideUp()
  },
  hidePost: function(id) {
    this.hideItem('post', id)
    $('#postbody' + id).slideUp('fast')
  },
  unhidePost: function(id) {
    this.unhideItem('post', id)
    $('#postbody' + id).slideDown('fast')
  },
  saveList: function(kind) {
    localStorage[`hidden${_.capitalize(kind)}s`] = this.lists[kind+'s'].join(',')
  }
}
HiddenItems.init()

function expandthread(threadid, board, ev) {
  ev.preventDefault()
  let $thread = $(`#thread${threadid}-${board}`)
  if ($thread.length) {
    let expandFrom = + $thread.find('.postnode.op').data('id')
    , expandTo = + $thread.find('.postnode:not(.op):first').data('id')
    , $omitted = $thread.find('.omittedposts')
    $omitted.find('a').hide()
    if (! $omitted.find('span').length) {
      $(`<div class="spinner"></div><span>${_l.loading}...</span>`).prependTo($omitted)
    }
    else {
      $omitted.find('.spinner').show()
    }
    HTMLoader.getThread(board, +threadid, [expandFrom, expandTo], (err, posts) => {
      if (posts) {
        $thread.find('.omittedposts').replaceWith(posts)
        replyMap.showReplies()
      }
      else {
        setTimeout(() => $omitted.find('.spinner').hide(), 1000)
        $omitted.find('span').text(_l.oops + (err !== false ? ` (${err})` : ''))
        $omitted.find('a').text(_l.tryAgain).show()
      }
    })
  }
  return false;
}

var newposts = {
  busy: false,
  get: function(options={}) {
    options = _.defaults(options, {
      threadid: null,
      expectedPost: null,
      silent: false,
      onError: err => pups.err(`Error geting new posts (${err})`),
      onSuccess: null,
      timestamp: null
    })
    if (!options.threadid) {
      if (ispage)
        return options.onError()
      options.threadid = +$('input[name=replythread]').val()
    }
    this.pushTask(options)
    return false
  },
  execute: function(task) {
    this.busy = true
    let threadid = ''+task.threadid
    , expectedPost = task.expectedPost
    , onError = err => {
      this.next()
      task.onError(err)
    }
    if (!threadid.split('-')[1])
      threadid += '-'+this_board_dir
    let replies_container, $newposts_get
    if (!ispage) {
      $newposts_get = $('#newposts_get')
      $newposts_get.removeClass('upd-counting').addClass('upd-updating')
      replies_container = document.querySelector('.replies')
    }
    else {
      replies_container = document.querySelector(`#thread${threadid} .replies`)
      if (!replies_container) return onError('No replies container!');
    }
    // If post is already there, skip the entire thing
    if (expectedPost && highlight(expectedPost)) {
      $newposts_get.removeClass('upd-updating')
      this.next()
      return
    }
    let $lastQrl = $(`.qrl[data-parent="${threadid}"]`).last()
    , lastpost = ($lastQrl.data('postnum') || $lastQrl.data('parent'))+''
    if (! lastpost) return onError();
    let idb = threadid.split('-')
    , id = idb[0], bd = idb[1]
    HTMLoader.getThread(bd, id, [+(lastpost.split('-')[0]),Infinity], (err, posts) => {
      if (!ispage) {
        $newposts_get.removeClass('upd-updating')
      }
      if (posts) {
        replies_container.insertAdjacentHTML('beforeend', posts)
        replyMap.showReplies()
        unreadCounter.refreshTimestamp(task.timestamp || null, bd)
        if (task.onSuccess)
          task.onSuccess()
      }
      else {
        if (!task.silent) {
          pups.info(posts === '' ? _l.noNewPosts : _l.oops + (err !== false ? ` (${err})` : ''), {time: 1})
        }
      }
      if (expectedPost && !highlight(expectedPost)) {
        return onError('No target reply!');
      }
      this.next()
    }, false, true)
  },
  pushTask: function(task) {
    if (this.busy)
      this.stack.push(task)
    else
      this.execute(task)
  },
  next: function() {
    let next = this.stack.shift()
    if (next)
      this.execute(next)
    else
      this.busy = false
  }, 
  stack: []
}

// Hiding animation
function flyTo(el, dest) {
  let destBox = dest.getBoundingClientRect()
  , destX = destBox.x + (destBox.width / 2)
  , destY = destBox.y + (destBox.height / 2)
  , elBox = el.getBoundingClientRect()
  el.style.transformOrigin = `${(destX - elBox.x) + 'px'} ${(destY - elBox.y) + 'px'}`
  el.classList.add('fly-to-zero')
  return new Promise((resolve, reject) => {
    setTimeout(() => {
      el.classList.remove('fly-to-zero')
      el.style.transformOrigin = null
      resolve(el)
    }, (+getComputedStyle(el).transition.match(/transform ([0-9\.]+)/)[1] * 1000) + 100 )
  })
}

function toggleFormCollapse(form, collapse = !form.classList.contains('collapsed'), focus = !collapse) {
  let oldBox = form.getBoundingClientRect()
  , offRight = oldBox.right - (window.innerWidth - 32)
  form.classList.toggle('collapsed', collapse)
  let box = form.getBoundingClientRect()
  , newWidth = box.width
  form.style.left = 
    box.left 
    + (oldBox.width - newWidth) 
    - (offRight > 0 ? offRight : 0) 
    + 'px'
  form.querySelector('.collapser').classList.toggle('collapsed', collapse)
  if (focus) {
    form.querySelector('textarea').focus()
  }
}

function toggleFormRow(form, btn) {
  let id = btn.dataset.toggle
  , row = form.querySelector(`.${btn.dataset.toggle}-row`)
  if (! row) return;
  let show = row.classList.toggle('row-shown')
  btn.querySelector('svg').classList.toggle('pressed', show)
  let rem = show ? 0 : isRowEmpty(row, id)
  btn.classList.toggle('button-with-reminder', rem)
  if (rem) {
    if (! btn._originalTitle)
      btn._originalTitle = btn.title
    btn.title = `${btn._originalTitle} (${_l.notEmpty})`
  }
  else if (btn._originalTitle)
    btn.title = btn._originalTitle
  if (show) { // focus on field
    let focal = null
    if (id == 'subject-send') {
      let i = form.querySelector('input[name=subject]')
      if (!i.value)
        focal = i
    }
    if (id == 'name') {
      let i = form.querySelector('input[name=name]')
      if (!i.value && !i.disabled)
        focal = i
    }
    if (id == 'embed') {
      let inputs = [].filter.call(form.querySelectorAll('input[type=text][name^=embed]'), 
        el => el.getClientRects().length && !el.value)
      if (inputs.length)
        focal = inputs[0]
    }
    if (focal)
      focal.focus()
  }
}

function isRowEmpty(row, id) {
  return !!(
    (id == 'name' &&
      row.querySelector('input[name=name]').value != ''
      &&
      !row.querySelector('input[name=disable_name]').checked
    ) ||
    (id == 'subject-send' &&
      row.querySelector('input[name=subject]').value != ''
    ) ||
    (id == 'embed' &&
      [].find.call(row.querySelectorAll('input[type=text]'), i => i.value.trim())
    ) ||
    (id == 'ttl' &&
      row.querySelector('input[name=ttl-enable]').checked
    )
  )
}

function updateReminders(form) {
  ['name', 'subject-send', 'embed', 'ttl'].forEach(id => {
    let row = form.querySelector(`.${id}-row:not(.row-shown)`)
    , btn = row && form.querySelector(`.simplified-toggle[data-toggle="${id}"]`)
    if (btn)
      btn.classList.toggle('button-with-reminder', isRowEmpty(row, id))
  })
}

function toggleFormFloating(form, float, focus=true) {
  let pinner = form.querySelector('.pinner')
  pinner.classList.toggle('pinned', !float)
  pinner.classList.toggle('unpinned', float)
  if (float) { // Make the form floating
    // Pin other forms
    scrollAnchor.save('toggleFormFloating', form.getParentPost())
    document.querySelectorAll('.qrf-floating').forEach(f => toggleFormFloating(f, false))
    scrollAnchor.restore('toggleFormFloating')
    .then(() => { // When all scrolling is settled
      let box = form.getBoundingClientRect()
      form.classList.add('qrf-floating')
      form.classList.remove('qrf-builtin', 'qrf-builtin-back')
      form.style.top = box.top + 'px'
      form.style.left = box.left + 'px'
      $(form).drags()
      .find('input, textarea, select, label, .fe-sort-wrapper, button, .b-icon').mousedown(function(e) {
        e.stopPropagation()
      })
    })
  }
  else { // Build the form back in
    form.classList.remove('qrf-floating', 'collapsed')
    form.querySelector('.collapser').classList.remove('collapsed')
    form.classList.add('qrf-builtin', 'qrf-builtin-back')
    $(form).dragsOff()
    ;['top', 'left', 'zIndex'].forEach(prop => form.style[prop] = null)
  }
  if (focus)
    form.querySelector('textarea').focus()
}

function hidePostForm(form) {
  form.classList.add('hidden')
  toggleFormFloating(form, false)
  toggleFormCollapse(form, false)
  setTimeout(() => {
    form.classList.remove('qrf-builtin-back')
  }, 200)
  if (form._preview) {
    let pinner = form._preview.querySelector('.pinner')
    if (pinner) {
      pinner.classList.remove('pinned')
      pinner.classList.add('unpinned')
    }
    form._preview._pinned = false
  }
  updateActiveForm()
}

function toggleFormSimplify(form, simplify) {
  let simplified = form.classList.toggle('postform-simplified', simplify)
  if (simplified)
    updateReminders(form)
}

function clonePostForm(anchorID, preview, boardName=false) {
  return new Promise((resolve, reject) => {
    HTMLoader.getPostbox(boardName).then(oldForm => {
      if (Captcha.enabled)
        Captcha.state = 'init' // Destroy old captcha because it will be inevitably reloaded at form cloning
      let newForm = oldForm.cloneNode(true)
      newForm.id = anchorID + '_qrf'
      newForm.dataset.anchorID = anchorID
      newForm.classList.remove('main-reply-form')
      newForm.querySelector('.simplified-send-row .primary span').innerText = _l.reply
      newForm.querySelector('input[name="postpassword"]').value = get_password('postpassword')
      newForm.querySelector('input.primary').value = _l.reply
      newForm.querySelector('.noko-row').remove()
      let blotter = newForm.querySelector('.blotter-row')
      if (preview)
        newForm._preview = preview
      blotter.insertAdjacentHTML('afterend', `
        <span class="extrabtns simplified-extras">
          ${newForm.querySelector('.name-row') ? `
            <a href="#" class="simplified-toggle" data-toggle="name" title="${_l.showName}">
              <svg class="icon b-icon"><use xlink:href="#i-name"></use></svg>
            </a>
          ` : ''}
          <a href="#" class="simplified-toggle" data-toggle="subject-send" title="${_l.showSubject}">
            <svg class="icon b-icon"><use xlink:href="#i-subject"></use></svg>
          </a>
          <a href="#" class="s-markup" data-toggle="password" title="${_l.showMarkup}">
            <svg class="icon b-icon"><use xlink:href="#i-markup"></use></svg>
          </a>
          ${newForm.querySelector('.embed-row') ? `
            <a href="#" class="simplified-toggle" data-toggle="embed" title="${_l.showEmbeds}">
              <svg class="icon b-icon"><use xlink:href="#i-embed"></use></svg>
            </a>
          ` : ''}
          <a href="#" class="simplified-toggle" data-toggle="password" title="${_l.showPassword}">
            <svg class="icon b-icon"><use xlink:href="#i-password"></use></svg>
          </a>
          ${newForm.querySelector('.ttl-row') ? `
            <a href="#" class="simplified-toggle" data-toggle="ttl" title="${_l.showTTL}">
              <svg class="icon b-icon"><use xlink:href="#i-timer"></use></svg>
            </a>
          ` : ''}
        </span>`)
      // Name reminder
      updateReminders(newForm)
      // Enabling extra inputs in simplified form
      newForm.querySelectorAll('.simplified-toggle').forEach(a => {
        a.onclick = ev => {
          ev.preventDefault()
          toggleFormRow(newForm, a)
        }
      })
      newForm.querySelector('.s-markup').onclick = function(ev) {
        ev.preventDefault()
        this.querySelector('.icon').classList.toggle('pressed')
        newForm.querySelector('.message-row').classList.toggle('markup-enabled')
      }
      blotter.remove()
      // Rename label[for]s and corresponding inputs
      let add = '_'+_.uniqueId()
      // newForm.id = 'postform'+add
      newForm.querySelectorAll('label').forEach(l => {
        let id = l.getAttribute('for')
        , newid = id + add
        , input = newForm.querySelector('#'+id)
        if (input)
          input.id = newid
        l.setAttribute('for', newid)
      })
      newForm.classList.add('quick-reply-form') // New general class to tell qr form apart from the main form
      newForm.classList.add('postform-simplified')
      // Clear fields
      resetForm(newForm, true /* ← isClone */)
      // Init rich file input
      richFileInput.initForm(newForm)
      // Add extra buttons
      newForm.insertAdjacentHTML('beforeend', `
        <span class="extrabtns postboxcontrol">
          <svg class="icon form-handle-icon"><use xlink:href="#i-handle"></use></svg>
          <a href="#" title="${_l.classicSimplifiedForm}" class="simplify-qr-form">
            <svg class="icon b-icon">
              <use class="use-simplify" xlink:href="#i-simplify"></use>
              <use class="use-complicate" xlink:href="#i-complicate"></use>
            </svg>
          </a>
          <a href="#" title="${_l.collapseExpand}" class="collapse-qr-form collapser">
            <svg class="icon b-icon">
              <use class="use-collapse" xlink:href="#i-collapse"></use>
              <use class="use-uncollapse" xlink:href="#i-uncollapse"></use>
            </svg>
          </a>
          <a href="#" class="pinner" title="${_l.pinUnpin}">
            <svg class="icon b-icon">
              <use class="use-pin" xlink:href="#i-float"></use>
              <use class="use-unpin" xlink:href="#i-unfloat"></use>
            </svg>
          </a>
          &nbsp;
          <a href="#" title="${_l.hideForm}" class="hide-qr-form">
            <svg class="icon b-icon"><use xlink:href="#i-x"></use></svg>
          </a>
        </span>`)
      // Form button actions
      newForm.getParentPost = () => {
        let maybePost = newForm._findPrevious('*:not(.i0svcel)')
        return maybePost.matches('.postnode') ? maybePost : null
      }
      newForm.querySelector('.hide-qr-form').onclick = ev => {
        ev.preventDefault()
        let skip = false
        if (newForm.classList.contains('qrf-floating')) {
          let anchor = newForm.getParentPost()
          if (anchor) {
            flyTo(newForm, anchor.querySelector('a.qrl .b-icon')).then(hidePostForm)
            skip = true
          }
        }
        if (!skip) {
          hidePostForm(newForm)
        }
      }
      newForm.querySelector('.simplify-qr-form').onclick = ev => {
        ev.preventDefault()
        toggleFormSimplify(newForm)
      }
      newForm.querySelector('.pinner').onclick = function(ev) {
        ev.preventDefault()
        let pinned = newForm.classList.contains('qrf-builtin')
        toggleFormFloating(newForm, pinned)
      }
      newForm.querySelector('.collapse-qr-form').onclick = ev => {
        ev.preventDefault()
        toggleFormCollapse(newForm)
      }
      // Prevent dragging of links
      newForm.querySelectorAll('a').forEach(a => {
        a.ondragstart = ev => false
        a.ondrop = ev => false
      })
      // Extra hooks for the new form
      initForm(newForm)

      resolve(newForm)
    })
  })
}

function quickreply() {
  let $this = $(this)
  , threadID = $this.data('parent')
  , postID = $(this).data('postnum') || threadID.split('-')[0]
  , $post = $this.parents('.postnode')
  , boardName = $post.data('board')
  , fromExternalBoard = (!is_overboard && boardName != this_board_dir)
  , post = $post[0]
  , $preview = $post.parents('.reflinkpreview')
  , isPreview = !!$preview
  , needInsert = true
  , form = post._findNext('*:not(.i0svcel)')
  , formExists = form && form.getAttribute('name') == 'postform'
  , formHidden = formExists && form.classList.contains('hidden')
  , formFloating = formExists && form.classList.contains('qrf-floating')
  , isOpen = true
  , preview = post._findParent('.reflinkpreview')
  , quotation = needInsert ? getPostQuotation(post.querySelector('.postbody')) : ''
  
  if (formExists) {
    if (formFloating) {
      toggleFormCollapse(form, false)
      toggleFormFloating(form, false)
    }
    else {
      if (!formHidden) {
        hidePostForm(form)
        formHidden = true
      }
      else {
        form.classList.remove('hidden')
      }
      formHidden = !formHidden
    }
    needInsert = false
  }
  else {
    $this.addClass('spin-around')
    clonePostForm(post.id || post.parentElement.id, preview, (is_overboard ? boardName : false)).then(form => {
      $this.removeClass('spin-around')
      form.classList.add('qrf-builtin')
      form.querySelector('.pinner').classList.toggle('pinned')
      form.querySelector('input[name=replythread]').value = threadID
      post.insertAdjacentElement('afterend', form)
      document.querySelectorAll('.qrf-floating:not(.collapsed)')
      .forEach(existingForm => toggleFormCollapse(existingForm))
      form.querySelector('textarea').focus()
      if (needInsert) {
        insert(`>>${fromExternalBoard ? `/${boardName}/` : ''}${postID}\n${quotation}`)
      }
      // Pin the post if it is in preview
      if (preview && !preview._pinned) {
        post.querySelector('.pinner').click()
      }
    })
  }
  // post.classList.toggle('reply-with-form-open', isOpen)
  return false
}

function _getSelection() {
  if (! window.getSelection) return null
  let selection = window.getSelection()
  if (selection.type !== "Range") return null
  let selectedText = selection.toString()
  if (! selectedText) return null
  return {
    text: selectedText,
    node: selection.anchorNode
  }
}

// get the selected text within the given element
function getLocalSelection(parent) {
  let sel = _getSelection()
  return sel && sel.node._isChildOf(parent)  
    ? sel.text 
    : null
}

function getPostQuotation(post) {
  let text = getLocalSelection(post)
  if (!text) return ''
  text = text
    .replace(/^\s/, '').replace(/\s$/, '') // remove leading and trailing whitespaces
    .replace(/^>/gm, ' >').replace(/^/gm, '> ') // add quotation marks
  return text/*+'\n'*/ || ''
}

function popupMessage(content, delay=1000) {
  pups.info(content, {time: delay/1000})
  console.warn('popupMessage() is deprecated. Please use YOBA alerts instead.')
}

var Captcha = {
  init: function(forceEnable=false) {
    let captchaImage = document.querySelector('.captchaimage')
    this.enabled = forceEnable || !!captchaImage
    if (!this.enabled) return;
    injector.inject('captcha-rotting',
    `.cw-running .rotting-indicator {
      -webkit-animation-duration: ${captchaTimeout}s;
              animation-duration: ${captchaTimeout}s;
    }
    .cw-running .captchaimage,
    .cw-running .rotten-msg {
      -webkit-animation-delay: ${captchaTimeout}s;
              animation-delay: ${captchaTimeout}s;}`)
    if (captchaImage) {
      this.addImgLoadListener(captchaImage)
    }
  },
  initForm: function(form) {
    let cw = form.querySelector('.captchawrap')
    if (!cw) return;
    cw.onclick = this.onClick.bind(this)
    ;['click', 'focus'].forEach(evt => {
      form.querySelector('input[name=captcha]').addEventListener(evt, this.onFieldClick.bind(this))
    })
    let captchaImage = form.querySelector('.captchaimage')
    if (captchaImage) {
      this.addImgLoadListener(captchaImage)
    }
  },
  addImgLoadListener: function(captchaImage) {
    captchaImage.onload = this.onImageLoad.bind(this)
    ;['animationend', 'webkitAnimationEnd', 'msAnimationEnd'].forEach(evType => {
      captchaImage.addEventListener(evType, this.onAnimationEnd.bind(this))
    })
  },
  _state: 'init',
  get state() {
    return this._state
  },
  set state(s) { // Capthca state machine
    document.querySelectorAll('.captchawrap').forEach(c => {
      if (s == 'init') {
        c.classList.remove('cw-running')
        c.classList.add('cw-initial')
      }
      if (s == 'init' || s == 'load') {
        c.classList.add('captchaimage-invisible')
      }
      if (s == 'load') {
        c.classList.remove('cw-initial', 'cw-running')
        void c.offsetWidth //trigger a reflow
        c.classList.add('cw-running', 'cw-loading')
      }
      else {
        c.classList.remove('cw-loading')
      }
      if (s == 'run') {
        c.classList.remove('captchaimage-invisible')
      }
    })
    if (s !== 'run')
      this.clear()
    this._state = s
  },
  onClick: function(ev) {
    this.refresh(ev.ctrlKey || ev.altKey)
    ev.target._findParent('form').querySelector('input[name=captcha]').focus()
  },
  onFieldClick: function() {
    if (this.state == 'init' || this.state == 'expired')
      this.refresh()
  },
  onImageLoad: function() {
    if (this.state == 'load')
      this.state = 'run'
  },
  onAnimationEnd: function() {
    this.state = 'expired'
  },
  clear: function() {
    document.querySelectorAll('input[name="captcha"]').forEach(i => i.value = '')
  },
  refresh: function(switch_lang=false) {
    let cColor = $('.captchawrap').css('color').match(/([0-9]+(?:\.[0-9]+)?)/g)
    cColor = (cColor.length >= 3) ? '&color='+cColor.slice(0, 3).join(',') : ''
    let url = ku_boardspath+'/captcha.php?'+Math.random()+cColor+(switch_lang ? '&switch' : '')
    document.querySelectorAll('.captchaimage').forEach(img => img.src = url)
    this.state = 'load'
  }
}

function formatFileSize(bytes) {
  if (bytes < 1000) {
    return `${bytes}B`
  }
  else if (bytes < 1000000) {
    return `${Math.round(bytes/1000)}KB`
  }
  else {
    return `${Math.round(bytes/100000)/10}MB`
  }
}

const richFileInput = {
  handleFiles: function(files, form, fromClipboard=false) {
    let maxfiles = +form.dataset.maxfiles
    , slotsLeft = maxfiles - $(form).find('.file-entry').length
    , filesLeft = files.length
    , i = 0
    for (; slotsLeft && filesLeft; i++) {
      let fileAttached = this.handleFile(files[i], form, fromClipboard)
      slotsLeft -= fileAttached
      filesLeft--
    }
    if (slotsLeft <= 0 && filesLeft)
      pups.warn(_l.maxAttNumReached)
  },
  handleFile: function(file, form, fromClipboard=false) {
    let fx = {}
    // Determine the file extension
    let ns = file.name.split('.')
    fx.ext = (ns.length) > 1 ? ns.pop().toLowerCase() : ""
    if (fx.ext == 'jpeg') 
      fx.ext = 'jpg'
    if (!_.contains(form.dataset.allowedFiletypes.split(','), fx.ext)) {
      pups.err(`${file.name}: ${_l.unsupportedFileType}.`)
      return 0
    }
    fx.fname = ns.join('.')

    fx.sizeFormatted = formatFileSize(file.size)
    
    if (file.type.indexOf("image") != -1) {
      let reader = new FileReader()
      reader.onload = ev => {
        fx.img = ev.target.result
        this.addFile(file, fx, form, fromClipboard)
      }
      reader.readAsDataURL(file, fx, form)
    }
    else {
      this.addFile(file, fx, form, fromClipboard)
    }
    return 1
  },
  addFile: function(file, extra, form, fromClipboard=false) {
    let index = this.store.push(file)-1
    form.querySelector('.fe-sort-wrapper').insertAdjacentHTML('beforeEnd', `
      <div class="file-entry${fromClipboard ? ' fe-name-hidden' : ''}" data-index="${index}" title="${file.name}">
        <div class="fe-thumb">
          ${extra.img ? `<img src=${extra.img}>` :  `<div class="fe-noimg">${extra.ext}</div>`}
        </div>
        <input type="text" value="${extra.fname}" class="fe-fn" ${fromClipboard ? ' disabled' : ''}>
        <button class="clear-filename fe-pop-button icon-wraping-button" title="${_l.hideName}"><svg class="icon b-icon">
          <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#i-hide-name"></use>
        </svg></button>
        <button class="remove-file fe-pop-button icon-wraping-button" title="${_l.removeFile}"><svg class="icon b-icon">
          <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#i-x"></use>
        </svg></button>
        <button class="spoiler-file fe-pop-button icon-wraping-button" title="${_l.spoilerOnOff}"><svg class="icon b-icon">
          <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#i-spoiler"></use>
        </svg></button>
        <div class="fe-info"></div>
      </div>
    `)
    this.recalcForm(form)
    let thisEl = form.querySelector(`.file-entry[data-index="${index}"]`)
    , img = false
    , updateInfo = () => thisEl.querySelector(`.fe-info`).innerText = 
        `${extra.ext.toUpperCase()}${(img ? `, ${img.naturalWidth}×${img.naturalHeight}`: '')}, ${extra.sizeFormatted}`
    if (extra.img) {
      img = thisEl.querySelector('img')
      img.onload = updateInfo
      img.onerror = updateInfo
    }
    else updateInfo()
    thisEl._ext = extra.ext
    this.updateTitle($(thisEl))
  },
  recalcForm: function(form) {
    let count = form.querySelectorAll('.file-entry').length
    , maxfiles = +form.dataset.maxfiles
    form.classList.toggle('fda-non-empty', count > 0)
    form.classList.toggle('fda-form-full', count >= maxfiles)
    form.querySelector('.file-count').innerHTML = count > 0
      ? `${_l.files}<br>(${count}/${maxfiles})`
      : `${_l.file}`
  },
  updateTitle: function($el) {
    let v = $el.find('.fe-fn').val()
    , fname = !$el.hasClass('fe-name-hidden') && v ? v : '*****'
    $el[0].title = `${fname}.${$el[0]._ext}`
  },
  serialize: function(form, fd) {
    form.querySelectorAll('.file-entry').forEach((fe, i) => {
      fd.append('imagefile[]', this.store[+fe.dataset.index])
      if (fe.classList.contains('fe-spoiler'))
        fd.append(`spoiler-${i}`, 1)
      let fname = fe.querySelector('.fe-fn').value
      if (fe.classList.contains('fe-name-hidden') || fname==='')
        fd.append(`hidename-${i}`, 1)
      else
        fd.append(`filename-${i}`, fname)
    } )
  },
  store: [],
  initiated: false,
  init: function(form) {
    this.enabled = form.querySelector('.file-row') && 'FileReader' in window
    if (this.enabled) {
      let _this = this
      $('body')
      .on('click', '.remove-file', function(ev) {
        ev.preventDefault()
        ev.stopPropagation()
        let $entry = $(this).parents('.file-entry')
        _this.store[+$entry.data('index')] = null
        let $form = $entry.parents('form[name=postform]')
        $entry.remove()
        _this.recalcForm($form[0])
      })
      .on('click', '.file-entry', function(ev) {
        $(this).toggleClass('fe-expanded')
        setTimeout(() => $(this).find('input').select(), 450)
      })
      .on('click', '.file-entry input', function(ev) {
        ev.stopPropagation()
      })
      .on('click', '.spoiler-file', function(ev) {
        ev.preventDefault()
        ev.stopPropagation()
        $(this).parents('.file-entry').toggleClass('fe-spoiler')
      })
      .on('click', '.clear-filename', function(ev) {
        ev.preventDefault()
        ev.stopPropagation()
        let $entry = $(this).parents('.file-entry')
        $entry.find('.fe-fn').prop('disabled', !$entry.hasClass('fe-name-hidden'))
        $entry.toggleClass('fe-name-hidden')
        _this.updateTitle($entry)
      })
      .on('change', '.fe-fn', function(ev) {
        _this.updateTitle($(this).parents('.file-entry'))
      })
      .on('change', 'input[type=file]', function(ev) {
        let form = $(this).parents('form[name=postform]')[0]
        _this.handleFiles(ev.target.files, form)
        this.value = null
      })
      .on('click', '.add-files', function(ev) {
        ev.preventDefault()
        $(this).parents('form[name=postform]').find('input[type=file]')[0].click()
      })
      .on('paste', 'form[name=postform]', function(ev) {
        let items = (ev.clipboardData || ev.originalEvent.clipboardData).items
        , arr = [];
        for (var i = 0 ; i < items.length ; i++) {
          var item = items[i];
          if (item.kind == 'file') {
            arr.push(item.getAsFile())
          }
        }
        _this.handleFiles(arr, this, true)
      })
    }
    else {
      $('body')
      .on('click', '.remove-file', function(ev) {
        ev.preventDefault()
        $(this).parent().find('input[type=file]')
          .val(null)
          .removeClass('non-empty-file-input')
      })
      .on('change', 'input[type=file]', function() {
        $(this).toggleClass('non-empty-file-input', this.value)
      })
    }
    this.initiated = true
    /*if (!is_overboard)
      this.initForm(postform)*/
  },
  initForm: function(form) {
    if (!this.initiated)
      this.init(form)
    let _this = this
    if (this.enabled) {
      // Drag and drop events
      $(form) // I tried using plain JS, doesn't work, sorry
      .on('dragenter dragend dragover drop dragleave', function(ev) {
        ev.originalEvent.stopPropagation()
        if (!(ev.target.nodeName == 'TEXTAREA' || ev.target.nodeName == 'INPUT')) 
          ev.preventDefault()
      })
      .on('drop', function(ev) {
        ev.originalEvent.stopPropagation()
        if (ev.originalEvent.dataTransfer && ev.originalEvent.dataTransfer.files.length) {
          ev.originalEvent.preventDefault() 
          _this.handleFiles(ev.originalEvent.dataTransfer.files, this)
        }
      })
      // File entry sorting
      if(form.querySelector('.fe-sort-wrapper')) {
        Sortable.create(form.querySelector('.fe-sort-wrapper'), {
          animation: 150,
          handle: '.fe-thumb'
        })
      }
      // Prevent legacy file inputs from being serialized
      form.querySelectorAll('input[name^=imagefile]').forEach(i => i.removeAttribute('name'))
    }
    else {
      let area = form.querySelector('.drop-area')
      if (!area) return;
      area.previousElementSibling.classList.remove('noscript')
      area.remove()
    }
  },
  clear: function(form, clearStore=false) {
    if (this.enabled) {
      form.querySelectorAll('.file-entry').forEach(fe => {
        if (clearStore)
          this.store[fe.dataset.index] = null
        fe.remove()
      })
      this.recalcForm(form)
    }
  }
}

function handleCtrlEnter(ev) {
  ev.preventDefault()
  let area = document.activeElement
  , form = area._findParent('form[name=postform]')
  if (form) {
    Ajax.submitPost(form)
  }
}

function redirectTo(href) {
  // console.warn('About to redirect to:', href)
  document.location.href = href
}

const Ajax = {
  submitPost: function(form, sage) {
    if (form.querySelector('script[src^="https://js.hcaptcha.com"]')) {
      hcaptcha.execute({ async: true })
      .then(({ response, key }) => {
        this.actuallySubmitPost(form, sage, response)
      })
      .catch(err => {
        console.error('hcapthca:', err)
        if (err == 'challenge-expired')
          this.shownErrors.push(pups.warn(_l.captchaExpired))
      });
    }
    else {
      this.actuallySubmitPost(form, sage)
    }
  },
  actuallySubmitPost: function(form, sage, hCaptchaResponse=false) {
    let alertsToRemove = _.clone(this.shownErrors)
    setTimeout(() => {
      alertsToRemove.forEach(a => {
        pups.closeByID(a)
      })
    }, 300)
    this.shownErrors = []
    let showError = (a, b={}) => this.shownErrors.push(pups.err(a, b))
    let showWarning = (a, b={}) => this.shownErrors.push(pups.warn(a, b))

    let captchafield = form.captcha
    if (captchafield && !captchafield.value.length) {
      captchafield.focus()
      return showWarning(_l.enterCaptcha)
    }
    
    form.classList.add('form-sending')
    let fd = new FormData(form)

    if (typeof sage !== 'undefined')
      fd.append('sagebtn', sage)

    if (hCaptchaResponse) {
      fd.append('h-captcha-response', hCaptchaResponse)
    }

    if (richFileInput.enabled)
      richFileInput.serialize(form, fd)
    let xr = new XMLHttpRequest()
    fd.append('AJAX', 1)

    // Token for live updates
    this.postToken = randomString()
    fd.append('token', this.postToken)

    xr.open('POST', form.action)
    xr.onload = e => {
      form.classList.remove('form-sending')
      if (xr.status !== 200) {
        showError(_l.xhrError)
        return
      }
      ;[].forEach.call(form.querySelectorAll('.error-in-attachment'), el => el.classList.remove('error-in-attachment'))
      if (Captcha.enabled)
        Captcha.state = 'init'
      let res = null
      try {
        res = JSON.parse(xr.response)
      }
      catch(e) {
        showError(_l.xhrError)
        console.error('Malformed response (JSON expected):', xr.response)
        return
      }
      if (res.error) {
        if (res.error_verbose)
          res.error = `${res.error}<br>${res.error_verbose}`
        if (res.error_type) {
          if (res.error_type == 'ban') {
            showError(`${res.error} (<a href="${ku_cgipath}/banned.php" target="_blank">${_l.details}</a>)`, {time: 0, save: true})
          }
          if (res.error_type == 'duplicate_file') {
            showError(res.error, {time: 0})
            form.querySelector('input[name="imagefile"]').value = null
          }
          if (res.error_type == 'upload_error') {
            showError(res.error, {time: 0})
            let badatt = form.querySelector(`.multiembedwrap[data-pos="${res.error_data.attachmenttype}-${res.error_data.position+1}"]`)
            if (badatt) {
              badatt.classList.add('error-in-attachment')
            }
          }
        }
        else {
          showError(res.error, {time: 0})
        }
        return
      }
      else {
        if (res.timings) {
          console.log('timings:', res.timings)
        }
        resetForm(form)
        if (form.classList.contains('quick-reply-form')) {
          hidePostForm(form) // TODO: tumbach pinning?
        }
        /*else {
        }*/
        let nokoInput = form.querySelector('input[name="redirecttothread"]')
        , noko = nokoInput && nokoInput.checked
        if (res.thread_replyto != 0) {
          newposts.get({
            threadid: ispage ? res.thread_replyto+'-'+res.board : null,
            expectedPost: res.post_id+'-'+res.board,
            onError: (err) => {
              console.error(err)
              redirectTo(`${ku_boardspath}/${res.board}/res/${res.thread_replyto}.html#${res.post_id}`)
              if (! ispage)
                document.location.reload()
            },
            onSuccess: () => $(`#delform div[id^=thread${res.thread_replyto}] .fresh-replies`).remove()
          })
        }
        else {
          if (noko) {
            redirectTo(`${ku_boardspath}/${res.board}/res/${res.post_id}.html`)
          }
          else {
            redirectTo(`${ku_boardspath}/${res.board}`)
          }
        }
      }
    }
    xr.send(fd)
  },
  shownErrors: [],
  reportPost: function(fd, callback=null) {
    let xr = new XMLHttpRequest()
    fd.append('AJAX', 1)
    fd.append('reportpost', 1)
    xr.open('POST', document.forms.delform.action)
    xr.onload = e => {
      if (xr.status !== 200) {
        pups.err(_l.xhrError)
        return callback(false)
      }
      let res = null
      try {
        res = JSON.parse(xr.response)
      }
      catch(e) {
        pups.err(_l.xhrError)
        console.error('Malformed response (JSON expected):', xr.response)
        return callback(false)
      }
      if (callback)
        callback(res.data)
      if (res.error) {
        if (res.error_verbose)
          res.error = `${res.error}<br>${res.error_verbose}`
        pups.err(res.error, {time: 0})
        return
      }
      else {
        $('body').removeClass('select-multiple')
        $('.userdelete').removeClass('ud-active')
        let postsReported = []
        , postErrors = []
        res.data.forEach(item => {
          if (!item.success) {
            postErrors.push(item)
            return
          }
          postsReported.push(item.id)
          if (! $('input[name="post[]"]:checked').length)
            $('.userdelete').removeClass('ud-active');
        })
        let msg = ''
        if (postsReported.length) {
          postsReported = postsReported.map(post => '#'+post)
          msg += postsReported.length > 1 ?
            `${_l.posts} ${postsReported.join(', ')} ${_l.reportedMulti}.`
           :`${_l.post} ${postsReported[0]} ${_l.reported}.`
          pups.succ(msg, {time: 2 + postsReported.length})
        }
        postErrors.forEach(err => {
          pups.err(`${_l.post} #${err.id}: ${err.message}`)
        })
      }
    }
    xr.send(fd)
  },
  cancelTimer: function(fd, callback=null) {
    let xr = new XMLHttpRequest()
    fd.append('AJAX', 1)
    fd.append('cancel_timer', 1)

    // Token for live updates
    this.timerToken = randomString()
    fd.append('token', this.timerToken)

    xr.open('POST', document.forms.delform.action)
    xr.onload = e => {
      if (xr.status !== 200) {
        pups.err(_l.xhrError)
        return callback(false)
      }
      let res = null
      try {
        res = JSON.parse(xr.response)
      }
      catch(e) {
        pups.err(_l.xhrError)
        console.error('Malformed response (JSON expected):', xr.response)
        return callback(false)
      }
      if (res.error) {
        if (res.error_verbose)
          res.error = `${res.error}<br>${res.error_verbose}`
        pups.err(res.error, {time: 0})
        return callback(false)
      }
      else {
        $('body').removeClass('select-multiple')
        $('.userdelete').removeClass('ud-active')
        let postsSaved = []
        , postErrors = []
        res.data.forEach(item => {
          if (!item.success) {
            postErrors.push(item)
            return
          }
          postsSaved.push(item.id)
          if (! $('input[name="post[]"]:checked').length)
            $('.userdelete').removeClass('ud-active');
        })
        let msg = ''
        if (postsSaved.length) {
          postsSaved.forEach(id => {
            let $reply = $(`.postnode[data-id=${id}]`)
            $reply.find('.post-ttl').remove()
            $reply.find('.post-menu').hide()
          } )
          msg += postsSaved.length > 1 ?
            `${_l.posts} #${postsSaved.join(', #')} ${_l.savedMulti}.`
           :`${_l.post} #${postsSaved[0]} ${_l.saved}.`
          pups.succ(msg, {time: 2 + postsSaved.length})
        }
        postErrors.forEach(err => {
          pups.err(`${_l.post} #${err.id}: ${err.message}`)
        })
        callback(postErrors)
      }
    }
    xr.send(fd)
  },
  modThread: function(a, action) {
    let $a = $(a)
    , fd = new FormData()
    , xr = new XMLHttpRequest()
    , $li = $a.find('li')
    $li.addClass('spin-around')
    fd.append('AJAX', 1)
    xr.open('POST', a.getAttribute('href'))
    xr.onload = e => {
      $li.removeClass('spin-around')
      if (xr.status !== 200) {
        pups.err(_l.xhrError)
        return
      }
      let res = null
      try {
        res = JSON.parse(xr.response)
      }
      catch(e) {
        pups.err(_l.xhrError)
        console.error('Malformed response (JSON expected):', xr.response)
        return
      }
      if (res.error) {
        if (res.error_verbose)
          res.error = `${res.error}<br>${res.error_verbose}`
        pups.err(res.error, {time: 0})
        return
      }
      else {
        let $posthead = $a.parents('.posthead')
        , $extrabtns = $posthead.find('.extrabtns')
        if (action == 'stickypost') {
          $extrabtns.prepend(makeIcon('pin', 'i-icon i-pin'));
          $posthead.addClass('thread-stickied')
        }
        if (action == 'unstickypost') {
          $extrabtns.find('svg.i-icon.i-pin').remove()
          $posthead.removeClass('thread-stickied')
        }
        if (action == 'lockpost') {
          $extrabtns.prepend(makeIcon('lock', 'i-icon i-lock'));
          $posthead.addClass('thread-locked')
        }
        if (action == 'unlockpost') {
          $extrabtns.find('svg.i-icon.i-lock').remove()
          $posthead.removeClass('thread-locked')
        }
        pups.succ(res.message)
      }
    }
    xr.send(fd)
  },
  deleteItems: function(fd, callback=null, asMod=false) {
    let kind = 'Items'
    if (asMod) {
      console.log('yes')
      fd.append('moddelete', 1)
    }
    if (fd.has) {
      if (fd.has('post[]')) {
        if (!fd.has('delete-file[]'))
          kind = 'Posts'
        fd.append('deletepost', 1)
      }
      else if (fd.has('delete-file[]'))
        kind = 'Files'
      else return;
    }
    else { // FormData.has() is not supported
      fd.append('deletepost', 1)
    }
    
    let xr = new XMLHttpRequest()
    fd.append('AJAX', 1)

    // Token for live updates
    this.delToken = randomString()
    fd.append('token', this.delToken)

    xr.open('POST', document.forms.delform.action)
    xr.onload = e => {
      if (xr.status !== 200) {
        pups.err(_l.xhrError)
        return callback ? callback(null) : null
      }
      let res = null
      try {
        res = JSON.parse(xr.response)
      }
      catch(e) {
        pups.err(_l.xhrError)
        console.error('Malformed response (JSON expected):', xr.response)
        return callback(null)
      }
      if (callback)
        callback(res.data)
      if (res.error) {
        if (res.error_verbose)
          res.error = `${res.error}<br>${res.error_verbose}`
        pups.err(res.error, {time: 0})
        return
      }
      else {
        deleteItems(res.data)
      }
    }
    xr.send(fd)
  }
}

function inspectFormData(formData) {
  for (var pair of formData.entries()) {
    console.log(pair[0]+ ', ' + pair[1]);
  }
}

function makeIcon(i, classes="", bare=false) {
  return `${bare ? '' : `<svg class="icon ${classes}">`}
    <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#i-${i}"></use>
  ${bare ? '' : '</svg>'}`
}

function addCaptchaToMenu($menu) {
  if ($menu.find('.captchawrap').length) {
    Captcha.state = 'init';
    return;
  }
  if (!Captcha.enabled) {
    Captcha.init('only-access')
  }
  $menu.prepend(`<li class="menu-captcha">
    <div class="captchawrap cw-initial captchaimage-invisible" title="${_l.refreshCaptcha}">
      <div class="captcha-show msg">${_l.showCaptcha}</div>
      <img class="captchaimage" valign="middle" border="0" alt="${_l.captchaImage}">
      <div class="rotting-indicator"></div>
      <div class="rotten-msg msg">${_l.captchaExpired}</div>
    </div>
    <input type="text" name="captcha" placeholder="${_l.captcha}" autocomplete="off">
  </li>`)
  Captcha.initForm($menu[0])
  $menu.find('input[name=captcha]').keydown(ev => {
    if (ev.key == 'Enter') {
      ev.preventDefault()
      ev.stopPropagation()
    }
  })
}

function expandimg(postnum, imgurl, thumburl, imgw, imgh, thumbw, thumbh) {
  let element = document.getElementById("thumb" + postnum);
  if (element == null) return false;
  let $element = $(element)
  , $postbody = $element.parents('.postbody')
  , $fc_filename = $element.parents('figure').find('.fc-filename')
  , fc_width
  if(typeof event !== 'undefined' && event.which === 2) return true;
  if (element.getElementsByTagName('img')[0].getAttribute('alt').substring(0,4)!='full') {
    $element.html('<img src="'+imgurl+'" alt="full'+postnum+'" class="thumb" height="'+imgh+'" width="'+imgw+'">');
    if (! Settings.expandImgFull()) {
      let img = element.getElementsByTagName('img')[0]
      , max_w = document.documentElement?document.documentElement.clientWidth : document.body.clientWidth
      , offset = 50
      , offset_el = img

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
        var textNode = document.createTextNode(_l.imageDownscaledBy + " " + Math.round(zoom*100) + "% "+_l.toFit);
        notice.appendChild(textNode);
        element.insertBefore(notice, img);
        $(img).width(new_w);
        $(img).height(new_h);
      }
      fc_width = new_w
    }
    else
      fc_width = imgw
    if ($postbody) $postbody.addClass('postbody-expanded')
  }
  else {
    element.innerHTML = '<img src="' + thumburl + '" alt="' + postnum + '" class="thumb" height="' + thumbh + '" width="' + thumbw + '">';
    fc_width = thumbw
    if ($postbody) $postbody.removeClass('postbody-expanded')
  }
  $fc_filename[0].style.maxWidth = `${fc_width - 50}px`
  return false;
}

// YOBA previews
var PostPreviews = {
  zindex:  3,
  parent: {},

  _parseLink: function(el) {
    let url = el.getAttribute("href")
    if (url) {
      let matches = url.match(/\/(.+)\/res\/([0-9]+)\.html#([0-9]+)|postbynumber\.php\?b=(.+)&p=([0-9]+)/i)
      if (matches) return {
        board: matches[1] || matches[4],
        parent: matches[2] || '?',
        post: matches[3] || matches[5],
      }
    }
    let cl = [].find.call(el.classList, c => c.match(/ref\|.+?\|([0-9]+|\?)\|[0-9]+/))
    if (cl) {
      cl = cl.split('|')
      return {
        board: cl[1],
        parent: cl[2],
        post: cl[3],
      }
    }
    return null
  },

  _mouseover: function(e) {
    e.stopPropagation();
    var isCatalog = $(this).hasClass('catalog-entry')
    , parsedURL = PostPreviews._parseLink(this)
    if (!parsedURL) return false;
    let board = parsedURL['board']
    , parentid = parsedURL['parent']
    , postid = parsedURL['post']
    , previewid = 'preview_'+board+'_'+postid
    , $preview = $('#' + previewid)
    if ($preview.length == 0) {
      $('body').children().first().before('<div id="'+previewid+'"></div>');
      $preview = $('#' + previewid);
      $preview.addClass('reflinkpreview content-background pre-hidden actual-reflinkpreview');
      $preview.mouseleave(PostPreviews._mouseout);
      $preview.mouseover(PostPreviews.mouseOverPreview);
    }
    else {
      if ($preview[0]._pinned)
        return false // No need to recreate a preview in this case
    }
    var parent = $(this).parents('div[id^=preview]');
    if (parent.length > 0) {
      if (previewid == parent.attr('id')) return; // anti-recursion
      for (var id in PostPreviews.parent) { 
        if (id == previewid || PostPreviews.parent[id] == previewid) 
          return
      }
      PostPreviews.parent[previewid] = parent.attr('id');
    }
    /*else {
      PostPreviews.parent = [];
    }*/
    let transformOrigin;
    if(e.clientY < ($(window).height() / 1.5)) {
      $preview.css({top:e.pageY+5});
      transformOrigin = "top "
    } else {
      $preview.css({bottom:$(window).height()-e.pageY+5});
      transformOrigin = "bottom "
    }
    if(e.clientX < ($(window).width() / 1.5)) {
      $preview.css({left:e.pageX+15});
      transformOrigin += "left"
    } else {
      $preview.css({right:$(window).width()-e.pageX+15});
      transformOrigin += "right"
    }
    $preview.css({zIndex: PostPreviews.zindex++, "transform-origin": transformOrigin});
    this.style.cursor = "progress"
    let preview = $preview[0]
    HTMLoader.getPost(board, parentid, postid, (err, post) => {
      window.setTimeout(() => {
        preview.getBoundingClientRect() // force reflow or how is this thing called
        $preview.html(post ? post : (_l.oops + (err !== false ? ` (${err})` : '')))
        .removeClass('pre-hidden')
        this.style.cursor = "pointer"
        // Add pin button
        let ebtns = preview.querySelector('.extrabtns')
        if (!ebtns) return;
        ebtns.insertAdjacentHTML('beforeEnd', `
        <a href="#" class="pin-preview pinner unpinned" title="${_l.pinUnpin}">
          <svg class="icon b-icon">
            <use class="use-pin" xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#i-pin"></use>
            <use class="use-unpin" xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#i-unpin"></use>
          </svg>
        </a>`)
        let pinner = ebtns.querySelector('.pinner')
        pinner.onclick = (ev) => {
          ev.preventDefault()
          ev.stopPropagation()
          let pin = !pinner.classList.contains('pinned')
          pinner.classList.toggle('pinned', pin)
          pinner.classList.toggle('unpinned', !pin)
          let $pre = $preview
          while ($pre.length > 0) {
            $pre[0]._pinned = pin
            $pre = $('#' + PostPreviews.parent[$pre.attr('id')])
          }
        }
      }, 0)
    })
    e.preventDefault();
  },

  mouseOverPreview: function() {
    var preview = $(this);
    if ($(this).is('a')) {
      let parsedURL = PostPreviews._parseLink(this)
      if (!parsedURL) return;
      let board = parsedURL['board']
      , postid = parsedURL['post']
      preview = $('#preview_'+board+"_"+postid).first();
    }
    while (preview.length > 0) {
      clearTimeout(preview[0].fadeout)
      preview = $('#' + PostPreviews.parent[preview.attr('id')])
    }
  },

  _mouseout: function(ev, nodelay=false) {
    var preview = $(this);
    if ($(this).is('a')) {
      if (this.predelay) {
        clearTimeout(this.predelay)
      }
      let parsedURL = PostPreviews._parseLink(this)
      if (!parsedURL) return;
      let board = parsedURL['board']
      , postid = parsedURL['post']
      preview = $('#preview_'+board+"_"+postid).first();
    }
    while (preview.length > 0) {
      clearTimeout(preview[0].fadeout)
      let pre = preview
      if (pre[0]._pinned) return;

      preview[0].fadeout = setTimeout(() => {
        pre.addClass('pre-hidden')
        setTimeout(() => {
          delete PostPreviews.parent[pre.attr('id')];
          pre.remove();
        }, PostPreviews._timings.transition)
      }, nodelay ? 0 : PostPreviews._timings.fade)
      preview = $('#' + PostPreviews.parent[preview.attr('id')]);
    }
  },

  _timings: {
    predelay: 50,
    transition: 200,
    fade: 400
  }
}

function set_inputs(id) {
  let form = document.getElementById(id)
  if (form) {
    let name = form.querySelector('input[name=name]')
    if(name && !name.value) {
      let savedName = getCookie("name")
      if (~savedName.indexOf('#'))
        name.type = 'password'
      name.value = savedName
    }
    if(!form.em.value) form.em.value = getCookie("email");
    if(!form.postpassword.value) form.postpassword.value = get_password("postpassword");
  }
}

function set_delpass(id) {
  let form = document.getElementById(id)
  if (! form) return;
  let postpassword = form.postpassword
  if (postpassword && !postpassword.value) {
    postpassword.value = get_password("postpassword");
  }
}

(function ($) { // [!] What the fuck is this?
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
  _checkbox: function(clicked, settingName, defaultValue, perBoard=false) {
    let settingNameGeneral = settingName
    if (perBoard)
      settingName = settingName+'.'+this_board_dir
    if (localStorage == null) {
      pups.err(_l.noLocalStorage);
      return;
    }
    if (localStorage[settingName] == null) {
      localStorage[settingName] = defaultValue;
    }
    if (clicked == true) {
      // save it
      localStorage[settingName] = $('#settings_' + settingNameGeneral).is(":checked");
    } else {
      // update checkbox (called on load)
      if (localStorage[settingName] == 'true')
        $('#settings_' + settingNameGeneral).attr("checked","checked");
      else
        $('#settings_' + settingNameGeneral).removeAttr("checked");
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
    var enabled = Settings._checkbox(changed, 'showReplies', true);
    if (changed != null && (typeof is_catalog === 'undefined' || !is_catalog)) {
      scrollAnchor.save('replymap', '.postnode')
      if (enabled) {
        replyMap.showReplies()
        injector.remove('hide-replies')
      }
      else {
        injector.inject('hide-replies', '.replieslist {display: none}')
      }
      scrollAnchor.restore('replymap')
    }
    return enabled;
  },

  sfwMode: function(changed) {
    var enabled = Settings._checkbox(changed, 'sfwMode', false);
    if (enabled) {
      injector.inject('sfwMode', 'figure > *, .emoji { opacity: 0.05;} figure:hover > *, .postbody:hover .emoji { opacity: 1;}');
    }
    else {
      injector.remove('sfwMode');
    }
    return enabled;
  },

  hideCompletely: function(changed) {
    var enabled = Settings._checkbox(changed, 'hideCompletely', false, true);
    if (changed) {
      HiddenItems.hideCompletely = enabled
      HiddenItems.updateCSS()
    }
    return enabled;
  },

  expandImgFull: function(changed) {
    return Settings._checkbox(changed, 'expandImgFull', false);
  },

  constrainWidth: function(changed) {
    var enabled = Settings._checkbox(changed, 'constrainWidth', false);
    if (enabled) {
      injector.inject('constrainWidth', `body {
        max-width: 960px;
        margin: 0px auto;
      }`)
    }
    else {
      injector.remove('constrainWidth')
    }
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

var captchalang = getCookie('captchalang') || 'ru';
function setCaptchaLang(lang) {
  if(!in_array(lang, ['ru', 'en', 'num'])) return;
  captchalang = lang;
  set_cookie('captchalang', lang, 365);
  pups.succ(_l.captchaLangChanged)
}

const offClick = []

function readyset() {
  $('.make-me-readonly').each(function() {
    $(this).attr('readonly', true)
    .on('focus', function() {
      $(this).removeAttr('readonly')
    })
  })

  if(!ispage) $('.mgoback').show();
  if(isTouch)
    $('#js_settings').prepend('<a href="javascript: localStorage.setItem(\'interfaceType\', \'desktop\'); location.reload();">'+_l.returnDesktop+'</a><br>');
  else
    $('#js_settings').prepend('<a href="javascript: localStorage.setItem(\'interfaceType\', \'touch\'); location.reload();">'+_l.returnTouch+'</a><br>');

  $('#js_settings').prepend(_l.captchalang+': <a href="javascript:setCaptchaLang(\'ru\');">Cyrillic</a> | <a href="javascript:setCaptchaLang(\'en\');">Latin</a> | <a href="javascript:setCaptchaLang(\'num\');">Numeral</a><br />');

  if(Styles.$cancelLink)
    $('#js_settings').prepend(Styles.$cancelLink)

  let pf = document.querySelector('#postform')
  if (pf)
    richFileInput.initForm(pf)

  pups.init();
  chpok.init(true)

  LatexIT.init();
  checkhighlight();
  checkgotothread();
  checknamesave();

  bnrs.init();

  if(getCookie('ku_menutype')) {
    var c = Cookie('ku_menutype');
    if(c != 'default' && c != '')
      document.getElementById('overlay_menu').style.position = c;
  }

  //initial post-process
  processNodeInsertion()

  if(!isTouch) {
    cloud20.init();
    $('.sect-exr').mouseenter(function() { menu_show('ms-'+$(this).data('toexpand')); });
    $('#overlay_menu').mouseleave(function() { menu_show('_off_'); });
    $('body').on('mouseenter', "a[class^='ref']", function(ev) {
      this.predelay = setTimeout(() => PostPreviews._mouseover.bind(this)(ev), PostPreviews._timings.predelay)
    })
    .on('mouseleave', "a[class^='ref']", PostPreviews._mouseout)
    .on('click', "a[class^='ref']", function(e) {
      e.preventDefault()
      let href = this.getAttribute('href')
      if (!highlight(href.split('#')[1], 0)) {
        redirectTo(href)
      }
    })
  }
  else {
    add_mob_menu();
    $('body').addClass('touch-mode');
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
    offClick.push(ev => {
      menu_show('_off_');
      document.querySelectorAll('[id^=preview]').forEach(pre => PostPreviews._mouseout.bind(pre)(null, true))
    })
    $('body').on('click', "a[class^='ref']", PostPreviews._mouseover)
    .on('doubletap', '.postnode', function(ev) {
      ev.stopPropagation()
      ev.preventDefault()
      this.querySelector('.qrl').click()
    })
  }

  $('html').click(ev => offClick.forEach(fn => fn(ev)))

  /* ---------- All delegated events on body ---------- */
  $('body')
  .on('click', '.uib-mup', function() {
    markup($(this).parents('form'), $(this).data('mups'), $(this).data('mupe'), $(this).data('imups'), $(this).data('imupe'));
    return false;
  })
  .on('click', '.uib-bul', function() {
    bullets($(this).parents('form'), $(this).data('bul'), $(this).data('imups'), $(this).data('imupe'));
    return false;
  })
  .on('click', '.uib-tx', function() {
    var target = $(this).data('target');
    head.js('http://latex.codecogs.com/editor3.js', function() {
      OpenLatexEditor(target,'phpBB','en-us', false, '','full');
    });
    return false;
  })
  //Video expanding
  .on('click', '.movie', function(event) {expandVideo($(this), event)})
  //new quick reply
  .on('click', '.qrl', quickreply)
  // ID highlighting
  .on('click', '.hashpic', function() {
    $('.highlight').removeClass('highlight')
    let found = $(`.hashpic[alt=${$(this).attr('alt')}]`).each(function() {
      $(this).parents('.posthead').parent().addClass('highlight')
    }).length
    pups.info(_l.found+': '+found, {time: 1.5})
  })
  .on('click', '.posttypeindicator a', function() { // TODO: what is this?
    var xl = $(this);
    var offset = $('[name="' + xl.attr('href').substr(1) + '"]').offset() || $('[name="' + xl.text().split('>>')[1] + '"]').offset() || false;
    if(offset) {
      $('html, body').animate({
        scrollTop: offset.top - ( $('#overlay_menu').height() + 10 )
      }, 250);
    }
    return false;
  })
  .on('click', '.dice', function() {
    if(typeof $(this).data('html') === 'undefined') $(this).data('html', $(this).html());
    var htm = stripHTML($(this).html());
    $(this).html($(this).attr('title'));
    $(this).attr('title', htm);
  })
  .on('click', '.reflink a', function(ev) {
    ev.preventDefault()
    let ref = this.parentElement
    , postnode = ref._findParent('.postnode')
    if (!lastActiveForm) {
      quickreply.bind(postnode.querySelector('.qrl'))()
    }
    else {
      let formBoard = is_overboard
        ? lastActiveForm.getAttribute('id').match(/[0-9]+\-(.+?)_qrf/)[1]
        : this_board_dir
      let postBody = postnode.querySelector('.postbody')
      , qoutation = getPostQuotation(postBody)
      insert(`>>${ref.dataset.b == formBoard ? '' : `/${ref.dataset.b}/`}${ref.dataset.id}\n${qoutation}`)
    }
  })
  //Ultimate YOBA Youtube embeds
  .on('click','.embed-play-button', function(ev) {
    ev.preventDefault()
    unwrapEmbed($(this).parents('figure'))
  })
  .on('click','.collapse-video', function(ev) {
    ev.preventDefault()
    wrapEmbed($(this).parents('figure'))
  })
  .on('mouseenter', '._country_', function() {
    if(typeof $(this).attr('title') === "undefined") {
      $(this).attr('title', countries[$(this).attr('src').split('flags/')[1].split('.png')[0].toUpperCase()]);
    }
  })
  .on('click', '.audiowrap', function(ev) {
    ev.preventDefault();
    let $this = $(this)
    var src = $this.attr('href');
    let $fig = $this.parents('figure')
    if (! $fig.hasClass('unwrapped')) {
      $fig.addClass('unwrapped unwrapped-audio')
    }
    let $fsz = $fig.find('.filesize');
    if (! $fsz.find('.collapse-btn').length)
      $fsz.append(`
        <button title="${_l.collapse}" class="emb-button collapse-video">
          ${makeIcon('shrink', 'b-icon')}
        </button>`);
    if (! $this.find('audio').length)
      $this.append('<audio src="'+src+'" controls autoplay="true"></audio>')
    $fsz.find('.collapse-video').click(function() {
      $fig.removeClass('unwrapped unwrapped-audio')
      .find('audio')[0].pause()
    })
  })
  .on('change', '.multidel', function() {
    let itemCount = $('.multidel:checked').length

    if (! $('.item-count').length) {
      $('.userdelete tbody').prepend(`<tr><td>
        ${_l.selected}:&nbsp;<span class="item-count"></span>
        <button class="close-multisel icon-wraping-button" title="${_l.cancel}">${makeIcon('x', 'b-icon ', false)}</button>
      </td></tr>`)
      $('.close-multisel').click(function(ev) {
        ev.preventDefault()
        $('.multidel').prop('checked', false)
        $('body').removeClass('select-multiple')
        $('.userdelete').removeClass('ud-active')
      })
    }

    if (itemCount > 0) {
      $('.item-count').text(itemCount)
    }
    else {
      $('.userdelete').removeClass('ud-active')
      $('body').removeClass('select-multiple')
    }
  })
  /* Post menu */ 
  .on('click', 'label.postinfo', function(ev) {
    if (! $(this).find('.multidel').is(':visible')) {
      ev.stopPropagation()
      ev.preventDefault()
      let $this = $(this)
      , $menu = $this.parent().find('.post-menu')
      let on = !$menu.is(':visible')
      $('.post-menu').hide()
      if (! $menu.length) {
        let $postnode = $(this).parents('.postnode')
        , board = $postnode.data('board')
        , id = $postnode.data('id')
        , isOP = $postnode.hasClass('op')
        /*if (!isOP)
          $postnode.toggleClass('post-hidden', HiddenItems.isHidden('post', id))*/
        this.insertAdjacentHTML('afterend', `<div class="content-background post-menu" style="display: none"> 
          <ul>
            <li class="menu-delete">${makeIcon('x')}<span>${_l.del}</span></li>
            ${!isOP ? `
              ${typeof opmod !== 'undefined' && +opmod || 0 ? `<li class="menu-delete menu-delete-op">${makeIcon('x')}<span>${_l.del} ${_l.asOP}</span></li>`: ''}
              <li class="menu-hide">${makeIcon('hide')}<span>${_l.hidePost}</span></li>
              <li class="menu-unhide">${makeIcon('unhide')}<span>${_l.unhidePost}</span></li>
            `: ''}
            <li class="menu-report">${makeIcon('warning')}<span>${_l.report}</span></li>
            <li class="menu-link-share">${makeIcon('link')}<span>${_l.links}</span></li>
            ${$postnode.find('.post-ttl').length ? `
              <li class="menu-cancel-timer">${makeIcon('untimer')}<span>${_l.cancelTimer}</span></li>
            `: ''}
            <li class="menu-select-multiple">${makeIcon('select-multiple')}<span>${_l.selectMultiple}</span></li>
            ${kumod_set ? `
              <li class="menu-delete menu-delete-mod">${makeIcon('x')}<span>${_l.del} ${_l.asMod}</span></li>
              <a href="${ku_cgipath}/manage_page.php?action=bans&amp;banboard=${board}&amp;banpost=${id}">
                <li class="menu-ban">${makeIcon('ban')}<span>${_l.ban}...<span></li>
              </a>
              ${isOP ? `
                <a class="stickypost-btn" onclick="Ajax.modThread(this, 'stickypost'); return false"
                href="${ku_cgipath}/manage_page.php?action=stickypost&amp;board=${board}&amp;postid=${id}">
                  <li>${makeIcon('pin')}<span>${_l.stickthread}<span></li>
                </a>
                <a class="unstickypost-btn" onclick="Ajax.modThread(this, 'unstickypost'); return false"
                href="${ku_cgipath}/manage_page.php?action=unstickypost&amp;board=${board}&amp;postid=${id}">
                  <li>${makeIcon('unpin')}<span>${_l.unstickthread}<span></li>
                </a>
                <a class="lockpost-btn" onclick="Ajax.modThread(this, 'lockpost'); return false"
                href="${ku_cgipath}/manage_page.php?action=lockpost&amp;board=${board}&amp;postid=${id}">
                  <li>${makeIcon('lock')}<span>${_l.lockthread}<span></li>
                </a>
                <a class="unlockpost-btn" onclick="Ajax.modThread(this, 'unlockpost'); return false"
                href="${ku_cgipath}/manage_page.php?action=unlockpost&amp;board=${board}&amp;postid=${id}">
                  <li>${makeIcon('unlock')}<span>${_l.unlockthread}<span></li>
                </a>`
              : ''}`
            : ''}
          </ul>
        </div>`)
        $menu = $this.parent().find('.post-menu')
      }
      $menu.toggle(on)
    }
  })
  // Figure menu is global because of FUCKING overflow
  .on('click', '.file-menu-toggle', function(ev) {
    ev.stopPropagation()
    ev.preventDefault()
    let $menu = $('.file-menu')
    , $this = $(this)
    , $fsz = $this.parent()
    , visible = $menu.is(':visible')
    $('.post-menu').hide()
    if (visible && $menu[0].$boundTo[0] == $fsz[0]) return;
    let ofs = $fsz.offset()
    , offsetTop = $fsz.hasClass('embed-wrap') ? 22 : $fsz.outerHeight()
    $menu.css({
      'left': `${ofs.left}px`,
      'top': `${ofs.top + offsetTop}px`,
      'min-width': `${$fsz.outerWidth()}px`
    }).show()
    let fileid = $fsz.parents('figure').data('fileid')
    , $pn = $fsz.parents('.postnode')
    , board = $pn.data('board')
    , pid = $pn.data('id')
    $menu[0].__menuProps = {
      fileid: fileid,
      board: board
    }
    $menu[0].$boundTo = $fsz
    // Set the proper link for file banning
    $menu.find('.menu-banfile').attr('href', `${ku_cgipath}/manage_page.php?action=bans&banboard=${board}&banpost=${pid}&banfile=${fileid}`)
  })
  .on('click', '.post-menu li', ev => ev.stopPropagation())
  .on('click', '.menu-select-multiple', function(ev) {
    $('.post-menu').hide()
    let $menu = $(this).parents('.post-menu')
    , isFile = $menu.hasClass('file-menu')
    , $md = (isFile ? $menu[0].$boundTo : $(this).parents('.posthead'))
    .find('.multidel')
    if (! $md.length) return;
    $md.prop('checked', true).trigger('change')
    $('.userdelete').addClass('ud-active')
    $('body').addClass('select-multiple')
  })
  .on('click', '.menu-link-share', function(ev) {
    let $this = $(this)
    , isShown = $this.find('input').length
    if (! isShown) {
      let $postnode = $this.parents('.postnode')
      , board = $postnode.data('board')
      , id = $postnode.data('id')
      , directLink = ku_boardspath + $postnode.find('.shl').attr('href')
      $this.find('span').html(`<input class="pm-direct-link pm-link" type="text" value="${directLink}" title="${_l.directLink}">&nbsp;
        <input class="pm-quote-link pm-link" type="text" value="&gt;&gt;/${board}/${id}" title="${_l.quoteLink}">`)
      .css({'font-size': 0})
      .find('.pm-direct-link').click()
    }
    else
      $this.toggleClass('direct-or-quote')
  })
  .on('click', '.pm-link', function(ev) {
    ev.stopPropagation()
    let $this = $(this)
    $('.pm-link').removeClass('selected')
    $this.focus().select().addClass('selected')
  })
  .on('click', '.menu-delete', function() {
    let $this = $(this)
    , $menu = $this.parents('.post-menu')
    , menu = $menu[0]
    , isFile = $menu.hasClass('file-menu')
    , $item = isFile ? $menu[0].$boundTo : $this.parents('.posthead')
    , $pass = $menu.find('.menu-password')
    $this.addClass('spin-around')
    let fd = new FormData()
    if ($menu.find('.menu-captcha').length) {
      let $c = $menu.find('input[name=captcha]')
      if (!$c.val()) {
        $c.focus()
        pups.warn(_l.enterCaptcha)
        $this.removeClass('spin-around')
        return;
      }
      fd.append('captcha', $c.val())
    }
    if (isFile)
      fd.append('delete-file[]', menu.__menuProps.fileid)
    else
      fd.append('post[]', $item.parents('.postnode').data('id'))
    let asMod = $this.hasClass('menu-delete-mod')
    fd.append('moddelete', asMod)
    fd.append('opdelete', +$this.hasClass('menu-delete-op'))
    fd.append('board', isFile 
      ? menu.__menuProps.board
      : $item.parents('.postnode').data('board'))
    fd.append('postpassword', $pass.length
      ? $pass.find('input').val()
      : $(`#delform input[name="postpassword"]`).val())
    Ajax.deleteItems(fd, data => {
      $this.removeClass('spin-around')
      if (!data) return;
      let result = data[0]
      if(!result.success && $item.is(':visible')) {
        if (asMod) return;
        if (!$pass.length) {
          $menu.prepend(`<li class="menu-password">${makeIcon('password')}
            <input value="${get_password("postpassword")}" class="make-me-readonly" type="password" placeholder="${_l.password}" readonly onfocus="this.readOnly=false"></li>`)
        }
        if (result.special_error && result.special_error == 'captchalocked') {
          addCaptchaToMenu($menu)
        }
        else {
          $menu.find('.menu-password input').select()
          .keydown(ev => {
            if (ev.key == 'Enter') {
              ev.preventDefault()
              ev.stopPropagation()
            }
          })
        }
      }
      else if (isFile)
        $('.file-menu').hide()
    })
  })
  .on('click', '.menu-report', function() {
    let $this = $(this)
    , $postnode = $this.parents('.postnode')
    $this.addClass('spin-around')
    let fd = new FormData()
    fd.append('post[]', $postnode.data('id'))
    fd.append('board', $postnode.data('board'))
    Ajax.reportPost(fd, data => {
      $this.removeClass('spin-around')
    })
  })
  .on('click', '.menu-hide', function() {
    let $postnode = $(this).parents('.postnode')
    HiddenItems.hidePost($postnode.data('id')+'-'+$postnode.data('board'))
    $postnode.addClass('post-hidden')
    .find('.post-menu').hide()
  })
  .on('click', '.menu-unhide', function() {
    let $postnode = $(this).parents('.postnode')
    HiddenItems.unhidePost($postnode.data('id')+'-'+$postnode.data('board'))
    $postnode.removeClass('post-hidden')
  })
  .on('click', '.menu-cancel-timer', function() {
    let $this = $(this)
    , $menu = $this.parents('.post-menu')
    , menu = $menu[0]
    , $pass = $menu.find('.menu-password')
    , $postnode = $this.parents('.postnode')
    $this.addClass('spin-around')
    let fd = new FormData()
    if ($menu.find('.menu-captcha').length) {
      let $c = $menu.find('input[name=captcha]')
      if (!$c.val()) {
        $c.focus()
        pups.warn(_l.enterCaptcha)
        $this.removeClass('spin-around')
        return;
      }
      fd.append('captcha', $c.val())
    }
    fd.append('post[]', $postnode.data('id'))
    fd.append('board', $postnode.data('board'))
    fd.append('modsave', $this.hasClass('menu-delete-mod'))
    fd.append('opsave', $this.hasClass('menu-delete-op'))
    fd.append('postpassword', $pass.length
      ? $pass.find('input').val()
      : $(`#delform input[name="postpassword"]`).val())
    Ajax.cancelTimer(fd, errors => {
      $this.removeClass('spin-around')
      if (errors.length) {
        let result = errors[0]
        if (!$pass.length) {
          $menu.prepend(`<li class="menu-password">${makeIcon('password')}
            <input value="${get_password("postpassword")}" class="make-me-readonly" type="password" placeholder="${_l.password}" readonly onfocus="this.readOnly=false"></li>`)
        }
        if (result.special_error && result.special_error == 'captchalocked') {
          addCaptchaToMenu($menu)
        }
        else {
          $menu.find('.menu-password input').select()
          .keydown(ev => {
            if (ev.key == 'Enter') {
              ev.preventDefault()
              ev.stopPropagation()
            }
          })
        }
      }
      else {
        $menu.find('.menu-password, .menu-captcha').remove()
      }
    })
  })
  .on('click', '.csswrap', function(ev) {
    ev.preventDefault()
    let cssLink = $(this).attr('href')
    , styleName = $(this).parent().find('.fc-filename').text()
    Styles.testStyle(cssLink, styleName)
  })
  .on('blur', 'input[name="name"]', function() {
    let isTrip = ~this.value.indexOf('#')
    this.type = isTrip ? 'password' : 'text'
  })
  .on('focus', 'input[name="name"]', function() {
    this.type ='text'
  })

  offClick.push(ev => {
    $('.post-menu').hide()
  })

  $(window).resize(() => {
    let $fileMenu = $('.file-menu')
    if (! $fileMenu.is(':visible')) return;
    let $fsz = $fileMenu[0].$boundTo
    , ofs = $fsz.offset()
    , offsetTop = $fsz.hasClass('embed-wrap') ? 22 : $fsz.outerHeight()
    $fileMenu.css({
      'left': `${ofs.left}px`,
      'top': `${ofs.top + offsetTop}px`,
      'min-width': `${$fsz.outerWidth()}px`
    })
  })

  $('.userdelete').addClass('content-background reflinkpreview')
  .find('tbody').append(`<tr><td><input name="moddelete" value="${_l.del} (${_l.mod_})" type="submit" class="styled-button bad-button" /></td></tr>`)

  $('#delform').on('submit', function(e) {
    e.preventDefault()
    Ajax.deleteItems(new FormData(this))
    return false
  })

  $('.userdelete input[type=submit]').click(function(e) {
    e.preventDefault()
    e.stopPropagation()
    let fd = new FormData($(this).parents('form')[0])
    if (this.name == 'deletepost' || this.name == 'moddelete') {
      Ajax.deleteItems(fd, null, this.name == 'moddelete')
    }
    if (this.name == 'cancel_timer') {
      Ajax.cancelTimer(fd)
    }
    if (this.name == 'reportpost') {
      Ajax.reportPost(fd)
    }
  })

  /*$('input[name=reportpost]').click(function(e) {
    e.preventDefault()
    Ajax.reportPost(new FormData($(this).parents('form')[0]))
  })*/
  if (!is_overboard)
    $('#delform').after('<div id="rswapper">[<a onclick="javascript:rswap.swap();return false;" href="#">'+(ispage ? _l.NewThread : _l.reply)+'</a>]<hr /></div>');

  Settings.sfwMode(false);
  if (localStorage) {
    for(var s in Settings) {
      if (s.substring(0,1) == "_") continue;
      $("#js_settings").append('<label><input type="checkbox" onchange="javascript:Settings.'+s+'(true)" name="settings_'+s+'" id="settings_'+s+'" value="true"> '+_l['settings_'+s]+'</label><br />');
      Settings[s](false);
    }
  } else {
    $("#js_settings").append("<span style=\"color:#F00\">"+_l.noLocalStorage+"</span><br />Твой браузер — говно. Скачай <a href=\"/web/20110329072959/http://google.com/chrome\" target=\"_blank\">Chome</a>, например.");
  }

  var textbox = document.getElementById('message');
  if(textbox) {
    textbox.onfocus=function(){is_entering = true;}
    textbox.onblur=function(){is_entering = false;}
  }

  // Figure menu is global because of FUCKING overflow
  $('body').append(`
    <div class="content-background post-menu file-menu" style="display: none"> 
      <ul>
        <li class="menu-delete">${makeIcon('x')}<span>${_l.del}</span></li>
        <li class="menu-select-multiple">${makeIcon('select-multiple')}<span>${_l.selectMultiple}</span></li>
        ${kumod_set ? `
          <li class="menu-delete menu-delete-mod">${makeIcon('x')}<span>${_l.del} ${_l.asMod}</span></li>
          <a href="${ku_cgipath}/manage_page.php?action=bans" class="menu-banfile">
            <li>${makeIcon('ban')}<span>${_l.ban} ${_l.file.toLowerCase()}...</span></li>
          </a>` 
        : ''}
      </ul>
    </div>
  `)
  
  //animation hooks  
  $(document).on('animationstart webkitAnimationStart MSAnimationStart oanimationstart', function(event) {
    var $target = $(event.target)
    //detect node insertions and process them
    if (event.originalEvent.animationName == "nodeInserted" && !$target.hasClass('_inserted_'))
      processNodeInsertion($target)
    //detect window resize
    if (event.originalEvent.animationName == "windowSmall") {
      document.querySelectorAll('.quick-reply-form').forEach(f => {
        toggleFormFloating(f, false)
        toggleFormSimplify(f, true)
      })
    }
  });

  Captcha.init(is_overboard)

  initForm(document.querySelector('#postform'))

  if(typeof is_catalog !== 'undefined' && is_catalog) catalog.init();

  $('<div id="tripinfo"></div>').addClass('content-background reflinkpreview qreplyform').hide().appendTo('body');

  $('#delform').on('click', '.postertrip', function(ev) {
    ev.preventDefault()
    ev.stopPropagation()
    var trip = $(this).text().split('!')[1]
    , offset = $(this).offset(), height = $(this).height()
    $.getJSON('/tripinfo.php', { trip: trip })
    .done(function(data) {
      var active_on = [];
      _.each(data.active_on, function(board) {
        active_on.push('<a target="_blank" href="'+ku_boardspath+'/'+board+'/">'+'/'+board+'/</a>');
      })
      $('#tripinfo')
      .html(
        '<div><b class="postertrip">!'+trip+'</b> ['+'<a href="https://www.google.com/search?q=!'+trip+'" target="_blank">G</a>]</div>'+
        '<a style="float:left;" href="#" onclick="javascript:$(\'#tripinfo\').hide();return false;"><svg style="position: absolute; top: 3px; right: 3px;" class="icon b-icon"><use xlink:href="#i-x"></use></svg></a>'+
        '<div class="trip-info-line">'+_l.threads+': '+data.threads+', '+_l.comments+': '+data.comments+'</div>'+
        '<div class="trip-info-line">'+_l.active_since+': '+catalog.formatDate(data.active_since, true)+'</div>'+
        '<div class="trip-info-line">'+_l.last_active+': '+catalog.formatDate(data.last_active, true)+'</div>'+
        (active_on.length ? '<div class="trip-info-line">'+_l.active_on+': '+active_on.join(', ')+'</div>' : '')
      )
      .css({
         top: offset.top + height,
         left: offset.left
       })
      .fadeIn('fast');
    })
    .fail(function(error) {
      console.error(error)
    })
  })

  unreadCounter.update()

  if(liveupd_ena && typeof io !== 'undefined')
    updater.init()

  onReady.ready()
}

/*
 * jQuery Double Tap https://gist.github.com/attenzione/7098476
 * Developer: Sergey Margaritov (github.com/attenzione)
 * License: MIT
 * Date: 22.10.2013
 * Based on jquery documentation http://learn.jquery.com/events/event-extensions/
 */
(function($){
  $.event.special.doubletap = {
    bindType: 'touchend',
    delegateType: 'touchend',

    handle: function(event) {
      var handleObj   = event.handleObj,
          targetData  = jQuery.data(event.target),
          now         = new Date().getTime(),
          delta       = targetData.lastTouch ? now - targetData.lastTouch : 0,
          delay       = delay == null ? 300 : delay,
          e           = event.originalEvent

      if (delta < delay && delta > 30 && !(e.touches && e.touches.length > 1)) {
        targetData.lastTouch = null;
        event.type = handleObj.origType;
        ['clientX', 'clientY', 'pageX', 'pageY'].forEach(function(property) {
          event[property] = event.originalEvent.changedTouches[0][property];
        })

        // let jQuery handle the triggering of "doubletap" event handlers
        handleObj.handler.apply(this, arguments);
      } else {
        targetData.lastTouch = now;
      }
    }
  };
})(jQuery)

const NTP = {
  calc: (t0, t1, t2, t3) => ((t1 - t0) + (t2 - t3)) / 2,
  sync: function(force) {
    this.pending = true
    let _this = this
    , t0 = (new Date()).valueOf()
    $.ajax({
      url: '/ntp.php',
      success: function(servertime) {
        let t1 = servertime,
            t2 = servertime,
            t3 = (new Date()).valueOf()

        _this.offset = _this.calc(t0, t1, t2, t3)
      },
      error: function() {
        pups.err('Failed to synchronize time with the server')
      },
      complete: function() {
        _this.synced = true

        while (_this.stack.length) {
          _this.stack.shift().resolve(_this.timeWithOffset)
        }
      }
    });
  },
  offset: 0,
  stack: [],
  get timeWithOffset() {
    return new Date(new Date().getTime() + this.offset).getTime()
  },
  getServerTime: function() {
    if (this.synced)
      return Promise.resolve(this.timeWithOffset)

    let promise = externallyResolvingPromise()
    if (! this.pending)
      this.sync()
    this.stack.push(promise)
    return promise.promise
  }
}

function externallyResolvingPromise() {
  let promiseResolve, promiseReject
  , promise = new Promise((resolve, reject) => {
    promiseResolve = resolve
    promiseReject = reject
  });

  return {
    promise: promise,
    resolve: promiseResolve,
    reject: promiseReject
  }
}

// this will be applied to every new inserted node (post)
function processNodeInsertion($node) {
  if(typeof $node === 'undefined')
    $node = $('body');
  else {
    $node.addClass('_inserted_');
    $node = $node.parents(":eq(1)");
  }
  if($node.find('.prettyprint').length)
    prettyPrint.apply(window);
  LatexIT.render($node);

  // Post time-to-live updating
  $node.find('.post-ttl:not(._inserted_)').each(function() {
    let $this = $(this)
    $this.addClass('_inserted_')
    let iv
    , updateTTL = () => {
      if ($this.length) {
        NTP.getServerTime().then(srvTime => {
          let ms_left = $this.data('deleted-timestamp')*1000 - srvTime
          if (ms_left >= 0) {
            let min_left = Math.round(ms_left / 60000)
            , h_left = Math.floor(min_left / 60)
            min_left -= h_left * 60
            $this.text(`${_.padLeft(h_left, 2, '0')}:${_.padLeft(min_left, 2, '0')}`)
          }
          else {
            $.get(document.forms.delform.action) // call board.php to force delete posts
            deleteItems([{action: 'delete_post', id: $this.parents('.postnode').data('id')}], false, updater.markOnly)
            clearInterval(iv)
          }
        })
      }
      else
        clearInterval(iv)
    }
    iv = setInterval(updateTTL, 30 * 1000)
    updateTTL()
  })
}

const chpok = {
  init: function(atLoad = false) {
    document.body.insertAdjacentHTML('beforeend', `<audio id="chpok-audio" style="display:none">
      <source src="${ku_boardspath}/images/chpok.mp3">
      <source src="${ku_boardspath}/images/chpok.ogg">
    </audio>`)
    this.initiated = true
    this.audio = document.querySelector('#chpok-audio')
  },
  play: function() {
    if (! this.initiated)
      this.init()
    // In chrome the sound won't play, causing a DOM exception, if a user hasn't clicked a page at least once (and it's unfixable)
    this.audio.play().catch(e => _.noop)
  }
}

const updater = {
  newThreads: [],
  init: function () {
    this.socket = io.connect(liveupd_api);
    let subscribeTo
    if(ispage) {
      subscribeTo = [liveupd_sitename+this_board_dir+':threads']
      document.querySelectorAll('.postnode.op').forEach(thr => subscribeTo.push(liveupd_sitename+thr.dataset.board+':'+thr.dataset.id))
    }
    else {
      subscribeTo = [liveupd_sitename+this_board_dir+':'+$('input[name=replythread]').val()];
      _l.noNewPosts += ("<br>" + _l.threadUpdationAutomatically)
    }
    this.socket
    .on('update', this.dispatch.bind(this))
    .emit('subscribe', subscribeTo)
    Object.defineProperty(this, 'markOnly', {
      value: true,
      writable: false,
      configurable: false
    })
  },
  dispatch: function(data) {
    if (! data.action) {
      pups.warn('Event with unspecified action, see console')
      console.warn(data)
      return
    }
    if (data.action == 'new_thread') {
      // Do nothing if it is my post
      if (data.token && Ajax.postToken && data.token==Ajax.postToken) return;
      this.notifyAboutNewThreads(data)
    }
    if (data.action == 'new_reply') {
      // Do nothing if it is my post
      if (data.token && Ajax.postToken && data.token==Ajax.postToken) return;
      if (ispage) {
        this.notifyAboutNewRepliesOnBoardPage(data)
      }
      else {
        this.showNewReplies(data)
      }
    }
    if (data.action == 'delete') {
      // Do nothing if it is my post
      if (data.token && Ajax.delToken && data.token==Ajax.delToken) return;
      deleteItems(data.items, false, this.markOnly)
    }
    if (data.action == 'cancel_timer') {
      // Do nothing if it is my post
      if (data.token && Ajax.timerToken && data.token==Ajax.timerToken) return;
      let postsSaved = [], msg = ''
      data.items.forEach(item => {
        $(`.postnode[data-id=${item.id}] .post-ttl`).remove()
        postsSaved.push(item.id)
      })
      if(postsSaved.length) {
        msg = postsSaved.length > 1 ?
          `${_l.posts} #${postsSaved.join(', #')} ${_l.savedMulti}.`
         :`${_l.post} #${postsSaved[0]} ${_l.saved}.`
        pups.info(msg, {time: 2 + postsSaved.length})
      }
    }
  },
  showNewReplies: function(data) {
    newposts.get({
      silent: true,
      timestamp: data.timestamp,
      onSuccess: () => {
        if (! document.hasFocus())
          chpok.play()
      }
    })
  },
  notifyAboutNewThreads: function(data) {
    this.newThreads.push({
      board: data.board,
      board_desc: data.board_desc || false,
      thread: data.new_thread_id
    });
    if (! document.hasFocus()) {
      scrollAnchor.save('autoload', '.postnode')
      this.showNewThreads(() => scrollAnchor.restore('autoload'))
      chpok.play()
    }
    else if(!$('#wild_thread_appeared').length) {
      $('#delform').before('<a class="xlink" onclick="updater.showNewThreads();return false" id="wild_thread_appeared"><span class="salient">'+_l.newThreadsAvailable+'</span><hr /></a>')
    }
  },
  notifyAboutNewRepliesOnBoardPage: function(data) {
    let room = data.room + '-' + data.board
    if (! this.repliesOnBoardPage[room])
      this.repliesOnBoardPage[room] = [data.reply_id]
    else
      this.repliesOnBoardPage[room].push(data.reply_id)
    this.refreshNewRepliesCount(data.board, data.room, data.timestamp)
  },
  refreshNewRepliesCount(board, threadID, timestamp=null) {
    let thread = threadID + '-' + board
    , count = (this.repliesOnBoardPage[thread] || []).length
    , $target = $('[id^=replies'+thread+']')
    , $counter = $target.find('.fresh-replies')
    let clearCounter = () => this.repliesOnBoardPage[thread] = []
    if (count) {
      if (! $counter.length) {
        function loadNewReplies(callback) {
          newposts.get({
            threadid: thread,
            onSuccess: () => {
              clearCounter()
              if (callback)
                callback()
            },
            onError: (e) => {
              console.error(e)
              pups.err(_l.oops)
            },
            silent: true,
            timestamp: timestamp
          })
        }
        if (! document.hasFocus()) {
          scrollAnchor.save('autoload', '.postnode')
          loadNewReplies(() => scrollAnchor.restore('autoload'))
          chpok.play()
        }
        else {
          let $freshReplies = $target.append('<br class="fresh-replies-breaker"><a href="/'+ board +'/res/'+threadID+'.html" class="salient fresh-replies">'+_l.newReplies+': <span class="fresh-replies-number"></span></a>')
          .find('.fresh-replies')
          , freshReplies = $freshReplies[0]
          $freshReplies.click(function(e) {
            e.preventDefault()
            if ($freshReplies.__alreadyLoading)
              return;
            freshReplies.__alreadyLoading = true
            freshReplies.insertAdjacentHTML('afterBegin', '<div class="spinner"></div>')
            loadNewReplies(() => $(freshReplies).remove())
          })
        }
      }
      $target.find('.fresh-replies-number').text(count)
    }
    else if($counter.length) {
      $counter.remove()
      clearCounter()
    }
  },
  repliesOnBoardPage: {},
  showNewThreads: function(callback) {
    let $wta = $('#wild_thread_appeared')
    if ($wta.length)
    $wta.find('span')[0].insertAdjacentHTML('afterBegin', '<div class="spinner"></div>')
    this.newThreads.forEach(thr => {
      HTMLoader.getThread(thr.board, thr.thread, null, (err, posts) => {
        if (err)
          return pups.err(_l.noDataLoaded)
        $wta.remove()
        document.querySelector('#delform').insertAdjacentHTML('afterBegin', `
            ${posts}
            <div id="replies${thr.thread}-${thr.board}" class="replies"></div>
          </div>
          <br clear="left" />
          <hr>`);
        if (is_overboard && thr.board_desc) {
          document.querySelector('#delform .posthead').insertAdjacentHTML('afterbegin', 
            `<a href="/${thr.board}" target="_blank" class="over-boardlabel">/${thr.board}/ — ${thr.board_desc}</a>`)
        }
        this.socket.emit('subscribe', liveupd_sitename+thr.board+':'+thr.thread)
        if (callback) 
          callback()
      })
    })
    this.newThreads = [];
  }
}

function deleteItems(items, bySelf=true, markOnly=false) {
  let postsDeleted = []
  , postErrors = []
  , insideDeletedThread
  , filesDeleted = []
  , fileErrors = []

  items.forEach(item => {
    let deletedClasses = 'deleted'
      + (item.by_op ? ' op-deleted' : '')
      + (item.by_mod ? ' mod-deleted' : '')

    if (item.action == 'delete_post' || item.itemtype == 'post') {
      if (bySelf && !item.success) {
        postErrors.push(item)
        return
      }
      insideDeletedThread = false
      let $thread = $(`#delform [id^=thread${item.id}]`)
      , $reply = $(`#delform [id^=reply${item.id}]`)
      if ($thread.length) {
        if (markOnly || !ispage) {
          $thread.addClass('deleted')
          .find('.reply, figure').addClass(deletedClasses)
          if (!ispage) {
            insideDeletedThread = true
            $('form[name=postform], .qrl, #rswapper').remove()
            pups.warn(`${_l.thread} ${_l.deleted}!`, {time: 0})
          }
        }
        else {
          $thread.prev().remove() //unhidethread
          $thread.next().remove() //br
          $thread.next().remove() //hr
          $thread.remove()
        }
      }
      else if ($reply.length) {
        if (markOnly)
          $reply.addClass(deletedClasses)
        else {
          let $reply = $(`#delform [id^=reply${item.id}]`)
          if ($reply.length) {
            $reply.parents('.postnode').remove()
          }
        }
      }
      else {
        if (updater.repliesOnBoardPage[item.thread_id])
          updater.repliesOnBoardPage[item.thread_id] = updater.repliesOnBoardPage[item.thread_id].filter(i => i!=item.id)
        updater.refreshNewRepliesCount(item.thread_id)
      }
      if (!insideDeletedThread)
        postsDeleted.push('#'+item.id)
    }

    if (item.action == 'delete_file'  || item.itemtype == 'file') {
      if (bySelf && !item.success) {
        fileErrors.push(item)
        return
      }
      let $fig = $(`figure[data-fileid=${item.id}]`)
      if (markOnly)
        $fig.addClass(deletedClasses)
      else
        $fig.replaceWith(`<div class="nothumb">${_l.fileRemoved}</div>`)
      filesDeleted.push('#'+item.id)
    }

    if (item.action == 'delete_thread') { // A notification about deleted
      updater.newThreads = updater.newThreads.filter(i => i!=item.id)
      if (! updater.newThreads.length)
        $('#wild_thread_appeared').remove();
      pups.info(`${_l.thread} ${item.id} ${_l.deleted}.`)
    }
  })

  if (! $('.multidel').is(":checked") && ! $('.multidel.delete-file').is(":checked")) {
    $('.userdelete').removeClass('ud-active')
    $('body').removeClass('select-multiple')
  }
    
  if (postsDeleted.length) {
    let msg = postsDeleted.length > 1
      ? `${_l.posts} ${postsDeleted.join(', ')} ${_l.deletedMulti}.`
      : `${_l.post} ${postsDeleted[0]} ${_l.deleted}.`
    pups[bySelf ? 'succ' : 'info'](msg, {time: 2 + postsDeleted.length})
  }
  if (filesDeleted.length) {
    let msg = filesDeleted.length > 1
      ? `${_l.files} ${filesDeleted.join(', ')} ${_l.deletedMulti}.`
      : `${_l.file} ${filesDeleted[0]} ${_l.deleted}.`
    pups[bySelf ? 'succ' : 'info'](msg, {time: 2 + filesDeleted.length})
  }
  if (postErrors.length)
    pups.err(postErrors.map(err => `${_l.post} #${err.id}: ${err.message}`).join('<br>'), 
      {time: 2 + postErrors.length})
  if (fileErrors.length) {
    pups.err(fileErrors.map(err => `${_l.file} #${err.id}: ${err.message}`).join('<br>'), 
      {time: 2 + fileErrors.length})
  }
}

if (+localStorage['localmod']) {
  kumod_set = true
}
else {
  let kumod = getCookie('kumod');
  if (kumod !== '') {
    if (kumod === 'allboards')
      kumod_set = true
    else
      kumod_set = in_array(this_board_dir, kumod.split('|'));
  }
}

function expandVideo($mov, ev) {
  //good luck understanding this shitcode :^)
  let $reply = $mov.parents('.reply')
  if($mov.data('expanded') !== '1') {
    ev.preventDefault();
    var movieurl = $mov.attr('href'), imgh = $mov.data('height'), imgw = $mov.data('width'), dt = $mov.data('thumb'), postnum = $mov.data('id');
    var uid = '_vframe_'+randomString(5)+(new Date().getTime());
    $mov.replaceWith(function() {
      return '<span id="'+uid+'" data-thumb="'+dt+'" data-width="'+imgw+'"" data-height="'+imgh+'" data-href="'+movieurl+'">'+this.innerHTML + '</span>';
    });
    $mov = $("#"+uid);
    $mov.find('img').hide();
    var video = $mov.find('video').show(), notice = '';
    if(!video.length) {
      $mov.find('.playable-thumb').append('<video class="thumb" src="'+movieurl+'" controls loop autoplay height="'+imgh+'" width="'+imgw+'"></video>').promise().done(function() {
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
        notice = _l.videoDownscaledBy + " " + Math.round(zoom*100) + "% "+_l.toFit;
      }
    }
    let $fig = $mov.parents('figure');
    if (! $fig.hasClass('unwrapped')) {
      $fig.addClass('unwrapped')
    }
    let $fsz = $mov.parent().find('.filesize');
    if (! $fsz.find('.collapse-btn').length) {
      $fsz.append(`
        <button title="${_l.collapse}" class="emb-button collapse-video">
          ${makeIcon('shrink', 'b-icon')}
        </button>`);
      $mov.parent().find('.collapse-video').click(function() {
        $fig.removeClass('unwrapped')
        var uid = '_vframe_'+randomString(5)+(new Date().getTime());
        $mov.replaceWith(function() {
          return '<a class="movie" id="'+uid+'" data-thumb="'+dt+'" data-width="'+imgw+'"" data-height="'+imgh+'" href="'+movieurl+'">'+this.innerHTML + '</a>';
        }).data('expanded', '0');
        $mov = $("#"+uid);
        $mov.find('video').hide()[0].pause();
        $mov.find('img').show();
        $(this).remove();
        $mov.parents('.reply').removeClass('reply-expanded')
        return false;
      });
    }
    $mov.parents('.reply').addClass('reply-expanded')
  }
}

function checknamesave() {
  var checkd;
  if(getCookie('name') != '') {
    checkd = true;
  } else {
    checkd = false;
  }
  var doc = document.getElementById('save');
  if (doc != null) doc.checked = checkd;
}
function checkgotothread() {
  var checkd;
  if(getCookie('tothread') == 'off') {
    checkd = false;
  } else {
    checkd = true;
  }
  $("#gotothread").attr('checked', checkd);
}

function navigatepages (event) {
  if (!document.getElementById) return;
    if (is_entering) return;
  if (window.event) event = window.event;

  if (event.ctrlKey) {
    var link = null;
    var href = null;

    var docloc = document.location.toString();
    if (docloc.indexOf('/res/') != -1) {
      if( (event.keyCode ? event.keyCode : event.which ? event.which : null) == 13 ) {
        handleCtrlEnter(event)
      }
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
      let match;
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
          handleCtrlEnter(event)
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
      if (href) redirectTo(href);
    }
  }
}
if (window.document.addEventListener) {
   window.document.addEventListener("keydown", navigatepages, false);
} else {
   window.document.attachEvent("onkeydown", navigatepages);
}

NodeList.prototype.forEach = Array.prototype.forEach;
NodeList.prototype.map = Array.prototype.map;

// More efficient parent finder
Element.prototype._findParent = function(selector) {
  let node = this
  while(node && !node.matches(selector)) {
    node = node.parentNode
    if (! node.matches) return null;
  }
  return node
}
Element.prototype._findNext = function(selector) {
  let node = this.nextElementSibling
  while(node && !node.matches(selector)) {
    node = node.nextElementSibling
    if (! node) return null
  }
  return node
}
Element.prototype._findPrevious = function(selector) {
  let node = this.previousElementSibling
  while(node && !node.matches(selector)) {
    node = node.previousElementSibling
    if (! node) return null
  }
  return node
}
Node.prototype._isChildOf = function(parent) {
  let node = this.parentNode
  while (node) {
    if (node == parent) {
      return true
    }
    node = node.parentNode
  }
  return false
}

var replyMap = {
  showReplies: function(root=document) {
    root.querySelectorAll('.postnode').forEach(post => {
      let postNum = post.dataset.id
      , board = post.dataset.board
      , thread = post._findParent('div[id^=thread]')
      , threadID = thread && thread.dataset['threadid']
      , msg = post.querySelector('.postmessage')
      , repliesContainer = post.querySelector('.replieslist')
      if (!this.posts[board])
        this.posts[board] = {}
      if (this.posts[board][postNum]) {
        this.posts[board][postNum].container = repliesContainer
      }
      else {
        this.posts[board][postNum] = {
          container: repliesContainer,
          replies: []
        }
      }
      let links = msg.querySelectorAll(`a[class^=ref\\|]`)
      if (links.length) links.forEach(link => {
        let linkData = link.className.split(' ')[0].split('|')
        , linkDestBoard = linkData[1]
        , linkPostNum = linkData[3]
        , href = `/${board}/res/${threadID}.html#${postNum}`
        , htm = `<a class="ref-reply" href="${href}">&gt;&gt;${board == linkDestBoard ? '' : `/${board}/`}${postNum}</a>`
        if (this.posts[linkDestBoard] && this.posts[linkDestBoard][linkPostNum]) {
          if (! _.includes(this.posts[linkDestBoard][linkPostNum].replies, htm)) {
            this.posts[linkDestBoard][linkPostNum].replies.push(htm)
          }
        }
        else {
          if (!this.posts[linkDestBoard])
            this.posts[linkDestBoard] = {}
          this.posts[linkDestBoard][linkPostNum] = {
            replies: [htm]
          }
        }
        this.posts[linkDestBoard][linkPostNum].skip = false
      })
    })
    _.each(this.posts, board => _.each(board, post => {
      if (!post.skip && post.replies.length && post.container) {
        post.container.innerHTML = `${_l.replies}: ${post.replies.join(', ')}`
        post.skip = true
      }
    }))
  },
  posts: {}
}

var scrollAnchor = {
  save: function(id, elements, parent=window, dimensions='v') {
    if (!window.scrollY) {
      this.saved[id] = {_top: true}
      return
    }
    let mid = [window.innerWidth / 2, window.innerHeight / 2]
    , elMap = []
    , parentBCR = (parent != window)
      ? parent.getBoundingClientRect()
      : {
        left: 0,
        top: 0,
        right: window.innerWidth,
        bottom: window.innerHeight
      }
    if (
      parent != window
      &&
      (
        (parentBCR.left <= 0 && parentBCR.right <= 0)
        ||
        (parentBCR.top <= 0 && parentBCR.bottom <= 0)
        ||
        parentBCR.left >= window.innerWidth
        ||
        parentBCR.top >= window.innerHeight
      )
    ) {
      mid = [parentBCR.left + parentBCR.width/2, parentBCR.top + parentBCR.height/2]
    }
    let elems = elements instanceof Node
      ? [elements]
      : elements instanceof NodeList
        ? elements
        : ((parent == window ? document : parent).querySelectorAll(elements))
    if (! elems.length) return;
    elems.forEach(function (el) {
      let bcr = el.getBoundingClientRect()
      , relativeVisibleWidth = Math.pos(bcr.width - (Math.pos(parentBCR.left - bcr.left) + Math.pos(bcr.right - parentBCR.right))) / bcr.width
      , relativeVisibleHeight = Math.pos(bcr.height - (Math.pos(parentBCR.top - bcr.top) + Math.pos(bcr.bottom - parentBCR.bottom))) / bcr.height
      , dx = Math.abs(mid[0] - (bcr.left + bcr.width/2))
      , dy = Math.abs(mid[1] - (bcr.top + bcr.height/2))
      elMap.push({
        el: el,
        primaryVisibility: dimensions[0] == 'h' ? relativeVisibleWidth : relativeVisibleHeight,
        secondaryVisibility: dimensions[0] == 'v' ? relativeVisibleWidth : relativeVisibleHeight,
        primaryOffset: dimensions[0] == 'h' ? dx : dy,
        secondaryOffset: dimensions[0] == 'v' ? dx : dy
      })
    })
    elMap.sort((a, b) => {
      if (b.primaryVisibility !== a.primaryVisibility) {
        return b.primaryVisibility - a.primaryVisibility
      }
      else if (dimensions.length > 1 && b.secondaryVisibility !== a.secondaryVisibility) {
        return b.secondaryVisibility - a.secondaryVisibility
      }
      else if (a.primaryOffset !== b.primaryOffset) {
        return a.primaryOffset - b.primaryOffset
      }
      else if (dimensions.length > 1) {
        return a.secondaryOffset - b.secondaryOffset
      }
    })
    let anchor = elMap[0].el
    , bcrBefore = anchor.getBoundingClientRect()
    this.saved[id] = {
      anchor: anchor,
      left: bcrBefore.left,
      top: bcrBefore.top,
      parent: parent,
      dimensions: dimensions
    }
  },
  restore: function(id) {
    return new Promise((resolve, reject) => {
      let loaded = this.saved[id]
      if (loaded) window.requestAnimationFrame(() => {
        if (loaded._top) {
          window.scrollTo(0,0)
          resolve()
          return
        }
        let bcrAfter = loaded.anchor.getBoundingClientRect()
        loaded.parent.scrollBy(
          loaded.dimensions.indexOf('h') !== (-1) 
            ? bcrAfter.left - loaded.left 
            : 0, 
          loaded.dimensions.indexOf('v') !== (-1) 
            ? bcrAfter.top - loaded.top 
            : 0
        )
        resolve()
      })
      else reject()
    })
  },
  saved: {}
}
Math.pos = x => x >= 0 ? x : 0;

// requestAnimationFrame polyfill https://gist.github.com/paulirish/1579671
(function() {
  var lastTime = 0;
  var vendors = ['ms', 'moz', 'webkit', 'o'];
  for(var x = 0; x < vendors.length && !window.requestAnimationFrame; ++x) {
    window.requestAnimationFrame = window[vendors[x]+'RequestAnimationFrame'];
    window.cancelAnimationFrame = window[vendors[x]+'CancelAnimationFrame'] || window[vendors[x]+'CancelRequestAnimationFrame'];
  }

  if (!window.requestAnimationFrame)
    window.requestAnimationFrame = function(callback, element) {
      var currTime = new Date().getTime();
      var timeToCall = Math.max(0, 16 - (currTime - lastTime));
      var id = window.setTimeout(function() { callback(currTime + timeToCall); }, timeToCall);
      lastTime = currTime + timeToCall;
      return id;
    };

  if (!window.cancelAnimationFrame)
    window.cancelAnimationFrame = function(id) {
      clearTimeout(id);
    };
}());

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
function menu_pin() {
  if(document.getElementById('overlay_menu').style.position == 'absolute') {
    document.getElementById('overlay_menu').style.position = 'fixed';
    Cookie('ku_menutype', 'fixed', 365);
  } else {
    document.getElementById('overlay_menu').style.position = 'absolute';
    Cookie('ku_menutype', 'absolute', 365);
  }
}

function toggle_oldmenu(on=null) {
  if (on===null)
    on = getCookie('ku_oldmenu') != 'yes'
  injector.inject('oldmenu', `#${on ? 'overlay_menu' : 'head_oldmenu'} { display: none }`)
  if (on && !document.getElementById('head_oldmenu')) {
    document.getElementById('boardlist_header').insertAdjacentHTML('afterBegin', `<div id="head_oldmenu" class="boardlist">
      ${document.getElementById('ns_oldmenu').innerText}
      <a href=\"#\" onclick=\"javascript:toggle_oldmenu();\" class="bl-sect" style="order:1">[overlay]</a>
    </div>`)
  }
  Cookie('ku_oldmenu', on ? 'yes' : 'no', 90); 
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
      txt=txt.replace(/<br>/gi,"").replace(/<br \/>/gi,"").replace(/&amp;/mg,'&');
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

function in_array(needle, haystack) {
  return (typeof haystack !== 'object') ? (needle === haystack) : _.includes(haystack, needle)
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
        if (opt.onMove)
          opt.onMove()
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
  /*$.fn.pin = function() {
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
      this.find('.pinner').removeClass('pinned').addClass('unpinned');
      localStorage['pinForm'] = 0;
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
      this.find('.pinner').removeClass('unpinned').addClass('pinned');
      localStorage['pinForm'] = 1;
    }
  }*/
})(jQuery);

function unwrapEmbed($fig) {
  $fig.addClass('unwrapped')
  let $iw = $fig.find('.emb-iframe-wrapper')
  if ($iw.data('h') > $iw.data('w'))
    $iw.addClass('vertical-video')
  if ($iw.data('site') == "Soundcloud")
    $iw.addClass('soundcloud-embed')
  $iw.css({
    paddingBottom: `${($iw.data('h')/$iw.data('w'))*100}%`
  })
  let code = $iw.data('code')
  , iframeOptions = `frameborder="0" scrolling="no" webkitallowfullscreen="" mozallowfullscreen="" allowfullscreen=""`
  if ($iw.data('site') == "Youtube") {
    let start = $iw.data('startraw')
    $iw.append(`<iframe src="https://www.youtube-nocookie.com/embed/${code}?autoplay=1${start ? `&start=${start}` : ''}" ${iframeOptions}></iframe>`)
  }
  if ($iw.data('site') == "Vimeo") {
    let start = $iw.data('start')
    $iw.append(`<iframe src="//player.vimeo.com/video/${code}?badge=0&autoplay=1${start ? `#t=${start}` : ''}" ${iframeOptions}></iframe>`)
  }
  if ($iw.data('site') == "Soundcloud") {
    let enccode = encodeURI(code)
    let start = $iw.data('start')
    $iw.append(`<iframe src="https://w.soundcloud.com/player/?url=https%3A%2F%2Fapi.soundcloud.com%2Ftracks%2F${enccode}" ${iframeOptions}></iframe>`)
  }
  if ($iw.data('site') == "Coub")
    $iw.append(`<iframe src="http://coub.com/embed/${code}?muted=false&autostart=true&originalSize=false&hideTopBar=false&noSiteButtons=false&startWithHD=false" ${iframeOptions}></iframe>`)
  let $ew = $fig.find('.embed-wrap')
  if (! $ew.find('.collapse-video').length)
    $fig.find('.embed-wrap').append(`
      <button title="${_l.collapse}" class="emb-button collapse-video">
        ${makeIcon('shrink')}
      </button>`)
}

function wrapEmbed($fig) {
  $fig.removeClass('unwrapped')
  $fig.find('.emb-iframe-wrapper').empty()
}

function resetForm(form, isClone=false) {
  let fields = form.querySelectorAll('input[type=text]:not([name=name]), input[type=file], textarea')
  fields.forEach(field => field.value = null)
  form.querySelectorAll('.site-indicator').forEach(i => i.remove())
  richFileInput.clear(form, !isClone)
  // Remove extra inputs
  form.querySelectorAll('input[name^=addembed], input[name^=addfile], input[name=ttl-enable]').forEach(i => {
    i.checked = false
    if (i.name == 'ttl-enable' && i.onchange)
      i.onchange()
  })
}

function initForm(form) {
  if (!form)
    return
  let $form = $(form)
  form.querySelectorAll('input[name^=embed]').forEach(i => {
    i.addEventListener('input', function() {
      let indicator = this.parentElement.querySelector('.site-indicator')
      if (indicator) 
        indicator.remove()
      let match = embedLinks.process(this.value)
      if(match)
        this.insertAdjacentHTML('afterend', `<img src="${ku_boardspath}/images/site-logos/${match.site}.png" class="site-indicator">`)
    })
  })
  form.querySelectorAll('input[name^=imagefile]').forEach(i => {
    i.addEventListener('change', function() {
      if (this.value) {
        let nextCheckbox = this.parentElement.nextElementSibling
        if (nextCheckbox)
          nextCheckbox.checked = true
      }
    })
  })
  // Markup
  form.querySelectorAll('.opt-exp').forEach(exp => {
    exp.onclick = function(ev) {
      ev.preventDefault()
      this.querySelector('.expandee').classList.toggle('expanded')
    }
    exp.onmouseleave = function(ev) {
      this.hideTimeout = setTimeout(() => this.querySelector('.expandee').classList.remove('expanded'), 250)
    }
    exp.onmouseenter = function() {
      clearTimeout(this.hideTimeout)
    }
  })
  let cms = form.querySelector('.code_markup_select')
  if (cms) {
    cms.onchange = function() {
      markup($form, `[code=${this.value}]`, '[/code]')
    }
  }
  form.querySelector('.quote_select').onchange = function() {
    quotes($form, this.value)
  }
  // for TeX
  let areaID = form.id + '_textarea'
  form.querySelector('textarea').id = areaID
  form.querySelector('.uib-tx').dataset.target = areaID
  // captcha
  if (Captcha.enabled && Captcha.enabled!=='only-access')
    Captcha.initForm(form)
  // readonly stuff
  form.querySelectorAll('.make-me-readonly').forEach(ro => {
    ro.readOnly = true
    ro.addEventListener('focus', () => ro.readOnly = false)
  })
  // Name disabling
  let name = form.querySelector('input[name=disable_name]')
  if (name) {
    name.onchange = function() {
      let off = this.checked
      form.querySelector('input[name=name]').disabled = off
      localStorage.setItem('post_anonymously', +off)
    }
    name.checked = !!+localStorage['post_anonymously']
    name.onchange()
  }
  // Submitting
  form.addEventListener('submit', function(ev) {
    ev.preventDefault()
    ev.stopPropagation()
    Ajax.submitPost(this)
  })
  form.querySelectorAll('.simplified-send-row button[type="submit"]').forEach(sub => {
    sub.onclick = function(ev) {
      ev.preventDefault()
      ev.stopPropagation()
      let sage = +(this.name == 'sagebtn')
      Ajax.submitPost(form, sage)
    }
  })
  // TTL
  let ttl = form.querySelector('input[name="ttl-enable"]')
  if (ttl) {
    ttl.onchange = function() {
      let on = this.checked
      , input = form.ttl
      input.disabled = !on
      input.min = on ? 1 : 0
      if (on && input.value == 0) {
        input.value = 1
      }
    }
    ttl.onchange()
  }
  // Detecting last active textarea
  form.querySelectorAll('textarea, input[type=text]').forEach(el => {
    el.onfocus = () => {
      lastActiveForm = form
    }
  })
  // Add file button
  let fileButton = form.querySelector('.s-file')
  if (fileButton) {
    fileButton.onclick = ev => {
      ev.preventDefault()
      form.querySelector('input[type=file]').click()
    }
  }
}

var injector = {
  inject: function(alias, css) {
    var id = `injector:${alias}`
    var existing = document.getElementById(id)
    if (existing) {
      existing.innerHTML = css
      return
    }
    var head = document.head || document.getElementsByTagName('head')[0]
    , style = document.createElement('style')
    style.type = 'text/css'
    style.id = id
    if (style.styleSheet) {
      style.styleSheet.cssText = css
    } else {
      style.appendChild(document.createTextNode(css))
    }
    head.appendChild(style)
  },
  remove: function(alias) {
    var id = `injector:${alias}`
    var style = document.getElementById(id)
    if (style) {
      var head = document.head || document.getElementsByTagName('head')[0]
      if (head)
        head.removeChild(document.getElementById(id))
    }
  }
}

function randomString(length=10, chars='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
  let result = ''
  for (let i = length; i > 0; --i) 
    result += chars[Math.round(Math.random() * (chars.length - 1))]
  return result
}

function add_mob_menu() {
  let r = 20
  , pos = 10
  , delay = 300
  $('#overlay_menu').hide()
  document.body.insertAdjacentHTML('afterbegin', `<div id="mobile-menu"><div id="mobile-menu-contents"></div></div>`)
  let mm = document.querySelector('#mobile-menu-contents')
  , $mmw = $('#mobile-menu')
  , htm = ''
  if (overboard_dir) {
    htm += `<a href="/${overboard_dir}" class="mm-item mm-brd">${overboard_desc}</a>`
  }
  $('<div id="mmb-_options" style="display: none"></div>').append($('#ms-_options')).appendTo($(mm))
  $mmw.append(`<div id="mm-toggle">
    <div class="mm-bars"></div>
    <div id="mm-circle"></div>
  </div>`)
  let cats = boards10.list
  cloud20.getBoards().then(b20 => {
    cats.sort((a,b) => +a.order > +b.order)
    .push({
      id: '20',
      name: '2.0',
      boards: b20
    })
    cats.forEach((cat, ix) => {
      if (cat.boards) {
        htm += `<a href="#" class="mm-item mm-cat" data-mmbx="${ix}">${cat.name}</a>
          <span class="mm-boards" id="mmb-${ix}">`
        cat.boards.forEach(b => {
          b.dir = b.dir || b.name
          htm += `<a class="mm-item mm-brd" href="/${b.dir}">/${b.dir}/ — ${b.desc}</a>`
        })
        htm += '</span>'
      }
    })
    htm += `<a href="#" class="mm-item mm-toggle-options" onclick="
      $('.mm-boards').toggle(false); 
      $('#mmb-_options').slideToggle('fast');
      $('.mmc-expanded').removeClass('mmc-expanded')
      return false
    ">Options</a>`
    mm.insertAdjacentHTML('afterBegin', `${htm}`)
    $('#mm-toggle').click(function() {
      if ($mmw.hasClass('mm-expanded')) return;
      $mmw.addClass('mm-expanded')
      let bcr = $mmw[0].getBoundingClientRect()
      , x = bcr.width - r - pos  // - radius/2, -position
      , y = bcr.height - r - pos // - radius/2, -position
      , R = Math.sqrt(x*x + y*y) // See, math is not useless after all
      , scale = Math.ceil(R / r)
      $('#mm-circle').css({'transform': `scale(${scale})`})
      $(mm).css({
        'visibility': 'visible',
        'opacity': 1
      })
      $mmw.addClass('bars-away')
      setTimeout(() => {
        $mmw.addClass('mm-expanded-full')
      }, delay)
    })

    offClick.push(() => {
      if (! $mmw.hasClass('mm-expanded-full')) return;
      $mmw.removeClass('mm-expanded-full bars-away')
      setTimeout(() => {
        $(mm).css({'visibility': 'hidden'})
        $mmw.removeClass('mm-expanded')
      }, delay)
      $(mm).css({
        'visibility': 'visible',
        'opacity': 0
      })
      $('#mm-circle').css({'transform': `scale(1)`})
    })
  })
  $mmw.click(function(ev) {
    ev.stopPropagation()
  })
  .on('click', '.mm-cat[data-mmbx]', function() {
    let $target = $(`#mmb-${$(this).data('mmbx')}`)
    if ($target.is(':visible')) {
      $target.toggle(false)
      $(this).removeClass('mmc-expanded')
    }
    else {
      $('.mm-boards').toggle(false)
      $('#mmb-_options').slideUp('fast');
      $target.toggle(true)
      $('.mmc-expanded').removeClass('mmc-expanded')
      $(this).addClass('mmc-expanded')
    }
    return false
  })
}

var boards10 = {
  get list() {
    if (!this.allboards) {
      this.allboards = []
      document.querySelectorAll('.olm-link').forEach(o => {
        o = o.querySelector('a')
        if (!o) return;
        let s = o.dataset.toexpand
        , sd = o.innerText
        , sect = document.querySelector(`#ms-${s}`)
        if (!s || s=='20' || s=='_options') return;
        let section = {
          id: s,
          name: sd,
          boards: []
        }
        sect.querySelectorAll('a').forEach(a => {
          let m = a.innerText.match(/\/(.+?)\/ - (.+)/)
          if (m) 
            section.boards.push({
              dir: m[1],
              desc: m[2]
            })
        })
        this.allboards.push(section)
      })
    }
    return this.allboards
  }
}

var cloud20 = {
  init: function() {
    this.getBoards().then(() => this.filter('')).catch(_.noop)
    $('#boardselect').on('input', function() {
      cloud20.filter($(this).val());
    });
  },
  getBoards: function() {
    return new Promise((rs, rj) => {
      if (this.allboards)
        rs(this.allboards)
      else {
        $.getJSON(ku_cgipath + '/boards20.json', data => {
          this.allboards = data
          rs(data)
        })
        .fail(e => {
          pups.err(_l.unable_load_20)
          rj(e)
        })
      }
    })
  },
  filter: function(query) {
    var res = [];
    if(typeof this.allboards === "undefined") return;
    if(query == '') res = this.allboards;
    else {
      query = query.toLowerCase();
      _.each(this.allboards, function(board) {
        if(board.name.toLowerCase().search(query) !== -1 || board.desc.toLowerCase().search(query) !== -1)
          res.push(board);
      });
    }
    this.display(res);
  },
  display: function(list) {
    var newhtml = '', opts = '';
    _.each(list, function(item) {
      newhtml += '<a class="menu-item" title="'+ item.desc +'" href="'+ku_boardsfolder+item.name+'/">/'+item.name+'/ - '+ item.desc +'</a>';
      opts += '<option value="'+item.name+'">/'+item.name+'/ - '+ item.desc +'</option>';
    });
    $('#boards20').html(newhtml);
    $('.boardsel20').append(opts);
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
  'XX': "OMCK",
  'T1': "Tor"
}

var bnrs = {
  initiated: false,
  init: function() {
    $.getJSON(ku_boardspath+'/bnrs.json', function(data) {
      var reduced = [];
      if(data.length > 1) {
        _.each(data, function(bnr) {
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
      _.each(this.data, function(item) {
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

var embedLinks = {
  sites: [
    {id: 'youtube', rx: /(?:youtu(?:\.be|be\.com)\/(?:.*v(?:\/|=)|(?:.*\/)?)([\w'-]+))/i },
    {id: 'vimeo', rx: /[\w\W]*vimeo\.com\/(?:.*?)([0-9]+)(?:.*)?/ },
    {id: 'coub', rx: /[\w\W]*coub\.com\/view\/([\w\W]*)[\w\W]*/ },
    {id: 'soundcloud', rx: /[\w\W]*soundcloud.com\/([\w\W]*)[\w\W]*/i },
  ],
  process: function(val) {
    var result = null;
    _.find(this.sites, function(site) {
      var fruit = site.rx.exec(val);
      if(fruit != null) {
        result = {
          site: site.id,
          code: fruit[1]
        }
      }
    })
    return result;
  }
}

var catalog = {
  conf: {
    sortBy: 'bumped',
    layout: 'text',
    respectStickied: true,
    showHidden: true,
    expandOnHover: true
  },
  saveConfig: function() {
    localStorage['catalogSettings'] = JSON.stringify(this.conf);
  },
  init: function() {
    document.body.classList.add('is-catalog')
    // apply settings
    if(localStorage['catalogSettings']) {
      try {
        var myConf = JSON.parse(localStorage['catalogSettings']);
        _.each(myConf, function(val, key) {
          this.conf[key] = val
        }, this)
      }
      catch(e) {
        console.error('Invalid catalog config');
        localStorage.removeItem('catalogSettings');
      }
    }

    /*var sortOptionElements = '';
    _.each([
      ['bumped', 'bumpOrder'],
      ['replied', 'lastReply'],
      ['timestamp', 'creationDate'],
      ['reply_count', 'replyCount']
    ], (function(val_desc) {
      sortOptionElements += '<option value="'+val_desc[0]+'"'+(val_desc[0]==this.conf.sortBy ? ' selected' : '')+'>'+_l[val_desc[1]]+'</option>';
    }).bind(this));*/
    // Catalog refresh button
    var refreshBtn = `<div class="refresh-catalog" title="${_l.refreshCatalog}"><svg class="icon i-16in20"><use xlink:href="#i-refresh"></use></svg></div>`
    // catalog control buttons
    var sortBtns = '<div class="button-group" data-select="sortBy">';
    _.each([
      ['bumped', 'bumpOrder', 'bump', 'i-20'],
      ['timestamp', 'creationDate', 'creation', 'i-20'],
      ['replied', 'lastReply', 'reply', 'i-16in20'],
      ['reply_count', 'replyCount', 'replies', 'i-20']
    ], (function(v_d_i) {
      sortBtns += '<div class="bg-button'+(v_d_i[0]==this.conf.sortBy ? ' bgb-selected' : '')+
      '" data-val="'+v_d_i[0]+'" title="'+_l.sortBy+' '+_l[v_d_i[1]]+'">\
      <svg class="icon '+v_d_i[3]+'"><use xlink:href="#i-'+v_d_i[2]+'"></use></svg></div>'
    }).bind(this));
    sortBtns += '</div>';
    var pinBtns = '<div class="button-group'+(this.conf.sortBy !== 'bumped' ? ' disabled' : '')+'" data-select="respectStickied" id="pinControl">';
    _.each([
      [1, 'doStick', 'pin', 'i-16in20'],
      [0, 'doNotStick', 'unpin', 'i-16in20']
    ], (function(v_d_i) {
      pinBtns += '<div class="bg-button'+(v_d_i[0]==this.conf.respectStickied ? ' bgb-selected' : '')+
      '" data-val="'+v_d_i[0]+'" title="'+_l[v_d_i[1]]+'">\
      <svg class="icon '+v_d_i[3]+'"><use xlink:href="#i-'+v_d_i[2]+'"></use></svg></div>'
    }).bind(this));
    pinBtns += '</div>';
    var hideBtns = '<div class="button-group" data-select="showHidden">';
    _.each([
      [0, 'hideHidden', 'hide', 'i-16in20'],
      [1, 'showHidden', 'unhide', 'i-16in20']
    ], (function(v_d_i) {
      hideBtns += '<div class="bg-button'+(v_d_i[0]==this.conf.showHidden ? ' bgb-selected' : '')+
      '" data-val="'+v_d_i[0]+'" title="'+_l[v_d_i[1]]+'">\
      <svg class="icon '+v_d_i[3]+'"><use xlink:href="#i-'+v_d_i[2]+'"></use></svg></div>'
    }).bind(this));
    hideBtns += '</div>';
    var layoutBtns = '<div class="button-group" data-select="layout">';
    _.each([
      ['text', 'smallPics', 'grid-small', 'i-20'],
      ['gallery', 'largePics', 'gallery-grid', 'i-20'],
      /*['legacy', 'legacyMode', 'legacy-grid', 'i-20']*/
    ], (function(v_d_i) {
      layoutBtns += '<div class="bg-button'+(v_d_i[0]==this.conf.layout ? ' bgb-selected' : '')+
      '" data-val="'+v_d_i[0]+'" title="'+_l[v_d_i[1]]+'">\
      <svg class="icon '+v_d_i[3]+'"><use xlink:href="#i-'+v_d_i[2]+'"></use></svg></div>'
    }).bind(this));
    layoutBtns += '</div>';
    // searck input
    var searchInput = '<input name="subject" autocomplete="false" class="button-group" type="text" id="cat-search" placeholder="'+_l.search+'..." /><input type="text" name="FUCKYOUCHROMEFUCKYOU" style="display:none;"/>'

    $('#catalog-controls').html(
      refreshBtn+sortBtns+pinBtns+searchInput+hideBtns+layoutBtns
    );
    this.load();
    if(this.conf.expandOnHover)
      $('#catalog-contents').addClass('expand-on-hover-enabled');
    // Card events
    $('#catalog-contents')
    .on('click', '.namedate-overlay', function() {
      $(this).toggleClass('date-on name-on');
    })
    .on('click', '.ce-text .bigThumb', function(ev) {
      ev.stopPropagation(); ev.preventDefault();
      var $card = $(this).parents('.cat-entry');
      $card.toggleClass('thumbExpanded');
    })
    .on('click', '.bigThumb audio, .bigThumb video', function(ev) {
      ev.stopPropagation();
    })
    .on('animationstart webkitAnimationStart MSAnimationStart oanimationstart', (function(event) {
      var $target = $(event.target);
      if (event.originalEvent.animationName == "embed-image-insert" && !$target.hasClass('_inserted_'))
        this.getEmbedThumb($target);
    }).bind(this))
    .on('mousedown', '.cat-prv', function(ev) {
      ev.preventDefault();
      PostPreviews._mouseover.bind(this)(ev);
    })
    .on('click', '.cat-prv', function(ev) {
      ev.stopPropagation();
      ev.preventDefault();
    })
    .on('mouseleave', '.cat-prv', function(ev) {
      PostPreviews._mouseout.bind(this)(/*ev*/);
    })
    .on('click', '.i-hide', (function(ev) {
      var $target = $(ev.currentTarget)
      , $card = $target.parents('.cat-entry')
      , threadID = $card.data('id')
      , threadIX = _.findIndex(this.model, {'id': threadID})
      , thread = this.model[threadIX];
      thread.hidden = !thread.hidden;
      // addClass won't work here for some reason
      if(thread.hidden) {
        $target[0].classList.add('pressed');
        $target.html(makeIcon('unhide', '', true));
        HiddenItems.hideItem('thread', threadID);
        $card.addClass('thread-hidden')
      }
      else {
        $target[0].classList.remove('pressed');
        $target.html(makeIcon('hide', '', true));
        HiddenItems.unhideItem('thread', threadID);
        $card.removeClass('thread-hidden')
      }
      //invalidate rendered cache
      this.model[threadIX] = thread;
      delete this.rendered[this.conf.layout][threadID];
    }).bind(this))
    // catalog configuration
    $('.bg-button').click((function(ev) {
      var $target = $(ev.currentTarget);
      if($target.hasClass('bgb-selected')) return;
      var $group = $target.parent()
      , val = $target.data('val')
      , key = $group.data('select');
      $group.find('.bg-button').removeClass('bgb-selected');
      $target.addClass('bgb-selected');
      if(key !== 'sortBy' && key !== 'layout') val = !!val;
      else {
        if(val == 'bumped') $('#pinControl').removeClass('disabled');
        else $('#pinControl').addClass('disabled');
      }
      this.conf[key] = val;
      this.saveConfig();
      if(key !== 'showHidden') this.build();
      else {
        if(val) $('#catalog-contents').removeClass('hideHidden');
        else $('#catalog-contents').addClass('hideHidden');
      }
    }).bind(this))
    $('.refresh-catalog').click((function(ev) {
      ev.preventDefault();
      this.load();
    }).bind(this));
    //search
    $('#cat-search').on('input', function() {
      var query = $(this).val().toLowerCase().replace(/\"/, '\\"');
      try {
        injector.remove('cat-search');
      } catch(e) {}
      if(query.length)
        injector.inject('cat-search', '#catalog-contents .cat-entry:not([data-search *= "'+query+'"]) { display:none; }');
      else injector.remove('cat-search');
    }).trigger('input');
  },
  load: function() {
    $('#catalog-contents').html(`<div class="cat-loading"><div class="spinner"></div>${_l.loading}...</div>`);
    // clear data
    this.rendered = {text: {}, gallery: {}};
    this.model = null;
    // get contents
    $.getJSON('catalog.json?v='+(new Date().getTime()))
    .done(this.build.bind(this))
    .fail(function(err) {
      throw err;
    })
  },
  fileTypes: {
    image: ['jpg', 'gif', 'png'],
    jpgThumb: ['webm', 'mp4', 'cob', 'vim', 'you'],
    iconsAvailable: ['swf', 'mp3', 'ogg', 'css', 'flv'],
    audio: ['mp3', 'ogg'],
    embed: ['cob', 'vim', 'you']
  },
  authorities: ['', 'Admin', 'Mod', '?', 'God'],
  formatDate: function(timestamp, short) {
    if(typeof short === 'undefined') short = false;
    var date = new Date(timestamp * 1000)
    , Dow = this.dateLocal.dows.hasOwnProperty(locale) ? this.dateLocal.dows[locale][date.getDay()] : this.dateLocal.dows.en[date.getDay()]
    , yy = _.padLeft(date.getFullYear() % 100, 2, 0)
    , mo = _.padLeft(date.getMonth()+1, 2, 0)
    , Mon = (locale === 'ru') ? this.dateLocal.mons.ru[date.getMonth()] : date.getMonth()+1
    , dd = _.padLeft(date.getDate(), 2, 0)
    , hh = _.padLeft(date.getHours(), 2, 0)
    , mm = _.padLeft(date.getMinutes(), 2, 0)
    , ss = _.padLeft(date.getSeconds(), 2, 0);
    return (short
      ? ( (locale === 'ru')
        ? (dd+'.'+mo+'.'+yy+' в ')
        : (mo+'/'+dd+'/'+yy+' @ ') )
      : ( (locale === 'ru')
        ? (Dow+' '+dd+' '+Mon+'’'+yy+' в ')
        : (mo+'/'+dd+'/'+yy+' ('+Dow+') @ ') )
    ) + hh+':'+mm+':'+ss;
  },
  dateLocal: {
    dows: {
      ru: ['Пнд','Втр','Срд','Чтв','Птн','Сбт','Вск'],
      en: ['Sun','Mon','Tue','Wen','Thu','Fri','Sat']
    },
    mons: {
      ru: ['Янв','Фев','Мар','Апр','Май','Июн','Июл','Авг','Сен','Окт','Ноя','Дек']
    }
  },
  build: function(data) {
    if(typeof data === 'undefined') data = this.model;
    if(!data) return;
    // normalize
    _.each(data, function(entry, i) {
      _.each(['id', 'reply_count', 'bumped', 'replied', 'reply_count', 'timestamp', 'page', 'locked', 'stickied', 'deleted_timestamp'], function(prop) {
        entry[prop] = ~~entry[prop];
      })
      data[i] = entry;
    })
    // Sort threads
    if(this.conf.sortBy === 'bumped' && this.conf.respectStickied)
      this.model = _.sortByOrder(data, ['stickied', 'bumped'], ['desc', 'desc']);
    else {
      var sby = [this.conf.sortBy];
      if(this.conf.sortBy !== 'bumped')
        sby.push('bumped')
      this.model = _.sortByOrder(data, sby, _.repeat('desc', sby.length));
    }

    var html = '';

    _.each(this.model, function(thread) {
      html += this.buildEntry(thread);
    }, this);

    $('#catalog-contents').html(html);
  },
  getEmbedThumb: function($el) {
    var site = $el.data('site'), id = $el.data('id'), img
    , $thread = $el.parents('.cat-entry')
    , threadID = $thread.data('id');
    if(site == 'cob')
      $.get(ku_boardspath+'/corpsy.php?code='+id, (function(res) {
        $el.replaceWith('<img src="'+res.thumbnail_url+'">');
        this.rendered[this.conf.layout][threadID] = $thread[0].outerHTML;
      }).bind(this));
    if(site == 'vim')
      $.get('http://vimeo.com/api/v2/video/'+id+'.json', (function(res) {
        $el.replaceWith('<img src="'+res[0].thumbnail_medium+'">');
        this.rendered[this.conf.layout][threadID] = $thread[0].outerHTML
      }).bind(this));
  },

  buildEntry: function(thread) {
    if(this.rendered[this.conf.layout].hasOwnProperty(thread.id))
      return this.rendered[this.conf.layout][thread.id];

    if(!thread.processed) {
      thread.url = '/'+this_board_dir+'/res/'+thread.id+'.html';
      // --- Building blocks ---
      // Thumbnails
      var expanderBtn = '<svg class="actor icon cat-thumb-expand"><use xlink:href="#i-expand"></use></svg>',
      playerBtn = '<svg class="actor icon cat-thumb-expand"><use xlink:href="#i-play"></use></svg>';
      // Find first non-deleted file
      let embed = thread.embeds ? (thread.embeds.find(e => e.file != 'removed') || 'removed') : null
      //  for images
      if(!embed || embed === 'removed') {
        thread.smallThumb =
        '<a href="'+thread.url+'" class="smallThumb">\
          <div class="nofile-removed ctx">'+(embed === 'removed' ? 'Удалён' : 'No File')+'</div>'
        +'</a>';
      }
      else {
        if (
          _.includes(this.fileTypes.image, embed.file_type)
          ||
          _.includes(this.fileTypes.jpgThumb, embed.file_type)
          ||
          (
            _.includes(this.fileTypes.audio, embed.file_type)
            &&
            (+embed.thumb_w) > 0
            &&
            (+embed.thumb_h) > 0
          )
        ) {
          let ftype = _.includes(this.fileTypes.image, embed.file_type)
            ? embed.file_type
            : 'jpg'
          , thumbURL = _.includes(this.fileTypes.embed, embed.file_type)
            ? `${embed.file_type}-${embed.file}-`
            : embed.file
          , vartype = embed.file_type == 'mp3' ? ' onerror="switchFileType(this)" extset="jpg,png,gif"' : ''
          thread.smallThumb =
          '<a href="'+thread.url+'" class="smallThumb">\
            <img src="thumb/'+thumbURL+'c.'+ftype+'"'+vartype+'>'
          +'</a>';
          thread.bigThumb =
          '<img src="thumb/'+thumbURL+'s.'+ftype+'"'+vartype+'>';
        }
        /*if(_.includes(this.fileTypes.image, thread.file_type)) {
          thread.fileTypeClass = 'image';
          thread.smallThumb =
          '<a href="'+thread.url+'" class="smallThumb">\
            <img src="thumb/'+thread.file+'c.'+(thread.file_type === 'webm' ? 'jpg' : thread.file_type)+'">'
          +'</a>';
          thread.bigThumb =
          '<img src="thumb/'+thread.file+'s.'+(thread.file_type === 'webm' ? 'jpg' : thread.file_type)+'">';
        }*/
        //  small thumbnail for embeds and generic files
        else {
          var smallSrc = (_.includes(this.fileTypes.iconsAvailable, embed.file_type))
          ? '/inc/filetypes/'+embed.file_type+'.png'
          : '/inc/filetypes/generic'+(_.includes(this.fileTypes.embed, embed.file_type) ? '-embed' : '')+'.png';
          var expandable = _.includes(this.fileTypes.embed, embed.file_type) || _.includes(this.fileTypes.audio, embed.file_type);
          thread.smallThumb =
          '<a href="'+thread.url+'" class="smallThumb">\
            <img src="'+smallSrc+'">' +
            /*+ ''+(expandable ? playerBtn : '') +*/
          '</a>';
          //  for audios
          if(_.includes(this.fileTypes.audio, embed.file_type)) {
            thread.bigThumb =
            '<audio src="src/'+embed.file+'.'+embed.file_type+'" controls></audio>';
          }
        }
        /*//  for embeds
        if(_.includes(this.fileTypes.embed, thread.file_type)) {
          thread.bigThumb = (thread.file_type == 'you')
          ? '<img src="http://i.ytimg.com/vi/'+thread.file+'/mqdefault.jpg">'
          : '<div class="cat-bt-embed" data-site="'+thread.file_type+'" data-id="'+thread.file+'"></div>';
        }
        //  for audios
        if(_.includes(this.fileTypes.audio, thread.file_type)) {
          thread.bigThumb =
          '<audio src="src/'+thread.file+'.'+thread.file_type+'" controls></audio>';
        }*/
        thread.bigThumb = '<a target="_blank" href="'+thread.url+'" class="bigThumb">'+thread.bigThumb+'</a>';
      }
      /*if(!embed || embed === 'removed')
        thread.bigThumb = '<a target="_blank" href="'+thread.url+'" class="bigThumb">'+thread.bigThumb+'</a>';*/

      //OP
      thread.op = '<a target="_blank" title="'+_l.goToThread+'" target="_blank" href="'+thread.url+'" class="op-number ctx">#'+thread.id+'</a>';

      //preview
      thread.preview =
      '<a href="'+thread.url+'#'+thread.id+'" class="actor cat-prv">\
        <svg class="icon"><use xlink:href="#i-eye"></use></svg>\
      </a>';

      //counters
      var repliesLabel =
      '<svg class="icon"><use xlink:href="#i-reply"></use></svg>\
      <span class="ctx reply-count">'+thread.reply_count+'</span>';
      if(thread.last_reply)
        repliesLabel = '<a href="'+thread.url+'#'+thread.last_reply+'" class="actor cat-prv">'+repliesLabel+'</a>';
      var replies =
      '<div class="infolabel">'+repliesLabel+'</div>',
      images =
      '<div class="infolabel">\
        <svg class="icon"><use xlink:href="#i-picture"></use></svg>\
        <span class="ctx image-count">'+thread.images+'</span>\
      </div>',
      page =
      '<div class="infolabel il-page">\
        <a title="'+_l.threadOnPage+' '+thread.page+'" target="_blank" href="/'+this_board_dir+'/' + ((thread.page > 0) ? thread.page+'.html' : '')+'#'+thread.id+'" class="actor">\
          <svg class="icon"><use xlink:href="#i-page"></use></svg>\
          <span class="ctx page-number">'+thread.page+'</span>\
        </a>\
      </div>';
      thread.countersCombined = replies+images+page;

      //Poster name+date
      thread.posterauthority = +thread.posterauthority;
      thread.nameDatePriority = 'date';
      if(localStorage['cat_nameDatePriority'] == 'name' ||
          (localStorage['cat_nameDatePriority'] != 'date' &&
            (thread.name || thread.tripcode || thread.posterauthority)
          )
        ) thread.nameDatePriority = 'name';

      // Poster name
      var poster =
      (thread.name ? '<span class="ctx postername">'+thread.name+'</span>' : '') +
      (thread.tripcode ? '<span class="ctx postertrip">!'+thread.tripcode+'</span>' : '') +
      (thread.posterauthority ? '<span class="ctx admin">&nbsp;##'+this.authorities[thread.posterauthority]+'##</span>' : '');
      thread.poster = '<div class="cat-poster"><span class="ctx">by&nbsp;</span>'+ (poster || '<span class="ctx c-postername">'+(this_board_defaultName || _l.anonymous)+'</span>')+'</div>';

      // Date
      var dn = ' style="display:none"';
      thread.date = '<div class="ctx cat-date cat-date-long">'+this.formatDate(thread.timestamp)+'</div>';
      thread.dateCompact = '<div class="ctx cat-date cat-date-short">'+this.formatDate(thread.timestamp, 1)+'</div>';

      //search data
      thread.searchData = _.escape(stripHTML(thread.subject + ' ' + thread.message).toLowerCase());

      thread.message = thread.message.replace(/\\"/mg, '"');

      thread.processed = true;
      this.model[_.findIndex(this.model, {id: thread.id})] = thread;
    }
    //indicators
    thread = this.buildIndicators(thread);

    var html = this.layouts[this.conf.layout].bind(this)(thread);
    this.rendered[this.conf.layout][thread.id] = html;
    return html
  },
  buildIndicators: function(thread) {
    thread.hidden =  HiddenItems.isHidden('thread', thread.id)
    var pin = thread.stickied ? '<svg class="foradmin-act icon i-layer-1 i-pin"><use xlink:href="#i-pin"></use></svg>' : '',
    lock = thread.locked ? '<svg class="foradmin-act icon i-layer-1 i-lock"><use xlink:href="#i-lock"></use></svg>' : '',
    deathmark = thread.deleted_timestamp ? '<svg class="foradmin-act icon i-layer-1 i-deathmark"><use xlink:href="#i-skull"></use></svg>' : '',
    hide = '<svg class="actor icon i-layer-1 i-hide'+(thread.hidden ? ' pressed' : '')+'"><use xlink:href="#i-'+(thread.hidden ? 'unhide' : 'hide')+'"></use></svg>',
    burger = '<svg class="actor icon i-burger foradmin-show"><use xlink:href="#i-burger"></use></svg>',
    del = '<svg class="actor icon i-layer-2 i-delete foradmin-show"><use xlink:href="#i-x"></use></svg>',
    and = '<svg class="actor icon i-layer-2 i-dnb foradmin-show"><use xlink:href="#i-and"></use></svg>',
    ban = '<svg class="actor icon i-layer-2 i-ban foradmin-show"><use xlink:href="#i-ban"></use></svg>';
    thread.indicatorsCombined = '<div class="indicators">'+burger+'<span class="i-layer-1">'+deathmark+pin+lock+hide+'</span><span class="i-layer-2">'+del+and+ban+'</span></div>';
    return thread;
  },
  layouts: {
    text: function(thread) {
      return ''+
      '<div data-id="'+thread.id+'" class="cat-entry ce-text'+(thread.hidden ? ' thread-hidden' : '')+'" data-search="'+thread.searchData+'">\
        <div class="cat-card">\
          <div class="ce-heda">'
            +thread.smallThumb+
            '<div class="cat-infoline ci-op-link">'
              +thread.op
              +thread.indicatorsCombined+
            '</div>\
            <div class="cat-infoline namedate-overlay '+thread.nameDatePriority+'-on">'
              +thread.poster
              +thread.date
            +'</div>\
            <div class="cat-infoline">'
              +thread.preview
              +thread.countersCombined+
            '</div>\
          </div>\
          <div class="ce-opcontent ctx">\
            <h5>'+thread.subject+'</h5>'+
            thread.message+
          '</div>\
        </div>'
        +thread.bigThumb+
      '</div>'
    },
    gallery: function(thread) {
      return ''+
      '<div data-id="'+thread.id+'" class="cat-entry ce-gallery'+(thread.hidden ? ' thread-hidden' : '')+'" data-search="'+thread.searchData+'">\
        <div class="cat-card">'+
          thread.bigThumb+
          '<div class="cat-infoline">\
            <div class="ci-op-link">'+thread.op+'</div>\
            <div class="counters">'+thread.countersCombined+'</div>\
          </div>\
          <div class="cat-infoline">'
            +thread.preview+
            '<div class="namedate-overlay '+thread.nameDatePriority+'-on">'
              +thread.poster
              +thread.dateCompact
            +'</div>'
            +thread.indicatorsCombined+
          '</div>\
          <div class="ce-opcontent ctx">\
            <h5>'+thread.subject+'</h5>'+
            thread.message+
          '</div>\
        </div>\
      </div>'
    }
  }
}

function stripHTML(html) {
  var tmp = document.implementation.createHTMLDocument("New").body;
  tmp.innerHTML = html;
  return tmp.textContent || tmp.innerText || "";
}

var overboard = {
  get boards() {
    if (!is_overboard)
      return [boardid]
    if (!this._boards)
      this._boards = _.unique([].map.call(document.querySelectorAll('div[id^=thread]'), e => e.dataset.boardid))
    return this._boards
  },
  idFromName: name => name!==null && $(`div[data-board="${name}"]`).parents('div[id^=thread]').data('boardid')
}

var unreadCounter = {
  init: function(built) {
    if (!built) {
      built = + document.querySelector('meta[property="i0:buildtime"]').content
    }
    this.lastvisits = localStorage['lastvisits'] ? (JSON.parse(localStorage['lastvisits']) || { }) : { }
    overboard.boards.forEach(boardid => {
      let last_ts = this.lastvisits.hasOwnProperty(boardid) ? parseInt(this.lastvisits[boardid]) : 0;
      if(last_ts < built) {
        this.lastvisits[boardid] = built
        localStorage.setItem('lastvisits', JSON.stringify(this.lastvisits))
      }
    })
  },
  refreshTimestamp: function(timestamp, boardName=null) { // PHP standard timestamp length
    if (!this.lastvisits)
      this.init()
    let id = !is_overboard ? boardid : overboard.idFromName(boardName)
    if (id) {
      if (timestamp===null || typeof timestamp === 'undefined')
        timestamp = Math.round(new Date().getTime() / 1000)
      this.lastvisits[id] = timestamp
      localStorage.setItem('lastvisits', JSON.stringify(this.lastvisits))
    }
  },
  update: function() {
    if (!this.lastvisits)
      this.init()
    $.ajax({
      url: '/newpostscount.php',
      data: this.lastvisits,
      dataType: 'json',
      success: function(data) {
        $('.newposts-counter').remove()
        $('.got-updates').removeClass('got-updates')
        _.each(data, function(val, brd) {
          if ((+val) > 0) {
            let valstr = `<span class="newposts-counter"> (${val})</span>`
            , $brd = $(`.menu-item[href="/${brd}/"]`)
            if ($brd.length) {
              $brd.append(valstr)
              $(`.sect-exr[data-toexpand="${$brd.parents('.menu-sect').attr('id').split('ms-')[1]}"]`).parent().addClass('got-updates')
            }
            $(`.mobile-nav option[value="${brd}"]`).append(valstr)
          }
        })
      }
    });
  }
}

var HTMLoader = {
  loadThread: function(boardID, threadID, done, postID) {
    $.get(threadID === '?'
      ? `${ku_boardspath}/postbynumber.php?b=${boardID}&p=${postID}`
      : `${ku_boardspath}/${boardID}/res/${threadID}.html?${force_html_nocache ? Math.random() : ''}`)
    .then(data => {
      let posts = data.match(/<div\s*?class\s*?=\s*?"[^"]*?i0svcel[^"]*?"\s*?>!i0-pd:[0-9]+\s*?<\/div\s*?>[\s\S]+?<div\s*?class\s*?=\s*?"[^"]*?i0svcel[^"]*?"\s*?>!i0-pd-end\s*?<\/div\s*?>/gi)
      .map(match => {
        let res = match.match(/<div\s*?class\s*?=\s*?"[^"]*?i0svcel[^"]*?"\s*?>!i0-pd:([0-9]+)\s*?<\/div\s*?>([\s\S]+?)<div\s*?class\s*?=\s*?"[^"]*?i0svcel[^"]*?"\s*?>!i0-pd-end\s*?<\/div\s*?>/i);
        return {
          id: +res[1],
          body: res[2]
        }
      })
      if (threadID === '?') {
        let thrno = data.match(/<!--i0:thrno=([0-9]+)-->/) //wtf
        if (thrno)
          threadID = thrno[1]
      }
      if (posts.length) {
        let postMap = posts.map(post => {
          this.cached[`${boardID}_${post.id}`] = post.body
          return post.id
        })
        if (threadID !== '?') {
          this.threadMaps[`${boardID}_${threadID}`] = postMap
        }
      }
      done()
    })
    .fail(err => {
      done(err.status || 'null')
    })
  },
  getPost: function(boardID, threadID, postID, callback, secondTry) {
    let cachedPost = this.cached[`${boardID}_${postID}`]
    if (cachedPost) {
      callback(false, cachedPost)
      return
    }
    else {
      if (!secondTry) {
        let found = false
        if (boardID === this_board_dir) {
          let $post = $(`a[name=${postID}]`)
          if ($post.length) {
            let post = $post.parents('.postnode')[0].outerHTML
            this.cached[`${boardID}_${postID}`] = post
            found = true
            callback(false, post)
          }
        }
        if (!found) {
          this.loadThread(boardID, threadID, err => {
            if (err) {
              callback(err)
            }
            else {
              this.getPost(boardID, threadID, postID, callback, true)
            }
          }, postID)
        }
      }
      else {
        callback(null)
      }
    }
  },
  getThread: function(boardID, threadID, range, callback, secondTry, force) {
    let threadMap = force ? false : this.threadMaps[`${boardID}_${threadID}`]
    if (threadMap) {
      if (range) {
        threadMap = threadMap.filter(n => n > range[0] && n < range[1])
      }
      callback(false, threadMap.reduce((htm, postID) => htm + this.cached[`${boardID}_${postID}`], ''))
    }
    else {
      if (!secondTry) {
        this.loadThread(boardID, threadID, err => {
          if (err) {
            callback(err)
          }
          else {
            this.getThread(boardID, threadID, range, callback, true)
          }
        })
      }
      else {
        callback(null)
      }
    }
  },
  cached: {},
  threadMaps: {},
  cachedPostboxes: {},
  getPostbox: function(boardID) {
    return new Promise((resolve, reject) => {
      if (boardID===false) // not overboard
        return resolve(document.querySelector('#postform'))
      if (this.cachedPostboxes[boardID])
        return resolve(this.cachedPostboxes[boardID])
      $.get(`${ku_boardspath}/${boardID}/`) // dirty deeds done dirt cheap
      .then(data => {
        let pb = data.match(/<div\s*?class\s*?=\s*?"[^"]*?i0svcel[^"]*?"\s*?>!i0-pb\s*?<\/div\s*?>([\s\S]+?)<div\s*?class\s*?=\s*?"[^"]*?i0svcel[^"]*?"\s*?>!i0-pb-end\s*?<\/div\s*?>/i)
        if (!pb)
          reject(404)
        let c = document.createElement('div')
        c.innerHTML = pb[1]
        let hcaptchaScript
        if (typeof hcaptcha === 'undefined' && (hcaptchaScript = pb[1].match(/<script src="(.+?js\.hcaptcha\.com.+?)"/))) {
          head.js(hcaptchaScript[1], () => console.log('hcaptcha loaded'))
        }
        this.cachedPostboxes[boardID] = c.firstChild
        resolve(this.cachedPostboxes[boardID])
      })
      .fail(err => {
        reject(err.status || 'null')
      })
    })
  }
}

function switchFileType(el) {
  let exp = /\.([a-z0-9]+)(?=$|\?)/i
  , match = el.src.match(exp)
  if (!match) return;
  let exts = el.getAttribute('extset').toLowerCase().split(',')
  if (!exts.length) return;
  let i = exts.indexOf(match[1].toLowerCase())
  if (i == -1 || i >= (exts.length-1)) return;
  el.src = el.src.replace(exp, `.${exts[i+1]}`)
}

function LSfetchJSON(key) {
  let val = null, data = localStorage[key]
  if (typeof data !== 'undefined') {
    try {
      val = JSON.parse(data)
    }
    catch(e) {
      console.error(e)
      localStorage.removeItem(key)
    }
  }
  return val
}

// YOBA alerts
var pups = {
  push: function(a) {
    // generate a unique alert ID
    a.id = (+_.uniqueId(_.now())).toString(16)
    this.container.insertAdjacentHTML('afterBegin', this.buildAlert(a))
    a.el = document.getElementById(`pup_${a.id}`)
    // Add the popup to the animation queue
    a.el.style.marginTop = -a.el.getBoundingClientRect().height + 'px'
    a.el.getBoundingClientRect() // This forces browser to respect the element's updated position for future animations (no idea why but it works)
    // Close on click
    a.el.onclick = () => {
      delete a.timeout
      a.onHold = false
      a.old = true
      a.el.classList.add('pup-away')
      a.el.style.marginTop = -a.el.getBoundingClientRect().height + 'px'
    }
    // Do not close if mouse is over
    a.el.onmouseenter = () => {
      // iterate stack from end to start
      this.stack.slice(0).reverse().find(ar => {
        ar.onHold = true
        return (ar.id == a.id) // break
      })
    }
    // Close after mouse leave
    a.el.onmouseleave = () => {
      this.stack.forEach(ae => {
        ae.onHold = false
      })
      this.holdOffTimeout = setTimeout(() => {
        this.stack.forEach(ae => {
          if (!ae.onHold && ae.old && ae.el) {
            ae.el.classList.add('pup-away')
            ae.el.style.marginTop = -a.el.getBoundingClientRect().height + 'px'
          }
        })
      }, 200)
    }
    this.queue.add(() => {
      this.scheduleClose(a)
      a.el.classList.remove('pup-pre')
      a.el.style.marginTop = null
      a.el.classList.remove('pup-noshadow')
    })
    this.stack.push(a)
    this.save()
    return a.id
  },
  closeByID: function(aid) {
    let a = this.byID(aid)
    if (a)
      a.el.onclick()
  },
  queue: {
    stack: [],
    busy: false,
    add: function(fn) {
      return new Promise((resolve, reject) => {
        if (! this.busy) {
          this.timeout = setTimeout(this.next.bind(this), this.cooldown * 1000)
          this.busy = true
          resolve(fn())
        }
        else {
          this.stack.push(() => resolve(fn()))
        }
      })
    },
    next: function() {
      let next = this.stack.shift()
      if (! next)
        this.busy = false
      else {
        next()
        this.timeout = setTimeout(this.next.bind(this), this.cooldown * 1000)
      }
    },
    cooldown: .3
  },
  byID: function(aid) {
    return this.stack.find(a => a.id == aid)
  },
  update: function(aid, upd) {
    let a = this.byID(aid)
    if (typeof upd !== 'object')
      a = {msg: upd}
    _.extend(a, upd)
    // reset timeout
    this.scheduleClose(a)
    a.el.setAttribute('pupclass', a.cls)
    a.el.querySelector('.pup-wrapped').innerHTML = this.buildAlert(a, 1)
    this.save()
  },
  buildAlert: function(a, contentsOnly=false) {
    let contents = `
      <div class="alert-icon">
        <svg class="icon"><use xlink:href="#i-${this.iconMap[a.cls]}"></use></svg>
      </div>
      <div class="alert-msg">${a.msg}</div>`
    if (contentsOnly)
      return contents
    return `<div class="pup ${a.old ? 'pup-away pup-away-full' : 'pup-pre pup-noshadow'}" pupclass="${a.cls}" id="pup_${a.id}">
      <div class="pup-wrapped">
        ${contents}
      </div>
    </div>`
  },
  iconMap: {
    succ: 'success',
    err: 'error',
    info: 'info',
    warn: 'warning'
  },
  historyToggle: function() {
    if (!this.stack.length) {
      this.push({cls: 'info', msg: _l.historyEmpty, destroy: true})
      return
    }
    let on = this.container.classList.toggle('history-mode')
    setTimeout(() => this.container.style.overflow = (on ? 'auto' : null), on ? 300 : 0)
  },
  scheduleClose: function(a) {
    if (a.timeout)
      clearTimeout(a.timeout)
    if (a.onHold)
      a.onHold = false
    let time = (a.time || a.time === 0) ? a.time : this.defaultTimeout
    if (time) // If time is 0 it means no timeout
      a.timeout = setTimeout(() => {
        a.old = true
        if (! a.onHold) {
          a.el.classList.add('pup-away')
          a.el.style.marginTop = -a.el.getBoundingClientRect().height + 'px'
        }
      }, time * 1000)
  },
  defaultTimeout: 3.5, // in seconds
  init: function(container_id="pups-container") {
    document.body.insertAdjacentHTML('beforeEnd', `<div id="${container_id}"><div class="pup-history-shadow"></div></div>`)
    this.container = document.getElementById(container_id)
    if (! this.container) {
      console.error('No popup container found')
      return;
    }
    this.container.onclick = () => {
      if (this.container.classList.contains('history-mode')) {
        return this.historyToggle()
      }
    }
    // make aliases for error classes
    ['err', 'warn', 'info', 'succ', 'wait'].forEach(pupclass => {
      this[pupclass] = function(a, options={}) {
        if (typeof a !== 'object')
          a = {msg: a}
        a.cls = pupclass
        _.extend(a, options)
        return this.push(a)
      }
    })
    // load history from LS
    let log = LSfetchJSON('I0_event_log') || []
    log.forEach(a => {
      a.old = true
      this.container.insertAdjacentHTML('afterBegin', this.buildAlert(a))
      this.stack.push(a)
    })
  },
  historySize: 10,
  save: function() {
    let ss = [] 
    _.cloneDeep(this.stack).forEach(a => {
      if (a.save) {
        // Clear stack from the properties that don't need to be saved
        ['old', 'onHold', 'timeout', 'el', 'save'].forEach(junkProperty => delete a[junkProperty])
        ss.push(a)
      }
    })
    while (ss.length > this.historySize) {
      ss.shift()
    }
    localStorage['I0_event_log'] = JSON.stringify(ss)
  },
  stack: []
}
