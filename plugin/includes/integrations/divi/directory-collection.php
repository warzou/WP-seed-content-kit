<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Whether the public Divi 5 module API is available for this request.
 *
 * @return bool
 */
function wp_seed_content_divi_directory_collection_is_available()
{
    return function_exists('et_builder_d5_enabled') && et_builder_d5_enabled();
}

/**
 * Extract Directory settings from Divi's nested module attributes.
 *
 * @param array $attrs Divi module attributes.
 *
 * @return array
 */
function wp_seed_content_divi_directory_collection_args($attrs)
{
    $values = isset($attrs['collection']['innerContent']['desktop']['value'])
        && is_array($attrs['collection']['innerContent']['desktop']['value'])
        ? $attrs['collection']['innerContent']['desktop']['value']
        : array();
    $defaults = array(
        'status' => 'all',
        'profile_types' => 'all',
        'profile_type_operator' => 'or',
        'seeking_models' => 'all',
        'department' => '',
        'country' => '',
        'featured' => 'all',
        'ids' => '',
        'exclude_ids' => '',
        'limit' => '0',
        'offset' => '0',
        'orderby' => 'display_order',
        'order' => 'asc',
        'template' => 'native',
    );
    $args = array();
    foreach ($defaults as $key => $default) {
        $args[$key] = isset($values[$key]) ? $values[$key] : $default;
    }
    if ('native' === $args['template']) {
        $args['template'] = '';
    }
    if ('all' === $args['profile_types']) {
        $args['profile_types'] = '';
    }

    return $args;
}

/**
 * Register the server-side module with Divi's dependency tree.
 *
 * @param object $dependency_tree Divi dependency tree.
 *
 * @return void
 */
function wp_seed_content_register_divi_directory_collection_module($dependency_tree)
{
    if (
        !is_object($dependency_tree)
        || !method_exists($dependency_tree, 'add_dependency')
        || !interface_exists('ET\\Builder\\Framework\\DependencyManagement\\Interfaces\\DependencyInterface')
        || !class_exists('ET\\Builder\\Packages\\ModuleLibrary\\ModuleRegistration')
    ) {
        return;
    }

    require_once __DIR__ . '/directory-collection/Module.php';

    $module_class = 'WPSeedContentKit\\Divi\\DirectoryCollection\\Module';
    if (class_exists($module_class)) {
        $dependency_tree->add_dependency(new $module_class());
    }
}
add_action(
    'divi_module_library_modules_dependency_tree',
    'wp_seed_content_register_divi_directory_collection_module'
);

/**
 * Return published Directory Templates for the Divi select field.
 *
 * @return array
 */
function wp_seed_content_get_divi_directory_template_options()
{
    $options = array(
        'native' => array('label' => __('Rendu natif', 'wp-seed-content-kit')),
    );

    if (!current_user_can('manage_wp_seed_templates')) {
        return $options;
    }

    $templates = get_posts(
        array(
            'post_type' => 'seed_template',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
            'no_found_rows' => true,
            'suppress_filters' => true,
        )
    );
    foreach ($templates as $template) {
        if (
            !$template instanceof WP_Post
            || 'directory' !== wp_seed_content_get_template_module($template->ID)
        ) {
            continue;
        }
        $slug = sanitize_title($template->post_name);
        if ('' !== $slug) {
            $options[$slug] = array(
                'label' => sprintf('%s (%s)', get_the_title($template), $slug),
            );
        }
    }

    return $options;
}

/**
 * Preserve raw scalar values until the canonical normalizer validates them.
 *
 * @param mixed $value REST value.
 *
 * @return string
 */
function wp_seed_content_sanitize_divi_directory_preview_scalar($value)
{
    return is_scalar($value) ? trim(wp_unslash((string) $value)) : '';
}

/**
 * Register the authenticated Builder preview route.
 *
 * WordPress REST cookie authentication validates the wp_rest nonce before this
 * permission callback. The explicit header check keeps the endpoint Builder-only.
 *
 * @return void
 */
function wp_seed_content_register_divi_directory_preview_route()
{
    if (!wp_seed_content_divi_directory_collection_is_available()) {
        return;
    }

    $args = array();
    foreach (array(
        'status',
        'profile_types',
        'profile_type_operator',
        'seeking_models',
        'department',
        'country',
        'featured',
        'ids',
        'exclude_ids',
        'limit',
        'offset',
        'orderby',
        'order',
        'template_slug',
    ) as $key) {
        $args[$key] = array(
            'required' => false,
            'sanitize_callback' => 'wp_seed_content_sanitize_divi_directory_preview_scalar',
        );
    }

    register_rest_route(
        'wp-seed-content-kit/v1',
        '/divi/directory-preview',
        array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => 'wp_seed_content_render_divi_directory_preview',
            'permission_callback' => 'wp_seed_content_can_preview_divi_directory',
            'args' => $args,
        )
    );
}
add_action('rest_api_init', 'wp_seed_content_register_divi_directory_preview_route');

