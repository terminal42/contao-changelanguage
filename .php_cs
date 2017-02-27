<?php

$date = date('Y');

$header = <<<EOF
changelanguage Extension for Contao Open Source CMS

@copyright  Copyright (c) 2008-$date, terminal42 gmbh
@author     terminal42 gmbh <info@terminal42.ch>
@license    http://opensource.org/licenses/lgpl-3.0.html LGPL
@link       http://github.com/terminal42/contao-changelanguage
EOF;

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules(
        array(
            '@Symfony' => true,
            '@Symfony:risky' => true,
            'array_syntax' => array('syntax' => 'short'),
            'combine_consecutive_unsets' => true,
            // one should use PHPUnit methods to set up expected exception instead of annotations
            'general_phpdoc_annotation_remove' => array('expectedException', 'expectedExceptionMessage', 'expectedExceptionMessageRegExp'),
            'header_comment' => array('header' => $header),
            'heredoc_to_nowdoc' => true,
            'no_extra_consecutive_blank_lines' => array('break', 'continue', 'extra', 'return', 'throw', 'use', 'parenthesis_brace_block', 'square_brace_block', 'curly_brace_block'),
            'no_unreachable_default_argument_value' => true,
            'no_useless_else' => true,
            'no_useless_return' => true,
            'ordered_class_elements' => true,
            'ordered_imports' => true,
            'php_unit_strict' => true,
            'phpdoc_add_missing_param_annotation' => true,
            'phpdoc_order' => true,
            'psr4' => true,
            'strict_comparison' => true,
            'strict_param' => true,
        )
    )
    ->setFinder(
        PhpCsFixer\Finder::create()->in(array(__DIR__.'/library'))
    )
;
