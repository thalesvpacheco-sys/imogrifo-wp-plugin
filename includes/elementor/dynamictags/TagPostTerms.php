<?php
namespace ImoGrifo\DynamicTags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Controls_Manager;
use Elementor\Modules\DynamicTags\Module as DynamicModule;

if (! defined('ABSPATH')) { exit; }

final class TagPostTerms extends Tag
{
    public function get_name() { return 'imo-post-terms'; }
    public function get_title(){ return __('Imo: Post Terms (Lista)', 'imo-grifo'); }
    public function get_group(){ return 'post'; }
    public function get_categories(){ return [ DynamicModule::TEXT_CATEGORY ]; }

    protected function register_controls()
    {
        // Lista de taxonomias públicas (exceto post_format)
        $tax_objects = get_taxonomies(['public' => true], 'objects');
        $options = [];
        if (is_array($tax_objects)) {
            foreach ($tax_objects as $slug => $tx) {
                if ($slug === 'post_format') { continue; }
                $label = isset($tx->labels->singular_name) ? $tx->labels->singular_name : $slug;
                $options[$slug] = $label . " ({$slug})";
            }
        }

        // Sugerir as que usamos
        $default = [];
        foreach (['cidade','estado','status','tipo'] as $tx) {
            if (taxonomy_exists($tx)) { $default[] = $tx; }
        }

        $this->add_control('taxonomies', [
            'label'       => __('Taxonomias', 'imo-grifo'),
            'type'        => Controls_Manager::SELECT2,
            'multiple'    => true,
            'label_block' => true,
            'options'     => $options,
            'default'     => $default,
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
            'description' => __('Use quando não estiver em Loop ou sem Preview Settings.', 'imo-grifo'),
        ]);
    }

    public function render()
    {
        $s         = $this->get_settings();
        $post_id   = !empty($s['post_id']) ? absint($s['post_id']) : get_the_ID();
        $taxonomies= isset($s['taxonomies']) ? (array) $s['taxonomies'] : [];
        $separator = isset($s['separator']) ? (string) $s['separator'] : ' | ';
        $link      = (isset($s['link_terms']) && $s['link_terms'] === 'yes');

        if (! $post_id || empty($taxonomies)) { echo ''; return; }

        $chunks = [];

        foreach ($taxonomies as $tx) {
            if (! taxonomy_exists($tx)) { continue; }

            $terms = get_the_terms($post_id, $tx);
            if (is_wp_error($terms) || empty($terms)) { continue; }

            $names = [];
            foreach ($terms as $t) {
                if ($link) {
                    $url = get_term_link($t);
                    if (! is_wp_error($url)) {
                        $names[] = '<a href="' . esc_url($url) . '">' . esc_html($t->name) . '</a>';
                    }
                } else {
                    $names[] = esc_html($t->name);
                }
            }

            if (! empty($names)) {
                $chunks[] = implode(', ', $names);
            }
        }

        echo wp_kses_post(implode($separator, $chunks));
    }
}
