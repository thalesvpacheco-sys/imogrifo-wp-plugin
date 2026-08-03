<?php
namespace ImoGrifo;

final class Taxonomies
{
    public function register(): void
    {
        // Helper de labels
        $labels = function (string $name, string $singular, bool $hierarchical) {
            $base = [
                'name'                       => $name,
                'singular_name'              => $singular,
                'menu_name'                  => $name,
                'all_items'                  => "Todos os {$name}",
                'edit_item'                  => "Editar {$singular}",
                'view_item'                  => "Ver {$singular}",
                'update_item'                => "Atualizar {$singular}",
                'add_new_item'               => "Adicionar novo {$singular}",
                'new_item_name'              => "Novo {$singular}",
                'search_items'               => "Buscar {$name}",
                'not_found'                  => "Nenhum {$singular} encontrado",
            ];

            if ($hierarchical) {
                $base['parent_item']       = "{$singular} pai";
                $base['parent_item_colon'] = "{$singular} pai:";
            } else {
                $base += [
                    'popular_items'              => "{$name} populares",
                    'separate_items_with_commas' => "Separe {$name} com vírgulas",
                    'add_or_remove_items'        => "Adicionar ou remover {$name}",
                    'choose_from_most_used'      => "Escolha entre os mais usados",
                    'no_terms'                   => "Nenhum {$singular}",
                ];
            }

            return $base;
        };

        // STATUS (OBRA) — hierárquica para UI de checkbox + edição rápida
        register_taxonomy('status', 'empreendimento', [
            'labels'            => $labels('Status (Obra)', 'Status (Obra)', true),
            'public'            => true,
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rest_base'         => 'status-obra',
            'rewrite'           => ['slug' => 'status-obra'],
        ]);

        // CIDADE (categorias)
        register_taxonomy('cidade', 'empreendimento', [
            'labels'            => $labels('Cidades', 'Cidade', true),
            'public'            => true,
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rest_base'         => 'cidades',
            'rewrite'           => ['slug' => 'cidade'],
        ]);

        // TIPO (categorias)
        register_taxonomy('tipo', 'empreendimento', [
            'labels'            => $labels('Tipos', 'Tipo', true),
            'public'            => true,
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rest_base'         => 'tipos',
            'rewrite'           => ['slug' => 'tipo-empreendimento'],
        ]);

        // ESTADO/UF (categorias)
        register_taxonomy('estado', 'empreendimento', [
            'labels'            => $labels('Estados (UF)', 'Estado (UF)', true),
            'public'            => true,
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rest_base'         => 'estados',
            'rewrite'           => ['slug' => 'estado'],
        ]);

        // LOTES (quantidade — administrativa, não filtrável — DR-12)
        register_taxonomy('lotes', 'empreendimento', [
            'labels'            => $labels('Lotes', 'Lotes', true),
            'public'            => true,
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rest_base'         => 'lotes',
            'rewrite'           => ['slug' => 'lotes'],
        ]);
    }
}
