<?php

define('ABSPATH', __DIR__ . '/');

$GLOBALS['wpsck_condition_filters'] = array();
$GLOBALS['wpsck_condition_actions'] = array();
$GLOBALS['wpsck_condition_package'] = null;

function add_filter($hook, $callback, $priority = 10, $accepted_args = 1)
{
    $GLOBALS['wpsck_condition_filters'][$hook] = array($callback, $priority, $accepted_args);
}

function add_action($hook, $callback)
{
    $GLOBALS['wpsck_condition_actions'][$hook] = $callback;
}

function et_core_is_fb_enabled()
{
    return true;
}

function wp_seed_content_directory_individual_contact_provider_definitions()
{
    $labels = array('phone' => 'Téléphone', 'email' => 'E-mail', 'website' => 'Site', 'facebook' => 'Facebook', 'instagram' => 'Instagram', 'linkedin' => 'LinkedIn', 'whatsapp' => 'WhatsApp', 'address' => 'Adresse');
    $definitions = array();
    foreach ($labels as $slug => $label) {
        $name_slug = 'linkedin' === $slug ? 'Linkedin' : ('whatsapp' === $slug ? 'Whatsapp' : ucfirst($slug));
        $definitions[$slug] = array(
            'condition_name' => 'wpsckDirectory' . $name_slug . 'Present',
            'condition_label' => 'WPSCK — Annuaire — ' . $label . ' renseigné',
            'display_provider_id' => 'loop_wpsck_directory_' . $slug,
        );
    }
    return $definitions;
}

eval('namespace ET\\Builder\\VisualBuilder\\Assets; class PackageBuildManager { public static function register_package_build($params) { $GLOBALS["wpsck_condition_package"] = $params; } }');

define('WP_SEED_CONTENT_KIT_URL', 'https://example.test/wp-content/plugins/wp-seed-content-kit/');
define('WP_SEED_CONTENT_KIT_VERSION', 'test');

require dirname(__DIR__) . '/plugin/includes/integrations/divi/directory-has-more-condition.php';

