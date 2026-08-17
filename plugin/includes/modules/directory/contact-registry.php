<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_directory_contact_type_option_key()
{
    return 'wp_seed_content_directory_contact_type_settings';
}

function wp_seed_content_directory_contact_behavior_registry()
{
    return array(
        'phone' => array('label' => __('Téléphone', 'wp-seed-content-kit'), 'value_type' => 'phone', 'href_type' => 'tel'),
        'email' => array('label' => __('E-mail', 'wp-seed-content-kit'), 'value_type' => 'email', 'href_type' => 'mailto'),
        'url' => array('label' => __('Lien web', 'wp-seed-content-kit'), 'value_type' => 'url', 'href_type' => 'url'),
        'linkedin' => array('label' => __('LinkedIn', 'wp-seed-content-kit'), 'value_type' => 'linkedin', 'href_type' => 'url'),
        'whatsapp' => array('label' => __('WhatsApp', 'wp-seed-content-kit'), 'value_type' => 'whatsapp', 'href_type' => 'whatsapp'),
        'address' => array('label' => __('Adresse', 'wp-seed-content-kit'), 'value_type' => 'text', 'href_type' => ''),
        'text' => array('label' => __('Texte sans lien', 'wp-seed-content-kit'), 'value_type' => 'text', 'href_type' => ''),
        'url_or_text' => array('label' => __('Lien ou texte', 'wp-seed-content-kit'), 'value_type' => 'url_or_text', 'href_type' => 'url_if_valid'),
    );
}

function wp_seed_content_directory_contact_type_contracts()
{
    return array(
        'phone' => array('label' => __('Téléphone', 'wp-seed-content-kit'), 'behavior' => 'phone', 'individual_provider' => true, 'condition_name' => 'wpsckDirectoryPhonePresent'),
        'email' => array('label' => __('E-mail', 'wp-seed-content-kit'), 'behavior' => 'email', 'individual_provider' => true, 'condition_name' => 'wpsckDirectoryEmailPresent'),
        'website' => array('label' => __('Site internet', 'wp-seed-content-kit'), 'provider_label' => __('Site', 'wp-seed-content-kit'), 'behavior' => 'url', 'individual_provider' => true, 'condition_name' => 'wpsckDirectoryWebsitePresent'),
        'facebook' => array('label' => __('Facebook', 'wp-seed-content-kit'), 'behavior' => 'url', 'individual_provider' => true, 'condition_name' => 'wpsckDirectoryFacebookPresent'),
        'instagram' => array('label' => __('Instagram', 'wp-seed-content-kit'), 'behavior' => 'url', 'individual_provider' => true, 'condition_name' => 'wpsckDirectoryInstagramPresent'),
        'linkedin' => array('label' => __('LinkedIn', 'wp-seed-content-kit'), 'behavior' => 'linkedin', 'individual_provider' => true, 'condition_name' => 'wpsckDirectoryLinkedinPresent'),
        'whatsapp' => array('label' => __('WhatsApp', 'wp-seed-content-kit'), 'behavior' => 'whatsapp', 'individual_provider' => true, 'condition_name' => 'wpsckDirectoryWhatsappPresent'),
        'address' => array('label' => __('Adresse', 'wp-seed-content-kit'), 'behavior' => 'address', 'individual_provider' => true, 'condition_name' => 'wpsckDirectoryAddressPresent', 'condition_label' => __('WPSCK — Annuaire — Adresse renseignée', 'wp-seed-content-kit')),
        'other' => array('label' => __('Autre', 'wp-seed-content-kit'), 'behavior' => 'url_or_text', 'individual_provider' => false),
    );
}

function wp_seed_content_directory_system_contact_types()
{
    $contracts = wp_seed_content_directory_contact_type_contracts();
    return array(
        'phone' => $contracts['phone'],
        'email' => $contracts['email'],
    );
}

function wp_seed_content_directory_default_contact_type_settings()
{
    return array(
        'phone' => array('slug' => 'phone', 'label' => __('Téléphone', 'wp-seed-content-kit'), 'behavior' => 'phone', 'active' => true, 'order' => 10),
        'email' => array('slug' => 'email', 'label' => __('E-mail', 'wp-seed-content-kit'), 'behavior' => 'email', 'active' => true, 'order' => 20),
    );
}

