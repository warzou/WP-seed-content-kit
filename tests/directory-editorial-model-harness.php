<?php

define('ABSPATH', __DIR__ . '/');
$GLOBALS['dem_assertions'] = 0;
$GLOBALS['dem_failures'] = array();
$GLOBALS['dem_options'] = array();
$GLOBALS['dem_meta'] = array();
$GLOBALS['dem_posts'] = array();
$GLOBALS['dem_terms'] = array();
$GLOBALS['dem_term_ids'] = array();

class WP_Error { public $code; public function __construct($code = '') { $this->code = $code; } }
class WP_Post {
    public $ID; public $post_type = 'seed_directory'; public $post_status = 'publish'; public $post_password = '';
    public $post_title; public $post_date = '2026-08-14 00:00:00'; public $menu_order;
    public function __construct($id, $title, $order) { $this->ID = $id; $this->post_title = $title; $this->menu_order = $order; }
}
function dem_same($expected, $actual, $label) { $GLOBALS['dem_assertions']++; if ($expected !== $actual) { $GLOBALS['dem_failures'][] = $label . ' got ' . var_export($actual, true); } }
function __($text, $domain = null) { return $text; }
function sanitize_key($value) { return strtolower(preg_replace('/[^a-z0-9_-]/', '', (string) $value)); }
function sanitize_title($value) { return trim(strtolower(preg_replace('/[^a-z0-9]+/i', '-', (string) $value)), '-'); }
function sanitize_text_field($value) { return trim(strip_tags((string) $value)); }
function esc_attr($value) { return (string) $value; }
function esc_html($value) { return (string) $value; }
function esc_attr__($value) { return (string) $value; }
function esc_html__($value) { return (string) $value; }
function esc_attr_e($value) { echo (string) $value; }
function esc_html_e($value) { echo (string) $value; }
function esc_js($value) { return (string) $value; }
function checked($checked) { if ($checked) { echo 'checked="checked"'; } }
function _n($single, $plural, $number) { return 1 === (int) $number ? $single : $plural; }
function sanitize_textarea_field($value) { return sanitize_text_field($value); }
function sanitize_email($value) { return (string) $value; }
function is_email($value) { return false !== strpos($value, '@'); }
function esc_url_raw($value, $protocols = null) { return (string) $value; }
function wp_parse_url($value) { return parse_url($value); }
function absint($value) { return abs((int) $value); }
function wp_seed_content_sanitize_iso_date($value) { return (string) $value; }
function add_action($hook, $callback, $priority = 10, $args = 1) {}
function add_filter($hook, $callback, $priority = 10, $args = 1) {}
function apply_filters($hook, $value) { return $value; }
function get_option($key, $default = false) { return array_key_exists($key, $GLOBALS['dem_options']) ? $GLOBALS['dem_options'][$key] : $default; }
function update_option($key, $value) { $GLOBALS['dem_options'][$key] = $value; return true; }
function get_post_meta($id, $key, $single = true) { return isset($GLOBALS['dem_meta'][$id][$key]) ? $GLOBALS['dem_meta'][$id][$key] : ''; }
function metadata_exists($type, $id, $key) { return isset($GLOBALS['dem_meta'][$id]) && array_key_exists($key, $GLOBALS['dem_meta'][$id]); }
function update_post_meta($id, $key, $value) { $GLOBALS['dem_meta'][$id][$key] = $value; return true; }
function delete_post_meta($id, $key) { unset($GLOBALS['dem_meta'][$id][$key]); return true; }
function get_post($id) { return isset($GLOBALS['dem_posts'][$id]) ? $GLOBALS['dem_posts'][$id] : null; }
function get_post_type($id) { $post = get_post($id); return $post ? $post->post_type : ''; }
function get_post_field($field, $id) { $post = get_post($id); return $post ? $post->{$field} : ''; }
function get_the_title($id) { $post = get_post($id); return $post ? $post->post_title : ''; }
function wp_update_post($data) { if (isset($GLOBALS['dem_posts'][$data['ID']], $data['menu_order'])) { $GLOBALS['dem_posts'][$data['ID']]->menu_order = (int) $data['menu_order']; } return $data['ID']; }
function wp_is_post_revision($id) { return false; }
function wp_seed_content_kit_is_module_active($module) { return 'directory' === $module; }
function wp_seed_content_directory_is_publicly_eligible($id) { return isset($GLOBALS['dem_posts'][$id]); }
function get_posts($args) {
    $posts = array_values($GLOBALS['dem_posts']);
    if (isset($args['fields']) && 'ids' === $args['fields']) { return array_map(function ($post) { return $post->ID; }, $posts); }
    if (!empty($args['post__in'])) { $posts = array_filter($posts, function ($post) use ($args) { return in_array($post->ID, $args['post__in'], true); }); }
    return array_values($posts);
}
function register_taxonomy($taxonomy, $types, $args) {}
function taxonomy_exists($taxonomy) { return true; }
function get_term_by($field, $value, $taxonomy) {
    if (!isset($GLOBALS['dem_term_ids'][$taxonomy][$value])) { return false; }
    return (object) array('term_id' => $GLOBALS['dem_term_ids'][$taxonomy][$value], 'slug' => $value, 'name' => $value);
}
function get_term($id, $taxonomy) {
    foreach (isset($GLOBALS['dem_term_ids'][$taxonomy]) ? $GLOBALS['dem_term_ids'][$taxonomy] : array() as $slug => $term_id) {
        if ((int) $id === (int) $term_id) { return (object) array('term_id' => $term_id, 'slug' => $slug, 'name' => $slug); }
    }
    return new WP_Error('missing');
}
function wp_insert_term($name, $taxonomy, $args) {
    $id = count(isset($GLOBALS['dem_term_ids'][$taxonomy]) ? $GLOBALS['dem_term_ids'][$taxonomy] : array()) + 1;
    $GLOBALS['dem_term_ids'][$taxonomy][$args['slug']] = $id;
    return array('term_id' => $id);
}
function wp_update_term($id, $taxonomy, $args) { return array('term_id' => $id); }
function wp_set_object_terms($id, $terms, $taxonomy, $append = false) {
    $slugs = array();
    foreach ((array) $terms as $term) {
        if (is_int($term)) { $object = get_term($term, $taxonomy); $slugs[] = $object->slug; } else { $slugs[] = $term; }
    }
    $GLOBALS['dem_terms'][$id][$taxonomy] = $slugs;
    return $slugs;
}
function wp_get_object_terms($id, $taxonomy, $args) { return isset($GLOBALS['dem_terms'][$id][$taxonomy]) ? $GLOBALS['dem_terms'][$id][$taxonomy] : array(); }
function is_wp_error($value) { return $value instanceof WP_Error; }
function wp_seed_content_directory_is_list_array($value) { return !$value || array_keys($value) === range(0, count($value) - 1); }
function wp_seed_content_divi_is_single_post_type_query($args, $type) { return isset($args['post_type']) && in_array($type, (array) $args['post_type'], true); }
function wp_seed_content_divi_apply_collection_ids($args, $ids) { $args['post__in'] = $ids ? $ids : array(0); $args['orderby'] = 'post__in'; return $args; }
function remove_accents($value) { return $value; }

