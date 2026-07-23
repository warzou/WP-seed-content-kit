<?php

define('ABSPATH', __DIR__ . '/');
define('WP_SEED_CONTENT_KIT_URL', 'https://example.test/plugin/');
define('WP_SEED_CONTENT_KIT_VERSION', '0.6.0-rc.4');

$GLOBALS['seed_admin_usage_assertions'] = 0;
$GLOBALS['seed_admin_usage_failures'] = array();
$GLOBALS['seed_admin_usage_actions'] = array();
$GLOBALS['seed_admin_usage_styles'] = array();
$GLOBALS['seed_admin_usage_scripts'] = array();
$GLOBALS['seed_admin_usage_can_manage'] = true;
$GLOBALS['seed_admin_usage_generators_rendered'] = 0;
$GLOBALS['wp_seed_content_kit_usage_page_hook'] = 'content-kit_page_usage';

function seed_admin_usage_assert($condition, $label)
{
    $GLOBALS['seed_admin_usage_assertions']++;
    if (!$condition) {
        $GLOBALS['seed_admin_usage_failures'][] = $label;
    }
}

function seed_admin_usage_same($expected, $actual, $label)
{
    seed_admin_usage_assert($expected === $actual, $label);
}

function __($text, $domain = null)
{
    return $text;
}

function esc_html__($text, $domain = null)
{
    return $text;
}

function esc_attr__($text, $domain = null)
{
    return $text;
}

function esc_html_e($text, $domain = null)
{
    echo $text;
}

function esc_attr_e($text, $domain = null)
{
    echo $text;
}

function esc_html($text)
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

function esc_attr($text)
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

function esc_url($url)
{
    return (string) $url;
}

function sanitize_key($value)
{
    return strtolower(preg_replace('/[^a-z0-9_-]/', '', (string) $value));
}

function sanitize_html_class($value)
{
    return sanitize_key($value);
}

function wp_unslash($value)
{
    return $value;
}

function admin_url($path = '')
{
    return 'https://example.test/wp-admin/' . ltrim($path, '/');
}

function add_query_arg($args, $url)
{
    return $url . '?' . http_build_query($args);
}

function add_action($hook, $callback, $priority = 10, $accepted_args = 1)
{
    $GLOBALS['seed_admin_usage_actions'][$hook][] = array($callback, $priority, $accepted_args);
}

function wp_enqueue_style($handle, $src, $dependencies, $version)
{
    $GLOBALS['seed_admin_usage_styles'][$handle] = array($src, $dependencies, $version);
}

function wp_enqueue_script($handle, $src, $dependencies, $version, $footer)
{
    $GLOBALS['seed_admin_usage_scripts'][$handle] = array($src, $dependencies, $version, $footer);
}

function current_user_can($capability)
{
    return 'manage_wp_seed_integrations' === $capability && !empty($GLOBALS['seed_admin_usage_can_manage']);
}

function wp_die($message)
{
    $GLOBALS['seed_admin_usage_died'] = $message;
}

function wp_seed_content_kit_render_generators_tab()
{
    $GLOBALS['seed_admin_usage_generators_rendered']++;
    echo '<div id="historical-generators"></div>';
}

require dirname(__DIR__) . '/plugin/includes/admin/usage-page.php';

seed_admin_usage_same(
    array('functioning', 'templates', 'collections', 'integrations'),
    array_keys(wp_seed_content_kit_get_usage_tabs()),
    'Four usage tabs keep their canonical order'
);
seed_admin_usage_same(
    array('shortcodes', 'gutenberg', 'spectra', 'divi'),
    array_keys(wp_seed_content_kit_get_usage_integration_tabs()),
    'Four integration tabs keep their canonical order'
);
seed_admin_usage_same('Fonctionnel', wp_seed_content_kit_get_usage_status_label('functional'), 'Functional status label');
seed_admin_usage_same('Indirect', wp_seed_content_kit_get_usage_status_label('indirect'), 'Indirect status label');
seed_admin_usage_same('Expérimental', wp_seed_content_kit_get_usage_status_label('experimental'), 'Experimental status label');
seed_admin_usage_same('Non disponible', wp_seed_content_kit_get_usage_status_label('unavailable'), 'Unavailable status label');

