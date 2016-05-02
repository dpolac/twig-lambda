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


class Lambda extends \Twig_Node_Expression
{
    public function __construct(\Twig_Node $node, $lineno)
    {
        parent::__construct(array('node' => $node), array(), $lineno);
    }

    public function compile(\Twig_Compiler $compiler)
    {
        $compiler->raw("\n");
        $compiler->indent();
        $compiler->addIndentation();
        $compiler->raw("function() use(&\$context) {\n");
        $compiler->indent();

        // copy of _ and __ from context
        $compiler->addIndentation();
        $compiler->raw("if (isset(\$context['_'])) \$outer_ = \$context['_'];\n");
        $compiler->addIndentation();
        $compiler->raw("if (isset(\$context['__'])) \$outer__ = \$context['__'];\n");

        // adding closure's arguments to context
        $compiler->addIndentation();
        $compiler->raw("\$context['__'] = func_get_args();\n");
        $compiler->addIndentation();
        $compiler->raw("if (func_num_args()>0) \$context['_'] = func_get_arg(0);\n");
        $compiler->addIndentation();
        $compiler->raw("else unset(\$context['_']);\n");

        // getting call result
        $compiler->addIndentation();
        $compiler->raw("\$result = ");
        $compiler->subcompile($this->getNode('node'));
        $compiler->raw(";\n");

        // recreating original context
        $compiler->addIndentation();
        $compiler->raw("if (isset(\$outer_)) \$context['_'] = \$outer_ ;\n");
        $compiler->addIndentation();
        $compiler->raw("else unset(\$context['_']);\n");
        $compiler->addIndentation();
        $compiler->raw("if (isset(\$outer__)) \$context['__'] = \$outer__ ;\n");
        $compiler->addIndentation();
        $compiler->raw("else unset(\$context['__']);\n");

        // return statement
        $compiler->addIndentation();
        $compiler->raw("return \$result;");
        $compiler->outdent();
        $compiler->addIndentation();

        $compiler->raw("}\n");
        $compiler->outdent();
        $compiler->addIndentation();
    }
}
