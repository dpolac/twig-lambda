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

/**
 * Behave like ArrayIterator over $array, but returns
 * objects from $keys array as keys.
 * 
 * @internal
 */
final class GroupByObjectIterator implements \Iterator
{
    private $array;
    private $keys;
    
    public function __construct(array $array, array $keys)
    {
        $this->array = $array;
        $this->keys = $keys;
    }

    function rewind() {
        return reset($this->array);
    }
    function current() {
        return current($this->array);
    }
    function key() {
        return $this->keys[key($this->array)];
    }
    function next() {
        return next($this->array);
    }
    function valid() {
        return key($this->array) !== null;
    }
}
