<?php

require __DIR__ . '/template-extension-test-bootstrap.php';

$root = dirname(__DIR__);
require $root . '/plugin/includes/core/template-contract.php';
require $root . '/plugin/includes/core/template-render-result.php';
require $root . '/plugin/includes/core/template-registry.php';
require $root . '/plugin/includes/core/templates.php';
require $root . '/plugin/includes/core/template-renderer.php';
require $root . '/plugin/includes/core/template-public-renderer.php';

wp_seed_contract_same('1.0', wp_seed_content_kit_get_contract_version(), 'contract version');
foreach (array('template_extension', 'template_modules', 'template_placeholders', 'typed_render_result', 'render_assets', 'recursion_guard') as $capability) {
    wp_seed_contract_true(wp_seed_content_kit_supports($capability), 'capability ' . $capability);
    wp_seed_contract_true(wp_seed_content_kit_supports($capability, '1.0'), 'capability minimum ' . $capability);
    wp_seed_contract_same(false, wp_seed_content_kit_supports($capability, '1.1'), 'capability future ' . $capability);
}
wp_seed_contract_same(false, wp_seed_content_kit_supports('unknown'), 'unknown capability');
wp_seed_contract_same(false, wp_seed_content_kit_supports('template_extension', 'invalid'), 'invalid minimum version');

$before_init = wp_seed_content_kit_render_template('card', 'third_party', array());
wp_seed_contract_same('unavailable', $before_init->get_code(), 'render before registry initialization');
wp_seed_contract_same('', $before_init->get_html(), 'failure HTML empty');
wp_seed_contract_same(0, $before_init->get_template_id(), 'failure template id empty');
wp_seed_contract_same(array('styles' => array(), 'scripts' => array()), $before_init->get_assets(), 'failure assets empty');

add_action('wp_seed_content_kit_register_template_modules', function () {
    $registered = wp_seed_content_kit_register_template_module(
        'third_party',
        array(
            'label' => 'Third-party cards',
            'description' => 'Neutral test provider.',
            'render_types' => array('native', 'divi_layout'),
            'assets' => array(
                'styles' => array('third-party-card', 'third-party-card'),
                'scripts' => array('third-party-interaction'),
            ),
            'validate_context' => function ($context) {
                return !isset($context['title']) || 'blocked' !== $context['title'];
            },
            'provider' => function ($context) {
                $GLOBALS['wp_seed_contract_provider_context'] = $context;
                return $context;
            },
            'placeholders' => array(
                'title' => array('type' => 'text', 'label' => 'Title', 'required' => true),
                'summary' => array('type' => 'textarea', 'label' => 'Summary'),
                'markup' => array('type' => 'html', 'label' => 'Markup'),
                'website' => array('type' => 'url', 'label' => 'Website'),
                'email' => array('type' => 'email', 'label' => 'Email'),
                'phone' => array('type' => 'tel', 'label' => 'Phone'),
                'photo' => array('type' => 'image', 'label' => 'Photo'),
                'tags' => array('type' => 'text_list', 'label' => 'Tags'),
                'normalized' => array(
                    'type' => 'text',
                    'label' => 'Normalized',
                    'normalize_callback' => function ($value) { return strtoupper($value); },
                ),
            ),
        )
    );
    $GLOBALS['wp_seed_contract_registration_success'] = $registered;
    $GLOBALS['wp_seed_contract_placeholder_extension'] = wp_seed_content_kit_register_template_placeholders(
        'third_party',
        array('subtitle' => array('type' => 'text', 'label' => 'Subtitle'))
    );
    $GLOBALS['wp_seed_contract_placeholder_duplicate'] = wp_seed_content_kit_register_template_placeholders(
        'third_party',
        array('title' => array('type' => 'text', 'label' => 'Duplicate title'))
    );
    $GLOBALS['wp_seed_contract_bad_type'] = wp_seed_content_kit_register_template_module('bad_type', array(
        'label' => 'Bad type',
        'placeholders' => array('value' => array('type' => 'unknown')),
    ));
    $GLOBALS['wp_seed_contract_bad_asset_definition'] = wp_seed_content_kit_register_template_module('bad_asset_definition', array(
        'label' => 'Bad asset definition',
        'assets' => array('styles' => array('https://example.test/style.css')),
        'placeholders' => array('value' => array('type' => 'text')),
    ));
    $GLOBALS['wp_seed_contract_duplicate'] = wp_seed_content_kit_register_template_module('third_party', array('placeholders' => array()));
    $GLOBALS['wp_seed_contract_invalid_module'] = wp_seed_content_kit_register_template_module('Invalid Module', array('placeholders' => array()));
    $GLOBALS['wp_seed_contract_provider_error_module'] = wp_seed_content_kit_register_template_module('provider_error', array(
        'label' => 'Provider error',
        'provider' => function () { throw new RuntimeException('private provider detail'); },
        'placeholders' => array('title' => array('type' => 'text')),
    ));
    $GLOBALS['wp_seed_contract_invalid_assets_module'] = wp_seed_content_kit_register_template_module('invalid_assets', array(
        'label' => 'Invalid assets',
        'assets' => array('styles' => array('not-registered')),
        'placeholders' => array('title' => array('type' => 'text')),
    ));
    $GLOBALS['wp_seed_contract_second_module'] = wp_seed_content_kit_register_template_module('second_party', array(
        'label' => 'Second-party cards',
        'placeholders' => array('title' => array('type' => 'text', 'required' => true)),
    ));
});

