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


class LambdaExtension extends \Twig_Extension
{

    public function getOperators()
    {
        return [
            [
                '=>' => [
                    'precedence' => 0,
                    'class' => '\DPolac\TwigLambda\NodeExpression\SimpleLambda'
                ],
            ],
            [
                '=>' => [
                    'precedence' => 0,
                    'class' => '\DPolac\TwigLambda\NodeExpression\LambdaWithArguments',
                    'associativity' => \Twig_ExpressionParser::OPERATOR_LEFT
                ],
                ';' => [
                    'precedence' => 5,
                    'class' => '\DPolac\TwigLambda\NodeExpression\Arguments',
                    'associativity' => \Twig_ExpressionParser::OPERATOR_RIGHT
                ],
            ]
        ];
    }
    
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('call', '\DPolac\TwigLambda\LambdaExtension::call'),
        ];
    }

    public function getTests()
    {
        return [
            new \Twig_SimpleTest('every', '\DPolac\TwigLambda\LambdaExtension::every'),
            new \Twig_SimpleTest('any', '\DPolac\TwigLambda\LambdaExtension::any'),
        ];
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('map', '\DPolac\TwigLambda\LambdaExtension::map'),
            new \Twig_SimpleFilter('select', '\DPolac\TwigLambda\LambdaExtension::map'),

            new \Twig_SimpleFilter('filter', '\DPolac\TwigLambda\LambdaExtension::filter'),
            new \Twig_SimpleFilter('where', '\DPolac\TwigLambda\LambdaExtension::filter'),

            new \Twig_SimpleFilter('unique_by', '\DPolac\TwigLambda\LambdaExtension::uniqueBy'),
            new \Twig_SimpleFilter('group_by', '\DPolac\TwigLambda\LambdaExtension::groupBy'),
            new \Twig_SimpleFilter('sort_by', '\DPolac\TwigLambda\LambdaExtension::sortBy'),
            new \Twig_SimpleFilter('count_by', '\DPolac\TwigLambda\LambdaExtension::countBy'),
        ];
    }
    
    public static function map($array, $callback) 
    {
        if (!is_callable($callback)) {
            throw new \Twig_Error_Runtime(sprintf(
                'Second argument of "map" must be callable, but is "%s".', gettype($callback)));
        }
        
        if (is_array($array)) {
            $array = array_map($callback, $array, array_keys($array));
        } elseif ($array instanceof \Traversable) {
            $result = [];
            foreach ($array as $i => $item) {
                $result[$i] = $callback($item, $i);
            }
            $array = $result;
        } else {
            throw new \Twig_Error_Runtime(sprintf(
                'First argument of "map" must be array or Traversable, but is "%s".', gettype($array)));
        }
        
        return $array;
    }

    public static function filter($array, $callback)
    {
        if (!is_callable($callback)) {
            throw new \Twig_Error_Runtime(sprintf(
                'Second argument of "filter" must be callable, but is "%s".', gettype($callback)));
        }

        if (is_array($array)) {
            $array = array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
        } elseif ($array instanceof \Traversable) {
            $result = [];
            foreach ($array as $i => $item) {
                if ($callback($item, $i)) {
                    $result[$i] = $item;
                }
            }
            $array = $result;
        } else {
            throw new \Twig_Error_Runtime(sprintf(
                'First argument of "filter" must be array or Traversable, but is "%s".', gettype($array)));
        }

        return $array;
    }

    public static function uniqueBy($array, $callback)
    {
        if (!is_array($array) && !($array instanceof \Traversable)) {
            throw new \Twig_Error_Runtime(sprintf(
                'First argument of "unique_by" must be array or Traversable, but is "%s".', gettype($array)));
        }

        if (!is_callable($callback)) {
            throw new \Twig_Error_Runtime(sprintf(
                'Second argument of "unique_by" must be callable, but is "%s".', gettype($callback)));
        }

        if ($array instanceof \Traversable) {
            $array = iterator_to_array($array);
        }

        $result = [];
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

    public static function groupBy($array, $callback, $keyType = 'detect')
    {
        $keyType = strtolower($keyType);

        if (!in_array($keyType, ['scalar', 'object', 'detect'])) {
            throw new \Twig_Error_Runtime(sprintf(
                'Third argument of "group_by" must be one of "scalar", "object" and "detect", '.
                'but is "%s".', $keyType));
        }

        if (!is_callable($callback)) {
            throw new \Twig_Error_Runtime(sprintf(
                'Second argument of "group_by" must be callable, but is "%s".', gettype($callback)));
        }

        if (!is_array($array) && !($array instanceof \Traversable)) {
            throw new \Twig_Error_Runtime(sprintf(
                'First argument of "group_by" must be array or Traversable, but is "%s".', gettype($array)));
        }

        $results = [];
        $hashes = [];
        foreach ($array as $i => $item) {
            $key = $callback($item, $i);

            if ('detect' === $keyType) {
                if (is_object($key)) {
                    $keyType = 'object';
                } else if (is_scalar($key)) {
                    $keyType = 'scalar';
                } else {
                    throw new \Twig_Error_Runtime(sprintf(
                        'Only allowed values returned by "group_by" are '.
                        'callback are scalars and objects with implemented __toString() method, '.
                        'but it returned "%s".', gettype($key)));
                }
            }

            if ('scalar' === $keyType) {
                if (is_object($key) && method_exists($key, '__toString')) {
                    $key = (string)$key;
                } elseif (!is_scalar($key)) {
                    throw new \Twig_Error_Runtime(sprintf(
                        'Only allowed values returned by "group_by" callback with "scalar" ' .
                        'as key type are scalars and objects with implemented __toString() method, ' .
                        'but it returned "%s".', gettype($key)));
                }

                if (!isset($results[$key])) {
                    $results[$key] = [$i => $item];
                } else {
                    $results[$key][$i] = $item;
                }
            } elseif ('object' === $keyType) {
                if (!is_object($key)) {
                    throw new \Twig_Error_Runtime(sprintf(
                        'Only allowed values returned by "group_by" callback with "object" ' .
                        'as key type are objects, but it returned "%s".', gettype($key)));
                }

                $hash = \spl_object_hash($key);
                $hashes[$hash] = $key;

                if (!isset($results[$hash])) {
                    $results[$hash] = [$i => $item];
                } else {
                    $results[$hash][$i] = $item;
                }
            }
        }

        if ('scalar' === $keyType) {
            return $results;
        } elseif ('object' === $keyType) {
            return new GroupByObjectIterator($results, $hashes);
        } else {
            // $keyType is still 'detect' if $array is empty
            return [];
        }
    }

    public static function sortBy($array, $callback, $direction = 'ASC')
    {
        if (!is_callable($callback)) {
            throw new \Twig_Error_Runtime(sprintf(
                'Second argument of "sort_by" must be callable, but is "%s".', gettype($callback)));
        }

        if (!is_array($array) && !($array instanceof \Traversable)) {
            throw new \Twig_Error_Runtime(sprintf(
                'First argument of "sort_by" must be array or Traversable, but is "%s".', gettype($array)));
        }

        if ($array instanceof \Traversable) {
            $array = iterator_to_array($array);
        }

        $direction = (strtoupper($direction) === 'DESC') ? SORT_DESC : SORT_ASC;
        $order = self::map($array, $callback);
        array_multisort($order, $direction, SORT_REGULAR, $array);
        return $array;
    }

    public static function countBy($array, $callback)
    {
        if (!is_callable($callback)) {
            throw new \Twig_Error_Runtime(sprintf(
                'Second argument of "count_by" must be callable, but is "%s".', gettype($callback)));
        }

        if (!is_array($array) && !($array instanceof \Traversable)) {
            throw new \Twig_Error_Runtime(sprintf(
                'First argument of "count_by" must be array or Traversable, but is "%s".', gettype($array)));
        }

        $result = [];
        foreach ($array as $i => $element) {
            $key = $callback($element, $i);
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
    
    public static function every($array, $callback)
    {
        if (!is_callable($callback)) {
            throw new \Twig_Error_Runtime(sprintf(
                'Second argument of "every" must be callable, but is "%s".', gettype($callback)));
        }

        if (!is_array($array) && !($array instanceof \Traversable)) {
            throw new \Twig_Error_Runtime(sprintf(
                'First argument of "every" must be array or Traversable, but is "%s".', gettype($array)));
        }

        foreach ($array as $i => $item) {
            if (!$callback($item, $i)) {
                return false;
            }
        }

        return true;
    }
    
    public static function any($array, $callback)
    {
        if (!is_callable($callback)) {
            throw new \Twig_Error_Runtime(sprintf(
                'Second argument of "any" must be callable, but is "%s".', gettype($callback)));
        }

        if (!is_array($array) && !($array instanceof \Traversable)) {
            throw new \Twig_Error_Runtime(sprintf(
                'First argument of "any" must be array or Traversable, but is "%s".', gettype($array)));
        }

        foreach ($array as $i => $item) {
            if ($callback($item, $i)) {
                return true;
            }
        }

        return false;
    }

    public static function call($callback, array $args = [])
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