$root = dirname(__DIR__) . '/plugin/';
require $root . 'includes/modules/directory/classification-registry.php';
require $root . 'includes/modules/directory/fields.php';
require $root . 'includes/modules/directory/classification-taxonomies.php';
require $root . 'includes/modules/directory/collections.php';
require $root . 'includes/modules/directory/classification-migration.php';
require $root . 'includes/integrations/divi/directory-collection-query.php';

dem_same(array('praticien', 'intervenant'), array_keys(wp_seed_content_directory_get_classification_registry('profile_type', true)), 'default profiles');
dem_same(array('en_exercice', 'recherche_modeles'), array_keys(wp_seed_content_directory_get_classification_registry('status', true)), 'default statuses');
$status_rows = wp_seed_content_directory_get_classification_registry('status', false);
$status_rows['en_exercice']['label'] = 'Actif';
$status_rows['recherche_modeles']['active'] = false;
$status_rows[] = array('slug' => 'en_pause', 'label' => 'En pause', 'active' => true, 'order' => 30);
$GLOBALS['dem_options']['wp_seed_content_directory_status_registry'] = array_values($status_rows);
dem_same('Actif', wp_seed_content_directory_classification_options('status', false)['en_exercice'], 'label rename preserves slug');
dem_same(false, isset(wp_seed_content_directory_classification_options('status', true)['recherche_modeles']), 'disabled status hidden from new selection');
dem_same(true, isset(wp_seed_content_directory_classification_options('status', true)['en_pause']), 'new status added');

$GLOBALS['dem_posts'][1] = new WP_Post(1, 'Alice', 20);
$GLOBALS['dem_posts'][2] = new WP_Post(2, 'Bruno', 10);
$GLOBALS['dem_posts'][3] = new WP_Post(3, 'Claire', 30);
$GLOBALS['dem_meta'][1] = array('seed_directory_status' => 'en_exercice', 'seed_directory_profile_types' => array('praticien'));
$GLOBALS['dem_meta'][2] = array('seed_directory_status' => 'practicing', '_seed_directory_seeking_models' => '1', '_seed_directory_profile_types' => array('intervenant'));
$GLOBALS['dem_meta'][3] = array('_seed_directory_status' => 'practicing', '_seed_directory_profile_types' => array('praticien', 'intervenant'));
dem_same('en_exercice', wp_seed_content_directory_resolve_status(1), 'canonical status wins');
dem_same('recherche_modeles', wp_seed_content_directory_resolve_status(2), 'legacy seeking resolves status');
dem_same('en_exercice', wp_seed_content_directory_resolve_status(3), 'legacy practicing resolves status');
dem_same(array('praticien', 'intervenant'), wp_seed_content_directory_resolve_profile_types(3), 'multi profile preserved');