function wp_seed_content_directory_is_list_array($value)
{
    if (!is_array($value)) {
        return false;
    }
    if (!$value) {
        return true;
    }
    return array_keys($value) === range(0, count($value) - 1);
}

function wp_seed_content_directory_normalize_contact_type_rows($value)
{
    $value = is_array($value) ? $value : array();
    $contracts = wp_seed_content_directory_contact_type_contracts();
    $system = wp_seed_content_directory_system_contact_types();
    $behaviors = wp_seed_content_directory_contact_behavior_registry();
    $rows = array();

    if (!wp_seed_content_directory_is_list_array($value)) {
        $legacy = array();
        foreach ($value as $slug => $row) {
            if (!is_array($row)) {
                continue;
            }
            $row['slug'] = $slug;
            $row['active'] = true;
            $legacy[] = $row;
        }
        $value = $legacy;
    }

    foreach (array_values($value) as $position => $row) {
        if (!is_array($row)) {
            continue;
        }
        $slug = isset($row['slug']) ? sanitize_key((string) $row['slug']) : '';
        $slug = substr($slug, 0, 64);
        if ('' === $slug || isset($rows[$slug])) {
            continue;
        }

        $contract_definition = isset($contracts[$slug]) ? $contracts[$slug] : null;
        $system_definition = isset($system[$slug]) ? $system[$slug] : null;
        $behavior = $system_definition
            ? $system_definition['behavior']
            : (isset($row['behavior'])
                ? sanitize_key((string) $row['behavior'])
                : ($contract_definition ? $contract_definition['behavior'] : 'url_or_text'));
        if (in_array($behavior, array('facebook', 'instagram'), true)) {
            $behavior = 'url';
        }
        if (!isset($behaviors[$behavior])) {
            $behavior = 'url_or_text';
        }

        $fallback_label = $contract_definition ? $contract_definition['label'] : $slug;
        $label = isset($row['label']) ? sanitize_text_field($row['label']) : '';
        $label = '' !== $label ? $label : $fallback_label;
        $rows[$slug] = array(
            'slug' => $slug,
            'label' => $label,
            'behavior' => $behavior,
            'active' => !empty($row['active']),
            'order' => isset($row['order']) ? max(0, (int) $row['order']) : (($position + 1) * 10),
            'individual_provider' => $system_definition
                ? !empty($system_definition['individual_provider'])
                : (array_key_exists('individual_provider', $row)
                    ? !empty($row['individual_provider'])
                    : ($contract_definition && !empty($contract_definition['individual_provider']))),
            'system' => null !== $system_definition,
            '_position' => $position,
        );
    }

    foreach (wp_seed_content_directory_default_contact_type_settings() as $slug => $default) {
        if (!isset($rows[$slug])) {
            $default['system'] = true;
            $default['_position'] = count($rows);
            $rows[$slug] = $default;
        }
    }

    uasort($rows, function ($left, $right) {
        if ($left['order'] === $right['order']) {
            return $left['_position'] - $right['_position'];
        }
        return $left['order'] - $right['order'];
    });
    foreach ($rows as &$row) {
        unset($row['_position']);
    }
    unset($row);

    return $rows;
}

function wp_seed_content_directory_get_contact_type_settings()
{
    $stored = function_exists('get_option')
        ? get_option(wp_seed_content_directory_contact_type_option_key(), null)
        : null;
    if (!is_array($stored)) {
        return wp_seed_content_directory_default_contact_type_settings();
    }
    return wp_seed_content_directory_normalize_contact_type_rows($stored);
}

function wp_seed_content_directory_sanitize_contact_type_settings($value, $previous = null)
{
    $rows = wp_seed_content_directory_normalize_contact_type_rows($value);
    $previous = is_array($previous) ? wp_seed_content_directory_normalize_contact_type_rows($previous) : array();

    foreach ($previous as $slug => $row) {
        if (isset($rows[$slug])) {
            continue;
        }
        $row['active'] = false;
        $rows[$slug] = $row;
    }

    return wp_seed_content_directory_normalize_contact_type_rows(array_values($rows));
}

/**
 * Counts raw canonical rows using each requested contact type.
 *
 * Private, inactive and invalid rows still count because removing their type
 * would orphan stored data even when the row is not publicly projected.
 */
