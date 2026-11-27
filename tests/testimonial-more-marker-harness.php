<?php

define('ABSPATH', __DIR__ . '/');

function sanitize_textarea_field($value)
{
    return strip_tags((string) $value);
}

function wp_kses_post($value)
{
    return preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', (string) $value);
}

require_once __DIR__ . '/../plugin/includes/core/content-data.php';
require_once __DIR__ . '/../plugin/includes/modules/testimonials/builder-meta.php';

$assertions = 0;
$failures = array();

function testimonial_more_same($expected, $actual, $label)
{
    global $assertions, $failures;
    $assertions++;
    if ($expected !== $actual) {
        $failures[] = $label;
    }
}

$cases = array(
    'empty' => array('', array('full' => '', 'intro' => '', 'more' => '', 'has_more' => false)),
    'none' => array('Texte complet.', array('full' => 'Texte complet.', 'intro' => 'Texte complet.', 'more' => '', 'has_more' => false)),
    'simple' => array('Introduction.<!--more-->Suite.', array('full' => 'Introduction.Suite.', 'intro' => 'Introduction.', 'more' => 'Suite.', 'has_more' => true)),
    'custom' => array('Introduction.<!--more Lire la suite-->Suite.', array('full' => 'Introduction.Suite.', 'intro' => 'Introduction.', 'more' => 'Suite.', 'has_more' => true)),
    'multiple' => array('A<!--more-->B<!--more Encore-->C', array('full' => 'ABC', 'intro' => 'A', 'more' => 'BC', 'has_more' => true)),
    'noteaser' => array('A<!--more--><!--noteaser-->B', array('full' => 'AB', 'intro' => 'A', 'more' => 'B', 'has_more' => true)),
    'gutenberg' => array(
        '<!-- wp:paragraph --><p>A</p><!-- /wp:paragraph --><!-- wp:more --><!--more--><!-- /wp:more --><!-- wp:paragraph --><p>B</p><!-- /wp:paragraph -->',
        array(
            'full' => '<!-- wp:paragraph --><p>A</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>B</p><!-- /wp:paragraph -->',
            'intro' => '<!-- wp:paragraph --><p>A</p><!-- /wp:paragraph -->',
            'more' => '<!-- wp:paragraph --><p>B</p><!-- /wp:paragraph -->',
            'has_more' => true,
        )
    ),
);

foreach ($cases as $name => $case) {
    testimonial_more_same($case[1], wp_seed_content_split_wordpress_more($case[0]), $name);
}

$sanitized = wp_seed_content_sanitize_testimonial_text_meta(
    '<p>Introduction</p><!--more Continuer--><script>alert(1)</script><p>Suite</p>'
);
testimonial_more_same(
    '<p>Introduction</p><!--more Continuer--><p>Suite</p>',
    $sanitized,
    'sanitizer preserves More and strips unsafe HTML'
);
testimonial_more_same(
    'seed_testimonial_text',
    array_keys(wp_seed_content_testimonial_builder_meta_definitions())[0],
    'canonical storage remains seed_testimonial_text'
);
testimonial_more_same(
    '_seed_testimonial_text',
    wp_seed_content_testimonial_builder_meta_definitions()['seed_testimonial_text']['legacy_key'],
    'legacy storage remains fallback only'
);

$dynamic_data_source = file_get_contents(__DIR__ . '/../plugin/includes/core/dynamic-data.php');
$bindings_source = file_get_contents(__DIR__ . '/../plugin/includes/integrations/gutenberg/block-bindings.php');
$divi_source = file_get_contents(__DIR__ . '/../plugin/includes/integrations/divi/class-dynamic-content-testimonial-loop-fields.php');
testimonial_more_same(true, false !== strpos($dynamic_data_source, "'testimonial.intro'") && false !== strpos($dynamic_data_source, "'testimonial.more'") && false !== strpos($dynamic_data_source, "'testimonial.has_more'"), 'Content Data fields');
testimonial_more_same(
    true,
    false !== strpos($bindings_source, "'testimonial.intro' => 'text'")
        && false !== strpos($bindings_source, "'testimonial.more' => 'text'"),
    'Block Bindings allow intro and more'
);
testimonial_more_same(true, false !== strpos($divi_source, "loop_wpsck_testimonial_intro") && false !== strpos($divi_source, "loop_wpsck_testimonial_more"), 'Divi providers');

if ($failures) {
    fwrite(STDERR, 'FAIL ' . count($failures) . '/' . $assertions . ': ' . implode(', ', $failures) . PHP_EOL);
    exit(1);
}

echo 'PASS ' . $assertions . ' Testimonial More marker assertions' . PHP_EOL;