dem_same(true, wp_seed_content_directory_sync_classification_projection(2), 'projection sync');
$first_projection = $GLOBALS['dem_terms'][2];
dem_same(true, wp_seed_content_directory_sync_classification_projection(2), 'projection idempotent call');
dem_same($first_projection, $GLOBALS['dem_terms'][2], 'projection idempotent values');
dem_same(true, wp_seed_content_directory_audit_classification_projection(2)['match'], 'projection audit match');
$GLOBALS['dem_terms'][2]['wp_seed_directory_status'] = array('en-exercice');
dem_same(false, wp_seed_content_directory_audit_classification_projection(2)['match'], 'projection mismatch detected');
dem_same(array(1, 2, 3), wp_seed_content_directory_rebuild_classification_projections(array(1, 2, 3))['synced'], 'projection rebuild');

dem_same(array(1, 3), wp_seed_content_directory_get_entries(array('status' => 'en_exercice')), 'status filter');
dem_same(array(2, 3), wp_seed_content_directory_get_entries(array('profile_type' => 'intervenant')), 'profile filter');
dem_same(array(2, 1, 3), wp_seed_content_directory_get_entries(array('orderby' => 'menu_order')), 'menu order');
dem_same(array(1, 2, 3), wp_seed_content_directory_get_entries(array('orderby' => 'name')), 'name order');
dem_same(array(1, 3, 2), wp_seed_content_directory_get_entries(array('orderby' => 'status,name')), 'registry status order');
dem_same(array(1, 3, 2), wp_seed_content_directory_get_entries(array('orderby' => 'profile_type,name')), 'registry profile order');
dem_same(array(1, 3, 2), wp_seed_content_directory_get_entries(array('orderby' => 'profile_type,status,menu_order,name')), 'multi criteria order');

$tax_query = array('post_type' => array('seed_directory'), 'tax_query' => array(array('taxonomy' => 'wp_seed_directory_profile_type', 'field' => 'slug', 'terms' => array('intervenant'))));
$adapted = wp_seed_content_divi_apply_directory_collection_query($tax_query);
dem_same(array(2, 3), $adapted['post__in'], 'Divi taxonomy filter uses canonical collection');
dem_same(false, isset($adapted['tax_query']), 'controlled taxonomy removed before WP query');

$dry_run = wp_seed_content_directory_classification_migration_dry_run(array(1, 2, 3));
dem_same(3, $dry_run['total'], 'migration dry run total');
dem_same(array('intervenants' => 2, 'active_practitioners' => 2, 'seeking_models' => 1), $dry_run['populations'], 'migration populations');
$before = $GLOBALS['dem_meta'];
$applied = wp_seed_content_directory_classification_migration_apply($dry_run);
dem_same(array(1, 2, 3), $applied['applied'], 'migration apply available');
dem_same('recherche_modeles', $GLOBALS['dem_meta'][2]['seed_directory_status'], 'migration writes canonical status');
$rolled_back = wp_seed_content_directory_classification_migration_rollback($applied);
dem_same(array(1, 2, 3), $rolled_back['restored'], 'migration rollback available');
dem_same($before, $GLOBALS['dem_meta'], 'rollback restores exact metas');

