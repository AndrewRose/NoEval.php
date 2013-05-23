<?php

/*
 This file is part of NoEval
 http://github.com/AndrewRose/noeval.php
 http://andrewrose.co.uk
 License: GPL; see below
 Copyright Andrew Rose (hello@andrewrose.co.uk) 2013

    NoEval.php is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    cached.php is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with cached.php.  If not, see <http://www.gnu.org/licenses/>
*/

include_once("noeval.php");

$tests = [];

$t = new NoEval;
$debugMode = FALSE;

$tests = [[
	'description' => 'Fizzbuzz example',
	'code' => ["let", ":i", 1, ["while", ["<", ":i", ["+", 100, 1]],
		["if", ["=", ["%", ":i", 3], 0],
			["if", ["=", ["%", ":i", 5], 0],
				["echo", "FizzBuzz"],
				["echo", "Fizz"]
			],
			["if", ["=", ["%", ":i", 5], 0],
				["echo", "Buzz"],
				["echo", ":i"]
			],
		],
		["++", ":i"]
	]],
	'vars' => [],
	'result' => [],
	'debug' => FALSE
],[
	'description' => 'Fibonacci example',
	'code' => ["let", ":z", 0,
		["while", ["<", ":z", 1000],
			["let", ":z", ["+", ":x", ":y"]],
/*			["echo", ":z"],			Need to return the final value? */
			["let", ":x", ":y"],
			["let", ":y", ":z"]
		]
	],
	'vars' => [":x" => 1, ":y" => 2],
	'result' => [],
	'debug' => FALSE
],[
	'description' => 'Loop while test',
	'code' => ['let', ':i', 10, ['while', ['>=', ':i', 1], ['--', ':i']]],
	'lisp' => '(let ((i 10)) (loop while (>= i 1) do (decf i)))',
	'vars' => [],
	'result' => [],
	'debug' => FALSE

],[
	'description' => 'Recursive function test',
	'code' => ['rec', 1],
	'vars' => [],
	'result' => 10,
	'debug' => FALSE
],[
	'description' => 'Recursive function defintion',
	'code' => ['defun', 'rec', ['if', ['<', ':0', '10'], ['rec', ['++', ':0']], ':0']],
	'lisp' => '(defun rec (i) (if (< i 10) (rec (incf i)) i))',
	'vars' => [],
	'result' => 'REC',
	'debug' => FALSE
],[
	'description' => 'Boolean OR test',
	'code' => ['or', ['<', 4, 3], ['>', 2, 4], ['!=', 1, 1], ['>=', 10, 1]],
	'vars' => [],
	'result' => TRUE,
	'debug' => FALSE
],[
	'description' => 'Boolean AND test',
	'code' => ['and', ['<', 3, ['*', 2, 5]], ['not', ['>=', 2, 6]]],
	'vars' => [],
	'result' => TRUE,
	'debug' => FALSE
],[
	'description' => 'Conditional test, if-else',
	'code' => ['if', ['<=', 3, 2], ['*', 3, 9], ['+', 4, 2, 3,]],
	'vars' => [],
	'result' => 9,
	'debug' => FALSE
],[
	'description' => 'Conditial test, if 2 > 1 return :i',
	'code' => ['if', ['>', 2, 1], [':i']],
	'vars' => [':i' => 42],
	'result' => 42,
	'debug' => FALSE

],[
	'description' => 'Factorial of 12 function test',
	'code' => ['factorial', 12],
	'vars' => [],
	'result' => 479001600,
	'debug' => FALSE
],[
	'description' => 'Factorial defun',
	'code' => ['defun', 'factorial', 'if', ['<', ':0', 2], 1, ['*', ':0', ['factorial', ['-', ':0', 1]]]],
	'vars' => [],
	'result' => 'FACTORIAL',
	'debug' => FALSE
],[
	'description' => 'Passing in 42 test',
	'code' => [':i'],
	'vars' => [':i' => 42],
	'result' => 42,
	'debug' => FALSE
],[
	'description' => 'Atom string test foobar',
	'code' => 'foobar',
	'vars' => [],
	'result' => 'foobar',
	'debug' => FALSE
],[
	'description' => 'Atom number test 42',
	'code' => 42,
	'vars' => [],
	'result' => 42,
	'debug' => FALSE
]];

echo "Running tests..\n";
foreach(array_reverse($tests) as $test)
{
	if(!$debugMode || ($debugMode && $test['debug']))
	{
		echo $test['description'].'...';
		ob_start();
		$result =  $t->parse($test['code'], $test['vars']);
		if($test['result'] !== $result)
		{
			echo ob_get_contents();
			echo "FAILED!\n";
			echo '>>>';
			print_r($result);
			echo "<<<\n";
		}
		else
		{
			ob_end_clean();
			echo "pass.\n";
		}
	}
}

/*
// if
$t->parse(["if", ["=", 0, ["%", 5, 3]],
	["echo", "bar"],
	["echo", "foo"]
]);
$t->parse(["if", ["=", ["%", 5, 5], 0], ["echo", "bar"], ["echo", "foo"]]);
$t->parse(["if", [">", 4, 10], ["echo", "greater than!\n"], ["echo", "less than!\n"]]);

// variables
$t->parse(["let", ":pi", 3.14159265359, ["echo", ["*", ":pi", 10, 10]]]);

// functions
$t->parse(["defun", "add", "+", ["*", ":0", ":1"], ":2"]);
$t->parse(["echo", ["add", 5, 2, 3]]);
$t->parse(["echo", ["add", 2, ["add", 5, ["-", 10, 5], 3], 32]]);
$t->parse(["echo", ["add", 98, ["add", 54, ["-", 124, 65], 2], 67]]);


while(1)
{
	$line = readline("NoEval: ");
	readline_add_history($line);
	if($line == 'exit') exit();
	echo $t->parse(json_decode($line), []);
}*/