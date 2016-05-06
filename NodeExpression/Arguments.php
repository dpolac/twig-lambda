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

class Arguments extends \Twig_Node_Expression
{
    private $arguments;
    
    public function __construct(\Twig_Node $left, \Twig_Node $right, $lineno)
    {
        $arguments = [];
        foreach ([$left, $right] as $node) {
            if ($node instanceof Arguments) {
                $arguments[] = $node->getArguments();
            } elseif ($node instanceof \Twig_Node_Expression_Name) {
                $arguments[] = [$node->getAttribute('name')];
            } else {
                throw new \InvalidArgumentException('Invalid argument.');
            }
        }
        
        $this->arguments = array_merge($arguments[0], $arguments[1]);
        
        parent::__construct(array('left' => $left, 'right' => $right), array(), $lineno);
    }

    public function compile(\Twig_Compiler $compiler)
    {
        throw new \Exception('Semicolon-separated list of arguments can be only used in lambda expression.');
    }
    
    public function getArguments() 
    {
        return $this->arguments;
    }
}
