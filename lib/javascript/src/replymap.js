var replyMap = {
  showReplies: function(root) {
    ;(root || document).querySelectorAll('.postnode').forEach(post => {
      let n = post.querySelector('.reflink a:last-child').innerHTML
      , msg = post.querySelector('.postmessage')
      , repliesContainer = post.querySelector('.replieslist')
      if (this.posts[n]) {
        this.posts[n].container = repliesContainer
      }
      else {
        this.posts[n] = {
          container: repliesContainer,
          replies: []
        }
      }
      let links = msg.querySelectorAll('a[class^=ref\\|]')
      if (links.length) links.forEach(link => {
        let linkData = link.className.split('|')
        , linkN = linkData[3]
        , htm = `<a class="ref-reply" href="/${linkData[1]}/res/${linkData[2]}.html#${n}" onclick="javascript:highlight(\'${n}\', true);">&gt;&gt;${n}</a>`
        if (this.posts[linkN]) {
          if (! _.includes(this.posts[linkN].replies, htm)) {
            this.posts[linkN].replies.push(htm)
          }
        }
        else {
          this.posts[linkN] = {
            replies: [htm]
          }
        }
        this.posts[linkN].skip = false
      })
    })
    _.each(this.posts, post => {
      if (!post.skip && post.replies.length && post.container) {
        post.container.innerHTML = `<br>${_l.replies}: ${post.replies.join(', ')}`
        post.skip = true
      }
    })
  },
  posts: {}
}

var scrollAnchor = {
  save: function(id, elements, parent, dimensions) {
    parent = parent || window
    dimensions = dimensions || 'vh'
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
    ;(parent == window ? document : parent).querySelectorAll(elements)
    .forEach(el => {
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
    let loaded = this.saved[id]
    if (! loaded) return;
    window.requestAnimationFrame(() => {
      let bcrAfter = loaded.anchor.getBoundingClientRect()
      loaded.parent.scrollBy(loaded.dimensions.indexOf('h') !== (-1) ? bcrAfter.left - loaded.left : 0, loaded.dimensions.indexOf('v') !== (-1) ? bcrAfter.top - loaded.top : 0)
    })
  },
  saved: {}
}

Math.pos = x => x >= 0 ? x : 0

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