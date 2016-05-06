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


abstract class Lambda extends \Twig_Node_Expression
{
    protected function compileWithArguments(
        \Twig_Compiler $compiler, $expressionNode, array $arguments)
    {
        $compiler->raw("\n");
        $compiler->indent();
        $compiler->addIndentation();
        $compiler->raw("function() use(&\$context) {\n");
        $compiler->indent();

        // copy of arguments and __ from context
        foreach ($arguments as $arg) {
            $compiler->addIndentation();
            $compiler->raw("if (isset(\$context['$arg'])) \$outer$arg = \$context['$arg'];\n");
        }
        $compiler->addIndentation();
        $compiler->raw("if (isset(\$context['__'])) \$outer__ = \$context['__'];\n");

        // adding closure's arguments to context
        $compiler->addIndentation();
        $compiler->raw("\$context['__'] = func_get_args();\n");
        foreach ($arguments as $i => $arg) {
            $compiler->addIndentation();
            $compiler->raw("if (func_num_args()>$i) \$context['$arg'] = func_get_arg($i);\n");
            $compiler->addIndentation();
            $compiler->raw("else unset(\$context['$arg']);\n");
        }

        // getting call result
        $compiler->addIndentation();
        $compiler->raw("\$result = ");
        $compiler->subcompile($this->getNode($expressionNode));
        $compiler->raw(";\n");

        // recreating original context
        foreach ($arguments as $arg) {
            $compiler->addIndentation();
            $compiler->raw("if (isset(\$outer$arg)) \$context['$arg'] = \$outer$arg ;\n");
            $compiler->addIndentation();
            $compiler->raw("else unset(\$context['$arg']);\n");
        }
        $compiler->addIndentation();
        $compiler->raw("if (isset(\$outer__)) \$context['__'] = \$outer__ ;\n");
        $compiler->addIndentation();
        $compiler->raw("else unset(\$context['__']);\n");

        // return statement
        $compiler->addIndentation();
        $compiler->raw("return \$result;\n");
        $compiler->outdent();
        $compiler->addIndentation();

        $compiler->raw("}\n");
        $compiler->outdent();
        $compiler->addIndentation();
    }
}
