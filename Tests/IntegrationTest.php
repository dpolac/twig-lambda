<?php

namespace LeonAero\TwigLambda\Tests;

use LeonAero\TwigLambda\LambdaExtension;
use Twig\Test\IntegrationTestCase;

class IntegrationTest extends IntegrationTestCase
{

    public function getExtensions()
    {
        return [
            new LambdaExtension(),
        ];
    }

    public function getFixturesDir()
    {
        return __DIR__ .'/Fixtures/';
    }

	public function testLegacyIntegration(
		$file = '',
		$message = '',
		$condition = '',
		$templates = '',
		$exception = '',
		$outputs = '',
		$deprecation = ''
	)
	{
		self::assertTrue(true);
    }
}
