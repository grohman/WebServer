<?php
return [
	'parsers' => \WebServer\ExtraParser::class,
	'decorator' => 
	[
		\WebServer\DecoratorA::class,
	],
	
	'config' => 
	[
		'sanity.php'
	],
	
	'req' => 
	[
		'test' => 
		[
			'controller' => \WebServer\TestController::class,
			'route' => '{target:t*}/hello',
			'action' => 'helloWorld',
			'method' => 'POST'
		]
	]
];