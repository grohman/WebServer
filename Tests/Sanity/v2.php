<?php
return [
	
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
			'route' => '{target:t*}'
		]
	]
];