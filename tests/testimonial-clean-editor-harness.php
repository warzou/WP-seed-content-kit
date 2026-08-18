<?php

define('ABSPATH', __DIR__ . '/');
$GLOBALS['testimonial_editor_hooks'] = array();

function add_action($hook, $callback, $priority = 10, $accepted_args = 1)
{
    $GLOBALS['testimonial_editor_hooks']['actions'][$hook][] = array($callback, $priority, $accepted_args);
}

function add_filter($hook, $callback, $priority = 10, $accepted_args = 1)
{
    $GLOBALS['testimonial_editor_hooks']['filters'][$hook][] = array($callback, $priority, $accepted_args);
}

require __DIR__ . '/../plugin/includes/modules/testimonials/meta-boxes.php';

$assertions = 0;
function testimonial_editor_assert($condition, $message)
{
    global $assertions;
    $assertions++;
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$settings = wp_seed_content_testimonial_editor_settings();
testimonial_editor_assert(true === $settings['tinymce'], 'Visual TinyMCE tab must be enabled.');
testimonial_editor_assert(is_array($settings['quicktags']), 'Code Quicktags tab must be enabled.');
testimonial_editor_assert(false === $settings['media_buttons'], 'Events media-button pattern must be preserved.');
testimonial_editor_assert(false === $settings['teeny'], 'Standard WordPress toolbar must be used.');
testimonial_editor_assert(8 === $settings['textarea_rows'], 'Events editor row count must be reused.');
testimonial_editor_assert('seed_testimonial_text' === $settings['textarea_name'], 'Canonical meta textarea name changed.');
testimonial_editor_assert(false !== strpos($settings['quicktags']['buttons'], 'more'), 'Native More button missing.');

$mce_buttons = array('formatselect', 'bold', 'italic', 'bullist', 'numlist', 'blockquote', 'alignleft', 'link', 'wp_more', 'undo', 'redo', 'et_learn_more', 'et_box', 'et_button', 'et_tabs', 'et_author');
$clean_buttons = wp_seed_content_testimonial_clean_mce_buttons($mce_buttons, wp_seed_content_testimonial_editor_id());
foreach (array('bold', 'italic', 'bullist', 'numlist', 'blockquote', 'link', 'wp_more', 'undo', 'redo') as $button) {
    testimonial_editor_assert(in_array($button, $clean_buttons, true), 'Useful core button removed: ' . $button);
}
foreach (array('et_learn_more', 'et_box', 'et_button', 'et_tabs', 'et_author') as $button) {
    testimonial_editor_assert(!in_array($button, $clean_buttons, true), 'Divi legacy button remains: ' . $button);
}
testimonial_editor_assert($mce_buttons === wp_seed_content_testimonial_clean_mce_buttons($mce_buttons, 'content'), 'Other editors must remain unchanged.');

$plugins = array('lists' => 'core.js', 'et_quicktags' => 'divi.js');
$clean_plugins = wp_seed_content_testimonial_clean_mce_plugins($plugins, wp_seed_content_testimonial_editor_id());
testimonial_editor_assert(isset($clean_plugins['lists']) && !isset($clean_plugins['et_quicktags']), 'Divi TinyMCE plugin must be removed locally.');
testimonial_editor_assert($plugins === wp_seed_content_testimonial_clean_mce_plugins($plugins, 'content'), 'Other TinyMCE instances must remain unchanged.');

$js = file_get_contents(__DIR__ . '/../plugin/assets/js/testimonial-editor.js');
testimonial_editor_assert(false !== strpos($js, "var editorId = 'wp_seed_content_testimonial_text'"), 'Quicktags cleanup is not editor-scoped.');
testimonial_editor_assert(false !== strpos($js, "'more'") && false !== strpos($js, 'MutationObserver'), 'Quicktags More whitelist or late-button guard missing.');

echo 'PASS ' . $assertions . ' Testimonial clean editor assertions' . PHP_EOL;
