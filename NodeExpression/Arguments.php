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

use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Expression\NameExpression;
use Twig\Node\Node;
use Twig\Compiler;

class Arguments extends AbstractExpression
{
    private $arguments;
    
    public function __construct(Node $left, Node $right, int $lineno)
    {
        $arguments = [];
        foreach ([$left, $right] as $node) {
            if ($node instanceof Arguments) {
                $arguments[] = $node->getArguments();
            } elseif ($node instanceof NameExpression) {
                $arguments[] = [$node->getAttribute('name')];
            } else {
                throw new \InvalidArgumentException('Invalid argument.');
            }
        }
        
        $this->arguments = array_merge($arguments[0], $arguments[1]);
        
        parent::__construct(array('left' => $left, 'right' => $right), array(), $lineno);
    }

    public function compile(Compiler $compiler)
    {
        throw new \Exception('Semicolon-separated list of arguments can be only used in lambda expression.');
    }
    
    public function getArguments() 
    {
        return $this->arguments;
    }
}