$profile_rows = wp_seed_content_directory_get_classification_registry('profile_type', false);
$profile_rows[] = array('slug' => 'mentor', 'label' => 'Mentor', 'active' => true, 'order' => 30);
$GLOBALS['dem_options']['wp_seed_content_directory_profile_type_registry'] = array_values($profile_rows);
$GLOBALS['dem_posts'][99] = new WP_Post(99, 'Usage', 99);
$GLOBALS['dem_meta'][99] = array('seed_directory_status' => 'en_pause', 'seed_directory_profile_types' => array('mentor'));
dem_same(array('mentor' => 1), wp_seed_content_directory_classification_usage_counts('profile_type', array('mentor')), 'profile usage counts resolved entries');
dem_same(array('en_pause' => 1), wp_seed_content_directory_classification_usage_counts('status', array('en_pause')), 'status usage counts resolved entries');
$used_profile_removal = wp_seed_content_directory_apply_classification_removals('profile_type', array_values($profile_rows), array_values($profile_rows), array('mentor'), array('mentor' => 1));
dem_same('used', $used_profile_removal['blocked']['mentor'], 'used custom profile removal blocked');
dem_same(true, isset($used_profile_removal['settings']['mentor']), 'used custom profile remains registered');
$unused_profile_removal = wp_seed_content_directory_apply_classification_removals('profile_type', array_values($profile_rows), array_values($profile_rows), array('mentor'), array('mentor' => 0));
dem_same(array('mentor'), $unused_profile_removal['removed'], 'unused custom profile removed');
dem_same(false, isset($unused_profile_removal['settings']['mentor']), 'removed custom profile leaves registry');
$system_profile_removal = wp_seed_content_directory_apply_classification_removals('profile_type', array_values($profile_rows), array_values($profile_rows), array('praticien'), array('praticien' => 0));
dem_same('system', $system_profile_removal['blocked']['praticien'], 'system profile removal blocked');
$used_status_removal = wp_seed_content_directory_apply_classification_removals('status', array_values($status_rows), array_values($status_rows), array('en_pause'), array('en_pause' => 1));
dem_same('used', $used_status_removal['blocked']['en_pause'], 'used custom status removal blocked');
$unused_status_removal = wp_seed_content_directory_apply_classification_removals('status', array_values($status_rows), array_values($status_rows), array('en_pause'), array('en_pause' => 0));
dem_same(array('en_pause'), $unused_status_removal['removed'], 'unused custom status removed');
$system_status_removal = wp_seed_content_directory_apply_classification_removals('status', array_values($status_rows), array_values($status_rows), array('en_exercice'), array('en_exercice' => 0));
dem_same('system', $system_status_removal['blocked']['en_exercice'], 'system status removal blocked');

ob_start();
wp_seed_content_directory_render_classification_settings('profile_type');
$profile_registry_html = ob_get_clean();
dem_same(true, false !== strpos($profile_registry_html, 'data-wpsck-classification-add="profile_type"'), 'profile registry exposes add action');
dem_same(true, false !== strpos($profile_registry_html, 'data-wpsck-classification-remove'), 'profile registry exposes custom removal action');
dem_same(true, false !== strpos($profile_registry_html, 'Utilisé par 1 fiche.'), 'profile registry explains used deletion block');
dem_same(true, false !== strpos($profile_registry_html, 'Type système'), 'profile registry marks protected system values');
ob_start();
wp_seed_content_directory_render_classification_settings('status');
$status_registry_html = ob_get_clean();
dem_same(true, false !== strpos($status_registry_html, 'data-wpsck-classification-add="status"'), 'status registry exposes add action');
dem_same(true, false !== strpos($status_registry_html, 'data-wpsck-classification-remove'), 'status registry exposes custom removal action');
dem_same(true, false !== strpos($status_registry_html, 'Utilisé par 1 fiche.'), 'status registry explains used deletion block');
unset($GLOBALS['dem_posts'][99], $GLOBALS['dem_meta'][99]);

$admin_source = file_get_contents($root . 'includes/modules/directory/admin.php');
$post_type_source = file_get_contents($root . 'includes/modules/directory/post-type.php');
$builder_meta_source = file_get_contents($root . 'includes/modules/directory/builder-meta.php');
dem_same(true, false !== strpos($admin_source, 'Nom de la personne'), 'name admin UX');
dem_same(true, false !== strpos($admin_source, 'Ce nom est utilisé dans les affichages publics'), 'name help');
dem_same(true, false !== strpos($admin_source, 'Intitulé professionnel'), 'professional label admin UX');
dem_same(true, false !== strpos($admin_source, 'Intitulé affichable sous le nom de la personne'), 'professional label help');
dem_same(false, false !== strpos($admin_source, 'name="seed_directory_professional_label" required'), 'professional label remains optional');
dem_same(true, false !== strpos($post_type_source, 'Photo de la personne'), 'featured image admin UX');
dem_same(false, false !== strpos($admin_source, 'Recherche actuellement des modèles'), 'legacy seeking checkbox removed');
dem_same(false, (bool) preg_match('/update_post_meta\([^;]*seed_directory_seeking_models/s', $admin_source), 'admin has no seeking writes');
dem_same(true, false !== strpos($builder_meta_source, "'seed_directory_seeking_models' === \$meta_key"), 'legacy public seeking meta is REST read only');
$request = new class { public function get_params() { return array('wp_seed_directory_status' => array(1)); } };
dem_same(true, wp_seed_content_directory_reject_projection_rest_writes(null, $request) instanceof WP_Error, 'projection REST write rejected');

if ($GLOBALS['dem_failures']) { fwrite(STDERR, 'FAIL ' . count($GLOBALS['dem_failures']) . '/' . $GLOBALS['dem_assertions'] . ': ' . implode('; ', $GLOBALS['dem_failures']) . PHP_EOL); exit(1); }
echo 'PASS ' . $GLOBALS['dem_assertions'] . ' Directory editorial model assertions' . PHP_EOL;