wp_seed_content_kit_initialize_template_registry();
wp_seed_contract_true($GLOBALS['wp_seed_contract_registration_success'], 'third-party module registered');
wp_seed_contract_true($GLOBALS['wp_seed_contract_placeholder_extension'], 'placeholder extension registered');
wp_seed_contract_same(false, $GLOBALS['wp_seed_contract_placeholder_duplicate'], 'duplicate placeholder refused');
wp_seed_contract_same(false, $GLOBALS['wp_seed_contract_bad_type'], 'invalid placeholder type refused');
wp_seed_contract_same(false, $GLOBALS['wp_seed_contract_bad_asset_definition'], 'arbitrary asset definition refused');
wp_seed_contract_same(false, $GLOBALS['wp_seed_contract_duplicate'], 'duplicate module refused');
wp_seed_contract_same(false, $GLOBALS['wp_seed_contract_invalid_module'], 'invalid module refused');
wp_seed_contract_true($GLOBALS['wp_seed_contract_second_module'], 'second module registered');
wp_seed_contract_same(false, wp_seed_content_kit_register_template_placeholders('third_party', array('late' => array('type' => 'text'))), 'late placeholder refused');
wp_seed_contract_same(false, wp_seed_content_kit_register_template_module('late', array('placeholders' => array())), 'late module refused');

$module = wp_seed_content_kit_get_registered_template_module('third_party');
wp_seed_contract_same('Third-party cards', $module['label'], 'module label');
wp_seed_contract_same(array('native', 'divi_layout'), $module['render_types'], 'module render types');
wp_seed_contract_same(array('third-party-card'), $module['assets']['styles'], 'module assets deduplicated');
wp_seed_contract_same(10, count(wp_seed_content_kit_get_registered_template_placeholders('third_party')), 'placeholder registry count');

