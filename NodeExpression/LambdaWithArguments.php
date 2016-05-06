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


class LambdaWithArguments extends Lambda
{
    private $arguments = [];

    public function __construct(\Twig_Node $left, \Twig_Node $right, $lineno)
    {
        parent::__construct(array('left' => $left, 'right' => $right), array(), $lineno);

        if ($left instanceof \Twig_Node_Expression_Name) {
            $this->arguments = [ $left->getAttribute('name') ];
        } elseif ($left instanceof Arguments) {
            $this->arguments = $left->getArguments();
        } else {
            throw new \InvalidArgumentException('Invalid argument\'s list for lambda.');
        }
        
        if (count($this->arguments) !== count(array_flip($this->arguments))) {
            throw new \InvalidArgumentException('Each lambda argument must have unique name.');
        }

    }

    public function compile(\Twig_Compiler $compiler)
    {
        $this->compileWithArguments($compiler, 'right', $this->arguments);
    }
}