function wpsck_condition_assert($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$condition_name = wp_seed_content_divi_directory_has_more_condition_name();
$value_key = wp_seed_content_divi_directory_has_more_condition_value_key();
$contact_value_key = wp_seed_content_divi_directory_contact_condition_value_key();

wpsck_condition_assert(
    'wpsckDirectoryHasMore' === $condition_name,
    'Stable condition name changed.'
);
wpsck_condition_assert(
    true === wp_seed_content_divi_directory_evaluate_has_more_condition(
        null,
        $condition_name,
        array($value_key => '<p>Continuation</p>'),
        'sophie'
    ),
    'Sophie continuation must make the condition true.'
);
wpsck_condition_assert(
    false === wp_seed_content_divi_directory_evaluate_has_more_condition(
        null,
        $condition_name,
        array($value_key => ''),
        'anne'
    ),
    'Anne empty continuation must make the condition false.'
);
wpsck_condition_assert(
    true === wp_seed_content_divi_directory_evaluate_has_more_condition(
        null,
        $condition_name,
        array($value_key => '$variable({"type":"content"})$'),
        'status-endpoint'
    ),
    'Unresolved Builder status token must not hide the module.'
);
wpsck_condition_assert(
    'untouched' === wp_seed_content_divi_directory_evaluate_has_more_condition(
        'untouched',
        'anotherCondition',
        array(),
        'other'
    ),
    'Unrelated custom conditions must remain untouched.'
);
wpsck_condition_assert(
    true === wp_seed_content_divi_directory_evaluate_has_more_condition(
        null,
        'wpsckDirectoryPhonePresent',
        array($contact_value_key => '07 45 00 78 41'),
        'phone-present'
    ),
    'Generic contact condition must be true for a loop-resolved value.'
);
wpsck_condition_assert(
    false === wp_seed_content_divi_directory_evaluate_has_more_condition(
        null,
        'wpsckDirectoryFacebookPresent',
        array($contact_value_key => ''),
        'facebook-empty'
    ),
    'Generic contact condition must be false for an empty loop-resolved value.'
);
$legacy_clone_values = array('06 00 00 00 01', '', '06 00 00 00 03');
$legacy_clone_results = array();
foreach ($legacy_clone_values as $index => $legacy_clone_value) {
    $legacy_clone_results[] = wp_seed_content_divi_directory_evaluate_has_more_condition(
        null,
        $condition_name,
        array($contact_value_key => $legacy_clone_value),
        'legacy-contact-clone-' . $index
    );
}
wpsck_condition_assert(
    array(true, false, true) === $legacy_clone_results,
    'Legacy misnamed contact conditions must resolve TRUE/FALSE/TRUE without clone leakage.'
);
wpsck_condition_assert(
    false === wp_seed_content_divi_directory_evaluate_has_more_condition(
        null,
        $condition_name,
        array($contact_value_key => ''),
        'legacy-contact-after-present'
    ),
    'A previous true contact clone must not leak into the next empty clone.'
);
wpsck_condition_assert(
    true === wp_seed_content_divi_directory_evaluate_has_more_condition(
        null,
        $condition_name,
        array(
            $value_key => '',
            $contact_value_key => 'contact@example.test',
        ),
        'legacy-contact-key-precedence'
    ),
    'The explicit legacy contact setting must take precedence over an empty has_more value.'
);
wpsck_condition_assert(
    8 === count(wp_seed_content_divi_directory_contact_condition_names()),
    'All standard contact-presence choices must use the shared evaluator.'
);
wpsck_condition_assert(
    isset($GLOBALS['wpsck_condition_filters']['divi_module_options_conditions_is_custom_condition_true']),
    'Divi custom condition callback is not registered.'
);
wpsck_condition_assert(
    isset($GLOBALS['wpsck_condition_actions']['divi_visual_builder_assets_before_enqueue_scripts']),
    'Visual Builder package registration hook is not registered.'
);

call_user_func($GLOBALS['wpsck_condition_actions']['divi_visual_builder_assets_before_enqueue_scripts']);
$package = $GLOBALS['wpsck_condition_package'];
wpsck_condition_assert(
    'wp-seed-content-kit-divi-directory-has-more-condition' === $package['name']
        && false === $package['script']['enqueue_top_window']
        && true === $package['script']['enqueue_app_window'],
    'Condition adapter must be registered as an app-window package.'
);
wpsck_condition_assert(
    in_array('divi-field-library', $package['script']['deps'], true)
        && in_array('divi-vendor-wp-hooks', $package['script']['deps'], true),
    'Visual Builder condition extension dependencies are incomplete.'
);
wpsck_condition_assert(
    8 === count($package['script']['data_app_window']['contactConditions'])
        && 'loop_wpsck_directory_phone' === $package['script']['data_app_window']['contactConditions'][0]['provider'],
    'Visual Builder conditions must be generated from the PHP contact registry.'
);
wpsck_condition_assert(
    WP_SEED_CONTENT_KIT_VERSION !== $package['version'],
    'Visual Builder asset version must be content-derived for reliable cache busting.'
);
wpsck_condition_assert(
    false !== strpos(
        file_get_contents(dirname(__DIR__) . '/plugin/includes/integrations/divi/directory-has-more-condition/visual-builder.js'),
        "name: 'loop_wpsck_directory_presentation_more'"
    ),
    'Condition adapter must store the canonical loop-aware Dynamic Content provider.'
);
wpsck_condition_assert(
    false !== strpos(
        file_get_contents(dirname(__DIR__) . '/plugin/includes/integrations/divi/directory-has-more-condition/visual-builder.js'),
        'data.contactConditions'
    ),
    'Contact condition adapter must consume registry-driven runtime data.'
);
wpsck_condition_assert(
    false !== strpos(
        file_get_contents(dirname(__DIR__) . '/plugin/includes/integrations/divi/directory-has-more-condition/visual-builder.js'),
        'conditionName: selectedName'
    ),
    'New contact conditions must persist their selected registry condition name.'
);

echo "Divi Directory has_more condition harness: PASS\n";
