<?php
declare(strict_types=1);

// Uncomment this line if you must temporarily take down your site for maintenance.
// require '.maintenance.php';

require __DIR__ . '/../vendor/autoload.php';

//\Tracy\OutputDebugger::enable();

App\Bootstrap::boot()
	->createContainer()
	->getByType(Nette\Application\Application::class)
	->run();
