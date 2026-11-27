<?php

if (!defined('ABSPATH')) {
    exit;
}

abstract class WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Loop_Text extends WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Base
{
    protected $source_name = '';
    protected $label = '';
    protected $field_id = '';

    public function get_name(): string
    {
        return $this->source_name;
    }

    public function get_label(): string
    {
        return __($this->label, 'wp-seed-content-kit');
    }

    protected function get_dynamic_data_field_id(): string
    {
        return $this->field_id;
    }
}

class WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Title extends WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Loop_Text
{
    protected $source_name = 'loop_wpsck_testimonial_title';
    protected $label = 'Titre';
    protected $field_id = 'testimonial.title';
}

class WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Summary extends WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Loop_Text
{
    protected $source_name = 'loop_wpsck_testimonial_summary';
    protected $label = 'Résumé';
    protected $field_id = 'testimonial.summary';
}

class WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Intro extends WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Loop_Text
{
    protected $source_name = 'loop_wpsck_testimonial_intro';
    protected $label = 'Introduction';
    protected $field_id = 'testimonial.intro';
}

class WP_Seed_Content_Divi_Dynamic_Content_Testimonial_More extends WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Loop_Text
{
    protected $source_name = 'loop_wpsck_testimonial_more';
    protected $label = 'Suite du témoignage';
    protected $field_id = 'testimonial.more';
}

class WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Has_More extends WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Loop_Text
{
    protected $source_name = 'loop_wpsck_testimonial_has_more';
    protected $label = 'Témoignage avec suite';
    protected $field_id = 'testimonial.has_more';
}

class WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Id extends WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Loop_Text
{
    protected $source_name = 'loop_wpsck_testimonial_id';
    protected $label = 'ID';
    protected $field_id = 'testimonial.id';
}

class WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Anchor extends WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Loop_Text
{
    protected $source_name = 'loop_wpsck_testimonial_anchor';
    protected $label = 'Ancre';
    protected $field_id = 'testimonial.anchor';
}

class WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Anchor_Url extends WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Loop_Text
{
    protected $source_name = 'wp_seed_content_testimonial_anchor_url';
    protected $label = 'Lien vers le témoignage';
    protected $field_id = 'testimonial.anchor_url';

    protected function get_dynamic_content_type(): string
    {
        return 'url';
    }
}
