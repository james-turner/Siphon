<?php

error_reporting(E_ALL|E_STRICT);

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(dirname(__FILE__) . '/../src'),
    get_include_path()
)));

require_once('Siphon/Autoloader.php');

Siphon\Autoloader::register();