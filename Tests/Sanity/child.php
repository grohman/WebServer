<?php
return [
	'controller'	=> 'notFound',
	'action'		=> 'notFoundAction',
	'parser'		=> \WebServer\MainParser::class,
	
	'require' =>
	[
		'v1' => 
		[
			'route'		=> 'v1',
			'config'	=> 'v1.php'
		],
		'v2' =>
		[
			'route'		=> 'v2',
			'config'	=> 'v2.php'
		]
	]
];