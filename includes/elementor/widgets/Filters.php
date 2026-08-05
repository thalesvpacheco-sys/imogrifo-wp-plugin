<?php
declare(strict_types=1);

namespace ImoGrifo\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

if ( ! defined('ABSPATH') ) { exit; }

final class Filters extends Widget_Base
{
    public function get_name() { return 'imo-filters'; }
    public function get_title() { return __('Imo Grifo: Filters (Archive)', 'imo-grifo'); }
    public function get_icon() { return 'eicon-filter'; }
    public function get_categories() { return ['imo-grifo']; }

    public function get_style_depends() { return ['imo-filters-css']; }
    public function get_script_depends() { return ['imo-filters-js']; }

    protected function register_controls() {
        $this->start_controls_section('section_content', [ 'label' => __('Conteúdo', 'imo-grifo') ]);

        $this->add_control('layout', [
            'label'   => __('Layout', 'imo-grifo'),
            'type'    => Controls_Manager::SELECT,
            'options' => [
                'full'   => __('Barra completa', 'imo-grifo'),
                'estado' => __('Estado', 'imo-grifo'),
            ],
            'default' => 'full',
        ]);

        $this->add_control('placeholder', [
            'label'     => __('Placeholder (busca)', 'imo-grifo'),
            'type'      => Controls_Manager::TEXT,
            'default'   => __('Encontre um Empreendimento', 'imo-grifo'),
            'condition' => [ 'layout' => 'full' ],
        ]);

        $this->add_control('show_cidade', [
            'label'        => __('Mostrar Localização (cidade)', 'imo-grifo'),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
            'condition'    => [ 'layout' => 'full' ],
        ]);

        $this->add_control('show_status', [
            'label'        => __('Mostrar Status (obra)', 'imo-grifo'),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
            'condition'    => [ 'layout' => 'full' ],
        ]);

        $this->add_control('show_estado', [
            'label'        => __('Mostrar Estado', 'imo-grifo'),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => '',
            'condition'    => [ 'layout' => 'full' ],
        ]);

        $this->add_control('show_tipo', [
            'label'        => __('Mostrar Tipo', 'imo-grifo'),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => '',
            'condition'    => [ 'layout' => 'full' ],
        ]);

        $this->end_controls_section();

        // ======================================================
        // ESTILO — Barra
        // ======================================================
        $this->start_controls_section('section_bar_style', [
            'label' => __('Barra', 'imo-grifo'),
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('bar_bg', [
            'label'     => __('Cor de fundo', 'imo-grifo'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#f0ebf5',
            'selectors' => [ '{{WRAPPER}} .rn-search-wrapper' => 'background-color: {{VALUE}};' ],
        ]);

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'bar_border',
                'label'    => __('Borda', 'imo-grifo'),
                'selector' => '{{WRAPPER}} .rn-search-wrapper',
            ]
        );

        $this->add_responsive_control('pill_radius', [
            'label'      => __('Raio da borda', 'imo-grifo'),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 100 ] ],
            'default'    => [ 'size' => 40, 'unit' => 'px' ],
            'selectors'  => [ '{{WRAPPER}} .rn-search-wrapper' => 'border-radius: {{SIZE}}{{UNIT}};' ],
        ]);

