<?php

use PhpCsFixer\Config;

// Create a new Config instance
return (new Config())
    // Disable the use of cache
    ->setUsingCache(false)
    // Allow risky rules
    ->setRiskyAllowed(true)
    // Set the rules for the PHP-CS-Fixer
    ->setRules(
        [
            // PSR-1 coding standard
            '@PSR1' => true,
            // PSR-2 coding standard
            '@PSR2' => true,
            // Symfony coding standard
            '@Symfony' => true,
            // PSR-12 coding standard
            '@PSR12' => true,
            // Enforce strict param types
            'strict_param' => true,
            // Enforce PSR autoloading
            'psr_autoloading' => true,
            // Align multiline comments
            'align_multiline_comment' => ['comment_type' => 'phpdocs_only'], // psr-5
            // Do not convert PHPDoc to comments
            'phpdoc_to_comment' => false,
            // Do not remove superfluous PHPDoc tags
            'no_superfluous_phpdoc_tags' => false,
            // Enforce array indentation
            'array_indentation' => true,
            // Enforce short array syntax
            'array_syntax' => ['syntax' => 'short'],
            // No spaces should be present around cast
            'cast_spaces' => ['space' => 'none'],
            // One space should be present around concatenation
            'concat_space' => ['spacing' => 'one'],
            // Enforce compact nullable type declaration
            'compact_nullable_type_declaration' => true,
            // Enforce nullable type declaration
            'nullable_type_declaration' => true,
            // Enforce nullable type declaration for default null value
            'nullable_type_declaration_for_default_null_value' => true,
            // Normalize declare equal sign
            'declare_equal_normalize' => ['space' => 'single'],
            // Do not enforce strict types declaration
            'declare_strict_types' => false,
            // Post increment style
            'increment_style' => ['style' => 'post'],
            // Enforce short list syntax
            'list_syntax' => ['syntax' => 'short'],
            // Enforce long echo tag syntax
            'echo_tag_syntax' => ['format' => 'long'],
            // Add missing param annotation in PHPDoc
            'phpdoc_add_missing_param_annotation' => ['only_untyped' => false],
            // Do not align PHPDoc
            'phpdoc_align' => false,
            // Do not remove empty return PHPDoc
            'phpdoc_no_empty_return' => false,
            // Order PHPDoc
            'phpdoc_order' => true, // psr-5
            // Do not remove useless inheritdoc PHPDoc
            'phpdoc_no_useless_inheritdoc' => false,
            // Do not change protected to private
            'protected_to_private' => false,
            // Do not enforce yoda style
            'yoda_style' => false,
            'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
            // Order imports
            'ordered_imports' => [
                'sort_algorithm' => 'alpha',
                'imports_order' => ['class', 'const', 'function']
            ],
            // Do not enforce single line throw
            'single_line_throw' => false,
            // Enforce fully qualified strict types
            'fully_qualified_strict_types' => true,
            // Do not import global namespace
            'global_namespace_import' => false,
        ]
    )
    // Set the finder for the PHP-CS-Fixer
    ->setFinder(
        PhpCsFixer\Finder::create()
            // Add directories for the finder to look in
            ->in(__DIR__ . '/src')
            ->in(__DIR__ . '/tests')
            ->in(__DIR__ . '/config')
            ->in(__DIR__ . '/public')
            // Only find PHP files
            ->name('*.php')
            // Ignore dot files
            ->ignoreDotFiles(true)
            // Ignore version control system files
            ->ignoreVCS(true)
    );
