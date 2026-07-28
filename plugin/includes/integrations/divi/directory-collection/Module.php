<?php

namespace WPSeedContentKit\Divi\DirectoryCollection;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Module as DiviModule;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

class Module implements DependencyInterface
{
    public function load()
    {
        add_action('init', array(__CLASS__, 'register_module'));
    }

    public static function register_module()
    {
        ModuleRegistration::register_module(
            __DIR__,
            array('render_callback' => array(__CLASS__, 'render_callback'))
        );
    }

    public static function module_classnames($args)
    {
        $attrs = isset($args['attrs']) ? $args['attrs'] : array();
        $decoration = isset($attrs['module']['decoration'])
            ? $attrs['module']['decoration']
            : array();
        $args['classnamesInstance']->add(
            ElementClassnames::classnames(array('attrs' => $decoration))
        );
    }

    public static function module_styles($args)
    {
        $elements = $args['elements'];
        $settings = isset($args['settings']) ? $args['settings'] : array();
        Style::add(
            array(
                'id' => $args['id'],
                'name' => $args['name'],
                'orderIndex' => $args['orderIndex'],
                'storeInstance' => $args['storeInstance'],
                'styles' => array(
                    $elements->style(
                        array(
                            'attrName' => 'module',
                            'styleProps' => array(
                                'disabledOn' => array(
                                    'disabledModuleVisibility' => isset($settings['disabledModuleVisibility'])
                                        ? $settings['disabledModuleVisibility']
                                        : null,
                                ),
                            ),
                        )
                    ),
                    $elements->style(array('attrName' => 'title')),
                ),
            )
        );
    }

    public static function module_script_data($args)
    {
        $args['elements']->script_data(array('attrName' => 'module'));
    }

    public static function render_callback($attrs, $content, $block, $elements)
    {
        $parsed = isset($block->parsed_block) ? $block->parsed_block : array();
        $id = isset($parsed['id']) ? $parsed['id'] : '';
        $order_index = isset($parsed['orderIndex']) ? $parsed['orderIndex'] : 0;
        $store_instance = isset($parsed['storeInstance']) ? $parsed['storeInstance'] : null;
        $background = ElementComponents::component(
            array(
                'attrs' => isset($attrs['module']['decoration'])
                    ? $attrs['module']['decoration']
                    : array(),
                'id' => $id,
                'orderIndex' => $order_index,
                'storeInstance' => $store_instance,
            )
        );
        $title = $elements->render(array('attrName' => 'title'));
        $collection = \wp_seed_content_render_directory_collection(
            \wp_seed_content_divi_directory_collection_args($attrs),
            true
        );
        $inner = HTMLUtility::render(
            array(
                'tag' => 'div',
                'attributes' => array(
                    'class' => 'wp-seed-divi-directory-collection__inner',
                ),
                'childrenSanitizer' => 'et_core_esc_previously',
                'children' => $title . $collection,
            )
        );

        return DiviModule::render(
            array(
                'orderIndex' => $order_index,
                'storeInstance' => $store_instance,
                'attrs' => $attrs,
                'elements' => $elements,
                'id' => $id,
                'moduleClassName' => 'wp_seed_content_kit_directory_collection',
                'name' => $block->block_type->name,
                'moduleCategory' => $block->block_type->category,
                'classnamesFunction' => array(__CLASS__, 'module_classnames'),
                'stylesComponent' => array(__CLASS__, 'module_styles'),
                'scriptDataComponent' => array(__CLASS__, 'module_script_data'),
                'children' => $background . $inner,
            )
        );
    }
}
