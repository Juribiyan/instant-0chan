$.getJSON('/boards10.json')
.done(function(data) {
	var html = '<tbody><tr><td valign="top">';
	_.each(_.sortBy(data, 'order'), function(sect) {
		html += '<div class="boards-cell">';
		_.each(_.sortBy(sect.boards, 'order'), function(board, ix) {
			html += '<a title="'+board.desc+'" href="'+board.dir+'/"> /'+board.dir+'/ • '+board.desc+'</a><br>';
			//long section breaker
			if(sect.boards.length >= 10 && !((ix+1) % 8)) 
				html += '</div><div class="boards-cell">';
		})
		html += '</div>';
	});
	html += '</td></tr></tbody>';
	$('#boardlist').html(html);
})
.fail(function(er) {
	console.error(er)
})