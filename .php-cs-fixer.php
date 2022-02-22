<?php

$excluded_folders = [
  'vendor',
];

return (new PhpCsFixer\Config())
  ->setFinder(
    PhpCsFixer\Finder::create()
      ->in(__DIR__)
      ->exclude($excluded_folders)
      ->notName('README.md')
  )
  ->setRiskyAllowed(true)
  ->setRules(array(
    '@Symfony' => true,
    'binary_operator_spaces' => ['operators' => ['=>' => 'single_space', '=' => 'single_space']],
    'array_syntax' => ['syntax' => 'short'],
    'linebreak_after_opening_tag' => true,
    'not_operator_with_successor_space' => false,
    'ordered_imports' => true,
    'phpdoc_order' => true,
    'logical_operators' => true,
    'modernize_types_casting' => true,
    'yoda_style' => false,
  ));
