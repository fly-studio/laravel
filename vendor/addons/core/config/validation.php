<?php

return [
	'manual' => [
		'store' => [
			'title' => [
				'name' => '标题',
				'rules' => 'required',
			],
			'content' => [
				'name' => '简介',
				'rules' => 'string',
			],
			'pid' => [
				'name' => '父级',
				'rules' => 'required|numeric',
			],
		],
	],
];