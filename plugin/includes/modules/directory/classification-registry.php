<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_directory_classification_definitions()
{
    return array(
        'profile_type' => array(
            'option' => 'wp_seed_content_directory_profile_type_registry',
            'heading' => __('Annuaire — Types de profil', 'wp-seed-content-kit'),
            'defaults' => array(
                'praticien' => __('Praticien', 'wp-seed-content-kit'),
                'intervenant' => __('Intervenant', 'wp-seed-content-kit'),
            ),
        ),
        'status' => array(
            'option' => 'wp_seed_content_directory_status_registry',
            'heading' => __('Annuaire — Statuts', 'wp-seed-content-kit'),
            'defaults' => array(
                'en_exercice' => __('En exercice', 'wp-seed-content-kit'),
                'recherche_modeles' => __('En recherche de modèles', 'wp-seed-content-kit'),
            ),
        ),
    );
}

function wp_seed_content_directory_default_classification_registry($kind)
{
    $definitions = wp_seed_content_directory_classification_definitions();
    if (!isset($definitions[$kind])) {
        return array();
    }
    $rows = array();
    $order = 10;
    foreach ($definitions[$kind]['defaults'] as $slug => $label) {
        $rows[$slug] = array(
            'slug' => $slug,
            'label' => $label,
            'active' => true,
            'order' => $order,
            'system' => true,
        );
        $order += 10;
    }
    return $rows;
}

function wp_seed_content_directory_normalize_classification_registry($kind, $value)
{
    $definitions = wp_seed_content_directory_classification_definitions();
    if (!isset($definitions[$kind])) {
        return array();
    }
    $value = is_array($value) ? $value : array();
    if (function_exists('wp_seed_content_directory_is_list_array') && !wp_seed_content_directory_is_list_array($value)) {
        $converted = array();
        foreach ($value as $slug => $row) {
            if (is_array($row)) {
                $row['slug'] = $slug;
                $converted[] = $row;
            }
        }
        $value = $converted;
    }
    $rows = array();
    foreach (array_values($value) as $position => $row) {
        if (!is_array($row)) {
            continue;
        }
        $slug = isset($row['slug']) ? substr(sanitize_key((string) $row['slug']), 0, 64) : '';
        if ('' === $slug || isset($rows[$slug])) {
            continue;
        }
        $label = isset($row['label']) ? sanitize_text_field($row['label']) : '';
        $rows[$slug] = array(
            'slug' => $slug,
            'label' => '' !== $label ? $label : $slug,
            'active' => !empty($row['active']),
            'order' => isset($row['order']) ? max(0, (int) $row['order']) : (($position + 1) * 10),
            'system' => isset($definitions[$kind]['defaults'][$slug]),
            '_position' => $position,
        );
    }
    foreach (wp_seed_content_directory_default_classification_registry($kind) as $slug => $default) {
        if (!isset($rows[$slug])) {
            $default['_position'] = count($rows);
            $rows[$slug] = $default;
        }
    }
    uasort($rows, function ($left, $right) {
        return $left['order'] === $right['order']
            ? $left['_position'] - $right['_position']
            : $left['order'] - $right['order'];
    });
    foreach ($rows as &$row) {
        unset($row['_position']);
    }
    unset($row);
    return $rows;
}

function wp_seed_content_directory_get_classification_registry($kind, $active_only = false)
{
    $definitions = wp_seed_content_directory_classification_definitions();
    if (!isset($definitions[$kind])) {
        return array();
    }
    $stored = function_exists('get_option') ? get_option($definitions[$kind]['option'], null) : null;
    $rows = is_array($stored)
        ? wp_seed_content_directory_normalize_classification_registry($kind, $stored)
        : wp_seed_content_directory_default_classification_registry($kind);
    if (!$active_only) {
        return $rows;
    }
    return array_filter($rows, function ($row) {
        return !empty($row['active']);
    });
}

