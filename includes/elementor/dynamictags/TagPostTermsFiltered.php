<?php

declare(strict_types=1);

namespace ImoGrifo\DynamicTags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Controls_Manager;
use Elementor\Modules\DynamicTags\Module;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Dynamic Tag: Post Terms (Filtrados)
 * - Escolhe a taxonomia
 * - Incluir/Excluir por slug
 * - Ordem (pela lista incluída, nome ou ID)
 * - Limite, separador e com/sem link
 * Uso: em qualquer campo TEXT do Elementor (ex.: Description no Image Hover Effects)
 */
final class TagPostTermsFiltered extends Tag
{
    public function get_name(): string
    {
        return 'imo-post-terms-filtered';
    }

    public function get_title(): string
    {
        return 'RN: Post Terms (Filtrados)';
    }

    public function get_group(): string
    {
        return Module::POST_GROUP;
    }

    public function get_categories(): array
    {
        return [ Module::TEXT_CATEGORY ];
    }

    protected function register_controls(): void
    {
        // Taxonomias públicas como opções
        $tax_objects = get_taxonomies(['public' => true], 'objects');
        $tax_options = [];
        foreach ($tax_objects as $tax) {
            $tax_options[$tax->name] = $tax->labels->singular_name ?: $tax->label;
        }

        $this->add_control('taxonomy', [
            'label'   => 'Taxonomia',
            'type'    => Controls_Manager::SELECT,
            'options' => $tax_options,
            'default' => 'category',
        ]);

        $this->add_control('include_slugs', [
            'label'       => 'Incluir (slugs, vírgula)',
            'type'        => Controls_Manager::TEXT,
            'placeholder' => 'ex.: goiania,setor-bueno,2-quartos',
            'description' => 'Se vazio, usa todos os termos atribuídos.',
        ]);

        $this->add_control('exclude_slugs', [
            'label'       => 'Excluir (slugs, vírgula)',
            'type'        => Controls_Manager::TEXT,
            'placeholder' => 'ex.: descontinuado,interno',
        ]);

        $this->add_control('limit', [
            'label'       => 'Limite',
            'type'        => Controls_Manager::NUMBER,
            'min'         => 0,
            'default'     => 0,
            'description' => '0 = sem limite',
        ]);

        $this->add_control('link', [
            'label'        => 'Linkar termos para o arquivo?',
            'type'         => Controls_Manager::SWITCHER,
            'label_on'     => 'Sim',
            'label_off'    => 'Não',
            'return_value' => 'yes',
            'default'      => 'yes',
        ]);

        $this->add_control('separator', [
            'label'   => 'Separador',
            'type'    => Controls_Manager::TEXT,
            'default' => ' • ',
        ]);

        $this->add_control('orderby', [
            'label'   => 'Ordenar por',
            'type'    => Controls_Manager::SELECT,
            'options' => [
                'include' => 'Ordem da lista "Incluir"',
                'name'    => 'Nome',
                'id'      => 'ID',
            ],
            'default' => 'include',
        ]);

        $this->add_control('order', [
            'label'     => 'Ordem',
            'type'      => Controls_Manager::SELECT,
            'options'   => [
                'ASC'  => 'ASC',
                'DESC' => 'DESC',
            ],
            'default'   => 'ASC',
            'condition' => [
                'orderby!' => 'include',
            ],
        ]);
    }

    public function render(): void
    {
        $post_id = get_the_ID();
        if (! $post_id) {
            return;
        }

        $tax = (string) ($this->get_settings('taxonomy') ?? '');
        if (! $tax) {
            return;
        }

        $terms = get_the_terms($post_id, $tax);
        if (empty($terms) || is_wp_error($terms)) {
            return;
        }

        // Normaliza listas
        $include = array_filter(array_map('trim', explode(',', (string) ($this->get_settings('include_slugs') ?? ''))));
        $exclude = array_filter(array_map('trim', explode(',', (string) ($this->get_settings('exclude_slugs') ?? ''))));

        // Filtra por include
        if (! empty($include)) {
            $terms = array_values(array_filter($terms, static function ($t) use ($include) {
                return in_array($t->slug, $include, true);
            }));
            // Reordena conforme a lista "include"
            usort($terms, static function ($a, $b) use ($include) {
                return array_search($a->slug, $include, true) <=> array_search($b->slug, $include, true);
            });
        }

        // Exclui
        if (! empty($exclude)) {
            $terms = array_values(array_filter($terms, static function ($t) use ($exclude) {
                return ! in_array($t->slug, $exclude, true);
            }));
        }

        // Ordenação alternativa
        $orderby = (string) ($this->get_settings('orderby') ?? 'include');
        if ($orderby !== 'include') {
            $order = strtoupper((string) ($this->get_settings('order') ?? 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
            usort($terms, static function ($a, $b) use ($orderby, $order) {
                $valA = $orderby === 'id' ? (int) $a->term_id : (string) $a->name;
                $valB = $orderby === 'id' ? (int) $b->term_id : (string) $b->name;
                $cmp  = $valA <=> $valB;
                return $order === 'DESC' ? -$cmp : $cmp;
            });
        }

        // Limite
        $limit = (int) ($this->get_settings('limit') ?? 0);
        if ($limit > 0) {
            $terms = array_slice($terms, 0, $limit);
        }

        if (empty($terms)) {
            return;
        }

        $with_link = $this->get_settings('link') === 'yes';
        $sep       = (string) ($this->get_settings('separator') ?? ' • ');

        $pieces = array_map(static function ($t) use ($with_link) {
            $name = esc_html($t->name);
            if ($with_link) {
                $url = get_term_link($t);
                if (! is_wp_error($url)) {
                    return '<a href="' . esc_url($url) . '">' . $name . '</a>';
                }
            }
            return $name;
        }, $terms);

        echo wp_kses_post(implode($sep, $pieces));
    }
}