/**
 * Require a valid REST nonce and page editing capability.
 *
 * @param WP_REST_Request $request REST request.
 *
 * @return bool
 */
function wp_seed_content_can_preview_divi_directory($request)
{
    $nonce = is_object($request) && method_exists($request, 'get_header')
        ? $request->get_header('X-WP-Nonce')
        : '';

    return '' !== $nonce
        && wp_verify_nonce($nonce, 'wp_rest')
        && current_user_can('edit_pages');
}

/**
 * Return validated request settings using the public collection contract.
 *
 * @param WP_REST_Request $request REST request.
 *
 * @return array|null
 */
function wp_seed_content_get_divi_directory_preview_args($request)
{
    $args = array();
    foreach (array(
        'status',
        'profile_types',
        'profile_type_operator',
        'seeking_models',
        'department',
        'country',
        'featured',
        'ids',
        'exclude_ids',
        'limit',
        'offset',
        'orderby',
        'order',
    ) as $key) {
        if (null !== $request->get_param($key)) {
            $args[$key] = $request->get_param($key);
        }
    }
    if (null !== $request->get_param('template_slug')) {
        $args['template'] = $request->get_param('template_slug');
    }

    return wp_seed_content_directory_normalize_shortcode_atts($args);
}

/**
 * Render the same public Directory HTML used on the frontend.
 *
 * @param WP_REST_Request $request REST request.
 *
 * @return WP_REST_Response|WP_Error
 */
function wp_seed_content_render_divi_directory_preview($request)
{
    $normalized = wp_seed_content_get_divi_directory_preview_args($request);
    if (null === $normalized) {
        return new WP_Error(
            'wp_seed_content_invalid_directory_preview',
            __('Paramètres d’aperçu Annuaire invalides.', 'wp-seed-content-kit'),
            array('status' => 400)
        );
    }

    $response = rest_ensure_response(
        array(
            'html' => wp_seed_content_render_normalized_directory_collection(
                $normalized,
                false
            ),
        )
    );
    if (is_object($response) && method_exists($response, 'header')) {
        $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->header('Pragma', 'no-cache');
    }

    return $response;
}

/**
 * Register the Visual Builder script, runtime data and canonical Directory styles.
 *
 * @return void
 */
function wp_seed_content_register_divi_directory_collection_builder_assets()
{
    static $registered = false;

    if (
        $registered
        || !wp_seed_content_divi_directory_collection_is_available()
        || !function_exists('et_core_is_fb_enabled')
        || !et_core_is_fb_enabled()
        || !class_exists('ET\\Builder\\VisualBuilder\\Assets\\PackageBuildManager')
    ) {
        return;
    }

    $metadata_file = __DIR__ . '/directory-collection/module.json';
    $metadata = json_decode((string) file_get_contents($metadata_file), true);
    if (!is_array($metadata)) {
        return;
    }

    $registered = true;
    \ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build(
        array(
            'name' => 'wp-seed-content-kit-divi-directory-collection',
            'version' => WP_SEED_CONTENT_KIT_VERSION,
            'script' => array(
                'src' => WP_SEED_CONTENT_KIT_URL . 'includes/integrations/divi/directory-collection/visual-builder.js',
                'deps' => array('divi-module-library', 'divi-vendor-wp-hooks'),
                'enqueue_top_window' => false,
                'enqueue_app_window' => true,
                'data_app_window' => array(
                    'metadata' => $metadata,
                    'templates' => wp_seed_content_get_divi_directory_template_options(),
                    'canManageTemplates' => current_user_can('manage_wp_seed_templates'),
                    'restRoute' => '/wp-seed-content-kit/v1/divi/directory-preview',
                    'labels' => array(
                        'loading' => __('Chargement de l’aperçu…', 'wp-seed-content-kit'),
                        'error' => __('L’aperçu ne peut pas être chargé.', 'wp-seed-content-kit'),
                    ),
                ),
            ),
            'style' => array(
                'src' => WP_SEED_CONTENT_KIT_URL . 'includes/integrations/divi/directory-collection/visual-builder.css',
                'deps' => array(),
                'enqueue_top_window' => false,
                'enqueue_app_window' => true,
            ),
        )
    );
}
add_action(
    'divi_visual_builder_assets_before_enqueue_scripts',
    'wp_seed_content_register_divi_directory_collection_builder_assets'
);
