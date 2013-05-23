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

class NoEval
{
	private $subs = [];

	public function parse($tree, $vars = [])
	{
		return $this->evalList($tree, $vars);
	}

	public function parseVar($var, $vars)
	{
		if(is_string($var) && $var{0} == ':')
		{
			return $vars[$var];
		}
		return $var;
	}

	public function evalList(&$list, &$vars=[])
	{
		if(!is_array($list))
		{
			return $this->parseVar($list, $vars);
		}

		if(sizeof($list) == 1)
		{
			return $this->evalList($this->parseVar(array_shift($list), $vars), $vars);
		}

		foreach($list as $tmp => &$a)
		{
			if(is_array($a))
			{
				$a = $this->evalList($a, $vars);
			}
			else if(!is_array($a))
			{
				$op = (string)$a;
				switch($op)
				{
					case 'let':
					{
						array_shift($list); // remove let op
						$ax = array_shift($list); // variable name

						$bx = array_shift($list); // expression
						$vars[$ax] = $this->parseVar($this->evalList($bx, $vars), $vars);

						if($cx = array_shift($list))
						{
							$this->evalList($cx, $vars);
						}

						return [];
// TODO: what do we return?
					}
					break;

					case 'defun':
					{
						array_shift($list); // remove defun op
						$fncName = array_shift($list);
						$this->subs[$fncName] = $list;
						return strtoupper($fncName);
					}
					break;

					case 'if':
					{
						array_shift($list); // remove if op
						$cond = array_shift($list);
						$trueBranch = array_shift($list);
						$falseBranch = array_shift($list);

						if($this->evalList($cond, $vars))
						{
							return $this->evalList($trueBranch, $vars);
						}
						else
						{
							return $this->evalList($falseBranch, $vars);
						}
					}
					break;

					case 'while':
					{
						array_shift($list); // drop while op
						$cond = array_shift($list); // pull the condition expression

						$body = [];
						while($e = array_shift($list)) // pull remaining s-expression
						{
							$body[] = $e;
						}

						$tmp = $cond;
						while(($return = $this->evalList($tmp, $vars)))
						{
							$tmp = $cond;
							$tmp2 = $body;
							foreach($tmp2 as $e)
							{
								$this->evalList($e, $vars);
							}
						}

						return [];
// TODO: what do we return?
					}
					break;
				}
			}
		}

		$result = 0;
		$op = (string)array_shift($list);

		switch($op)
		{
			case 'and':
			{
				$listSize = sizeof($list);
				// we can't directly array_shift in a while loop due to NULL/FALSE
				for($idx=0; $idx<$listSize; $idx++)
				{
					if($this->parseVar(array_shift($list), $vars) !== TRUE)
					{
						return FALSE;
					}
				}
				return TRUE;
			}
			break;

			case 'not':
			{
				$listSize = sizeof($list);
				for($idx=0; $idx<$listSize; $idx++)
				{
					if($this->parseVar(array_shift($list), $vars) !== FALSE)
					{
						return FALSE;
					}
				}
				return TRUE;
			}
			break;

			case 'or':
			{
				$listSize = sizeof($list);
				for($idx=0; $idx<$listSize; $idx++)
				{
					if($this->parseVar(array_shift($list), $vars) === TRUE)
					{

						return TRUE;
						//continue 2; // dumb
					}
				}
				return FALSE;
			}
			break;

			case '--':
			{
				$ax = array_shift($list);
				$vars[$ax]--;
				return $vars[$ax];
			}
			break;

			case '++':
			{
				$ax = array_shift($list);
				$vars[$ax]++;
				return $vars[$ax];
			}
			break;

			case '!=':
			{
				$ax = array_shift($list);
				$bx = array_shift($list);

				if($this->parseVar($ax, $vars) == $this->parseVar($bx, $vars))
				{
					return FALSE;
				}

				return TRUE;
			}
			break;

			case '<':
			{
				$ax = array_shift($list);
				$bx = array_shift($list);

				if($this->parseVar($ax, $vars) >= $this->parseVar($bx, $vars))
				{
					return FALSE;
				}

				return TRUE;
			}
			break;

			case '>':
			{
				$ax = array_shift($list);
				$bx = array_shift($list);

				if($this->parseVar($ax, $vars) <= $this->parseVar($bx, $vars))
				{
					return FALSE;
				}

				return TRUE;
			}
			break;

			case '<=':
			{
				$ax = array_shift($list);
				$bx = array_shift($list);

				if($this->parseVar($ax, $vars) > $this->parseVar($bx, $vars))
				{
					return FALSE;
				}

				return TRUE;
			}
			break;

			case '>=':
			{
				$ax = array_shift($list);
				$bx = array_shift($list);

				if($this->parseVar($ax, $vars) < $this->parseVar($bx, $vars))
				{
					return FALSE;
				}

				return TRUE;
			}
			break;

			case '+':
			{
				foreach($list as $val)
				{
					$result += $this->parseVar($val, $vars);
				}

				return $result;
			}
			break;

			case '-':
			{
				$ax = $this->parseVar(array_shift($list), $vars);
				foreach($list as $val)
				{
					$ax -= $this->parseVar($val, $vars);
				}
				return $ax;
			}
			break;

			case '*':
			{
				$ax = $this->parseVar(array_shift($list), $vars);
				foreach($list as $val)
				{
					$ax *= $this->parseVar($val, $vars);
				}
				return $ax;
			}
			break;

			case '%':
			{
				$ax = $this->parseVar(array_shift($list), $vars);
				foreach($list as $val)
				{
					$ax %= $val;
				}
				return $ax;
			}
			break;

			case '=':
			{
				$ax = $this->parseVar(array_pop($list), $vars);
				foreach($list as $val)
				{
					if($this->parseVar($val, $vars) != $ax)
					{
						return FALSE;
					}
				}

				return TRUE;
			}
			break;

			case 'echo':
			{
				$ax = $this->parseVar(array_shift($list), $vars);
				echo $ax;
				return $ax;
			}
			break;

			default:
			{
				if(isset($this->subs[$op]))
				{
					$vals = [];
					foreach($list as $idx => $val)
					{
						$vals[':'.$idx] = $this->parseVar($val, $vars);
					}
					$tmp = $this->subs[$op];
					$t = $this->evalList($tmp, $vals);
					return $t;
				}
				else
				{
//					exit('Unknown op: '.$op."\n");
				}
			}
			break;
		}

		print_r($list);
		exit("We shouldn't be here!\n");
	}
}