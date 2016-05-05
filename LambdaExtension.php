<?php
/**
 * This file is part of TwigLambda
 *
 * (c) Damian Polac <damian.polac.111@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DPolac\TwigLambda;

use Underscore\Types\Arrays;

class LambdaExtension extends \Twig_Extension
{

    public function getOperators()
    {
        return [
            [
                '=>' => [
                    'precedence' => 0,
                    'class' => '\DPolac\TwigLambda\NodeExpression\Lambda'
                ],
            ],
            []
        ];
    }
    
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('call', [$this, 'call']),
        ];
    }

    public function getTests()
    {
        return [
            new \Twig_SimpleTest('every', '\Underscore\Types\Arrays::matches'),
            new \Twig_SimpleTest('any', [$this, 'any']),
        ];
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('map', '\Underscore\Types\Arrays::each'),
            new \Twig_SimpleFilter('select', '\Underscore\Types\Arrays::each'),

            new \Twig_SimpleFilter('filter', '\Underscore\Types\Arrays::filter'),
            new \Twig_SimpleFilter('where', '\Underscore\Types\Arrays::filter'),

            new \Twig_SimpleFilter('unique', 'array_unique'),

            new \Twig_SimpleFilter('group_by', '\Underscore\Types\Arrays::group'),
            new \Twig_SimpleFilter('sort_by', '\Underscore\Types\Arrays::sort'),
            new \Twig_SimpleFilter('count_by', [$this, 'countBy']),
        ];
    }

    public function countBy(array $array, $callback)
    {
        $result = [];
        foreach ($array as $element) {
            $key = $callback($element);
            if (is_bool($key)) {
                $key = $key ? 'true' : 'false';
            } elseif (is_null($key)) {
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

    public function any(array $array, $callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException(
                'Second argument of any must be callable.');
        }

        if (count($array) === 0) {
            return false;
        }

        return Arrays::matchesAny($array, $callback);
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
        return 'dpolac_lambda_extension';
    }
}
