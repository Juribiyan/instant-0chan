<?php
namespace ElephantIO;
require_once '/ElephantIO/Client.php';

use ElephantIO\Client as Elephant;
if(isset($_GET['q'])) {

    $elephant = new Elephant('127.0.0.1:1337', 'socket.io', 1, false, true, true);
    $id = $_GET['q'];
    
    $elephant->init();
    $elephant->send(
        Elephant::TYPE_EVENT,
    	null,
    	null,
    	json_encode(array('name' => 'srvmsg', 'args' => $id ))
    );
    
    $elephant->close();
}
else echo "q not defined";