function wp_seed_content_directory_contact_type_usage_counts($slugs = array())
{
    $slugs = array_values(array_unique(array_filter(array_map('sanitize_key', (array) $slugs))));
    $counts = array_fill_keys($slugs, 0);
    if (!$slugs || !function_exists('get_posts') || !function_exists('get_post_meta')) {
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
        $rows = get_post_meta((int) $post_id, wp_seed_content_directory_contacts_meta_key(), true);
        if (!is_array($rows)) {
            continue;
        }
        foreach ($rows as $row) {
            $slug = is_array($row) && isset($row['type']) ? sanitize_key((string) $row['type']) : '';
            if (isset($counts[$slug])) {
                $counts[$slug]++;
            }
        }
    }

    return $counts;
}

/**
 * Applies explicit removals without treating ordinary omitted rows as deletes.
 */
function wp_seed_content_directory_apply_contact_type_removals($value, $previous, $requested, $usage_counts = null)
{
    $previous = wp_seed_content_directory_normalize_contact_type_rows($previous);
    $requested = array_values(array_unique(array_filter(array_map('sanitize_key', (array) $requested))));
    $system = wp_seed_content_directory_system_contact_types();
    $usage_counts = is_array($usage_counts)
        ? $usage_counts
        : wp_seed_content_directory_contact_type_usage_counts($requested);
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

    $settings = wp_seed_content_directory_sanitize_contact_type_settings($filtered, $previous);
    foreach ($removed as $slug => $unused) {
        unset($settings[$slug]);
    }

    return array(
        'settings' => $settings,
        'removed' => array_keys($removed),
        'blocked' => $blocked,
    );
}

function wp_seed_content_directory_contact_type_registry($active_only = false)
{
    $behaviors = wp_seed_content_directory_contact_behavior_registry();
    $types = array();
    foreach (wp_seed_content_directory_get_contact_type_settings() as $slug => $row) {
        if ($active_only && empty($row['active'])) {
            continue;
        }
        $behavior = isset($behaviors[$row['behavior']]) ? $behaviors[$row['behavior']] : $behaviors['url_or_text'];
        $types[$slug] = array_merge($behavior, $row, array(
            'default_order' => (int) $row['order'],
        ));
    }

    $filtered = function_exists('apply_filters')
        ? apply_filters('wp_seed_content_directory_contact_types', $types, $active_only)
        : $types;
    return is_array($filtered) ? $filtered : $types;
}

/**
 * Defines unambiguous individual contact providers from the contact registry.
 *
 * Published provider IDs are permanent independently of deletion protection.
 * Configurable types must remain registered and opted in. Repeatable `other`
 * rows never enter this contract.
 *
 * @return array
 */
function wp_seed_content_directory_individual_contact_provider_definitions()
{
    $contracts = wp_seed_content_directory_contact_type_contracts();
    $system = wp_seed_content_directory_system_contact_types();
    $types = wp_seed_content_directory_contact_type_registry(false);
    $provider_types = array();
    foreach ($system as $slug => $system_definition) {
        if (!empty($system_definition['individual_provider'])) {
            $provider_types[$slug] = isset($types[$slug])
                ? $types[$slug]
                : wp_seed_content_directory_get_contact_type($slug);
        }
    }
    foreach ($types as $slug => $type) {
        if (!isset($provider_types[$slug])) {
            $provider_types[$slug] = $type;
        }
    }
    $definitions = array();

    foreach ($provider_types as $slug => $type) {
        $is_system = isset($system[$slug]);
        $contract = isset($contracts[$slug]) ? $contracts[$slug] : null;
        $enabled = $is_system
            ? !empty($system[$slug]['individual_provider'])
            : (!empty($type['active']) && !empty($type['individual_provider']));
        if (!$enabled || 'other' === $slug) {
            continue;
        }

        $label = $contract && !empty($contract['provider_label'])
            ? $contract['provider_label']
            : $type['label'];
        $condition_name = $contract && !empty($contract['condition_name'])
            ? $contract['condition_name']
            : 'wpsckDirectoryContact' . str_replace(' ', '', ucwords(str_replace(array('-', '_'), ' ', $slug))) . 'Present';
        $has_href = !empty($type['href_type']);

        $definitions[$slug] = array(
            'slug' => $slug,
            'label' => $label,
            'display_field_id' => 'directory.' . $slug,
            'display_provider_id' => 'loop_wpsck_directory_' . $slug,
            'href_field_id' => $has_href ? 'directory.' . $slug . '_href' : '',
            'href_provider_id' => $has_href ? 'loop_wpsck_directory_' . $slug . '_href' : '',
            'condition_name' => $condition_name,
            'condition_label' => $contract && !empty($contract['condition_label'])
                ? $contract['condition_label']
                : sprintf(
                    /* translators: %s: contact type label. */
                    __('WPSCK — Annuaire — %s renseigné', 'wp-seed-content-kit'),
                    $label
                ),
            'has_href' => $has_href,
            'system' => $is_system,
        );
    }

    $filtered = function_exists('apply_filters')
        ? apply_filters('wp_seed_content_directory_individual_contact_providers', $definitions)
        : $definitions;

    return is_array($filtered) ? $filtered : $definitions;
}

