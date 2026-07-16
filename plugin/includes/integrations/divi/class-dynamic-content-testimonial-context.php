<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Experimental Divi 5 Dynamic Content source for testimonial context.
 */
class WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Context extends WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Base
{
    public function get_name(): string
    {
        return 'wp_seed_content_testimonial_context';
    }

    public function get_label(): string
    {
        return __('Information complémentaire', 'wp-seed-content-kit');
    }

    protected function get_dynamic_data_field_id(): string
    {
        return 'testimonial.context';
    }
}
