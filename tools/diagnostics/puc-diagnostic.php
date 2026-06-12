<?php
/**
 * Temporary read-only diagnostic snippet for WP Seed Content Kit updates.
 *
 * Copy this file into Code Snippets on the site to diagnose Plugin Update
 * Checker GitHub responses from WordPress itself. Keep it disabled except
 * during diagnostics, then disable or delete it after use.
 *
 * This snippet is read-only:
 * - no transient deletion;
 * - no option update;
 * - no cache flush;
 * - admin-only display through manage_options.
 */

add_action('admin_menu', function () {
    if (!current_user_can('manage_options')) {
        return;
    }

    add_management_page(
        'Diagnostic PUC WP Seed',
        'Diagnostic PUC WP Seed',
        'manage_options',
        'wp-seed-puc-diagnostic',
        'wp_seed_puc_diagnostic_render_page'
    );
});

function wp_seed_puc_diagnostic_render_page()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Access denied.', 'default'));
    }

    $plugin_file = WP_PLUGIN_DIR . '/wp-seed-content-kit/wp-seed-content-kit.php';
    $update_checker_file = WP_PLUGIN_DIR . '/wp-seed-content-kit/includes/core/update-checker.php';
    $github_api_url = 'https://api.github.com/repos/warzou/WP-seed-content-kit/releases/latest';

    echo '<div class="wrap">';
    echo '<h1>Diagnostic PUC WP Seed Content Kit</h1>';
    echo '<p><strong>Mode :</strong> lecture seule. Aucun transient, option ou cache n’est modifié.</p>';

    echo '<h2>1. Version installée</h2>';

    $header_version = null;
    if (file_exists($plugin_file)) {
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugin_data = get_plugin_data($plugin_file, false, false);
        $header_version = isset($plugin_data['Version']) ? $plugin_data['Version'] : null;
    }

    wp_seed_puc_diagnostic_dump(array(
        'plugin_file_exists' => file_exists($plugin_file),
        'plugin_file' => $plugin_file,
        'header_version' => $header_version,
        'constant_defined' => defined('WP_SEED_CONTENT_KIT_VERSION'),
        'constant_version' => defined('WP_SEED_CONTENT_KIT_VERSION') ? WP_SEED_CONTENT_KIT_VERSION : null,
    ));

    echo '<h2>2. Configuration PUC installée</h2>';

    $configured_urls = array();
    if (file_exists($update_checker_file) && is_readable($update_checker_file)) {
        $update_checker_source = file_get_contents($update_checker_file);
        if (is_string($update_checker_source)) {
            preg_match_all('#https://github\.com/[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+/?#', $update_checker_source, $matches);
            $configured_urls = isset($matches[0]) ? array_values(array_unique($matches[0])) : array();
        }
    }

    wp_seed_puc_diagnostic_dump(array(
        'update_checker_file_exists' => file_exists($update_checker_file),
        'update_checker_file_readable' => is_readable($update_checker_file),
        'configured_github_urls_found' => $configured_urls,
    ));

    echo '<h2>3. Test HTTP WordPress vers GitHub</h2>';
    echo '<p><code>' . esc_html($github_api_url) . '</code></p>';

    $response = wp_remote_get($github_api_url, array(
        'timeout' => 20,
        'redirection' => 5,
        'headers' => array(
            'Accept' => 'application/vnd.github+json',
            'User-Agent' => 'WP-Seed-Content-Kit-Diagnostic/' . (defined('WP_SEED_CONTENT_KIT_VERSION') ? WP_SEED_CONTENT_KIT_VERSION : 'unknown'),
        ),
    ));

    if (is_wp_error($response)) {
        wp_seed_puc_diagnostic_dump(array(
            'success' => false,
            'wp_error_code' => $response->get_error_code(),
            'wp_error_message' => $response->get_error_message(),
            'wp_error_data' => $response->get_error_data(),
        ));
    } else {
        $headers = wp_seed_puc_diagnostic_headers_to_array(wp_remote_retrieve_headers($response));
        $body = wp_remote_retrieve_body($response);
        $body_json = json_decode($body, true);

        $body_extract = array(
            'json_valid' => is_array($body_json),
            'tag_name' => is_array($body_json) && isset($body_json['tag_name']) ? $body_json['tag_name'] : null,
            'html_url' => is_array($body_json) && isset($body_json['html_url']) ? $body_json['html_url'] : null,
            'assets' => array(),
        );

        if (is_array($body_json) && isset($body_json['assets']) && is_array($body_json['assets'])) {
            foreach ($body_json['assets'] as $asset) {
                $body_extract['assets'][] = array(
                    'name' => isset($asset['name']) ? $asset['name'] : null,
                    'size' => isset($asset['size']) ? $asset['size'] : null,
                    'state' => isset($asset['state']) ? $asset['state'] : null,
                    'digest' => isset($asset['digest']) ? $asset['digest'] : null,
                    'browser_download_url' => isset($asset['browser_download_url']) ? $asset['browser_download_url'] : null,
                );
            }
        }

        wp_seed_puc_diagnostic_dump(array(
            'success' => true,
            'http_code' => wp_remote_retrieve_response_code($response),
            'http_message' => wp_remote_retrieve_response_message($response),
            'headers_useful' => array(
                'x-ratelimit-limit' => wp_seed_puc_diagnostic_header_get($headers, 'x-ratelimit-limit'),
                'x-ratelimit-remaining' => wp_seed_puc_diagnostic_header_get($headers, 'x-ratelimit-remaining'),
                'x-ratelimit-reset' => wp_seed_puc_diagnostic_header_get($headers, 'x-ratelimit-reset'),
                'x-github-request-id' => wp_seed_puc_diagnostic_header_get($headers, 'x-github-request-id'),
            ),
            'body_extract' => $body_extract,
            'body_first_1000_chars' => substr($body, 0, 1000),
        ));
    }

    echo '<h2>4. Options / transients PUC en lecture seule</h2>';

    echo '<h3>external_updates-wp-seed-content-kit</h3>';
    wp_seed_puc_diagnostic_dump(array(
        'get_option' => get_option('external_updates-wp-seed-content-kit', null),
        'get_site_option' => get_site_option('external_updates-wp-seed-content-kit', null),
    ));

    echo '<h3>_site_transient_update_plugins - extrait wp-seed-content-kit uniquement</h3>';
    wp_seed_puc_diagnostic_dump(wp_seed_puc_diagnostic_filter_update_plugins(get_site_transient('update_plugins')));

    echo '<h3>_site_transient_puc_manual_check_errors-wp-seed-content-kit</h3>';
    wp_seed_puc_diagnostic_dump(array(
        'get_site_transient' => get_site_transient('puc_manual_check_errors-wp-seed-content-kit'),
        'raw_site_option_value' => get_site_option('_site_transient_puc_manual_check_errors-wp-seed-content-kit', null),
        'raw_site_option_timeout' => get_site_option('_site_transient_timeout_puc_manual_check_errors-wp-seed-content-kit', null),
    ));

    echo '<h2>5. Plugins potentiellement concernés</h2>';
    wp_seed_puc_diagnostic_dump(wp_seed_puc_diagnostic_get_relevant_plugins());

    echo '</div>';
}