$_GET = array('usage_tab' => 'invalid', 'integration' => 'invalid');
seed_admin_usage_same('functioning', wp_seed_content_kit_get_current_usage_tab(), 'Invalid usage tab falls back safely');
seed_admin_usage_same('shortcodes', wp_seed_content_kit_get_current_usage_integration_tab(), 'Invalid integration tab falls back safely');
seed_admin_usage_assert(false !== strpos(wp_seed_content_kit_get_usage_url('integrations', 'divi'), 'integration=divi'), 'Usage URL preserves integration');
wp_seed_content_kit_enqueue_usage_assets('other_page');
seed_admin_usage_same(array(), $GLOBALS['seed_admin_usage_styles'], 'Assets stay scoped to Usage');
wp_seed_content_kit_enqueue_usage_assets('content-kit_page_usage');
seed_admin_usage_assert(isset($GLOBALS['seed_admin_usage_styles']['wp-seed-content-kit-admin-usage']), 'Usage stylesheet enqueued');
seed_admin_usage_assert(isset($GLOBALS['seed_admin_usage_scripts']['wp-seed-content-kit-admin-usage']), 'Usage script enqueued');
seed_admin_usage_same('0.6.0-rc.4', $GLOBALS['seed_admin_usage_styles']['wp-seed-content-kit-admin-usage'][2], 'Usage assets use plugin version');

ob_start();
wp_seed_content_kit_render_usage_tabs('collections');
$tabs_html = ob_get_clean();
seed_admin_usage_same(4, substr_count($tabs_html, '<a class="nav-tab'), 'Four horizontal tabs rendered');
seed_admin_usage_same(1, substr_count($tabs_html, 'aria-current="page"'), 'Current usage tab exposed accessibly');

ob_start();
wp_seed_content_kit_render_usage_integration_tabs('gutenberg');
$subtabs_html = ob_get_clean();
seed_admin_usage_same(4, substr_count($subtabs_html, 'class="button'), 'Four internal integration tabs rendered');
seed_admin_usage_same(1, substr_count($subtabs_html, 'aria-current="page"'), 'Current integration tab exposed accessibly');
ob_start();
wp_seed_content_kit_render_usage_functioning();
$functioning_html = ob_get_clean();
foreach (array('Contenus', 'Collections', 'Templates', 'Intégrations', 'Annuaire', 'Témoignages', 'Citations') as $needle) {
    seed_admin_usage_assert(false !== strpos($functioning_html, $needle), 'Functioning explains ' . $needle);
}

$placeholders = wp_seed_content_kit_get_usage_template_placeholders();
seed_admin_usage_same(3, count($placeholders), 'Three Template modules documented');
seed_admin_usage_same(15, count($placeholders['Annuaire']), 'Fifteen Directory placeholders documented');
seed_admin_usage_assert(in_array('directory.phone', $placeholders['Annuaire'], true), 'Directory phone placeholder documented');

ob_start();
wp_seed_content_kit_render_usage_templates();
$templates_html = ob_get_clean();
seed_admin_usage_assert(false !== strpos($templates_html, 'Gérer les Templates'), 'Template management link rendered');
seed_admin_usage_assert(false !== strpos($templates_html, 'Créer un Template'), 'Template creation link rendered');
seed_admin_usage_assert(false !== strpos($templates_html, 'rendu natif'), 'Native Template fallback explained');
seed_admin_usage_assert(false !== strpos($templates_html, 'Divi Library'), 'Divi Template source explained');
ob_start();
wp_seed_content_kit_render_usage_collections();
$collections_html = ob_get_clean();
seed_admin_usage_assert(false !== strpos($collections_html, 'Une Collection définit quels contenus afficher et dans quel ordre.'), 'Collection definition is exact');
seed_admin_usage_assert(false !== strpos($collections_html, 'n’est pas un contenu enregistré'), 'Collections are explicitly non-persistent');
seed_admin_usage_assert(false !== strpos($collections_html, 'historical-generators'), 'Historical generators remain available');
seed_admin_usage_assert(false !== strpos($collections_html, 'data-seed-usage-generator'), 'Directory generator rendered');
seed_admin_usage_same(1, $GLOBALS['seed_admin_usage_generators_rendered'], 'Historical generators rendered once');
foreach (array('status', 'featured', 'ids', 'limit', 'orderby', 'order') as $needle) {
    seed_admin_usage_assert(false !== strpos($collections_html, $needle), 'Collection parameter documented: ' . $needle);
}

