<?php

namespace DPolac\TwigLambda\Tests;

use DPolac\TwigLambda\LambdaExtension;

class IntegrationTest extends \Twig_Test_IntegrationTestCase
{

    public function getExtensions()
    {
        return array(
            new LambdaExtension(),
        );
    }

    public function getFixturesDir()
    {
        return dirname(__FILE__).'/Fixtures/';
    }
}
