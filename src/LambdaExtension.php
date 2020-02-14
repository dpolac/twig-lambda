<?php
/**
 * This file is part of TwigLambda
 *
 * (c) Damian Polac <damian.polac.111@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LeonAero\TwigLambda;

use DPolac\Dictionary;
use LeonAero\TwigLambda\NodeExpression\Arguments;
use LeonAero\TwigLambda\NodeExpression\LambdaWithArguments;
use LeonAero\TwigLambda\NodeExpression\SimpleLambda;
use Twig\Error\RuntimeError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class LambdaExtension extends AbstractExtension
{

    public function getOperators()
    {
        return [
            [
				'==>' => [
					'precedence' => 0,
					'class' => SimpleLambda::class
				],
            ],
            [
				'==>' => [
					'precedence' => 0,
					'class' => LambdaWithArguments::class,
					'associativity' => \Twig_ExpressionParser::OPERATOR_LEFT
				],
                ';' => [
                    'precedence' => 5,
                    'class' => Arguments::class,
                    'associativity' => \Twig_ExpressionParser::OPERATOR_RIGHT
                ],
            ]
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('call', [$this, 'call']),
        ];
    }

    public function getFilters()
    {
        return [
            new TwigFilter('unique_by', [$this, 'unique_by']),
            new TwigFilter('group_by', [$this, 'group_by']),
            new TwigFilter('count_by', [$this, 'count_by']),

			new TwigFilter('is_every', [$this, 'is_every']),
			new TwigFilter('is_any', [$this, 'is_any']),
        ];
    }

    public function unique_by($array, $callback)
    {
        if (!is_array($array) && !($array instanceof \Traversable)) {
            throw new RuntimeError(sprintf(
                'First argument of "unique_by" must be array or Traversable, but is "%s".', gettype($array)));
        }

        if ('==' === $callback) {
            $callback = static function($item1, $item2) { return $item1 == $item2; };
        } else if ('===' === $callback) {
            $callback = static function($item1, $item2) { return $item1 === $item2; };
        } else if (!is_callable($callback)) {
            throw new RuntimeError(sprintf(
                'Second argument of "unique_by" must be callable, "==" or "===", but is "%s".', gettype($callback)));
        }

        if ($array instanceof \Traversable) {
            if ($array instanceof \Iterator) {
                // convert Iterator to IteratorAggregate for nested foreach
                $array = Dictionary::fromArray($array);
            }
            $result = new Dictionary();
        } else {
            $result = [];
        }

        foreach ($array as $i => $item) {
            foreach ($array as $j => $previous) {
                if ($i === $j) {
                    // add to results if already checked every previous element
                    $result[$i] = $item;
                } elseif (isset($result[$j]) && $callback($item, $previous, $i, $j)) {
                    // skip if is identical with value which is already in results array
                    continue 2;
                }
            }
        }
        return $result;
    }

    public function group_by($array, $callback)
    {

        if (!is_callable($callback)) {
            throw new RuntimeError(sprintf(
                'Second argument of "group_by" must be callable, but is "%s".', gettype($callback)));
        }

        if (!is_array($array) && !($array instanceof \Traversable)) {
            throw new RuntimeError(sprintf(
                'First argument of "group_by" must be array or Traversable, but is "%s".', gettype($array)));
        }

        $results = new Dictionary();

        foreach ($array as $i => $item) {
            $key = $callback($item, $i);

            if (!isset($results[$key])) {
                $results[$key] = [$i => $item];
            } else {
                $results[$key][$i] = $item;
            }

        }

        return $results;
    }

    public function count_by($array, $callback)
    {
        if (!is_callable($callback)) {
            throw new RuntimeError(sprintf(
                'Second argument of "count_by" must be callable, but is "%s".', gettype($callback)));
        }

        if (!is_array($array) && !($array instanceof \Traversable)) {
            throw new RuntimeError(sprintf(
                'First argument of "count_by" must be array or Traversable, but is "%s".', gettype($array)));
        }

        $result = [];
        foreach ($array as $i => $element) {
            $key = $callback($element, $i);
            if (is_bool($key)) {
                $key = $key ? 'true' : 'false';
            } elseif ($key === null) {
                $key = 'null';
            }
            if (!isset($result[$key])) {
                $result[$key] = 1;
            } else {
                ++$result[$key];
            }
        }
        return $result;
    }

    public function is_every($array, $callback): bool
	{
        if (!is_callable($callback)) {
            throw new RuntimeError(sprintf(
                'Second argument of "every" must be callable, but is "%s".', gettype($callback)));
        }

        if (!is_array($array) && !($array instanceof \Traversable)) {
            throw new RuntimeError(sprintf(
                'First argument of "every" must be array or Traversable, but is "%s".', gettype($array)));
        }

        foreach ($array as $i => $item) {
            if (!$callback($item, $i)) {
                return false;
            }
        }

        return true;
    }

    public function is_any($array, $callback): bool
	{
        if (!is_callable($callback)) {
            throw new RuntimeError(sprintf(
                'Second argument of "any" must be callable, but is "%s".', gettype($callback)));
        }

        if (!is_array($array) && !($array instanceof \Traversable)) {
            throw new RuntimeError(sprintf(
            	'First argument of "any" must be array or Traversable, but is "%s".', gettype($array)));
        }

        foreach ($array as $i => $item) {
            if ($callback($item, $i)) {
                return true;
            }
        }

        return false;
    }

    public function call($callback, array $args = [])
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('First argument must be callable.');
        }
        return call_user_func_array($callback, $args);
    }

    public function getName()
    {
        return 'leonaero_lambda_extension';
    }
}
