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


class SimpleLambda extends Lambda
{
    public function __construct(\Twig_Node $node, $lineno)
    {
        parent::__construct(array('node' => $node), array(), $lineno);
    }

    public function compile(\Twig_Compiler $compiler)
    {
        $this->compileWithArguments($compiler, 'node', ['_']);
    }
}
