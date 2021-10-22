<?php

$finder = Symfony\Component\Finder\Finder::create()
  ->notPath('node_modules')
  ->notPath('vendor')
  ->notPath('public')
  ->notPath('resources')
  ->in(__DIR__)
  ->name('*.php')
  ->notName('*.blade.php');

$config = new PhpCsFixer\Config();

return $config->setRules([
    '@PSR2' => true,
    'array_syntax' => ['syntax' => 'short'],
    'ordered_imports' => ['sort_algorithm' => 'alpha'],
    'no_unused_imports' => true,
  ])
  ->setFinder($finder);