function wp_seed_content_directory_get_contact_type($type)
{
    $type = sanitize_key((string) $type);
    $registry = wp_seed_content_directory_contact_type_registry(false);
    if (isset($registry[$type])) {
        return $registry[$type];
    }

    $system = wp_seed_content_directory_system_contact_types();
    $behaviors = wp_seed_content_directory_contact_behavior_registry();
    if (!isset($system[$type]) || !isset($behaviors[$system[$type]['behavior']])) {
        return null;
    }
    return array_merge($behaviors[$system[$type]['behavior']], array(
        'slug' => $type,
        'label' => $system[$type]['label'],
        'behavior' => $system[$type]['behavior'],
        'active' => false,
        'order' => 100,
        'default_order' => 100,
        'system' => true,
    ));
}

function wp_seed_content_directory_is_contact_type_active($type)
{
    $definition = wp_seed_content_directory_get_contact_type($type);
    return $definition && !empty($definition['active']);
}

function wp_seed_content_directory_render_contact_type_settings_row($row, $index, $is_new = false, $usage_count = 0)
{
    $system = wp_seed_content_directory_system_contact_types();
    $behaviors = wp_seed_content_directory_contact_behavior_registry();
    $slug = isset($row['slug']) ? sanitize_key($row['slug']) : '';
    $protected = isset($system[$slug]);
    $prefix = 'wp_seed_content_directory_contact_type_settings[' . $index . ']';
    ?>
    <tr data-wpsck-contact-type-row data-wpsck-contact-type-slug-value="<?php echo esc_attr($slug); ?>" data-wpsck-contact-type-new="<?php echo $is_new ? '1' : '0'; ?>">
        <td>
            <?php if ($is_new) : ?>
                <input class="regular-text" type="text" maxlength="64" name="<?php echo esc_attr($prefix . '[slug]'); ?>" value="" placeholder="<?php esc_attr_e('ex. booking', 'wp-seed-content-kit'); ?>" data-wpsck-contact-type-slug>
            <?php else : ?>
                <code><?php echo esc_html($slug); ?></code>
                <input type="hidden" name="<?php echo esc_attr($prefix . '[slug]'); ?>" value="<?php echo esc_attr($slug); ?>">
            <?php endif; ?>
        </td>
        <td><input class="regular-text" type="text" name="<?php echo esc_attr($prefix . '[label]'); ?>" value="<?php echo esc_attr(isset($row['label']) ? $row['label'] : ''); ?>"></td>
        <td>
            <select name="<?php echo esc_attr($prefix . '[behavior]'); ?>"<?php echo $protected ? ' disabled' : ''; ?>>
                <?php foreach ($behaviors as $behavior => $definition) : ?>
                    <option value="<?php echo esc_attr($behavior); ?>" <?php selected(isset($row['behavior']) ? $row['behavior'] : 'url_or_text', $behavior); ?>><?php echo esc_html($definition['label']); ?></option>
                <?php endforeach; ?>
            </select>
            <?php if ($protected) : ?><input type="hidden" name="<?php echo esc_attr($prefix . '[behavior]'); ?>" value="<?php echo esc_attr($system[$slug]['behavior']); ?>"><?php endif; ?>
        </td>
        <td><label><input type="checkbox" name="<?php echo esc_attr($prefix . '[active]'); ?>" value="1" <?php checked(!empty($row['active'])); ?>> <?php esc_html_e('Actif', 'wp-seed-content-kit'); ?></label></td>
        <td>
            <label><input type="checkbox" name="<?php echo esc_attr($prefix . '[individual_provider]'); ?>" value="1" <?php checked(!empty($row['individual_provider']) || ($protected && !empty($system[$slug]['individual_provider']))); ?><?php echo $protected ? ' disabled' : ''; ?>> <?php esc_html_e('Individuel', 'wp-seed-content-kit'); ?></label>
            <?php if ($protected) : ?><input type="hidden" name="<?php echo esc_attr($prefix . '[individual_provider]'); ?>" value="<?php echo !empty($system[$slug]['individual_provider']) ? '1' : '0'; ?>"><?php endif; ?>
        </td>
        <td>
            <input type="hidden" name="<?php echo esc_attr($prefix . '[order]'); ?>" value="<?php echo absint(isset($row['order']) ? $row['order'] : 0); ?>" data-wpsck-contact-type-order>
            <button type="button" class="button" data-wpsck-contact-type-up aria-label="<?php esc_attr_e('Monter', 'wp-seed-content-kit'); ?>">↑</button>
            <button type="button" class="button" data-wpsck-contact-type-down aria-label="<?php esc_attr_e('Descendre', 'wp-seed-content-kit'); ?>">↓</button>
        </td>
        <td>
            <?php if ($protected) : ?>
                <span class="description"><?php esc_html_e('Type système', 'wp-seed-content-kit'); ?></span>
            <?php else : ?>
                <button type="button" class="button-link-delete" data-wpsck-contact-type-remove data-wpsck-contact-type-usage="<?php echo absint($usage_count); ?>" data-wpsck-contact-type-label="<?php echo esc_attr(isset($row['label']) ? $row['label'] : $slug); ?>"><?php esc_html_e('Supprimer', 'wp-seed-content-kit'); ?></button>
                <?php if (!$is_new && $usage_count > 0) : ?>
                    <p class="description"><?php echo esc_html(sprintf(_n('Utilisé par %d coordonnée.', 'Utilisé par %d coordonnées.', $usage_count, 'wp-seed-content-kit'), $usage_count)); ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </td>
    </tr>
    <?php
}

