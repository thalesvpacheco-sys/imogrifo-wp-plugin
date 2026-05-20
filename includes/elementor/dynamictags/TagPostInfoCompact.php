<?php
namespace ImoGrifo\DynamicTags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Controls_Manager;
use Elementor\Modules\DynamicTags\Module as DynamicModule;

if (! defined('ABSPATH')) { exit; }

final class TagPostInfoCompact extends Tag
{
    public function get_name() { return 'imo-post-info-compact'; }
    public function get_title(){ return __('Imo: Info (Cidade/Estado/Status/Tipo)', 'imo-grifo'); }
    public function get_group(){ return 'post'; }
    public function get_categories()
    {
        $cat = defined('Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY')
            ? DynamicModule::TEXT_CATEGORY
            : 'text';
        return [ $cat ];
    }

    protected function register_controls()
    {
        $this->add_control('order', [
            'label'       => __('Ordem', 'imo-grifo'),
            'type'        => Controls_Manager::SELECT2,
            'multiple'    => true,
            'label_block' => true,
            'options'     => [
                'cidade' => __('Cidade', 'imo-grifo'),
                'estado' => __('Estado (UF)', 'imo-grifo'),
                'status' => __('Status (Obra)', 'imo-grifo'),
                'tipo'   => __('Tipo', 'imo-grifo'),
            ],
            'default'     => ['cidade','estado','status','tipo'],
        ]);

        $this->add_control('separator', [
            'label'   => __('Separador', 'imo-grifo'),
            'type'    => Controls_Manager::TEXT,
            'default' => ' | ',
        ]);

        $this->add_control('link_terms', [
            'label'        => __('Linkar termos', 'imo-grifo'),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => '',
        ]);

        // Fundamental p/ editor fora de Loop
        $this->add_control('post_id', [
            'label'       => __('Post ID (pré-visualização no editor)', 'imo-grifo'),
            'type'        => Controls_Manager::NUMBER,
        ]);
    }

    private function first_terms(int $post_id, string $tax, bool $link): string
    {
        if (! taxonomy_exists($tax)) { return ''; }
        $terms = get_the_terms($post_id, $tax);
        if (is_wp_error($terms) || empty($terms)) { return ''; }

        $items = [];
        foreach ($terms as $t) {
            if ($link) {
                $url = get_term_link($t);
                if (! is_wp_error($url)) {
                    $items[] = '<a href="' . esc_url($url) . '">' . esc_html($t->name) . '</a>';
                }
            } else {
                $items[] = esc_html($t->name);
            }
        }
        return implode(', ', $items);
    }

    public function render()
    {
        $s       = $this->get_settings();
        $post_id = !empty($s['post_id']) ? absint($s['post_id']) : get_the_ID();
        $order   = isset($s['order']) ? (array) $s['order'] : ['cidade','estado','status','tipo'];
        $sep     = isset($s['separator']) ? (string) $s['separator'] : ' | ';
        $link    = (isset($s['link_terms']) && $s['link_terms'] === 'yes');

        if (! $post_id) { echo ''; return; }

        $chunks = [];
        foreach ($order as $key) {
            if (! in_array($key, ['cidade','estado','status','tipo'], true)) { continue; }
            $txt = $this->first_terms($post_id, $key, $link);
            if ($txt !== '') { $chunks[] = $txt; }
        }

        echo wp_kses_post(implode($sep, $chunks));
    }
}