$template_content = '<article>{{title}}|{{summary}}|{{markup}}|{{website}}|{{email}}|{{phone}}|{{photo}}|{{tags}}|{{normalized}}</article>';
wp_seed_contract_add_template(101, 'card', 'third_party', 'publish', $template_content);
wp_seed_contract_add_template(102, 'draft-card', 'third_party', 'draft', $template_content);
wp_seed_contract_add_template(103, 'wrong-module', 'quotes', 'publish', $template_content);
wp_seed_contract_add_template(104, 'empty-card', 'third_party', 'publish', '');
wp_seed_contract_add_template(105, 'recursive-card', 'third_party', 'publish', '[[render:recursive-card]]');
wp_seed_contract_add_template(106, 'recursive-a', 'third_party', 'publish', 'A[[render:recursive-b]]');
wp_seed_contract_add_template(107, 'recursive-b', 'third_party', 'publish', 'B[[render:recursive-a]]');
wp_seed_contract_add_template(108, 'divi-card', 'third_party', 'publish', 'native');
wp_seed_contract_add_template(109, 'provider-error', 'provider_error', 'publish', '{{title}}');
wp_seed_contract_add_template(110, 'invalid-assets', 'invalid_assets', 'publish', '{{title}}');
wp_seed_contract_add_template(111, 'divi-fallback', 'third_party', 'publish', '<div class="native-fallback">{{title}}</div>');
wp_seed_contract_add_template(112, 'unknown-placeholder', 'third_party', 'publish', '{{unknown.value}}');
wp_seed_contract_add_template(113, 'nested-outer', 'third_party', 'publish', 'Outer [[render:second_party:nested-inner]]');
wp_seed_contract_add_template(114, 'nested-inner', 'second_party', 'publish', 'Inner {{title}}');
wp_seed_contract_add_template(115, 'cross-recursion-a', 'third_party', 'publish', 'A [[render:second_party:cross-recursion-b]]');
wp_seed_contract_add_template(116, 'cross-recursion-b', 'second_party', 'publish', 'B [[render:third_party:cross-recursion-a]]');
wp_seed_contract_add_template(117, 'pipeline-error', 'third_party', 'publish', '[[throw:pipeline]]');
wp_seed_contract_add_template(118, 'core-block', 'third_party', 'publish', '<!-- wp:paragraph --><p>{{title}}</p><!-- /wp:paragraph -->');
for ($depth = 1; $depth <= 9; $depth++) {
    $next = $depth < 9 ? '[[render:depth-' . ($depth + 1) . ']]' : 'Depth end';
    wp_seed_contract_add_template(120 + $depth, 'depth-' . $depth, 'third_party', 'publish', $next);
}
wp_seed_contract_add_template(201, 'divi-layout', '', 'publish', '<!-- wp:divi/text --><div>{{title}}</div><!-- /wp:divi/text -->');
$GLOBALS['wp_seed_contract_posts'][201]->post_type = 'et_pb_layout';
$GLOBALS['wp_seed_contract_meta'][108]['_wp_seed_content_template_source'] = 'divi_layout';
$GLOBALS['wp_seed_contract_meta'][108]['_wp_seed_content_divi_layout_id'] = 201;
$GLOBALS['wp_seed_contract_meta'][111]['_wp_seed_content_template_source'] = 'divi_layout';

$context = array(
    'title' => '<script>alert(1)</script>Title',
    'summary' => "Line one\nLine two",
    'markup' => '<strong>Allowed</strong><script>bad()</script><span onclick="bad()">Span</span>',
    'website' => 'https://example.test/path?x=1&y=2',
    'email' => 'person@example.test',
    'phone' => '+33 (0)1 23 45 67 ext BAD',
    'photo' => array('url' => 'https://example.test/photo.jpg', 'alt' => 'A "photo"'),
    'tags' => array('First', '<b>Second</b>'),
    'normalized' => 'mixed',
    'private_unknown' => 'MUST NOT PASS',
);
$GLOBALS['wp_seed_contract_render_context'] = $context;

$single_start = microtime(true);
$success = wp_seed_content_kit_render_template('card', 'third_party', $context);
$GLOBALS['wp_seed_contract_single_seconds'] = microtime(true) - $single_start;
wp_seed_contract_true($success instanceof WP_Seed_Content_Kit_Render_Result, 'typed result instance');
wp_seed_contract_true($success->is_success(), 'successful render');
wp_seed_contract_same('success', $success->get_code(), 'success code');
wp_seed_contract_same(101, $success->get_template_id(), 'success template id');
wp_seed_contract_contains('&lt;script&gt;', $success->get_html(), 'text escaped');
wp_seed_contract_contains('Line one<br', $success->get_html(), 'textarea line break');
wp_seed_contract_contains('<strong>Allowed</strong>', $success->get_html(), 'allowed HTML retained');
wp_seed_contract_not_contains('<script>', $success->get_html(), 'script removed from HTML');
wp_seed_contract_not_contains('onclick', $success->get_html(), 'inline event removed');
wp_seed_contract_contains('https://example.test/path', $success->get_html(), 'URL escaped');
wp_seed_contract_contains('person@example.test', $success->get_html(), 'email normalized');
wp_seed_contract_not_contains('ext BAD', $success->get_html(), 'telephone normalized');
wp_seed_contract_contains('<img src="https://example.test/photo.jpg"', $success->get_html(), 'image rendered');
wp_seed_contract_contains('alt="A &quot;photo&quot;"', $success->get_html(), 'image alt escaped');
wp_seed_contract_contains('First, &lt;b&gt;Second&lt;/b&gt;', $success->get_html(), 'text list escaped');
wp_seed_contract_contains('MIXED', $success->get_html(), 'normalizer applied');
wp_seed_contract_same(false, isset($GLOBALS['wp_seed_contract_provider_context']['private_unknown']), 'unknown context removed');
wp_seed_contract_same(array('styles' => array('third-party-card'), 'scripts' => array('third-party-interaction')), $success->get_assets(), 'result assets');
wp_seed_contract_true(isset($GLOBALS['wp_seed_contract_enqueued_styles']['third-party-card']), 'style enqueued');
wp_seed_contract_true(isset($GLOBALS['wp_seed_contract_enqueued_scripts']['third-party-interaction']), 'script enqueued');

