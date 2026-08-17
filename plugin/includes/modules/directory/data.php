<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_directory_get_location_label($location)
{
    if (!is_array($location)) {
        return '';
    }

    $locality = trim(implode(' ', array_filter(array(
        isset($location['postal_code']) ? $location['postal_code'] : '',
        isset($location['city']) ? $location['city'] : '',
    ))));
    $parts = array_filter(array(
        $locality,
        isset($location['department']) ? $location['department'] : '',
        isset($location['country']) ? $location['country'] : '',
    ));

    return implode(' · ', $parts);
}

function wp_seed_content_directory_get_professional_label($post_id)
{
    if (function_exists('wp_seed_content_directory_get_builder_meta')) {
        return (string) wp_seed_content_directory_get_builder_meta($post_id, 'seed_directory_professional_label');
    }

    return sanitize_text_field(get_post_meta($post_id, '_seed_directory_profession', true));
}

function wp_seed_content_directory_get_public_contacts($post_id)
{
    $contacts = array();
    if (function_exists('wp_seed_content_directory_get_public_contact_rows')) {
        foreach (wp_seed_content_directory_get_public_contact_rows(absint($post_id)) as $contact) {
            if (!isset($contacts[$contact['type']])) {
                $contacts[$contact['type']] = $contact['value'];
            }
        }
        return $contacts;
    }

    foreach (wp_seed_content_directory_get_contact_definitions() as $type => $contact) {
        if ('1' !== get_post_meta($post_id, $contact['visible_key'], true)) {
            continue;
        }
        $value = wp_seed_content_directory_normalize_contact_value($contact['key'], get_post_meta($post_id, $contact['key'], true));
        if ('' !== $value) {
            $contacts[$type] = $value;
        }
    }
    return $contacts;
}

function wp_seed_content_directory_render_full_presentation($content)
{
    $content = (string) $content;
    if ('' === trim($content)) {
        return '';
    }

    $rendered = apply_filters('the_content', $content);
    if (function_exists('strip_shortcodes')) {
        $rendered = strip_shortcodes($rendered);
    }
    if (function_exists('wp_kses_post')) {
        $rendered = wp_kses_post($rendered);
    }

    return trim((string) $rendered);
}