function wp_seed_content_directory_render_contact_type_settings()
{
    $settings = wp_seed_content_directory_get_contact_type_settings();
    $usage_counts = wp_seed_content_directory_contact_type_usage_counts(array_keys($settings));
    ?>
    <section class="wpsck-directory-contact-types">
        <h2><?php esc_html_e('Annuaire — Types de coordonnées', 'wp-seed-content-kit'); ?></h2>
        <p><?php esc_html_e('Ajoutez les types proposés dans les fiches et dans le provider Divi. Les slugs enregistrés restent immuables afin de préserver les bindings.', 'wp-seed-content-kit'); ?></p>
        <input type="hidden" name="wp_seed_content_directory_contact_types_present" value="1">
        <table class="widefat striped">
            <thead><tr>
                <th><?php esc_html_e('Slug stable', 'wp-seed-content-kit'); ?></th>
                <th><?php esc_html_e('Libellé', 'wp-seed-content-kit'); ?></th>
                <th><?php esc_html_e('Comportement sûr', 'wp-seed-content-kit'); ?></th>
                <th><?php esc_html_e('État', 'wp-seed-content-kit'); ?></th>
                <th><?php esc_html_e('Provider Divi', 'wp-seed-content-kit'); ?></th>
                <th><?php esc_html_e('Ordre', 'wp-seed-content-kit'); ?></th>
                <th><?php esc_html_e('Actions', 'wp-seed-content-kit'); ?></th>
            </tr></thead>
            <tbody data-wpsck-contact-types-list>
                <?php foreach (array_values($settings) as $index => $row) { wp_seed_content_directory_render_contact_type_settings_row($row, $index, false, isset($usage_counts[$row['slug']]) ? $usage_counts[$row['slug']] : 0); } ?>
            </tbody>
        </table>
        <p><button type="button" class="button" data-wpsck-contact-type-add><?php esc_html_e('Ajouter un type', 'wp-seed-content-kit'); ?></button></p>
        <p class="description"><?php esc_html_e('Les types système ne peuvent pas être supprimés. Un type personnalisé utilisé par une coordonnée doit être désactivé ou ses données doivent être migrées avant suppression.', 'wp-seed-content-kit'); ?></p>
        <template data-wpsck-contact-type-template><?php wp_seed_content_directory_render_contact_type_settings_row(array('slug' => '', 'label' => '', 'behavior' => 'url_or_text', 'active' => true, 'individual_provider' => false, 'order' => 0), '__INDEX__', true); ?></template>
    </section>
    <script>
    document.addEventListener('click', function (event) {
        var list = document.querySelector('[data-wpsck-contact-types-list]');
        if (!list) return;
        var button = event.target.closest('[data-wpsck-contact-type-add],[data-wpsck-contact-type-up],[data-wpsck-contact-type-down],[data-wpsck-contact-type-remove]');
        if (!button) return;
        event.preventDefault();
        if (button.hasAttribute('data-wpsck-contact-type-add')) {
            var template = document.querySelector('[data-wpsck-contact-type-template]');
            var index = list.querySelectorAll('[data-wpsck-contact-type-row]').length;
            list.insertAdjacentHTML('beforeend', template.innerHTML.replace(/__INDEX__/g, index));
        } else if (button.hasAttribute('data-wpsck-contact-type-remove')) {
            var removeRow = button.closest('[data-wpsck-contact-type-row]');
            var usage = parseInt(button.getAttribute('data-wpsck-contact-type-usage') || '0', 10);
            var isNew = '1' === removeRow.getAttribute('data-wpsck-contact-type-new');
            var label = button.getAttribute('data-wpsck-contact-type-label') || removeRow.getAttribute('data-wpsck-contact-type-slug-value');
            if (usage > 0) {
                window.alert('<?php echo esc_js(__('Ce type est utilisé par des coordonnées. Il ne peut pas être supprimé. Désactivez-le d’abord ou migrez les données.', 'wp-seed-content-kit')); ?>');
                return;
            }
            if (!isNew && !window.confirm('<?php echo esc_js(__('Supprimer définitivement ce type ? Cette action est possible uniquement car aucune coordonnée ne l’utilise.', 'wp-seed-content-kit')); ?>' + '\n\n' + label)) {
                return;
            }
            if (!isNew) {
                var marker = document.createElement('input');
                marker.type = 'hidden';
                marker.name = 'wp_seed_content_directory_contact_type_remove[]';
                marker.value = removeRow.getAttribute('data-wpsck-contact-type-slug-value');
                list.closest('form').appendChild(marker);
            }
            removeRow.remove();
        } else if (button.hasAttribute('data-wpsck-contact-type-up')) {
            var row = button.closest('[data-wpsck-contact-type-row]');
            if (row.previousElementSibling) list.insertBefore(row, row.previousElementSibling);
        } else if (button.hasAttribute('data-wpsck-contact-type-down')) {
            var downRow = button.closest('[data-wpsck-contact-type-row]');
            if (downRow.nextElementSibling) list.insertBefore(downRow.nextElementSibling, downRow);
        }
        Array.prototype.forEach.call(list.querySelectorAll('[data-wpsck-contact-type-row]'), function (row, index) {
            row.querySelector('[data-wpsck-contact-type-order]').value = (index + 1) * 10;
        });
    });
    </script>
    <?php
}
add_action('wp_seed_content_kit_render_configuration_sections', 'wp_seed_content_directory_render_contact_type_settings');

function wp_seed_content_directory_save_contact_type_settings()
{
    if (empty($_POST['wp_seed_content_directory_contact_types_present'])) {
        return;
    }
    if (!current_user_can('manage_wp_seed_content_kit')
        || empty($_POST['wp_seed_content_kit_modules_nonce'])
        || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wp_seed_content_kit_modules_nonce'])), 'wp_seed_content_kit_save_modules')) {
        return;
    }
    $value = isset($_POST['wp_seed_content_directory_contact_type_settings'])
        ? wp_unslash($_POST['wp_seed_content_directory_contact_type_settings'])
        : array();
    $requested = isset($_POST['wp_seed_content_directory_contact_type_remove'])
        ? wp_unslash($_POST['wp_seed_content_directory_contact_type_remove'])
        : array();
    $result = wp_seed_content_directory_apply_contact_type_removals(
        $value,
        wp_seed_content_directory_get_contact_type_settings(),
        $requested
    );
    update_option(
        wp_seed_content_directory_contact_type_option_key(),
        array_values($result['settings'])
    );
}
add_action('wp_seed_content_kit_save_configuration_sections', 'wp_seed_content_directory_save_contact_type_settings');