wp_seed_contract_same('invalid_slug', wp_seed_content_kit_render_template('', 'third_party', $context)->get_code(), 'empty slug');
wp_seed_contract_same('invalid_slug', wp_seed_content_kit_render_template('Card Invalid', 'third_party', $context)->get_code(), 'noncanonical slug');
wp_seed_contract_same('template_not_found', wp_seed_content_kit_render_template('missing', 'third_party', $context)->get_code(), 'missing template');
$consumer_render = function ($slug, $fallback) use ($context) {
    $result = wp_seed_content_kit_render_template($slug, 'third_party', $context);
    return $result->is_success() ? $result->get_html() : $fallback;
};
wp_seed_contract_same('<article>Consumer fallback</article>', $consumer_render('missing', '<article>Consumer fallback</article>'), 'consumer owns fallback after API failure');
wp_seed_contract_not_contains('Consumer fallback', $consumer_render('card', '<article>Consumer fallback</article>'), 'consumer fallback skipped after API success');
wp_seed_contract_same('template_not_found', wp_seed_content_kit_render_template('draft-card', 'third_party', $context)->get_code(), 'draft template refused');
wp_seed_contract_same('module_mismatch', wp_seed_content_kit_render_template('wrong-module', 'third_party', $context)->get_code(), 'module mismatch');
wp_seed_contract_same('empty_render', wp_seed_content_kit_render_template('empty-card', 'third_party', $context)->get_code(), 'empty render');
wp_seed_contract_same('invalid_context', wp_seed_content_kit_render_template('card', 'third_party', array_merge($context, array('title' => array())))->get_code(), 'wrong scalar type');
wp_seed_contract_same('invalid_context', wp_seed_content_kit_render_template('card', 'third_party', array_merge($context, array('title' => '')))->get_code(), 'required empty value');
wp_seed_contract_same('invalid_context', wp_seed_content_kit_render_template('card', 'third_party', array_merge($context, array('title' => 'blocked')))->get_code(), 'context validator');
wp_seed_contract_same('invalid_context', wp_seed_content_kit_render_template('card', 'third_party', array_merge($context, array('email' => 'invalid')))->get_code(), 'invalid email');
wp_seed_contract_same('invalid_context', wp_seed_content_kit_render_template('card', 'third_party', array_merge($context, array('photo' => array('url' => 'x', 'secret' => 'y'))))->get_code(), 'invalid image shape');
wp_seed_contract_same('invalid_context', wp_seed_content_kit_render_template('card', 'third_party', array_merge($context, array('tags' => 'not-list')))->get_code(), 'invalid text list');

$GLOBALS['wp_seed_contract_nested_codes'] = array();
$direct_recursion = wp_seed_content_kit_render_template('recursive-card', 'third_party', $context);
wp_seed_contract_true(in_array('recursion_detected', $GLOBALS['wp_seed_contract_nested_codes'], true), 'direct recursion detected');
wp_seed_contract_same('recursion_detected', $direct_recursion->get_code(), 'direct recursion propagated to outer result');
$GLOBALS['wp_seed_contract_nested_codes'] = array();
$indirect_recursion = wp_seed_content_kit_render_template('recursive-a', 'third_party', $context);
wp_seed_contract_true(in_array('recursion_detected', $GLOBALS['wp_seed_contract_nested_codes'], true), 'indirect recursion detected');
wp_seed_contract_same('recursion_detected', $indirect_recursion->get_code(), 'indirect recursion propagated to outer result');
wp_seed_contract_true(wp_seed_content_kit_render_template('card', 'third_party', $context)->is_success(), 'stack restored after recursion');
wp_seed_contract_same(array(), $GLOBALS['wp_seed_content_kit_template_render_stack'], 'render stack empty after recursion');

$nested = wp_seed_content_kit_render_template('nested-outer', 'third_party', $context);
wp_seed_contract_true($nested->is_success(), 'two different modules render while nested');
wp_seed_contract_contains('Outer Inner', $nested->get_html(), 'nested module output retained');
wp_seed_contract_same(array(), $GLOBALS['wp_seed_content_kit_template_render_stack'], 'render stack empty after nested success');