function wp_seed_content_directory_sanitize_classification_registry($kind, $value, $previous)
{
    $rows = wp_seed_content_directory_normalize_classification_registry($kind, $value);
    $previous = wp_seed_content_directory_normalize_classification_registry($kind, $previous);
    foreach ($previous as $slug => $row) {
        if (!isset($rows[$slug])) {
            $row['active'] = false;
            $rows[$slug] = $row;
        }
    }
    return wp_seed_content_directory_normalize_classification_registry($kind, array_values($rows));
}

/**
 * Counts entries whose resolved classification uses a requested slug.
 *
 * Canonical and legacy fallbacks both count so a removal cannot orphan
 * existing Directory data.
 */
function wp_seed_content_directory_classification_usage_counts($kind, $slugs = array())
{
    $definitions = wp_seed_content_directory_classification_definitions();
    $slugs = array_values(array_unique(array_filter(array_map('sanitize_key', (array) $slugs))));
    $counts = array_fill_keys($slugs, 0);
    if (!isset($definitions[$kind]) || !$slugs || !function_exists('get_posts')) {
        return $counts;
    }

    $post_ids = get_posts(array(
        'post_type' => 'seed_directory',
        'post_status' => function_exists('get_post_stati') ? array_keys(get_post_stati()) : 'any',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'orderby' => 'ID',
        'order' => 'ASC',
        'suppress_filters' => true,
    ));
    foreach ((array) $post_ids as $post_id) {
        $values = 'status' === $kind
            ? array(wp_seed_content_directory_resolve_status((int) $post_id))
            : wp_seed_content_directory_resolve_profile_types((int) $post_id);
        foreach (array_unique(array_map('sanitize_key', (array) $values)) as $slug) {
            if (isset($counts[$slug])) {
                $counts[$slug]++;
            }
        }
    }

    return $counts;
}

/**
 * Applies only explicit removals of unused custom rows.
 */
function wp_seed_content_directory_apply_classification_removals($kind, $value, $previous, $requested, $usage_counts = null)
{
    $definitions = wp_seed_content_directory_classification_definitions();
    if (!isset($definitions[$kind])) {
        return array('settings' => array(), 'removed' => array(), 'blocked' => array());
    }

    $previous = wp_seed_content_directory_normalize_classification_registry($kind, $previous);
    $requested = array_values(array_unique(array_filter(array_map('sanitize_key', (array) $requested))));
    $system = $definitions[$kind]['defaults'];
    $usage_counts = is_array($usage_counts)
        ? $usage_counts
        : wp_seed_content_directory_classification_usage_counts($kind, $requested);
    $removed = array();
    $blocked = array();

    foreach ($requested as $slug) {
        if (isset($system[$slug])) {
            $blocked[$slug] = 'system';
            continue;
        }
        if (!isset($previous[$slug])) {
            $blocked[$slug] = 'unknown';
            continue;
        }
        if (!empty($usage_counts[$slug])) {
            $blocked[$slug] = 'used';
            continue;
        }
        unset($previous[$slug]);
        $removed[$slug] = true;
    }

    $filtered = array();
    foreach ((array) $value as $row) {
        $slug = is_array($row) && isset($row['slug']) ? sanitize_key((string) $row['slug']) : '';
        if ('' !== $slug && isset($removed[$slug])) {
            continue;
        }
        $filtered[] = $row;
    }
    $settings = wp_seed_content_directory_sanitize_classification_registry($kind, $filtered, $previous);
    foreach ($removed as $slug => $unused) {
        unset($settings[$slug]);
    }

    return array(
        'settings' => $settings,
        'removed' => array_keys($removed),
        'blocked' => $blocked,
    );
}

function wp_seed_content_directory_classification_options($kind, $active_only = true)
{
    $options = array();
    foreach (wp_seed_content_directory_get_classification_registry($kind, $active_only) as $slug => $row) {
        $options[$slug] = $row['label'];
    }
    return $options;
}

