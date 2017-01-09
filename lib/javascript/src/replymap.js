var replyMap = {
  showReplies: function(root) {
    // let start = new Date().getTime()
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
    /*let end = new Date().getTime()
    console.log(end - start)*/
  },
  posts: {}
}