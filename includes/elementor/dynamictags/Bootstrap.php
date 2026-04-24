<?php
namespace ImoGrifo\DynamicTags;

if (! defined('ABSPATH')) { exit; }

final class Bootstrap
{
    public static function init(): void
    {
        add_action('elementor/dynamic_tags/register', function($manager){

            // === Requires das suas tags existentes ===
            require_once IMOGRIFO_PATH . '/includes/DynamicTags/TagMetaCTALabel.php';
            require_once IMOGRIFO_PATH . '/includes/DynamicTags/TagMetaCTAUrl.php';
            require_once IMOGRIFO_PATH . '/includes/DynamicTags/TagMetaFrase.php';
            require_once IMOGRIFO_PATH . '/includes/DynamicTags/TagPostTerms.php';
            require_once IMOGRIFO_PATH . '/includes/DynamicTags/TagPostInfoCompact.php';

            // === NOVA tag: Post Terms (Filtrados) ===
            require_once IMOGRIFO_PATH . '/includes/DynamicTags/TagPostTermsFiltered.php';

            // === Registro no Elementor ===
            $manager->register(new \ImoGrifo\DynamicTags\TagMetaCTALabel());
            $manager->register(new \ImoGrifo\DynamicTags\TagMetaCTAUrl());
            $manager->register(new \ImoGrifo\DynamicTags\TagMetaFrase());
            $manager->register(new \ImoGrifo\DynamicTags\TagPostTerms());
            $manager->register(new \ImoGrifo\DynamicTags\TagPostInfoCompact());

            // Registra a nova
            $manager->register(new \ImoGrifo\DynamicTags\TagPostTermsFiltered());
        });
    }
}
