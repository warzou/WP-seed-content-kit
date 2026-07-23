<?php

$GLOBALS['wp_seed_consumer_assertions'] = 0;
$GLOBALS['wp_seed_consumer_failures'] = array();

function wp_seed_consumer_same($expected, $actual, $message)
{
    $GLOBALS['wp_seed_consumer_assertions']++;
    if ($expected !== $actual) {
        $GLOBALS['wp_seed_consumer_failures'][] = $message;
    }
}

function wp_seed_consumer_render_or_fallback($fallback)
{
    if (
        !function_exists('wp_seed_content_kit_supports')
        || !wp_seed_content_kit_supports('template_extension', '1.0')
        || !function_exists('wp_seed_content_kit_render_template')
    ) {
        return $fallback;
    }

    return 'API';
}

wp_seed_consumer_same(false, function_exists('wp_seed_content_kit_render_template'), 'Template API absent in isolated consumer');
wp_seed_consumer_same('<article>Fallback</article>', wp_seed_consumer_render_or_fallback('<article>Fallback</article>'), 'consumer fallback used when API is absent');

if (!empty($GLOBALS['wp_seed_consumer_failures'])) {
    fwrite(STDERR, 'FAIL ' . count($GLOBALS['wp_seed_consumer_failures']) . ' / ' . $GLOBALS['wp_seed_consumer_assertions'] . PHP_EOL);
    exit(1);
}

echo 'PASS ' . $GLOBALS['wp_seed_consumer_assertions'] . ' absent API consumer assertions' . PHP_EOL;
