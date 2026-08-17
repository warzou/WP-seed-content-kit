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
$structural_css = file_get_contents($root . '/plugin/assets/css/native-loop-structural.css');
$bootstrap = file_get_contents($root . '/plugin/wp-seed-content-kit.php');
$assets = file_get_contents($root . '/plugin/includes/integrations/divi/testimonial-native-loop-assets.php');
$structural_assets = file_get_contents($root . '/plugin/includes/integrations/divi/native-loop-structural-assets.php');
$context = file_get_contents($root . '/plugin/includes/integrations/divi/loop-context.php');

wpsck_loop_render_assert(false !== strpos($structural_css, '.wpsck-testimonial-loop--alternating:nth-child(odd of .wpsck-testimonial-loop--alternating)'), 'Legacy testimonial odd clone selector is missing.');
wpsck_loop_render_assert(false !== strpos($structural_css, '.wpsck-testimonial-loop--alternating:nth-child(even of .wpsck-testimonial-loop--alternating)'), 'Legacy testimonial even clone selector is missing.');
wpsck_loop_render_assert(false !== strpos($structural_css, '.wpsck-loop--alternating:nth-child(odd of .wpsck-loop--alternating)'), 'Shared odd Loop clone selector is missing.');
wpsck_loop_render_assert(false !== strpos($structural_css, '.wpsck-loop--alternating:nth-child(even of .wpsck-loop--alternating)'), 'Shared even Loop clone selector is missing.');
wpsck_loop_render_assert(false === strpos($structural_css, '.wpsck-testimonial-loop--alternating:nth-child(odd)'), 'Legacy testimonial selector must not count Divi overlay siblings.');
wpsck_loop_render_assert(false === strpos($structural_css, '.wpsck-testimonial-loop--alternating:nth-child(even)'), 'Legacy testimonial selector must not count Divi overlay siblings.');
wpsck_loop_render_assert(false === strpos($structural_css, '.wpsck-loop--alternating:nth-child(odd)'), 'Shared Loop selector must not count Divi overlay siblings.');
wpsck_loop_render_assert(false === strpos($structural_css, '.wpsck-loop--alternating:nth-child(even)'), 'Shared Loop selector must not count Divi overlay siblings.');
wpsck_loop_render_assert(false !== strpos($structural_css, '> .wpsck-loop__layout > .wpsck-loop__media'), 'Nested Loop media selector is missing.');
wpsck_loop_render_assert(false !== strpos($structural_css, '> .wpsck-loop__layout > .wpsck-loop__content'), 'Nested Loop content selector is missing.');
wpsck_loop_render_assert(false !== strpos($css, 'scroll-margin-top: 7rem'), 'Sticky header anchor offset is missing.');
wpsck_loop_render_assert(false !== strpos($structural_css, '> .wpsck-testimonial-loop__media'), 'Direct media column selector is missing.');
wpsck_loop_render_assert(false !== strpos($structural_css, '> .wpsck-testimonial-loop__content'), 'Direct content column selector is missing.');
wpsck_loop_render_assert(3 === substr_count($structural_css, 'order: 1;'), 'Media-first orders differ.');
wpsck_loop_render_assert(3 === substr_count($structural_css, 'order: 2;'), 'Content-second orders differ.');
wpsck_loop_render_assert(false !== strpos($structural_css, '@media (min-width: 981px)'), 'Desktop breakpoint is missing.');
wpsck_loop_render_assert(false !== strpos($structural_css, '@media (max-width: 980px)'), 'Narrow breakpoint is missing.');
wpsck_loop_render_assert(false !== strpos($structural_css, 'flex-direction: column !important'), 'Narrow layout does not stack the Loop row.');
wpsck_loop_render_assert(false !== strpos($structural_css, 'flex-wrap: nowrap !important'), 'Narrow layout does not keep a deterministic column stack.');
wpsck_loop_render_assert(false !== strpos($structural_css, 'width: 100% !important'), 'Narrow columns do not fill the available width.');
wpsck_loop_render_assert(false !== strpos($structural_css, 'max-width: 100% !important'), 'Inherited Divi column max-width is not neutralized.');
wpsck_loop_render_assert(false !== strpos($structural_css, 'min-width: 0 !important'), 'Narrow columns are not protected from intrinsic overflow.');
wpsck_loop_render_assert(false !== strpos($structural_css, 'flex: 0 0 100% !important'), 'Inherited Divi flex sizing is not neutralized.');
wpsck_loop_render_assert(false !== strpos($structural_css, 'grid-column: 1 / -1 !important'), 'Desktop grid placement is not neutralized on narrow screens.');
wpsck_loop_render_assert(false === strpos($structural_css, 'row-reverse'), 'Alternation must not change semantic DOM order.');
wpsck_loop_render_assert(false === strpos($structural_css, 'font-'), 'Structural CSS must not set typography.');
wpsck_loop_render_assert(false === strpos($structural_css, 'color:'), 'Structural CSS must not set colors.');
wpsck_loop_render_assert(false === strpos($structural_css, 'background'), 'Structural CSS must not set backgrounds.');
wpsck_loop_render_assert(false === strpos($structural_css, 'border'), 'Structural CSS must not set borders.');
wpsck_loop_render_assert(false !== strpos($assets, 'enqueue_app_window'), 'Visual Builder stylesheet registration is missing.');
wpsck_loop_render_assert(false !== strpos($assets, "add_action('wp_enqueue_scripts'"), 'Frontend stylesheet enqueue is missing.');
wpsck_loop_render_assert(false !== strpos($assets, "hash_file('sha256'"), 'Structural stylesheet URL is not content-versioned.');
wpsck_loop_render_assert(false !== strpos($assets, 'substr($hash, 0, 12)'), 'Structural stylesheet hash is not bounded.');
wpsck_loop_render_assert(3 === substr_count($assets, 'wp_seed_content_testimonial_native_loop_style_version()'), 'Frontend and Builder must share the content-based stylesheet version.');
wpsck_loop_render_assert(false !== strpos($bootstrap, 'testimonial-native-loop-assets.php'), 'Native Loop assets are not bootstrapped.');
wpsck_loop_render_assert(false !== strpos($bootstrap, 'native-loop-structural-assets.php'), 'Shared structural assets are not bootstrapped.');
wpsck_loop_render_assert(false !== strpos($structural_assets, 'enqueue_app_window'), 'Shared Visual Builder stylesheet registration is missing.');
wpsck_loop_render_assert(false !== strpos($structural_assets, "add_action('wp_enqueue_scripts'"), 'Shared frontend stylesheet enqueue is missing.');
wpsck_loop_render_assert(false !== strpos($structural_assets, "hash_file('sha256'"), 'Shared structural stylesheet URL is not content-versioned.');

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
        'Generic Visual Builder query item key is missing: ' . $source
    );
}
wpsck_loop_render_assert(false === strpos($context, 'wp_seed_content_testimonial_anchor_url'), 'Removed URL provider leaked into query results.');
wpsck_loop_render_assert(false === strpos($assets, 'MutationObserver'), 'DOM observer workaround is forbidden.');
wpsck_loop_render_assert(false === strpos($assets, '<script'), 'Inline script workaround is forbidden.');
wpsck_loop_render_assert(false === strpos($structural_assets, 'MutationObserver'), 'Shared DOM observer workaround is forbidden.');
wpsck_loop_render_assert(false === strpos($structural_assets, '<script'), 'Shared inline script workaround is forbidden.');

