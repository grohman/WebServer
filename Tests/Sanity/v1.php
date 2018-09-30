<?php
return [
	
	'decorator' => 
	[
		\WebServer\DecoratorB::class,
	],
	'config' => 
	[
		'sanity.php'
	],
	'req' => 
	[
		'test' => 
		[
			'controller'	=> \WebServer\TestController::class,
			'route'			=> '{target:t*}',
			'method'		=> 'DELETE'
		]
	]
];