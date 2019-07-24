<?php
/**
 * This file is part of TwigLambda
 *
 * (c) Damian Polac <damian.polac.111@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DPolac\TwigLambda\NodeExpression;

use Twig\Compiler;
use Twig\Node\Node;

class SimpleLambda extends Lambda
{
    public function __construct(Node $node, int $lineno)
    {
        parent::__construct(array('node' => $node), array(), $lineno);
    }

    public function compile(Compiler $compiler)
    {
        $this->compileWithArguments($compiler, 'node', ['_']);
    }
}