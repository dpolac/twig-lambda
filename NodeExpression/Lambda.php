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
use Twig\Node\Expression\AbstractExpression;

abstract class Lambda extends AbstractExpression
{
    protected function compileWithArguments(Compiler $compiler, $expressionNode, array $arguments)
    {
        $compiler->raw("\n");
        $compiler->indent();
        $compiler->write('');
        $compiler->raw("function() use(&\$context) {\n");
        $compiler->indent();

        // copy of arguments and __ from context
        foreach ($arguments as $arg) {
            $compiler->write('');
            $compiler->raw("if (isset(\$context['$arg'])) \$outer$arg = \$context['$arg'];\n");
        }
        $compiler->write('');
        $compiler->raw("if (isset(\$context['__'])) \$outer__ = \$context['__'];\n");

        // adding closure's arguments to context
        $compiler->write('');
        $compiler->raw("\$context['__'] = func_get_args();\n");
        foreach ($arguments as $i => $arg) {
            $compiler->write('');
            $compiler->raw("if (func_num_args()>$i) \$context['$arg'] = func_get_arg($i);\n");
            $compiler->write('');
            $compiler->raw("else unset(\$context['$arg']);\n");
        }

        // getting call result
        $compiler->write('');
        $compiler->raw("\$result = ");
        $compiler->subcompile($this->getNode($expressionNode));
        $compiler->raw(";\n");

        // recreating original context
        foreach ($arguments as $arg) {
            $compiler->write('');
            $compiler->raw("if (isset(\$outer$arg)) \$context['$arg'] = \$outer$arg ;\n");
            $compiler->write('');
            $compiler->raw("else unset(\$context['$arg']);\n");
        }
        $compiler->write('');
        $compiler->raw("if (isset(\$outer__)) \$context['__'] = \$outer__ ;\n");
        $compiler->write('');
        $compiler->raw("else unset(\$context['__']);\n");

        // return statement
        $compiler->write('');
        $compiler->raw("return \$result;\n");
        $compiler->outdent();
        $compiler->write('');

        $compiler->raw("}\n");
        $compiler->outdent();
        $compiler->write('');
    }
}
