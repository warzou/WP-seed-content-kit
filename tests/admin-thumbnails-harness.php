<?php
define('ABSPATH', __DIR__ . '/');
define('WP_SEED_CONTENT_KIT_URL', 'https://example.test/plugin/');
define('WP_SEED_CONTENT_KIT_VERSION', '0.9.0-rc.2');
$default = dirname(__DIR__) . '/plugin';
if (!is_file($default . '/includes/admin/thumbnails.php')) { $default = dirname(__DIR__, 2) . '/dist/admin-thumbnails-svg/candidate/plugin'; }
$plugin = isset($argv[1]) ? $argv[1] : $default;
$enqueued = array(); $screen = null; $count = 0;
function get_current_screen() { return $GLOBALS['screen']; }
function add_action($hook, $callback) { $GLOBALS['hook'] = array($hook, $callback); }
function wp_enqueue_style($handle, $url, $deps, $version) { $GLOBALS['enqueued'][] = array($handle, $url, $deps, $version); }
function check_thumbnail($condition, $message) { $GLOBALS['count']++; if (!$condition) { throw new RuntimeException($message); } }
require $plugin . '/includes/admin/thumbnails.php';
check_thumbnail($hook === array('admin_enqueue_scripts', 'wp_seed_content_enqueue_admin_thumbnails'), 'admin-only hook');
foreach (array('seed_directory', 'seed_testimonial', 'post', 'page', 'attachment', 'seed_quote', 'seed_schedule') as $type) {
    foreach (array('edit.php', 'post.php', 'post-new.php', 'upload.php', 'index.php', 'widgets.php') as $hook_name) {
        $screen = (object) array('post_type' => $type); $enqueued = array();
        wp_seed_content_enqueue_admin_thumbnails($hook_name);
        $expected = $hook_name === 'edit.php' && in_array($type, array('seed_directory','seed_testimonial'), true)
            || in_array($hook_name,array('post.php','post-new.php'),true) && $type === 'seed_testimonial';
        check_thumbnail(count($enqueued) === (int) $expected, $type . '/' . $hook_name . ' scope');
    }
}
$screen = null; $enqueued=array(); wp_seed_content_enqueue_admin_thumbnails('edit.php');
check_thumbnail(!$enqueued, 'missing screen is a no-op');
$css=file_get_contents($plugin.'/assets/css/admin-thumbnails.css');
check_thumbnail(strpos($css,'!important')===false, 'no important override');
foreach(array('48px','60px','150px','object-fit: contain','width: auto','height: auto','min(100%,') as $s){check_thumbnail(strpos($css,$s)!==false,$s);}
foreach(array('directory/admin.php','testimonials/editorial-identity.php','testimonials/meta-boxes.php') as $f){$s=file_get_contents($plugin.'/includes/modules/'.$f);check_thumbnail(strpos($s,"'full'")!==false,'native full source '.$f);}
foreach(array('directory/admin.php','testimonials/editorial-identity.php','testimonials/meta-boxes.php') as $f){$s=file_get_contents($plugin.'/includes/modules/'.$f);check_thumbnail(strpos($s,"'loading' => false")!==false,'no admin auto-sizes containment '.$f);}
$s=file_get_contents($plugin.'/includes/modules/testimonials/editorial-identity.php');check_thumbnail(strpos($s,'object-fit:cover')===false,'no forced square crop');
$s=file_get_contents($plugin.'/includes/modules/testimonials/meta-boxes.php');check_thumbnail(strpos($s,'var preview = sizes.full || attachment;')!==false,'media selector keeps full framing');
echo 'PASS '.$count." admin thumbnail contract assertions\n";