function wp_seed_puc_diagnostic_dump($value)
{
    echo '<pre style="max-width:100%; overflow:auto; padding:12px; background:#fff; border:1px solid #ccd0d4;">';
    echo esc_html(wp_json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    echo '</pre>';
}

function wp_seed_puc_diagnostic_headers_to_array($headers)
{
    if (is_object($headers) && method_exists($headers, 'getAll')) {
        return $headers->getAll();
    }

    return is_array($headers) ? $headers : array();
}

function wp_seed_puc_diagnostic_header_get($headers, $name)
{
    foreach ($headers as $header_name => $header_value) {
        if (strtolower((string) $header_name) === strtolower($name)) {
            return $header_value;
        }
    }

    return null;
}

function wp_seed_puc_diagnostic_filter_update_plugins($update_plugins)
{
    if (!is_object($update_plugins) && !is_array($update_plugins)) {
        return array(
            'found' => false,
            'raw_type' => gettype($update_plugins),
            'value' => $update_plugins,
        );
    }

    $data = is_object($update_plugins) ? get_object_vars($update_plugins) : $update_plugins;
    $plugin_keys = array(
        'wp-seed-content-kit/wp-seed-content-kit.php',
        'wp-seed-content-kit.php',
    );

    $result = array(
        'last_checked' => isset($data['last_checked']) ? $data['last_checked'] : null,
        'checked' => array(),
        'response' => array(),
        'no_update' => array(),
        'translations' => isset($data['translations']) ? $data['translations'] : null,
    );

    foreach ($plugin_keys as $plugin_key) {
        if (isset($data['checked'][$plugin_key])) {
            $result['checked'][$plugin_key] = $data['checked'][$plugin_key];
        }

        if (isset($data['response'][$plugin_key])) {
            $result['response'][$plugin_key] = $data['response'][$plugin_key];
        }

        if (isset($data['no_update'][$plugin_key])) {
            $result['no_update'][$plugin_key] = $data['no_update'][$plugin_key];
        }
    }

    $result['found_any_wp_seed_entry'] = !empty($result['checked']) || !empty($result['response']) || !empty($result['no_update']);

    return $result;
}

function wp_seed_puc_diagnostic_get_relevant_plugins()
{
    if (!function_exists('get_plugins')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    $plugins = get_plugins();
    $relevant = array();

    foreach ($plugins as $plugin_path => $plugin_data) {
        $name = isset($plugin_data['Name']) ? $plugin_data['Name'] : '';
        $path_lc = strtolower($plugin_path);
        $name_lc = strtolower($name);

        if (
            strpos($path_lc, 'wp-seed-content-kit') !== false
            || strpos($path_lc, 'wordfence') !== false
            || strpos($path_lc, 'w3-total-cache') !== false
            || strpos($name_lc, 'wordfence') !== false
            || strpos($name_lc, 'w3 total cache') !== false
            || strpos($name_lc, 'cache') !== false
            || strpos($name_lc, 'security') !== false
        ) {
            $relevant[$plugin_path] = array(
                'name' => $name,
                'version' => isset($plugin_data['Version']) ? $plugin_data['Version'] : null,
                'active' => is_plugin_active($plugin_path),
            );
        }
    }

    return $relevant;
}
