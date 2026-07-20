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

function wp_seed_content_directory_get_safe_tel_href($phone)
{
    $phone = wp_seed_content_directory_sanitize_phone($phone);
    if ('' === $phone) {
        return '';
    }

    $href = preg_replace('/[^0-9+]/', '', $phone);
    return preg_match('/\d/', $href) ? 'tel:' . $href : '';
}

function wp_seed_content_directory_render_native_card($data)
{
    if (!is_array($data)) {
        return '';
    }

    $photo = isset($data['photo']) && is_array($data['photo']) ? $data['photo'] : null;
    $contacts = isset($data['contacts']) && is_array($data['contacts']) ? $data['contacts'] : array();
    $location = wp_seed_content_directory_get_location_label(isset($data['location']) ? $data['location'] : array());

    ob_start();
    ?>
    <article class="wp-seed-directory-card">
        <div class="wp-seed-directory-card__media">
            <?php if ($photo && !empty($photo['url'])) : ?>
                <img class="wp-seed-directory-card__photo" src="<?php echo esc_url($photo['url']); ?>" alt="<?php echo esc_attr(isset($photo['alt']) ? $photo['alt'] : ''); ?>"<?php echo !empty($photo['width']) ? ' width="' . (int) $photo['width'] . '"' : ''; ?><?php echo !empty($photo['height']) ? ' height="' . (int) $photo['height'] . '"' : ''; ?> loading="lazy">
            <?php else : ?>
                <span class="wp-seed-directory-card__photo-placeholder" aria-hidden="true"></span>
            <?php endif; ?>
        </div>
        <div class="wp-seed-directory-card__body">
            <h3 class="wp-seed-directory-card__name"><?php echo esc_html($data['name']); ?></h3>
            <?php if (!empty($data['status_label'])) : ?><p class="wp-seed-directory-card__status"><?php echo esc_html($data['status_label']); ?></p><?php endif; ?>
            <?php if ('' !== $location) : ?><p class="wp-seed-directory-card__location"><?php echo esc_html($location); ?></p><?php endif; ?>
            <?php if (!empty($data['bio'])) : ?><p class="wp-seed-directory-card__bio"><?php echo nl2br(esc_html($data['bio'])); ?></p><?php endif; ?>
            <?php if (!empty($contacts)) : ?>
                <ul class="wp-seed-directory-card__contacts">
                    <?php if (!empty($contacts['phone']) && wp_seed_content_directory_get_safe_tel_href($contacts['phone'])) : ?><li><a href="<?php echo esc_attr(wp_seed_content_directory_get_safe_tel_href($contacts['phone'])); ?>"><?php echo esc_html($contacts['phone']); ?></a></li><?php endif; ?>
                    <?php if (!empty($contacts['email'])) : ?><li><a href="<?php echo esc_attr('mailto:' . sanitize_email($contacts['email'])); ?>"><?php echo esc_html($contacts['email']); ?></a></li><?php endif; ?>
                    <?php foreach (array('website' => __('Site internet', 'wp-seed-content-kit'), 'facebook' => __('Facebook', 'wp-seed-content-kit'), 'instagram' => __('Instagram', 'wp-seed-content-kit')) as $key => $label) : ?>
                        <?php if (!empty($contacts[$key])) : ?><li><a href="<?php echo esc_url($contacts[$key]); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($label); ?></a></li><?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </article>
    <?php
    return ob_get_clean();
}

function wp_seed_content_directory_render_entry($data, $template)
{
    $fallback = wp_seed_content_directory_render_native_card($data);
    if ('' === $template) {
        return array('html' => $fallback, 'native' => true);
    }

    try {
        $result = wp_seed_content_kit_render_template($template, 'directory', wp_seed_content_directory_get_template_context($data));
        if ($result instanceof WP_Seed_Content_Kit_Render_Result && $result->is_success() && '' !== trim($result->get_html())) {
            return array(
                'html' => '<article class="wp-seed-directory-template-card">' . $result->get_html() . '</article>',
                'native' => false,
            );
        }
    } catch (Throwable $error) {
    }

    return array('html' => $fallback, 'native' => true);
}
