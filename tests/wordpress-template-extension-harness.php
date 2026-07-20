<?php

require __DIR__ . '/template-extension-test-bootstrap.php';

$root = dirname(__DIR__);
require $root . '/plugin/includes/core/template-contract.php';
require $root . '/plugin/includes/core/template-render-result.php';
require $root . '/plugin/includes/core/template-registry.php';
require $root . '/plugin/includes/core/templates.php';
require $root . '/plugin/includes/core/template-renderer.php';
require $root . '/plugin/includes/core/template-public-renderer.php';

add_action('wp_seed_content_kit_register_template_modules', function () {
    wp_seed_content_kit_register_template_module('catalog', array(
        'label' => 'Catalog',
        'description' => 'Neutral WordPress integration fixture.',
        'render_types' => array('native', 'divi_layout'),
        'assets' => array('styles' => array('third-party-card')),
        'placeholders' => array(
            'item.title' => array('type' => 'text', 'context_key' => 'title', 'required' => true),
            'item.body' => array('type' => 'html', 'context_key' => 'body'),
        ),
    ));
});
do_action('init');

$modules = wp_seed_content_get_template_modules();
wp_seed_contract_true(array_key_exists('catalog', $modules), 'third-party module available in Template admin');
wp_seed_contract_same('', $modules['catalog'], 'third-party module does not invent a shortcode');
wp_seed_contract_same('Catalog', wp_seed_content_get_template_module_name('catalog'), 'third-party module label in Template admin');
wp_seed_contract_true(in_array('catalog', wp_seed_content_get_template_supported_modules(), true), 'third-party module marked supported');
wp_seed_contract_same('', wp_seed_content_get_template_shortcode_from_module('catalog'), 'empty third-party shortcode helper');
wp_seed_contract_contains('{{item.title}}', wp_seed_content_get_template_example_by_module('catalog'), 'generic third-party Template example');
wp_seed_contract_contains('{{item.body}}', wp_seed_content_get_template_example_by_module('catalog'), 'all third-party placeholders in example');

wp_seed_contract_add_template(301, 'catalog-card', 'catalog', 'publish', '<article class="catalog-card"><h2>{{item.title}}</h2>{{item.body}}</article>');
wp_seed_contract_add_template(302, 'catalog-draft', 'catalog', 'draft', '{{item.title}}');
wp_seed_contract_add_template(303, 'catalog-wrong', 'quotes', 'publish', '{{item.title}}');
wp_seed_contract_add_template(304, 'catalog-recursive', 'catalog', 'publish', '[[render:catalog-recursive]]');

$context = array('title' => 'Public title', 'body' => '<p>Public body</p>', 'private_meta' => 'PRIVATE');
$rendered = wp_seed_content_kit_render_template('catalog-card', 'catalog', $context);
wp_seed_contract_true($rendered->is_success(), 'published third-party template renders');
wp_seed_contract_contains('Public title', $rendered->get_html(), 'public title rendered');
wp_seed_contract_contains('<p>Public body</p>', $rendered->get_html(), 'public HTML rendered');
wp_seed_contract_not_contains('PRIVATE', $rendered->get_html(), 'unknown private context excluded');
wp_seed_contract_same('template_not_found', wp_seed_content_kit_render_template('catalog-draft', 'catalog', $context)->get_code(), 'WordPress draft refused');
wp_seed_contract_same('module_mismatch', wp_seed_content_kit_render_template('catalog-wrong', 'catalog', $context)->get_code(), 'WordPress module mismatch');
wp_seed_contract_true(isset($GLOBALS['wp_seed_contract_enqueued_styles']['third-party-card']), 'WordPress asset enqueued');

$gutenberg = apply_filters('the_content', '<!-- wp:shortcode -->' . $rendered->get_html() . '<!-- /wp:shortcode -->');
wp_seed_contract_contains('catalog-card', $gutenberg, 'Gutenberg server pipeline');
wp_seed_contract_same(false, class_exists('ET_Builder_Module'), 'works without Divi classes');
wp_seed_contract_same(false, isset($GLOBALS['wp_seed_contract_meta_reads_outside_template']), 'no implicit business meta access');

wp_seed_contract_finish('WordPress template extension');
