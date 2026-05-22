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

        // Autocomplete AJAX
        $sc = IMOGRIFO_PATH . 'includes/rest/SuggestController.php';
        if (is_readable($sc)) {
            require_once $sc;
            if (class_exists('\ImoGrifo\Rest\SuggestController')) {
                \ImoGrifo\Rest\SuggestController::boot();
            }
        }

        // Elementor: suporte
        $this->ensure_elementor_support();

        // -------- BOOTSTRAP "OFICIAL" DO ELEMENTOR (se existir) ----------
        add_action('plugins_loaded', function () {
            if (! did_action('elementor/loaded')) { return; }

            $file = IMOGRIFO_PATH . 'includes/elementor/Bootstrap.php';
            if (is_readable($file)) {
                require_once $file;
                if (class_exists('\ImoGrifo\Elementor\Bootstrap')) {
                    \ImoGrifo\Elementor\Bootstrap::init();
                }
            }
        }, 30);

        // Filtros no archive (q / cidade / status|status_obra)
        add_action('pre_get_posts', [__CLASS__, 'apply_archive_filters']);

        // Filtros para Elementor Loop Grid com Query ID "imo_archive"
        add_action('elementor/query/imo_archive', [$this, 'apply_loop_grid_filters'], 10, 2);
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

    // ----------------- Filtro no archive (query principal) -----------------
    public static function apply_archive_filters(\WP_Query $q): void
    {
        if (is_admin() || ! $q->is_main_query()) { return; }
        if (! $q->is_post_type_archive('empreendimento')) { return; }
        self::apply_filters_to_query($q);
    }

    // ----------------- Filtro para Elementor Loop Grid -----------------
    public function apply_loop_grid_filters(\WP_Query $q): void
    {
        self::apply_filters_to_query($q);
    }

    // ----------------- Lógica compartilhada de filtros GET -----------------
    private static function apply_filters_to_query(\WP_Query $q): void
    {
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
