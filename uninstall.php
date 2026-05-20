<?php

if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Remove plugin options (prefixo imo_)
$option_names = [
    // Nenhuma option registrada na versão atual.
    // Adicionar aqui quando novas options forem criadas.
];
foreach ($option_names as $option) {
    delete_option($option);
    delete_site_option($option);
}

// Remove plugin transients (prefixo imo_)
$transient_names = [
    // Nenhum transient registrado na versão atual.
    // Adicionar aqui quando novos transients forem criados.
];
foreach ($transient_names as $transient) {
    delete_transient($transient);
    delete_site_transient($transient);
}

// NÃO remover: posts de empreendimento, termos de taxonomia, metadados.
// Esses dados pertencem ao cliente e devem persistir após remoção do plugin.
