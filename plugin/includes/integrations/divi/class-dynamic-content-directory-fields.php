<?php

if (!defined('ABSPATH')) {
    exit;
}

abstract class WP_Seed_Content_Divi_Dynamic_Content_Directory_Field extends WP_Seed_Content_Divi_Dynamic_Content_Directory_Base
{
    protected $source_name = '';
    protected $label = '';
    protected $field_id = '';
    protected $content_type = 'text';

    public function get_name(): string { return $this->source_name; }
    public function get_label(): string { return __($this->label, 'wp-seed-content-kit'); }
    protected function get_dynamic_data_field_id(): string { return $this->field_id; }
    protected function get_dynamic_content_type(): string { return $this->content_type; }
}

class WP_Seed_Content_Divi_Dynamic_Content_Directory_Visual extends WP_Seed_Content_Divi_Dynamic_Content_Directory_Field { protected $source_name = 'loop_wpsck_directory_visual'; protected $label = 'Visuel'; protected $field_id = 'directory.photo'; protected $content_type = 'image'; }
class WP_Seed_Content_Divi_Dynamic_Content_Directory_Name extends WP_Seed_Content_Divi_Dynamic_Content_Directory_Field { protected $source_name = 'loop_wpsck_directory_name'; protected $label = 'Nom'; protected $field_id = 'directory.name'; }
class WP_Seed_Content_Divi_Dynamic_Content_Directory_Professional_Label extends WP_Seed_Content_Divi_Dynamic_Content_Directory_Field { protected $source_name = 'loop_wpsck_directory_professional_label'; protected $label = 'Intitulé professionnel'; protected $field_id = 'directory.professional_label'; }
class WP_Seed_Content_Divi_Dynamic_Content_Directory_Summary extends WP_Seed_Content_Divi_Dynamic_Content_Directory_Field { protected $source_name = 'loop_wpsck_directory_summary'; protected $label = 'Résumé'; protected $field_id = 'directory.summary'; }
class WP_Seed_Content_Divi_Dynamic_Content_Directory_Presentation extends WP_Seed_Content_Divi_Dynamic_Content_Directory_Field { protected $source_name = 'loop_wpsck_directory_presentation'; protected $label = 'Présentation'; protected $field_id = 'directory.presentation'; }
class WP_Seed_Content_Divi_Dynamic_Content_Directory_Presentation_Intro extends WP_Seed_Content_Divi_Dynamic_Content_Directory_Field { protected $source_name = 'loop_wpsck_directory_presentation_intro'; protected $label = 'Introduction'; protected $field_id = 'directory.presentation_intro'; }
class WP_Seed_Content_Divi_Dynamic_Content_Directory_Presentation_More extends WP_Seed_Content_Divi_Dynamic_Content_Directory_Field { protected $source_name = 'loop_wpsck_directory_presentation_more'; protected $label = 'Suite de présentation'; protected $field_id = 'directory.presentation_more'; }
class WP_Seed_Content_Divi_Dynamic_Content_Directory_Status extends WP_Seed_Content_Divi_Dynamic_Content_Directory_Field { protected $source_name = 'loop_wpsck_directory_status'; protected $label = 'Statut'; protected $field_id = 'directory.status'; }
class WP_Seed_Content_Divi_Dynamic_Content_Directory_Profile_Types extends WP_Seed_Content_Divi_Dynamic_Content_Directory_Field { protected $source_name = 'loop_wpsck_directory_profile_types'; protected $label = 'Types de profil'; protected $field_id = 'directory.profile_types'; }
class WP_Seed_Content_Divi_Dynamic_Content_Directory_Seeking_Models extends WP_Seed_Content_Divi_Dynamic_Content_Directory_Field { protected $source_name = 'loop_wpsck_directory_seeking_models'; protected $label = 'Recherche de modèles'; protected $field_id = 'directory.seeking_models'; }
class WP_Seed_Content_Divi_Dynamic_Content_Directory_Location extends WP_Seed_Content_Divi_Dynamic_Content_Directory_Field { protected $source_name = 'loop_wpsck_directory_location'; protected $label = 'Localisation'; protected $field_id = 'directory.location'; }

class WP_Seed_Content_Divi_Dynamic_Content_Directory_Contact_Field extends WP_Seed_Content_Divi_Dynamic_Content_Directory_Field
{
    public function __construct($definition, $href = false)
    {
        $this->source_name = $href ? $definition['href_provider_id'] : $definition['display_provider_id'];
        $this->label = $definition['label'] . ($href ? ' — Lien' : '');
        $this->field_id = $href ? $definition['href_field_id'] : $definition['display_field_id'];
        $this->content_type = $href ? 'url' : 'text';
    }
}
class WP_Seed_Content_Divi_Dynamic_Content_Directory_Id extends WP_Seed_Content_Divi_Dynamic_Content_Directory_Field { protected $source_name = 'loop_wpsck_directory_id'; protected $label = 'ID'; protected $field_id = 'directory.id'; }
class WP_Seed_Content_Divi_Dynamic_Content_Directory_Anchor extends WP_Seed_Content_Divi_Dynamic_Content_Directory_Field { protected $source_name = 'loop_wpsck_directory_anchor'; protected $label = 'Ancre'; protected $field_id = 'directory.anchor'; }
