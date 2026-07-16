<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Experimental Divi 5 Dynamic Content source for testimonial dates.
 */
class WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Date extends WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Base
{
    public function get_name(): string
    {
        return 'wp_seed_content_testimonial_date';
    }

    public function get_label(): string
    {
        return __('Date du témoignage', 'wp-seed-content-kit');
    }

    protected function get_dynamic_data_field_id(): string
    {
        return 'testimonial.testimonial_date';
    }
}
