<?php
namespace ImoGrifo;


final class PostType
{
public function register(): void
{
$labels = [
'name' => __('Empreendimentos', 'imo-grifo'),
'singular_name' => __('Empreendimento', 'imo-grifo'),
'add_new' => __('Adicionar novo', 'imo-grifo'),
'add_new_item' => __('Adicionar novo Empreendimento', 'imo-grifo'),
'edit_item' => __('Editar Empreendimento', 'imo-grifo'),
'new_item' => __('Novo Empreendimento', 'imo-grifo'),
'view_item' => __('Ver Empreendimento', 'imo-grifo'),
'search_items' => __('Buscar Empreendimentos', 'imo-grifo'),
'not_found' => __('Nenhum Empreendimento encontrado', 'imo-grifo'),
'not_found_in_trash' => __('Nenhum Empreendimento na lixeira', 'imo-grifo'),
'all_items' => __('Todos os Empreendimentos', 'imo-grifo'),
];


$args = [
'label' => __('Empreendimentos', 'imo-grifo'),
'labels' => $labels,
'public' => true,
'show_ui' => true,
'show_in_menu' => true,
'menu_icon' => 'dashicons-building',
'supports' => ['title', 'editor', 'excerpt', 'thumbnail'],
'has_archive' => true,
'rewrite' => ['slug' => 'empreendimentos'],
'show_in_rest' => true,
];


register_post_type('empreendimento', $args);
}
}