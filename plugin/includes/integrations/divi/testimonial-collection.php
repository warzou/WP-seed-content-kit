<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Whether the public Divi 5 module API is available for this request.
 *
 * @return bool
 */
function wp_seed_content_divi_testimonial_collection_is_available()
{
    return function_exists('et_builder_d5_enabled') && et_builder_d5_enabled();
}

/**
 * Extract collection settings from Divi's nested module attributes.
 *
 * @param array $attrs Divi module attributes.
 *
 * @return array
 */
function wp_seed_content_divi_testimonial_collection_args($attrs)
{
    $values = isset($attrs['collection']['innerContent']['desktop']['value'])
        && is_array($attrs['collection']['innerContent']['desktop']['value'])
        ? $attrs['collection']['innerContent']['desktop']['value']
        : array();

    $template = isset($values['template']) ? $values['template'] : 'native';
    if ('native' === $template) {
        $template = '';
    }

    return array(
        'ids' => isset($values['ids']) ? $values['ids'] : '',
        'featured' => isset($values['featured']) ? $values['featured'] : 'all',
        'context' => isset($values['context']) ? $values['context'] : '',
        'limit' => isset($values['limit']) ? $values['limit'] : 3,
        'orderby' => isset($values['orderby']) ? $values['orderby'] : 'date',
        'order' => isset($values['order']) ? $values['order'] : 'desc',
        'template' => $template,
        'columns' => isset($values['columns']) ? $values['columns'] : 3,
    );
}

/**
 * Register the server-side module with Divi's dependency tree.
 *
 * @param object $dependency_tree Divi dependency tree.
 *
 * @return void
 */
function wp_seed_content_register_divi_testimonial_collection_module($dependency_tree)
{
    if (
        !is_object($dependency_tree)
        || !method_exists($dependency_tree, 'add_dependency')
        || !interface_exists('ET\\Builder\\Framework\\DependencyManagement\\Interfaces\\DependencyInterface')
        || !class_exists('ET\\Builder\\Packages\\ModuleLibrary\\ModuleRegistration')
    ) {
        return;
    }

    require_once __DIR__ . '/testimonial-collection/Module.php';

    $module_class = 'WPSeedContentKit\\Divi\\TestimonialCollection\\Module';
    if (class_exists($module_class)) {
        $dependency_tree->add_dependency(new $module_class());
    }
}
add_action(
    'divi_module_library_modules_dependency_tree',
    'wp_seed_content_register_divi_testimonial_collection_module'
);

/**
 * Return published testimonial Templates for the Divi select field.
 *
 * @return array
 */
function wp_seed_content_get_divi_testimonial_template_options()
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
            || 'testimonials' !== wp_seed_content_get_template_module($template->ID)
        ) {
            continue;
        }

        $slug = sanitize_title($template->post_name);
        if ('' === $slug) {
            continue;
        }

        $options[$slug] = array(
            'label' => sprintf('%s (%s)', get_the_title($template), $slug),
        );
    }

    return $options;
}

/**
 * Register the authenticated Builder preview route.
 *
 * @return void
 */
function wp_seed_content_register_divi_testimonial_preview_route()
{
    if (!wp_seed_content_divi_testimonial_collection_is_available()) {
        return;
    }

    register_rest_route(
        'wp-seed-content-kit/v1',
        '/divi/testimonials-preview',
        array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => 'wp_seed_content_render_divi_testimonial_preview',
            'permission_callback' => 'wp_seed_content_can_preview_divi_testimonials',
            'args' => array(
                'ids' => array('sanitize_callback' => 'sanitize_text_field'),
                'featured' => array('sanitize_callback' => 'sanitize_key'),
                'context' => array('sanitize_callback' => 'sanitize_text_field'),
                'limit' => array('sanitize_callback' => 'absint'),
                'orderby' => array('sanitize_callback' => 'sanitize_key'),
                'order' => array('sanitize_callback' => 'sanitize_key'),
                'template_slug' => array('sanitize_callback' => 'sanitize_title'),
                'columns' => array('sanitize_callback' => 'absint'),
            ),
        )
    );
}
add_action('rest_api_init', 'wp_seed_content_register_divi_testimonial_preview_route');

/**
 * Restrict Builder previews to users who can edit pages.
 *
 * @return bool
 */
function wp_seed_content_can_preview_divi_testimonials()
{
    return current_user_can('edit_pages');
}

/**
 * Render the same collection HTML used on the frontend.
 *
 * @param WP_REST_Request $request REST request.
 *
 * @return WP_REST_Response
 */
function wp_seed_content_render_divi_testimonial_preview($request)
{
    $args = array();
    foreach (array('ids', 'featured', 'context', 'limit', 'orderby', 'order', 'columns') as $key) {
        if (null !== $request->get_param($key)) {
            $args[$key] = $request->get_param($key);
        }
    }

    if (null !== $request->get_param('template_slug')) {
        $args['template'] = $request->get_param('template_slug');
    }

    return rest_ensure_response(
        array(
            'html' => wp_seed_content_render_testimonial_collection($args, false),
        )
    );
}

/**
 * Register the Visual Builder script, its runtime data and collection styles.
 *
 * @return void
 */
function wp_seed_content_register_divi_testimonial_collection_builder_assets()
{
    static $registered = false;

    if (
        $registered
        || !wp_seed_content_divi_testimonial_collection_is_available()
        || !function_exists('et_core_is_fb_enabled')
        || !et_core_is_fb_enabled()
        || !class_exists('ET\\Builder\\VisualBuilder\\Assets\\PackageBuildManager')
    ) {
        return;
    }

    $metadata_file = __DIR__ . '/testimonial-collection/module.json';
    $metadata = json_decode((string) file_get_contents($metadata_file), true);
    if (!is_array($metadata)) {
        return;
    }

    $registered = true;
    \ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build(
        array(
            'name' => 'wp-seed-content-kit-divi-testimonial-collection',
            'version' => WP_SEED_CONTENT_KIT_VERSION,
            'script' => array(
                'src' => WP_SEED_CONTENT_KIT_URL . 'includes/integrations/divi/testimonial-collection/visual-builder.js',
                'deps' => array(
                    'divi-module-library',
                    'divi-vendor-wp-hooks',
                ),
                'enqueue_top_window' => false,
                'enqueue_app_window' => true,
                'data_app_window' => array(
                    'metadata' => $metadata,
                    'templates' => wp_seed_content_get_divi_testimonial_template_options(),
                    'canManageTemplates' => current_user_can('manage_wp_seed_templates'),
                    'restRoute' => '/wp-seed-content-kit/v1/divi/testimonials-preview',
                    'labels' => array(
                        'loading' => __('Chargement de l’aperçu…', 'wp-seed-content-kit'),
                        'error' => __('L’aperçu ne peut pas être chargé.', 'wp-seed-content-kit'),
                    ),
                ),
            ),
            'style' => array(
                'src' => WP_SEED_CONTENT_KIT_URL . 'assets/css/seed-content-kit.css',
                'deps' => array(),
                'enqueue_top_window' => false,
                'enqueue_app_window' => true,
            ),
        )
    );
}
add_action(
    'divi_visual_builder_assets_before_enqueue_scripts',
    'wp_seed_content_register_divi_testimonial_collection_builder_assets'
);