$builder_states = array(
    'frontend' => array('clone', 'clone', 'clone'),
    'builder_no_hover' => array('clone', 'clone', 'clone'),
    'builder_row_hover' => array('overlay', 'clone', 'overlay', 'clone', 'overlay', 'clone'),
    'builder_row_selected' => array('portal', 'overlay', 'clone selected', 'overlay', 'clone', 'overlay', 'clone'),
    'builder_child_selected' => array('portal', 'clone child-selected', 'overlay', 'clone', 'overlay', 'clone'),
);
foreach ($builder_states as $state => $siblings) {
    $positions = array();
    $raw_positions = array();
    foreach ($siblings as $raw_index => $sibling) {
        if (0 === strpos($sibling, 'clone')) {
            $positions[] = count($positions) + 1;
            $raw_positions[] = $raw_index + 1;
        }
    }
    wpsck_loop_render_assert(array(1, 2, 3) === $positions, 'Filtered clone parity changed in state: ' . $state);
    if (0 === strpos($state, 'builder_') && 'builder_no_hover' !== $state) {
        wpsck_loop_render_assert($raw_positions !== $positions, 'Builder overlays did not reproduce raw nth-child instability: ' . $state);
    }
}

if ($failures) {
    fwrite(STDERR, 'FAIL ' . count($failures) . '/' . $assertions . ': ' . implode(', ', $failures) . PHP_EOL);
    exit(1);
}

echo 'Testimonial Native Loop render harness: ' . $assertions . ' assertions OK' . PHP_EOL;
