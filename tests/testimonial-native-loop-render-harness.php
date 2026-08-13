<?php

declare(strict_types=1);

$assertions = 0;
$failures = array();

function wpsck_loop_render_assert($condition, $message)
{
    global $assertions, $failures;
    $assertions++;
    if (!$condition) {
        $failures[] = $message;
    }
}

$root = dirname(__DIR__);
$css = file_get_contents($root . '/plugin/assets/css/testimonial-native-loop.css');
$bootstrap = file_get_contents($root . '/plugin/wp-seed-content-kit.php');
$assets = file_get_contents($root . '/plugin/includes/integrations/divi/testimonial-native-loop-assets.php');
$context = file_get_contents($root . '/plugin/includes/integrations/divi/testimonial-loop-context.php');

wpsck_loop_render_assert(false !== strpos($css, '.wpsck-testimonial-loop--alternating:nth-child(odd)'), 'Odd row selector is missing.');
wpsck_loop_render_assert(false !== strpos($css, '.wpsck-testimonial-loop--alternating:nth-child(even)'), 'Even row selector is missing.');
wpsck_loop_render_assert(false !== strpos($css, 'scroll-margin-top: 7rem'), 'Sticky header anchor offset is missing.');
wpsck_loop_render_assert(false !== strpos($css, '> .wpsck-testimonial-loop__media'), 'Direct media column selector is missing.');
wpsck_loop_render_assert(false !== strpos($css, '> .wpsck-testimonial-loop__content'), 'Direct content column selector is missing.');
wpsck_loop_render_assert(3 === substr_count($css, 'order: 1;'), 'Media-first orders differ.');
wpsck_loop_render_assert(3 === substr_count($css, 'order: 2;'), 'Content-second orders differ.');
wpsck_loop_render_assert(false !== strpos($css, '@media (min-width: 981px)'), 'Desktop breakpoint is missing.');
wpsck_loop_render_assert(false !== strpos($css, '@media (max-width: 980px)'), 'Narrow breakpoint is missing.');
wpsck_loop_render_assert(false !== strpos($css, 'flex-direction: column !important'), 'Narrow layout does not stack the Loop row.');
wpsck_loop_render_assert(false !== strpos($css, 'flex-wrap: nowrap !important'), 'Narrow layout does not keep a deterministic column stack.');
wpsck_loop_render_assert(false !== strpos($css, 'width: 100% !important'), 'Narrow columns do not fill the available width.');
wpsck_loop_render_assert(false !== strpos($css, 'max-width: 100% !important'), 'Inherited Divi column max-width is not neutralized.');
wpsck_loop_render_assert(false !== strpos($css, 'min-width: 0 !important'), 'Narrow columns are not protected from intrinsic overflow.');
wpsck_loop_render_assert(false !== strpos($css, 'flex: 0 0 100% !important'), 'Inherited Divi 50/50 flex sizing is not neutralized.');
wpsck_loop_render_assert(false !== strpos($css, 'grid-column: 1 / -1 !important'), 'Desktop grid placement is not neutralized on narrow screens.');
wpsck_loop_render_assert(false === strpos($css, 'font-'), 'Structural CSS must not set typography.');
wpsck_loop_render_assert(false === strpos($css, 'color:'), 'Structural CSS must not set colors.');
wpsck_loop_render_assert(false === strpos($css, 'background'), 'Structural CSS must not set backgrounds.');
wpsck_loop_render_assert(false === strpos($css, 'border'), 'Structural CSS must not set borders.');
wpsck_loop_render_assert(false !== strpos($assets, 'enqueue_app_window'), 'Visual Builder stylesheet registration is missing.');
wpsck_loop_render_assert(false !== strpos($assets, "add_action('wp_enqueue_scripts'"), 'Frontend stylesheet enqueue is missing.');
wpsck_loop_render_assert(false !== strpos($assets, "hash_file('sha256'"), 'Structural stylesheet URL is not content-versioned.');
wpsck_loop_render_assert(false !== strpos($assets, 'substr($hash, 0, 12)'), 'Structural stylesheet hash is not bounded.');
wpsck_loop_render_assert(3 === substr_count($assets, 'wp_seed_content_testimonial_native_loop_style_version()'), 'Frontend and Builder must share the content-based stylesheet version.');
wpsck_loop_render_assert(false !== strpos($bootstrap, 'testimonial-native-loop-assets.php'), 'Native Loop assets are not bootstrapped.');

$sources = array(
    'visual',
    'title',
    'summary',
    'full',
    'name',
    'context',
    'date',
    'id',
    'anchor',
);
foreach ($sources as $source) {
    wpsck_loop_render_assert(
        false !== strpos($context, "'wpsck_testimonial_" . $source . "'"),
        'Visual Builder query item key is missing: ' . $source
    );
}
wpsck_loop_render_assert(false === strpos($context, 'wp_seed_content_testimonial_anchor_url'), 'Removed URL provider leaked into query results.');
wpsck_loop_render_assert(false === strpos($assets, 'MutationObserver'), 'DOM observer workaround is forbidden.');
wpsck_loop_render_assert(false === strpos($assets, '<script'), 'Inline script workaround is forbidden.');

if ($failures) {
    fwrite(STDERR, 'FAIL ' . count($failures) . '/' . $assertions . ': ' . implode(', ', $failures) . PHP_EOL);
    exit(1);
}

echo 'Testimonial Native Loop render harness: ' . $assertions . ' assertions OK' . PHP_EOL;