ob_start();
wp_seed_content_kit_render_usage_shortcodes();
$shortcodes_html = ob_get_clean();
seed_admin_usage_assert(false !== strpos($shortcodes_html, '[seed_directory'), 'Canonical Directory shortcode example rendered');
seed_admin_usage_assert(false !== strpos($shortcodes_html, '[wp_seed_directory]'), 'Deprecated Directory alias disclosed');
seed_admin_usage_assert(false !== strpos($shortcodes_html, 'data-seed-usage-copy'), 'Shortcode examples are copyable');
seed_admin_usage_assert(false !== strpos($shortcodes_html, 'aria-live="polite"'), 'Copy feedback is accessible');
ob_start();
wp_seed_content_kit_render_usage_gutenberg();
$gutenberg_html = ob_get_clean();
seed_admin_usage_assert(false !== strpos($gutenberg_html, 'Fonctionnel'), 'Gutenberg Shortcode marked functional');
seed_admin_usage_assert(false !== strpos($gutenberg_html, 'Indirect'), 'Gutenberg Block Bindings marked indirect');
seed_admin_usage_assert(false !== strpos($gutenberg_html, 'Non disponible'), 'Missing Gutenberg UI disclosed');
seed_admin_usage_assert(false !== strpos($gutenberg_html, 'ne couvrent pas encore Annuaire'), 'Directory Block Bindings limitation disclosed');

ob_start();
wp_seed_content_kit_render_usage_spectra();
$spectra_html = ob_get_clean();
seed_admin_usage_assert(false !== strpos($spectra_html, 'Indirect'), 'Spectra marked indirect');
seed_admin_usage_assert(false !== strpos($spectra_html, 'Aucun provider'), 'No native Spectra provider claimed');
ob_start();
wp_seed_content_kit_render_usage_divi();
$divi_html = ob_get_clean();
foreach (array('Fonctionnel', 'Indirect', 'Expérimental', 'Non disponible') as $needle) {
    seed_admin_usage_assert(false !== strpos($divi_html, $needle), 'Divi state documented: ' . $needle);
}
seed_admin_usage_assert(false !== strpos($divi_html, 'ne couvrent pas Annuaire'), 'Divi Dynamic Content scope is accurate');
seed_admin_usage_assert(false !== strpos($divi_html, 'Aucun module Divi propriétaire'), 'No proprietary Divi module claimed');
seed_admin_usage_assert(false !== strpos($tabs_html, 'role="tablist"'), 'Primary navigation exposes a tablist');
seed_admin_usage_same(4, substr_count($tabs_html, 'role="tab"'), 'Primary navigation exposes four tabs');
seed_admin_usage_same(1, substr_count($tabs_html, 'aria-selected="true"'), 'Primary navigation exposes one selected tab');
seed_admin_usage_assert(false !== strpos($tabs_html, 'aria-controls="seed-usage-panel-collections"'), 'Primary tab controls its panel');
seed_admin_usage_assert(false !== strpos($subtabs_html, 'role="tablist"'), 'Integration navigation exposes a tablist');
seed_admin_usage_same(4, substr_count($subtabs_html, 'role="tab"'), 'Integration navigation exposes four tabs');
seed_admin_usage_same(1, substr_count($subtabs_html, 'aria-selected="true"'), 'Integration navigation exposes one selected tab');