function wp_seed_content_directory_normalize_status_slug($value, $allow_inactive = true)
{
    $value = sanitize_key((string) $value);
    $legacy = array('practicing' => 'en_exercice', 'seeking_models' => 'recherche_modeles');
    if (isset($legacy[$value])) {
        $value = $legacy[$value];
    }
    $registry = wp_seed_content_directory_get_classification_registry('status', !$allow_inactive);
    return isset($registry[$value]) ? $value : '';
}

function wp_seed_content_directory_normalize_profile_slugs($value, $allow_inactive = true)
{
    if (is_string($value)) {
        $value = '' === trim($value) ? array() : preg_split('/[|,]+/', $value);
    }
    if (!is_array($value)) {
        return array();
    }
    $requested = array();
    foreach ($value as $slug) {
        $slug = sanitize_key((string) $slug);
        if ('' !== $slug) {
            $requested[$slug] = true;
        }
    }
    $normalized = array();
    foreach (wp_seed_content_directory_get_classification_registry('profile_type', !$allow_inactive) as $slug => $row) {
        if (isset($requested[$slug])) {
            $normalized[] = $slug;
        }
    }
    return $normalized;
}

function wp_seed_content_directory_resolve_status($post_id)
{
    $canonical = get_post_meta($post_id, 'seed_directory_status', true);
    $registry = wp_seed_content_directory_get_classification_registry('status', false);
    $canonical_slug = sanitize_key((string) $canonical);
    if (isset($registry[$canonical_slug])) {
        return $canonical_slug;
    }
    $legacy_status = '' !== $canonical_slug
        ? $canonical_slug
        : sanitize_key((string) get_post_meta($post_id, '_seed_directory_status', true));
    if ('seeking_models' === $legacy_status || 'recherche_modeles' === $legacy_status) {
        return 'recherche_modeles';
    }
    $seeking = get_post_meta($post_id, 'seed_directory_seeking_models', true);
    if ('' === (string) $seeking) {
        $seeking = get_post_meta($post_id, '_seed_directory_seeking_models', true);
    }
    if (!empty($seeking)) {
        return 'recherche_modeles';
    }
    if ('practicing' === $legacy_status) {
        return 'en_exercice';
    }
    return isset($registry[$legacy_status]) ? $legacy_status : '';
}

function wp_seed_content_directory_resolve_profile_types($post_id)
{
    $canonical = get_post_meta($post_id, 'seed_directory_profile_types', true);
    $resolved = wp_seed_content_directory_normalize_profile_slugs($canonical, true);
    if ($resolved) {
        return $resolved;
    }
    return wp_seed_content_directory_normalize_profile_slugs(
        get_post_meta($post_id, '_seed_directory_profile_types', true),
        true
    );
}

