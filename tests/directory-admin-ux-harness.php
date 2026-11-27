<?php

define('ABSPATH', __DIR__ . '/');

$assertions = 0;
$failures = array();

function directory_admin_ux_same($expected, $actual, $label)
{
    global $assertions, $failures;
    $assertions++;
    if ($expected !== $actual) {
        $failures[] = $label . ' got ' . var_export($actual, true);
    }
}

function __($value, $domain = null) { return $value; }
function add_action($hook, $callback, $priority = 10, $args = 1) {}
function add_filter($hook, $callback, $priority = 10, $args = 1) {}
function sanitize_key($value) { return strtolower(preg_replace('/[^a-z0-9_-]/', '', (string) $value)); }
function sanitize_text_field($value) { return trim(strip_tags((string) $value)); }
function sanitize_textarea_field($value) { return trim(strip_tags((string) $value)); }
function wp_unslash($value) { return $value; }
function wp_verify_nonce($nonce, $action) { return 'valid' === $nonce && 'wp_seed_content_directory_save' === $action; }
function wp_seed_content_directory_get_meta_definitions() { return array(); }

$root = dirname(__DIR__) . '/plugin/';
require $root . 'includes/modules/directory/validation.php';
require $root . 'includes/modules/directory/post-type.php';
require $root . 'includes/core/content-data.php';
require $root . 'includes/modules/directory/admin.php';

$original = array(
    'post_type' => 'seed_directory',
    'post_status' => 'draft',
    'post_title' => 'Marie-Odile Houver',
);

$_POST = array(
    'wp_seed_content_directory_nonce' => 'valid',
    'wp_seed_content_directory_name' => '  <b>Marie-Odile Houver Martin</b>  ',
);
$renamed = wp_seed_content_directory_filter_insert_post_data($original, array('ID' => 71));
directory_admin_ux_same('Marie-Odile Houver Martin', $renamed['post_title'], 'valid admin name updates canonical post title');
directory_admin_ux_same(false, isset($renamed['meta_input']), 'name save creates no parallel meta');

$_POST['wp_seed_content_directory_nonce'] = 'invalid';
$invalid_nonce = wp_seed_content_directory_filter_insert_post_data($original, array('ID' => 71));
directory_admin_ux_same('Marie-Odile Houver', $invalid_nonce['post_title'], 'invalid nonce preserves canonical title');

$_POST = array('wp_seed_content_directory_nonce' => 'valid');
$missing_field = wp_seed_content_directory_filter_insert_post_data($original, array('ID' => 71));
directory_admin_ux_same('Marie-Odile Houver', $missing_field['post_title'], 'partial save without name preserves title');
directory_admin_ux_same('wp_seed_content_directory_name', wp_seed_content_directory_get_error_field_ids()['missing_name'], 'missing name error targets canonical admin field');

$admin_source = file_get_contents($root . 'includes/modules/directory/admin.php');
directory_admin_ux_same(true, false !== strpos($admin_source, 'name="wp_seed_content_directory_name"'), 'editable person name field is rendered');
directory_admin_ux_same(true, false !== strpos($admin_source, '$post->post_title'), 'person name field reads canonical post title');
directory_admin_ux_same(true, false !== strpos($admin_source, 'Afficher dans l’annuaire des praticiens'), 'practitioner destination label is rendered');
directory_admin_ux_same(true, false !== strpos($admin_source, 'Afficher parmi les intervenants'), 'speaker destination label is rendered');
directory_admin_ux_same(true, false !== strpos($admin_source, 'name="_seed_directory_profile_types[]"'), 'multiple profile selection storage is preserved');
directory_admin_ux_same(true, false !== strpos($admin_source, 'Concernant la personne'), 'presentation editor has the requested admin landmark');
directory_admin_ux_same(true, false !== strpos($admin_source, 'Le statut est utilisé pour l’affichage dans l’annuaire des praticiens.'), 'status help explains practitioner scope');
directory_admin_ux_same(
    true,
    strpos($admin_source, "add_meta_box('wp_seed_content_directory_situation'") < strpos($admin_source, "add_meta_box('wp_seed_content_directory_profile'"),
    'location and presentation box is registered before display and ordering'
);
directory_admin_ux_same(true, false !== strpos($admin_source, "array_key_exists('_seed_directory_status', \$_POST)"), 'partial profile saves preserve an omitted status');

$directory_post = (object) array('post_type' => 'seed_directory', 'post_status' => 'auto-draft');
$existing_directory_post = (object) array('post_type' => 'seed_directory', 'post_status' => 'draft');
$other_post = (object) array('post_type' => 'post', 'post_status' => 'auto-draft');
$default_more = "<!-- wp:more -->\n<!--more-->\n<!-- /wp:more -->";
directory_admin_ux_same($default_more, wp_seed_content_directory_default_presentation('', $directory_post), 'new Directory presentation starts with one native More block');
directory_admin_ux_same('Existing content', wp_seed_content_directory_default_presentation('Existing content', $directory_post), 'existing Directory presentation is unchanged');
directory_admin_ux_same($default_more, wp_seed_content_directory_default_presentation($default_more, $directory_post), 'existing More block is not duplicated');
directory_admin_ux_same('', wp_seed_content_directory_default_presentation('', $existing_directory_post), 'existing empty Directory presentation remains empty');
directory_admin_ux_same('', wp_seed_content_directory_default_presentation('', $other_post), 'other post types are unchanged');

$existing_more_help = wp_seed_content_directory_get_more_editor_help($default_more);
directory_admin_ux_same(true, false !== strpos($existing_more_help, 'déjà présent'), 'preinserted empty More is reported as present');
directory_admin_ux_same(true, false !== strpos($existing_more_help, 'Déplacez-le'), 'existing More reminder explains moving the native block');
directory_admin_ux_same(true, false !== strpos($existing_more_help, 'N’ajoutez pas de second bloc'), 'existing More reminder prevents duplicate insertion');
directory_admin_ux_same(true, false !== strpos($existing_more_help, 'préinséré'), 'reminder explains the new-person default');
directory_admin_ux_same(false, false !== strpos($existing_more_help, 'Résumé'), 'reminder no longer asks for a deprecated summary');
directory_admin_ux_same(true, false !== strpos(wp_seed_content_directory_get_more_editor_help('Old presentation'), 'n’en contient pas'), 'legacy person without More receives truthful insertion guidance');
directory_admin_ux_same($existing_more_help, wp_seed_content_directory_get_more_editor_help('Intro<!--more Lire la suite-->Continuation'), 'custom native marker receives the same moving guidance');
directory_admin_ux_same(true, false !== strpos($admin_source, 'seed-directory-more-help'), 'reminder belongs to the identity metabox used by both editors');

$moved_content = 'Introduction' . $default_more . 'Continuation';
$moved_parts = wp_seed_content_split_wordpress_more($moved_content);
directory_admin_ux_same(1, substr_count($moved_content, '<!--more-->'), 'moving the existing block leaves a single marker');
directory_admin_ux_same('Introduction', $moved_parts['intro'], 'content before the moved marker is introduction');
directory_admin_ux_same('Continuation', $moved_parts['more'], 'content after the moved marker is continuation');
wp_seed_content_directory_get_more_editor_help($moved_content);
directory_admin_ux_same('Introduction' . $default_more . 'Continuation', $moved_content, 'reminder does not modify content');

if ($failures) {
    fwrite(STDERR, 'FAIL ' . count($failures) . '/' . $assertions . ': ' . implode('; ', $failures) . PHP_EOL);
    exit(1);
}

echo 'PASS ' . $assertions . ' Directory admin UX assertions' . PHP_EOL;
