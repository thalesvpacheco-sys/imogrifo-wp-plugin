<?php
declare(strict_types=1);

namespace ImoGrifo\Elementor;

if (! defined('ABSPATH')) { exit; }

final class Bootstrap
{
    public static function init(): void
    {
        // Categoria "Imo Grifo"
        add_action('elementor/elements/categories_registered', [__CLASS__, 'register_category'], 10, 1);

        // Widgets
        add_action('elementor/widgets/register', [__CLASS__, 'register_widgets'], 10, 1);

        // Registra assets (para editor e frontend)
        add_action('elementor/frontend/after_register_styles', [__CLASS__, 'register_styles']);
        add_action('elementor/frontend/after_register_scripts', [__CLASS__, 'register_scripts']);

        // No editor, garanta os assets carregados
        add_action('elementor/editor/after_enqueue_scripts', [__CLASS__, 'enqueue_editor_assets']);

        // Dynamic Tags
        $dt_bootstrap = IMOGRIFO_PATH . 'includes/elementor/dynamictags/Bootstrap.php';
        if (is_readable($dt_bootstrap)) {
            require_once $dt_bootstrap;
            \ImoGrifo\DynamicTags\Bootstrap::init();
        }
    }

    public static function register_category($elements_manager): void
    {
        $elements_manager->add_category('imo-grifo', [
            'title' => __('Imo Grifo', 'imo-grifo'),
            'icon'  => 'fa fa-plug',
        ], 1);
    }

    public static function register_widgets($widgets_manager): void
    {
        // Filtros (Archive)
        $file = IMOGRIFO_PATH . 'includes/elementor/widgets/Filters.php';
        if (is_readable($file)) {
            require_once $file;
            $widgets_manager->register(new \ImoGrifo\Elementor\Widgets\Filters());
        }
    }

    public static function register_styles(): void
    {
        $css = IMOGRIFO_PATH . 'assets/css/filters.css';
        wp_register_style(
            'imo-filters-css',
            plugins_url('assets/css/filters.css', IMOGRIFO_FILE),
            [],
            is_readable($css) ? (string) filemtime($css) : '1.0.0'
        );
    }

    public static function register_scripts(): void
    {
        $js = IMOGRIFO_PATH . 'assets/js/filters.js';
        wp_register_script(
            'imo-filters-js',
            plugins_url('assets/js/filters.js', IMOGRIFO_FILE),
            ['jquery'],
            is_readable($js) ? (string) filemtime($js) : '1.0.0',
            true
        );
    }

    public static function enqueue_editor_assets(): void
    {
        wp_enqueue_style('imo-filters-css');
        wp_enqueue_script('imo-filters-js');
    }
}