        $this->add_responsive_control('bar_padding', [
            'label'      => __('Padding', 'imo-grifo'),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px'],
            'default'    => [
                'top'      => 12,
                'right'    => 16,
                'bottom'   => 12,
                'left'     => 16,
                'unit'     => 'px',
                'isLinked' => false,
            ],
            'selectors'  => [
                '{{WRAPPER}} .rn-search-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'bar_shadow',
                'label'    => __('Sombra', 'imo-grifo'),
                'selector' => '{{WRAPPER}} .rn-search-wrapper',
            ]
        );

        $this->end_controls_section();

        // ======================================================
        // ESTILO — Campo de Busca
        // ======================================================
        $this->start_controls_section('section_search_style', [
            'label' => __('Campo de Busca', 'imo-grifo'),
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('search_bg', [
            'label'     => __('Cor de fundo', 'imo-grifo'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '',
            'selectors' => [ '{{WRAPPER}} .rn-search-wrapper input:not([type="hidden"])' => 'background-color: {{VALUE}};' ],
        ]);

        $this->add_control('search_color', [
            'label'     => __('Cor do texto', 'imo-grifo'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#0f172a',
            'selectors' => [ '{{WRAPPER}} .rn-search-wrapper input:not([type="hidden"])' => 'color: {{VALUE}};' ],
        ]);

        $this->add_control('search_placeholder_color', [
            'label'     => __('Cor do placeholder', 'imo-grifo'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '',
            'selectors' => [ '{{WRAPPER}} .rn-search-wrapper input:not([type="hidden"])::placeholder' => 'color: {{VALUE}};' ],
        ]);

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'search_typography',
                'label'    => __('Tipografia', 'imo-grifo'),
                'selector' => '{{WRAPPER}} .rn-search-wrapper input:not([type="hidden"])',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'search_border',
                'label'    => __('Borda', 'imo-grifo'),
                'selector' => '{{WRAPPER}} .rn-search-wrapper input:not([type="hidden"])',
            ]
        );

        $this->add_responsive_control('search_radius', [
            'label'      => __('Raio da borda', 'imo-grifo'),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 50 ] ],
            'default'    => [ 'size' => 0, 'unit' => 'px' ],
            'selectors'  => [ '{{WRAPPER}} .rn-search-wrapper input:not([type="hidden"])' => 'border-radius: {{SIZE}}{{UNIT}};' ],
        ]);

        $this->add_responsive_control('search_padding', [
            'label'      => __('Padding', 'imo-grifo'),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px'],
            'default'    => [
                'top'      => 10,
                'right'    => 12,
                'bottom'   => 10,
                'left'     => 12,
                'unit'     => 'px',
                'isLinked' => false,
            ],
            'selectors'  => [
                '{{WRAPPER}} .rn-search-wrapper input:not([type="hidden"])' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->end_controls_section();

        // ======================================================
        // ESTILO — Dropdowns
        // ======================================================
        $this->start_controls_section('section_dropdown_style', [
            'label' => __('Dropdowns', 'imo-grifo'),
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('dropdown_heading_trigger', [
            'label' => __('Trigger (botão)', 'imo-grifo'),
            'type'  => Controls_Manager::HEADING,
        ]);

        $this->add_control('dropdown_trigger_bg', [
            'label'     => __('Cor de fundo', 'imo-grifo'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#ffffff',
            'selectors' => [ '{{WRAPPER}} .imo-dropdown-trigger' => 'background-color: {{VALUE}};' ],
        ]);

        $this->add_control('dropdown_trigger_color', [
            'label'     => __('Cor do texto', 'imo-grifo'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#333333',
            'selectors' => [ '{{WRAPPER}} .imo-dropdown-trigger' => 'color: {{VALUE}};' ],
        ]);

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'dropdown_trigger_typography',
                'label'    => __('Tipografia', 'imo-grifo'),
                'selector' => '{{WRAPPER}} .imo-dropdown-trigger',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'dropdown_trigger_border',
                'label'    => __('Borda', 'imo-grifo'),
                'selector' => '{{WRAPPER}} .imo-dropdown-trigger',
            ]
        );

        $this->add_responsive_control('dropdown_trigger_radius', [
            'label'      => __('Raio da borda', 'imo-grifo'),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 100 ] ],
            'default'    => [ 'size' => 8, 'unit' => 'px' ],
            'selectors'  => [ '{{WRAPPER}} .imo-dropdown-trigger' => 'border-radius: {{SIZE}}{{UNIT}};' ],
        ]);

        $this->add_control('dropdown_heading_list', [
            'label'     => __('Lista (opções)', 'imo-grifo'),
            'type'      => Controls_Manager::HEADING,
            'separator' => 'before',
        ]);

        $this->add_control('dropdown_list_bg', [
            'label'     => __('Cor de fundo da lista', 'imo-grifo'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#ffffff',
            'selectors' => [ '{{WRAPPER}} .imo-dropdown-list' => 'background-color: {{VALUE}};' ],
        ]);

        $this->add_control('dropdown_list_color', [
            'label'     => __('Cor do texto dos itens', 'imo-grifo'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#333333',
            'selectors' => [ '{{WRAPPER}} .imo-dropdown-list li' => 'color: {{VALUE}};' ],
        ]);

        $this->add_control('dropdown_list_hover_bg', [
            'label'     => __('Cor de fundo hover dos itens', 'imo-grifo'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#f5f5f5',
            'selectors' => [ '{{WRAPPER}} .imo-dropdown-list li:hover' => 'background-color: {{VALUE}};' ],
        ]);

        $this->add_responsive_control('dropdown_list_radius', [
            'label'      => __('Raio da borda da lista', 'imo-grifo'),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 50 ] ],
            'default'    => [ 'size' => 12, 'unit' => 'px' ],
            'selectors'  => [ '{{WRAPPER}} .imo-dropdown-list' => 'border-radius: {{SIZE}}{{UNIT}};' ],
        ]);

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'dropdown_list_shadow',
                'label'    => __('Sombra da lista', 'imo-grifo'),
                'selector' => '{{WRAPPER}} .imo-dropdown-list',
            ]
        );

        $this->end_controls_section();

        // ======================================================
        // ESTILO — Botão de Busca
        // ======================================================
        $this->start_controls_section('section_btn_style', [
            'label' => __('Botão de Busca', 'imo-grifo'),
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('btn_bg', [
            'label'     => __('Cor de fundo', 'imo-grifo'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#026938',
            'selectors' => [ '{{WRAPPER}} .rn-search-btn' => 'background-color: {{VALUE}};' ],
        ]);

        $this->add_control('btn_bg_hover', [
            'label'     => __('Cor de fundo (hover)', 'imo-grifo'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#01512c',
            'selectors' => [ '{{WRAPPER}} .rn-search-btn:hover' => 'background-color: {{VALUE}};' ],
        ]);

        $this->add_control('btn_icon_color', [
            'label'     => __('Cor do ícone', 'imo-grifo'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#ffffff',
            'selectors' => [ '{{WRAPPER}} .rn-search-btn svg path' => 'fill: {{VALUE}};' ],
        ]);

        $this->add_responsive_control('btn_radius', [
            'label'      => __('Raio da borda', 'imo-grifo'),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 50 ] ],
            'default'    => [ 'size' => 12, 'unit' => 'px' ],
            'selectors'  => [ '{{WRAPPER}} .rn-search-btn' => 'border-radius: {{SIZE}}{{UNIT}};' ],
        ]);

        $this->add_responsive_control('btn_icon_size', [
            'label'      => __('Tamanho do ícone', 'imo-grifo'),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => [ 'px' => [ 'min' => 12, 'max' => 48 ] ],
            'default'    => [ 'size' => 22, 'unit' => 'px' ],
            'selectors'  => [
                '{{WRAPPER}} .rn-search-btn svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
            ],
        ]);

        $this->add_responsive_control('btn_size', [
            'label'      => __('Tamanho do botão', 'imo-grifo'),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => [ 'px' => [ 'min' => 32, 'max' => 80 ] ],
            'default'    => [ 'size' => 44, 'unit' => 'px' ],
            'selectors'  => [
                '{{WRAPPER}} .rn-search-btn' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
            ],
        ]);

        $this->end_controls_section();

        // ======================================================
        // ESTILO — Autocomplete
        // ======================================================
        $this->start_controls_section('section_autocomplete_style', [
            'label' => __('Autocomplete', 'imo-grifo'),
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('autocomplete_bg', [
            'label'     => __('Cor de fundo da lista', 'imo-grifo'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#ffffff',
            'selectors' => [ '{{WRAPPER}} .rn-autocomplete' => 'background-color: {{VALUE}};' ],
        ]);

        $this->add_control('autocomplete_title_color', [
            'label'     => __('Cor do título', 'imo-grifo'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#0f172a',
            'selectors' => [ '{{WRAPPER}} .imo-suggest-title' => 'color: {{VALUE}};' ],
        ]);

        $this->add_control('autocomplete_meta_color', [
            'label'     => __('Cor da meta info (cidade/status)', 'imo-grifo'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#64748b',
            'selectors' => [ '{{WRAPPER}} .imo-suggest-meta' => 'color: {{VALUE}};' ],
        ]);

        $this->add_control('autocomplete_hover_bg', [
            'label'     => __('Cor de fundo hover', 'imo-grifo'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#f1f5f9',
            'selectors' => [ '{{WRAPPER}} .rn-autocomplete li:hover' => 'background-color: {{VALUE}};' ],
        ]);

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'autocomplete_border',
                'label'    => __('Borda', 'imo-grifo'),
                'selector' => '{{WRAPPER}} .rn-autocomplete',
            ]
        );

        $this->add_responsive_control('autocomplete_radius', [
            'label'      => __('Raio da borda', 'imo-grifo'),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 50 ] ],
            'default'    => [ 'size' => 12, 'unit' => 'px' ],
            'selectors'  => [ '{{WRAPPER}} .rn-autocomplete' => 'border-radius: {{SIZE}}{{UNIT}};' ],
        ]);

        $this->end_controls_section();
    }

    public function render() {
        $s      = $this->get_settings_for_display();
        $layout = ! empty($s['layout']) ? $s['layout'] : 'full';

        if ( $layout === 'estado' ) {
            $this->render_estado();
            return;
        }

        $this->render_full( $s );
    }

    // --- Layout Full (barra completa) ---
    private function render_full( array $s ): void {
        // Garante enqueue explícito — get_script_depends() pode não enfileirar
        // quando o Elementor "Improved Asset Loading" está ativo
        wp_enqueue_script('imo-filters-js');
        wp_localize_script('imo-filters-js', 'imoFilters', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('imo_suggest'),
        ]);

        $action = get_post_type_archive_link('empreendimento');
        if ( ! $action ) { $action = home_url('/'); }

        $q_val   = isset($_GET['q']) ? sanitize_text_field( wp_unslash($_GET['q']) ) : '';
        $cid_cur = isset($_GET['cidade']) ? sanitize_text_field( wp_unslash($_GET['cidade']) ) : '';
        $st_cur  = isset($_GET['status_obra']) ? sanitize_text_field( wp_unslash($_GET['status_obra']) )
                  : ( isset($_GET['status']) ? sanitize_text_field( wp_unslash($_GET['status']) ) : '' );
        $est_cur = isset($_GET['estado']) ? sanitize_title( wp_unslash($_GET['estado']) ) : '';
        $tipo_cur = isset($_GET['tipo']) ? sanitize_title( wp_unslash($_GET['tipo']) ) : '';

        // Compat: usa status_obra se existir; senão cai pro status antigo
        $status_tax = taxonomy_exists('status_obra') ? 'status_obra' : ( taxonomy_exists('status') ? 'status' : 'status_obra' );

        $cidades = get_terms([ 'taxonomy'=>'cidade',    'hide_empty'=>false ]);
        $status  = get_terms([ 'taxonomy'=>$status_tax, 'hide_empty'=>false ]);
        $estados = $s['show_estado'] === 'yes' ? get_terms([ 'taxonomy'=>'estado', 'hide_empty'=>true ]) : [];
        $tipos   = $s['show_tipo'] === 'yes' ? get_terms([ 'taxonomy'=>'tipo', 'hide_empty'=>true ]) : [];

        echo '<form class="imo-filters-form" action="'. esc_url($action) .'" method="get" role="search">';
        echo '<div class="rn-search-wrapper imo-rn-filter" data-scope="1">';

        echo '<input type="text" class="rn-search" name="q" autocomplete="off" value="'. esc_attr($q_val) .'" placeholder="'. esc_attr($s['placeholder']) .'" aria-label="'. esc_attr__('Buscar por nome do empreendimento', 'imo-grifo') .'">';

        if ( $s['show_cidade'] === 'yes' ) {
            $this->render_dropdown( 'cidade', __('Localização', 'imo-grifo'), $cidades, $cid_cur );
        }

        if ( $s['show_status'] === 'yes' ) {
            $this->render_dropdown( $status_tax, __('Status', 'imo-grifo'), $status, $st_cur );
        }

        if ( $s['show_estado'] === 'yes' ) {
            $this->render_dropdown( 'estado', __('Estado', 'imo-grifo'), $estados, $est_cur );
        }

        if ( $s['show_tipo'] === 'yes' ) {
            $this->render_dropdown( 'tipo', __('Tipo', 'imo-grifo'), $tipos, $tipo_cur );
        }

        echo '<button class="rn-search-btn" type="submit" aria-label="'. esc_attr__('Aplicar filtros', 'imo-grifo') .'">
                <svg width="22" height="22" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                    <path d="M16.6 18L10.3 11.7C9.8 12.1 9.225 12.4167 8.575 12.65C7.925 12.8833 7.23333 13 6.5 13C4.68333 13 3.14583 12.3708 1.8875 11.1125C0.629167 9.85417 0 8.31667 0 6.5C0 4.68333 0.629167 3.14583 1.8875 1.8875C3.14583 0.629167 4.68333 0 6.5 0C8.31667 0 9.85417 0.629167 11.1125 1.8875C12.3708 3.14583 13 4.68333 13 6.5C13 7.23333 12.8833 7.925 12.65 8.575C12.4167 9.225 12.1 9.8 11.7 10.3L18 16.6L16.6 18ZM6.5 11C7.75 11 8.8125 10.5625 9.6875 9.6875C10.5625 8.8125 11 7.75 11 6.5C11 5.25 10.5625 4.1875 9.6875 3.3125C8.8125 2.4375 7.75 2 6.5 2C5.25 2 4.1875 2.4375 3.3125 3.3125C2.4375 4.1875 2 5.25 2 6.5C2 7.75 2.4375 8.8125 3.3125 9.6875C4.1875 10.5625 5.25 11 6.5 11Z" fill="#EA932A"/>
                </svg>
              </button>';

        echo '<ul class="rn-autocomplete" aria-label="'. esc_attr__('Sugestões', 'imo-grifo') .'" role="listbox"></ul>';

        echo '</div>';
        echo '</form>';
    }

    // --- Dropdown customizado (reutilizado por cidade, status, estado) ---
    private function render_dropdown( string $param, string $placeholder, mixed $terms, string $cur_val ): void {
        // Descobre label atual
        $cur_label = $placeholder;
        if ( $cur_val !== '' && ! is_wp_error($terms) && $terms ) {
            foreach ( $terms as $t ) {
                if ( $t->slug === $cur_val ) { $cur_label = $t->name; break; }
            }
        }

        printf('<div class="imo-dropdown" data-name="%s">', esc_attr($param));

        echo '<button type="button" class="imo-dropdown-trigger" aria-haspopup="listbox" aria-expanded="false">';
        echo '<span class="imo-dropdown-label">'. esc_html($cur_label) .'</span>';
        echo '<svg class="imo-dropdown-chevron" width="12" height="12" viewBox="0 0 12 12" aria-hidden="true" focusable="false"><path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        echo '</button>';

        echo '<ul class="imo-dropdown-list" role="listbox" aria-label="'. esc_attr($placeholder) .'">';
        printf(
            '<li data-value="" role="option"%s>%s</li>',
            $cur_val === '' ? ' class="selected"' : '',
            esc_html($placeholder)
        );
        if ( ! is_wp_error($terms) && $terms ) {
            foreach ( $terms as $t ) {
                printf(
                    '<li data-value="%s" role="option"%s>%s</li>',
                    esc_attr($t->slug),
                    $cur_val === $t->slug ? ' class="selected"' : '',
                    esc_html($t->name)
                );
            }
        }
        echo '</ul>';

        printf('<input type="hidden" name="%s" value="%s">', esc_attr($param), esc_attr($cur_val));

        echo '</div>';
    }

    // --- Layout Estado (dropdown minimalista standalone) ---
    private function render_estado(): void {
        $estados = get_terms([ 'taxonomy' => 'estado', 'hide_empty' => true ]);
        $scope   = esc_attr( (string) $this->get_id() );
        $archive = get_post_type_archive_link('empreendimento');
        if ( ! $archive ) { $archive = home_url('/'); }

        $cur_slug  = isset($_GET['estado']) ? sanitize_title( wp_unslash($_GET['estado']) ) : '';
        $cur_label = __('Todos os estados', 'imo-grifo');
        if ( $cur_slug && ! is_wp_error($estados) && $estados ) {
            foreach ( $estados as $e ) {
                if ( $e->slug === $cur_slug ) { $cur_label = $e->name; break; }
            }
        }

        printf(
            '<div class="imo-state-filter" data-scope="%s" data-archive="%s">',
            $scope,
            esc_attr( $archive )
        );

        echo '<button class="imo-state-trigger" type="button" aria-expanded="false" aria-haspopup="listbox">';
        echo '<svg class="imo-state-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z" fill="#026938"/></svg>';
        echo '<span class="imo-state-label">'. esc_html( $cur_label ) .'</span>';
        echo '<svg class="imo-state-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M7 10l5 5 5-5z" fill="#999"/></svg>';
        echo '</button>';

        echo '<ul class="imo-state-dropdown" role="listbox" aria-label="'. esc_attr__('Selecionar estado', 'imo-grifo') .'">';
        printf(
            '<li data-slug="" role="option"%s>%s</li>',
            $cur_slug === '' ? ' class="imo-state-selected"' : '',
            esc_html__('Todos os estados', 'imo-grifo')
        );
        if ( ! is_wp_error($estados) && $estados ) {
            foreach ( $estados as $e ) {
                printf(
                    '<li data-slug="%s" role="option"%s>%s</li>',
                    esc_attr( $e->slug ),
                    $cur_slug === $e->slug ? ' class="imo-state-selected"' : '',
                    esc_html( $e->name )
                );
            }
        }
        echo '</ul>';

        echo '</div>';
    }
}