function wp_seed_content_directory_render_classification_registry_row($kind, $row, $index, $new = false, $usage_count = 0)
{
    $prefix = 'wp_seed_content_directory_' . $kind . '_registry[' . $index . ']';
    $system = !empty($row['system']);
    ?>
    <tr data-wpsck-classification-row data-wpsck-classification-kind="<?php echo esc_attr($kind); ?>" data-wpsck-classification-slug-value="<?php echo esc_attr(isset($row['slug']) ? $row['slug'] : ''); ?>" data-wpsck-classification-new="<?php echo $new ? '1' : '0'; ?>">
        <td><?php if ($new) : ?><input type="text" name="<?php echo esc_attr($prefix . '[slug]'); ?>" value="" placeholder="<?php esc_attr_e('slug-stable', 'wp-seed-content-kit'); ?>"><?php else : ?><code><?php echo esc_html($row['slug']); ?></code><input type="hidden" name="<?php echo esc_attr($prefix . '[slug]'); ?>" value="<?php echo esc_attr($row['slug']); ?>"><?php endif; ?></td>
        <td><input class="regular-text" type="text" name="<?php echo esc_attr($prefix . '[label]'); ?>" value="<?php echo esc_attr($row['label']); ?>"></td>
        <td><label><input type="checkbox" name="<?php echo esc_attr($prefix . '[active]'); ?>" value="1" <?php checked(!empty($row['active'])); ?>> <?php esc_html_e('Actif', 'wp-seed-content-kit'); ?></label><?php if ($system) : ?> <span class="description"><?php esc_html_e('Slug système protégé', 'wp-seed-content-kit'); ?></span><?php endif; ?></td>
        <td><input class="small-text" type="number" min="0" name="<?php echo esc_attr($prefix . '[order]'); ?>" value="<?php echo absint($row['order']); ?>" data-wpsck-classification-order></td>
        <td>
            <?php if ($system) : ?>
                <span class="description"><?php esc_html_e('Type système', 'wp-seed-content-kit'); ?></span>
            <?php else : ?>
                <button type="button" class="button-link-delete" data-wpsck-classification-remove data-wpsck-classification-usage="<?php echo absint($usage_count); ?>" data-wpsck-classification-label="<?php echo esc_attr(isset($row['label']) ? $row['label'] : ''); ?>"><?php echo $new ? esc_html__('Retirer', 'wp-seed-content-kit') : esc_html__('Supprimer', 'wp-seed-content-kit'); ?></button>
                <?php if (!$new && $usage_count > 0) : ?>
                    <p class="description"><?php echo esc_html(sprintf(_n('Utilisé par %d fiche.', 'Utilisé par %d fiches.', $usage_count, 'wp-seed-content-kit'), $usage_count)); ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </td>
    </tr>
    <?php
}

function wp_seed_content_directory_render_classification_settings($kind)
{
    $definitions = wp_seed_content_directory_classification_definitions();
    $rows = wp_seed_content_directory_get_classification_registry($kind, false);
    $usage_counts = wp_seed_content_directory_classification_usage_counts($kind, array_keys($rows));
    ?>
    <section class="wpsck-directory-classification">
        <h2><?php echo esc_html($definitions[$kind]['heading']); ?></h2>
        <p><?php esc_html_e('Le slug est le contrat stable des données et des builders. Le libellé, l’état et l’ordre restent configurables.', 'wp-seed-content-kit'); ?></p>
        <input type="hidden" name="wp_seed_content_directory_<?php echo esc_attr($kind); ?>_registry_present" value="1">
        <table class="widefat striped"><thead><tr><th><?php esc_html_e('Slug stable', 'wp-seed-content-kit'); ?></th><th><?php esc_html_e('Libellé', 'wp-seed-content-kit'); ?></th><th><?php esc_html_e('État', 'wp-seed-content-kit'); ?></th><th><?php esc_html_e('Ordre', 'wp-seed-content-kit'); ?></th><th><?php esc_html_e('Actions', 'wp-seed-content-kit'); ?></th></tr></thead><tbody data-wpsck-classification-list="<?php echo esc_attr($kind); ?>">
        <?php foreach (array_values($rows) as $index => $row) { wp_seed_content_directory_render_classification_registry_row($kind, $row, $index, false, isset($usage_counts[$row['slug']]) ? $usage_counts[$row['slug']] : 0); } ?>
        </tbody></table>
        <p><button type="button" class="button" data-wpsck-classification-add="<?php echo esc_attr($kind); ?>"><?php esc_html_e('Ajouter une valeur', 'wp-seed-content-kit'); ?></button></p>
        <p class="description"><?php esc_html_e('Une valeur système ne peut pas être supprimée. Une valeur personnalisée utilisée par une fiche doit être désactivée ou ses données doivent être migrées avant suppression.', 'wp-seed-content-kit'); ?></p>
        <template data-wpsck-classification-template="<?php echo esc_attr($kind); ?>"><?php wp_seed_content_directory_render_classification_registry_row($kind, array('slug' => '', 'label' => '', 'active' => true, 'order' => 0, 'system' => false), '__INDEX__', true); ?></template>
    </section>
    <?php
    wp_seed_content_directory_render_classification_registry_script();
}

