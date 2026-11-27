<?php

$assertions = 0;
$failures = array();

function seed_template_ux_assert($condition, $label)
{
    global $assertions, $failures;
    $assertions++;

    if (!$condition) {
        $failures[] = $label;
    }
}

function seed_template_ux_contains($source, $needle, $label)
{
    seed_template_ux_assert(false !== strpos($source, $needle), $label);
}

$root = dirname(__DIR__);
$templates_path = $root . '/plugin/includes/core/templates.php';
$builders_path = $root . '/plugin/includes/integrations/builders.php';
$templates = file_get_contents($templates_path);
$builders = file_get_contents($builders_path);

seed_template_ux_assert(false !== $templates, 'Template admin source is readable');
seed_template_ux_assert(false !== $builders, 'Builder integration source is readable');

seed_template_ux_contains($templates, "'name' => __('Templates WP Seed'", 'CPT name uses Templates WP Seed');
seed_template_ux_contains($templates, "'menu_name' => __('Templates WP Seed'", 'CPT menu uses Templates WP Seed');
seed_template_ux_contains($templates, "'all_items' => __('Templates WP Seed'", 'CPT list uses Templates WP Seed');
seed_template_ux_contains($templates, "'public' => false", 'seed_template remains private');
seed_template_ux_contains($templates, "'show_in_rest' => true", 'seed_template remains REST-enabled');

seed_template_ux_contains($templates, "__('Utilisation & intégration', 'wp-seed-content-kit')", 'Usage meta box title is updated');
seed_template_ux_contains($templates, "'wp_seed_content_render_template_usage_meta_box'", 'Usage meta box callback is unchanged');
seed_template_ux_contains($templates, "'normal',", 'Usage meta box remains below the editor');
seed_template_ux_contains($templates, "'high'", 'Usage meta box keeps high priority');

seed_template_ux_contains($templates, '$divi_available = $divi_detected && $divi_library_available;', 'Divi availability requires active Divi and its library');
seed_template_ux_contains($templates, '<?php if ($divi_available) : ?>', 'Divi source is conditionally rendered');
seed_template_ux_contains($templates, '<?php if (!$divi_available) : ?>', 'Divi-absent explanation is conditionally rendered');
seed_template_ux_contains($templates, 'La source Divi Library est disponible lorsque Divi est installé et actif.', 'Neutral Divi-absent explanation is present');
seed_template_ux_contains($templates, 'value="divi_layout"', 'Divi Library source remains available');
seed_template_ux_contains($templates, 'data-wp-seed-divi-layout-settings', 'Divi layout selector remains available');
seed_template_ux_contains($templates, 'Créer un layout Divi', 'Create Layout action remains available');
seed_template_ux_contains($templates, 'Gérer les layouts Divi', 'Manage Layouts action remains available');
seed_template_ux_contains($templates, 'Ouvrir le layout sélectionné', 'Open selected Layout action remains available');

seed_template_ux_contains($builders, "'status' => 'library_workflow'", 'Divi Library workflow remains active');
seed_template_ux_assert(false === strpos($builders, 'et_builder_post_types'), 'seed_template is not enabled directly through et_builder_post_types');
seed_template_ux_assert(false === strpos($builders, 'et_builder_post_types_'), 'seed_template is not enabled through a related Divi post-type filter');

if (!empty($failures)) {
    fwrite(STDERR, 'FAIL ' . count($failures) . ' / ' . $assertions . PHP_EOL);
    foreach ($failures as $failure) {
        fwrite(STDERR, '- ' . $failure . PHP_EOL);
    }
    exit(1);
}

echo 'PASS ' . $assertions . ' template admin UX assertions' . PHP_EOL;
