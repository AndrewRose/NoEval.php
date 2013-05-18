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

$t = new NoEval;

echo $t->parse(["+", 3, 4]),"\n";

//boolean
echo $t->parse(['and',
	['<', 3, ['*', 2, 5]],
	['not', ['>=', 2, 6]]
]),"\n";

echo $t->parse(['or',
	['<', 4, 3],
	['>', 2, 4],
	['!=', 1, 1],
	['>=', 10, 1]
]),"\n";

exit();


// if
$t->parse(["if", ["=", 0, ["%", 5, 3]],
		["echo", "bar"],
		/* else */ ["echo", "foo"]
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

//recursion
$t->parse(["defun", "rec", ["if", ["<", ":0", "10"], [["echo", ":0"], ["++", ":0"], ["rec", ":0"]], ["echo", ":0"]]]);
$t->parse(["rec", 1]);

//fizzbuzz
$t->parse(["let", ":i", 1, [["echo", "Fizzbuzz example"],
	["while", ["<", ":i", ["+", 100, 1]],
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
	]]
]);

//fibonacci
$t->parse(["let", ":z", 0,
	["while", ["<", ":z", 1000],
		["let", ":z", ["+", ":x", ":y"]],
		["echo", ":z"],
		["let", ":x", ":y"],
		["let", ":y", ":z"]
	]
],[":x" => 1, ":y" => 2]);

echo(json_encode(["let", ":i", 1, [["echo", "Fizzbuzz example"],
	["while", ["<", ":i", ["+", 100, 1]],
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
	]]
], TRUE));

/*while(1)
{
	$line = readline("NoEval: ");
	readline_add_history($line);
	if($line == 'exit') exit();
	echo $t->parse(json_decode($line), []);
}*/

