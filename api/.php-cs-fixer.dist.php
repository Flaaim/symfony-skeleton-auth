<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

return new Config()
    ->setRiskyAllowed(true)
    ->setCacheFile(__DIR__ . '/var/cache/.php-cs-fixer.cache')
    ->setRules([
        '@PSR12' => true,
        '@PSR12:risky' => true,
        '@DoctrineAnnotation' => true,
        '@PHP8x2Migration' => true,
        '@PHP8x2Migration:risky' => true,
        '@PHPUnit10x0Migration:risky' => true,
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,

        'ordered_imports' => ['imports_order' => ['class', 'function', 'const']],

        'concat_space' => ['spacing' => 'one'],
        'cast_spaces' => ['space' => 'none'],
        'binary_operator_spaces' => false,

        'phpdoc_to_comment' => false,
        'phpdoc_separation' => false,
        'phpdoc_types_order' => ['null_adjustment' => 'always_last'],
        'phpdoc_align' => false,

        'operator_linebreak' => false,

        'global_namespace_import' => true,

        'blank_line_before_statement' => false,
        'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],

        'fopen_flags' => ['b_mode' => true],

        'php_unit_strict' => false,
    ])
    // 💡 by default, Fixer looks for `*.php` files excluding `./vendor/` - here, you can groom this config
    ->setFinder(
        new Finder()
            // 💡 root folder to check
            ->in([
                __DIR__ . '/bin',
                __DIR__ . '/config',
                __DIR__ . '/public',
                __DIR__ . '/src',
                __DIR__ . '/tests',
            ])
            ->append([
                __FILE__,
            ])
        // 💡 additional files, eg bin entry file
        // ->append([__DIR__.'/bin-entry-file'])
        // 💡 folders to exclude, if any
        // ->exclude([/* ... */])
        // 💡 path patterns to exclude, if any
        // ->notPath([/* ... */])
        // 💡 extra configs
        // ->ignoreDotFiles(false) // true by default in v3, false in v4 or future mode
        // ->ignoreVCS(true) // true by default
    );
