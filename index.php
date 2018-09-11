<?php
define('WWW_DIR', dirname(__FILE__));
define('BASE_URI', '/cp/');
$container = require __DIR__ . '/app/bootstrap.php';

$container->getByType(Nette\Application\Application::class)
	->run();
