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
use Twig\Node\Expression\NameExpression;
use Twig\Node\Node;

class LambdaWithArguments extends Lambda
{
    private $arguments = [];

    public function __construct(Node $left, Node $right, int $lineno)
    {
        parent::__construct(array('left' => $left, 'right' => $right), array(), $lineno);

        if ($left instanceof NameExpression) {
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

    public function compile(Compiler $compiler)
    {
        $this->compileWithArguments($compiler, 'right', $this->arguments);
    }
}