$GLOBALS['wp_seed_contract_nested_codes'] = array();
$cross_recursion = wp_seed_content_kit_render_template('cross-recursion-a', 'third_party', $context);
wp_seed_contract_same('recursion_detected', $cross_recursion->get_code(), 'cross-module recursion propagated');
wp_seed_contract_same(array(), $GLOBALS['wp_seed_content_kit_template_render_stack'], 'render stack empty after cross-module recursion');

$GLOBALS['wp_seed_contract_nested_codes'] = array();
$maximum_depth = wp_seed_content_kit_render_template('depth-1', 'third_party', $context);
wp_seed_contract_same('recursion_detected', $maximum_depth->get_code(), 'maximum render depth enforced');
wp_seed_contract_same(array(), $GLOBALS['wp_seed_content_kit_template_render_stack'], 'render stack empty after maximum depth');

$divi = wp_seed_content_kit_render_template('divi-card', 'third_party', $context);
wp_seed_contract_true($divi->is_success(), 'Divi Library render');
wp_seed_contract_contains('<div>', $divi->get_html(), 'Divi layout HTML');
wp_seed_contract_not_contains('{{title}}', $divi->get_html(), 'Divi placeholder replaced');
$divi_fallback = wp_seed_content_kit_render_template('divi-fallback', 'third_party', $context);
wp_seed_contract_true($divi_fallback->is_success(), 'missing Divi layout uses native Template source');
wp_seed_contract_contains('native-fallback', $divi_fallback->get_html(), 'native Template fallback rendered');
wp_seed_contract_same('invalid_context', wp_seed_content_kit_render_template('unknown-placeholder', 'third_party', $context)->get_code(), 'unknown Template placeholder refused');


$failed_assets_before = count($GLOBALS['wp_seed_contract_enqueued_styles']);
$provider_error = wp_seed_content_kit_render_template('provider-error', 'provider_error', array('title' => 'Safe'));
wp_seed_contract_same('provider_error', $provider_error->get_code(), 'provider exception controlled');
wp_seed_contract_not_contains('private provider detail', $provider_error->get_html(), 'provider exception not public');
wp_seed_contract_same(array(), $GLOBALS['wp_seed_content_kit_template_render_stack'], 'render stack empty after provider exception');
$pipeline_error = wp_seed_content_kit_render_template('pipeline-error', 'third_party', $context);
wp_seed_contract_same('provider_error', $pipeline_error->get_code(), 'pipeline exception controlled');
wp_seed_contract_not_contains('private pipeline detail', $pipeline_error->get_html(), 'pipeline exception not public');
wp_seed_contract_same(array(), $GLOBALS['wp_seed_content_kit_template_render_stack'], 'render stack empty after pipeline exception');
wp_seed_contract_true(wp_seed_content_kit_render_template('card', 'third_party', $context)->is_success(), 'render succeeds after exception cleanup');
$core_block = wp_seed_content_kit_render_template('core-block', 'third_party', $context);
wp_seed_contract_true($core_block->is_success(), 'Core block Template renders without builder dependency');
wp_seed_contract_not_contains('{{title}}', $core_block->get_html(), 'Core block placeholder replaced');
$invalid_assets = wp_seed_content_kit_render_template('invalid-assets', 'invalid_assets', array('title' => 'Safe'));
wp_seed_contract_same('invalid_assets', $invalid_assets->get_code(), 'unknown asset refused');
wp_seed_contract_same($failed_assets_before, count($GLOBALS['wp_seed_contract_enqueued_styles']), 'no asset after failure');

$GLOBALS['wp_seed_contract_template_queries'] = 0;
$start = microtime(true);
for ($index = 0; $index < 14; $index++) {
    wp_seed_content_kit_render_template('card', 'third_party', $context);
}
$GLOBALS['wp_seed_contract_performance_seconds'] = microtime(true) - $start;
wp_seed_contract_true($GLOBALS['wp_seed_contract_performance_seconds'] < 1.0, 'fourteen renders stay bounded');
wp_seed_contract_true($GLOBALS['wp_seed_contract_template_queries'] <= 1, 'template resolution reused for fourteen renders');

echo 'PERF single=' . number_format($GLOBALS['wp_seed_contract_single_seconds'], 6, '.', '')
    . 's fourteen=' . number_format($GLOBALS['wp_seed_contract_performance_seconds'], 6, '.', '')
    . 's repeated_resolutions=' . $GLOBALS['wp_seed_contract_template_queries'] . PHP_EOL;
wp_seed_contract_finish('template extension');
