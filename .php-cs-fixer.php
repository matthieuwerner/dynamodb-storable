<?php

$finder = PhpCsFixer\Finder::create()
    ->path('src')
    ->in(__DIR__);

$config = new PhpCsFixer\Config();
return $config->setRules([
    '@PHP80Migration' => true,
    '@PhpCsFixer' => true,
    '@PSR1' => true,
    '@PSR12' => true,
    '@PSR2' => true,
])->setFinder($finder);