function wp_seed_content_directory_get_admin_data($post_id)
{
    $post_id = absint($post_id);
    $post = get_post($post_id);
    if (!$post || 'seed_directory' !== $post->post_type || !current_user_can('edit_seed_directory_entry', $post_id)) {
        return new WP_Error('wp_seed_content_directory_forbidden', __('Accès refusé à cette fiche.', 'wp-seed-content-kit'));
    }

    $data = array(
        'id' => $post_id,
        'name' => (string) $post->post_title,
        'summary' => (string) $post->post_excerpt,
        'professional_label' => wp_seed_content_directory_get_professional_label($post_id),
        'presentation' => (string) $post->post_excerpt,
        'full_presentation' => isset($post->post_content) ? (string) $post->post_content : '',
        'publicly_listed' => '1' === get_post_meta($post_id, '_seed_directory_publicly_listed', true),
        'order' => (int) $post->menu_order,
        'wordpress_status' => (string) $post->post_status,
        'photo_id' => (int) get_post_thumbnail_id($post_id),
    );
    foreach (wp_seed_content_directory_get_meta_definitions() as $key => $definition) {
        $data[substr($key, strlen('_seed_directory_'))] = wp_seed_content_directory_get_meta_value($post_id, $key);
    }

    return $data;
}
function wp_seed_content_directory_get_public_data($post_id)
{
    $post_id = absint($post_id);
    if (!wp_seed_content_directory_is_publicly_eligible($post_id)) {
        return false;
    }

    $post = get_post($post_id);
    if (!$post) {
        return false;
    }

    $status = wp_seed_content_directory_get_meta_value($post_id, '_seed_directory_status');
    $statuses = function_exists('wp_seed_content_directory_classification_options')
        ? wp_seed_content_directory_classification_options('status', false)
        : wp_seed_content_directory_get_statuses();
    $profile_types = wp_seed_content_directory_get_meta_value(
        $post_id,
        '_seed_directory_profile_types'
    );
    $profile_type_labels = wp_seed_content_directory_get_profile_type_labels($profile_types);
    $seeking_models = '1' === wp_seed_content_directory_get_meta_value(
        $post_id,
        '_seed_directory_seeking_models'
    );
    $photo = null;
    $thumbnail_id = (int) get_post_thumbnail_id($post_id);
    if ($thumbnail_id > 0) {
        $image = wp_get_attachment_image_src($thumbnail_id, 'large');
        if (is_array($image) && !empty($image[0])) {
            $photo = array(
                'id' => $thumbnail_id,
                'url' => esc_url_raw($image[0], array('http', 'https')),
                'alt' => sanitize_text_field(get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true)),
                'width' => isset($image[1]) ? absint($image[1]) : 0,
                'height' => isset($image[2]) ? absint($image[2]) : 0,
            );
        }
    }
    $summary = sanitize_textarea_field($post->post_excerpt);
    $presentation_parts = wp_seed_content_split_wordpress_more(
        isset($post->post_content) ? $post->post_content : ''
    );
    $presentation = wp_seed_content_directory_render_full_presentation($presentation_parts['full']);
    $presentation_intro = wp_seed_content_directory_render_full_presentation($presentation_parts['intro']);
    $presentation_more = wp_seed_content_directory_render_full_presentation($presentation_parts['more']);

    $location = array(
        'city' => wp_seed_content_directory_get_meta_value($post_id, '_seed_directory_city'),
        'postal_code' => wp_seed_content_directory_get_meta_value($post_id, '_seed_directory_postal_code'),
        'department' => wp_seed_content_directory_get_meta_value($post_id, '_seed_directory_department'),
        'country' => wp_seed_content_directory_get_meta_value($post_id, '_seed_directory_country'),
    );
    $contact_rows = function_exists('wp_seed_content_directory_get_public_contact_rows')
        ? wp_seed_content_directory_get_public_contact_rows($post_id)
        : array();
    $contacts = wp_seed_content_directory_get_public_contacts($post_id);
    $contact_hrefs = array();
    foreach ($contact_rows as $contact_row) {
        if (!isset($contact_hrefs[$contact_row['type']])) {
            $contact_hrefs[$contact_row['type']] = isset($contact_row['href'])
                ? (string) $contact_row['href']
                : '';
        }
    }

    $data = array(
        'id' => $post_id,
        'name' => sanitize_text_field($post->post_title),
        'professional_label' => wp_seed_content_directory_get_professional_label($post_id),
        'photo' => $photo,
        'summary' => $summary,
        'bio' => $summary,
        'presentation' => $presentation,
        'full_presentation' => $presentation,
        'presentation_intro' => $presentation_intro,
        'presentation_more' => $presentation_more,
        'has_more' => $presentation_parts['has_more'],
        'publicly_listed' => true,
        'status' => $status,
        'status_label' => isset($statuses[$status]) ? $statuses[$status] : '',
        'profile_types' => $profile_types,
        'profile_type_labels' => $profile_type_labels,
        'profile_types_label' => implode(', ', $profile_type_labels),
        'seeking_models' => $seeking_models,
        'seeking_models_label' => $seeking_models
            ? __('Recherche de modèles', 'wp-seed-content-kit')
            : '',
        'location' => $location,
        'location_label' => wp_seed_content_directory_get_location_label($location),
        'featured' => '1' === wp_seed_content_directory_get_meta_value($post_id, '_seed_directory_featured'),
        'display_order' => max(0, (int) $post->menu_order),
        'contacts' => $contacts,
        'contact_rows' => $contact_rows,
    );

    if (function_exists('wp_seed_content_directory_individual_contact_provider_definitions')) {
        $provider_definitions = wp_seed_content_directory_individual_contact_provider_definitions();
        foreach ($provider_definitions as $definition) {
            $slug = $definition['slug'];
            $data[$slug] = isset($contacts[$slug]) ? $contacts[$slug] : '';
        }
        foreach ($provider_definitions as $definition) {
            $slug = $definition['slug'];
            if (!empty($definition['has_href'])) {
                $data[$slug . '_href'] = isset($contact_hrefs[$slug]) ? $contact_hrefs[$slug] : '';
            }
        }
    }
    $data['anchor'] = 'annuaire-' . $post_id;

    return $data;
}
