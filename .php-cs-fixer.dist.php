<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
    ->exclude('vendor')
    ->notPath('tests/bootstrap.php');

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHP82Migration' => true,
        '@PHP80Migration:risky' => true,
        '@PHPUnit100Migration:risky' => true,
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,

        // Дополнительные строгие правила
        'declare_strict_types' => true,
        'strict_param' => true,
        'strict_comparison' => true,
        'native_function_invocation' => [
            'include' => ['@all'],
            'scope' => 'namespaced',
        ],
        'native_constant_invocation' => [
            'scope' => 'namespaced',
        ],

        // Типизация
        'fully_qualified_strict_types' => true,
        'phpdoc_to_param_type' => true,
        'phpdoc_to_property_type' => true,
        'phpdoc_to_return_type' => true,

        // Форматирование и стиль
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
            'imports_order' => ['class', 'function', 'const'],
        ],
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'case',
                'constant_public',
                'constant_protected',
                'constant_private',
                'property_public',
                'property_protected',
                'property_private',
                'construct',
                'destruct',
                'magic',
                'phpunit',
                'method_public',
                'method_protected',
                'method_private',
            ],
        ],
        'phpdoc_order' => true,
        'phpdoc_separation' => true,
        'phpdoc_align' => ['align' => 'left'],
        'concat_space' => ['spacing' => 'one'],
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],

        // Безопасность
        'void_return' => true,
        'no_useless_else' => true,

        // Отключаем некоторые конфликтующие правила
        'single_line_throw' => false,
        'blank_line_before_statement' => [
            'statements' => ['break', 'continue', 'declare', 'return', 'throw', 'try'],
        ],
        'phpdoc_summary' => false,
        'php_unit_internal_class' => false,
        'php_unit_test_class_requires_covers' => false,
        'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
        'return_assignment' => false,
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
