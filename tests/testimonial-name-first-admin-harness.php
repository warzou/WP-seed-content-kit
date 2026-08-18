<?php

define('ABSPATH', __DIR__ . '/');
$GLOBALS['name_hooks'] = array();
$GLOBALS['name_meta'] = array();
$GLOBALS['name_updates'] = array();

class WP_Post
{
    public $ID;
    public $post_type = 'seed_testimonial';
    public $post_title;

    public function __construct($id, $title)
    {
        $this->ID = $id;
        $this->post_title = $title;
    }
}

class WP_Query
{
    private $vars;
    public function __construct($vars) { $this->vars = $vars; }
    public function is_main_query() { return true; }
    public function get($key) { return isset($this->vars[$key]) ? $this->vars[$key] : ''; }
}

class Testimonial_Name_WPDB
{
    public $postmeta = 'wp_postmeta';
    public $posts = 'wp_posts';
    public function esc_like($value) { return addcslashes($value, '_%\\'); }
    public function prepare($sql)
    {
        $values = array_slice(func_get_args(), 1);
        foreach ($values as $value) {
            $position = strpos($sql, '%s');
            $sql = substr_replace($sql, "'" . $value . "'", $position, 2);
        }
        return $sql;
    }
}
$wpdb = new Testimonial_Name_WPDB();

function __($value) { return $value; }
function sanitize_text_field($value) { return trim(strip_tags((string) $value)); }
function add_action($hook, $callback, $priority = 10, $args = 1) { $GLOBALS['name_hooks'][$hook] = array($callback, $priority, $args); }
function add_filter($hook, $callback, $priority = 10, $args = 1) { $GLOBALS['name_hooks'][$hook] = array($callback, $priority, $args); }
function wp_is_post_revision() { return false; }
function wp_seed_content_get_testimonial_builder_meta($post_id, $key) { return isset($GLOBALS['name_meta'][$post_id][$key]) ? $GLOBALS['name_meta'][$post_id][$key] : ''; }
function wp_update_post($data) { $GLOBALS['name_updates'][] = $data; wp_seed_content_testimonial_sync_technical_title($data['ID'], new WP_Post($data['ID'], $data['post_title'])); }
function get_edit_post_link($post_id) { return 'post.php?post=' . (int) $post_id; }
function esc_url($value) { return $value; }
function esc_html($value) { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function get_the_post_thumbnail() { return ''; }
function get_post_modified_time() { return '18/08/2026'; }
function get_option() { return 'd/m/Y'; }
function is_admin() { return true; }

require __DIR__ . '/../plugin/includes/modules/testimonials/editorial-identity.php';

$assertions = 0;
function testimonial_name_assert($condition, $message)
{
    global $assertions;
    $assertions++;
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

testimonial_name_assert('Témoignage — Test' === wp_seed_content_testimonial_get_technical_title('Test'), 'Technical title format.');
$GLOBALS['name_meta'][10]['seed_testimonial_name'] = 'Nom canonique';
wp_seed_content_testimonial_sync_technical_title(10, new WP_Post(10, 'Ancien titre'));
testimonial_name_assert(1 === count($GLOBALS['name_updates']), 'One title update without recursion.');
testimonial_name_assert('Témoignage — Nom canonique' === $GLOBALS['name_updates'][0]['post_title'], 'Canonical public meta drives title.');

$columns = wp_seed_content_testimonial_admin_columns(array('position' => 'Position', 'actions' => 'Actions', 'title' => 'Titre', 'date' => 'Date'));
testimonial_name_assert(!isset($columns['title'], $columns['date']), 'Technical title and date columns removed.');
testimonial_name_assert(isset($columns['testimonial_name'], $columns['testimonial_context'], $columns['testimonial_photo'], $columns['testimonial_modified']), 'Editorial columns present.');
testimonial_name_assert('testimonial_name' === wp_seed_content_testimonial_primary_column('title', 'edit-seed_testimonial'), 'Name is primary column.');

$query = new WP_Query(array('post_type' => 'seed_testimonial', 's' => 'Nom'));
$search = wp_seed_content_testimonial_admin_search(" AND ((wp_posts.post_title LIKE '%Nom%'))", $query);
testimonial_name_assert(false !== strpos($search, "'seed_testimonial_name'") && false !== strpos($search, "'_seed_testimonial_name'"), 'Search covers canonical and legacy fallback keys.');
testimonial_name_assert(isset($GLOBALS['name_hooks']['save_post'], $GLOBALS['name_hooks']['posts_search']), 'Hooks registered.');

$post_type_source = file_get_contents(__DIR__ . '/../plugin/includes/modules/testimonials/post-type.php');
testimonial_name_assert(false === strpos($post_type_source, "array('title', 'excerpt'"), 'Title editor support remains hidden.');
$content_data_source = file_get_contents(__DIR__ . '/../plugin/includes/core/content-data.php');
testimonial_name_assert(false === strpos($content_data_source, '_seed_testimonial_title'), 'Legacy title is not an editorial fallback.');

echo 'PASS ' . $assertions . ' Testimonial name-first admin assertions' . PHP_EOL;
