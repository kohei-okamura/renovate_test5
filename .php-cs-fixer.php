<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
$finder = PhpCsFixer\Finder::create()
    ->exclude([
        'resources',
        'storage',
        'vendor',
        '_generated',
    ])
    ->in(__DIR__ . '/server');

$config = new PhpCsFixer\Config();
return $config
    ->setRiskyAllowed(true)
    ->setRules([
        '@PhpCsFixer' => true,
        '@PSR2' => true,
        'blank_line_before_statement' => false,
        'cast_spaces' => [
            'space' => 'none',
        ],
        'concat_space' => [
            'spacing' => 'one',
        ],
        'empty_loop_body' => false,
        'function_declaration' => [
            'closure_function_spacing' => 'one',
        ],
        'logical_operators' => true,
        'modernize_types_casting' => true,
        'multiline_whitespace_before_semicolons' => true,
        'native_constant_invocation' => true,
        'native_function_invocation' => false,
        'no_alias_functions' => true,
        'no_extra_blank_lines' => true,
        'no_homoglyph_names' => true,
        'no_php4_constructor' => true,
        'no_superfluous_elseif' => false,
        'no_superfluous_phpdoc_tags' => false,
        'no_unneeded_final_method' => true,
        'no_unreachable_default_argument_value' => true,
        'no_unset_on_property' => true,
        'no_useless_else' => false,
        'non_printable_character' => [
            'use_escape_sequences_in_strings' => true,
        ],
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'constant_public',
                'constant_protected',
                'constant_private',
                'property_public',
                'property_protected',
                'property_private',
                'construct',
                'destruct',
                'phpunit',
                'method_public',
                'method_protected',
                'method_private',
                'magic',
            ],
        ],
        'php_unit_construct' => true,
        'php_unit_dedicate_assert' => true,
        'php_unit_internal_class' => false,
        'php_unit_method_casing' => false,
        'php_unit_mock' => true,
        'php_unit_namespaced' => true,
        'php_unit_no_expectation_annotation' => true,
        'php_unit_set_up_tear_down_visibility' => true,
        'php_unit_test_case_static_method_calls' => [
            'call_type' => 'self',
        ],
        'php_unit_test_class_requires_covers' => false,
        'phpdoc_align' => [
            'align' => 'left',
        ],
        'phpdoc_add_missing_param_annotation' => [
            'only_untyped' => false,
        ],
        'phpdoc_no_alias_tag' => false,
        'phpdoc_no_empty_return' => false,
        'phpdoc_separation' => false,
        'phpdoc_summary' => false,
        'phpdoc_to_comment' => false,
        'pow_to_exponentiation' => true,
        'random_api_migration' => true,
        'self_accessor' => false,
        'set_type_to_cast' => true,
        'single_class_element_per_statement' => true,
        'simplified_null_return' => true,
        'single_quote' => true,
        'strict_comparison' => true,
        'strict_param' => true,
        'string_line_ending' => true,
        'ternary_to_null_coalescing' => true,
        'trailing_comma_in_multiline' => true,
        'visibility_required' => true,
        'void_return' => false,
        'yoda_style' => false,
    ])
    ->setFinder($finder);
