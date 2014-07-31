var node_ip = '127.0.0.1', node_port = '1337';

var sites = [
{name: 'yourchanid', srvtoken: 'ENTERRANDOMSHIT'},
// add more sites if you want
]

var bodyParser = require('body-parser'); 
var express = require('express'), app = express();
app.use(bodyParser.json());
var server = require('http').createServer(app);
var io = require('socket.io')(server);

iter(sites, function(site) {
    app.post('/qr/'+site.name, function(req, res) {
      var data = req.body;
      if(typeof data.srvtoken !== 'undefined' || data.srvtoken === site.srvtoken || typeof data.room !== 'undefined')
        io.to(site.name+':'+data.room).emit('update', {token: data.clitoken, timestamp: data.timestamp, room: data.room.split(':')[1], newthread: data.newthreadid });
      res.status(200).end();
    });
});

io.on('connection', function (socket) {
    socket.on('subscribe', function(rooms) {
    	iter(rooms, function(room) {
    		socket.join(room)
    	});
    });
});

function iter(array, callback) {
    if(typeof array !== 'object') return callback(array);
    var i=0, len = array.length;
    for ( ; i < len ; i++ ) {
        callback(array[i]);
    }
}

server.listen(node_port, node_ip);