<?php
declare(strict_types=1);

namespace ImoGrifo;

if (! defined('ABSPATH')) { exit; }

final class Plugin
{
    public function __construct()
    {
        add_action('plugins_loaded', function () { $this->boot(); });
    }

    public function boot(): void
    {
        // i18n
        $base_for_i18n = defined('IMOGRIFO_FILE') ? IMOGRIFO_FILE : __FILE__;
        load_plugin_textdomain('imo-grifo', false, dirname(plugin_basename($base_for_i18n)) . '/languages');

        // Autoloader
        $this->boot_autoloader();

        // CPT / Taxonomias
        add_action('init', function () {
            $pt = IMOGRIFO_PATH . 'includes/PostType.php';
            if (is_readable($pt)) {
                require_once $pt;
                if (class_exists('\ImoGrifo\PostType')) {
                    $o = new \ImoGrifo\PostType();
                    if (method_exists($o, 'boot')) { $o->boot(); }
                    elseif (method_exists($o, 'register')) { $o->register(); }
                }
            }

            $tx = IMOGRIFO_PATH . 'includes/Taxonomies.php';
            if (is_readable($tx)) {
                require_once $tx;
                if (class_exists('\ImoGrifo\Taxonomies')) {
                    $o = new \ImoGrifo\Taxonomies();
                    if (method_exists($o, 'boot')) { $o->boot(); }
                    elseif (method_exists($o, 'register')) { $o->register(); }
                }
            }
        }, 5);

        // Seeds (opcional)
        add_action('init', function () {
            $sd = IMOGRIFO_PATH . 'includes/Seeds.php';
            if (is_readable($sd)) {
                require_once $sd;
                if (class_exists('\ImoGrifo\Seeds')) {
                    $o = new \ImoGrifo\Seeds();
                    if (method_exists($o, 'boot')) { $o->boot(); }
                    elseif (method_exists($o, 'run')) { $o->run(); }
                }
            }
        }, 20);

        // Elementor: suporte
        $this->ensure_elementor_support();

        // -------- BOOTSTRAP "OFICIAL" DO ELEMENTOR (se existir) ----------
        add_action('plugins_loaded', function () {
            if (! did_action('elementor/loaded')) { return; }

            foreach ([
                IMOGRIFO_PATH . 'includes/elementor/Bootstrap.php',
                IMOGRIFO_PATH . 'includes/Elementor/Bootstrap.php',
            ] as $file) {
                if (is_readable($file)) {
                    require_once $file;
                    if (class_exists('\ImoGrifo\Elementor\Bootstrap')) {
                        \ImoGrifo\Elementor\Bootstrap::init();
                    }
                    break;
                }
            }
        }, 30);

        // -------- FALLBACK: registra categoria, widget e assets diretamente ----------
        add_action('elementor/elements/categories_registered', [__CLASS__, 'register_elementor_category'], 9);
        add_action('elementor/frontend/after_register_styles', [__CLASS__, 'register_widget_styles']);
        add_action('elementor/frontend/after_register_scripts', [__CLASS__, 'register_widget_scripts']);
        add_action('elementor/editor/after_enqueue_scripts', [__CLASS__, 'enqueue_widget_in_editor']);
        add_action('elementor/widgets/register', [__CLASS__, 'register_filters_widget'], 9);

        // Filtros no archive (q / cidade / status|status_obra)
        add_action('pre_get_posts', [__CLASS__, 'apply_archive_filters']);
    }

    // ----------------- Helpers -----------------
    private static function firstReadable(array $paths): ?string
    {
        foreach ($paths as $rel) {
            $file = IMOGRIFO_PATH . $rel;
            if (is_readable($file)) { return $file; }
        }
        return null;
    }

    // ----------------- Elementor (fallback) -----------------
    public static function register_elementor_category($elements_manager): void
    {
        if (! did_action('elementor/loaded')) { return; }
        $elements_manager->add_category('imo-grifo', [
            'title' => __('Imo Grifo', 'imo-grifo'),
            'icon'  => 'fa fa-plug',
        ], 1);
    }

    public static function register_widget_styles(): void
    {
        $css_file = IMOGRIFO_PATH . 'assets/css/filters.css';
        wp_register_style(
            'imo-filters-css',
            plugins_url('assets/css/filters.css', IMOGRIFO_FILE),
            [],
            is_readable($css_file) ? (string) filemtime($css_file) : '1.0.0'
        );
    }