function wp_seed_content_directory_render_classification_registry_script()
{
    static $rendered = false;
    if ($rendered) {
        return;
    }
    $rendered = true;
    ?>
    <script>
    document.addEventListener('click', function (event) {
        var button = event.target.closest('[data-wpsck-classification-add],[data-wpsck-classification-remove]');
        if (!button) return;
        event.preventDefault();
        var kind = button.getAttribute('data-wpsck-classification-add');
        if (kind) {
            var list = document.querySelector('[data-wpsck-classification-list="' + kind + '"]');
            var template = document.querySelector('[data-wpsck-classification-template="' + kind + '"]');
            var index = list.querySelectorAll('[data-wpsck-classification-row]').length;
            list.insertAdjacentHTML('beforeend', template.innerHTML.replace(/__INDEX__/g, index));
            return;
        }
        var row = button.closest('[data-wpsck-classification-row]');
        var usage = parseInt(button.getAttribute('data-wpsck-classification-usage') || '0', 10);
        var isNew = '1' === row.getAttribute('data-wpsck-classification-new');
        var label = button.getAttribute('data-wpsck-classification-label') || row.getAttribute('data-wpsck-classification-slug-value');
        if (usage > 0) {
            window.alert('<?php echo esc_js(__('Cette valeur est utilisée par des fiches. Elle ne peut pas être supprimée. Désactivez-la d’abord ou migrez les données.', 'wp-seed-content-kit')); ?>');
            return;
        }
        if (!isNew && !window.confirm('<?php echo esc_js(__('Supprimer définitivement cette valeur ? Cette action est possible uniquement car aucune fiche ne l’utilise.', 'wp-seed-content-kit')); ?>' + '\n\n' + label)) {
            return;
        }
        if (!isNew) {
            var marker = document.createElement('input');
            marker.type = 'hidden';
            marker.name = 'wp_seed_content_directory_' + row.getAttribute('data-wpsck-classification-kind') + '_registry_remove[]';
            marker.value = row.getAttribute('data-wpsck-classification-slug-value');
            row.closest('form').appendChild(marker);
        }
        row.remove();
    });
    </script>
    <?php
}

function wp_seed_content_directory_render_profile_type_registry_settings()
{
    wp_seed_content_directory_render_classification_settings('profile_type');
}
add_action('wp_seed_content_kit_render_configuration_sections', 'wp_seed_content_directory_render_profile_type_registry_settings', 5);

function wp_seed_content_directory_render_status_registry_settings()
{
    wp_seed_content_directory_render_classification_settings('status');
}
add_action('wp_seed_content_kit_render_configuration_sections', 'wp_seed_content_directory_render_status_registry_settings', 6);

function wp_seed_content_directory_save_classification_registries()
{
    if (!current_user_can('manage_wp_seed_content_kit')
        || empty($_POST['wp_seed_content_kit_modules_nonce'])
        || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wp_seed_content_kit_modules_nonce'])), 'wp_seed_content_kit_save_modules')) {
        return;
    }
    foreach (wp_seed_content_directory_classification_definitions() as $kind => $definition) {
        if (empty($_POST['wp_seed_content_directory_' . $kind . '_registry_present'])) {
            continue;
        }
        $key = 'wp_seed_content_directory_' . $kind . '_registry';
        $posted = isset($_POST[$key]) ? wp_unslash($_POST[$key]) : array();
        $remove_key = $key . '_remove';
        $requested = isset($_POST[$remove_key]) ? wp_unslash($_POST[$remove_key]) : array();
        $result = wp_seed_content_directory_apply_classification_removals(
            $kind,
            $posted,
            wp_seed_content_directory_get_classification_registry($kind, false),
            $requested
        );
        update_option($definition['option'], array_values($result['settings']));
    }
}
add_action('wp_seed_content_kit_save_configuration_sections', 'wp_seed_content_directory_save_classification_registries', 5);
