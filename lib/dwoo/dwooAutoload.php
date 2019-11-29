<?php
include dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Dwoo'. DIRECTORY_SEPARATOR . 'Core.php';
function dwooAutoload($class)
{
	if (substr($class, 0, 5) === 'Dwoo_' || $class === 'Dwoo') {
		include dirname(__DIR__) . DIRECTORY_SEPARATOR . strtr($class, '_', DIRECTORY_SEPARATOR).'.php';
	}
}
spl_autoload_register('dwooAutoload');
