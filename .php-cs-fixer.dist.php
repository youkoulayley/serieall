<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude([
        'bootstrap/cache',
        'public/js/elFinder/php'
    ])
    ->in(__DIR__)
;

$config = new PhpCsFixer\Config();
return $config->setRules([
    '@PSR12' => true,
    'array_syntax' => ['syntax' => 'short'],
])
    ->setFinder($finder)
;