$placeholder_catalog = wp_seed_content_kit_get_usage_template_placeholder_catalog();
seed_admin_usage_same(7, count($placeholder_catalog['Témoignages']), 'Seven Testimonial placeholders described');
seed_admin_usage_same(4, count($placeholder_catalog['Citations']), 'Four Quote placeholders described');
seed_admin_usage_same(15, count($placeholder_catalog['Annuaire']), 'Fifteen Directory placeholders described');
seed_admin_usage_same('directory.name', $placeholder_catalog['Annuaire'][0]['key'], 'Directory placeholder keys stay canonical');
seed_admin_usage_same('text', $placeholder_catalog['Annuaire'][0]['type'], 'Directory placeholder type documented');
seed_admin_usage_same('Publique si autorisée', $placeholder_catalog['Annuaire'][9]['visibility'], 'Directory contact visibility is conditional');
foreach ($placeholder_catalog as $definitions) {
    foreach ($definitions as $definition) {
        seed_admin_usage_assert(false === strpos($definition['key'], 'private'), 'No private placeholder exposed: ' . $definition['key']);
        seed_admin_usage_assert('' !== $definition['description'], 'Placeholder description provided: ' . $definition['key']);
        seed_admin_usage_assert('' !== $definition['empty'], 'Empty behavior provided: ' . $definition['key']);
    }
}
seed_admin_usage_same(26, substr_count($templates_html, 'data-seed-usage-copy-value='), 'Every placeholder has a copy action');
seed_admin_usage_assert(false !== strpos($templates_html, 'Un Template présente un contenu'), 'Template and selection stay separate');
seed_admin_usage_assert(false !== strpos($templates_html, 'sans association enregistrée'), 'No persistent Template Collection association');
seed_admin_usage_assert(false !== strpos($templates_html, 'Seules les données de rendu publiques'), 'Private fields excluded from placeholder catalog');

$collection_catalog = wp_seed_content_kit_get_usage_collection_catalog();
seed_admin_usage_same(3, count($collection_catalog), 'Three Collection modules documented');
seed_admin_usage_same(
    array('status', 'department', 'country', 'featured', 'ids', 'limit', 'orderby', 'order'),
    array_keys($collection_catalog['Annuaire']['parameters']),
    'Directory Collection parameters are exact'
);
seed_admin_usage_assert(false !== strpos($collections_html, 'Aucune recherche publique'), 'No public search promised');
seed_admin_usage_assert(false !== strpos($collections_html, 'aucun filtre public visible'), 'No visible public filters promised');
foreach (array('department', 'country', 'ids', 'template') as $attribute) {
    seed_admin_usage_assert(false !== strpos($collections_html, 'data-seed-usage-attr="' . $attribute . '"'), 'Directory generator supports ' . $attribute);
}
seed_admin_usage_assert(false !== strpos($collections_html, 'data-seed-usage-summary'), 'Directory generator provides a plain-language summary');
seed_admin_usage_assert(false !== strpos($collections_html, '[seed_testimonials'), 'Testimonial generator remains available');
seed_admin_usage_assert(false !== strpos($collections_html, '[seed_quotes'), 'Quote generator remains available');