    public static function register_widget_scripts(): void
    {
        $js_file = IMOGRIFO_PATH . 'assets/js/filters.js';
        wp_register_script(
            'imo-filters-js',
            plugins_url('assets/js/filters.js', IMOGRIFO_FILE),
            ['jquery'],
            is_readable($js_file) ? (string) filemtime($js_file) : '1.0.0',
            true
        );
    }

    public static function enqueue_widget_in_editor(): void
    {
        wp_enqueue_style('imo-filters-css');
        wp_enqueue_script('imo-filters-js');
    }

    public static function register_filters_widget($widgets_manager): void
    {
        if (! did_action('elementor/loaded')) { return; }

        $path = self::firstReadable([
            'includes/Elementor/Widgets/Filters.php',
            'includes/elementor/Widgets/Filters.php',
            'includes/Elementor/widgets/Filters.php',
            'includes/elementor/widgets/Filters.php',
        ]);

        if ($path) {
            require_once $path;
            if (class_exists('\ImoGrifo\Elementor\Widgets\Filters')) {
                // garante assets disponíveis para o widget
                self::register_widget_styles();
                self::register_widget_scripts();
                $widgets_manager->register(new \ImoGrifo\Elementor\Widgets\Filters());
            }
        }
    }

    // ----------------- Autoloader -----------------
    private function boot_autoloader(): void
    {
        $auto = IMOGRIFO_PATH . 'includes/Autoloader.php';
        if (is_readable($auto)) {
            require_once $auto;
            if (class_exists('\ImoGrifo\Autoloader')) {
                if (method_exists('\ImoGrifo\Autoloader', 'init')) {
                    \ImoGrifo\Autoloader::init(); return;
                }
                if (method_exists('\ImoGrifo\Autoloader', 'register')) {
                    \ImoGrifo\Autoloader::register(); return;
                }
            }
        }

        spl_autoload_register(static function (string $class): void {
            $prefix = 'ImoGrifo\\';
            if (strncmp($class, $prefix, strlen($prefix)) !== 0) { return; }
            $rel  = substr($class, strlen($prefix));
            $file = IMOGRIFO_PATH . 'includes/' . str_replace('\\', '/', $rel) . '.php';
            if (is_readable($file)) { require_once $file; }
        });
    }

    // ----------------- Elementor support -----------------
    private function ensure_elementor_support(): void
    {
        add_filter('elementor/editor/post_types', static function (array $types) {
            $want = ['page', 'post', 'empreendimento'];
            return array_values(array_unique(array_merge($types, $want)));
        });

        add_action('admin_init', static function () {
            if (! current_user_can('manage_options')) { return; }
            $opt = get_option('elementor_cpt_support');
            if (! is_array($opt)) { $opt = []; }
            foreach (['page','post','empreendimento'] as $pt) {
                if (! in_array($pt, $opt, true)) { $opt[] = $pt; }
            }
            update_option('elementor_cpt_support', $opt);
        });
    }

    // ----------------- Filtro no archive -----------------
    public static function apply_archive_filters(\WP_Query $q): void
    {
        if (is_admin() || ! $q->is_main_query()) { return; }
        if (! $q->is_post_type_archive('empreendimento')) { return; }

        if (isset($_GET['q']) && $_GET['q'] !== '') {
            $q->set('s', sanitize_text_field(wp_unslash($_GET['q'])));
        }

        $tax_query = [];

        if (! empty($_GET['cidade'])) {
            $tax_query[] = [
                'taxonomy' => 'cidade',
                'field'    => 'slug',
                'terms'    => sanitize_text_field(wp_unslash($_GET['cidade'])),
            ];
        }

        $status_slug = '';
        $status_tax  = '';

        if (! empty($_GET['status_obra'])) {
            $status_slug = sanitize_text_field(wp_unslash($_GET['status_obra']));
            $status_tax  = taxonomy_exists('status_obra') ? 'status_obra' : 'status';
        } elseif (! empty($_GET['status'])) {
            $status_slug = sanitize_text_field(wp_unslash($_GET['status']));
            $status_tax  = taxonomy_exists('status') ? 'status' : 'status_obra';
        }

        if ($status_slug !== '' && $status_tax !== '') {
            $tax_query[] = [
                'taxonomy' => $status_tax,
                'field'    => 'slug',
                'terms'    => $status_slug,
            ];
        }

        if ($tax_query) {
            $q->set('tax_query', $tax_query);
        }
    }
}
