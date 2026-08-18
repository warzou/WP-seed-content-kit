<?php

define('ABSPATH', __DIR__ . '/');

$meta_boxes = file_get_contents(__DIR__ . '/../plugin/includes/modules/testimonials/meta-boxes.php');
$save_meta = file_get_contents(__DIR__ . '/../plugin/includes/modules/testimonials/save-meta.php');
$bindings = file_get_contents(__DIR__ . '/../plugin/includes/integrations/gutenberg/block-bindings.php');
$dynamic = file_get_contents(__DIR__ . '/../plugin/includes/core/dynamic-data.php');
$divi = file_get_contents(__DIR__ . '/../plugin/includes/integrations/divi/class-dynamic-content-testimonial-loop-fields.php');
$post_type = file_get_contents(__DIR__ . '/../plugin/includes/modules/testimonials/post-type.php');

$assertions = 0;
$failures = array();
function summary_admin_assert($condition, $label)
{
    global $assertions, $failures;
    $assertions++;
    if (!$condition) {
        $failures[] = $label;
    }
}

summary_admin_assert(false !== strpos($meta_boxes, 'wp_seed_content_testimonial_summary'), 'summary field');
summary_admin_assert(false !== strpos($meta_boxes, 'esc_textarea((string) $post->post_excerpt)'), 'summary reads post_excerpt');
summary_admin_assert(false !== strpos($meta_boxes, "remove_meta_box('postexcerpt'"), 'excerpt metabox hidden');
summary_admin_assert(false !== strpos($meta_boxes, "remove_meta_box('postcustom'"), 'custom fields metabox hidden');
summary_admin_assert(false !== strpos($save_meta, "'post_excerpt' => \$summary"), 'summary writes post_excerpt');
summary_admin_assert(false === strpos($save_meta, "'post_content' => \$summary"), 'summary does not write post_content');
summary_admin_assert(false === strpos($save_meta, "'seed_testimonial_text' => \$summary"), 'summary does not write testimonial text');
summary_admin_assert(false !== strpos($bindings, "'testimonial.summary' => 'text'"), 'summary Block Binding');
summary_admin_assert(false !== strpos($dynamic, "'testimonial.summary' => array("), 'summary Content Data');
summary_admin_assert(false !== strpos($divi, "'loop_wpsck_testimonial_summary'"), 'summary Divi provider');
summary_admin_assert(false !== strpos($post_type, "'excerpt'") && false !== strpos($post_type, "'custom-fields'"), 'CPT supports preserved');

if ($failures) {
    fwrite(STDERR, 'FAIL ' . count($failures) . '/' . $assertions . ': ' . implode(', ', $failures) . PHP_EOL);
    exit(1);
}
echo 'PASS ' . $assertions . ' Testimonial summary admin assertions' . PHP_EOL;