$_GET = array('usage_tab' => 'integrations', 'integration' => 'gutenberg');
ob_start();
wp_seed_content_kit_render_usage_integrations();
$integrations_html = ob_get_clean();
seed_admin_usage_assert(false !== strpos($integrations_html, 'role="tabpanel"'), 'Integration content exposes a tab panel');
seed_admin_usage_assert(false !== strpos($integrations_html, 'aria-labelledby="seed-usage-integration-tab-gutenberg"'), 'Integration panel is labelled by the selected tab');
seed_admin_usage_assert(false !== strpos($gutenberg_html, 'Ajoutez un bloc Shortcode'), 'Gutenberg steps are concrete');
seed_admin_usage_assert(false !== strpos($spectra_html, 'Container Spectra'), 'Spectra indirect steps are concrete');
seed_admin_usage_assert(false !== strpos($divi_html, 'module Texte ou Code'), 'Divi shortcode steps are concrete');
$GLOBALS['seed_admin_usage_can_manage'] = false;
$GLOBALS['seed_admin_usage_died'] = '';
ob_start();
wp_seed_content_kit_render_usage_page();
ob_end_clean();
seed_admin_usage_assert('' !== $GLOBALS['seed_admin_usage_died'], 'Editor has no Usage access without the advanced integration capability');
$GLOBALS['seed_admin_usage_can_manage'] = true;
$root = dirname(__DIR__);
$usage_source = file_get_contents($root . '/plugin/includes/admin/usage-page.php');
$menu_source = file_get_contents($root . '/plugin/includes/admin/modules-page.php');
$bootstrap_source = file_get_contents($root . '/plugin/wp-seed-content-kit.php');
$css_source = file_get_contents($root . '/plugin/assets/css/admin-usage.css');
$js_source = file_get_contents($root . '/plugin/assets/admin-usage.js');

seed_admin_usage_assert(false === strpos($usage_source, 'register_post_type'), 'Usage creates no persistent content type');
seed_admin_usage_assert(false === strpos($usage_source, 'add_cap('), 'Usage grants no capability');
seed_admin_usage_assert(false === strpos($usage_source, 'remove_cap('), 'Usage removes no capability');
seed_admin_usage_assert(false === strpos($usage_source, 'register_rest_route'), 'Usage adds no REST route');
seed_admin_usage_assert(false === strpos($usage_source, 'wp_ajax_'), 'Usage adds no AJAX action');
seed_admin_usage_assert(false !== strpos($menu_source, "'wp-seed-content-kit-usage'"), 'Usage submenu registered');
seed_admin_usage_assert(false !== strpos($menu_source, "'manage_wp_seed_content_kit'"), 'Configuration uses its dedicated capability');
seed_admin_usage_assert(false !== strpos($menu_source, "'manage_wp_seed_integrations'"), 'Usage uses its dedicated capability');
seed_admin_usage_assert(false === strpos($menu_source, 'Aide / Documentation'), 'Minimal Help menu removed');
seed_admin_usage_assert(false !== strpos($bootstrap_source, '0.6.0-rc.4'), 'Release candidate version updated');
seed_admin_usage_assert(false !== strpos($bootstrap_source, 'usage-page.php'), 'Usage page loaded only in admin bootstrap');
seed_admin_usage_assert(false !== strpos($css_source, '@media screen and (max-width: 782px)'), 'Responsive admin layout included');
seed_admin_usage_assert(false !== strpos($css_source, ':focus-visible'), 'Visible keyboard focus included');
seed_admin_usage_assert(false !== strpos($css_source, 'content: attr(data-label)'), 'Responsive table labels included');
seed_admin_usage_assert(false !== strpos($js_source, 'ArrowLeft'), 'Arrow-key tab navigation included');
seed_admin_usage_assert(false !== strpos($js_source, 'data-seed-usage-copy-value'), 'Placeholder copy interaction included');
seed_admin_usage_assert(false !== strpos($js_source, 'data-seed-usage-summary'), 'Generator plain-language summary included');
seed_admin_usage_assert(false !== strpos($js_source, "addEventListener('click'"), 'Copy interaction is keyboard-triggerable');
seed_admin_usage_assert(false !== strpos($js_source, 'data-seed-usage-generator'), 'Directory generator updates locally');

if (!empty($GLOBALS['seed_admin_usage_failures'])) {
    fwrite(STDERR, 'FAIL ' . count($GLOBALS['seed_admin_usage_failures']) . ' / ' . $GLOBALS['seed_admin_usage_assertions'] . PHP_EOL);
    foreach ($GLOBALS['seed_admin_usage_failures'] as $failure) {
        fwrite(STDERR, '- ' . $failure . PHP_EOL);
    }
    exit(1);
}

echo 'PASS ' . $GLOBALS['seed_admin_usage_assertions'] . ' CK-A1 admin usage assertions' . PHP_EOL;
